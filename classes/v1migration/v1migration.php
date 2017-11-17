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

defined('MOODLE_INTERNAL') || die();

define('MIGRATION_SUBMISSIONS_SITE_CUTOFF', 200);
define('MIGRATION_MAX_SLEEP', 5);

/**
 * Migrate assignments from turnitintool (Moodle Direct v1) to turnitintooltwo (Moodle Direct v2).
 */

class v1migration {

	public $courseid;
	public $v1assignment;

	private $cm;

	public function __construct($courseid, $v1assignment) {
		$this->courseid = $courseid;
		$this->v1assignment = $v1assignment;
		$this->cm = get_coursemodule_from_instance('turnitintool', $this->v1assignment->id);
	}

    /**
     * Save the status of the migration tool
     */
    public static function togglemigrationstatus($value) {
        global $DB;

        $migrationsetting = $DB->get_record('config_plugins', array('plugin' => 'turnitintooltwo', 'name' => 'enablemigrationtool'));
        if (!$migrationsetting) {
            $migrationsetting = new stdClass();
            $migrationsetting->plugin = 'turnitintooltwo';
            $migrationsetting->name = 'enablemigrationtool';
        }
        $migrationsetting->value = (int)$value;

        // Save migration setting to the database.
        $method = (isset($migrationsetting->id)) ? 'update_record' : 'insert_record';
        if ($DB->$method('config_plugins', $migrationsetting)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return the HTML for the progress bar.
     */
    public static function output_progress_bar() {
        global $DB;

        // Begin progress bar.
        $output = html_writer::tag('p', get_string('migrationtoolprogress', 'turnitintooltwo'));

        // Counts for use in the progress bar.
        $totalv1 = $DB->count_records('turnitintool');
        $totalmigrated = $DB->count_records('turnitintool', array('migrated' => 1));

        if ($totalv1 == 0) {
            $complete = 100;
        } else {
            $complete = floor(($totalmigrated/$totalv1)*100);
        }

        // Progress bar text.
        $percentcompletedtext = $complete.'% '. get_string('complete', 'turnitintooltwo');
        $numassignmentstext = '('.$totalmigrated.'/'.$totalv1.' '. get_string('assignments', 'turnitintooltwo').')';
        $completedtext = html_writer::tag('span', $percentcompletedtext.' '.$numassignmentstext);

        // If migration is less than a third complete then show the text after the completed section of the progress bar.
        if ( $complete < 33 ) {
            $divwidth = 100 - $complete;
            $completedtext = html_writer::tag('div', $completedtext, array('id' => 'migration-progress-todo', 'style' => 'width: '.$divwidth.'%'));
            $progress = html_writer::tag('div', '', array('id' => 'migration-progress', 'style' => 'width: '.$complete.'%')).$completedtext;
        } else {
            $progress = html_writer::tag('div', $completedtext, array('id' => 'migration-progress', 'style' => 'width: '.$complete.'%'));
        }

        // Output our progress bar.
        $output .= html_writer::tag('div', $progress, array('id' => 'migration-progress-bar', 'class' => 'active'));

        return $output;
    }

    /**
     * Return the true if the user proceeds with the migration.
     *
     * @param int $courseid - The course ID.
     * @param int $turnitintooltwoid - The turnitintooltwoid.
     * @return string $output The HTML for the modal.
     */
    public function migrate_modal($courseid, $turnitintoolid) {
        global $PAGE;
        $cssurl = new moodle_url('/mod/turnitintooltwo/jquery/colorbox.css');
        $PAGE->requires->css($cssurl);
        $cssurl = new moodle_url('/mod/turnitintooltwo/css/font-awesome.min.css');
        $PAGE->requires->css($cssurl);
        $PAGE->requires->jquery_plugin('turnitintooltwo-colorbox', 'mod_turnitintooltwo');
        $PAGE->requires->jquery_plugin('turnitintooltwo-turnitintooltwo', 'mod_turnitintooltwo');
        $PAGE->requires->jquery_plugin('turnitintooltwo-migration_tool', 'mod_turnitintooltwo');

        $PAGE->requires->string_for_js('closebutton', 'turnitintooltwo');

        $migratelink = html_writer::tag('button', get_string('migrateassignment', 'turnitintooltwo'),
                                                    array('class' => 'migrate_link btn-primary', 'id' => 'migrate_link',
                                                    'data-courseid' => $courseid, 'data-turnitintoolid' => $turnitintoolid));
        $dontmigratelink = html_writer::tag('button', get_string('dontmigrateassignment', 'turnitintooltwo'),
                                                        array('class' => 'dontmigrate_link btn-default', 'id' => 'dontmigrate_link'));

        $spinner = html_writer::tag('i', '', array('class' => 'fa fa-spinner fa-spin fa-2x'));
        $spinner = html_writer::tag('div', $spinner, array('class' => 'migration-spinner'));

        $migrating = html_writer::tag('div', html_writer::tag('p', get_string('migrating', 'turnitintooltwo'))
                                        . $spinner
                                        . html_writer::tag('p', get_string('migrationredirect', 'turnitintooltwo')),
                                        array('id' => 'migrating', 'class' => 'hide'));

        $asktomigrate = html_writer::tag('div', html_writer::tag('p', get_string('migrationtooltitle', 'turnitintooltwo'), array('class' => 'migrationtitle'))
                                        . html_writer::tag('p', get_string('migrationtoolinfo', 'turnitintooltwo'), array('class' => 'migrationtoolinfo'))
                                        . $migratelink . $dontmigratelink
                                        , array('id' => 'asktomigrate', 'class' => 'hide'));

        $output = html_writer::tag('div', $asktomigrate . $migrating, array('id' => 'migration_alert', 'class' => 'hide'));

        return $output;
    }

	/**
	 * Return the $id of the turnitintooltwo assignment or false.
	 */
	public function migrate() {
		global $DB;

        // Migrate course.
        $v1course = $DB->get_record('turnitintool_courses', array('courseid' => $this->courseid));

        $v2course = $this->migrate_course($v1course);

        // Initialise any null values that are now not allowed.
        $this->set_default_values();

        // Begin transaction. If this doesn't complete then nothing is migrated.
        $transaction = $DB->start_delegated_transaction();

        // Insert V1 assignment into V2 table.
        $turnitintooltwoid = $DB->insert_record("turnitintooltwo", $this->v1assignment);

        // Hide the v1 assignment.
        $this->hide_v1_assignment();

        // Set up a V2 course module.
        $this->setup_v2_module($this->courseid, $turnitintooltwoid);

        // Get the assignment parts.
        $v1parts = $DB->get_records('turnitintool_parts', array('turnitintoolid' => $this->v1assignment->id));

        // Migrate the parts.
        foreach ($v1parts as $v1part) {
            $v1part->turnitintooltwoid = $turnitintooltwoid;
            $v1partid = $v1part->id;
            // Save the timestamp when the grades were updated.
            if (!empty($_SESSION["migrationtool"][$this->v1assignment->id]["gradesupdated"])) {
                $v1part->gradesupdated = $_SESSION["migrationtool"][$this->v1assignment->id]["gradesupdated"];
            }
            unset($v1part->turnitintoolid);
            unset($v1part->id);

            $v2partid = $DB->insert_record("turnitintooltwo_parts", $v1part);

            // Get the submissions for this part.
            $v1partsubmissions = $DB->get_records('turnitintool_submissions', array('submission_part' => $v1partid));

            $migratedsubs = array();
            foreach ($v1partsubmissions as $v1partsubmission) {
                $v1partsubmission->turnitintooltwoid = $turnitintooltwoid;
                $v1partsubmission->submission_part = $v2partid;
                $v1partsubmission->migrate_gradebook = 1;

                // Submission hash must be unique. However, there may be situations where a user erroneously has multiple submissions.
                // If this is the case then we only want to migrate the latest submission.
                $userid = (empty($v1partsubmission->userid)) ? $v1partsubmission->submission_nmuserid : $v1partsubmission->userid;
                $v1partsubmission->submission_hash = $userid.'_'.$turnitintooltwoid.'_'.$v2partid;

                unset($v1partsubmission->turnitintoolid);
                unset($v1partsubmission->id);

                // Migrate user to v2 if necessary.
                if (!empty($v1partsubmission->userid)) {
                    $this->migrate_user($v1partsubmission->userid);
                }

                // If hash has not been used then insert new record
                if (empty($migratedsubs[$v1partsubmission->submission_hash])) {
                    $turnitintooltwosubmissionid = $DB->insert_record("turnitintooltwo_submissions", $v1partsubmission);
                    $migratedsubs[$v1partsubmission->submission_hash] = $turnitintooltwosubmissionid;
                } else {
                    // Overwrite older submission if hash has already been used.
                    $oldersubmission = $DB->get_record('turnitintooltwo_submissions', array('submission_hash' => $v1partsubmission->submission_hash));
                    if ($oldersubmission) {
                        $v1partsubmission->id = $oldersubmission->id;
                        $turnitintooltwosubmissionid = $DB->update_record("turnitintooltwo_submissions", $v1partsubmission);
                    }
                }
            }
        }

        // Commit transaction.
        $transaction->allow_commit();

        // Logs the successful migration event to the Moodle Log
        $this->log_success_migration_event($turnitintooltwoid, $this->courseid, $this->cm);

        // Update gradebook for submissions.
        $gradeupdates = $this->migrate_gradebook($turnitintooltwoid);

        // Only change the titles if we have updated the grades.
        if ($gradeupdates == "migrated") {
            $this->update_titles_post_migration($turnitintooltwoid);
        }

        // Link the v2 id to the v1 id in the session.
        if (is_int($turnitintooltwoid)) {
            $_SESSION["migrationtool"][$this->v1assignment->id] = $turnitintooltwoid;

            return $turnitintooltwoid;
        } else {
            return false;
        }
	}

    /**
     * Hide the V1 assignment and rename the title to show "Migration in progress".
     */
    public function hide_v1_assignment() {
        global $CFG, $DB;

        // Edit the V1 assignment title.
        $updatetitle = new stdClass();
        $updatetitle->id = $this->v1assignment->id;
        $updatetitle->migrated = 1;

        // Create the postfix for the title "(Migration in progress...)"
        $postfix = sprintf('(%s...)', get_string('migrationinprogress', 'turnitintooltwo'));

        // Create the full title with the postfix. E.g. "title 101 (Migration in progress...)"
        $fulltitle = sprintf('%s %s', $this->v1assignment->name, $postfix);

        // If the full title, with postfix is over 255 (the moodle DB limit for this field)
        // then we need to truncate the title and add the postfix title.
        if (strlen($fulltitle) > 255) {
	        // Truncate the title and account for the space by deducting 1.
	        $fulltitle = sprintf('%s %s', substr($this->v1assignment->name, 0, 254 - strlen($postfix)), $postfix);
        }

        $updatetitle->name = $fulltitle;

        $DB->update_record('turnitintool', $updatetitle);

        // Hide the V1 assignment.
        require_once($CFG->dirroot."/course/lib.php");
        set_coursemodule_visible($this->cm->id, 0);
    }

    /**
     * Set up a V2 module.
     * @param int $courseid Moodle course ID
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     */
    public function setup_v2_module($courseid, $turnitintooltwoid) {
        global $DB;

        $module = $DB->get_record("modules", array("name" => "turnitintooltwo"));
        $coursemodule = new stdClass();
        $coursemodule->course = $courseid;
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
        rebuild_course_cache($coursemodule->coursemodule);
    }

    /**
     * Initialise any values from old assignments that can not now be null but have been when the assignment was created.
     */
    public function set_default_values() {
        $nullcheckfields = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat',
                            'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
                            'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook',
                            'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch');

        foreach ($nullcheckfields as $field) {
            $this->v1assignment->$field = (is_null($this->v1assignment->$field)) ? 0 : $this->v1assignment->$field;
        }
        $this->v1assignment->excludetype = (is_null($this->v1assignment->excludetype)) ? 1 : $this->v1assignment->excludetype;
        $this->v1assignment->perpage = (is_null($this->v1assignment->perpage)) ? 25 : $this->v1assignment->perpage;
    }

	/**
	 * Migrate a user from v1 to v2 - only if the user does not already exist in turnitintooltwo_users.
     *
     * @param int $userid - the moodle user id to migrate
	 */
	public function migrate_user($userid) {
		global $DB;

        // Get user link from V1 table.
        $turnitintooluser = $DB->get_record("turnitintool_users", array('userid' => $userid), 'userid, turnitin_uid, turnitin_utp');
        // Check if user link exists in V2 table.
        $turnitintooltwouser = $DB->get_record("turnitintooltwo_users", array('userid' => $userid), 'userid');

        if ($turnitintooluser && !$turnitintooltwouser) {
            $DB->insert_record("turnitintooltwo_users", $turnitintooluser);
        }
	}

    /**
     *  Migrate the users from v1 to v2 - only if the user does not already exist in turnitintooltwo_users.
     *
     * @param Object $v1course - The course object for the V1 assignment we are migrating.
     */
    public function migrate_course($v1course) {
        global $DB;

        // We may have more than one course if the course contained V2 assignments prior to the first V1 migration.
        $select = "courseid = " . $this->courseid . " AND course_type != 'PP'";
        $v2courses = $DB->get_records_select('turnitintooltwo_courses', $select);

        // Check each course to see if we can use an existing course for this migration.
        foreach ($v2courses as $v2course) {
            if (($v2course->course_type == "TT") && ($v2course->turnitin_cid == $v1course->turnitin_cid)) {
                return $v2course;
            } elseif ($v2course->course_type == "V1") {
                // This flag is used to call the correct course from turnitintooltwo_courses table in cases where we have a second course.
                $this->v1assignment->legacy = 1;

                return $v2course;
            }
        }

        // If there are V2 courses and we did not return during the above checks, we are migrating the first assignment on a course with pre-existing V2 assignments.
        if (count($v2courses) > 0) {
            $coursetype = "V1";

            // This flag is used to call the correct course from turnitintooltwo_courses table in cases where we have a second course.
            $this->v1assignment->legacy = 1;
        } else {
            $coursetype = "TT";
        }

        // As we didn't return during the above checks, we need to insert a new course.
        $v2course = new stdClass();
        $v2course->courseid = $v1course->courseid;
        $v2course->ownerid = $v1course->ownerid;
        $v2course->turnitin_ctl = $v1course->turnitin_ctl;
        $v2course->turnitin_cid = $v1course->turnitin_cid;
        $v2course->course_type = $coursetype;
        $v2course->migrated = 1;

        // Insert the course to the turnitintooltwo courses table.
        $id = $DB->insert_record('turnitintooltwo_courses', $v2course);
        $v2course->id = $id;

        return $v2course;
    }

    /**
     * Update the gradebook for a given assignment.
     * @param int $turnitintooltwoid The turnitintooltwoid of the assignment.
     * @param string $workflow Whether the function is called from the site or the cron.
     * @return string Whether we have migrated the assignment or need to use the cron.
     */
    public static function migrate_gradebook($turnitintooltwoid, $workflow = "site") {
        global $CFG, $DB;

        // Create new Turnitintooltwo object.
        require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');
        require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_submission.class.php');

        $assignmentclass = new turnitintooltwo_assignment($turnitintooltwoid);
        $submissionclass = new turnitintooltwo_submission();

        // Get the submissions for this assignment, or all submissions requiring a gradebook update.
        $submissions = $DB->get_records("turnitintooltwo_submissions", array("turnitintooltwoid" => $turnitintooltwoid, "migrate_gradebook" => 1));

        // Add an artificial sleep to give the instructor time to read the migration processing modal.
        if ($workflow == "site") {
            $migrationspersleepsecond = floor(MIGRATION_SUBMISSIONS_SITE_CUTOFF/MIGRATION_MAX_SLEEP);
            sleep(round(max(MIGRATION_MAX_SLEEP - (count($submissions)/$migrationspersleepsecond), 0)));
        }

        /**
         * Grade migrations are slow, roughly 27 submissions per second.
         * As such we only migrate these during the assignment migration if there are not a lot of them. If there are a lot, we use the cron.
         * We have set this value to MIGRATION_SUBMISSIONS_CUTOFF, meaning a wait time of roughly 8 seconds.
         */
        if (($workflow == "site") && (count($submissions) > MIGRATION_SUBMISSIONS_SITE_CUTOFF)) {
            return "cron";
        } else {
            foreach ($submissions as $submission) {
                $submissionclass->update_gradebook($submission, $assignmentclass);

                // Update the migrate_gradebook field for this submission.
                $updatesubmission = new stdClass();
                $updatesubmission->id = $submission->id;
                $updatesubmission->migrate_gradebook = 0;

                $DB->update_record('turnitintooltwo_submissions', $updatesubmission);
            }

            return "migrated";
        }
    }

    /**
     * Update module titles after migration has completed.
     * @param int $v2assignmentid V2 Module id
     */
    public function update_titles_post_migration($v2assignmentid) {
        global $CFG, $DB;

        // Remove the migration in progress text.
        $this->v1assignment->name = str_replace(" (" . get_string('migrationinprogress', 'turnitintooltwo') . "...)", "", $this->v1assignment->name);

        // Update the assignment title with new status.
        $updatetitle = new stdClass();
        $updatetitle->id = $this->v1assignment->id;
        $updatetitle->name = $this->v1assignment->name . ' ('. get_string('migrated', 'turnitintooltwo') . ')';

        $DB->update_record('turnitintool', $updatetitle);

        // Temporarily set the assignment to visible so that the cron can rebuild the course cache for this assignment,
        set_coursemodule_visible($this->cm->id, 1);
        rebuild_course_cache($this->cm->id);
        set_coursemodule_visible($this->cm->id, 0);

        // Update the V1 assignment title in the gradebook.
        @include_once($CFG->dirroot."/lib/gradelib.php");
        $params = array();
        $params['itemname'] = $updatetitle->name;
        grade_update('mod/turnitintool', $this->courseid, 'mod', 'turnitintool', $this->v1assignment->id, 0, NULL, $params);

        // Update the V2 assignment title in the gradebook.
        $params['itemname'] = $this->v1assignment->name;
        grade_update('mod/turnitintooltwo', $this->courseid, 'mod', 'turnitintooltwo', $v2assignmentid, 0, NULL, $params);
    }

    /**
     * Logs the successful migration event to the Moodle log.
     */
    private function log_success_migration_event($turnitintooltwoid, $course_id, $v1cm) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/turnitintooltwo/lib.php');

        // Get the Course Module for the new  V2 assignment.
        $v2cm = get_coursemodule_from_instance('turnitintooltwo', $turnitintooltwoid);

        $success = new stdClass();
        $success->v1_name = $v1cm->name;
        $success->v1_cm_id = $v1cm->id;
        $success->v2_cm_id = $v2cm->id;

        // Add to log.
        turnitintooltwo_add_to_log(
            $course_id,
            "migrate assignment",
            'view.php?id=' . $v2cm->id,
            get_string('migration_event_desc', 'turnitintooltwo', $success),
            $v2cm->id
        );
    }

