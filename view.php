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

require_once(__DIR__."/../../config.php");
require_once(__DIR__."/lib.php");
require_once($CFG->libdir."/formslib.php");
require_once($CFG->libdir."/form/text.php");
require_once($CFG->libdir."/form/datetimeselector.php");
require_once($CFG->libdir."/form/hidden.php");
require_once($CFG->libdir."/form/button.php");
require_once($CFG->libdir."/form/submit.php");
require_once($CFG->libdir."/uploadlib.php");

// Offline mode provided by Androgogic. Set tiioffline in config.php.
if (!empty($CFG->tiioffline)) {
    turnitintooltwo_print_error('turnitintoolofflineerror', 'turnitintooltwo');
}

require_once(__DIR__."/turnitintooltwo_view.class.php");
$turnitintooltwoview = new turnitintooltwo_view();

require_once(__DIR__.'/classes/nonsubmitters/nonsubmitters_message.php');
$nonsubmitters = new nonsubmitters_message();

// Get/Set variables and work out which function to perform.
$id = required_param('id', PARAM_INT); // Course Module ID.
$a = optional_param('a', 0, PARAM_INT); // Turnitintooltwo ID.
$part = optional_param('part', 0, PARAM_INT); // Part ID.
$user = optional_param('user', 0, PARAM_INT); // User ID.
$do = optional_param('do', "submissions", PARAM_ALPHAEXT);
$action = optional_param('action', "", PARAM_ALPHA);
$viewcontext = optional_param('view_context', "window", PARAM_ALPHAEXT);

$notice = null;
if (isset($_SESSION["notice"])) {
    $notice = $_SESSION["notice"];
    $notice["type"] = (empty($_SESSION["notice"]["type"])) ? "general" : $_SESSION["notice"]["type"];
    unset($_SESSION["notice"]);
}

if ($id) {
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
} else {
    if (!$turnitintooltwo = $DB->get_record("turnitintooltwo", array("id" => $a))) {
        turnitintooltwo_print_error('coursemodincorrect', 'turnitintooltwo');
    }
    if (!$course = $DB->get_record("course", array("id" => $turnitintooltwo->course))) {
        turnitintooltwo_print_error('coursemisconfigured', 'turnitintooltwo');
    }
    if (!$cm = get_coursemodule_from_instance("turnitintooltwo", $turnitintooltwo->id, $course->id)) {
        turnitintooltwo_print_error('coursemodidincorrect', 'turnitintooltwo');
    }
}

// If opening DV then $viewcontext needs to be set to box
$viewcontext = ($do == "origreport" || $do == "grademark" || $do == "default") ? "box" : $viewcontext;

require_login($course->id, true, $cm);

//Check if the user has the capability to view the page - used when an assignment is set to hidden.
$context = context_module::instance($cm->id);
require_capability('mod/turnitintooltwo:view', $context);

//Set the page layout to base.
$PAGE->set_pagelayout('base');

// Settings for page navigation
if ($viewcontext == "window") {
    // Show navigation if required.
    $config = turnitintooltwo_admin_config();
    if ($config->inboxlayout == 1) {
        $PAGE->set_cm($cm);
        $PAGE->set_pagelayout('incourse');
    }
}

// Don't show messages popup if we are in submission modal.
$forbiddenmsgscreens = array('submission_success', 'submitpaper');
if (in_array($do, $forbiddenmsgscreens)) {
    $PAGE->set_popup_notification_allowed(false);
}

// Configure URL correctly.
$urlparams = array('id' => $id, 'a' => $a, 'part' => $part, 'user' => $user, 'do' => $do, 'action' => $action,
                    'view_context' => $viewcontext);
$url = new moodle_url('/mod/turnitintooltwo/view.php', $urlparams);

// Load Javascript and CSS.
$turnitintooltwoview->load_page_components();

$turnitintooltwoassignment = new turnitintooltwo_assignment($turnitintooltwo->id, $turnitintooltwo);

// Define file upload options.
$maxbytessite = ($CFG->maxbytes == 0 || $CFG->maxbytes > TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE) ?
                            TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE : $CFG->maxbytes;
$maxbytescourse = ($COURSE->maxbytes == 0 || $COURSE->maxbytes > TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE) ?
                            TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE : $COURSE->maxbytes;

