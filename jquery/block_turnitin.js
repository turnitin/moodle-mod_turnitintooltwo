jQuery(document).ready(function($) {
    if ($('.block_turnitin').length > 0) {

        $('#block_loading').show();

        $.ajax({
            "dataType": 'json',
            "type": "POST",
            "url": M.cfg.wwwroot + "/mod/turnitintooltwo/ajax.php",
            "data": {action: "search_classes", request_source: "block", sesskey: M.cfg.sesskey},
            "success": function(result) {
                eval(result);
                $('#block_loading').hide();
                if (result.blockHTML == '') {
	                $('.block_turnitin').hide();
                } else {
                    $('#block_migrate_content').html(result.blockHTML);
                }
    		},
            "error": function() {
                $('.block_turnitin').hide();
            }
        });
	}
});