    /**
     * Get assignments for migrated data table. Called from ajax.php via turnitintooltwo_extra.min.js.
     *
     * @global type $DB
     * @return array return array of assignments to display
     */
    public static function turnitintooltwo_getassignments() {
        global $DB;

        $config = get_config('turnitintooltwo');

        $return = array();
        $idisplaystart = optional_param('iDisplayStart', 0, PARAM_INT);
        $idisplaylength = optional_param('iDisplayLength', 10, PARAM_INT);
        $secho = optional_param('sEcho', 1, PARAM_INT);
        $displaycolumns = array('', 'id', 'name', 'migrated');
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
        $query = "SELECT id, name, migrated FROM {turnitintool}".$querywhere.$queryorder;
        $assignments = $DB->get_records_sql($query, $queryparams, $idisplaystart, $idisplaylength);
        $totalassignments = count($DB->get_records_sql($query, $queryparams));
        $return["aaData"] = array();
        foreach ($assignments as $assignment) {
            if ($assignment->migrated == 1) {
                $checkbox = html_writer::checkbox('assignmentids[]', $assignment->id, false, '', array("class" => "browser_checkbox"));
                $sronly = html_writer::tag('span', get_string('yes', 'turnitintooltwo'), array('class' => 'sr-only'));
                $assignment->migrated = html_writer::tag('span', $sronly, array('class' => 'fa fa-check'));

                $assignmenttitle = format_string($assignment->name);

            } else {
                $checkbox = "";
                $sronly = html_writer::tag('span', get_string('no', 'turnitintooltwo'), array('class' => 'sr-only'));
                $assignment->migrated = html_writer::tag('span', $sronly, array('class' => 'fa fa-times'));

                $assignmentlink = new moodle_url('/mod/turnitintool/view.php', array('a' => $assignment->id, 'id' => '0'));
                $assignmenttitle = html_writer::link($assignmentlink, format_string($assignment->name), array('target' => '_blank' ));
            }
            $return["aaData"][] = array($checkbox, $assignment->id, $assignmenttitle, $assignment->migrated);
        }
        $return["sEcho"] = $secho;
        $return["iTotalRecords"] = count($assignments);
        $return["iTotalDisplayRecords"] = $totalassignments;
        return $return;
    }