$maxfilesize = get_user_max_upload_file_size(context_module::instance($cm->id),
                                                $maxbytessite,
                                                $maxbytescourse,
                                                $turnitintooltwoassignment->turnitintooltwo->maxfilesize);
$maxfilesize = ($maxfilesize <= 0) ? TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE : $maxfilesize;
$turnitintooltwofileuploadoptions = array('maxbytes' => $maxfilesize,
                                            'subdirs' => false, 'maxfiles' => 1, 'accepted_types' => '*');

if (!$parts = $turnitintooltwoassignment->get_parts()) {
    turnitintooltwo_print_error('partgeterror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
}

// Get whether user is a tutor/student.
$istutor = has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id));
$userrole = ($istutor) ? 'Instructor' : 'Learner';

// Deal with actions here.
if (!empty($action)) {
    if ($action != "submission") {
        turnitintooltwo_activitylog("Action: ".$action." | Id: ".$turnitintooltwo->id." | Part: ".$part, "REQUEST");
    }

    switch ($action) {
        case "delpart":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            if (!$istutor) {
                throw new moodle_exception('nopermissions', 'error', '', 'delpart');
            }

            if ($turnitintooltwoassignment->delete_moodle_assignment_part($turnitintooltwoassignment->turnitintooltwo->id, $part)) {
                $_SESSION["notice"]['message'] = get_string('partdeleted', 'turnitintooltwo');
            }

            redirect(new moodle_url('/course/mod.php', array('update' => $cm->id,
                                            'return' => true, 'sesskey' => sesskey())));
            exit;
            break;

        case "addtutor":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            if ($istutor) {
                $tutorid = required_param('turnitintutors', PARAM_INT);
                $_SESSION["notice"]['message'] = $turnitintooltwoassignment->add_tii_tutor($tutorid);
            }

            redirect(new moodle_url('/mod/turnitintooltwo/view.php', array('id' => $id, 'do' => $do)));
            exit;
            break;

        case "removetutor":
        case "removestudent":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            $memberrole = ($action == "removetutor") ? "Instructor" : "Learner";

            if ($istutor) {
                $membershipid = required_param('membership_id', PARAM_INT);
                $_SESSION["notice"]['message'] = $turnitintooltwoassignment->remove_tii_user_by_role($membershipid, $memberrole);
            }
            redirect(new moodle_url('/mod/turnitintooltwo/view.php', array('id' => $id, 'do' => $do)));
            exit;
            break;

        case "submission":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            $do = "submission_success";
            $error = false;

            // Clean posted variables.
            $post = array();
            $post['submissiontype'] = required_param('submissiontype', PARAM_INT);
            $post['submissiontext'] = optional_param('submissiontext', '', PARAM_TEXT);
            $post['submissiontext'] = trim($post['submissiontext']);
            $post['submissiontitle'] = optional_param('submissiontitle', '', PARAM_TEXT);
            $post['submissiontitle'] = trim(filter_var($post['submissiontitle'], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
            $post['studentsname'] = optional_param('studentsname', $USER->id, PARAM_INT);
            $post['studentsname'] = ($istutor) ? $post['studentsname'] : $USER->id;
            $post['submissionpart'] = required_param('submissionpart', PARAM_INT);
            $post['submissionagreement'] = required_param('submissionagreement', PARAM_INT);

            // Default params for redirecting if there is a problem.
            $extraparams = array("part" => $post['submissionpart'], "user" => $post['studentsname']);

            // Check that text content has been provided for submission if applicable.
            if ($post['submissiontype'] == 2 && empty($post['submissiontext'])) {
                $_SESSION["notice"]["message"] = get_string('submissiontexterror', 'turnitintooltwo');
                $error = true;
                $do = "submitpaper";
            }

            // Check that title for submission has been entered.
            if (empty($post['submissiontitle'])) {
                $_SESSION["notice"]["message"] = get_string('submissiontitleerror', 'turnitintooltwo');
                $error = true;
                $do = "submitpaper";
            }

            // Check that student has accepted disclaimer if applicable.
            if (empty($post['submissionagreement'])) {
                $_SESSION["notice"]["message"] = get_string('copyrightagreementerror', 'turnitintooltwo');
                $error = true;
                $do = "submitpaper";
            }

            if ($error) {
                // Save data in session incase of error
                $_SESSION['form_data']->submissiontype = $post['submissiontype'];
                $_SESSION['form_data']->submissiontitle = $post['submissiontitle'];
                $_SESSION['form_data']->submissiontext = $post['submissiontext'];
            } else {
                // Check for previous submission to this part.
                if (!$prevsubmission = $turnitintooltwoassignment->get_user_submissions($post['studentsname'],
                                                    $turnitintooltwoassignment->turnitintooltwo->id, $post['submissionpart'])) {
                    // Create submission object if not a previous one.
                    $turnitintooltwosubmission = new turnitintooltwo_submission(0, "moodle", $turnitintooltwoassignment);
                    if (!$turnitintooltwosubmission->create_submission($post)) {
                        $_SESSION["notice"]["message"] = get_string('createsubmissionerror', 'turnitintooltwo');
                        $do = "submitpaper";
                    }
                } else {
                    foreach ($prevsubmission as $prev) {
                        $submission = $prev;
                    }
                    $turnitintooltwosubmission = new turnitintooltwo_submission($submission->id, "moodle", $turnitintooltwoassignment);
                    $turnitintooltwosubmission->reset_submission($post);
                }

                if ($turnitintooltwosubmission) {
                    if ($post['submissiontype'] == 1) {
                        // Upload file.
                        $doupload = $turnitintooltwosubmission->do_file_upload($cm, $turnitintooltwofileuploadoptions);
                        if (!$doupload["result"]) {
                            if (!$prevsubmission) {
                                $turnitintooltwosubmission->delete_submission('failed');
                            }
                            $_SESSION["notice"]["message"] = $doupload["message"];
                            $_SESSION["notice"]["type"] = "error";
                            $do = "submitpaper";
                        }
                    } else if ($post['submissiontype'] == 2) {
                        $turnitintooltwosubmission->prepare_text_submission($cm, $post);
                    }

                    if ($do == "submission_success") {
                        // Log successful submission to Moodle.
                        turnitintooltwo_add_to_log(
                            $turnitintooltwoassignment->turnitintooltwo->course,
                            "add submission",
                            'view.php?id='.$cm->id,
                            get_string('addsubmissiondesc', 'turnitintooltwo') . " '" . $post['submissiontitle'] . "'",
                            $cm->id, $post['studentsname']
                        );

                        $tiisubmission = $turnitintooltwosubmission->do_tii_submission($cm, $turnitintooltwoassignment);
                        $_SESSION["digital_receipt"] = $tiisubmission;
                        $_SESSION["digital_receipt"]["is_manual"] = 0;

                        if ($tiisubmission['success'] == true) {
                            $locked_assignment = new stdClass();
                            $locked_assignment->id = $turnitintooltwoassignment->turnitintooltwo->id;
                            $locked_assignment->submitted = 1;
                            $DB->update_record('turnitintooltwo', $locked_assignment);

                            $locked_part = new stdClass();
                            $locked_part->id = $post['submissionpart'];
                            $locked_part->submitted = 1;

                            //Disable anonymous marking if post date has passed.
                            if ($parts[$post['submissionpart']]->dtpost <= time()) {
                                $locked_part->unanon = 1;
                            }

                            $DB->update_record('turnitintooltwo_parts', $locked_part);
                        } else {
                            $do = "submission_failure";
                        }
                        $extraparams = array();
                        unset($_SESSION['form_data']);
                    }
                }
            }

            $params = array_merge(array('id' => $id, 'do' => $do, 'view_context' => $viewcontext), $extraparams);
            redirect(new moodle_url('/mod/turnitintooltwo/view.php', $params));
            exit;
            break;

        case "manualsubmission":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            $submissionid = required_param('sub', PARAM_INT);
            $turnitintooltwosubmission = new turnitintooltwo_submission($submissionid, "moodle", $turnitintooltwoassignment);

            $digitalreceipt = $turnitintooltwosubmission->do_tii_submission($cm, $turnitintooltwoassignment);
            $_SESSION["digital_receipt"] = $digitalreceipt;

            redirect(new moodle_url('/mod/turnitintooltwo/view.php', array('id' => $id, 'do' => 'submissions')));
            exit;
            break;

        case "deletesubmission":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            $submissionid = required_param('sub', PARAM_INT);
            $turnitintooltwosubmission = new turnitintooltwo_submission($submissionid, "moodle", $turnitintooltwoassignment);

            // Allow instructors to delete submission and students to delete if the submission hasn't gone to Turnitin.
            if (($istutor && $submissionid != 0) ||
                ($USER->id == $turnitintooltwosubmission->userid && empty($turnitintooltwosubmission->submission_objectid))) {
                $_SESSION["notice"] = $turnitintooltwosubmission->delete_submission();
            }
            redirect(new moodle_url('/mod/turnitintooltwo/view.php', array('id' => $id, 'partid' => $part, 'do' => 'submissions')));
            exit;
            break;

        case "emailnonsubmitters":
            if (!confirm_sesskey()) {
                throw new moodle_exception('invalidsesskey', 'error');
            }

            $subject = required_param('nonsubmitters_subject', PARAM_TEXT);
            $message = required_param('nonsubmitters_message', PARAM_TEXT);
            $sendtoself = optional_param('nonsubmitters_sendtoself', 0, PARAM_INT);

            // Error handling for non submitters form.
            $error = false;
            if (empty($subject) || empty($message)) {
                $_SESSION['embeddednotice'] = array("type" => "error");
                $_SESSION["embeddednotice"]["message"] = get_string('nonsubmitterserror', 'turnitintooltwo');
                $error = true;
                $do = "emailnonsubmittersform";
            }

            if ($error) {
                // Save data in session incase of error
                $_SESSION['form_data'] = new stdClass;
                $_SESSION['form_data']->nonsubmitters_subject = $subject;
                $_SESSION['form_data']->nonsubmitters_message = $message;
                $_SESSION['form_data']->nonsubmitters_sendtoself = $sendtoself;
            } else {

                // Get all users enrolled in the class.
                $context = context_module::instance($cm->id);
                $allusers = get_users_by_capability(context_module::instance($cm->id), 'mod/turnitintooltwo:submit', 'u.id',
                                                '', '', '', groups_get_activity_group($cm));

                // Get users who've submitted.
                $params = array('turnitintooltwoid' => $turnitintooltwo->id, 'submission_part' => $part);
                $submittedusers = $DB->get_records('turnitintooltwo_submissions', $params, '', 'userid');

                // Send message to all non submitted users.
                $nonsubmittedusers = array_diff_key((array)$allusers, (array)$submittedusers);
                foreach ($nonsubmittedusers as $nonsubmitteduser) {
                    //Send a message to the user's Moodle inbox with the digital receipt.
                    $nonsubmitters->send_message($nonsubmitteduser->id, $subject, $message);
                }

                // Send a copy of message to the instructor if appropriate.
                if (!empty($sendtoself)) {
                    $nonsubmitters->send_message($USER->id, $subject, $message);
                }

                $do = "emailsent";
            }

            $params = array('id' => $id, 'do' => $do, 'view_context' => 'box_solid');
            redirect(new moodle_url('/mod/turnitintooltwo/view.php', $params));
            exit;
            break;
    }
}

// Enable activity completion on page view.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Show header and navigation
if ($viewcontext == "box" || $viewcontext == "box_solid") {

    $PAGE->set_pagelayout('embedded');

    $turnitintooltwoview->output_header($cm,
            $course,
            $url,
            '',
            '',
            array(),
            "",
            "",
            true,
            '',
            '');
} else {
    $turnitintooltwoview->output_header($cm,
            $course,
            $url,
            $turnitintooltwoassignment->turnitintooltwo->name,
            $SITE->fullname,
            array(),
            "",
            "",
            true,
            $OUTPUT->update_module_button($cm->id, "turnitintooltwo"),
            '');

    // Dropdown to filter by groups.
    $groupmode = groups_get_activity_groupmode($cm);
    if ($groupmode) {
        groups_get_activity_group($cm, true);
        groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/turnitintooltwo/view.php?id='.$id.'&do='.$do);
    }

    $turnitintooltwoview->draw_tool_tab_menu($cm, $do);

    // Show Helpdesk link for tutors if enabled.
    if ($istutor && $config->helpdeskwizard) {
        $helpdesklink = html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/extras.php?id='.$id.'&cmd=supportwizard',
                                            get_string('helpdesklink', 'turnitintooltwo'));

        echo html_writer::tag('p', $helpdesklink);
    }
}

echo html_writer::start_tag('div', array('class' => 'mod_turnitintooltwo'));

// Include the css for if javascript isn't enabled when a student is logged in.
if (!$istutor) {
    $noscriptcss = html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                                        "href" => $CFG->wwwroot."/mod/turnitintooltwo/css/student_noscript.css"));
    echo html_writer::tag('noscript', $noscriptcss);
}

