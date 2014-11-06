/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    $(document).on('mouseover', '.tii_links_container .tii_tooltip', function() {
        $(this).tooltipster({ multiple: true });
        return false;
    });

    $(document).on('click', '.pp_origreport_open', function() {
        var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');
        var url = $(this).attr("id");

        for (var i = 0; i < classList.length; i++) {
            if (classList[i].indexOf('origreport_') !== -1 && classList[i] != 'pp_origreport_open') {
                var classStr = classList[i].split("_");
                openDV("origreport", classStr[1], classStr[2], url);
            }
        }
    });

    $(document).on('click', '.pp_grademark_open', function() {
        var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');
        var url = $(this).attr("id");

        for (var i = 0; i < classList.length; i++) {
            if (classList[i].indexOf('grademark_') !== -1 && classList[i] != 'pp_grademark_open') {
                var classStr = classList[i].split("_");
                openDV("grademark", classStr[1], classStr[2], url);
            }
        }
    });

    // Open an iframe light box containing the Peermark Manager
    if ($('.plagiarism_turnitin_peermark_manager_pp_launch').length > 0) {
        $('.plagiarism_turnitin_peermark_manager_pp_launch').colorbox({
            iframe:true, width:"802px", height:"772px", opacity: "0.7", className: "peermark_manager",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); },
            onClosed: function() {
                refreshPPPeermarkAssignments();
            }
        });
    }

    // Open an iframe light box containing the Peermark reviews
    $(document).on('click', '.peermark_reviews_pp_launch', function() {
        $('.peermark_reviews_pp_launch').colorbox({
            open:true,iframe:true, width:"802px", height:"772px", opacity: "0.7", className: "peermark_reviews",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); }
        });
        return false;
    });

    // Open an iframe light box containing the Rubric View
    $(document).on('click', '.rubric_view_pp_launch', function() {
        $(this).colorbox({
            open:true,iframe:true, width:"832px", height:"682px", opacity: "0.7", className: "rubric_view",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); }
        });
        return false;
    });

    $(document).on('click', '.pp_turnitin_eula_link', function() {
        $(this).colorbox({
            open:true,iframe:true, width:"766px", height:"596px", opacity: "0.7", className: "eula_view", scrolling: "false",
            onLoad: function() { getLoadingGif(); },
            onComplete: function() {
                $(window).on("message", function(ev) {
                    var message = typeof ev.data === 'undefined' ? ev.originalEvent.data : ev.data;
                    window.location.reload();
                });
            },
            onCleanup: function() { hideLoadingGif(); }
        });
        return false;
    });

    // Launch the Turnitin EULA
    if ($(".pp_turnitin_ula").length > 0) {
        if ($('.editsubmissionform').length > 0) {
            $('.editsubmissionform').hide();
        }
        if ($('.pp_turnitin_ula').siblings('.mform').length > 0) {
            $('.pp_turnitin_ula').siblings('.mform').hide();
        }
    }

    function getLoadingGif() {
        var img = '<div class="loading_gif"></div>';
        $('#cboxOverlay').after(img);
        var top = $(window).scrollTop() + ($(window).height() / 2);
        $('.loading_gif').css('top', top+'px');
    }

    function hideLoadingGif() {
        $('.loading_gif').remove();
    }

    // Refresh Peermark assignments stored locally for this module
    function refreshPPPeermarkAssignments() {
        $.ajax({
            type: "POST",
            url: "../plagiarism/turnitin/ajax.php",
            dataType: "json",
            data: {action: "refresh_peermark_assignments", cmid: $('input[name="coursemodule"]').val(), sesskey: M.cfg.sesskey},
            success: function(data) {}
        });
    }

    // Open the DV in a new window in such a way as to not be blocked by popups.
    function openDV(dvtype, submission_id, coursemoduleid, url) {
        var url = url+'&viewcontext=box&cmd='+dvtype+'&submissionid='+submission_id+'&sesskey='+M.cfg.sesskey;

        var dvWindow = window.open(url, 'dv_'+submission_id);
        var width = $(window).width();
        var height = $(window).height();
        dvWindow.document.write('<iframe id="dvWindow" name="dvWindow" width="'+width+'" height="'+height+'" sandbox="allow-same-origin allow-top-navigation allow-forms allow-scripts"></iframe>');
        dvWindow.document.write('<script>document.body.style = \'margin: 0 0;\';</script'+'>'); 
        dvWindow.document.getElementById('dvWindow').src = url;
        dvWindow.document.close();
        $(dvWindow).bind('beforeunload', function() {
            refreshScores(submission_id, coursemoduleid);
        });
        // Previous event does not work in Safari.
        $(dvWindow).bind('unload', function() {
            refreshScores(submission_id, coursemoduleid);
        });
    }

    function refreshScores(submission_id, coursemoduleid) {
        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            dataType: "json",
            data: {action: "update_grade", submission: submission_id, cmid: coursemoduleid, sesskey: M.cfg.sesskey},
            success: function(data) {
                eval(data);
                window.location = window.location;
            }
        });
    }

    // Update the DB value for EULA accepted
    function userAgreementAccepted( user_id ){
        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            dataType: "json",
            data: {action: 'acceptuseragreement', user_id: user_id},
            success: function(data) {
                window.location = window.location;
            }
        });
    }
});
