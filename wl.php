<?php 
	require_once("auth.php");
	require_once("includes/connect.php");
	if($_SESSION['userType'] != 'Hostel')
	{
		header('Location: logout.php');
		exit;	
	}
	if($_SESSION['access'] != '' && !in_array("5_0", $_SESSION['access']) && !in_array("5_1", $_SESSION['access']))
    {
        header('Location: logout.php');
        exit;
    }

    // Get Back code starts
    if(isset($_REQUEST['act']) && $_REQUEST['act'] == 'getback')
    {
        $act = $_REQUEST['act'];
        $guestID = $_REQUEST['sgid'];
        $shared_record_id = $_REQUEST['srID'];
        
        // udate shared status in guest table
        $updGuest['curr_activity_id'] = '0';
        $updGuest['isShared'] = '0';
        $updGuest['outsideHostID'] = '0';
        $whrGuest['guestID'] = $guestID;
        $upd_query = $db->update( 'guest', $updGuest, $whrGuest );

        // udate record in shared workers table
        $updSR['releasedOn'] = date('Y-m-d');
        $whrSR['id'] = $shared_record_id;
        $upd_query = $db->update( 'shared_workers_record', $updSR, $whrSR );
        $rurl = "worklist.php";

        header("location: ".$rurl);
        exit;
    }
    // Get Back code ends
    
    $isUpdate = true;
    if($_SESSION['parent'] != 0 && $_SESSION['access'] != '' && !in_array("5_1", $_SESSION['access']))
        $isUpdate = false;

    // GEt worklist settings to insert into worklist table as we want this in olf worklist pdfs
    $wsq = "SELECT * FROM worklist_settings WHERE hostID = '".$hostID."'";
	if($db->num_rows( $wsq ) == 0)
	{
		$wsq = "SELECT * FROM worklist_settings WHERE id = '1'";
	}
	$wset = $db->get_results( $wsq );
	$top_line_first = $wset[0]['top_line_first'];
	$top_line_second = $wset[0]['top_line_second'];
	$standby_line_first = $wset[0]['standby_line_first'];
	$standby_line_second = $wset[0]['standby_line_second'];
	$guest_order = $wset[0]['guest_order'];
	// ends

	$msg = '';
	if(isset($_GET['pr']) && $_GET['pr'] != '')
	{
		$wldate = date("Y-m-d", strtotime(' + 1 days'));
		if(isset($_GET['p']) && $_GET['p'] != '')
		{
			$worklistID = $_GET['p'];
			$insarr = array();
			$wok_query = "SELECT * FROM worklists Where worklistID = '".$worklistID."'";
			if( $db->num_rows( $wok_query ) > 0 )
			{
				$wres = $db->get_results( $wok_query );
				$hostID = $wres[0]['hostID'];
				$wljson = $wres[0]['wljson'];

				// If guest last date is equal to worklist date then remove from wljson and worklist detail table - Start
				$newr1 = array();
				if($wljson != '')
				{
					$pos = strpos($wljson,",");
					if($pos === false)
						$newr1[] = $wljson;	
					else
					{
						$nwl = explode(",",$wljson);
						$newr1 = array_merge($newr1, $nwl);	
					}
				}

				foreach($newr1 as $vally)
				{
					$gar = explode("_",$vally);
					$guestKaID = $gar[1];
					$guestKiquery = "SELECT * FROM guest Where guestID = '".$guestKaID."'";
					$guestKaRes = $db->get_results( $guestKiquery );
					if($guestKaRes[0]["last_work_date"] != $wldate)
					{
						$wlArray[] = $vally;
					}
				}
				$wljson = implode(",", $wlArray);
				// end
				
				$insarr['hostID'] = $hostID;
				$insarr['wljson'] = $wljson;
				$insarr['wdate'] = $wldate;
				
				$insarr['top_line_first'] = $top_line_first;
				$insarr['top_line_second'] = $top_line_second;
				$insarr['standby_line_first'] = $standby_line_first;
				$insarr['standby_line_second'] = $standby_line_second;

				$ins_query = $db->insert( 'worklists', $insarr);
				if($ins_query)
				{
					$insarr2 = array();
					$wlid = $db->lastid();
					$wd_query = "SELECT * FROM worklists_detail Where worklistID = '".$worklistID."'";
					
					if( $db->num_rows( $wd_query ) > 0 )
					{
						$wd_res = $db->get_results( $wd_query );
						foreach($wd_res as $ro)
						{
							$insarr2['worklistID'] = $wlid;
							$insarr2['enterpriseID'] = $ro['enterpriseID'];
							$insarr2['guestID'] = $ro['guestID'];

							$guestKiquery = "SELECT * FROM guest Where guestID = '".$ro['guestID']."'";
							$guestKaRes = $db->get_results( $guestKiquery );
							if($guestKaRes[0]["last_work_date"] != $wldate)
							{
								$insq = $db->insert( 'worklists_detail', $insarr2);
							}
						}
					}
					
				}
			}
		}
		else
		{
			$insarr['hostID'] = $_GET['pr'];
			$insarr['wdate'] = $wldate;
			$insarr['top_line_first'] = $top_line_first;
			$insarr['top_line_second'] = $top_line_second;
			$insarr['standby_line_first'] = $standby_line_first;
			$insarr['standby_line_second'] = $standby_line_second;
			$ins_query = $db->insert( 'worklists', $insarr);
		}
		
		// Add record in guest availability table
		$hostID = $_GET['pr'];
		$query = "SELECT * FROM guest Where hostID = '".$hostID."' AND sign_out_date = '0000-00-00' AND isOutside = 'No'";
		if( $db->num_rows( $query ) > 0 )
		{
			$rs = $db->get_results( $query );
			$cwldate = DateFromDB($wldate);
			foreach($rs as $rga)
			{
				if($rga['unavailable_dates'] != '')
				{
					$unavailable_dates = explode(",",$rga['unavailable_dates']);
					if(!in_array($cwldate, $unavailable_dates))
					{
						$ir['is_available'] = $rga['available_to_work'];
					}
					else
					{
						$ir['is_available'] = "0";	
					}
				}
				else
				{
					$ir['is_available'] = $rga['available_to_work'];
				}

				$ir['hostID'] = $hostID;
				$ir['guestID'] = $rga['guestID'];
				$ir['wdate'] = $wldate;
				$insq = $db->insert( 'guest_availability', $ir);
			}
		}
		
		header("location: worklist.php");
		exit;
	}
		
	if(isset($_POST['saveEnt']) && $_POST['saveEnt'] == 1)
	{
		
		$var = $_POST;
		$hostID = $var['hostID'];
		
		$worklistID = $var['worklistID'];
		$new['hostID'] = $db->filter($var['hostID']);
		$new['wljson'] = $db->filter($var['WLJson']);
		
		if($worklistID != '')
		{
			$where['worklistID'] = $worklistID;
			$ins_query = $db->update( 'worklists', $new, $where );
		}
		else
		{
			$new['wdate'] = date("Y-m-d", strtotime(' + 1 days'));

			$new['top_line_first'] = $top_line_first;
			$new['top_line_second'] = $top_line_second;
			$new['standby_line_first'] = $standby_line_first;
			$new['standby_line_second'] = $standby_line_second;
			
			$ins_query = $db->insert( 'worklists', $new);
			$worklistID = $db->lastid();
		}
		if($ins_query)
		{
			$arrn = array();
			
			if($worklistID != '')
			{
				$where['worklistID'] = $worklistID;
				$del = $db->delete( 'worklists_detail', $where);		
			}
			
			$wljson = explode(',',$new['wljson']);
			foreach($wljson as $val)
			{
				$nval = explode("_",$val);
				$enterpriseID = $nval[0];
				$guestID = $nval[1];
				$arrn['enterpriseID'] = $enterpriseID;
				$arrn['worklistID'] = $worklistID;
				$arrn['guestID'] = $guestID;
				
				$ins_query2 = $db->insert( 'worklists_detail', $arrn);
				
				// Check that curr activity id is already set or not. If already set then we won't change that until admin unallocate that
				$un_query = "SELECT `curr_activity_id` FROM guest Where guestID = '".$guestID."'";
				$un_res = $db->get_results( $un_query );
				if($un_res[0]['curr_activity_id'] == 0)
				{
					$gar['curr_activity_id'] = $enterpriseID;
					$whr['guestID'] = $guestID;
					$upd_query = $db->update( 'guest', $gar, $whr);
				}
				
			}
		}
		
		
		// Add record in guest availability table
		
		$un_q = "SELECT `wdate` FROM worklists Where worklistID = '".$worklistID."'";
		$un_r = $db->get_results( $un_q );
		$wdate = $un_r[0]['wdate'];
		
		$query = "SELECT * FROM guest Where hostID = '".$hostID."' AND sign_out_date = '0000-00-00' AND isOutside = 'No'";
		if( $db->num_rows( $query ) > 0 )
		{
			$cwdate = DateFromDB($wdate);
			$rs = $db->get_results( $query );
			foreach($rs as $rga)
			{
				$query5 = "SELECT * FROM guest_availability Where guestID = '".$rga['guestID']."' AND wdate = '".$wdate."'";
				if( $db->num_rows( $query5 ) > 0 )
				{
					$rs5 = $db->get_results( $query5 );
					$whre['gaID'] = $rs5[0]['gaID'];

					if($rga['unavailable_dates'] != '')
					{
						$unavailable_dates = explode(",",$rga['unavailable_dates']);
						if(!in_array($cwdate, $unavailable_dates))
						{
							$iru['is_available'] = $rga['available_to_work'];
						}
						else
						{
							$iru['is_available'] = "0";	
						}
					}
					else
					{
						$iru['is_available'] = $rga['available_to_work'];
					}
					//$iru['is_available'] = $rga['available_to_work'];
					
					$insq = $db->update( 'guest_availability', $iru, $whre);		
				}
				else
				{
					$ir['hostID'] = $hostID;
					$ir['guestID'] = $rga['guestID'];


					if($rga['unavailable_dates'] != '')
					{
						$unavailable_dates = explode(",",$rga['unavailable_dates']);
						if(!in_array($cwdate, $unavailable_dates))
						{
							$ir['is_available'] = $rga['available_to_work'];
						}
						else
						{
							$ir['is_available'] = "0";	
						}
					}
					else
					{
						$ir['is_available'] = $rga['available_to_work'];
					}

					$ir['wdate'] = $wdate;
					$insq = $db->insert( 'guest_availability', $ir);
				}
			}
		}
		
		$rurl = "worklist.php";
		header("location: ".$rurl);
		exit;
	}
	
	
	
	
	$que = "SELECT hostID FROM hostel Where userID = '".$_SESSION['userID']."'";
	if( $db->num_rows( $que ) > 0 )
	{
		$res = $db->get_results( $que );
		$hostID = $res[0]['hostID'];
	}
	
	// Changes guest to available according to number of days start
	$qav = "SELECT * FROM guest Where hostID = '".$hostID."' AND sign_out_date = '0000-00-00' AND is_next_avail_date = '1' AND isOutside = 'No'";
	if( $db->num_rows( $qav ) > 0 )
	{
		$rsav = $db->get_results( $qav );
		foreach($rsav as $rav)
		{
			$cdate = date('Y-m-d');
			if($rav['next_avail_date'] == $cdate)
			{
				$new2['next_avail_date'] = '0000-00-00';
				$new2['days_till_avail'] = 0;
				$new2['is_next_avail_date'] = 0;
				$where['guestID'] = $rav['guestID'];
				$upd_query = $db->update( 'guest', $new2, $where );		
			}
		}
	}
	// Changes guest to available according to number of days ends
	
	$today = date("Y-m-d");
	$wlistAvailable = false;
	$wlistPrepare = true;
	$enterpriseIDArray = array();
	$guestIDArray = array();
	$wlj = array();
	$WLJson = '';
	$worklistID = '';
	$wlistDate = date('d M Y', strtotime(' + 1 days'));
	$wlistdchk = date('Y-m-d', strtotime(' + 1 days'));
	
	$wok_query = "SELECT * FROM worklists Where hostID = '".$hostID."' ORDER BY worklistID DESC";
	if( $db->num_rows( $wok_query ) > 0 )
	{
		$wlistAvailable = true;
		$work_res = $db->get_results( $wok_query );
		$WLJson = $work_res[0]['wljson'];
		$worklistID = $work_res[0]['worklistID'];
		if($WLJson != '')
			$wlj = explode(',',$WLJson);
		
		if(count($wlj) > 0)
		{
			foreach($wlj as $val)
			{
				$nval = explode("_",$val);
				$enterpriseID = $nval[0];
				$guestID = $nval[1];
				$enterpriseIDArray[] = $enterpriseID;
				$guestIDArray[] = $guestID;
			}
		}
		
		$wdate = $work_res[0]['wdate'];
		if($wdate == $today || $wdate < $today)
		{
			$wlistPrepare = true;
			$wlistDate = date('d M Y', strtotime(' + 1 days'));
		}
		else
			$wlistPrepare = false;
		
		if($wlistdchk == $wdate)
			$wlborstyle = "border:solid 5px #000;";
		else
			$wlborstyle = "border:solid 5px #FF9999;";
	}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Booking System">
        <meta name="author" content="Amar Singh">

        <link rel="shortcut icon" href="images/favicon_1.ico">

        <title><?php echo $LANG['txtPatients']; ?> - Worklist</title>

        <?php require_once('header.php'); ?>
       
    </head>



    <body class="fixed-left">
        
        <!-- Begin page -->
        <div id="wrapper">
        
            <!-- Top Bar Start -->
            <?php require_once('top.php'); ?>
            <!-- Top Bar End -->

            <!-- ========== Left Sidebar Start ========== -->
			<?php require_once('left.php'); ?>
            <!-- Left Sidebar End --> 

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->                      
            <div class="content-page">
                <!-- Start content -->
                <div class="content">
                    <div class="container">

                        <!-- Page-Title -->
                        <div class="row">
                            <div class="col-md-12">
                            	<div class="col-md-6">
                                <h4 class="pull-left page-title">
                                <?php if($wlistAvailable) { echo "Current Worklist for ".date("d M Y",strtotime($wdate)); }else{ echo "Worklist"; } ?>
                                </h4>
                                </div>
                                <?php if($wlistPrepare) { ?>
                                <?php
                                if($isUpdate)
                                {
                                ?>
                                	<div class="col-md-3">
                                	<a href="worklist.php?p=<?php echo $worklistID; ?>&pr=<?php echo $hostID; ?>"><button class="btn btn-default waves-effect waves-light">Prepare worklist for <?php echo $wlistDate; ?></button></a>
                                    </div>
                                <?php } ?>    
                                
                                <?php } ?>
                                	<div class="col-md-3">
                                    	<div class="col-sm-9" style="float:left;">
                                    		<input type="text" class="form-control" value="" name="searchwl" id="searchwl">
                                        </div>
                                        <div class="col-sm-3">
                                        	<button type="button" class="btn btn-success waves-effect waves-light" name="searchit" id="searchit" >Search</button>
                                        </div>
                                        
                                    </div>
                            </div>
                            
                        </div>
                        <?php if(isset($_REQUEST['es']) && $_REQUEST['es'] == '1'){ ?>
                        <div class="row">
                            <div class="col-md-4">
                        		<div class="alert alert-success alert-dismissable">
                                	<button type="button" class="close" data-dismiss="alert" aria-hidden="true" onclick="location.href='worklist.php';">×</button>
                                	Email has been sent successfully to all the workers.
                        		</div>
                        	</div>
                        </div>
                    <?php } ?>
                        <!-- Form code starts -->
						<div class="row">
                            <div class="col-sm-12">
                            
                            <?php if($msg != ''){ ?>  
                                        
                                        <div class="alert alert-info alert-dismissable">
                                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                            <?php echo $msg; ?>
                                        </div>
                    					<?php } ?>

                            <form class="form-horizontal" role="form" name="updsnip" id="updsnip" method="post" action="" novalidate>
                            	
                            	<?php
	                                            if($isUpdate)
	                                            {
	                                            ?>

                                <!-- Model for guest available starts -->
                                <div class="modal fade choose-enterprise" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodal">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                <div class="col-md-12">
                                                <select class="form-control" name="enterpriseID" id="enterpriseID" style="font-size:12px;">
                                                    <option value="">Select Enterprise</option>
                                                    <?php
													
													if(isset($hostID))
													{
														$ent_q = "SELECT e.* , emp.* FROM enterprise e ,employers emp WHERE e.`empID` = emp.`empID` AND e.hostID = '".$hostID."' AND e.status = '1' ORDER BY TIME(STR_TO_DATE(e.leave_time, '%l:%i %p')) ASC";
														
														if( $db->num_rows( $ent_q ) > 0 )
														{
															$entr = $db->get_results( $ent_q );
															foreach( $entr as $row )
															{
																$n_w_r = $row['num_workers_required'];
																$show = true;
																if(count($wlj) > 0)
																{
																	$cnt = 0;
																	foreach($wlj as $val)
																	{
																		$nvl = explode("_",$val);
																		$eID = $nvl[0];
																		$gID = $nvl[1];
																		if($eID == $row['enterpriseID'])
																			$cnt++;
																	}
																	if($cnt == $n_w_r)
																		$show = false;
																}
															if($show){	
                                                    ?>
                                                            <option value="<?php echo $row['enterpriseID']; ?>" data-dismiss="modal"><?php echo getEmployerName($row['empID']); ?>&nbsp;&nbsp;<?php echo getActivityName($row['activityID']); ?>&nbsp;&nbsp;<?php echo $row['leave_time']." / ".$row['start_time']; ?></option>
                                                    <?php 
															}
                                                        	}
                                                    	}
													}
                                                    ?>	
                                                    	
                                                </select>
                                                </div>
                                                
                                                </div>
                                                <div class="row">
                                                	<div class="col-md-12 m-t-5">
                                                    <button type="button" class="btn btn-danger waves-effect waves-light btn-xs makeANA" data-dismiss="modal" aria-hidden="true">Make Guest UnAvailable</button>
                                                    <button type="button" class="btn btn-warning waves-effect waves-light btn-xs" id="guest-unallocate" data-dismiss="modal">Un-Allocate</button>
                                                    <a href="#" id="guestA-link" class="btn btn-danger waves-effect waves-light btn-xs">Guests</a>
                                                    <a href="#" id="share-link" data-toggle="modal" data-target=".choose-share" class="btn btn-warning waves-effect waves-light btn-xs">Share Guest</a>
                                                	</div>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->


                                <!-- Model for outside worker starts -->
                                <div class="modal fade choose-enterprise-ow" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodal-ow">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                	<div class="col-md-12">What will you provide at the leave time?</div>
                                            	</div>
                                            	<div class="row">
                                                	<div class="col-md-12" style="font-size: 12px;">
                                                		<div class="radio radio-info radio-inline">
	                                                        <input type="radio" id="PickedUp" value="Picked Up" name="leave_time_provide">
	                                                        <label for="PickedUp"> Picked Up </label>
	                                                    </div>
	                                                    <div class="radio radio-info radio-inline">
	                                                        <input type="radio" id="DroppedOff" value="Dropped Off" name="leave_time_provide">
	                                                        <label for="DroppedOff"> Dropped Off </label>
	                                                    </div>
                                                	</div>
                                            	</div>
                                            	<div class="row"><div class="col-md-12">&nbsp;</div></div>
                                            	<div class="row">
                                                <div class="col-md-12">
                                                <select class="form-control" name="enterpriseID_ow" id="enterpriseID_ow" style="font-size:12px;">
                                                    <option value="">Select Enterprise</option>
                                                    <?php
													
													if(isset($hostID))
													{
														$ent_q = "SELECT e.* , emp.* FROM enterprise e ,employers emp WHERE e.`empID` = emp.`empID` AND e.hostID = '".$hostID."' AND e.status = '1' ORDER BY TIME(STR_TO_DATE(e.leave_time, '%l:%i %p')) ASC";
														
														if( $db->num_rows( $ent_q ) > 0 )
														{
															$entr = $db->get_results( $ent_q );
															foreach( $entr as $row )
															{
																$n_w_r = $row['num_workers_required'];
																$show = true;
																if(count($wlj) > 0)
																{
																	$cnt = 0;
																	foreach($wlj as $val)
																	{
																		$nvl = explode("_",$val);
																		$eID = $nvl[0];
																		$gID = $nvl[1];
																		if($eID == $row['enterpriseID'])
																			$cnt++;
																	}
																	if($cnt == $n_w_r)
																		$show = false;
																}
															if($show){	
                                                    ?>
                                                            <option value="<?php echo $row['enterpriseID']; ?>" data-dismiss="modal"><?php echo getEmployerName($row['empID']); ?>&nbsp;&nbsp;<?php echo getActivityName($row['activityID']); ?>&nbsp;&nbsp;<?php echo $row['leave_time']." / ".$row['start_time']; ?></option>
                                                    <?php 
															}
                                                        	}
                                                    	}
													}
                                                    ?>	
                                                    	
                                                </select>
                                                </div>
                                                </div>
                                                
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->
                                
                                
                                <!-- Model for guest NOT available starts -->
                                <div class="modal fade guest-not" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodalNA">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                	<div class="col-md-12">
                                                    <button type="button" class="btn btn-primary waves-effect waves-light btn-xs makeANA" data-dismiss="modal" aria-hidden="true">Make Guest Available</button>
                                                    <a href="#" id="guestNA-link" class="btn btn-danger waves-effect waves-light btn-xs">Guests</a>
                                                	</div>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->

                                <!-- Model for share guest starts -->
                        <div class="modal fade choose-share" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodalShare">
                            <div class="modal-dialog modal-sm">
                            	
	                                <div class="modal-content">
	                                    <div class="modal-body">
	                                    	<div class="row">
		                                        <div class="col-md-9">
		                                        <select class="form-control" name="toHostID" id="toHostID" style="font-size:12px;">
		                                            <option value="">Select Hostel</option>
		                                            
		                                            <?php
	                                                // code to get myhostel's hostel listing
	                                                $query = "SELECT * FROM hostel Where status = '1' AND isDelete = 'No' AND isOutside = 'No' AND hostID != '".$hostID."' order by `hostel_name` ASC";

	                                                if( $db->num_rows( $query ) > 0 )
	                                                {
	                                                    $results = $db->get_results( $query );
	                                                    foreach( $results as $row )
	                                                    {
	                                                ?>
	                                                        <option value="<?php echo $row['hostID']; ?>"><?php echo $row['hostel_name']; ?></option>
	                                                <?php 
	                                                    }
	                                                }
	                                                ?>
		                                                                                        	
		                                        </select>
		                                        </div>
		                                        <div class="col-md-2">
		                                        	<button type="button" class="btn btn-primary waves-effect waves-light shareIT">Share</button>
		                                        </div>
	                                        </div>
	                                        
	                                    </div>
	                                </div><!-- /.modal-content -->
                            	
                            </div><!-- /.modal-dialog -->
                        </div><!-- /.modal -->
                        <!-- Model for share guest ends -->
                            	
                            <?php } ?>
                                <input type="hidden" name="guestID" id="guestID" value="">
                                
                                <div class="panel panel-default">
                                    
                                    <div class="panel-body">
                                    	<div class="row">
                                        	<div class="col-md-5">
                                            	&nbsp;
                                            </div>
                                            <div class="col-md-7" style="text-align:right;">
                                            	<?php
	                                            if($isUpdate)
	                                            {
	                                            ?>
                                            	<button type="submit" class="btn btn-primary btn-rounded waves-effect waves-light m-b-5"><?php echo $LANG['txtSave']; ?></button>
                                            <?php } ?>
                                            </div>
                                        </div>
                                    	<div class="col-md-5" style="margin-top:-20px;">
                                        	<div class="row">
                                        	<label>Guests available for work</label>
                                            <div class="panel panel-default panel-border">
                                                <div class="panel-body"> 
                                                    <div class="row">
                                                        
                                                        <div style="max-height:200px;overflow:scroll;">
                                                        <table id="guest_available_table" class="table table-hover" style="font-size:12px;" border="1">
                                                            <tr>
                                                            	<th style="font-size:12px;">Name</th>
                                                                <th style="font-size:12px;">Lic</th>
                                                                <th style="font-size:12px;">Activity</th>
                                                                <th style="font-size:12px;">Notes</th>
                                                                <th style="font-size:12px;">Last Work</th>
                                                            </tr>
                                                            <tbody id="guest_available_tbody">
                                                                <?php
                                                                if(isset($hostID))
                                                                {
                                                                	if($guest_order == 'CID_ASC')
                                                                		$gorder = "ORDER BY sign_in_date ASC";
                                                                	else
                                                                		$gorder = "ORDER BY sign_in_date DESC";
                                                                    $query = "SELECT * FROM guest Where hostID = '".$hostID."' AND sign_out_date = '0000-00-00' AND isOutside = 'No' AND isShared != '2' AND available_to_work = '1' ".$gorder;
                                                                    if( $db->num_rows( $query ) > 0 )
                                                                    {
                                                                        $results = $db->get_results( $query );
                                                                        foreach( $results as $row )
                                                                        {
																			$licence = "";
																			if($row['gender'] == 'Male')
																				$bgstyle = "background-color:#C0FFFF;";
																			else if($row['gender'] == 'Female')
																				$bgstyle = "background-color:#FFC0FF;";
																			
																				if(!in_array($row['guestID'],$guestIDArray))
																			{
																				if($row['drivers_licence'] == 1)
																					$licence = "LIC";
																					
																				$activityName = '';
																				$activityID = getCurrActivity($row['guestID']);
																				if($activityID != '')
																					$activityName = getActivityName($activityID);
																				
																				// get notes strike	
																				$strike = getNotesStrike($row['notes']);

																				// code for bday icon
																				$bday_icon = getBdayIcon($row['date_of_birth']);

																				// code for wont work sat & sun
																				$sat_status = true;
																				$sun_status = true;
																				//$tday = date('D');
																				$tday = date('D', strtotime('+1 Days'));
																				if($tday == 'Sat' && $row['wont_work_sat'] == '1')
																					$sat_status = false;
																				if($tday == 'Sun' && $row['wont_work_sun'] == '1')
																					$sun_status = false;

																				if($sat_status && $sun_status)
																				{
																					$showFin = true;

																					if($row['unavailable_dates'] != '')
																					{
																						$unavailable_dates = explode(",",$row['unavailable_dates']);

																						$cwdate = DateFromDB($wdate);
																						if(in_array($cwdate, $unavailable_dates))
																						{
																							$showFin = false;
																						}
																					}
																					if($showFin)
																					{
																				
                                                                ?>
                                                                <tr id="<?php echo $row['guestID']; ?>" class="guestRow guestRowAll" data-toggle="modal" data-target=".choose-enterprise">
                                                                    <td style="<?php echo $bgstyle; ?>">
																	<?php echo $row['first_name']." ".$row['last_name']." [".$row['room']."]"; ?><?php echo $bday_icon; ?>
                                                                    </td>
                                                                    <td><?php echo $licence; ?></td>
                                                                    <td class="HLCls"><?php echo getEmpNameByEntID($row['curr_activity_id']).' '.$activityName;?></td>
                                                                    <td><?php echo $strike; ?></td>
                                                                    <td><?php echo DateFromDB($row['last_work_date']); ?></td>
                                                                </tr>
                                                                <?php 
                                                            						}
																				}
																			}
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                                
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div> 
                                                </div> 
                                            </div>
                                            </div>
                                            
                                            <div class="row m-t-5">
                                        	<label>Guests not available for work</label>
                                            <div class="panel panel-default panel-border">
                                                <div class="panel-body"> 
                                                    <div class="row">
                                                        
                                                        <div style="max-height:150px;overflow:scroll;">
                                                        <table id="guest_not_available_table" class="table table-hover" style="font-size:12px;" border="1">
                                                            <tr>
                                                            	<th style="font-size:12px;">Name</th>
                                                                <th style="font-size:12px;">Lic</th>
                                                                <th style="font-size:12px;">Activity</th>
                                                                <th style="font-size:12px;">&nbsp;</th>
                                                                <th style="font-size:12px;">Last Work</th>
                                                            </tr>
                                                            <tbody id="guest_not_available_tbody">
                                                                <?php
                                                                if(isset($hostID))
                                                                {
                                                                    $query = "SELECT * FROM guest Where hostID = '".$hostID."' AND sign_out_date = '0000-00-00' AND isOutside = 'No' AND isShared != '2' AND available_to_work = '0' ORDER BY createdOn DESC";
                                                                    if( $db->num_rows( $query ) > 0 )
                                                                    {
                                                                        $results = $db->get_results( $query );
                                                                        foreach( $results as $row )
                                                                        {
																			$licence = '';
																			if($row['gender'] == 'Male')
																				$bgstyle1 = "background-color:#C0FFFF;";
																			else if($row['gender'] == 'Female')
																				$bgstyle1 = "background-color:#FFC0FF;";
																				
																			if($row['drivers_licence'] == 1)
																					$licence = "LIC";
																					
																			// worklist strike code starts	
																			$strike = '';
																			$pos3 = strpos($row['notes'], "y:");
																			if ($pos3 !== false)
																				$strike .= '<span style="color:#060;">&#10004;</span>';
																			$pos1 = strpos($row['notes'], "X:");
																			if ($pos1 !== false)
																				$strike .= '<span style="color:#F00;">X</span>';
																			$pos2 = strpos($row['notes'], "x:");
																			if ($pos2 !== false)
																				$strike .= '<span style="color:#F00;">x</span>';
																			// worklist strike code ends

																			// code for bday icon
																			$bday_icon = getBdayIcon($row['date_of_birth']);
                                                                ?>
                                                                <tr id="<?php echo $row['guestID']; ?>" class="guestRowNA guestRowAll" data-toggle="modal" data-target=".guest-not">
                                                                    <td style="<?php echo $bgstyle1; ?>"><?php echo $row['first_name']." ".$row['last_name']." [".$row['room']."]"; ?><?php echo $bday_icon; ?></td>
                                                                    <td><?php echo $licence; ?></td>
                                                                    <td>
                                                                    <?php echo getEmpNameByEntID($row['curr_activity_id']); 
																		$activityID = getCurrActivity($row['guestID']);
																		if($activityID != '')
																			echo getActivityName($activityID);
																	?>
                                                                    </td>
                                                                    <td><?php echo $strike; ?></td>
                                                                    <td><?php echo DateFromDB($row['last_work_date']); ?></td>
                                                                    
                                                                </tr>
                                                                <?php 
																		}
                                                                    }
                                                                }
                                                                ?>
                                                                
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div> 
                                                </div> 
                                            </div>
                                            </div>

                                            <!-- Outsied workers section starts -->
                                            <div class="row m-t-5" id="showHideOW">
                                        	<label>Outside workers</label>
                                            <div class="panel panel-default panel-border">
                                                <div class="panel-body"> 
                                                    <div class="row">
                                                        
                                                        <div style="max-height:150px;overflow:scroll;">
                                                        <table id="outside_worker_table" class="table table-hover" style="font-size:12px;" border="1">
                                                            <tr>
                                                            	<th style="font-size:12px;" colspan="2">Name</th>
                                                                <th style="font-size:12px;">Hostel</th>
                                                                <th style="font-size:12px;">Lic</th>
                                                                <th style="font-size:12px;">Activity</th>
                                                            </tr>
                                                            <tbody id="outside_worker_table_tbody">
                                                                <?php
                                                                if(isset($hostID))
                                                                {
                                                                    $query = "SELECT * FROM guest Where status = '1' AND ((hostID = '".$hostID."' AND isOutside = 'Yes') OR (isShared = '2' AND outsideHostID = '".$hostID."')) ORDER BY first_name ASC";

                                                                    if( $db->num_rows( $query ) > 0 )
                                                                    {
                                                                        $results = $db->get_results( $query );
                                                                        foreach( $results as $row )
                                                                        {
																			if(!in_array($row['guestID'],$guestIDArray))
																			{
																				$licence = '';
																				if($row['gender'] == 'Male')
																					$bgstyle1 = "background-color:#C0FFFF;";
																				else if($row['gender'] == 'Female')
																					$bgstyle1 = "background-color:#FFC0FF;";
																				
																				if($row['drivers_licence'] == 1)
																					$licence = "LIC";
																					
																				// code for bday icon
																				$bday_icon = getBdayIcon($row['date_of_birth']);

																				// get hostel name
			                                                                    if($row['isShared'] == '2')
																				   $hostelName = getHostNameByHostID($row['hostID']);
			                                                                    else
			                                                                        $hostelName = getHostNameByHostID($row['outsideHostID']);
                                                                ?>
                                                                <tr id="<?php echo $row['guestID']; ?>" class="guestRowOW guestRowAll" data-toggle="modal" data-target=".choose-enterprise-ow">
                                                                    <td style="<?php echo $bgstyle1; ?>" colspan="2"><?php echo $row['first_name']." ".$row['last_name']; ?><?php echo $bday_icon; ?></td>
                                                                    <td><?php echo $hostelName; ?></td>
                                                                    <td><?php echo $licence; ?></td>
                                                                    <td>
                                                                    <?php 
                                                                    if($row['curr_activity_id'] != "0")
                                                                    {
                                                                    	echo getEmpNameByEntID($row['curr_activity_id']); 
																		$activityID = getCurrActivity($row['guestID']);
																		if($activityID != '')
																			echo getActivityName($activityID);
																	}
																	?>
                                                                    </td>
                                                                    
                                                                </tr>
                                                                <?php 
                                                            				}
																		}
                                                                    }
                                                                }
                                                                ?>
                                                                
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div> 
                                                </div> 
                                            </div>
                                            </div>
                                            <!-- Outside workers section ends -->

                                            <div class="row m-t-5">

                                            	<?php
	                                            if($isUpdate)
	                                            {
	                                            ?>
											
											<!-- Model for all activities starts -->
                                <div class="modal fade all-activity-mode" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodalAct">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
													<div class="col-md-5">
														<label for="actBlockName">Block</label>
														<input class="form-control" type="text" name="actBlockName" id="actBlockName" value="" style="font-size:12px;" readonly>
													</div>
													<div class="col-md-2">
														<label for="actLeaveTime">Leave Time</label>
														<input class="form-control" type="text" name="actLeaveTime" id="actLeaveTime" value="" style="font-size:12px;">
													</div>
													<div class="col-md-2">
														<label for="actStartTime">Start Time</label>
														<input class="form-control" type="text" name="actStartTime" id="actStartTime" value="" style="font-size:12px;">
													</div>
													<div class="col-md-3">
														<label for="actNumOfWorkers">Number of workers</label>
														<input class="form-control" type="text" name="actNumOfWorkers" id="actNumOfWorkers" value="" style="font-size:12px;">
													</div>
													<input type="hidden" name="AllActID" id="AllActID" value="">
													<input type="hidden" name="allActEntID" id="allActEntID" value="">
													
                                                </div>
                                                <div class="row">
                                                	<div class="col-md-6 m-t-5">
														<button type="button" class="btn btn-primary waves-effect waves-light btn-xs" id="act-save-all" >Save</button>
														&nbsp;
														<button type="button" class="btn btn-warning waves-effect waves-light btn-xs"  data-dismiss="modal">Close</button>
                                                	</div>
													<div class="col-md-6 m-t-5" style="text-align:right;">
														<button type="button" class="btn btn-danger waves-effect waves-light btn-xs" id="changeAI-status" data-dismiss="modal">Change Active/InActive Status</button>
                                                	</div>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div><!-- /.modal -->

                            <?php } ?>

                                        	<label>All Activities</label>
                                            <div class="panel panel-default panel-border">
                                                <div class="panel-body"> 
                                                    <div class="row">
                                                        
                                                        <div style="max-height:200px;overflow:scroll;">
                                                        <table id="all_activities_table" class="table table-hover" style="font-size:12px;" border="1">
                                                            <tr>
                                                            	<th style="font-size:12px;">Employer</th>
                                                                <th style="font-size:12px;">Block</th>
                                                                <th style="font-size:12px;">Activity</th>
                                                                <th style="font-size:12px;">Time</th>
                                                                <th style="font-size:12px;">Commences</th>
                                                            </tr>
                                                            <tbody>
                                                                <?php
                                                                if(isset($hostID))
                                                                {
                                                                    $query = "SELECT * FROM enterprise Where hostID = '".$hostID."' ORDER BY status, TIME(STR_TO_DATE(leave_time, '%l:%i %p')) ASC";
				                                                    if( $db->num_rows( $query ) > 0 )
                                                                    {
                                                                        $results = $db->get_results( $query );
																		$rowar1 = array();
																		$rowar2 = array();
																		foreach( $results as $row )
                                                                        {
																			if(in_array($row['enterpriseID'],$enterpriseIDArray))
																				$rowar1[] = $row['enterpriseID'];
																			else
																				$rowar2[] = $row['enterpriseID'];
																		}
																		
																		// code to sort second array by employer name starts
																		$empAry = array();
																		$rowar2New = array();
																		foreach( $rowar2 as $rowar2_val )
                                                                        {
                                                                        	$query9 = "SELECT * FROM enterprise Where enterpriseID = '".$rowar2_val."'";
																			$res9 = $db->get_results( $query9 );
																			$res9_empName = getEmployerName($res9[0]['empID']);
																			$empAry[$rowar2_val] = $res9_empName;
                                                                        }
                                                                        asort($empAry);
                                                                        foreach( $empAry as $key => $vali )
                                                                        {
                                                                        	$rowar2New[] = $key;
                                                                        }
																		// code to sort second array by employer name ends


																		$rar = array_merge($rowar1,$rowar2New);
																		foreach( $rar as $val )
                                                                        {
																			$query = "SELECT * FROM enterprise Where enterpriseID = '".$val."'";
																			$res2 = $db->get_results( $query );
																			if($res2[0]['status'] == "1")
																			{
																				$bgstyle2 = "background-color:#C0FFC0;";
																				$wlstatus = "0";
																			}
																			else
																			{
																				$bgstyle2 = "";
																				$wlstatus = "1";
																			}
                                                                ?>
                                                                <tr id="ALL_<?php echo $res2[0]['enterpriseID']; ?>_<?php echo $wlstatus; ?>" style="<?php echo $bgstyle2; ?>" class="allRow" data-toggle="modal" data-target=".all-activity-mode">
                                                                    <td><?php echo getEmployerName($res2[0]['empID']); ?></td>
                                                                    <td class="all-block-name"><?php echo getBlockName($res2[0]['blockID']); ?></td>
                                                                    <td class="acttd"><?php echo getActivityName($res2[0]['activityID']); ?></td>
                                                                    <td class="all-start-time"><?php echo $res2[0]['start_time']; ?></td>
                                                                    <td><?php echo DateFromDB($res2[0]['commence_date']); ?></td>
                                                                </tr>
                                                                <?php 
																	    }
                                                                    }
                                                                }
                                                                ?>
                                                                
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div> 
                                                </div> 
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7" style="margin-top:-20px;">
                                
                                        	<?php
	                                            if($isUpdate)
	                                            {
	                                            ?>

                                <div class="modal fade dloptions" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="deathListReason">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                	<div class="col-md-12">
                                                	<textarea name="dlReason" id="dlReason" rows="3" cols="33"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row m-t-5">
                                                	<div class="col-md-12">
                                                	<button type="button" class="btn btn-primary waves-effect waves-light btn-xs" id="saveDLReason" data-dismiss="modal">Deathlist with Reason</button>
                                                    </div>                                                
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div>
                                            
                                            
                                      <div class="modal fade worklist-options" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="mymodalWlist">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                <div class="col-md-12">
                                                <button type="button" class="btn btn-danger waves-effect waves-light btn-xs" id="deathlist" data-toggle="modal" data-target=".dloptions">Death List</button>
                                                <button type="button" class="btn btn-warning waves-effect waves-light btn-xs" id="unallocate" data-dismiss="modal">Un-Allocate</button>
                                                <button type="button" class="btn btn-danger waves-effect waves-light btn-xs remwlist" id="remove" data-dismiss="modal">Remove</button>
                                                <button type="button" class="btn btn-primary waves-effect waves-light btn-xs" id="sick" data-dismiss="modal">Sick</button>
                                                <button type="button" class="btn btn-primary waves-effect waves-light btn-xs" id="noshow" data-dismiss="modal">No Show</button>
                                                <a href="#" id="wl-guest-link" class="btn btn-danger waves-effect waves-light btn-xs">Guests</a>
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                </div>
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div>
                                <!-- Modal for Worklist notes starts -->
                                <div class="modal fade workListModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" style="display: none;" id="workListNotes">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content">
                                            <div class="modal-body">
                                            	<div class="row">
                                                	<div class="col-md-12">
                                                	<textarea name="worklist_notes" id="worklist_notes" rows="3" cols="33"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row m-t-5">
                                                	<div class="col-md-12">
                                                	<button type="button" class="btn btn-primary waves-effect waves-light btn-xs" id="saveWLNotes" data-dismiss="modal">Save Notes</button>
                                                    </div>                                                
                                                </div>
                                            </div>
                                        </div><!-- /.modal-content -->
                                    </div><!-- /.modal-dialog -->
                                </div>
                                <!-- Modal for Worklist notes ends -->
                            <?php } ?>

                                        	<div class="row">
                                        	<label>Work List</label>
                                            <input type="hidden" name="WLJson" id="WLJson" value="<?php echo $WLJson; ?>">
                                            <input type="hidden" name="worklistID" id="worklistID" value="<?php echo $worklistID; ?>">
                                            <input type="hidden" name="e_g_id" id="e_g_id" value="">
                                            <input type="hidden" name="E_ID" id="E_ID" value="">
                                            
                                            <div class="panel panel-default panel-border" style="<?php echo $wlborstyle; ?>">
                                                <div class="panel-body"> 
                                                    <div class="row">
                                                        
                                                        <div id="wltableM" style="max-height:550px;overflow:scroll;">
                                                        <table id="worklist_table" class="table table-hover" style="font-size:12px;" border="1">
                                                            <tr style="background-color:#FF9999;">
                                                            	<th style="font-size:12px;">&nbsp;</th>
                                                            	<th style="font-size:12px;">Notes</th>
                                                                <th style="font-size:12px;">&nbsp;</th>
                                                                <th style="font-size:12px;">Last</th>
                                                                <th style="font-size:12px;">Hostel</th>
                                                            </tr>

                                                            <?php
                                                            $query = "SELECT * FROM guest Where status = '1' AND hostID = '".$hostID."' AND outsideHostID != '0' AND isShared = '2' ORDER BY first_name ASC";
                                                            if( $db->num_rows( $query ) > 0 )
															{
																$results = $db->get_results( $query );
																foreach( $results as $rowM )
																{
																	$showRA_String_M = '';
																	
																	// Get Hostel name
										                        	$showRA_String_M .= "<b>".getHostNameByHostID($rowM['outsideHostID'])."</b>&nbsp;&nbsp;";

										                        	// Get shared hostel start leave time
										                        	$query2 = "SELECT * FROM worklists_detail Where guestID = '".$rowM['guestID']."' ORDER BY wldID DESC";
										                        	if( $db->num_rows( $query2 ) > 0 )
										                        	{
										                        		$mresults = $db->get_results( $query2 );
										                        		$mWorklistID = $mresults[0]['worklistID'];
										                        		$mEnterpriseID = $mresults[0]['enterpriseID'];

										                        		$query3 = "SELECT * FROM worklists Where worklistID = '".$mWorklistID."'";
										                        		$m3results = $db->get_results( $query3 );

										                        		if($m3results[0]['hostID'] == $rowM['outsideHostID'])
										                        		{
										                        			$mEntQue = "SELECT * FROM enterprise Where enterpriseID = '".$mEnterpriseID."'";
										                        			$mEntR = $db->get_results( $mEntQue );
										                        			
										                        			$showRA_String_M .= getActivityName($mEntR[0]['activityID']);
										                        			if($rowM['leave_time_provide'] != 'No')
										                        			{
										                        				$showRA_String_M .= "&nbsp;&nbsp;".$rowM['leave_time_provide'];
										                        			}
										                        			$showRA_String_M .= "&nbsp;&nbsp;".$mEntR[0]['leave_time']." / ".$mEntR[0]['start_time'];
										                        			
										                        		}

										                        	}
                                                            ?>
                                                            <tr>
                                                            	<td colspan="5"><?php echo $rowM['first_name'].' '.$showRA_String_M; ?>&nbsp;<a class="btn btn-warning waves-effect waves-light btn-xs" href="worklist.php?sgid=<?php echo $rowM['guestID']; ?>&srID=<?php echo $rowM['shared_record_id']; ?>&ohID=<?php echo $rowM['outsideHostID']; ?>&act=getback" onclick="return confirm('Are you sure that the shared hostel has removed this guest from their worklist? If yes then proceed, otherwise cancel.');">Get Back</a></td>
                                                            </tr>
                                                        <?php } } ?>
                                                            <tbody id="worklist_table_tbody">
                                                                <?php
                                                                if(isset($hostID))
                                                                {
                                                                	// Get Worklist order from worklist settings
																	$worklist_order = getWorklistOrderByHostID($hostID);
																	
                                                                	if($worklist_order == "Alphabet")
                                                                	{
                                                                		$ent_query = "SELECT ent.* FROM `enterprise` as ent LEFT JOIN `employers` as emp ON ent.empID = emp.empID WHERE ent.hostID = '".$hostID."' AND ent.status = '1' ORDER BY emp.common_name ASC";
                                                                	}
                                                                	else
                                                                	{
                                                                		$ent_query = "SELECT * FROM enterprise Where hostID = '".$hostID."' AND status = '1' ORDER BY TIME(STR_TO_DATE(leave_time, '%l:%i %p')) ASC";
                                                                	}
																																
																	if( $db->num_rows( $ent_query ) > 0 )
																	{
																		$resty = $db->get_results( $ent_query );
																		foreach( $resty as $row )
																		{
																			$num = 0;

																			// code to know that worklst notes available or not
																			$worklist_nt = $db->clean($row['worklist_notes']);
																			$showWlNoti = '';
																			if($worklist_nt != '')
																				$showWlNoti = '<span style="color: red;">*</span>';

																			// code for vehicle dropdown starts
																			$vehicleSel = '';
																			
																			$getVDArray = getDriverVehicleID($hostID, $worklistID, $row['enterpriseID']);
																			
																			$v_query = "SELECT * FROM vehicles WHERE hostID = '".$hostID."'";
																			if( $db->num_rows( $v_query ) > 0 )
																			{
																				$vehicleSel = '<select class="workVehicle" name="work_vehicle" id="work_vehicle"><option value="">Select Vehicle</option>';

																				$vres = $db->get_results( $v_query );
																				foreach( $vres as $vehicle )
																				{
																					if($getVDArray['vehicleID'] == $vehicle['vehicleID'])
																						$vselect = "selected";
																					else
																						$vselect = "";
																					
																					$vehicleSel .= '<option value="'.$vehicle['vehicleID'].'" '.$vselect.'>'.$vehicle['vehicle_name'].'</option>';
																				}
																				$vehicleSel .= '</select>';
																			}
																			// code for vehicle dropdown ends
														?>
														
														<tr id="ep_<?php echo $row['enterpriseID'] ?>" style="background-color:#FF0 !important;font-weight:bold;" class="WLEntRows">
															<td style="background-color:#FF0 !important;font-weight:bold !important;"><?php echo getEmployerName($row['empID']); ?>&nbsp;&nbsp;<?php echo getActivityName($row['activityID']); ?>&nbsp;&nbsp;<?php echo getBlockName($row['blockID']); ?>&nbsp;&nbsp;<?php echo $row['leave_time']." / ".$row['start_time']; ?></td>
															<td style="text-align: right;"><?php echo $vehicleSel; ?></td>
															<td class="width82">
																<img src="images/email-icon.png" class="email_list" border="0" height="20" width="20" alt="Email" title="Email" />&nbsp;
																<img src="images/people-icon.png" class="add_people" border="0" height="20" width="20" alt="Auto Fill" title="Auto Fill" />&nbsp;
																<img src="images/worklist-icon.png" class="openWorkList" border="0" height="20" width="20" alt="Notes" title="Notes" id="WLN_<?php echo $row['enterpriseID']; ?>" data-toggle="modal" data-target=".workListModal" /><?php echo $showWlNoti; ?>
															</td>
															<td>&nbsp;</td>
															<td>&nbsp;</td>
                                                            <!--<td>&nbsp;</td>-->
														</tr>
														<?php 
														
														$inc = 0;
														// $wlj is an array format of Wljson
														if(count($wlj) > 0)
														{
															foreach($wlj as $val)
															{
																$nval = explode("_",$val);
																$licence = '';
																if($nval[0] == $row['enterpriseID'])
																{
																	$num++;
																	$bgstyle = '';
																	$query = "SELECT * FROM guest Where guestID = '".$nval[1]."'";
																	$rqu = $db->get_results( $query );
																	
																	if($rqu[0]['gender'] == 'Male')
																		$bgstyle = "background-color:#C0FFFF;";
																	else if($rqu[0]['gender'] == 'Female')
																		$bgstyle = "background-color:#FFC0FF;";
																		
																	if($rqu[0]['drivers_licence'] == 1)
																		$licence = "LIC";
																		
																	// get notes strike	
																	$strike = getNotesStrike($rqu[0]['notes']);

																	// code for bday icon
																	$bday_icon = getBdayIcon($rqu[0]['date_of_birth']);


																	// Wont work sat and sun code
																	$sat_sun = array();
																	$sat_sun_str = '';
																	if($rqu[0]['wont_work_sat'] == '1')
																		$sat_sun[] = "Sat";
																	if($rqu[0]['wont_work_sun'] == '1')
																		$sat_sun[] = "Sun";
																	if(count($sat_sun) > 0)
																	{
																		$sstring = implode(",",$sat_sun);
																		$sat_sun_str = '&nbsp;<span style="font-size:10px;color:red;">['.$sstring.']</span>';
																	}

																	// code to show outside hostel name in worklist starts
																	$outside_hostel = '';
																	$showRA = true;
																	if($rqu[0]['isShared'] == '2' || $rqu[0]['isOutside'] == 'Yes')
																	{
																		if($rqu[0]['isShared'] == '2')
																		   $outside_hostel = '['.getHostNameByHostID($rqu[0]['hostID']).']';
											                            elseif($rqu[0]['isOutside'] == 'Yes')
											                                $outside_hostel = '['.getHostNameByHostID($rqu[0]['outsideHostID']).']';

											                            $showRA = false;
											                        }
																	// code to show outside hostel name in worklist ends

											                        if($showRA)
											                        {
											                        	$activityID = getCurrActivity($rqu[0]['guestID']);
											                        	$showRA_String = " [".$rqu[0]['room']."]";
											                        	$showRA_String .= "[".getEmpNameByEntID($rqu[0]['curr_activity_id'])."&nbsp;";
											                        	if($activityID != '')
											                        		$showRA_String .= getActivityName($activityID);
											                        	$showRA_String .= "]";
											                        }
											                        else
											                        	$showRA_String = "";
																	
																	// code for vehicle driver
																	$isDriverName = "isDriver_".$row['enterpriseID'];
																	$isDriverID = "isDriver_".$row['enterpriseID']."_".$nval[1];
																	$isDriverClass = "isDriver";
																	
																	?>
																	<tr id="<?php echo $row['enterpriseID'].'_'.$nval[1]; ?>" class="wListRow" data-toggle="modal" data-target=".worklist-options">
                                                                        <td style="<?php echo $bgstyle; ?>">
                                                                        [<?php echo $num; ?>]&nbsp;<?php echo $rqu[0]['first_name']." ".$rqu[0]['last_name'].$showRA_String; ?>
                                                                        <?php echo $outside_hostel.$bday_icon.$sat_sun_str; ?>
                                                                        </td>
                                                                        <td style="text-align:right;<?php echo $bgstyle; ?>"><?php echo $strike; ?>
																&nbsp;<input type="radio" class="<?php echo $isDriverClass; ?>" name="<?php echo $isDriverName; ?>" id="<?php echo $isDriverID; ?>" value="<?php echo $nval[1]; ?>" <?php if($getVDArray['driverID'] == $nval[1]){ ?> checked <?php } ?> /><label for="<?php echo $isDriverID; ?>">&nbsp;Driver</label>
																		</td>
                                                                        <td><?php echo $licence; ?></td>
                                                                        <td>
																			<?php
																			if($rqu[0]['last_work_date'] != '0000-00-00')
																			{
																				$todate = date("Y-m-d");
																				$date1 = date_create($todate);
																				$date2 = date_create($rqu[0]['last_work_date']); 
																				$diff = date_diff($date1,$date2);
																				if( $diff->days <= 5 )
																				{
																					echo DateFromDB($rqu[0]['last_work_date']);	
																				}
																			}
																			?>
                                                                        </td>
                                                                        <td>&nbsp;</td>
                                                                        
                                                                    </tr>
                                                                    <?php
																	$inc++;
																		
																}
															}
														}
																		
																	$nwr = $row['num_workers_required'];
																	if($nwr != '' && $nwr > 0)
																	{
																		for($i=0;$i<$nwr-$inc;$i++)
																		{
																			$num++;
																			?>
                                                                            <tr id="<?php echo $row['enterpriseID']; ?>_<?php echo $i; ?>">
                                                                                <td>[<?php echo $num; ?>]</td>
                                                                                <td></td>
                                                                                <td></td>
                                                                                <td></td>
                                                                                <td>&nbsp;</td>
                                                                                <!--<td></td>-->
                                                                            </tr>
                                                                            <?php	
																		}
																	}
																		
																		}
																	}
                                                                        
                                                                }
                                                                ?>
                                                                
                                                            </tbody>
                                                        </table>
                                                        </div>
                                                    </div>
													
                                                </div> 
                                            </div>
											<div class="panel panel-default">
												<div class="panel-body">
													<div class="row">
														<textarea name="guest_notes" class="form-control" id="guest_notes" rows="4" readonly style="font-weight:bold;"></textarea>
													</div>
												</div>
											</div>
                                            </div>
                                            
                                        </div>
                                        
   										<div class="row">
                                        	<div class="form-group">
												<div class="col-sm-12 m-t-10" >
                                                	<div class="col-sm-6" style="text-align:left;">
                                                    	<a class="btn btn-default btn-rounded waves-effect waves-light m-b-5" href="oldworklist.php">Previous Work Lists</a>
                                                    </div>
                                                    <div class="col-sm-6" style="text-align:right;">
                                                	<input type="hidden" name="hostID" id="hostID" value="<?php echo $hostID; ?>">
                                                    <input type="hidden" name="saveEnt" id="saveEnt" value="1">
                                                    <a class="btn btn-default btn-rounded waves-effect waves-light m-b-5" href="emailworkers.php?wlid=<?php echo $worklistID; ?>&hid=<?php echo $hostID; ?>">Email Workers</a>
                                                    <a class="btn btn-default btn-rounded waves-effect waves-light m-b-5 SH_OW_Button" href="jvascript:;">Show/Hide Outside Workers</a>
                                                    <a class="btn btn-default btn-rounded waves-effect waves-light m-b-5" href="printemplist.php?wlid=<?php echo $worklistID; ?>">Print Employee List</a>
                                                    <a class="btn btn-default btn-rounded waves-effect waves-light m-b-5" href="cretapdf.php?wlid=<?php echo $worklistID; ?>">Print Work List</a>

                                                    <?php
		                                            if($isUpdate)
		                                            {
		                                            ?>
                                                    <a class="btn btn-default btn-rounded waves-effect waves-light m-b-5" href="enterprise.php">Enterprise</a>

                                                    <button type="submit" class="btn btn-primary btn-rounded waves-effect waves-light m-b-5"><?php echo $LANG['txtSave']; ?></button>
                                                <?php } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> <!-- panel-body -->
                                </div> <!-- panel -->
                                
                            </form>    

                            </div> <!-- col -->
                        </div>
                        

                    </div> <!-- container -->
                               
                </div> <!-- content -->

                <?php require_once('footer-copyright.php'); ?>

            </div>
            

        </div>
        <!-- END wrapper -->

		<?php require_once('footer-inner.php'); ?>
        
        <script src="assets/js/jquery.highlight.js"></script>
        
    <script>

    <?php
    if($isUpdate)
    {
    ?>
		<script src="assets/js/wl.js"></script>
	
	<?php } ?>

	$('#searchit').click(function(){
		$('#wltableM').removeHighlight().highlight($('#searchwl').val());

		$('#wltableM').scrollTop(0);
		var pos = $('.highlight').offset().top;
		var pos2 = $('#wltableM').offset().top + 2;
		var newpos = pos - pos2;
		$('#wltableM').animate({
			  scrollTop: newpos
			}, 1000);
		
	})
	

    </script>
    </body>
</html>