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

define('AJAX_SCRIPT', 1);

require_once(__DIR__."/../../config.php");
require_once(__DIR__."/lib.php");
require_once(__DIR__."/turnitintooltwo_view.class.php");

require_login();
$action = required_param('action', PARAM_ALPHAEXT);

/**
 * Calculate the runtime based on a given start time.
 *
 * @param float $startTime Start time in microseconds.
 */
function calcRunTime($startTime) {
    return number_format(( microtime(true) - $startTime), 4);
}

switch ($action) {
    case "check_anon":
        $assignmentid = required_param('assignment', PARAM_INT);
        $partid = required_param('part', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $part = $turnitintooltwoassignment->get_part_details($partid);

        $anonData = array(
            'anon' => $turnitintooltwoassignment->turnitintooltwo->anon,
            'unanon' => $part->unanon,
            'submitted' => $part->submitted
        );
        echo json_encode($anonData);
        break;

    case "edit_field":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $partid = required_param('pk', PARAM_INT);
        $return = array();

        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            $fieldname = required_param('name', PARAM_ALPHA);
            switch ($fieldname) {
                case 'partname':
                    $fieldvalue = required_param('value', PARAM_TEXT);
                    break;

                case "maxmarks":
                    $fieldvalue = required_param('value', PARAM_RAW);
                    break;

                case "dtstart":
                case "dtdue":
                case "dtpost":
                    $fieldvalue = required_param('value', PARAM_RAW);
                    // We need to work out the users timezone or GMT offset.
                    $usertimezone = get_user_timezone();

                    if (is_numeric($usertimezone)) {
                        if ($usertimezone > 13) {
                            $usertimezone = "";
                        } else if ($usertimezone <= 13 && $usertimezone > 0) {
                            $usertimezone = "GMT+$usertimezone";
                        } else if ($usertimezone < 0) {
                            $usertimezone = "GMT$usertimezone";
                        } else {
                            $usertimezone = 'GMT';
                        }
                    }

                    $fieldvalue = strtotime($fieldvalue.' '.$usertimezone);
                    break;
            }

            $return = $turnitintooltwoassignment->edit_part_field($partid, $fieldname, $fieldvalue);
        } else {
            $return["aaData"] = '';
        }

        $partdetails = $turnitintooltwoassignment->get_parts();

        $return['export_option'] = ($turnitintooltwoassignment->turnitintooltwo->anon == 0 || time() > $partdetails[$partid]->dtpost) ?
                                    "tii_export_options_show" : "tii_export_options_hide";

        echo json_encode($return);
        break;

    case "useragreement":
        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:read', context_module::instance($cm->id))) {
            $user = new turnitintooltwo_user($USER->id, "Learner");
            echo turnitintooltwo_view::output_dv_launch_form("useragreement", 0, $user->tii_user_id, "Learner", "Submit", true);
        }
        break;

    case "acceptuseragreement":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $message = optional_param('message', '', PARAM_ALPHAEXT);

        // Get the id from the turnitintooltwo_users table so we can update
        $turnitin_user = $DB->get_record('turnitintooltwo_users', array('userid' => $USER->id));

        // Build user object for update
        $eula_user = new object();
        $eula_user->id = $turnitin_user->id;
        $eula_user->user_agreement_accepted = 0;
        if ($message == 'turnitin_eula_accepted') {
            $eula_user->user_agreement_accepted = 1;
        }

        // Update the user using the above object
        $DB->update_record('turnitintooltwo_users', $eula_user, $bulk=false);
        break;

    case "downloadoriginal":
    case "default":
    case "origreport":
    case "grademark":
        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:read', context_module::instance($cm->id))) {
            $submissionid = required_param('submission', PARAM_INT);
            $userrole = (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) ? 'Instructor' : 'Learner';

            $user = new turnitintooltwo_user($USER->id, $userrole);

            $launch_form = turnitintooltwo_view::output_dv_launch_form($action, $submissionid, $user->tii_user_id, $userrole, '');
            if ($action == 'downloadoriginal') {
                echo $launch_form;
            } else {
                $launch_form = html_writer::tag("div", $launch_form, array('style' => 'display: none'));
                echo json_encode($launch_form);
            }
        }
        break;

    case "orig_zip":
    case "xls_inbox":
    case "origchecked_zip":
    case "gmpdf_zip":

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {

            $partid = optional_param('part', 0, PARAM_INT);
            if ($partid != 0 && ($action == "origchecked_zip" || $action == "gmpdf_zip")) {
                $partdetails = $turnitintooltwoassignment->get_part_details($partid, "moodle");
                $partid = $partdetails->tiiassignid;
            }

            $user = new turnitintooltwo_user($USER->id, 'Instructor');
            $user->edit_tii_user();

            if ($action == "orig_zip") {
                $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
                $partdetails = $turnitintooltwoassignment->get_part_details($partid, "turnitin");
                $submissions = $turnitintooltwoassignment->get_submissions($cm, $partdetails->id);

                $submissionids = array();
                foreach ($submissions[$partdetails->id] as $k => $v) {
                    if (!empty($v->submission_objectid)) {
                        $submissionids[] = $v->submission_objectid;
                    }
                }
            } else {
                $submissionids = optional_param_array('submission_ids', array(), PARAM_INT);
            }

            echo turnitintooltwo_view::output_download_launch_form($action, $user->tii_user_id, $partid, $submissionids);
        }
        break;

    case "get_users":
        $PAGE->set_context(context_system::instance());
        if (is_siteadmin()) {
            echo json_encode(turnitintooltwo_getusers());
        } else {
            throw new moodle_exception('accessdenied', 'admin');
        }
        break;

    case "initialise_redraw":
        $PAGE->set_context(context_system::instance());
        $return["aaData"] = array();

        echo json_encode($return);
        break;

    case "get_submissions":

        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));
        $return = array();

        if (has_capability('mod/turnitintooltwo:read', context_module::instance($cm->id))) {
            $partid = required_param('part', PARAM_INT);
            $refreshrequested = required_param('refresh_requested', PARAM_INT);
            $start = required_param('start', PARAM_INT);
            $total = required_param('total', PARAM_INT);
            $parts = $turnitintooltwoassignment->get_parts();
            $updatefromtii = ($refreshrequested || $turnitintooltwoassignment->turnitintooltwo->autoupdates == 1) ? 1 : 0;

            if ($updatefromtii && $start == 0) {
                $turnitintooltwoassignment->get_submission_ids_from_tii($parts[$partid]);
                $total = $_SESSION["TiiSubmissions"][$partid];
            }

            if ($start < $total && $updatefromtii) {
                $turnitintooltwoassignment->refresh_submissions($parts[$partid], $start);
            }

            $PAGE->set_context(context_module::instance($cm->id));
            $turnitintooltwoview = new turnitintooltwo_view();

            $return["aaData"] = $turnitintooltwoview->get_submission_inbox($cm, $turnitintooltwoassignment, $parts, $partid, $start);
            $totalsubmitters = $DB->count_records('turnitintooltwo_submissions',
                                                    array('turnitintooltwoid' => $turnitintooltwoassignment->turnitintooltwo->id,
                                                            'submission_part' => $partid));
            $return["end"] = $start + TURNITINTOOLTWO_SUBMISSION_GET_LIMIT;
            $return["total"] = $_SESSION["num_submissions"][$partid];
            $return["nonsubmitters"] = $return["total"] - $totalsubmitters;

            // Remove any leftover submissions from session and update grade timestamp/
            if ($return["end"] >= $return["total"]) {
                unset($_SESSION["submissions"][$partid]);

                $updatepart = new stdClass();
                $updatepart->id = $partid;
                // Set timestamp to 10 minutes ago to account for time taken to complete (somewhat exagerrated).
                $updatepart->gradesupdated = time()-(60*10);
                $DB->update_record('turnitintooltwo_parts', $updatepart);
            }
        } else {
            $return["aaData"] = '';
        }

        echo json_encode($return);
        break;

    case "refresh_user_messages":
        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            $turnitintooltwouser = new turnitintooltwo_user($USER->id, 'Instructor');
            $turnitintooltwouser->set_user_values_from_tii();

            echo $turnitintooltwouser->get_user_messages();
        } else {
            echo 0;
        }
        break;

    case "refresh_peermark_assignments":

        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:read', context_module::instance($cm->id))) {
            $partid = required_param('part', PARAM_INT);
            $refreshrequested = optional_param('refresh_requested', 0, PARAM_INT);
            $partdetails = $turnitintooltwoassignment->get_part_details($partid);

            if ($refreshrequested) {
                $turnitintooltwoassignment->update_assignment_from_tii(array($partdetails->tiiassignid));
                $partdetails = $turnitintooltwoassignment->get_part_details($partid);
            }

            $PAGE->set_context(context_module::instance($cm->id));

            $turnitintooltwoview = new turnitintooltwo_view();
            $peermarkdata['peermark_table'] = $turnitintooltwoview->show_peermark_assignment($partdetails->peermark_assignments);
            $peermarkdata['no_of_peermarks'] = count($partdetails->peermark_assignments);
            $peermarkdata['peermarks_active'] = false;
            foreach ($partdetails->peermark_assignments as $peermarkassignment) {
                if (time() > $peermarkassignment->dtstart) {
                    $peermarkdata['peermarks_active'] = true;
                    break;
                }
            }
            echo json_encode($peermarkdata);
        }
        break;

    case "refresh_submission_row":

        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:read', context_module::instance($cm->id))) {
            $partid = required_param('part', PARAM_INT);
            $userid = required_param('user', PARAM_INT);
            $istutor = (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) ? true : false;

            $parts = $turnitintooltwoassignment->get_parts();

            // Get the id of the submission in the row and update it from Turnitin then get the new details.
            $submission = $turnitintooltwoassignment->get_user_submissions($userid, $assignmentid, $partid);
            $submissionid = current(array_keys($submission));

            if (!empty($submissionid)) {
                $submission = new turnitintooltwo_submission($submissionid);
                $submission->update_submission_from_tii(true);

                // Get the submission details again in case the submission has been transferred within Turnitin.
                $submission = $turnitintooltwoassignment->get_user_submissions($userid, $assignmentid, $partid);
                $submissionid = current(array_keys($submission));
            }

            $submission = new turnitintooltwo_submission($submissionid);
            if (empty($submissionid)) {
                $user = new turnitintooltwo_user($userid, 'Learner', false);

                $submission->firstname = $user->firstname;
                $submission->lastname = $user->lastname;
                $submission->fullname = $user->fullname;
                $submission->userid = $user->id;
            }

            $useroverallgrades = array();

            $PAGE->set_context(context_module::instance($cm->id));

            $turnitintooltwoview = new turnitintooltwo_view();
            $submissionrow["submission_id"] = $submission->submission_objectid;
            $submissionrow["row"] = $turnitintooltwoview->get_submission_inbox_row($cm, $turnitintooltwoassignment, $parts,
                                                                                $partid, $submission, $useroverallgrades,
                                                                                $istutor, 'refresh_row');

            echo json_encode($submissionrow);
        }
        break;

    case "enrol_all_students":

        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            echo $turnitintooltwoassignment->enrol_all_students($cm);
        }
        break;

    case "refresh_rubric_select":
        $courseid = required_param('course', PARAM_INT);
        $assignmentid = required_param('assignment', PARAM_INT);
        $modulename = required_param('modulename', PARAM_ALPHA);

        $PAGE->set_context(context_course::instance($courseid));

        if (has_capability('moodle/course:update', context_course::instance($courseid))) {
            // Set Rubric options to instructor rubrics.
            $instructor = new turnitintooltwo_user($USER->id, 'Instructor');
            $instructor->set_user_values_from_tii();
            $instructorrubrics = $instructor->get_instructor_rubrics();

            $options = array('' => get_string('norubric', 'turnitintooltwo')) + $instructorrubrics;

            // Get rubrics that are shared on the Turnitin account.
            if ($modulename == "turnitintooltwo") {
                $turnitinclass = new turnitintooltwo_class($courseid);
            } else {
                require_once($CFG->dirroot.'/plagiarism/turnitin/lib.php');
                $turnitinclass = new turnitin_class($courseid);
            }
            $turnitinclass->read_class_from_tii();
            $options = $options + $turnitinclass->sharedrubrics;

            // Get assignment details.
            if (!empty($assignmentid)) {
                if ($modulename == "turnitintooltwo") {
                    $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
                } else {
                    $pluginturnitin = new plagiarism_plugin_turnitin();
                    $cm = get_coursemodule_from_instance($modulename, $assignmentid);
                    $plagiarismsettings = $pluginturnitin->get_settings($cm->id);
                }
            }

            // Add in selected rubric if it belongs to another instructor.
            if (!empty($assignmentid)) {
                if ($modulename == "turnitintooltwo") {
                    if (!empty($turnitintooltwoassignment->turnitintooltwo->rubric)) {
                        $options[$turnitintooltwoassignment->turnitintooltwo->rubric] =
                                                    (isset($options[$turnitintooltwoassignment->turnitintooltwo->rubric])) ?
                                                                $options[$turnitintooltwoassignment->turnitintooltwo->rubric] :
                                                                get_string('otherrubric', 'turnitintooltwo');
                    }
                } else {
                    if (!empty($plagiarismsettings["plagiarism_rubric"])) {
                        $options[$plagiarismsettings["plagiarism_rubric"]] =
                                                    (isset($options[$plagiarismsettings["plagiarism_rubric"]])) ?
                                                                    $options[$plagiarismsettings["plagiarism_rubric"]] :
                                                                    get_string('otherrubric', 'turnitintooltwo');
                    }
                }
            }
        } else {
            $options = array();
        }
        echo json_encode($options);
        break;

    case "get_files":
        $PAGE->set_context(context_system::instance());
        if (is_siteadmin()) {
            $modules = $DB->get_record('modules', array('name' => 'turnitintooltwo'));
            echo json_encode(turnitintooltwo_getfiles($modules->id));
        }
        break;

    case "get_members":
        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));

        $return["aaData"] = array();
        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            $role = required_param('role', PARAM_ALPHA);
            $members = $turnitintooltwoassignment->get_tii_users_by_role($role);

            $PAGE->set_context(context_module::instance($cm->id));
            $turnitintooltwoview = new turnitintooltwo_view();
            $return["aaData"] = $turnitintooltwoview->get_tii_members_by_role($cm, $turnitintooltwoassignment, $members, $role);
        }
        echo json_encode($return);
        break;

    case "reveal_submission_name":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);
        $PAGE->set_context(context_module::instance($cm->id));
        $return = array("status" => "fail", "msg" => get_string('unanonymiseerror', 'turnitintooltwo'));

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            $submissionid = required_param('submission_id', PARAM_INT);
            $reason = optional_param('reason', get_string('noreason', 'turnitintooltwo'), PARAM_TEXT);

            $turnitintooltwosubmission = new turnitintooltwo_submission($submissionid, "turnitin");
            if ($turnitintooltwosubmission->unanonymise_submission($reason)) {
                if ($turnitintooltwosubmission->userid == 0) {

                    $tmpuser = new stdClass();
                    $tmpuser->firstname = $turnitintooltwosubmission->nmfirstname;
                    $tmpuser->lastname = $turnitintooltwosubmission->nmlastname;

                    $return["name"] = fullname($tmpuser);
                } else {
                    $user = new turnitintooltwo_user($turnitintooltwosubmission->userid);
                    $return["name"] = fullname($user);
                }
                $return["status"] = "success";
                $return["userid"] = $turnitintooltwosubmission->userid;
                $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
                $return["courseid"] = $turnitintooltwoassignment->turnitintooltwo->course;
                $return["msg"] = "";
            }

            // Refresh submission and save.
            $turnitintooltwosubmission->update_submission_from_tii();
        }

        echo json_encode($return);
        break;

    case "search_classes":
        $PAGE->set_context(context_system::instance());
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $coursetitle = optional_param('course_title', '', PARAM_TEXT);
        $courseintegration = optional_param('course_integration', '', PARAM_ALPHANUM);
        $courseenddate = optional_param('course_end_date', null, PARAM_TEXT);
        $requestsource = optional_param('request_source', 'mod', PARAM_TEXT);

        $modules = $DB->get_record('modules', array('name' => 'turnitintooltwo'));

        $return = turnitintooltwo_get_courses_from_tii($tiiintegrationids, $coursetitle, $courseintegration, $courseenddate, $requestsource);
        echo json_encode($return);
        break;

    case "create_courses":
        $PAGE->set_context(context_system::instance());
        set_time_limit(0);
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        if (has_capability('moodle/course:create', context_system::instance())) {
            $coursecategory = optional_param('course_category', 0, PARAM_INT);
            $createassignments = optional_param('create_assignments', 0, PARAM_INT);
            $classids = required_param('class_ids', PARAM_SEQUENCE);
            $classids = explode(",", $classids);

            $i = 0;
            foreach ($classids as $tiiclassid) {
                $tiicoursename = $_SESSION['tii_classes'][$tiiclassid];
                $coursename = $tiicoursename;

                $course = turnitintooltwo_assignment::create_moodle_course($tiiclassid, $tiicoursename, $coursename, $coursecategory);
                if ($createassignments == 1 && !empty($course)) {
                    $return = turnitintooltwo_get_assignments_from_tii($tiiclassid, "raw");

                    foreach ($return as $assignment) {
                        turnitintooltwo_assignment::create_migration_assignment(array($assignment["tii_id"]),
                                                                                $course->id, $assignment["tii_title"]);
                    }
                }
                $i++;
            }

            $result = new stdClass();
            $result->completed = $i;
            $result->total = count($classids);
            $msg = get_string('recreatemulticlassescomplete', 'turnitintooltwo', $result);
        } else {
            $msg = get_string('nopermissions', 'error', get_string('course:create', 'role'));
        }

        echo $msg;
        break;

    case "create_course":
        $PAGE->set_context(context_system::instance());
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        if (has_capability('moodle/course:create', context_system::instance())) {
            $tiicoursename = optional_param('tii_course_name', get_string('defaultcoursetiititle', 'turnitintooltwo'), PARAM_TEXT);
            $coursecategory = optional_param('course_category', 0, PARAM_INT);
            $tiicourseid = optional_param('tii_course_id', 0, PARAM_INT);
            $coursename = urldecode(optional_param('course_name', '', PARAM_TEXT));
            if (empty($coursename)) {
                $coursename = get_string('defaultcoursetiititle', 'turnitintooltwo')." (".$tiicourseid.")";
            }

            $course = turnitintooltwo_assignment::create_moodle_course($tiicourseid, urldecode($tiicoursename),
                                                                        $coursename, $coursecategory);

            $newcourse = array('courseid' => $course->id, 'coursename' => $course->fullname);
            echo json_encode($newcourse);
        } else {
            throw new moodle_exception('nopermissions', 'error', '', get_string('course:create', 'role'));
        }
        break;

    case "link_course":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        if (has_capability('moodle/course:update', context_system::instance())) {
            $tiicoursename = optional_param('tii_course_name', get_string('defaultcoursetiititle', 'turnitintooltwo'), PARAM_TEXT);
            $tiicourseid = optional_param('tii_course_id', 0, PARAM_INT);
            $coursetolink = optional_param('course_to_link', 0, PARAM_INT);

            $turnitincourse = new stdClass();
            $turnitincourse->courseid = $coursetolink;
            $turnitincourse->ownerid = $USER->id;
            $turnitincourse->turnitin_cid = $tiicourseid;
            $turnitincourse->turnitin_ctl = urldecode($tiicoursename);
            $turnitincourse->course_type = 'TT';

            $PAGE->set_context(context_system::instance($coursetolink));

            if (!$insertid = $DB->insert_record('turnitintooltwo_courses', $turnitincourse)) {
                echo "0";
            } else {
                $course = $DB->get_record("course", array("id" => $coursetolink), 'fullname');
                $newcourse = array('courseid' => $coursetolink, 'coursename' => $course->fullname);

                echo json_encode($newcourse);
            }
        } else {
            throw new moodle_exception('nopermissions', 'error', '', get_string('course:update', 'role'));
        }
        break;

    case "get_assignments":
        set_time_limit(0);
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $PAGE->set_context(context_system::instance());

        if (has_capability('moodle/course:update', context_system::instance())) {
            $tiicourseid = required_param('tii_course_id', PARAM_INT);
            $return = turnitintooltwo_get_assignments_from_tii($tiicourseid, "json");
            $return["number_of_assignments"] = count($return["aaData"]);
        } else {
            $return["number_of_assignments"] = 0;
        }
        echo json_encode($return);
        break;

    case "create_assignment":
        set_time_limit(0);
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        if (has_capability('mod/turnitintooltwo:addinstance', context_system::instance())) {
            $partids = required_param('parts', PARAM_SEQUENCE);
            $courseid = optional_param('course_id', 0, PARAM_INT);
            $assignmentname = optional_param('assignment_name', '', PARAM_TEXT);
            $assignmentname = (empty($assignmentname)) ? get_string('defaultassignmenttiititle', 'turnitintooltwo') :
                                                                urldecode($assignmentname);

            $partids = explode(',', $partids);
            if (is_array($partids)) {
                turnitintooltwo_assignment::create_migration_assignment($partids, $courseid, $assignmentname);
            }
        }
        break;

    case "edit_course_end_date":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        if (has_capability('moodle/course:update', context_system::instance())) {
            $tiicourseid = required_param('tii_course_id', PARAM_INT);
            $tiicoursetitle = required_param('tii_course_title', PARAM_TEXT);
            $enddated = required_param('end_date_d', PARAM_INT);
            $enddatem = required_param('end_date_m', PARAM_INT);
            $enddatey = required_param('end_date_y', PARAM_INT);

            $enddate = mktime(00, 00, 00, $enddatem, $enddated, $enddatey);

            $PAGE->set_context(context_system::instance());

            if (turnitintooltwo_assignment::edit_tii_course_end_date($tiicourseid, $tiicoursetitle, $enddate)) {
                $return["status"] = "success";
                $return["end_date"] = userdate($enddate, get_string('strftimedate', 'langconfig'));
            } else {
                $return["status"] = "fail";
                $return["msg"] = get_string('unanonymiseerror', 'turnitintooltwo');
            }
        } else {
            $return["status"] = "fail";
            $return["msg"] = get_string('nopermissions', 'error', get_string('course:update', 'role'));
        }
        echo json_encode($return);
        break;

    case "check_upgrade":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }
        $data = '';
        $current_version = required_param('current_version', PARAM_INT);

        $PAGE->set_context(context_system::instance());

        if (is_siteadmin()) {
            $data = turnitintooltwo_updateavailable($current_version);
        }
        echo json_encode($data);
        break;

    case "test_connection":
        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }
        $data = array("connection_status" => "fail", "msg" => get_string('connecttestcommerror', 'turnitintooltwo'));

        $PAGE->set_context(context_system::instance());
        if (is_siteadmin()) {
            // Initialise API connection.

            $account_id = required_param('account_id', PARAM_RAW);
            $account_shared = required_param('account_shared', PARAM_RAW);
            $url = required_param('url', PARAM_RAW);

            $turnitincomms = new turnitintooltwo_comms($account_id, $account_shared, $url);

            $testingconnection = true; // Provided by Androgogic to override offline mode for testing connection.

            // We only want an API log entry for this if diagnostic mode is set to Debugging
            if (empty($config)) {
                $config = turnitintooltwo_admin_config();
            }
            if ($config->enablediagnostic != 2) {
                $turnitincomms->setDiagnostic(0);
            }

            $tiiapi = $turnitincomms->initialise_api($testingconnection);

            $class = new TiiClass();
            $class->setTitle('Test finding a class to see if connection works');

            try {
                $response = $tiiapi->findClasses($class);
                $data["connection_status"] = "success";
                $data["msg"] = get_string('connecttestsuccess', 'turnitintooltwo');
            } catch (Exception $e) {
                $turnitincomms->handle_exceptions($e, 'connecttesterror', false);
            }
        }
        echo json_encode($data);
        break;

    case "submit_nothing":

        if (!confirm_sesskey()) {
            throw new moodle_exception('invalidsesskey', 'error');
        }

        $assignmentid = required_param('assignment', PARAM_INT);
        $turnitintooltwoassignment = new turnitintooltwo_assignment($assignmentid);
        $cm = get_coursemodule_from_instance("turnitintooltwo", $assignmentid);

        $PAGE->set_context(context_system::instance());

        if (has_capability('mod/turnitintooltwo:grade', context_module::instance($cm->id))) {
            $partid = required_param('part', PARAM_INT);
            $userid = required_param('user', PARAM_INT);
            $turnitintooltwosubmission = new turnitintooltwo_submission();
            $data = $turnitintooltwosubmission->do_tii_nothing_submission($cm, $turnitintooltwoassignment, $partid, $userid);
        } else {
            header("HTTP/1.0 403 Forbidden");
            exit();
        }
        if ( !is_array( $data ) ) {
            header("HTTP/1.0 400 Bad Request");
            echo $data;
            exit();
        } else {
            echo json_encode($data);
        }
    break;

    case "migration":
        include_once($CFG->dirroot."/course/lib.php");

        $start = required_param('start', PARAM_INT);
        $processAtOnce = required_param('processAtOnce', PARAM_INT);
        $iteration = required_param('iteration', PARAM_INT);
        $trial = required_param('trial', PARAM_INT);
        $doOnce = required_param('doOnce', PARAM_INT);
        $totalToMigrate = required_param('totalToMigrate', PARAM_INT);
        $etd = optional_param('etd', 0, PARAM_INT);

        $data = "";

        // Open our debug log file.
        $debug = fopen(__DIR__."/logs/migrationtool/debug/".date('Y-m-d').".txt", "a");

        // We only want to do this once.
        if ($doOnce) {
            $_SESSION["migrationtool"]["csvname"] = date('Y-m-d_His').' - Migration Status';

            // Print names of all the fields
            $csvexport = fopen(__DIR__."/logs/migrationtool/".$_SESSION["migrationtool"]["csvname"].".csv", "a");
            if ($etd) {
                fputcsv($csvexport, array('Moodle Course ID', 'Turnitin Course ID', 'Course Name', 'TII Assignment ID'));
            } else {
                fputcsv($csvexport, array('Moodle Course ID', 'Turnitin Course ID', 'Course Name', 'Status'));
            }
            fclose($csvexport);

            if (!$trial) {
                fputs($debug, date('H:i:s')." - Begin migration tool (non-trial) run.\r\n");

                // Migrate the users and set flag as
                $turnitintool_users = $DB->get_records('turnitintool_users', NULL, NULL, 'userid, turnitin_uid, turnitin_utp');
                foreach ($turnitintool_users as $turnitintool_user) {
                    unset($turnitintool_user->id);

                    if (!$DB->record_exists("turnitintooltwo_users", array('userid' => $turnitintool_user->userid))) {
                        $DB->insert_record("turnitintooltwo_users", $turnitintool_user);
                    }
                }
                fputs($debug, "+" . calcRunTime($startTime) . " seconds - Users have been migrated.\r\n");

                // Course header once migration has been complete.
                $data .= html_writer::tag('p', get_string('migrationtool_migrated', 'turnitintooltwo'), array('class' => 'courseheader darkgreen'));
            }
            $doOnce = 0;
        }
        if (!$trial) {
            fputs($debug, date('H:i:s')." - Begin Ajax call with the following parameters: Start: ".$start." Process at Once: ".$processAtOnce." Iteration: ".$iteration." Trial: ".$trial." Total To Migrate: ".$totalToMigrate." ETD: ".$etd."\r\n");
        }

        // Initialise the CSV log.
        $csvexport = fopen(__DIR__."/logs/migrationtool/".$_SESSION["migrationtool"]["csvname"].".csv", "a");

        // Get a list of courses with V1 assignments.
        $courses = $DB->get_records_sql("SELECT tc.id, courseid, ownerid, turnitin_ctl, turnitin_cid, fullname
                                         FROM {turnitintool_courses} tc JOIN {course} c
                                         ON c.id = tc.courseid", NULL, $start, $processAtOnce);

        // We'll use this next time round to determine where to start.
        $end = $start + count($courses);

        // Loop through each course and migrate if we can.
        foreach ($courses as $course) {
            if (!$trial) {
                fputs($debug, date('H:i:s')." - Begin migrating a course\r\n");
                $startTime = microtime(true);

                // Begin transaction.
                $transaction = $DB->start_delegated_transaction();
            }

            $v2course = $DB->get_record('turnitintooltwo_courses', array('courseid' => $course->courseid, 'course_type' => 'TT'));

            // Get some values for count checks.
            $numsubmissions = count($DB->get_records_sql("SELECT s.id FROM mdl_turnitintool_submissions s
                                                          JOIN mdl_turnitintool t
                                                          ON t.id = s.turnitintoolid
                                                          WHERE t.course = :course;", array('course' => 2)));
            $numenrolled = count_enrolled_users(context_course::instance($course->id));

            $canMigrate = 0;
            $headerColour = "red";
            if ($numenrolled > 1000) {
                $statusText = "migrationtool_cant_migrate1";
            } else if ($numsubmissions > 10000) {
                $statusText = "migrationtool_cant_migrate2";
            } elseif (isset($v2course->migrated)) {
                $statusText = "migrationtool_cant_migrate3";
            } elseif (($v2course) && (!$etd)) {
                $statusText = "migrationtool_cant_migrate4";
            } else {
                $canMigrate = 1;
                $totalToMigrate++;
                $headerColour = "darkgreen";

                if (!$trial) {
                    $statusText = "migrationtool_migrated2";
                    if (!$v2course) {
                        // Insert the course to the Turnitintooltwo courses table.
                        $turnitincourse = new stdClass();
                        $turnitincourse->courseid = $course->courseid;
                        $turnitincourse->ownerid = $course->ownerid;
                        $turnitincourse->turnitin_ctl = $course->turnitin_ctl;
                        $turnitincourse->turnitin_cid = $course->turnitin_cid;
                        $turnitincourse->course_type = 'TT';
                        $turnitincourse->migrated = 1;

                        $DB->insert_record('turnitintooltwo_courses', $turnitincourse);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - A course record was created for the course '".$turnitincourse->turnitin_ctl."' (TII: ".$turnitincourse->turnitin_cid.") (Moodle: ".$turnitincourse->courseid.")\r\n");
                    } else {
                        $update = new stdClass();
                        $update->id = $course->id;
                        $update->turnitin_cid = $v2course->turnitin_cid;
                        $DB->update_record('turnitintool_courses', $update);
                        $update->id = $v2course->id;
                        $update->migrated = 1;
                        $DB->update_record('turnitintooltwo_courses', $update);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - A course record was updated for the course '".$v2course->turnitin_ctl."' (TII: ".$v2course->turnitin_cid.") (Moodle: ".$course->id.")\r\n");
                    }
                } else {
                    $statusText = "migrationtool_can_migrate";
                }
            }

            // Output text.
            if ($trial) {
                $data .= html_writer::tag('div', get_string('migrationtool_course_text', 'turnitintooltwo') .' '. $course->fullname, array('class' => 'courseheader '.$headerColour)).
                html_writer::tag('div', get_string($statusText, 'turnitintooltwo'), array('class' => 'text-margin '.$headerColour));
            } else {
                if ($canMigrate == 1) {
                    $data .= html_writer::tag('p', $course->fullname, array('class' => $headerColour));
                }
            }

            // Save CSV session data.
            if (!$etd) {
                fputcsv($csvexport, array($course->courseid, $course->turnitin_cid, $course->turnitin_ctl, get_string($statusText, 'turnitintooltwo')));
            }

            // Loop through each assignment, get its parts and submissions.
            $v1_assignments = $DB->get_records('turnitintool', array('course' => $course->courseid));
            foreach ($v1_assignments as $v1_assignment) {
                // Skip if we can't migrate.
                if ($canMigrate == 1) {
                    $v1_assignment_id = $v1_assignment->id;
                    unset($v1_assignment->id);

                    if (!$trial) {
                        fputs($debug, "+".calcRunTime($startTime)." seconds - Begin migration for assignment: '".$v1_assignment->name."' (Moodle ID: ".$v1_assignment_id.")\r\n");
                        // For old assignments we may encounter null values in fields where they can't be null, check all values.
                        $nullchecks = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat', 'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize', 'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook', 'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch');
                        foreach ($nullchecks as $k => $v) {
                            $v1_assignment->$v = (is_null($v1_assignment->$v)) ? 0 : $v1_assignment->$v;
                        }
                        $v1_assignment->excludetype = (is_null($v1_assignment->excludetype)) ? 1 : $v1_assignment->excludetype;
                        $v1_assignment->perpage = (is_null($v1_assignment->perpage)) ? 25 : $v1_assignment->perpage;

                        // Insert V1 assignment into V2 table.
                        $turnitintooltwoid = $DB->insert_record("turnitintooltwo", $v1_assignment);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - Assignment inserted to V2 table.\r\n");

                        // Update the old assignment title.
                        $updatetitle = new stdClass();
                        $updatetitle->id = $v1_assignment_id;
                        $updatetitle->name = $v1_assignment->name . ' (V1)';
                        $DB->update_record('turnitintool', $updatetitle);

                        // Update the old assignment title in the gradebook.
                        @include_once($CFG->dirroot."/lib/gradelib.php");
                        $params = array();
                        $params['itemname'] = $updatetitle->name;
                        grade_update('mod/turnitintool', $course->courseid, 'mod', 'turnitintool', $v1_assignment_id, 0, NULL, $params);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - Old assignment title has been updated in gradebook.\r\n");

                        // Hide the V1 assignment.
                        $cm = get_coursemodule_from_instance('turnitintool', $v1_assignment_id);
                        set_coursemodule_visible($cm->id, 0);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - V1 assignment has been hidden.\r\n");

                        // Set up a V2 course module.
                        $module = $DB->get_record("modules", array("name" => "turnitintooltwo"));
                        $coursemodule = new stdClass();
                        $coursemodule->course = $course->courseid;
                        $coursemodule->module = $module->id;
                        $coursemodule->added = time();
                        $coursemodule->instance = $turnitintooltwoid;
                        $coursemodule->section = 0;

                        // Add Course module and get course section.
                        $coursemodule->coursemodule = add_course_module($coursemodule);

                        if (is_callable('course_add_cm_to_section')) {
                            $sectionid = course_add_cm_to_section($coursemodule->course, $coursemodule->coursemodule, $coursemodule->section);
                        } else {
                            $sectionid = add_mod_to_section($coursemodule);
                        }

                        $DB->set_field("course_modules", "section", $sectionid, array("id" => $coursemodule->coursemodule));
                        rebuild_course_cache($courseid);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - Course Module has been created in Moodle.\r\n");
                    }

                    // Create new Turnitintooltwo object.
                    $turnitintooltwoassignment = new turnitintooltwo_assignment($turnitintooltwoid);

                    // Get the assignment parts.
                    $v1_parts = $DB->get_records('turnitintool_parts', array('turnitintoolid' => $v1_assignment_id));

                    // Migrate the parts.
                    foreach ($v1_parts as $v1_part) {
                        $v1_part->turnitintooltwoid = $turnitintooltwoid;
                        $v1_part_id = $v1_part->id;
                        unset($v1_part->turnitintoolid);
                        unset($v1_part->id);

                        if (($v2course) && ($etd)) {
                            fputcsv($csvexport, array($v2course->courseid, $v2course->turnitin_cid, $v2course->turnitin_ctl, $v1_part->tiiassignid));
                        }

                        if (!$trial) {
                            fputs($debug, "+".calcRunTime($startTime)." seconds - Begin part migration for part name '".$v1_part->partname."' (TII: ".$v1_part->tiiassignid.") (Moodle: ".$v1_part_id.")\r\n");

                            $v2_part_id = $DB->insert_record("turnitintooltwo_parts", $v1_part);

                            // Get the submissions for this part.
                            $v1_part_submissions = $DB->get_records('turnitintool_submissions', array('submission_part' => $v1_part_id));

                            fputs($debug, "+".calcRunTime($startTime)." seconds - Begin submission migration for part ".$v1_part->tiiassignid."\r\n");
                            $total_gradebook_time = 0;
                            foreach ($v1_part_submissions as $v1_part_submission) {
                                $v1_part_submission->turnitintooltwoid = $turnitintooltwoid;
                                $v1_part_submission->submission_part = $v2_part_id;
                                $v1_part_submission->migration_gradebook = 1;
                                unset($v1_assignment_submissions->turnitintoolid);
                                unset($v1_part_submission->id);

                                $turnitintooltwo_submissionid = $DB->insert_record("turnitintooltwo_submissions", $v1_part_submission);
                            }
                            fputs($debug, "+".calcRunTime($startTime)." seconds - Finished submission migration for part ".$v1_part->tiiassignid."\r\n                  Time spent updating gradebook: 0 seconds.\r\n");
                        }
                        if (!$trial) {
                            fputs($debug, "+".calcRunTime($startTime)." seconds - Finished part migration for part name '".$v1_part->partname."' (TII: ".$v1_part->tiiassignid.") (Moodle: ".$v1_part_id.")\r\n");
                        }
                    }

                    if (!$trial) {
                        // Create entry in gradebook for this assignment.
                        turnitintooltwo_grade_item_update($turnitintooltwoassignment->turnitintooltwo);
                        fputs($debug, "+".calcRunTime($startTime)." seconds - Finished migration for assignment: '".$v1_assignment->name."' (Moodle ID: ".$v1_assignment_id.")\r\n");
                    }
                }
            }

            if (!$trial) {
                // Commit transaction.
                $transaction->allow_commit();
                fputs($debug, "+".calcRunTime($startTime)." seconds - Finish migrating a course\r\n");
            }
        }

        fclose($csvexport);
        if (!$trial) {
            fputs($debug, "+".calcRunTime($startTime)." seconds - End Ajax call\r\n\r\n");
        }
        fclose($debug);

        echo json_encode(array("start" => 0, "processAtOnce" => $processAtOnce, "startpost" => $start, "end" => $end, "iteration" => $iteration, "dataset" => $data, "doOnce" => $doOnce, "totalToMigrate" => $totalToMigrate));
    break;
}