    /**
     * Delete a list of assignments.
     *
     * @param array $assignmentids The assignment IDs to delete.
     */
    public static function turnitintooltwo_delete_assignments($assignmentids) {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/mod/turnitintool/lib.php");

        foreach ($assignmentids as $assignmentid) {
            $cm = get_coursemodule_from_instance('turnitintool', $assignmentid);

            // We have found that backups aren't reliable on MSSQL so rather than use Moodle's
            // function which uses the recycle tool and the backup procedure. We handle the deletion directly.
            if ($CFG->dbtype == 'mssql' || $CFG->dbtype == 'sqlsrv') {
                turnitintool_delete_instance($assignmentid);

                // Delete course module.
                $DB->delete_records('course_modules', array('id' => $cm->id));

                rebuild_course_cache($cm->course);
            } else {
                course_delete_module($cm->id);
            }
            
        }
    }

    /**
     * Check that v1 and v2 account ids are the same.
     */
    public static function check_account_ids() {
        global $CFG;

        // Check that v1 and v2 Account Ids are the same.
        $v1accountid = $CFG->turnitin_account_id;
        $v2config = turnitintooltwo_admin_config();

        // If they are different then disable the form and show user a warning.
        $enabled = (int)(boolval($v1accountid == $v2config->accountid));

        // Turn the Migration Tool off if account IDs are different.
        if (!$enabled) {
            v1migration::togglemigrationstatus(0);
            return false;
        }

        return true;
    }

