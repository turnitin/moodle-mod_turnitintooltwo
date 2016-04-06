<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package   turnitintooltwo
 * @copyright 2012 iParadigms LLC
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once(__DIR__."/lib.php");
require_once(__DIR__."/turnitintooltwo_view.class.php");

$turnitintooltwoview = new turnitintooltwo_view();

$cmd = optional_param('cmd', "", PARAM_ALPHAEXT);
$viewcontext = optional_param('view_context', "window", PARAM_ALPHAEXT);

// Initialise variables.
$output = "";
$jsrequired = false;
$hidebg = ($cmd == 'rubricmanager' || $cmd == 'quickmarkmanager') ? true : false;

// Module id needed for support form.
$id = optional_param('id', 0, PARAM_INT);

// Get course and module data that we've linked to here from and set context accordingly.
if ($id != 0) {
    //Pre 2.8 does not have the function get_course_and_cm_from_cmid.
    if ($CFG->branch >= 28) {
        list($course, $cm) = get_course_and_cm_from_cmid($id, 'turnitintooltwo');
    }
    else {
        $cm = get_coursemodule_from_id('turnitintooltwo', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    }

    if (!$cm) {
        turnitintooltwo_print_error('coursemodidincorrect', 'turnitintooltwo');
    }
    if (!$course = $DB->get_record("course", array("id" => $cm->course))) {
        turnitintooltwo_print_error('coursemisconfigured', 'turnitintooltwo');
    }
    if (!$turnitintooltwo = $DB->get_record("turnitintooltwo", array("id" => $cm->instance))) {
        turnitintooltwo_print_error('coursemodincorrect', 'turnitintooltwo');
    }

    $PAGE->set_context(context_module::instance($cm->id));
    $PAGE->set_pagelayout('base');
    require_login($course->id, true, $cm);
} else {
    $PAGE->set_context(context_system::instance());
    require_login();
}

// Load Javascript and CSS.
$turnitintooltwoview->load_page_components($hidebg);

// Configure URL correctly.
$urlparams = array('cmd' => $cmd, 'view_context' => $viewcontext);
if ($id != 0) {
    $urlparams['id'] = $id;
}
$url = new moodle_url('/mod/turnitintooltwo/extras.php', $urlparams);
$title = "";

switch ($cmd) {
    case "supportwizard":
        $PAGE->requires->jquery_plugin('turnitintooltwo-turnitin_helpdesk', 'mod_turnitintooltwo');
        $title = get_string('turnitinhelpdesk', 'turnitintooltwo');

        include "classes/helpdeskwizard/helpdeskwizard.php";
        $helpdeskwizard = new helpdeskwizard();
        $output = $helpdeskwizard->output_wizard($id);
        break;

    case "supportform":
        include "classes/helpdeskwizard/helpdeskwizard.php";
        $helpdeskwizard = new helpdeskwizard();
        $title = get_string('turnitinhelpdesk', 'turnitintooltwo');

        // Get the Turnitin class id if we are in a class context.
        $tiiclass = 0;
        if ($id != 0) {
            $course = turnitintooltwo_assignment::get_course_data($course->id);
            $tiiclass = (isset($course->turnitin_cid)) ? $course->turnitin_cid : 0;
        }

        $category = optional_param('category', "", PARAM_ALPHAEXT);
        $sub_category = optional_param('sub_category', "", PARAM_ALPHAEXT);

        $config = turnitintooltwo_admin_config();
        $plugin_version = turnitintooltwo_get_version();

        // Parameters to pass to support form.
        $params = array(
                'category' => $category,
                'sub_category' => $sub_category,
                'class_id' => $tiiclass,
                'vle_version' => 'Moodle ('.$CFG->branch.') '.$CFG->version,
                'integration_id' => 'MoodleDirectV2',
                'integration_version' => $plugin_version,
                'account_id' => $config->accountid
            );

        // Output Iframe containing Turnitin Helpdesk form.
        $output = $helpdeskwizard->output_form( $params );
        break;

    case "courses":
        require_capability('moodle/course:create', context_system::instance());

        $title = get_string('restorationheader', 'turnitintooltwo');
        $jsrequired = true;

        $output .= html_writer::tag('h2', get_string('restorationheader', 'turnitintooltwo'));
        $output .= html_writer::tag('p', get_string('coursebrowserdesc', 'turnitintooltwo'));

        $coursesearchform = html_writer::label(get_string('coursetitle', 'turnitintooltwo').': ', 'search_course_title');
        $coursesearchform .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'search_course_title',
                                                                    'name' => 'search_course_title'));

        $coursesearchform .= html_writer::label(get_string('integration', 'turnitintooltwo').': ', 'search_course_integration');
        $coursesearchform .= html_writer::select($tiiintegrationids, 'search_course_integration', '', array('' => 'choosedots'),
                                                array('id' => 'search_course_integration'));

        $coursesearchform .= html_writer::label(get_string('ced', 'turnitintooltwo').': ', 'search_course_end_date');
        $coursesearchform .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'search_course_end_date',
                                                                    'name' => 'search_course_end_date'));

        $coursesearchform .= html_writer::tag('button', get_string('searchcourses', 'turnitintooltwo'),
                                                array("id" => "search_courses_button"));

        $output .= $OUTPUT->box($coursesearchform, 'generalbox', 'course_search_options');

        $displaylist = array();
        $parentlist = array();
        require_once($CFG->dirroot."/course/lib.php");
        if (file_exists($CFG->libdir.'/coursecatlib.php')) {
            require_once($CFG->libdir.'/coursecatlib.php');
            $displaylist = coursecat::make_categories_list('');
        } else {
            make_categories_list($displaylist, $parentlist, '');
        }

        $categoryselectlabel = html_writer::label(get_string('selectcoursecategory', 'turnitintooltwo'), 'create_course_category');
        $categoryselect = html_writer::select($displaylist, 'create_course_category', '', array(),
                                                array('class' => 'create_course_category'));

        $createassigncheckbox = html_writer::checkbox('create_assign', 1, false,
                                                get_string('createmoodleassignments', 'turnitintooltwo'),
                                                array("class" => "create_assignment_checkbox"));
        $createassign = html_writer::tag('div', $createassigncheckbox, array("class" => "create_assign_checkbox_container"));

        $createbutton = html_writer::tag('button', get_string('createmoodlecourses', 'turnitintooltwo'),
                                        array("id" => "create_classes_button"));
        $output .= $OUTPUT->box($categoryselectlabel." ".$categoryselect.$createassign.$createbutton, 'create_checkboxes navbar');

        $table = new html_table();
        $table->id = "courseBrowserTable";
        $rows = array();

        // Make up json array for drop down in table.
        $integrationidsjson = array();
        foreach ($tiiintegrationids as $k => $v) {
            $integrationidsjson[] = array('value' => $k, 'label' => $v);
        }
        $output .= html_writer::script('var integration_ids = '.json_encode($integrationidsjson));

        // Do the table headers.
        $cells = array();
        $cells[0] = new html_table_cell(html_writer::checkbox('selectallcb', 1, false));
        $cells[1] = new html_table_cell(get_string('coursetitle', 'turnitintooltwo'));
        $cells[2] = new html_table_cell(get_string('integration', 'turnitintooltwo'));
        $cells[3] = new html_table_cell(get_string('ced', 'turnitintooltwo'));
        $cells[4] = new html_table_cell(get_string('turnitinid', 'turnitintooltwo'));
        $cells[5] = new html_table_cell(get_string('moodlelinked', 'turnitintooltwo'));
        $cells[6] = new html_table_cell('&nbsp;');

        $table->head = $cells;
        $output .= $OUTPUT->box(html_writer::table($table), '');

        $output .= turnitintooltwo_show_edit_course_end_date_form();

        break;

    case "multiple_class_recreation":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $PAGE->set_pagelayout('embedded');
        $title = get_string('restorationheader', 'turnitintooltwo');

        require_capability('moodle/course:create', context_system::instance());

        $assignments = optional_param('assignments', 0, PARAM_INT);
        $category = optional_param('category', 0, PARAM_INT);

        $urlparams['assignments'] = $assignments;
        $urlparams['category'] = $category;

        $classids = array();
        foreach ($_REQUEST as $k => $v) {
            if (strstr($k, "class_id") !== false) {
                $classids[] = (int)$v;
            }
        }

        $output = html_writer::tag('div', get_string('recreatemulticlasses', 'turnitintooltwo'),
                                                array('class' => 'course_creation_bulk_msg centered_div'));
        $output .= $OUTPUT->box($category, '', 'course_category');
        $output .= $OUTPUT->box($assignments, '', 'create_assignments');
        $output .= $OUTPUT->box(implode(",", $classids), '', 'class_ids');

        $output .= html_writer::tag('div', $OUTPUT->pix_icon('loader', get_string('recreatemulticlasses', 'turnitintooltwo'),
                                                                'mod_turnitintooltwo'),
                                    array('id' => 'course_creation_status', 'class' => 'centered_div'));
        break;

    case "class_recreation":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $PAGE->set_pagelayout('embedded');
        $title = get_string('restorationheader', 'turnitintooltwo');

        require_capability('moodle/course:create', context_system::instance());

        $tiicourseid = optional_param('id', 0, PARAM_INT);
        $urlparams['id'] = $tiicourseid;

        $output = "";

        $turnitincourse = $DB->get_records_sql("SELECT tc.turnitin_cid ".
                                                "FROM {turnitintooltwo_courses} tc ".
                                                "RIGHT JOIN {course} c ON c.id = tc.courseid  ".
                                                "WHERE tc.turnitin_cid = ? ", array($tiicourseid));

        if (empty($turnitincourse)) {
            $output .= turnitintooltwo_show_browser_new_course_form();
            $output .= turnitintooltwo_show_browser_link_course_form();
        }
        $output .= turnitintooltwo_init_browser_assignment_table($tiicourseid);
        break;

    case "rubricmanager":
        $PAGE->set_pagelayout('embedded');
        $tiicourseid = optional_param('tiicourseid', 0, PARAM_INT);

        echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('rubric_manager', 'Instructor', 0, $tiicourseid),
                                                                                array("class" => "launch_form"));
        break;

    case "quickmarkmanager":
        $PAGE->set_pagelayout('embedded');

        echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('quickmark_manager', 'Instructor'),
                                                                                array("class" => "launch_form"));
        break;
    case "useragreement":
        $PAGE->set_pagelayout('embedded');
        $user = new turnitintooltwo_user($USER->id, "Learner");

        $output .= $OUTPUT->box_start('tii_eula_launch');
        $output .= turnitintooltwo_view::output_dv_launch_form("useragreement", 0, $user->tii_user_id, "Learner", '');
        $output .= $OUTPUT->box_end(true);
        echo $output;

        echo html_writer::script("<!--
                                    window.document.forms[0].submit();
                                    //-->");
        break;
}

