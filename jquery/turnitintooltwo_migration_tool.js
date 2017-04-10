$(document).ready(function(){
    $.colorbox({width: 500, height: 500, inline:true, href:"#migration_alert"});
    $('#migration_alert').show();
}); 

$('.migrate_link').click(function() {
    $.ajax({
        "dataType": 'json',
        "type": "POST",
        url: M.cfg.wwwroot + "/mod/turnitintooltwo/ajax.php",
        "data": {action: "begin_migration", courseid: $(this).data("courseid"), turnitintoolid: $(this).data("turnitintoolid"), sesskey: M.cfg.sesskey},
        success: function(data) {
            $.colorbox.close();
            $('#migration_alert').hide();
            window.location.href = M.cfg.wwwroot + "/mod/turnitintooltwo/view.php?id="+data.id;
        }
    });
});

$('.dontmigrate_link').click(function () {
    $.colorbox.close();
    $('#migration_alert').hide();
});

