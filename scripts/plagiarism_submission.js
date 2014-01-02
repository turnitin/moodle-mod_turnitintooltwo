/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    $('.plagiarism_submission').each( function() {

        var idStr = $(this).attr("id").split("-");
        var pathnamehash = idStr[0];
        var submission_type = idStr[1];

        $.ajax({
            type: "POST",
            url: "../../plagiarism/turnitin/ajax.php",
            async: false,
            dataType: "json",
            data: {action: "process_submission", cmid: cmid, itemid: itemid, pathnamehash: pathnamehash, submission_type: submission_type, sesskey: M.cfg.sesskey},
            success: function(data) {
                eval(data);
                if (data.success == false) {
                    $('span.maincontent').html(data.message);
                }
            }
        });
    });
});