if (!is_null($notice)) {
    echo $turnitintooltwoview->show_notice($notice);
}

// Show a warning (and hide the rest of the output) if javascript is not enabled while a tutor is logged in.
if ($istutor) {
    echo html_writer::tag('noscript', get_string('noscript', 'turnitintooltwo'), array("class" => "warning"));
}
// Determine if javascript is required and apply class which will hide/show appropriate content.
$class = ($istutor) ? "js_required" : "";

echo html_writer::start_tag("div", array("class" => $class));
echo html_writer::tag("div", $viewcontext, array("id" => "view_context"));

$course = $turnitintooltwoassignment->get_course_data($turnitintooltwoassignment->turnitintooltwo->course);

switch ($do) {
    case "submission_success":
        $digitalreceipt = $turnitintooltwoview->show_digital_receipt($_SESSION["digital_receipt"]);
        if ($viewcontext == "box_solid") {
            $digitalreceipt = html_writer::tag("div", $digitalreceipt, array("id" => "box_receipt"));
        }
        echo $digitalreceipt;
        unset($_SESSION["digital_receipt"]);
        break;

    case "submission_failure":

        $output = $OUTPUT->box($OUTPUT->pix_icon('icon', get_string('turnitin', 'turnitintooltwo'),
                                                    'mod_turnitintooltwo'), 'centered_div');

        $output .= html_writer::tag("div", $_SESSION["digital_receipt"]["message"], array("class" => "general_warning"));
        if ($viewcontext == "box_solid") {
            $output = html_writer::tag("div", $output, array("class" => "submission_failure_msg"));
        }
        echo $output;
        unset($_SESSION["digital_receipt"]);
        break;

    case "digital_receipt":
        $submissionid = required_param('submissionid', PARAM_INT);
        $submission = new turnitintooltwo_submission($submissionid, 'turnitin');

        if ($istutor || $USER->id == $submission->userid) {
            $table = new html_table();
            $table->data = array(
                array(get_string('submissionauthor', 'turnitintooltwo'), $submission->firstname . ' ' . $submission->lastname),
                array(get_string('turnitinpaperid', 'turnitintooltwo') . ' <small>(' . get_string('refid', 'turnitintooltwo') . ')</small>', $submissionid),
                array(get_string('submissiontitle', 'turnitintooltwo'), $submission->submission_title),
                array(get_string('receiptassignmenttitle', 'turnitintooltwo'), $turnitintooltwoassignment->turnitintooltwo->name),
                array(get_string('submissiondate', 'turnitintooltwo'), date("d/m/y, H:i", $submission->submission_modified))
            );

            $digitalreceipt = $OUTPUT->pix_icon('tii-logo', get_string('turnitin', 'turnitintooltwo'), 'mod_turnitintooltwo', array('class' => 'logo'));
            $digitalreceipt .= '<h2>'.get_string('digitalreceipt', 'turnitintooltwo').'</h2>';
            $digitalreceipt .= '<p>'.get_string('receiptparagraph', 'turnitintooltwo').'</p>';
            $digitalreceipt .= html_writer::table($table);
            $digitalreceipt .= '<a href="#" id="tii_receipt_print">' . $OUTPUT->pix_icon('printer', get_string('turnitin', 'turnitintooltwo'), 'mod_turnitintooltwo') . ' ' . get_string('print', 'turnitintooltwo') .'</a>';
        } else {
            $digitalreceipt = "";
        }

        echo html_writer::tag("div", $digitalreceipt, array("id" => "tii_digital_receipt_box"));
        break;

    case "submitpaper":
        if ($istutor || (has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id)) &&
                $user == $USER->id)) {
            echo $turnitintooltwoview->show_submission_form($cm, $turnitintooltwoassignment, $part,
                                                            $turnitintooltwofileuploadoptions, "box_solid", $user);
            unset($_SESSION['form_data']);

            // Add loader icon for when iframe refreshes.
            $loadericon = html_writer::tag('i', '', array('class' => 'fa fa-spinner fa-spin fa-5x'));
            $output = html_writer::tag('div', $loadericon, array('id' => 'refresh_loading'));

            // Create div for submitting text.
            $icon = $OUTPUT->pix_icon('icon', get_string('uploadingsubtoturnitin', 'turnitintooltwo'), 'mod_turnitintooltwo');
            $text = html_writer::tag('p', get_string('uploadingsubtoturnitin', 'turnitintooltwo'));
            $loadericon = $OUTPUT->pix_icon('loader-lrg', get_string('uploadingsubtoturnitin', 'turnitintooltwo'),
                                                    'mod_turnitintooltwo');

            // Add loader icon and text for submission.
            $output .= html_writer::tag('div', $icon.$text.$loadericon, array('id' => 'submitting_loader'));

        } else {
            $output = html_writer::tag("div", get_string('permissiondeniederror', 'turnitintooltwo'), array("id" => "box_receipt"));
        }

        echo $output;
        break;

    case "export_pdfs":
        $submissionids = array();
        $downloadtype = "pdf_zip";
        foreach ($_REQUEST as $k => $v) {
            if (strstr($k, "submission_id") !== false) {
                $submissionids[] = (int)$v;
                $downloadtype = "gmpdf_zip";
            }
        }

        if ($istutor) {
            $user = new turnitintooltwo_user($USER->id, "Instructor");
            echo html_writer::tag("div", $turnitintooltwoview->output_download_launch_form($downloadtype, $user->tii_user_id,
                                                    $parts[$part]->tiiassignid, $submissionids), array("class" => "launch_form"));
        }
        break;

    case "rubricview":
        if (has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id))) {
            $user = new turnitintooltwo_user($USER->id, "Learner");
            $course = $turnitintooltwoassignment->get_course_data($turnitintooltwoassignment->turnitintooltwo->course);
            $user->join_user_to_class($course->turnitin_cid);

            echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('rubric_view', 'Learner',
                                                    $parts[$part]->tiiassignid), array("class" => "launch_form"));
        }
        break;

    case "loadmessages":
        if ($istutor || has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id))) {
            echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('messages_inbox', $userrole),
                                                    array("id" => "inbox_form"));
        }
        break;

    case "peermarkmanager":
        if ($istutor) {
            echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('peermark_manager', 'Instructor',
                                                    $parts[$part]->tiiassignid), array("class" => "launch_form"));
        }
        break;

    case "peermarkreviews":
        if ($istutor || has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id))) {
            echo html_writer::tag("div", $turnitintooltwoview->output_lti_form_launch('peermark_reviews', $userrole,
                                                    $parts[$part]->tiiassignid), array("class" => "launch_form"));
        }
        break;

    case "origreport":
    case "grademark":
    case "downloadoriginal":
    case "default":
        $submissionid = required_param('submissionid', PARAM_INT);
        $user = new turnitintooltwo_user($USER->id, $userrole);

        echo html_writer::tag("div", $turnitintooltwoview->output_dv_launch_form($do, $submissionid, $user->tii_user_id, $userrole),
                                                                                array("class" => "launch_form"));
        if ($do === "origreport") {
            $submission = new turnitintooltwo_submission($submissionid, 'turnitin');
            turnitintooltwo_add_to_log($turnitintooltwoassignment->turnitintooltwo->course, "view submission", 'view.php?id='.$cm->id, get_string('viewsubmissiondesc', 'turnitintooltwo') . " '$submission->submission_title'", $cm->id, $submission->userid);
        }
        break;

    case "submissions":
        // Output a link for the student to accept the turnitin licence agreement.
        $noscriptula = "";
        $ula = "";

        if (!$istutor) {
            $eulaaccepted = false;
            $user = new turnitintooltwo_user($USER->id, $userrole);
            $coursedata = $turnitintooltwoassignment->get_course_data($turnitintooltwoassignment->turnitintooltwo->course);
            $user->join_user_to_class($coursedata->turnitin_cid);
            $eulaaccepted = ($user->user_agreement_accepted != 1) ? $user->get_accepted_user_agreement() : $user->user_agreement_accepted;

            // Check if the submitting user has accepted the EULA
            if ($eulaaccepted != 1) {
                // Moodle strips out form and script code for forum posts so we have to do the Eula Launch differently.
                $ula_link = html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/extras.php?cmid='.$cm->id.'&cmd=useragreement&view_context=box_solid',
                                        html_writer::tag('i', '', array('class' => 'tiiicon icon-warn icon-2x turnitin_ula_warn')) .'</br></br>'.
                                        get_string('turnitinula', 'turnitintooltwo')." ".get_string('turnitinula_btn', 'turnitintooltwo'),
                                        array("class" => "turnitin_eula_link"));

                $eulaignoredclass = ($eulaaccepted == 0) ? ' turnitin_ula_ignored' : '';
                $ula = html_writer::tag('div', $ula_link, array('class' => 'turnitin_ula js_required'.$eulaignoredclass,
                                            'data-userid' => $user->id));

                $noscriptula = html_writer::tag('noscript',
                                turnitintooltwo_view::output_dv_launch_form("useragreement", 0, $user->tii_user_id,
                                    "Learner", get_string('turnitinula', 'turnitintooltwo'), false)." ".
                                        get_string('noscriptula', 'turnitintooltwo'),
                                            array('class' => 'warning turnitin_ula_noscript'));
                echo $ula.$noscriptula;
            }
        }

        $listsubmissionsdesc = ($istutor) ? "listsubmissionsdesc" : "listsubmissionsdesc_student";
        turnitintooltwo_add_to_log($turnitintooltwoassignment->turnitintooltwo->course, "list submissions", 'view.php?id='.$cm->id, get_string($listsubmissionsdesc, 'turnitintooltwo') . ": $course->id", $cm->id);

        if (!$istutor && !has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id))) {
            turnitintooltwo_print_error('permissiondeniederror', 'turnitintooltwo');
            exit();
        }

        $turnitintooltwouser = new turnitintooltwo_user($USER->id, $userrole);

        // Get course data.
        if ($istutor) {
            $course = $turnitintooltwoassignment->get_course_data($turnitintooltwoassignment->turnitintooltwo->course);
        }

        // Update Assignment from Turnitin on first visit.
        if (empty($_SESSION["assignment_updated"][$turnitintooltwoassignment->turnitintooltwo->id])) {
            $turnitintooltwoassignment->update_assignment_from_tii();
            // Enrol the tutor on the class.
            if ($istutor) {
                $turnitintooltwouser->join_user_to_class($course->turnitin_cid);
            }
        }

        // Show submission failure if this has been a manual submission.
        if (isset($_SESSION["digital_receipt"]["success"]) && $_SESSION["digital_receipt"]["success"] == false) {
            $output = html_writer::tag("div", $_SESSION["digital_receipt"]["message"],
                                    array("class" => "general_warning manual_submission_failure_msg"));
            if ($viewcontext == "box_solid") {
                $output = html_writer::tag("div", $output, array("class" => "submission_failure_msg"));
            }
            echo $output;
            unset($_SESSION["digital_receipt"]);
        }

        // Show duplicate assignment warning if applicable.
        if ($istutor) {
            echo $turnitintooltwoview->show_duplicate_assignment_warning($turnitintooltwoassignment, $parts);
        }

        if (has_capability('mod/turnitintooltwo:submit', context_module::instance($cm->id)) &&
                !empty($_SESSION["digital_receipt"]) && !isset($_SESSION["digital_receipt"]["is_manual"])) {
            echo $turnitintooltwoview->show_digital_receipt($_SESSION["digital_receipt"]);
            unset($_SESSION["digital_receipt"]);
        }

        // Initialise inbox, if a student is logged in then populate it also incase they have no javascript.
        echo $turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $parts, $turnitintooltwouser);

        // Show submission form for students (only shows if they don't have javascript enabled).
        if (!$istutor) {
            echo html_writer::start_tag("div", array("class" => "js_hide"));
            echo $turnitintooltwoview->show_submission_form($cm, $turnitintooltwoassignment, $part,
                                                    $turnitintooltwofileuploadoptions, "window", $USER->id);
            echo html_writer::end_tag("div");
        } else if ($turnitintooltwoassignment->turnitintooltwo->anon > 0) {
            // Put the html for unanonymising a submission below the form for including in lightbox.
            echo $turnitintooltwoview->show_unanonymise_form();
        }
        break;

    case "students":
    case "tutors":
        if (!$istutor) {
            turnitintooltwo_print_error('permissiondeniederror', 'turnitintooltwo');
            exit();
        }
        $introtext = ($do == "tutors") ? get_string('turnitintutors_desc', 'turnitintooltwo') :
                                            get_string('turnitinstudents_desc', 'turnitintooltwo');
        echo $OUTPUT->box($introtext, 'generalbox boxaligncenter', 'general');

        $memberrole = ($do == "tutors") ? 'Instructor' : 'Learner';
        echo $turnitintooltwoview->init_tii_member_by_role_table($cm, $turnitintooltwoassignment, $memberrole);
        if ($do == "tutors") {
            $tutors = $turnitintooltwoassignment->get_tii_users_by_role("Instructor", "mdl");
            echo $turnitintooltwoview->show_add_tii_tutors_form($cm, $tutors);
        }
        break;

    case "emailnonsubmittersform":
            if (!$istutor) {
                turnitintooltwo_print_error('permissiondeniederror', 'turnitintooltwo');
                exit();
            }

            $output = '';

            if (isset($_SESSION["embeddednotice"])) {
                $output = html_writer::tag("div", $_SESSION["embeddednotice"]["message"], array('class' => 'general_warning'));
                unset($_SESSION["embeddednotice"]);
            }

            $elements = array();
            $elements[] = array('header', 'nonsubmitters_header', get_string('emailnonsubmitters', 'turnitintooltwo'));
            $elements[] = array('static', 'nonsubmittersformdesc', get_string('nonsubmittersformdesc', 'turnitintooltwo'), '', '');
            $elements[] = array('text', 'nonsubmitters_subject', get_string('nonsubmitterssubject', 'turnitintooltwo'), '', '',
                                    'required', get_string('nonsubmitterssubjecterror', 'turnitintooltwo'), PARAM_TEXT);
            $elements[] = array('textarea', 'nonsubmitters_message', get_string('nonsubmittersmessage', 'turnitintooltwo'), '', '',
                                    'required', get_string('nonsubmittersmessageerror', 'turnitintooltwo'), PARAM_TEXT);
            $elements[] = array('advcheckbox', 'nonsubmitters_sendtoself', get_string('nonsubmitterssendtoself', 'turnitintooltwo'), '', array(0, 1));
            $customdata["checkbox_label_after"] = true;

            $elements[] = array('hidden', 'id', $cm->id);
            $elements[] = array('hidden', 'part', $part);
            $elements[] = array('hidden', 'action', 'emailnonsubmitters');
            $elements[] = array('submit', 'send_email', get_string('nonsubmitterssubmit', 'turnitintooltwo'));

            $customdata["elements"] = $elements;
            $customdata["hide_submit"] = true;
            $customdata["disable_form_change_checker"] = true;

            $optionsform = new turnitintooltwo_form('', $customdata);

            echo html_writer::tag('div', $output.$optionsform->display(), array('class' => 'nonsubmittersform'));
            unset($_SESSION['form_data']);
            break;

    case "emailsent":
        echo html_writer::tag('div', get_string('nonsubmittersformsuccess', 'turnitintooltwo'), array('class' => 'nonsubmittersformsuccessmsg'));
        break;
}

echo html_writer::end_tag("div");
echo html_writer::end_tag("div");
echo $OUTPUT->footer();

// This comment is here as it is useful for product support.
$partsstring = "(";
foreach ($parts as $part) {
    $partsstring .= ($partsstring != "(") ? " | " : "";
    $partsstring .= $part->partname.': '.$part->tiiassignid;
}
$partsstring .= ")";
$courseID = $course->turnitin_cid;
echo '<!-- Turnitin Moodle Direct Version: '.turnitintooltwo_get_version().' - course ID: '.$courseID.' - '.$partsstring.' -->';