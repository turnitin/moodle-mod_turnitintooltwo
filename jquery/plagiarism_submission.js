/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    $('.plagiarism_submission').each( function() {

        if (typeof $(this).attr("id") !== "undefined") {
            var idStr = $(this).attr("id").split("-");
        } else {
            var idStr = $(this).children('.plagiarism_submission_id').html().split("-");
            var textcontent = $(this).children('.plagiarism_submission_content').html();
        }
        var pathnamehash = idStr[0];
        var submission_type = idStr[1];
        var cmid = $(this).children('.plagiarism_submission_cmid').html();
        var itemid = $(this).children('.plagiarism_submission_itemid').html();

        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            async: false,
            dataType: "json",
            data: {action: "process_submission", cmid: cmid, itemid: itemid, pathnamehash: pathnamehash, 
                    textcontent: textcontent, submission_type: submission_type, sesskey: M.cfg.sesskey},
            success: function(data) {
                eval(data);
                if (data.success == false) {
                    $('div.turnitin_submit_error_'+pathnamehash).css('display', 'block');
                    $('div.turnitin_submit_error_'+pathnamehash).html(data.message);
                } else {
                    if (typeof data.message !== "undefined") {
                        $('div.turnitin_submit_success_'+pathnamehash).css('display', 'block');
                        $('div.turnitin_submit_success_'+pathnamehash).html(data.message);
                    }
                }
            }
        });
    });
});