$(document).ready(function(){
    $.colorbox({width: 500, height: 500, inline:true, opacity: "0.7", href:"#migration_alert",
    onLoad: function() {
        lightBoxCloseButton();
    },
    onCleanup:function() {
        $('#tii_close_bar').remove();
    }});
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

function lightBoxCloseButton() {
    $('body').append('<div id="tii_close_bar"><a href="#" onclick="$.colorbox.close(); return false;">' + M.str.turnitintooltwo.closebutton + '</a></div>');
}
