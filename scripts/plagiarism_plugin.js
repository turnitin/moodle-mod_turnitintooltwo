/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    $('.tii_links_container .tii_tooltip').live('mouseover', function(e) {
        $(this).tooltipster();
        return false;
    });

    $('.origreport_open').live('click', function() {
        var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');

        for (var i = 0; i < classList.length; i++) {
           if (classList[i].indexOf('origreport_') !== -1 && classList[i] != 'origreport_open') {
                var classStr = classList[i].split("_");
                openDV("origreport", classStr[1], classStr[2]);
           }
        }
    });

    $('.grademark_open').live('click', function() {
        var classList = $(this).attr('class').replace(/\s+/,' ').split(' ');

        for (var i = 0; i < classList.length; i++) {
           if (classList[i].indexOf('grademark_') !== -1 && classList[i] != 'grademark_open') {
                var classStr = classList[i].split("_");
                openDV("grademark", classStr[1], classStr[2]);
           }
        }
    });

    // Open an iframe light box containing the Peermark Manager
    if ($('.peermark_manager_pp_launch').length > 0) {
        $('.peermark_manager_pp_launch').colorbox({
            iframe:true, width:"802px", height:"772px", opacity: "0.7", className: "peermark_manager",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); },
            onClosed: function() {
                refreshPPPeermarkAssignments();
            }
        });
    }

    // Open an iframe light box containing the Peermark reviews
    $('.peermark_reviews_pp_launch').live('click', function(e) {
        $('.peermark_reviews_pp_launch').colorbox({
            open:true,iframe:true, width:"802px", height:"772px", opacity: "0.7", className: "peermark_reviews",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); }
        });
        return false;
    });

    // Open an iframe light box containing the Rubric View
    $('.rubric_view_pp_launch').live('click', function(e) {
        $(this).colorbox({
            open:true,iframe:true, width:"832px", height:"682px", opacity: "0.7", className: "rubric_view",
            onLoad: function() { getLoadingGif(); },
            onCleanup: function() { hideLoadingGif(); }
        });
        return false;
    });

    // Launch the Turnitin EULA
    if ($(".pp_turnitin_ula").length > 0) {
        $(".pp_turnitin_ula").live('click', function(e) {
            launchEULA('#useragreement_form form');
        });
    }

    // Launch the EULA for forums. Has to be done differently
    if ($(".forum_eula_launch").length > 0) {

        // Remove the a tag and replace with form and message
        var spanClick = '<span>'+$('.forum_eula_launch_noscript').html()+'</span>';
        $(".forum_eula_launch").html(spanClick+'<form class="useragreement_form" action="'+$('span.turnitin_eula_link').html()+'" method="POST" accept-charset="utf-8" target="_blank"></form>')
        $('.forum_eula_launch_noscript').remove();

        $(".forum_eula_launch span").on('click', function(e) {
            launchEULA('.useragreement_form');
        });
    }

    function launchEULA(identifier) {
        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            dataType: "json",
            data: {action: 'useragreement', cmid: $('span.cmid').html()},
            success: function(data) {
                $(identifier).html('');
                $.each(data, function(key, val) {
                    $(identifier).append('<input name="'+key+'" value="'+val+'" type="hidden" />');
                });
                $(identifier).append('<input type="submit" value="Submit" />');
                $(identifier).submit();

                $(window).on("message", function(ev) {
                    if (ev.originalEvent.data == 'turnitin_eula_accepted') {
                        window.location = window.location;
                    }
                });
            }
        });
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

    // Open the document viewer within a frame in a new tab
    function openDV(dvtype, submission_id, coursemoduleid) {
        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            dataType: "html",
            data: {action: dvtype, submission: submission_id, cmid: coursemoduleid},
            success: function(data) {

                $("."+dvtype+"_form_"+submission_id).html(data);
                $("."+dvtype+"_form_"+submission_id).children("form").on("submit", function(event) {
                    dvWindow = window.open('/', 'dv_'+submission_id);
                    dvWindow.document.write('<frameset><frame id="dvWindow" name="dvWindow"></frame></frameset>');
                    dvWindow.document.close();
                    $(dvWindow).bind('beforeunload', function() {
                        refreshScores(submission_id, coursemoduleid);
                    });
                });
                $("."+dvtype+"_form_"+submission_id).children("form").submit();
                $("."+dvtype+"_form_"+submission_id).html("");
            }
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
});