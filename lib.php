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
 * Required and general functions used by the plugin
 *
 * @package   turnitintooltwo
 * @copyright 2013 iParadigms LLC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/turnitintooltwo_assignment.class.php');
require_once(__DIR__.'/turnitintooltwo_class.class.php');

// Constants.
define('TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE', 41943040);
define('TURNITINTOOLTWO_DEFAULT_PSEUDO_DOMAIN', '@tiimoodle.com');
define('TURNITINTOOLTWO_DEFAULT_PSEUDO_FIRSTNAME', get_string('defaultcoursestudent'));
define('TURNITINTOOLTWO_SUBMISSION_GET_LIMIT', 100);
define('TURNITINTOOLTWO_MAX_FILENAME_LENGTH', 180);
define('TURNITIN_SUPPORT_FORM', 'http://turnitin.com/self-service/support-form.html');
define('TURNITIN_COURSE_TITLE_LIMIT', 300);
define('TURNITIN_ASSIGNMENT_TITLE_LIMIT', 300);

// For use in course migration.
$tiiintegrationids = array(0 => get_string('nointegration', 'turnitintooltwo'), 1 => 'Blackboard Basic',
                                    2 => 'WebCT', 5 => 'Angel', 6 => 'Moodle Basic', 7 => 'eCollege', 8 => 'Desire2Learn',
                                    9 => 'Sakai', 12 => 'Moodle Direct', 13 => 'Blackboard Direct');

/**
 * Function for either adding to log or triggering an event
 * depending on Moodle version
 * @param int $courseid Moodle course ID
 * @param string $eventname The event we are logging
 * @param string $link A link to the Turnitin activity
 * @param string $desc Description of the logged event
 * @param int $cmid Course module id
 */
function turnitintooltwo_add_to_log($courseid, $eventname, $link, $desc, $cmid, $userid = 0) {
    global $CFG, $USER;
    if ( ( property_exists( $CFG, 'branch' ) AND ( $CFG->branch < 27 ) ) || ( !property_exists( $CFG, 'branch' ) ) ) {
        add_to_log($courseid, "turnitintooltwo", $eventname, $link, $desc, $cmid);
    } else {
        $eventname = str_replace(' ', '_', $eventname);
        $eventpath = '\mod_turnitintooltwo\event\\'.$eventname;

        $data = array(
            'objectid' => $cmid,
            'context' => ( $cmid == 0 ) ? context_course::instance($courseid) : context_module::instance($cmid),
            'other' => array('desc' => $desc)
        );
        if (!empty($userid) && ($userid != $USER->id)) {
            $data['relateduserid'] = $userid;
        }
        $event = $eventpath::create($data);
        $event->trigger();
    }
}

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, null if doesn't know
 */
