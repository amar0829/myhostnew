$(document).ready(function(e) {
		

    /* sick code starts */
    sickfunc = function(){
        var crid = $("#e_g_id").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();

        if(crid != '')
        {
            $.post("setwlist.php", {ent_gue_ID: crid, WLJson: WLJson, hostID: hostID, sick: "Yes", worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }	
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $('#guest_available_tbody').html(response.glist);
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".guestRow").bind("click",guestclick);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);

                        // set outside users list starts
                        if(response.owlist != '')
                        {
                            $('#outside_worker_table_tbody').html(response.owlist);
                        }
                        $(".guestRowOW").bind("click",guestclickOW);
                        // set outside users list ends
                        
                        $("#e_g_id").val("");
                    }
                });
        }
        
        }
        
    $("#sick").click(sickfunc);
    /* sick code ends */
    
    /* no show code starts */
    noshowfunc = function(){
        var crid = $("#e_g_id").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();

        if(crid != '')
        {
            $.post("setwlist.php", {ent_gue_ID: crid, WLJson: WLJson, hostID: hostID, noshow: "Yes", worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }	
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $('#guest_available_tbody').html(response.glist);
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".guestRow").bind("click",guestclick);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);

                        // set outside users list starts
                        if(response.owlist != '')
                        {
                            $('#outside_worker_table_tbody').html(response.owlist);
                        }
                        $(".guestRowOW").bind("click",guestclickOW);
                        // set outside users list ends
                        
                        $("#e_g_id").val("");
                    }
                });
        }
        
        }
        
    $("#noshow").click(noshowfunc);
    /* no show code ends */
    
    
    /* all activity status code starts */
    
    allrowact = function(e){
        
        var egid = $(this).attr("id");
         $("#AllActID").val(egid);
        
        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;

        $('#mymodalAct').on('show.bs.modal', function () {
            $('#mymodalAct').css("top", yw);
            $('#mymodalAct').css("left", xw);
        });
        
        
        if(egid != '')
        {
            $.post("getent.php", {allEntID: egid}, function(result){

                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#allActEntID').val(response.enterpriseID);
                        $("#actBlockName").val(response.blockName);
                        $("#actLeaveTime").val(response.leave_time);
                        $("#actStartTime").val(response.start_time);
                        $("#actNumOfWorkers").val(response.num_workers_required);
                    }
                });

        }
        
        
        }
        
    $(".allRow").click(allrowact);
    
    $("#changeAI-status").click(function(){
        
        var egid = $("#AllActID").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        if(egid != '')
        {
            $.post("wlsetto.php", {allact: egid, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        location.href = "worklist.php";
                    }
                });
        }

    });



    actSaveAll = function(e){
        
        var egid = $("#allActEntID").val();

        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        var actLeaveTime = $("#actLeaveTime").val();
        var actStartTime = $("#actStartTime").val();
        var actNumOfWorkers = $("#actNumOfWorkers").val();
        $("#"+egid+" .all-start-time").html(actStartTime);
        
        if(egid != '')
        {
            $.post("wlsetto.php", {allStartLeaveEnt: egid, WLJson: WLJson, hostID: hostID, worklistID: worklistID, actStartTime: actStartTime, actLeaveTime: actLeaveTime, actNumOfWorkers: actNumOfWorkers}, function(result){
                    if(result != 0)
                    {
                        location.href = "worklist.php";
                    }
                });
        }
        
        
        }
        
    $("#act-save-all").click(actSaveAll);
    
    
    /* all activity status code ends */
    
    
    /* unallocate code starts */
    
    unallocate = function(){
        
        var egid = $("#e_g_id").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        if(egid != '')
        {
            $.post("wlsetto.php", {unallocate: egid, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }	
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $('#guest_available_tbody').html(response.glist);
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".guestRow").bind("click",guestclick);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);

                        // set outside users list starts
                        if(response.owlist != '')
                        {
                            $('#outside_worker_table_tbody').html(response.owlist);
                        }
                        $(".guestRowOW").bind("click",guestclickOW);
                        // set outside users list ends
                        
                        $("#e_g_id").val("");
                    }
                });
        }
        
        }
        
    $("#unallocate").click(unallocate);
    /* Unallocate code ends */
    
    
    /* unallocate from guest available to code starts */
    
    guestunallocate = function(){
        
        var gid = $("#guestID").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        if(gid != '')
        {
            $.post("wlsetto.php", {guest_unallocate: gid, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#guest_available_tbody').html(response.glist);
                        $(".guestRow").bind("click",guestclick);
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);
                    }
                });
        }
        
        }
        
    $("#guest-unallocate").click(guestunallocate);
    /* Unallocate from guest available to code ends */
    
    /* add to deathlist code starts */
    
    $("#saveDLReason").click(function(e) {
        var dlReason = $("#dlReason").val();
        
        var egid = $("#e_g_id").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        if(egid != '')
        {
            $.post("wlsetto.php", {deathlist: egid, WLJson: WLJson, hostID: hostID, dlReason: dlReason, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }	
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $('#guest_available_tbody').html(response.glist);
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".guestRow").bind("click",guestclick);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);

                        // set outside users list starts
                        if(response.owlist != '')
                        {
                            $('#outside_worker_table_tbody').html(response.owlist);
                        }
                        $(".guestRowOW").bind("click",guestclickOW);
                        // set outside users list ends
                        
                        $("#e_g_id").val("");
                    }
                });
        }
        
    });
    
    addtodeathlist = function(e){
        
        $('#mymodalWlist').modal('hide');
            
        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        $('#deathListReason').on('show.bs.modal', function () {
            $('#deathListReason').css("top", yw);
            $('#deathListReason').css("left", xw);
        });
        $('#deathListReason').modal('show');
        return false;
        
        }
        
    $("#deathlist").click(addtodeathlist);
    /* add to deathlist code ends */
    
    removewlist = function(){
        
        var crid = $("#e_g_id").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        if(crid != '')
        {
            $.post("setwlist.php", {ent_gue_ID: crid, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $('#guest_available_tbody').html(response.glist);
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".guestRow").bind("click",guestclick);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);

                        // set outside users list starts
                        if(response.owlist != '')
                        {
                            $('#outside_worker_table_tbody').html(response.owlist);
                        }
                        $(".guestRowOW").bind("click",guestclickOW);
                        // set outside users list ends
                        
                        $("#e_g_id").val("");
                    }
                });
        }
        
        }
    
    $(".remwlist").click(removewlist);
    
    guestclick = function(e) {
        var gid = $(this).attr("id");
        $("#guestID").val(gid);
        $("#enterpriseID").val("");

        // Set href link for guest button starts
        var hrefGA = "updateguest.php?id="+gid+"&sto=GU_"+gid;
        $("#guestA-link").attr("href",hrefGA);
        // Set href link for guest button ends

        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        $('#mymodal').on('show.bs.modal', function () {
            $('#mymodal').css("top", yw);
            $('#mymodal').css("left", xw);
        });
        
    }
    
    $(".guestRow").click(guestclick);
    
    guestnaclick = function(e) {
        var gid = $(this).attr("id");
        $("#guestID").val(gid);

        // Set href link for guest button starts
        var hrefGA = "updateguest.php?id="+gid+"&sto=GU_"+gid;
        $("#guestNA-link").attr("href",hrefGA);
        // Set href link for guest button ends

        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        $('#mymodalNA').on('show.bs.modal', function () {
            $('#mymodalNA').css("top", yw);
            $('#mymodalNA').css("left", xw);
        });
        
    }
    
    $(".guestRowNA").click(guestnaclick);

    // for outside worker
    guestclickOW = function(e) {
        var gid = $(this).attr("id");
        $("#guestID").val(gid);
        $("#enterpriseID").val("");

        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        $('#mymodal-ow').on('show.bs.modal', function () {
            $('#mymodal-ow').css("top", yw);
            $('#mymodal-ow').css("left", xw);
        });

    }
    
    $(".guestRowOW").click(guestclickOW);


    // code for share guest model
    shareLinkClick = function(e) {
       
        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        $('#mymodalShare').on('show.bs.modal', function () {
            $('#mymodalShare').css("top", yw);
            $('#mymodalShare').css("left", xw);
        });
        
    }

    $("#share-link").click(shareLinkClick);

    
    wlistclick = function(e) {
        var thisid = $(this).attr("id");
        $("#e_g_id").val(thisid);

        // Set href link for guest button starts
        var thisidAr = thisid.split("_");
        var gid = thisidAr[1];
        var hrefGA = "updateguest.php?id="+gid+"&sto=GU_"+gid;
        $("#wl-guest-link").attr("href",hrefGA);
        // Set href link for guest button ends

        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;

        $('#mymodalWlist').on('show.bs.modal', function () {
            $('#mymodalWlist').css("top", yw);
            $('#mymodalWlist').css("left", xw);
        });
        
    }
    
    $(".wListRow").click(wlistclick);
    
    guesthover = function(e){
        var guestID = $(this).attr("id");
        if(guestID != '')
        {
            $.post("getgnotes.php", {guestID: guestID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#guest_notes').val(response.notes);
                    }
                });
        }
        
    }
    
    guesthoverout = function(e){
        $('#guest_notes').val("");
        
    }
    
    $(".guestRowAll").hover(guesthover,guesthoverout);

    
    // Code for worklist hover notes
    wlguesthover = function(e){
        var eguestID = $(this).attr("id");
        var egamrID = eguestID.split("_");

        var guestID = egamrID[1];
        if(guestID != '')
        {
            $.post("getgnotes.php", {guestID: guestID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#guest_notes').val(response.notes);
                    }
                });
        }
        
    }
    
    wlguesthoverout = function(e){
        $('#guest_notes').val("");
        
    }

    $(".wListRow").hover(wlguesthover,wlguesthoverout);
    
    /* Make available / unavailable code starts */
    makeANA = function(){

        var gid = $("#guestID").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();
        
        if(gid != '')
        {
            $.post("wlsetto.php", {makeana: gid, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#guest_available_tbody').html(response.glist);
                        $('#guest_not_available_tbody').html(response.glistNA);
                        $(".guestRow").bind("click",guestclick);
                        $(".guestRowNA").bind("click",guestnaclick);
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);
                    }
                });
        }
        
        }
    
    $(".makeANA").click(makeANA);



    /* share guest code starts */
    shareGuestFunc = function(){

        var gid = $("#guestID").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var toHostID = $("#toHostID").val();
        var worklistID = $("#worklistID").val();

        if(toHostID == '')
        {
            alert("Please select hostel.");
            return false;
        }
                    
        if(gid != '')
        {
            $.post("wlsetto.php", {share_guest: gid, WLJson: WLJson, hostID: hostID, toHostID: toHostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#guest_available_tbody').html(response.glist);
                        $('#guest_not_available_tbody').html(response.glistNA);
                        $(".guestRow").bind("click",guestclick);
                        $(".guestRowNA").bind("click",guestnaclick);
                        $(".guestRowAll").bind("mouseenter",guesthover);
                        $(".guestRowAll").bind("mouseleave",guesthoverout);

                        $('#mymodalShare').modal('hide');
                        $('#mymodal').modal('hide');
                        location.reload();
                    }
                });
        }
        
        }
    
    $(".shareIT").click(shareGuestFunc);


    
    // Code to open and save worklist notes start

    $("#saveWLNotes").click(function(e) {
        var worklist_notes = $("#worklist_notes").val();
        
        var E_ID = $("#E_ID").val();
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var worklistID = $("#worklistID").val();

        if(E_ID != '')
        {
            $.post("wlsetto.php", {saveWList: E_ID, WLJson: WLJson, hostID: hostID, worklist_notes: worklist_notes, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        $(".remwlist").bind("click",removewlist);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);
                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);
                        
                        $("#E_ID").val("");
                        $("#worklist_notes").val("");
                    }
                });
        }
        
    });


    // Code to send worker lists to employer on click of email button
    emailListClick = function(e) {
        
        var WLJson = $("#WLJson").val();
        var worklistID = $("#worklistID").val();
        var hostID = $("#hostID").val();
        var EP_ID = $(this).closest("tr").attr("id");
        
        if(EP_ID != '')
        {
            $.post("wlsetto.php", {emailList: EP_ID, WLJson: WLJson, hostID: hostID, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        alert(result);
                        return false;
                    }
                });
        }
        
    };

    $(".email_list").click(emailListClick);


    openWorkListClick = function(e) {
        var thisid = $(this).attr("id");
        
        $("#E_ID").val(thisid);

        var eamrID = thisid.split("_");

        var E_ID = eamrID[1];
        if(E_ID != '')
        {
            $.post("getwlnotes.php", {E_ID: E_ID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        $('#worklist_notes').val(response.worklist_notes);
                    }
                });
        }


        var stopc = window.pageYOffset;
        var xw = e.pageX;
        var yw = e.pageY;
        yw = yw - stopc;
        
        $('#workListNotes').on('show.bs.modal', function () {
            $('#workListNotes').css("top", yw);
            $('#workListNotes').css("left", xw);
        });
        
    }
    
    $(".openWorkList").click(openWorkListClick);
    // Code to open and save worklist notes end

    
    $("#enterpriseID").change(function(e) {
        var guestID = $("#guestID").val();
        var hostID = $("#hostID").val();
        var enterpriseID = $("#enterpriseID").val();
        var WLJson = $("#WLJson").val();
        var worklistID = $("#worklistID").val();
        var goit = true;
        
        if(enterpriseID != '')
        {
            
            // code to check min three months start
            $.post("chkminthree.php", {guestID: guestID, enterpriseID: enterpriseID}, function(res){
                    if(res != 1)
                    {
                        var conf = confirm("This person will check out before the minimum 3 months required for this job. Are you sure you want to allocate them here?");
                        if(conf)
                        {
                            $.post("setwlist.php", {guestID: guestID, enterpriseID: enterpriseID, hostID: hostID, WLJson: WLJson, worklistID: worklistID}, function(result){
                            if(result != 0)
                            {
                                var response = JSON.parse(result);
                                if(response.entsel != '')
                                {
                                    $('#enterpriseID').html(response.entsel);
                                    $('#enterpriseID_ow').html(response.entsel);	
                                }
                                $('#WLJson').val(response.wson);
                                $('#worklist_table_tbody').html(response.wlist);
                                $('#'+guestID).remove();
                                
                                $(".remwlist").bind("click",removewlist);
                                $(".wListRow").bind("click",wlistclick);
                                $(".WLEntRows").bind("click",WLEntRowsFunc);

                                $(".wListRow").bind("mouseenter",wlguesthover);
                                $(".wListRow").bind("mouseleave",wlguesthoverout);
                                $(".openWorkList").bind("click",openWorkListClick);
                                $(".email_list").bind("click",emailListClick);
                                $(".add_people").bind("click",addPeopleClick);
                                
                                
                            }
                            $('#mymodal').modal('hide');
                        });
                        }
                        else
                            $('#mymodal').modal('hide');
                    }
                    else
                    {
                    //alert(res);
                        $.post("setwlist.php", {guestID: guestID, enterpriseID: enterpriseID, hostID: hostID, WLJson: WLJson, worklistID: worklistID}, function(result){
                            if(result != 0)
                            {
                                var response = JSON.parse(result);
                                if(response.entsel != '')
                                {
                                    $('#enterpriseID').html(response.entsel);
                                    $('#enterpriseID_ow').html(response.entsel);	
                                }	
                                $('#WLJson').val(response.wson);
                                $('#worklist_table_tbody').html(response.wlist);
                                $('#'+guestID).remove();
                                
                                $(".remwlist").bind("click",removewlist);
                                $(".wListRow").bind("click",wlistclick);
                                $(".WLEntRows").bind("click",WLEntRowsFunc);

                                $(".wListRow").bind("mouseenter",wlguesthover);
                                $(".wListRow").bind("mouseleave",wlguesthoverout);
                                $(".openWorkList").bind("click",openWorkListClick);
                                $(".email_list").bind("click",emailListClick);
                                $(".add_people").bind("click",addPeopleClick);

                                
                            }
                            $('#mymodal').modal('hide');
                        });
                                    
                    }

                });	
            
            
        }
        
    });


    $("#enterpriseID_ow").change(function(e) {
        var guestID = $("#guestID").val();
        var hostID = $("#hostID").val();
        var enterpriseID = $("#enterpriseID_ow").val();
        var WLJson = $("#WLJson").val();
        var worklistID = $("#worklistID").val();
        var goit = true;
        var leave_time_provide = $("input[name='leave_time_provide']:checked"). val();

        if(enterpriseID != '')
        {
            $.post("setwlist.php", {guestID: guestID, enterpriseID: enterpriseID, hostID: hostID, WLJson: WLJson, worklistID: worklistID}, function(result){
                if(result != 0)
                {
                    var response = JSON.parse(result);
                    if(response.entsel != '')
                    {
                        $('#enterpriseID').html(response.entsel);
                        $('#enterpriseID_ow').html(response.entsel);	
                    }
                    $('#WLJson').val(response.wson);
                    $('#worklist_table_tbody').html(response.wlist);
                    $('#'+guestID).remove();
                    
                    $(".remwlist").bind("click",removewlist);
                    $(".wListRow").bind("click",wlistclick);
                    $(".WLEntRows").bind("click",WLEntRowsFunc);

                    $(".wListRow").bind("mouseenter",wlguesthover);
                    $(".wListRow").bind("mouseleave",wlguesthoverout);
                    $(".openWorkList").bind("click",openWorkListClick);
                    $(".email_list").bind("click",emailListClick);
                    $(".add_people").bind("click",addPeopleClick);

                    if(leave_time_provide)
                    {
                        $.post("saveleavetime.php", {guestID: guestID, leave_time_provide: leave_time_provide}, function(res){ });
                    }
                    
                    
                }
                $('#mymodal-ow').modal('hide');
            });		
            
        }
        
    });

    addPeopleClick = function() {
        
        var WLJson = $("#WLJson").val();
        var hostID = $("#hostID").val();
        var EP_ID = $(this).closest("tr").attr("id");
        var worklistID = $("#worklistID").val();
        
        if(EP_ID != '')
        {
            
            var conf = confirm("It will load regular people of this farm and will replace existing ones so please use this only for blank farm.");
            if(conf)
            {

                $.post("setwlist.php", {EP_Auto: EP_ID, hostID: hostID, WLJson: WLJson, worklistID: worklistID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        if(response.entsel != '')
                        {
                            $('#enterpriseID').html(response.entsel);
                            $('#enterpriseID_ow').html(response.entsel);	
                        }	
                        $('#WLJson').val(response.wson);
                        $('#worklist_table_tbody').html(response.wlist);
                        
                        for (i = 0; i < response.guest_exlude.length; i++) {
                         
                          $('#'+response.guest_exlude[i]).remove();
                        }
                        
                        $(".remwlist").bind("click",removewlist);
                        $(".wListRow").bind("click",wlistclick);
                        $(".WLEntRows").bind("click",WLEntRowsFunc);

                        $(".wListRow").bind("mouseenter",wlguesthover);
                        $(".wListRow").bind("mouseleave",wlguesthoverout);
                        $(".openWorkList").bind("click",openWorkListClick);
                        $(".email_list").bind("click",emailListClick);
                        $(".add_people").bind("click",addPeopleClick);
                    }
                })
            }
        
        }
    }

    $(".add_people").click(addPeopleClick);
    
    
    /* Highlight old workers */
    WLEntRowsFunc = function(){
        
        var epID = $(this).attr("id");
        var worklistID = $("#worklistID").val();
        var hostID = $("#hostID").val();
        
        var gid = $("#guestID").val();
        var WLJson = $("#WLJson").val();
        
        
        if(epID != '' && worklistID != '')
        {
            $.post("wlsetto.php", {hlight: epID, worklistID: worklistID, hostID: hostID}, function(result){
                    if(result != 0)
                    {
                        var response = JSON.parse(result);
                        var garr2 = response.garr;
                        var i = 0;
                        $(".guestRow .HLCls").css("background-color","");
                        for(i=0;i<garr2.length;i++)
                        {
                            $("#"+garr2[i]+" .HLCls").css("background-color","#FF6");		
                        }
                    }
                });
        }
        
        }
    
    $(".WLEntRows").click(WLEntRowsFunc);
    

    $(".SH_OW_Button").click(function () {
    
        var isShowHide = $("#showHideOW").css("display");
        if (isShowHide == 'block') {
            $("#showHideOW").css('display','none');
        } else {
            $("#showHideOW").css('display','block');
        }
    });
    
    
    // function to set the vehicle
    // it calls when we change vehicle dropdown in the worklist
    workVehicleFunc = function(e) {
        var worklistID = $("#worklistID").val();
        var hostID = $("#hostID").val();
        var enterpriseID = $(this).closest("tr").attr("id");
        var entrID = enterpriseID.split("_");
        var eID = entrID[1]; 
        var vehicleID = $(this).val();
        var assign_vehicle = "1";
        
        if(worklistID != '')
        {
            $.post("wlsetto.php", {worklistID: worklistID, enterpriseID: eID, hostID: hostID, vehicleID: vehicleID, assign_vehicle: assign_vehicle}, function(result){
                if(result != 0)
                {

                }
            });		
            
        }
        
    }
    
    $(".workVehicle").change(workVehicleFunc);
    
    // function to set the driver
    // calls on the change of driver radio button
    isDriverFunc = function(){
        
        setTimeout(function(){$('#mymodalWlist').modal('hide');}, 300);
        
        var worklistID = $("#worklistID").val();
        var hostID = $("#hostID").val();
        var enterpriseID = $(this).attr("id");
        var entrID = enterpriseID.split("_");
        var eID = entrID[1]; 
        var guestID = entrID[2];
        var assign_driver = "1";
        
        if(worklistID != '')
        {
            $.post("wlsetto.php", {worklistID: worklistID, enterpriseID: eID, hostID: hostID, driverID: guestID, assign_driver: assign_driver}, function(result){
                if(result != 0)
                {
                    //alert(result);
                }
            });		
            
        }			
    }
    $(".isDriver").change(isDriverFunc);
    
});
