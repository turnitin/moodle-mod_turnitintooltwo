jQuery(document).ready(function($) {
	$(document).on('change', '.tii_helpdesk_category', function() {
		var category = $(this).val();
		$('#btn_tiisupportform_link').hide();

		// Add sub category options.
		$(".tii_helpdesk_sub_category").find("option:gt(0)").remove();
		$("#tii_solution_template").hide();
		if (category) {
			$(".tii_helpdesk_sub_category").append($("#tii_"+category.toLowerCase()+"_options").html());
		}
	});

	$(document).on('change', '.tii_helpdesk_sub_category', function() {
		var category = $('.tii_helpdesk_category').val();
		var sub_category = $(this).val();

		// Populate template with solution.
		if (sub_category !== "") {
			var issue = $("#tii_"+category.toLowerCase()+"_"+sub_category+" .issue").html();
			$("#tii_solution_template #solution_issue").html(issue);

			var answer = $("#tii_"+category.toLowerCase()+"_"+sub_category+" .answer").html();
			$("#tii_solution_template #solution_answer").html(answer);

			var link = $("#tii_"+category.toLowerCase()+"_"+sub_category+" .link").html();
			$("#tii_solution_template #solution_link").html(link);

			$("#tii_solution_template").show();
			$('#btn_tiisupportform_link').show();
		} else {
			$("#tii_solution_template").hide();
		}
	});

	$(document).on('click', '#btn_supportform', function() {
		var category = $('.tii_helpdesk_category').val();
		var sub_category = $('.tii_helpdesk_sub_category').val();

		window.location.href = M.cfg.wwwroot+'/mod/turnitintooltwo/extras.php?cmd=supportform&category='+category+'&sub_category='+sub_category;
	});
});