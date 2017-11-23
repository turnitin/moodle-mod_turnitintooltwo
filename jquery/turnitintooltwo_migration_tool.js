$(document).ready(function(){

    // Check whether this assignment has been migrated in this session and redirect if so.
    $.ajax({
        "dataType": 'json',
        "type": "POST",
        "url": M.cfg.wwwroot + "/mod/turnitintooltwo/ajax.php",
        "data": {
            action: "check_migrated",
            turnitintoolid: $("#migrate_type").data("turnitintoolid"),
            sesskey: M.cfg.sesskey
        },
        "success": function(data) {
            if (data.migrated === true) {
                window.location.href = M.cfg.wwwroot + "/mod/turnitintooltwo/view.php?id="+data.v2id;
            } else {
                displayMigrationModal();

                $('.dontmigrate_link').click($.proxy(dontmigrate, null, $.colorbox));
            }
        }
    });
});

$('.migrate_link').on('click', function() {
    $('#asktomigrate').hide();
    $('#migrating').show();
    migrate($(this).data("courseid"), $(this).data("turnitintoolid"));
});

function dontmigrate(cb) {
    $('#migration_alert').hide();
    cb.close();
}

function lightBoxCloseButton(cb) {
    $('body').append('<div id="tii_close_bar"><a class="tii_close_link" href="#">' + M.str.turnitintooltwo.closebutton + '</a></div>');
}


function displayMigrationModal() {
    // Only display the modal during a manual migration.
    if ($('#migrate_type').data("turnitintoolid") != $("#migrate_type").data("lastasked")) {
        $.colorbox({width: 550, height: 600, inline:true, opacity: "0.7", href:"#migration_alert",
            onLoad: function() {
                $('#asktomigrate').show();
                lightBoxCloseButton();

                $('.tii_close_link').click($.proxy(dontmigrate, null, $.colorbox));
            },
            onCleanup:function() {
                $('#tii_close_bar').remove();
                $('#migration_alert').hide();
            }
        });

        $('#migration_alert').show();
    }

    if ($('#migrate_type').data("migratetype") == 2) {
        $('#asktomigrate').hide();
        $('#migrating').show();

        migrate($("#migrate_type").data("courseid"), $("#migrate_type").data("turnitintoolid"));
    }
}

function migrate(courseid, turnitintoolid) {
    $.ajax({
        "dataType": 'json',
        "type": "POST",
        url: M.cfg.wwwroot + "/mod/turnitintooltwo/ajax.php",
        "data": {action: "begin_migration", courseid: courseid, turnitintoolid: turnitintoolid, sesskey: M.cfg.sesskey},
        success: function(data) {
            if ($.colorbox) {
                $.colorbox.close();
            }
            $('#migration_alert').hide();
            window.location.href = M.cfg.wwwroot + "/mod/turnitintooltwo/view.php?id="+data.id+"&migrated=1";
        },
        error: function(error) {
            var data = error.responseJSON;
            if ($.colorbox) {
                $.colorbox.close();
            }
            $('#migration_alert').hide();
            $('#turnitintool_style')
                .prepend('<div id="full-error" class="box generalbox noticebox">' + data.error + ' ' + data.message + '</div>');

            // Check if we have a stack trace included.
            if (data.hasOwnProperty('trace')) {
                console.error(data.message);
                console.error(JSON.stringify(data.trace, null, 4));
            }
        }
    });
}