$nav = ($cmd == "courses" || $cmd == "multiple_class_recreation" || $cmd == "class_recreation") ?
            array(array('title' => get_string('restorationheader', 'turnitintooltwo'), 'url' => '')) : array();

// Build page.
$coursemodforheader = ($id != 0) ? $cm : null;
$courseforheader = ($id != 0) ? $course : null;
echo $turnitintooltwoview->output_header($coursemodforheader,
            $courseforheader,
            $url,
            $title,
            $title,
            $nav,
            "",
            "",
            true,
            '',
            '');

echo html_writer::start_tag('div', array('class' => 'mod_turnitintooltwo'));
echo html_writer::tag("div", $viewcontext, array("id" => "view_context"));
if ($cmd == 'courses') {
    echo $OUTPUT->heading(get_string('pluginname', 'turnitintooltwo'), 2, 'main');
    // Show a warning if javascript is not enabled while a tutor is logged in.
    echo html_writer::tag('noscript', get_string('noscript', 'turnitintooltwo'), array("class" => "warning"));
}

$class = ($jsrequired) ? " js_required" : "";
if ($cmd == 'class_recreation') {
    echo $OUTPUT->box($output, 'generalbox class_recreation');
} else if ($cmd == 'multiple_class_recreation' || $cmd == 'rubricmanager' || $cmd == 'quickmarkmanager') {
    echo $output;
} else {
    echo $OUTPUT->box($output, 'generalbox'.$class);
}
echo html_writer::end_tag("div");

echo $OUTPUT->footer();