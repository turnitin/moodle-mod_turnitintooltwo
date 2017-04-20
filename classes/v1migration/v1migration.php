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

/**
 * Migrate assignments from turnitintool (Moodle Direct v1) to turnitintooltwo (Moodle Direct v2).
 */

class v1migration {

	public $courseid;
	public $v1assignment;

	public function __construct($courseid, $v1assignment) {
		$this->courseid = $courseid;
		$this->v1assignment = $v1assignment;
	}

    /**
     * Return the true if the user proceeds with the migration.
     *
     * @param int $courseid - The course ID.
     * @param int $turnitintooltwoid - The turnitintooltwoid.
     * @return string $output The HTML for the modal.
     */
    function asktomigrate($courseid, $turnitintoolid) {
        global $PAGE;
        $cssurl = new moodle_url('/mod/turnitintooltwo/jquery/colorbox.css');
        $PAGE->requires->css($cssurl);
        $cssurl = new moodle_url('/mod/turnitintooltwo/css/font-awesome.min.css');
        $PAGE->requires->css($cssurl);
        $PAGE->requires->jquery_plugin('turnitintooltwo-migration_tool', 'mod_turnitintooltwo');
        $PAGE->requires->jquery_plugin('turnitintooltwo-colorbox', 'mod_turnitintooltwo');
        $PAGE->requires->jquery_plugin('turnitintooltwo-turnitintooltwo', 'mod_turnitintooltwo');

        $PAGE->requires->string_for_js('closebutton', 'turnitintooltwo');

        $migratelink = html_writer::tag('div', html_writer::tag('i', '', array('class' => 'fa fa-forward fa-lg',
                                                    'title' => get_string('migrateassignment', 'turnitintooltwo')))." ".
                                                    get_string('migrateassignment', 'turnitintooltwo'),
                                                    array('class' => 'migrate_link', 'id' => 'migrate_link',
                                                    'data-courseid' => $courseid, 'data-turnitintoolid' => $turnitintoolid));
        $dontmigratelink = html_writer::tag('div', html_writer::tag('i', '', array('class' => 'fa fa-pause fa-lg',
                                                    'title' => get_string('dontmigrateassignment', 'turnitintooltwo')))." ".
                                                    get_string('dontmigrateassignment', 'turnitintooltwo'),
                                                        array('class' => 'dontmigrate_link', 'id' => 'dontmigrate_link'));
                                                        
        $output = html_writer::tag('div', html_writer::tag('p', get_string('migrationtooltitle', 'turnitintooltwo')
                                        . html_writer::tag('p', get_string('migrationtoolinfo', 'turnitintooltwo'))
                                        . $migratelink . $dontmigratelink
                                        , array('class' => 'migrationtitle')), array('class' => 'hide', 'id' => 'migration_alert'));
        return $output;
    }
	/**
	 * Return the $id of the turnitintooltwo assignment or false.
	 */
	function migrate() {
		global $CFG, $DB;

		// Migrate Users.
		$this->migrate_users();

        // Migrate course.
        $v1course = $DB->get_record('turnitintool_courses', array('courseid' => $this->courseid));
        $this->migrate_course($v1course);

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

        // Create new Turnitintooltwo object.
        require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');
        $turnitintooltwoassignment = new turnitintooltwo_assignment($turnitintooltwoid);

        // Get the assignment parts.
        $v1parts = $DB->get_records('turnitintool_parts', array('turnitintoolid' => $this->v1assignment->id));

        // Migrate the parts.
        foreach ($v1parts as $v1part) {
            $v1part->turnitintooltwoid = $turnitintooltwoid;
            $v1partid = $v1part->id;
            unset($v1part->turnitintoolid);
            unset($v1part->id);

            $v2partid = $DB->insert_record("turnitintooltwo_parts", $v1part);

            // Get the submissions for this part.
            $v1partsubmissions = $DB->get_records('turnitintool_submissions', array('submission_part' => $v1partid));

            // Create submission object.
            require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_submission.class.php');
            $submission = new turnitintooltwo_submission();

            foreach ($v1partsubmissions as $v1partsubmission) {
                $v1partsubmission->turnitintooltwoid = $turnitintooltwoid;
                $v1partsubmission->submission_part = $v2partid;

                // WILL NEED TO REJIG THIS IN FINAL VERSION.
                // We can't leave as is, otherwise we could have a clash with existing V2 assignment hashes.
                $v1partsubmission->submission_hash = rand(1000, 100000000);;

                unset($v1partsubmission->turnitintoolid);
                unset($v1partsubmission->id);

                $turnitintooltwosubmissionid = $DB->insert_record("turnitintooltwo_submissions", $v1partsubmission);

                // Get the V2 part and update grade book.
                $v2partsubmission = $DB->get_record("turnitintooltwo_submissions", array("id" => $turnitintooltwosubmissionid));
                $submission->update_gradebook($v2partsubmission, $turnitintooltwoassignment);
            }
        }

        $this->update_titles_post_migration($v1course->courseid, $v2course->courseid, $turnitintooltwoid);
        
        // Commit transaction.
        $transaction->allow_commit();

        return (is_int($turnitintooltwoid)) ? $turnitintooltwoid : false;
	}