    /**
     * Output the settings form to enable v1 migration.
     * @param $enablesetting - whether the settings form should be enabled.
     */
    public static function output_settings_form($enablesetting = true) {
        global $CFG, $DB, $PAGE;
        $output = "";

        require_once($CFG->dirroot.'/mod/turnitintooltwo/turnitintooltwo_form.class.php');

        if (!$enablesetting) {
            $close = html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert'));
            $output .= html_writer::tag('div', $close.get_string('migrationtoolaccounterror', 'turnitintooltwo'),
                            array('class' => 'alert alert-error', 'role' => 'alert'));
        }

        // Get current migration setting value.
        $migrationsettings = array();
        $currentsetting = $DB->get_record('config_plugins', array('plugin' => 'turnitintooltwo', 'name' => 'enablemigrationtool'));
        if ($currentsetting) {
            $migrationsettings = array('enablemigrationtool' => $currentsetting->value);
        }

        $output .= html_writer::tag('h2', get_string('v1migrationsubtitle', 'turnitintooltwo'), array('class' => 'migrationheader'));

        $output .= html_writer::tag('p', get_string('migrationtoolintro', 'turnitintooltwo'));

        // Add hidden value to page so we can disable the select box if necessary.
        $output .= html_writer::tag('div', '', 
                            array('id' => 'sametiiaccount', 'data-sametiiaccount' => (int)$enablesetting, 'class' => 'hidden'));

        $options = array(
                    0 => get_string('migration:off', 'turnitintooltwo'),
                    1 => get_string('migration:manual', 'turnitintooltwo'),
                    2 => get_string('migration:auto', 'turnitintooltwo')
                    );

        $elements[] = array('select', 'enablemigrationtool', get_string('enablemigrationtool','turnitintooltwo'), 
                            'enablemigrationtool', $options);
        $customdata["elements"] = $elements;
        $customdata["disable_form_change_checker"] = true;
        $customdata["show_cancel"] = false;

        // Strings for javascript confirm deletion.
        $PAGE->requires->string_for_js('confirmv1deletetitle', 'turnitintooltwo');
        $PAGE->requires->string_for_js('confirmv1deletetext', 'turnitintooltwo');
        $PAGE->requires->string_for_js('confirmv1deletewarning', 'turnitintooltwo');
        
        $migrationform = new turnitintooltwo_form($CFG->wwwroot.'/mod/turnitintooltwo/settings_extras.php?cmd=v1migration',
                                                    $customdata);

        $migrationform->set_data( $migrationsettings );

        $output .= html_writer::tag('div', $migrationform->display(), array('id' => 'migrationform'));

        return $output;
    }

