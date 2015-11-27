/*
 * This small piece of script is a workaround that's needed to add tabs in to
 * the settings page. They need to be removed from the settings form and placed
 * outside that containing fieldset.
 */
jQuery(document).ready(function($) {
	if ($('.settingsform fieldset div.formsettingheading').length > 0) {
	    var tabmenu = $('.settingsform fieldset div.formsettingheading:first').html();
	    if (tabmenu.indexOf("tabtree") >= 0) {
	        $('.settingsform fieldset div.formsettingheading:first').remove();
	        $('.settingsform h2:first').after(tabmenu);
	    }
    }

    $('input[name="selectallcb"]').click(function() {
    	if ($(this).prop('checked')) {
    		$('.browser_checkbox').prop('checked', true);
    		if ($('.browser_checkbox:checked').length > 0) {
	            $('.create_checkboxes').slideDown();
	        } else {
	            $('.create_checkboxes').slideUp();
	        }
    	} else {
    		$('.browser_checkbox').prop('checked', false);
    		$('.create_checkboxes').slideUp();
    	}
    });

    $('.tii_upgrade_check').click(function(e) {
    	e.preventDefault();
    	// Change Url depending on Settings page
	    var url = "ajax.php";
	    if ($('.settingsform fieldset div.formsettingheading').length > 0) {
	        url = "../mod/turnitintooltwo/ajax.php";
	    }

	    $('.tii_upgrade_check').hide();
	    $('.tii_upgrading_check').css('display', 'inline-block');
	    var current_version = $(this).attr('id').split('_')[1];

    	$.ajax({
	        type: "POST",
	        url: url,
	        dataType: "html",
	        data: {action: "check_upgrade", current_version: current_version, sesskey: M.cfg.sesskey},
	        success: function(data) {
	        	var data = $.parseJSON(data)

				if (data['update'] === 1) {
					$('.tii_upgrade_check').hide();
					$('.tii_upgrading_check').hide();
					$('.tii_no_upgrade').html('<a href="' + data['file'][0] + '">' + M.str.turnitintooltwo.upgradeavailable + '</a>');
	        	} else {
	        		$('.tii_upgrading_check').hide();
	        		$('.tii_upgrade_check').show();
	        	}
	        }
	    });
    });

    if ($('.test_connection').length > 0) {
    	if ($('#id_s_turnitintooltwo_accountid').val() != '' || $('#id_s_turnitintooltwo_secretkey').val() != '') {
			$('.test_connection').show();
			$('#test_link').show();
		}

        $('#id_s_turnitintooltwo_accountid, #id_s_turnitintooltwo_secretkey, #id_s_turnitintooltwo_apiurl').keyup(function() {
            $('#testing_container').hide();

            var accountid = $('#id_s_turnitintooltwo_accountid').val();
            var accountshared = $('#id_s_turnitintooltwo_secretkey').val();

            // Make sure they aren't all spaces or empty
            if (accountid == '' ||
                accountshared == '' ||
                ! /\S/.test(accountid) ||
                ! /\S/.test(accountshared))
            {
                $('#test_result').hide();
                $('.test_connection').hide();
            } else {
                 $('.test_connection').show();
                 $('#test_link').show();
            }
        });

    	$('#test_link').click(function() {
    		$('#test_result').hide();
            $('input, #id_s_turnitintooltwo_apiurl').prop('disabled', true);
		    $('#test_link').hide();
			$("#test_result").css('opacity', '');
			$('#test_result').removeClass('test_link_success test_link_fail');
			$('#testing_container').show();

		    // Change Url depending on Settings page
		    var url = "ajax.php";
		    if ($('.settingsform fieldset div.formsettingheading').length > 0) {
		        url = "../mod/turnitintooltwo/ajax.php";
		    }

		    var accountid = $('#id_s_turnitintooltwo_accountid').val();
		    var accountshared = $('#id_s_turnitintooltwo_secretkey').val();
		    var accounturl = $('#id_s_turnitintooltwo_apiurl').val();

		    $.ajax({
		        type: "POST",
		        url: url,
		        dataType: "json",
		        data: {action: "test_connection", sesskey: M.cfg.sesskey, account_id: accountid, account_shared: accountshared, url: accounturl},
		        success: function(data) {
		            eval(data);

		            $('#testing_container').hide();

		            if (data.connection_status == "success") {
		                $('#test_result').addClass('test_link_success');
		            } else {
		                $('#test_result').addClass('test_link_fail');
		            }

		            $('#test_result').html(data.msg);
		            $('#test_result').show();
		            $('#test_link').show();
                    $('input, #id_s_turnitintooltwo_apiurl').prop('disabled', false);
		        }
		    });
		});
    }

    //Disable/enable resubmit selected files when one or more are selected.
    $(document).on('click', '.migration_checkbox', function() {
        if ($('.migration_checkbox:checked').length == 2) {
        	$('#trial-migration-button').removeAttr('disabled');
        } else {
            $('#trial-migration-button').attr('disabled', 'disabled');
        }
    });



    // Disable/enable resubmit selected files when one or more are selected.
    $(document).on('click', '.migration-button', function() {
        $("#progress-bar").removeClass("hidden_class");
    	var id = this.id;
		var totalCourses = $(this).data("courses");
		var processAtOnce = 10;

		if (totalCourses >= processAtOnce) {
			var iterations = Math.ceil((totalCourses/processAtOnce));
		} else {
			var iterations = totalCourses;
			processAtOnce = 1;
		}

		// Determine whether this is the trial run or not.
    	if (this.id == "trial-migration-button") {
    		var trial = 1;
    	} else {
    		var trial = 0;
        	$("#migration-footer").addClass("hidden_class");
    	}

		// Do the migration.
    	$('.migrationtool').html('');
		migrateCourses(0, totalCourses, processAtOnce, iterations, 1, trial, 1);
    });


    function migrateCourses(start, totalCourses, processAtOnce, iterations, iteration, trial, migrateUsers) {
		// Percentage increase of the progress bar on each iteration.
    	var progressBarSegment = Math.round(100/iterations);

    	// Current progress bar percentage.
    	if (iteration == iterations) {
    		var progressBar = 100;
    	} else {
    		var progressBar = progressBarSegment*iteration;
    	}

	    $.ajax({
	        type: "POST",
	        url: "ajax.php",
	        dataType: "json",
	        data: {action: "migration", sesskey: M.cfg.sesskey, start: start, totalCourses: totalCourses, processAtOnce: processAtOnce, iteration: iteration, trial: trial, migrateUsers: migrateUsers},
	        success: function(result) {
                $('.migrationtool').append(result.dataset);

                // Update progress bar.
                $(".bar").width((progressBar) + '%');
                $(".bar-complete").text((progressBar) + '% Complete');

                if (progressBar == 100) {
                	$("#progress-bar").removeClass("active");

        			if (trial == 1) {
        				$("#migration-footer").removeClass("hidden_class");
        			}
        			else {
        				$("#migrationtool_complete").removeClass("hidden_class");
        			}
                }

                start = result.end;
                iteration = result.iteration + 1;
                if (result.end < totalCourses) {
                    migrateCourses(start, totalCourses, processAtOnce, iterations, iteration, trial, migrateUsers);
                }
            },
	    });
    }
});