    /**
     * Update module titles after migration has completed.
     * @param int $v1courseid Moodle course ID for v1 assignment.
     * @param int $v2courseid Moodle course ID for v2 assignment.
     * @param int $v2assignmentid V2 Module id
     */
    function update_titles_post_migration($v1courseid, $v2courseid, $v2assignmentid) {
        global $DB;

        // Update the assignment title with new status.
        $updatetitle = new stdClass();
        $updatetitle->id = $this->v1assignment->id;
        $updatetitle->name = $this->v1assignment->name . ' (Migrated)';
        $DB->update_record('turnitintool', $updatetitle);

        $cm = get_coursemodule_from_instance('turnitintool', $this->v1assignment->id);
        rebuild_course_cache($cm->id);

        // Update the V1 assignment title in the gradebook.
        @include_once($CFG->dirroot."/lib/gradelib.php");
        $params = array();
        $params['itemname'] = $updatetitle->name;
        grade_update('mod/turnitintool', $v1courseid, 'mod', 'turnitintool', $this->v1assignment->id, 0, NULL, $params);

        // Update the V2 assignment title in the gradebook.
        $params['itemname'] = $this->v1assignment->name;
        grade_update('mod/turnitintooltwo', $v2courseid, 'mod', 'turnitintooltwo', $v2assignmentid, 0, NULL, $params);
    }

    /** 
     * Hide the V1 assignment and rename the title to show "Migration in progress".
     */
    function hide_v1_assignment() {
        global $CFG, $DB;

        // Edit the V1 assignment title.
        $updatetitle = new stdClass();
        $updatetitle->id = $this->v1assignment->id;
        $updatetitle->name = $this->v1assignment->name . ' (Migration in progress...)';
        $updatetitle->migrated = 1;
        $DB->update_record('turnitintool', $updatetitle);

        // Hide the V1 assignment.
        $cm = get_coursemodule_from_instance('turnitintool', $this->v1assignment->id);

        require_once($CFG->dirroot."/course/lib.php");
        set_coursemodule_visible($cm->id, 0);
    }

    /**
     * Set up a V2 module.
     * @param int $courseid Moodle course ID
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     */
    function setup_v2_module($courseid, $turnitintooltwoid) {
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
    function set_default_values() {
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
	 *  Migrate the users from v1 to v2 - only if the user does not already exist in turnitintooltwo_users.
	 */
	function migrate_users() {
		global $DB;

        $turnitintoolusers = $DB->get_records('turnitintool_users', NULL, NULL, 'userid, turnitin_uid, turnitin_utp');
        foreach ($turnitintoolusers as $turnitintooluser) {
            unset($turnitintooluser->id);

            if (!$DB->record_exists("turnitintooltwo_users", array('userid' => $turnitintooluser->userid))) {
                $DB->insert_record("turnitintooltwo_users", $turnitintooluser);
            }
        }
	}

    /**
     *  Migrate the users from v1 to v2 - only if the user does not already exist in turnitintooltwo_users.
     *
     * @param Object $v1course - The course object for the V1 assignment we are migrating.
     */
    function migrate_course($v1course) {
        global $DB;

        // We may have more than one course if the course contained V2 assignments prior to the first V1 migration.
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('courseid' => $this->courseid));

        // Check each course to see if we can use an existing course for this migration.
        foreach ($v2courses as $v2course) {
            if (($v2course->course_type == "TT") && ($v2course->turnitin_cid == $v1course->turnitin_cid)) {
                return;
            } elseif ($v2course->course_type == "V1") {
                // This flag is used to call the correct course from turnitintooltwo_courses table in cases where we have a second course.
                $this->v1assignment->legacy = 1;

                return;
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
        $DB->insert_record('turnitintooltwo_courses', $v2course);
    }
}



/* Tests
    1. V1 migration with no V2 assignments. Should create entry in turnitintooltwo_courses table, course type TT. Legacy field = 0.
    2. Second V1 migration with no V2 assignments other than above migrated assignment. Should NOT create entry in turnitintooltwo_courses table. Legacy field = 0.

    Create a new course and add a V2 assignment.

    3. V1 migration with V2 assignment. Should create entry in turnitintooltwo_courses table, course type V1. Legacy field = 1.
    4. Second V1 migration with V2 assignment. Should NOT create entry in turnitintooltwo_courses table. Legacy field = 1.
*/