function turnitintooltwo_supports($feature) {
    defined("FEATURE_SHOW_DESCRIPTION") or define("FEATURE_SHOW_DESCRIPTION", null);
    switch($feature) {
        case FEATURE_GROUPS:
        case FEATURE_GROUPMEMBERSONLY:
        case FEATURE_MOD_INTRO:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * @return int the plugin version for use within the plugin.
 */
function turnitintooltwo_get_version() {
    global $DB, $CFG;
    $pluginversion = '';

    if ($CFG->branch >= 26) {
        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintooltwo', 'name' => 'version'));
        $pluginversion = $module->value;
    } else {
        $module = $DB->get_record('modules', array('name' => 'turnitintooltwo'));
        $pluginversion = $module->version;
    }

    return $pluginversion;
}

/**
 * @return mixed the admin config settings for the plugin
 */
function turnitintooltwo_admin_config() {
    return get_config('turnitintooltwo');
}

/**
 * Log activity / errors
 *
 * @param string $string The string describing the activity
 * @param string $activity The activity prompting the log
 * e.g. PRINT_ERROR (default), API_ERROR, INCLUDE, REQUIRE_ONCE, REQUEST, REDIRECT
 */
function turnitintooltwo_activitylog($string, $activity) {
    global $CFG;

    static $config;
    if (empty($config)) {
        $config = turnitintooltwo_admin_config();
    }

    if ($config->enablediagnostic) {
        // We only keep 10 log files, delete any additional files.
        $prefix = "activitylog_";

        $dirpath = $CFG->tempdir."/turnitintooltwo/logs";
        if (!file_exists($dirpath)) {
            mkdir($dirpath, 0777, true);
        }
        $dir = opendir($dirpath);
        $files = array();
        while ($entry = readdir($dir)) {
            if (substr(basename($entry), 0, 1) != "." AND substr_count(basename($entry), $prefix) > 0) {
                $files[] = basename($entry);
            }
        }
        sort($files);
        for ($i = 0; $i < count($files) - 10; $i++) {
            unlink($dirpath."/".$files[$i]);
        }

        // Replace <br> tags with new line character.
        $string = str_replace("<br/>", "\r\n", $string);

        // Write to log file.
        $filepath = $dirpath."/".$prefix.gmdate('Y-m-d', time()).".txt";
        $file = fopen($filepath, 'a');
        $output = date('Y-m-d H:i:s O')." (".$activity.")"." - ".$string."\r\n";
        fwrite($file, $output);
        fclose($file);
    }
}

/**
 * Needed for instance name update via quick update on the course home page
 *
 * @param  stdClass  $turnitintooltwo
 * @param  integer $userid
 * @param  boolean $nullifnone
 */
function turnitintooltwo_update_grades($turnitintooltwo, $userid = 0, $nullifnone = true) {
    global $DB, $USER, $CFG;

    if ($userid != 0) {
        return;
    }

    $turnitintooltwoassignment = new turnitintooltwo_assignment($turnitintooltwo->id);

    try {
        $turnitintooltwoassignment->edit_moodle_assignment(false);
    } catch (Exception $e) {
        turnitintooltwo_comms::handle_exceptions($e, 'turnitintooltwoupdateerror', false);
    }

    // Update events in the calendar.
    $parts = $DB->get_records_select("turnitintooltwo_parts", " turnitintooltwoid = ? ",
                                        array($turnitintooltwo->id), 'id ASC');
    foreach ($parts as $part) {
        $dbselect = " modulename = ? AND instance = ? AND courseid = ? AND name LIKE ? ";
        // Moodle pre 2.5 on SQL Server errors here as queries weren't allowed on ntext fields, the relevant fields
        // are nvarchar from 2.6 onwards so we have to cast the relevant fields in pre 2.5 SQL Server setups.
        if ($CFG->branch <= 25 && $CFG->dbtype == "sqlsrv") {
            $dbselect = " CAST(modulename AS nvarchar(max)) = ? AND instance = ?
                            AND courseid = ? AND CAST(name AS nvarchar(max)) = ? ";
        }

        try {
            // Update event for assignment part.
            if ($event = $DB->get_record_select("event", $dbselect,
                                        array('turnitintooltwo', $turnitintooltwo->id,
                                                    $turnitintooltwo->course, '% - '.$part->partname))) {
                $updatedevent = new stdClass();
                $updatedevent->id = $event->id;
                $updatedevent->userid = $USER->id;
                $updatedevent->name = $turnitintooltwo->name." - ".$part->partname;

                $DB->update_record('event', $updatedevent);
            }
        } catch (Exception $e) {
            turnitintooltwo_comms::handle_exceptions($e, 'turnitintooltwoupdateerror', false);
        }
    }
}

/**
 * Create/update grade item for given assignment
 *
 * @param object $turnitintooltwo object with extra cmidnumber (if available)
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */

function turnitintooltwo_grade_item_update($turnitintooltwo, $grades = null) {
    global $CFG, $DB;
    @include_once($CFG->dirroot."/lib/gradelib.php");

    $params = array();
    $cm = get_coursemodule_from_instance("turnitintooltwo", $turnitintooltwo->id, $turnitintooltwo->course);
    $params['itemname'] = $turnitintooltwo->name;
    $params['idnumber'] = isset($cm->idnumber) ? $cm->idnumber : null;

    $grade = (empty($turnitintooltwo->grade)) ? 0 : $turnitintooltwo->grade;
    if ($grade < 0) { // If we're using a grade scale.
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$grade;
    } else if ($grade > 0) { // If we are using a grade value.
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $grade;
        $params['grademin'] = 0;
    } else { // If we aren't using a grade at all.
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    $lastpart = $DB->get_record('turnitintooltwo_parts', array('turnitintooltwoid' => $turnitintooltwo->id), 'max(dtpost)');
    $lastpart = current($lastpart);
    $params['hidden'] = $lastpart;
    $params['grademin']  = 0;

    return grade_update('mod/turnitintooltwo', $turnitintooltwo->course, 'mod', 'turnitintooltwo',
                $turnitintooltwo->id, 0, $grades, $params);
}

/**
 * Given an object containing all the necessary data, (defined by the form in
 * mod_form.php) these functions will create/edit a new instance and return the id
 * number of the instance.
 *
 * @global object
 * @param object $turnitintooltwo turnitintooltwo data
 * @return int instance id
 */
function turnitintooltwo_add_instance($turnitintooltwo) {
    turnitintooltwo_activitylog("Create tool instance - ".$turnitintooltwo->name, "REQUEST");
    return turnitintooltwo_edit_instance(0, $turnitintooltwo);
}

function turnitintooltwo_update_instance($turnitintooltwo) {
    turnitintooltwo_activitylog("Update tool instance - ".$turnitintooltwo->name." (".$turnitintooltwo->instance.")",
                                    "REQUEST");
    return turnitintooltwo_edit_instance($turnitintooltwo->instance, $turnitintooltwo);
}

function turnitintooltwo_edit_instance($id, $turnitintooltwo) {
    global $USER;

    $turnitintooltwoassignment = new turnitintooltwo_assignment($id, $turnitintooltwo);
    if ($id == 0) {
        $id = $turnitintooltwoassignment->create_moodle_assignment();
    } else {
        $turnitintooltwoassignment->edit_moodle_assignment();
    }

    if (!empty($turnitintooltwo->set_instructor_defaults)) {
        $instructor = new turnitintooltwo_user($USER->id, 'Instructor');
        $instructor->save_instructor_defaults($turnitintooltwo);
    }

    return $id;
}

/**
 * Given an ID of an instance of this module, this function
 * will permanently delete the instance and any data that depends on it.
 *
 * @param int $id turnitintooltwo instance id
 * @return bool success
 */
function turnitintooltwo_delete_instance($id) {
    $turnitintooltwoassignment = new turnitintooltwo_assignment($id);
    return $turnitintooltwoassignment->delete_moodle_assignment($id);
}

/**
 * Function called by course/reset.php when resetting moodle course, this is used to duplicate or reset a courses Turnitin
 * activities. If action specified is 'NEWCLASS' we are creating a new class on Turnitin. For both actions we create new
 * assignments on Turnitin and replace the turnitin ids for those parts in the database.
 *
 * @global object
 * @param var $courseid The course ID for the course to reset
 * @param string $action The action to use OLDCLASS or NEWCLASS
 * @return array The status array to pass to turnitintooltwo_reset_userdata
 */
function turnitintooltwo_duplicate_recycle($courseid, $action) {
    set_time_limit(0);
    global $DB, $USER;

    $config = turnitintooltwo_admin_config();
    $partsarray = array();
    $error = false;

    $turnitintooltwouser = new turnitintooltwo_user($USER->id, "Instructor");
    $turnitintooltwouser->set_user_values_from_tii();
    $instructorrubrics = $turnitintooltwouser->get_instructor_rubrics();

    if (!$turnitintooltwos = $DB->get_records('turnitintooltwo', array('course' => $courseid))) {
        turnitintooltwo_print_error('assigngeterror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
        exit();
    }

    if (!$DB->get_record('course', array('id' => $courseid))) {
        turnitintooltwo_print_error('coursegeterror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
        exit();
    }

    foreach ($turnitintooltwos as $turnitintooltwo) {
        if (!$parts = $DB->get_records('turnitintooltwo_parts', array('turnitintooltwoid' => $turnitintooltwo->id,
                                                                            'deleted' => 0))) {
            turnitintooltwo_print_error('partgeterror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
            exit();
        }

        foreach ($parts as $part) {
            $partsarray[$courseid][$turnitintooltwo->id][$part->id]['tiiassignid'] = $part->tiiassignid;
        }
    }

    $currentcourse = turnitintooltwo_assignment::get_course_data($courseid);
    if ($action == "NEWCLASS") {
        // Delete Turnitin class link.
        if (!$delete = $DB->delete_records('turnitintooltwo_courses', array('courseid' => $courseid))) {
            turnitintooltwo_print_error('coursedeleteerror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
            exit();
        }
        $currentcourse->tii_rel_id = '';

        // Create a new class to use with new parts.
        $tmpassignment = new turnitintooltwo_assignment(0, '', '');
        $newcourse = $tmpassignment->create_tii_course($currentcourse, $USER->id);

        // Join Instructor to class.
        $turnitintooltwouser->join_user_to_class($newcourse->turnitin_cid);
        $currentcourse->turnitin_cid = $newcourse->turnitin_cid;
        $currentcourse->turnitin_ctl = $newcourse->turnitin_ctl;
    }

    // Create array of all the Turnitin assignment ids.
    $assignmentids = array();
    foreach ($partsarray[$courseid] as $tiitoolid => $tiitool) {
        foreach ($tiitool as $partid => $data) {
            $assignmentids[] = $data['tiiassignid'];
        }
    }

    // Update all the assignments and parts from Turnitin.
    $turnitintooltwoassignment = new turnitintooltwo_assignment(0, '', '');
    $turnitintooltwoassignment->update_assignment_from_tii($assignmentids);

    // Loop through Turnitintool instances and re-create assignment on Turnitin then swap over the stored Turnitin ids.
    foreach ($partsarray[$courseid] as $tiitoolid => $tiitool) {
        $turnitintooltwoassignment = new turnitintooltwo_assignment($tiitoolid);

        // Create new Assignment parts on Turnitin.
        $i = 0;
        foreach ($tiitool as $partid => $data) {
            $i++;
            $assignment = new TiiAssignment();
            $assignment->setClassId($currentcourse->turnitin_cid);
            $assignment->setAuthorOriginalityAccess($turnitintooltwoassignment->turnitintooltwo->studentreports);

            // Get rubrics that are shared on the account.
            $turnitinclass = new turnitintooltwo_class($courseid);
            $turnitinclass->read_class_from_tii();
            $rubrics = $turnitinclass->sharedrubrics;
            $rubrics = $rubrics + $instructorrubrics;

            $rubricid = '';
            if (!empty($turnitintooltwoassignment->turnitintooltwo->rubric)) {
                $rubricid = $turnitintooltwoassignment->turnitintooltwo->rubric;
            }
            $rubricid = (!empty($rubricid) && array_key_exists($rubricid, $rubrics)) ? $rubricid : '';

            $assignment->setRubricId($rubricid);
            $assignment->setSubmitPapersTo($turnitintooltwoassignment->turnitintooltwo->submitpapersto);
            $assignment->setResubmissionRule($turnitintooltwoassignment->turnitintooltwo->reportgenspeed);
            $assignment->setBibliographyExcluded($turnitintooltwoassignment->turnitintooltwo->excludebiblio);
            $assignment->setQuotedExcluded($turnitintooltwoassignment->turnitintooltwo->excludequoted);
            $assignment->setSmallMatchExclusionType($turnitintooltwoassignment->turnitintooltwo->excludetype);
            $assignment->setSmallMatchExclusionThreshold((int)$turnitintooltwoassignment->turnitintooltwo->excludevalue);
            if ($config->useanon) {
                $assignment->setAnonymousMarking($turnitintooltwoassignment->turnitintooltwo->anon);
            }
            $assignment->setLateSubmissionsAllowed($turnitintooltwoassignment->turnitintooltwo->allowlate);
            if ($config->repositoryoption == 1) {
                $institutioncheck = 0;
                if (isset($turnitintooltwoassignment->turnitintooltwo->institution_check)) {
                    $institutioncheck = $turnitintooltwoassignment->turnitintooltwo->institution_check;
                }
                $assignment->setInstitutionCheck($institutioncheck);
            }

            $attribute = "maxmarks".$i;
            $assignment->setMaxGrade($turnitintooltwoassignment->turnitintooltwo->$attribute);
            $assignment->setSubmittedDocumentsCheck($turnitintooltwoassignment->turnitintooltwo->spapercheck);
            $assignment->setInternetCheck($turnitintooltwoassignment->turnitintooltwo->internetcheck);
            $assignment->setPublicationsCheck($turnitintooltwoassignment->turnitintooltwo->journalcheck);
            $assignment->setTranslatedMatching($turnitintooltwoassignment->turnitintooltwo->transmatch);
            $assignment->setAllowNonOrSubmissions($turnitintooltwoassignment->turnitintooltwo->allownonor);

            // Erater settings.
            $erater = 0;
            if (isset($turnitintooltwoassignment->turnitintooltwo->erater)) {
                $erater = $turnitintooltwoassignment->turnitintooltwo->erater;
            }
            $assignment->setErater($erater);
            $assignment->setEraterSpelling($turnitintooltwoassignment->turnitintooltwo->erater_spelling);
            $assignment->setEraterGrammar($turnitintooltwoassignment->turnitintooltwo->erater_grammar);
            $assignment->setEraterUsage($turnitintooltwoassignment->turnitintooltwo->erater_usage);
            $assignment->setEraterMechanics($turnitintooltwoassignment->turnitintooltwo->erater_mechanics);
            $assignment->setEraterStyle($turnitintooltwoassignment->turnitintooltwo->erater_style);

            $eraterdictionary = 'en_US';
            if (isset($turnitintooltwoassignment->turnitintooltwo->erater_dictionary)) {
                $eraterdictionary = $turnitintooltwoassignment->turnitintooltwo->erater_dictionary;
            }
            $assignment->setEraterSpellingDictionary($eraterdictionary);

            $eraterhandbook = 0;
            if (isset($turnitintooltwoassignment->turnitintooltwo->erater_handbook)) {
                $eraterhandbook = $turnitintooltwoassignment->turnitintooltwo->erater_handbook;
            }
            $assignment->setEraterHandbook($eraterhandbook);

            $attribute = "dtstart".$i;
            $assignment->setStartDate(gmdate("Y-m-d\TH:i:s\Z", $turnitintooltwoassignment->turnitintooltwo->$attribute));
            $attribute = "dtdue".$i;
            $assignment->setDueDate(gmdate("Y-m-d\TH:i:s\Z", $turnitintooltwoassignment->turnitintooltwo->$attribute));
            $attribute = "dtpost".$i;
            $assignment->setFeedbackReleaseDate(gmdate("Y-m-d\TH:i:s\Z", $turnitintooltwoassignment->turnitintooltwo->$attribute));

            $attribute = "partname".$i;
            $tiititle = $turnitintooltwoassignment->turnitintooltwo->name." ".$turnitintooltwoassignment->turnitintooltwo->$attribute;
            $tiititle = $turnitintooltwoassignment->truncate_title( $tiititle, TURNITIN_ASSIGNMENT_TITLE_LIMIT, 'TT' );
            $assignment->setTitle( $tiititle );

            $partassignid = $turnitintooltwoassignment->create_tii_assignment($assignment,
                                $turnitintooltwoassignment->turnitintooltwo->id, $i);

            if (empty($partassignid)) {
                turnitintooltwo_activitylog("Moodle Assignment could not be created (".$turnitintooltwoassignment->id.") - ".
                                                $turnitintooltwoassignment->turnitintooltwo->name , "REQUEST");
                $error = true;
            }

            $part = new stdClass();
            $part->id = $partid;
            $part->tiiassignid = $partassignid;
            $part->turnitintooltwoid = $turnitintooltwoassignment->turnitintooltwo->id;
            $part->partname = $turnitintooltwoassignment->turnitintooltwo->$attribute;
            $part->deleted = 0;
            $part->maxmarks = $assignment->getMaxGrade();
            $part->dtstart = strtotime($assignment->getStartDate());
            $part->dtdue = strtotime($assignment->getDueDate());
            $part->dtpost = strtotime($assignment->getFeedbackReleaseDate());

            if (!$DB->update_record('turnitintooltwo_parts', $part)) {
                turnitintooltwo_print_error('partupdateerror', 'turnitintooltwo', null, $i, __FILE__, __LINE__);
                exit();
            } else {
                turnitintooltwo_activitylog("Moodle Assignment part updated (".$part->id.")", "REQUEST");
            }

            if (!$DB->delete_records('turnitintooltwo_submissions', array('submission_part' => $partid))) {
                turnitintooltwo_print_error('submissiondeleteerror', 'turnitintooltwo', null, null, __FILE__, __LINE__);
                exit();
            }
        }
    }

    $datastr = ($action == "NEWCLASS") ? 'copyassigndata' : 'replaceassigndata';
    $item = get_string($datastr, 'turnitintooltwo');
    $status[] = array('component' => get_string('modulenameplural', 'turnitintooltwo'), 'item' => $item, 'error' => $error);

    return $status;
}

/**
 * Function called by course/reset.php when resetting moodle course to actually reset / recycle the data
 *
 * @param object $data The data object passed by course reset
 * @return array The Result of the turnitintooltwo_duplicate_recycle call
 */
function turnitintooltwo_reset_userdata($data) {
    $status = array();
    if ($data->reset_turnitintooltwo == 0) {
        $status = turnitintooltwo_duplicate_recycle($data->courseid, 'NEWCLASS');
    } else if ($data->reset_turnitintooltwo == 1) {
        $status = turnitintooltwo_duplicate_recycle($data->courseid, 'OLDCLASS');
    }
    return $status;
}

/**
 * Function called by course/reset.php when resetting moodle course to reset / recycle the course data using default values
 *
 * @param object $course The course object passed by moodle
 * @return array The result array
 */
function turnitintooltwo_reset_course_form_defaults($course) {
    return array('reset_turnitintooltwo' => 0);
}

/**
 * Function called by course/reset.php when resetting moodle course to build the element for the reset form
 *
 * @param object $mform The mod form object passed by reference by course reset
 */
function turnitintooltwo_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'turnitintooltwoheader', get_string('modulenameplural', 'turnitintooltwo'));
    $options = array(
            '0' => get_string('turnitintooltworesetdata0', 'turnitintooltwo'),
            '1' => get_string('turnitintooltworesetdata1', 'turnitintooltwo'),
            '2' => get_string('turnitintooltworesetdata2', 'turnitintooltwo')
    );
    $mform->addElement('select', 'reset_turnitintooltwo', get_string('selectoption', 'turnitintooltwo'), $options);
}

/**
 * A Standard Moodle function that moodle executes at the time the cron runs
 */
function turnitintooltwo_cron() {
    global $DB, $CFG, $tiitaskcall;

    // 2.7 onwards we would like to be called from task calls.
    if ( $CFG->version > 2014051200 && !$tiitaskcall ) {
        mtrace(get_string('crontaskmodeactive', 'turnitintooltwo'));
        return;
    }

    // Reset task call flag.
    if ($tiitaskcall) {
        $tiitaskcall = false;
    }

    // Update gradebook when a part has been deleted.
    // Get assignment that needs updating and check whether it exists.
    if ($assignment = $DB->get_record('turnitintooltwo', array("needs_updating" => 1), '*', IGNORE_MULTIPLE)) {

        // Update the gradebook.
        $task = "needsupdating";
        turnitintooltwo_cron_update_gradbook($assignment, $task);
    }

    // Send grades to the gradebook for anonymous marking assignments when the post date has passed.
    // Get a list of assignments that need updating.
    if ($assignmentlist = $DB->get_records_sql("SELECT t.id FROM {turnitintooltwo} t
                                                LEFT JOIN {turnitintooltwo_parts} p ON (p.turnitintooltwoid = t.id)
                                                WHERE (turnitintooltwoid, dtpost) IN (SELECT turnitintooltwoid, MAX(dtpost)
                                                    FROM {turnitintooltwo_parts}
                                                    GROUP BY turnitintooltwoid)
                                                AND t.anon = 1 AND t.anongradebook = 0 AND dtpost < ".time()."
                                                GROUP BY t.id")) {

        // Update each assignment.
        $task = "anongradebook";
        foreach ($assignmentlist as $assignment) {
            turnitintooltwo_cron_update_gradbook($assignment, $task);
        }
    }

    // Refresh the submissions for migrated assignment parts if there are none stored locally
    // as the 1st time this is done can be quite a long job if there are a lot of submissions.
    $migratedemptyparts = $DB->get_records_select('turnitintooltwo_parts', " migrated = 1 AND ".
                            " (SELECT COUNT(id) FROM {turnitintooltwo_submissions} ".
                            " WHERE submission_part = {turnitintooltwo_parts}.id) = 0 ");

    if (count($migratedemptyparts) > 0) {
        $updatedassignments = array();
        foreach ($migratedemptyparts as $part) {
            if (!array_search($part->id, $updatedassignments)) {
                $cm = get_coursemodule_from_instance("turnitintooltwo", $part->turnitintooltwoid);
                $turnitintooltwoassignment = new turnitintooltwo_assignment($part->turnitintooltwoid);
                $turnitintooltwoassignment->get_submission_ids_from_tii($part);
                $turnitintooltwoassignment->refresh_submissions($cm, $part);
                $updatedassignments[] = $part->id;

                turnitintooltwo_activitylog('Turnitintool submissions downloaded for assignment '.$part->id, 'REQUEST');
            }
        }
        echo 'Turnitintool submissions downloaded for assignments: '.implode(',', $updatedassignments).' ';
    }
}

/**
 * Update the gradebook for cron calls.
 *
 * @param type $assignment The assignment that we are going to update the grades for.
 * @param string $task The cron task we are performing the update from.
 */
function turnitintooltwo_cron_update_gradbook($assignment, $task) {
    global $DB, $CFG;
    @include_once($CFG->dirroot."/lib/gradelib.php");

    $turnitintooltwoassignment = new turnitintooltwo_assignment($assignment->id);
    $cm = get_coursemodule_from_instance("turnitintooltwo", $turnitintooltwoassignment->turnitintooltwo->id,
        $turnitintooltwoassignment->turnitintooltwo->course);

    if ($cm) {
        $users = get_enrolled_users(context_module::instance($cm->id),
                                'mod/turnitintooltwo:submit', groups_get_activity_group($cm), 'u.id');

        foreach ($users as $user) {
            $fieldlist = array('turnitintooltwoid' => $turnitintooltwoassignment->turnitintooltwo->id,
                               'userid' => $user->id);

            // Set submission_unanon when needsupdating is used.
            if ($task == "needsupdating") {
                $fieldlist['submission_unanon'] = 1;
            }

            $grades = new stdClass();

            if ($submissions = $DB->get_records('turnitintooltwo_submissions', $fieldlist)) {
                $overallgrade = $turnitintooltwoassignment->get_overall_grade($submissions, $cm);
                if ($turnitintooltwoassignment->turnitintooltwo->grade < 0) {
                    // Using a scale.
                    $grades->rawgrade = ($overallgrade == '--') ? null : $overallgrade;
                } else {
                    $grades->rawgrade = ($overallgrade == '--') ? null : number_format($overallgrade, 2);
                }
            }
            $grades->userid = $user->id;
            $params['idnumber'] = $cm->idnumber;

            grade_update('mod/turnitintooltwo', $turnitintooltwoassignment->turnitintooltwo->course, 'mod',
                'turnitintooltwo', $turnitintooltwoassignment->turnitintooltwo->id, 0, $grades, $params);
        }

        // Remove the "anongradebook" flag.
        $updateassignment = new stdClass();
        $updateassignment->id = $assignment->id;

        // Depending on the task we need to update a different column.
        switch($task) {
            case "needsupdating":
                $updateassignment->needs_updating = 0;
                break;

            case "anongradebook":
                $updateassignment->anongradebook = 1;
                break;
        }

        $DB->update_record("turnitintooltwo", $updateassignment);
    }
}

/**
 * Abstracted version of print_error()
 *
 * @param string $input The error string if module = null otherwise the language string called by get_string()
 * @param string $module The module string
 * @param string $param The parameter to send to use as the $a optional object in get_string()
 * @param string $file The file where the error occured
 * @param string $line The line number where the error occured
 */
function turnitintooltwo_print_error($input, $module = 'turnitintooltwo',
                                     $link = null, $param = null, $file = __FILE__, $line = __LINE__) {
    global $CFG;
    turnitintooltwo_activitylog($input, "PRINT_ERROR");

    $message = (is_null($module)) ? $input : get_string($input, $module, $param);
    $linkid = optional_param('id', 0, PARAM_INT);

    if (is_null($link) AND substr_count($_SERVER["PHP_SELF"], "turnitintooltwo/view.php") > 0) {
        $link = (!empty($linkid)) ? $CFG->wwwroot.'/mod/turnitintooltwo/view.php?id='.$linkid : $CFG->wwwroot;
    }

    if (basename($file) != "lib.php") {
        $message .= ' ('.basename($file).' | '.$line.')';
    }

    print_error($input, 'turnitintooltwo', $link, $message);
    exit();
}

/**
 * Outputs the file type array for acceptable file type uploads
 *
 * @param boolean $setup True if the call is from the assignment activity setup screen
 * @param array The array of filetypes ready for the modform parameter
 */
function turnitintooltwo_filetype_array($setup = true) {
    $output = array(
        1 => get_string('fileupload', 'turnitintooltwo'),
        2 => get_string('textsubmission', 'turnitintooltwo')
    );
    if ($setup) {
        $output[0] = get_string('anytype', 'turnitintooltwo');
    }
    ksort($output);
    return $output;
}

/**
 * Creates a temp file for submission to Turnitin, uses a random number suffixed with the stored filename
 *
 * @param array $filename Used to build a more readable filename
 * @param string $suffix The file extension for the upload
 * @return string $file The filepath of the temp file
 */
function turnitintooltwo_tempfile(array $filename, $suffix) {
    $filename = implode('_', $filename);
    $filename = str_replace(' ', '_', $filename);
    $filename = clean_param(strip_tags($filename), PARAM_FILE);

    $tempdir = make_temp_directory('turnitintooltwo');

    // Get the file extension (if there is one).
    $pathparts = explode('.', $suffix);
    $ext = '';
    if (count($pathparts) > 1) {
        $ext = '.' . array_pop($pathparts);
    }

    $permittedstrlength = TURNITINTOOLTWO_MAX_FILENAME_LENGTH - mb_strlen($tempdir.DIRECTORY_SEPARATOR, 'UTF-8');
    $extlength = mb_strlen('_' . mt_getrandmax() . $ext, 'UTF-8');
    if ($extlength > $permittedstrlength) {
        // Someone has likely used a filename with an absurdly long extension, or the
        // tempdir path is huge, so preserve the extension as much as possible.
        $extlength = $permittedstrlength;
    }

    // Shorten the filename as needed, taking the extension into consideration.
    $permittedstrlength -= $extlength;
    $filename = mb_substr($filename, 0, $permittedstrlength, 'UTF-8');

    // Ensure the filename doesn't have any characters that are invalid for the fs.
    $filename = clean_param($filename . mb_substr('_' . mt_rand() . $ext, 0, $extlength, 'UTF-8'), PARAM_FILE);

    $tries = 0;
    do {
        if ($tries == 10) {
            throw new invalid_dataroot_permissions("turnitintooltwo temporary file cannot be created.");
        }
        $tries++;

        $file = $tempdir . DIRECTORY_SEPARATOR . $filename;
    } while ( !touch($file) );

    return $file;
}

/**
 * Checks whether update is available for plugin from Turnitin.
 *
 * @param type $module
 * @return null
 */
function turnitintooltwo_updateavailable($currentversion) {
    global $CFG;

    $updateneeded['update'] = 0;

    try {
        // Open connection.
        $ch = curl_init();

        // Set the url, number of POST vars, POST data.
        curl_setopt($ch, CURLOPT_URL, "https://www.turnitin.com/static/resources/files/moodledirect2_latest.xml");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        if (isset($CFG->proxyhost) AND !empty($CFG->proxyhost)) {
            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }
        if (isset($CFG->proxyuser) AND !empty($CFG->proxyuser)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', $CFG->proxyuser, $CFG->proxypassword));
        }

        // Execute post.
        $result = curl_exec($ch);

        // Close connection.
        curl_close($ch);

        $xml = simplexml_load_string($result);
        if ((isset($xml)) AND (isset($xml->version))) {
            if ($xml->version > $currentversion) {
                $updateneeded['update'] = 1;
                $updateneeded['file'] = $xml->filename;
            }
        }

    } catch (Exception $e) {
        turnitintooltwo_comms::handle_exceptions($e, 'checkupdateavailableerror', false);
    }

    return $updateneeded;
}

/**
 * Gets the assignments from a specified course from Turnitin
 *
 * @param int $courseid the id of the course/class in Turnitin
 * @param str $returnformat the format we are returning data in
 * @return array of data in a datatables readable format
 */
function turnitintooltwo_get_assignments_from_tii($courseid, $returnformat = "json") {
    global $DB;

    $return = array();
    if ($returnformat == "json") {
        $return["aaData"] = array();
    }

    $turnitincomms = new turnitintooltwo_comms();
    $turnitincall = $turnitincomms->initialise_api();

    $assignment = new TiiAssignment();
    $assignment->setClassId($courseid);

    $assignmentids = array();
    try {
        $response = $turnitincall->findAssignments( $assignment );
        $findassignment = $response->getAssignment();
        $assignmentids = $findassignment->getAssignmentIds();
    } catch (Exception $e) {
        $turnitincomms->handle_exceptions($e, 'getassignmenterror', false);
    }

    $assignment = new TiiAssignment();
    $assignment->setAssignmentIds($assignmentids);

    $currentassignments = array();
    if (count($assignmentids) > 0) {
        list($insql, $inparams) = $DB->get_in_or_equal($assignmentids);

        $currentassignments['TT'] = $DB->get_records_select("turnitintooltwo_parts",
                                                                " tiiassignid ".$insql, $inparams, '', 'tiiassignid');

        $dbman = $DB->get_manager();
        if ($dbman->table_exists('plagiarism_turnitin_config')) {
            $currentassignments['PP'] = $DB->get_records_select("plagiarism_turnitin_config", " name = 'turnitin_assignid' ".
                                                                " AND value ".$insql, $inparams, '', 'value');
        } else {
            $currentassignments['PP'] = array();
        }
    }

    try {
        $response = $turnitincall->readAssignments( $assignment );
        $readassignments = $response->getAssignments();
        foreach ($readassignments as $readassignment) {
            if (empty($currentassignments['TT'][$readassignment->getAssignmentId()]) &&
                    empty($currentassignments['PP'][$readassignment->getAssignmentId()])) {
                $checkbox = html_writer::checkbox('assignmentids[]', $readassignment->getAssignmentId(), false, '',
                                    array("id" => "assignmentid_".$readassignment->getAssignmentId(),
                                            "disabled" => "disabled", "class" => "assignmentids_check"));
                if ($returnformat == "json") {
                    $return["aaData"][] = array($checkbox, $readassignment->getTitle(), $readassignment->getMaxGrade(),
                                                $readassignment->getAssignmentId());
                } else {
                    $return[$readassignment->getAssignmentId()] = array("tii_id" => $readassignment->getAssignmentId(),
                                                                    "tii_title" => $readassignment->getTitle());
                }
            } else {
                // Double check that migration has completed for this assignment.
                // If not then there has been an error so we delete the previously migrated assignment attempt.
                if ($part = $DB->get_record('turnitintooltwo_parts',
                                    array('tiiassignid' => $readassignment->getAssignmentId(), 'migrated' => -1))) {
                    $DB->delete_records('turnitintooltwo_parts',
                                            array('tiiassignid' => $readassignment->getAssignmentId(), 'migrated' => -1));
                    turnitintooltwo_activitylog("Deleted failed migration assignment part - id (".
                                                            $readassignment->getAssignmentId().")", "REQUEST");

                    // Remove the Turnitintool if it contains no other parts.
                    if ($DB->count_records('turnitintooltwo_parts', array('turnitintooltwoid' => $part->turnitintooltwoid)) == 0) {
                        $DB->delete_records("turnitintooltwo", array("id" => $part->turnitintooltwoid));
                        turnitintooltwo_activitylog("Deleted tool instance - id (".$part->turnitintooltwoid.")", "REQUEST");
                    }
                }
            }
        }
    } catch (Exception $e) {
        turnitintooltwo_activitylog(get_string('migrationassignmentgeterror', 'turnitintooltwo').' - Class: '.$courseid, "REQUEST");
    }

    return $return;
}

/**
 * Gets the courses for this account from Turnitin.
 *
 * @param array $tiiintegrationids the integration ids we want courses from
 * @return array of data in a datatables readable format
 */
function turnitintooltwo_get_courses_from_tii($tiiintegrationids, $coursetitle, $courseintegration,
                                                $courseenddate = null, $requestsource = "mod") {
    global $CFG, $DB, $OUTPUT, $USER;
    set_time_limit(0);

    $_SESSION["stored_tii_courses"] = array();
    $return = array();
    $return["aaData"] = array();
    $secho = optional_param('sEcho', 0, PARAM_INT);

    $ssortdir = optional_param('sSortDir_0', 'desc', PARAM_ALPHA);
    $isortcol = optional_param('iSortCol_0', '6', PARAM_INT);

    $turnitincomms = new turnitintooltwo_comms();
    $turnitincall = $turnitincomms->initialise_api();

    $class = new TiiClass();
    $class->setTitle($coursetitle);
    if ($courseenddate != null) {
        $class->setDateFrom(gmdate("Y-m-d", strtotime($courseenddate)).'T00:00:00Z' );
    }
    if (array_key_exists($courseintegration, $tiiintegrationids)) {
        $class->setIntegrationId($courseintegration);
    }

    if (!is_siteadmin()) {
        $turnitintooltwouser = new turnitintooltwo_user($USER->id, 'Instructor');
        $tiiinstructorid = $turnitintooltwouser->tiiuserid;
        $class->setUserId($tiiinstructorid);
        $class->setUserRole('Instructor');
    }

    try {
        $response = $turnitincall->findClasses($class);
        $findclass = $response->getClass();
        $classids = $findclass->getClassIds();

    } catch (Exception $e) {
        turnitintooltwo_activitylog(get_string('migrationcoursegeterror', 'turnitintooltwo'), "REQUEST");
        $classids = array();
    }

    // Get currently linked courses.
    $currentcourses = array();
    if (!empty($classids)) {
        list($insql, $inparams) = $DB->get_in_or_equal($classids);

        $currentcourses["PP"] = $DB->get_records_sql("SELECT tc.turnitin_cid FROM {turnitintooltwo_courses} tc ".
                                                     "RIGHT JOIN {course} c ON c.id = tc.courseid ".
                                                     "WHERE tc.course_type = 'PP' AND tc.turnitin_cid ".$insql, $inparams);

        $currentcourses["TT"] = $DB->get_records_sql("SELECT tc.turnitin_cid FROM {turnitintooltwo_courses} tc ".
                                                     "RIGHT JOIN {course} c ON c.id = tc.courseid ".
                                                     "WHERE tc.course_type = 'TT' AND tc.turnitin_cid ".$insql, $inparams);
    }

    $class = new TiiClass();
    $class->setClassIds($classids);

    $tiicourses = array();
    $i = 0;
    if (!empty($classids)) {
        try {
            $response = $turnitincall->readClasses($class);
            $readclasses = $response->getClasses();

            foreach ($readclasses as $readclass) {
                if (array_key_exists($readclass->getIntegrationId(), $tiiintegrationids)) {
                    $_SESSION["stored_tii_courses"][$readclass->getClassId()] = $readclass->getTitle();

                    // If we're coming from block we don't need any information, just the number of records.
                    if ($requestsource == "mod") {
                        $linkpage = (is_siteadmin()) ? "settings_extras.php" : "extras.php";

                        $titlecell = html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/'.$linkpage.
                                                        '?cmd=class_recreation&id='.$readclass->getClassId().
                                                        '&view_context=box&sesskey='.sesskey(),
                                                        $readclass->getTitle(), array("class" => "course_recreate",
                                                                                "id" => "course_".$readclass->getClassId()));
                        $datecell = html_writer::link('.edit_course_end_date_form',
                                        html_writer::tag('span',
                                                userdate(strtotime($readclass->getEndDate()),
                                                            get_string('strftimedate', 'langconfig')),
                                                    array("id" => $readclass->getClassId()."_".
                                                                gmdate("j", strtotime($readclass->getEndDate()))."_".
                                                                    gmdate("n", strtotime($readclass->getEndDate()))."_".
                                                                        gmdate("Y", strtotime($readclass->getEndDate()))))." ".
                                            html_writer::tag('i', '', array('class' => 'fa fa-pencil fa-lg grey')),
                                        array("class" => "edit_course_end_link", "id" => "course_date_".$readclass->getClassId()));

                        $checkbox = '';
                        $class = '';
                        if (empty($currentcourses["PP"][$readclass->getClassId()]) &&
                                empty($currentcourses["TT"][$readclass->getClassId()])) {
                            $class = 'hidden_class';
                            $checkbox = html_writer::checkbox('check_'.$readclass->getClassId(), $readclass->getClassId(),
                                        false, '', array("class" => "browser_checkbox"));
                        }

                        $moodlecell = $OUTPUT->pix_icon('tick', get_string('moodlelinked', 'turnitintooltwo'),
                                    'mod_turnitintooltwo', array('class' => $class, 'id' => 'tick_'.$readclass->getClassId()));

                        $tiicourses[$i] = array($checkbox, $titlecell, $tiiintegrationids[$readclass->getIntegrationId()],
                                                $datecell, $readclass->getClassId(), $moodlecell, $readclass->getTitle(),
                                                userdate(strtotime($readclass->getEndDate()),
                                                                    get_string('strftimedate', 'langconfig')));
                    }
                    $i++;

                    // Keep course names in case of course recreation.
                    $_SESSION['tii_classes'][$readclass->getClassId()] = $readclass->getTitle();
                }
            }

            if (count($tiicourses) > 0 && $requestsource == "mod") {
                turnitintooltwo_sort_array($tiicourses, $isortcol, $ssortdir);

                $j = 0;
                foreach ($tiicourses as $class) {
                    $return["aaData"][$j] = $class;
                    $j++;
                }
            }

        } catch (Exception $e) {
            $turnitincomms->handle_exceptions($e, 'coursegettiierror');
        }
    }

    $return["iTotalRecords"] = $i;
    $return["iTotalDisplayRecords"] = $i;
    $return["sEcho"] = $secho;
    $return["blockHTML"] = ($i == 0) ? '' : get_string('coursestomigrate', 'mod_turnitintooltwo', $i);

    return $return;
}

function turnitintooltwo_sort_array(&$data, $sortcol, $sortdir) {
    if ($sortdir == "desc") {
        return usort($data, function($b, $a) use ($sortcol) {
            return strnatcmp($a[$sortcol], $b[$sortcol]);
        });
    } else {
        return usort($data, function($a, $b) use ($sortcol) {
            return strnatcmp($a[$sortcol], $b[$sortcol]);
        });
    }
}

/**
 * Get files for displaying in settings. Called from ajax.php via turnitintooltwo.min.js.
 *
 * @param  $moduleid the id of the module to return files for
 * @global type $DB
 * @global type $CFG
 * @global type $OUTPUT
 * @return type array of filedate to be displayed
 */
function turnitintooltwo_getfiles($moduleid) {
    global $DB, $CFG, $OUTPUT;

    $return = array();
    $idisplaystart = optional_param('iDisplayStart', 0, PARAM_INT);
    $idisplaylength = optional_param('iDisplayLength', 10, PARAM_INT);
    $secho = optional_param('sEcho', 1, PARAM_INT);
    $moduleid = (int)$moduleid;

    $displaycolumns = array( 'tu.name', 'cs.shortname', 'cs.fullname', 'sb.submission_filename', 'us.firstname',
                                'us.lastname', 'us.email', 'fl.filename', 'sb.submission_objectid' );
    $queryparams = array();

    // Add Sort to Query.
    $isortcol[0] = optional_param('iSortCol_0', null, PARAM_INT);
    $isortingcols = optional_param('iSortingCols', 0, PARAM_INT);
    $queryorder = "";
    if (!is_null( $isortcol[0])) {
        $queryorder = " ORDER BY ";
        $startorder = $queryorder;
        for ($i = 0; $i < intval($isortingcols); $i++) {
            $isortcol[$i] = optional_param('iSortCol_'.$i, null, PARAM_INT);
            $bsortable[$i] = optional_param('bSortable_'.$isortcol[$i], null, PARAM_TEXT);
            $ssortdir[$i] = optional_param('sSortDir_'.$i, null, PARAM_TEXT);
            if ( $bsortable[$i] == "true" ) {
                $queryorder .= $displaycolumns[$isortcol[$i]]." ".$ssortdir[$i].", ";
            }
        }
        $queryorder = substr_replace($queryorder, "", -2);
        if ($queryorder == $startorder) {
            $queryorder = "";
        }
    }
    $queryorder .= ", tu.id asc ";

    // Add Search to Query.
    $ssearch = optional_param('sSearch', '', PARAM_TEXT);
    $start = true;
    $querywhere = " AND ( ";
    $nobracket = false;
    for ($i = 0; $i < count($displaycolumns); $i++) {
        $bsearchable[$i] = optional_param('bSearchable_'.$i, null, PARAM_TEXT);
        $ssearchn[$i] = optional_param('sSearch_'.$i, null, PARAM_TEXT);
        if (!is_null($bsearchable[$i]) && $bsearchable[$i] == "true" && ( $ssearch != '' OR $ssearchn[$i] != '')) {
            if (!$start) {
                $querywhere .= " OR ";
            }

            if ($displaycolumns[$i] == 'sb.submission_objectid') {
                $querywhere = ( $querywhere == ' AND ( ' ) ? '' : substr_replace( $querywhere, "", -3 ) . ' )';
                $querywhere .= " AND ( sb.submission_objectid IS NOT NULL OR sb.submission_filename IS NULL )";
                $nobracket = true;
            } else if ($displaycolumns[$i] != ' ') {
                $namedparam = 'search_term_'.$i;
                $querywhere .= $DB->sql_like($displaycolumns[$i], ':'.$namedparam, false);
                $queryparams['search_term_'.$i] = '%'.$ssearch.'%';
                $start = false;
            }
        }
    }
    if ($querywhere != ' AND ( ' AND !$nobracket) {
        $querywhere .= " ) ";
    } else if ($nobracket) {
        $querywhere .= " ";
    } else {
        $querywhere = "";
    }

    $query = "SELECT fl.id AS id, cm.id AS cmid, tu.id AS activityid, tu.name AS activity, tu.anon AS anon_enabled, ".
             "sb.submission_unanon AS unanon, sb.id AS submission_id, us.firstname AS firstname, us.lastname AS lastname, ".
             "us.email AS email, us.id AS userid, fl.mimetype AS mimetype, fl.filesize AS filesize, fl.timecreated AS created, ".
             "fl.pathnamehash AS hash, fl.filename AS rawfilename, fl.itemid AS itemid, fl.contextid AS contextid, ".
             "cs.fullname AS coursetitle, cs.shortname AS courseshort, cs.id AS course, sb.submission_filename AS filename, ".
             "sb.submission_objectid AS objectid ".
             "FROM {files} fl ".
             "LEFT JOIN {turnitintooltwo_submissions} sb ON fl.itemid = sb.id ".
             "LEFT JOIN {user} us ON sb.userid = us.id ".
             "LEFT JOIN {context} cx ON fl.contextid = cx.id ".
             "LEFT JOIN {course_modules} cm ON cx.instanceid = cm.id ".
             "LEFT JOIN {turnitintooltwo} tu ON cm.instance = tu.id ".
             "LEFT JOIN {course} cs ON tu.course = cs.id ".
             "WHERE fl.component = 'mod_turnitintooltwo' AND fl.filesize != 0 AND cm.module = :moduleid ".$querywhere.$queryorder;

    $params = array_merge(array('moduleid' => $moduleid), $queryparams);
    $files = $DB->get_records_sql($query, $params, $idisplaystart, $idisplaylength);
    $totalfiles = count($DB->get_records_sql($query, $params));

    $return = array("sEcho" => $secho, "iTotalRecords" => count($files), "iTotalDisplayRecords" => $totalfiles,
                "aaData" => array());

    foreach ($files as $file) {

        if (!empty($ssearch) AND ($file->anon_enabled == 1 AND !is_null($file->unanon) AND !$file->unanon)) {
            $return['iTotalDisplayRecords'] = $return['iTotalDisplayRecords'] - 1;
            continue; // If these are search results and this is anonymised skip it.
        }

        if (is_null($file->activityid)) {
            $assignment = html_writer::tag("span", get_string('assigngeterror', 'turnitintooltwo'),
                                            array("class" => "italic bold"));
        } else {
            $assignment = html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/view.php?id='.$file->cmid,
                                    $file->coursetitle . ' (' . $file->courseshort . ') - ' . $file->activity);
        }

        $filenametodisplay = (empty($file->filename) ? $file->rawfilename : $file->filename);
        $submission = html_writer::link($CFG->wwwroot.'/pluginfile.php/'.$file->contextid.'/mod_turnitintooltwo/submissions/'.
                                    $file->itemid.'/'.$file->rawfilename,
                                        $OUTPUT->pix_icon('fileicon', 'open '.$filenametodisplay, 'mod_turnitintooltwo')." ".
                                        $filenametodisplay);

        if (($file->anon_enabled == 1 && $file->unanon == 1) ||
            ($file->anon_enabled == 0 && (!empty($file->firstname) || !empty($file->lastname)))) {
            $user = html_writer::link($CFG->wwwroot.'/user/view.php?id='.$file->userid,
                                   fullname($file) . '</a> (' . $file->email . ')');
        } else if ($file->anon_enabled == 1 && empty($file->unanon)) {
            $user = get_string('anonenabled', 'turnitintooltwo');
        } else {
            $user = get_string('nosubmissiondataavailable', 'turnitintooltwo');
        }

        $delete = '';
        if (!empty($file->objectid) OR empty($file->filename)) {
            $fnd = array("\n", "\r");
            $rep = array('\n', '\r');
            $attributes["onclick"] = "return confirm('".str_replace($fnd, $rep,
                                                            get_string('filedeleteconfirm', 'turnitintooltwo'))."');";
            $delete = html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/settings_extras.php?cmd=files&file='.
                            $file->id.'&filehash='.$file->hash,
                                html_writer::tag('i', '', array('class' => 'fa fa-trash-o fa-lg')), $attributes);
        }

        $return["aaData"][] = array($assignment, $file->courseshort, $file->coursetitle, $submission,
                                    " ", $user, " ", userdate($file->created), $delete);
    }

    return $return;
}

/**
 * Serves submitted files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function turnitintooltwo_pluginfile($course,
                $cm,
                context $context,
                $filearea,
                $args,
                $forcedownload,
                array $options=array()) {

    $itemid = (int)array_shift($args);
    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/mod_turnitintooltwo/$filearea/$itemid/$relativepath";

    $fs = get_file_storage();
    $relativepath = implode('/', $args);

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Get users for unlinking/relinking. Called from ajax.php via turnitintooltwo.min.js.
 *
 * @global type $DB
 * @return array return array of users to display
 */
function turnitintooltwo_getusers() {
    global $DB;

    $config = turnitintooltwo_admin_config();
    $return = array();
    $idisplaystart = optional_param('iDisplayStart', 0, PARAM_INT);
    $idisplaylength = optional_param('iDisplayLength', 10, PARAM_INT);
    $secho = optional_param('sEcho', 1, PARAM_INT);

    $displaycolumns = array('tu.userid', 'tu.turnitin_uid', 'mu.lastname', 'mu.firstname', 'mu.email');
    $queryparams = array();

    // Add sort to query.
    $isortcol[0] = optional_param('iSortCol_0', null, PARAM_INT);
    $isortingcols = optional_param('iSortingCols', 0, PARAM_INT);
    $queryorder = "";
    if (!is_null( $isortcol[0])) {
        $queryorder = " ORDER BY ";
        $startorder = $queryorder;
        for ($i = 0; $i < intval($isortingcols); $i++) {
            $isortcol[$i] = optional_param('iSortCol_'.$i, null, PARAM_INT);
            $bsortable[$i] = optional_param('bSortable_'.$isortcol[$i], null, PARAM_TEXT);
            $ssortdir[$i] = optional_param('sSortDir_'.$i, null, PARAM_TEXT);
            if ($bsortable[$i] == "true") {
                $queryorder .= $displaycolumns[$isortcol[$i]]." ".$ssortdir[$i].", ";
            }
        }
        if ($queryorder == $startorder) {
            $queryorder = "";
        } else {
            $queryorder = substr_replace($queryorder, "", -2);
        }
    }

    // Add search to query.
    $ssearch = optional_param('sSearch', '', PARAM_TEXT);
    $querywhere = ' WHERE ( ';
    for ($i = 0; $i < count($displaycolumns); $i++) {
        $bsearchable[$i] = optional_param('bSearchable_'.$i, null, PARAM_TEXT);
        if (!is_null($bsearchable[$i]) && $bsearchable[$i] == "true" && $ssearch != '') {
            $include = true;
            if ($i <= 1) {
                if (!is_int($ssearch) || is_null($ssearch)) {
                    $include = false;
                }
            }

            if ($include) {
                $querywhere .= $DB->sql_like($displaycolumns[$i], ':search_term_'.$i, false)." OR ";
                $queryparams['search_term_'.$i] = '%'.$ssearch.'%';
            }
        }
    }
    if ( $querywhere == ' WHERE ( ' ) {
        $querywhere = "";
    } else {
        $querywhere = substr_replace( $querywhere, "", -3 );
        $querywhere .= " )";
    }

    $query = "SELECT tu.id AS id, tu.userid AS userid, tu.turnitin_uid AS turnitin_uid, tu.turnitin_utp AS turnitin_utp, ".
             "mu.firstname AS firstname, mu.lastname AS lastname, mu.email AS email ".
             "FROM {turnitintooltwo_users} tu ".
             "LEFT JOIN {user} mu ON tu.userid = mu.id ".$querywhere.$queryorder;

    $users = $DB->get_records_sql($query, $queryparams, $idisplaystart, $idisplaylength);
    $totalusers = count($DB->get_records_sql($query, $queryparams));

    $return["aaData"] = array();
    foreach ($users as $user) {
        $checkbox = html_writer::checkbox('userids[]', $user->id, false, '', array("class" => "browser_checkbox"));

        $pseudoemail = "";
        if (!empty($config->enablepseudo)) {
            $pseudouser = new TiiPseudoUser(turnitintooltwo_user::get_pseudo_domain());
            $pseudouser->setEmail($user->email);
            $pseudoemail = $pseudouser->getEmail();
        }

        $aadata = array($checkbox);
        $user->turnitin_uid = ($user->turnitin_uid == 0) ? '' : $user->turnitin_uid;
        $userdetails = array($user->turnitin_uid, format_string($user->lastname), format_string($user->firstname), $pseudoemail);
        $return["aaData"][] = array_merge($aadata, $userdetails);
    }
    $return["sEcho"] = $secho;
    $return["iTotalRecords"] = count($users);
    $return["iTotalDisplayRecords"] = $totalusers;
    return $return;
}

/**
 * This is a standard Moodle module that prints out a summary of all activities of this kind in the My Moodle page for a user
 *
 * @param object $courses
 * @param object $htmlarray
 * @global type $USER
 * @global type $CFG
 * @global type $DB
 * @global type $OUTPUT
 * @return bool success
 */
function turnitintooltwo_print_overview($courses, &$htmlarray) {
    global $USER, $CFG, $DB, $OUTPUT;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$turnitintooltwos = get_all_instances_in_courses('turnitintooltwo', $courses)) {
        return;
    }

    $submissioncount = array();
    foreach ($turnitintooltwos as $turnitintooltwo) {
        $turnitintooltwoassignment = new turnitintooltwo_assignment($turnitintooltwo->id, $turnitintooltwo);
        $parts = $turnitintooltwoassignment->get_parts(false);

        $cm = get_coursemodule_from_id('turnitintooltwo', $turnitintooltwo->coursemodule);
        $context = context_module::instance($cm->id);

        $partsarray = array();
        $grader = has_capability('mod/turnitintooltwo:grade', $context);
        if ($grader) {
            $submissionsquery = $DB->get_records_select('turnitintooltwo_submissions',
                            'turnitintooltwoid = ? GROUP BY id, submission_part, submission_grade, submission_gmimaged',
                            array($turnitintooltwo->id), '', 'id, submission_part, submission_grade, submission_gmimaged');
            foreach ($submissionsquery as $submission) {
                if (!isset($submissioncount[$submission->submission_part])) {
                    $submissioncount[$submission->submission_part] = array('graded' => 0, 'submitted' => 0);
                }
                if ($submission->submission_grade != 'NULL' and $submission->submission_gmimaged == 1) {
                    $submissioncount[$submission->submission_part]['graded']++;
                }
                $submissioncount[$submission->submission_part]['submitted']++;
            }
        }

        foreach ($parts as $part) {

            if (!isset($submissioncount[$part->id])) {
                $submissioncount[$part->id] = array('graded' => 0, 'submitted' => 0);
            }

            $partsarray[$part->id]['name'] = $part->partname;
            $partsarray[$part->id]['dtdue'] = $part->dtdue;

            if ($grader) {
                // If user is a grader.
                $numsubmissions = $submissioncount[$part->id]['submitted'];
                $graded = $submissioncount[$part->id]['graded'];
                $input = new stdClass();
                $input->submitted = $numsubmissions;
                $input->graded = $graded;
                $input->total = count_enrolled_users($context, 'mod/turnitintooltwo:submit', 0);
                $input->gplural = ($graded != 1) ? 's' : '';
                $partsarray[$part->id]['status'] = get_string('tutorstatus', 'turnitintooltwo', $input);
            } else {
                // If user is a student.
                $submission = $turnitintooltwoassignment->get_submissions($cm, $part->id, $USER->id, 1);

                if (!empty($submission[$part->id][$USER->id])) {
                    $input = new stdClass();
                    $input->modified = userdate($submission[$part->id][$USER->id]->submission_modified,
                                            get_string('strftimedatetimeshort', 'langconfig'));
                    $input->objectid = $submission[$part->id][$USER->id]->submission_objectid;
                    $partsarray[$part->id]['status'] = get_string('studentstatus', 'turnitintooltwo', $input);
                } else {
                    $partsarray[$part->id]['status'] = get_string('nosubmissions', 'turnitintooltwo');
                }
            }
        }

        $attributes["class"] = ($turnitintooltwo->visible ? "" : "dimmed");
        $attributes["title"] = get_string('modulename', 'turnitintooltwo');
        $assignmentlink = html_writer::link($CFG->wwwroot."/mod/turnitintooltwo/view.php?id=".$turnitintooltwo->coursemodule,
                                $turnitintooltwo->name, $attributes);

        $partsblock = "";
        foreach ($partsarray as $thispart) {
            $partstr = $thispart['name'].' - '.get_string('dtdue', 'turnitintooltwo').': '.userdate($thispart['dtdue'],
                                    get_string('strftimedatetimeshort', 'langconfig'), $USER->timezone);
            $partsblock .= $OUTPUT->box($OUTPUT->box($partstr, 'bold').$OUTPUT->box($thispart['status'], 'italic'), 'info');
        }

        $str = html_writer::tag('div',
                        html_writer::tag('div', get_string('modulename', 'turnitintooltwo').": ".$assignmentlink.$partsblock,
                            array('class' => 'name')), array('class' => 'turnitintooltwo overview'));

        if (empty($htmlarray[$turnitintooltwo->course]['turnitintooltwo'])) {
            $htmlarray[$turnitintooltwo->course]['turnitintooltwo'] = $str;
        } else {
            $htmlarray[$turnitintooltwo->course]['turnitintooltwo'] .= $str;
        }
    }
}

/**
 * Show form to create a new moodle course from the existing Turnitin Course
 *
 * @global type $OUTPUT
 * @return html the form object to create a new course
 */
function turnitintooltwo_show_browser_new_course_form() {
    global $CFG;

    $elements = array();
    $elements[] = array('header', 'create_course_fieldset', get_string('createcourse', 'turnitintooltwo'));
    $displaylist = array();
    $parentlist = array();
    require_once($CFG->dirroot."/course/lib.php");

    if (file_exists($CFG->libdir.'/coursecatlib.php')) {
        require_once($CFG->libdir.'/coursecatlib.php');
        $displaylist = coursecat::make_categories_list('');
    } else {
        make_categories_list($displaylist, $parentlist, '');
    }

    $elements[] = array('select', 'coursecategory', get_string('category'), '', $displaylist);
    $elements[] = array('text', 'coursename', get_string('coursetitle', 'turnitintooltwo'), '');
    $elements[] = array('button', 'create_course', get_string('createcourse', 'turnitintooltwo'));
    $customdata["elements"] = $elements;
    $customdata["hide_submit"] = true;
    $customdata["disable_form_change_checker"] = true;

    $createcourseform = new turnitintooltwo_form('', $customdata);

    return $createcourseform->display();
}

/**
 * Show form to link an existing non-linked moodle course to a selected existing Turnitin Course
 *
 * @global type $DB
 * @global type $OUTPUT
 * @return html the form object containing a drop down of unlinked courses
 */
function turnitintooltwo_show_browser_link_course_form() {
    global $DB, $OUTPUT;

    $output = "";
    $courseids = $DB->get_records("turnitintooltwo_courses", array('course_type' => 'TT'), '', 'courseid');
    if (!empty($courseids)) {
        list($notinsql, $notinparams) = $DB->get_in_or_equal(array_keys($courseids), SQL_PARAMS_QM, 'param', false);
        $unlinkedcoursesquery = $DB->get_records_select('course', "category != 0 AND id ".$notinsql,
                                        $notinparams, 'shortname', 'id, shortname AS name');

        if (!empty($unlinkedcoursesquery)) {
            $unlinkedcourses = array();
            foreach ($unlinkedcoursesquery as $course) {
                $unlinkedcourses[$course->id] = $course->name;
            }

            $elements = array();
            $elements[] = array('header', 'update_course_fieldset', get_string('linkcourse', 'turnitintooltwo'));
            $elements[] = array('select', 'coursetolink', get_string('selectcourse', 'turnitintooltwo'), '', $unlinkedcourses);
            $elements[] = array('button', 'update_course', get_string('linkcourse', 'turnitintooltwo'));
            $customdata["elements"] = $elements;
            $customdata["hide_submit"] = true;
            $customdata["disable_form_change_checker"] = true;

            $updatecourseform = new turnitintooltwo_form('', $customdata);
            $output = $OUTPUT->box(get_string('or', 'turnitintooltwo'), '', 'or_container');
            $output .= $updatecourseform->display();
        }
    }

    return $output;
}

/**
 * Show the table that will display the assignments in course migration
 *
 * @param int the course id to get assignments for
 * @global type $DB
 * @global type $OUTPUT
 * @return html
 */
function turnitintooltwo_init_browser_assignment_table($tiicourseid) {
    global $OUTPUT, $DB;

    $table = new html_table();
    $table->id = "assignmentBrowserTable";
    $output = "";
    $courseid = 0;
    $coursetitle = '';

    $turnitincourse = $DB->get_records_sql("
                            SELECT tc.turnitin_cid, tc.course_type, tc.courseid
                            FROM {turnitintooltwo_courses} tc
                            RIGHT JOIN {course} c ON c.id = tc.courseid
                            WHERE tc.turnitin_cid = ? ", array($tiicourseid)
                        );

    if (!empty($turnitincourse)) {
        $course = current($turnitincourse);
        $coursedetails = turnitintooltwo_assignment::get_course_data($course->courseid, $course->course_type);
        $courseid = $course->courseid;
        $coursetitle = $coursedetails->fullname;
    }

    $class = (empty($coursetitle)) ? ' hidden_class' : '';
    $coursetitle = html_writer::tag('span', $coursetitle, array('id' => 'existing_course_title_span'));
    $output .= html_writer::tag("h3", get_string('courseexistsmoodle', 'turnitintooltwo').$coursetitle,
                                    array('class' => 'existing_course_title_h3'.$class));

    // Do the table headers.
    $cells = array();
    $cells[0] = new html_table_cell('&nbsp;');
    $cells[1] = new html_table_cell(get_string('turnitintooltwoname', 'turnitintooltwo'));
    $cells[2] = new html_table_cell(get_string('maxmarks', 'turnitintooltwo'));
    $cells[3] = new html_table_cell(get_string('turnitinid', 'turnitintooltwo'));
    $table->head = $cells;

    $elements = array();
    $elements[] = array('html', get_string('coursebrowserassignmentdesc', 'turnitintooltwo'));
    $elements[] = array('html', html_writer::table($table));
    $elements[] = array('text', 'assignmentname', get_string('assignmenttitle', 'turnitintooltwo'));
    $elements[] = array('button', 'create_assignment', get_string('downloadassignment', 'turnitintooltwo'));
    $customdata = array();
    $customdata["elements"] = $elements;
    $customdata["hide_submit"] = true;
    $customdata["disable_form_change_checker"] = true;

    $assignmentform = new turnitintooltwo_form('', $customdata);

    $output .= $OUTPUT->box($_SESSION["stored_tii_courses"][$tiicourseid], '', 'tii_course_name');
    $output .= $OUTPUT->box($tiicourseid, '', 'tii_course_id');
    $output .= $OUTPUT->box($courseid, '', 'course_id');
    $output .= $assignmentform->display();

    return $output;
}

/**
 * Show the form to allow an adinistrator to change the end date of a course with in Turnitin
 *
 * @return html
 */
function turnitintooltwo_show_edit_course_end_date_form() {
    $output = html_writer::tag("div", get_string('newenddatedesc', 'turnitintooltwo'), array("id" => "edit_end_date_desc"));

    $elements = array();
    $dateoptions = array('startyear' => date( 'Y', strtotime( '-6 years' )), 'stopyear' => date( 'Y', strtotime( '+6 years' )));
    $elements[] = array('date_selector', 'new_course_end_date',
                    get_string('newcourseenddate', 'turnitintooltwo'), '', $dateoptions);
    $elements[] = array('hidden', 'tii_course_id', '');
    $elements[] = array('hidden', 'tii_course_title', '');
    $elements[] = array('button', 'save_end_date', get_string('savecourseenddate', 'turnitintooltwo'));

    $customdata["elements"] = $elements;
    $customdata["hide_submit"] = true;
    $customdata["disable_form_change_checker"] = true;
    $optionsform = new turnitintooltwo_form('', $customdata);

    return html_writer::tag('div', $output.$optionsform->display(), array('class' => 'edit_course_end_date_form'));
}

/**
 * Moodle participation report hooks for views with Moodle 2.6-
 *
 * @return array Array of available log labels
 */
function turnitintooltwo_get_view_actions() {
    return array('view');
}

/**
 * Moodle participation report hooks for views with Moodle 2.6-
 *
 * @return array Array of available log labels
 */
function turnitintooltwo_get_post_actions() {
    return array('submit');
}

/**
 * @return string Returns a UUID for use within the plugin.
 */
function turnitintooltwo_genUuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff )
    );
}