    /**
     * activate_migration
     * Updates the database to flag that the user has enabled the migration tool.
     * @return object $activation - db record of the migration activation row inserted into config_plugins.
     */
    public static function activate_migration() {
        global $DB;
        $migration_enabled_params = array(
            'plugin' => 'turnitintooltwo',
            'name' => 'migration_enabled'
        );
        $migration_enabled = $DB->get_record('config_plugins', $migration_enabled_params);

        $activation_properties = new stdClass;
        $activation_properties->plugin = 'turnitintooltwo';
        $activation_properties->name = 'migration_enabled';
        $activation_properties->value  = 1;

        if (empty($migration_enabled)) {
            $activation = $DB->insert_record('config_plugins', $activation_properties);
        } else {
            $id = $migration_enabled->id;
            $activation = $DB->update_record('config_plugins', array('id' => $id, 'value' => 1));
        }
        
        return $activation;
    }

    public static function check_account($accountid) {
        global $CFG;

        $config = turnitintooltwo_admin_config();

        $tiiapiurl = (substr($config->apiurl, -1) == '/') ? substr($config->apiurl, 0, -1) : $config->apiurl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tiiapiurl."/api/rest/check?lang=en_us&operation=mdl-migration&account=".$accountid);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (isset($CFG->proxyhost) AND !empty($CFG->proxyhost)) {
            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }
        if (isset($CFG->proxyuser) AND !empty($CFG->proxyuser)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', $CFG->proxyuser, $CFG->proxypassword));
        }

        curl_exec($ch);
        curl_close($ch);
    }
}
