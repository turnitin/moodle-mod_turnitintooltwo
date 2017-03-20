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
	 * Return the $id of the turnitintooltwo assignment or false.
	 */
	function migrate() {
		global $CFG, $DB;

		// Migrate Users.
		$this->migrate_users();

		/**
         * Handle situation where a V2 course already exists.
         * THIS WILL DIFFER IN THE ACTUAL MIGRATION TOOL.
         * In the actual version we will insert a new course and handle the situation of two Turnitin class IDs in v2.
         */
        $v1course = $DB->get_record('turnitintool_courses', array('courseid' => $this->courseid));
        $v2course = $DB->get_record('turnitintooltwo_courses', array('courseid' => $v1course->courseid, 'course_type' => 'TT'));
        if (!$v2course) {
            // Insert the course to the Turnitintooltwo courses table.
            $turnitincourse = new stdClass();
            $turnitincourse->courseid = $v1course->courseid;
            $turnitincourse->ownerid = $v1course->ownerid;
            $turnitincourse->turnitin_ctl = $v1course->turnitin_ctl;
            $turnitincourse->turnitin_cid = $v1course->turnitin_cid;
            $turnitincourse->course_type = 'TT';
            $turnitincourse->migrated = 1;

            $DB->insert_record('turnitintooltwo_courses', $turnitincourse);
        } else {
            $update = new stdClass();
            $update->id = $v1course->id;
            $update->turnitin_cid = $v2course->turnitin_cid;
            $DB->update_record('turnitintool_courses', $update);
            $update->id = $v2course->id;
            $update->migrated = 1;
            $DB->update_record('turnitintooltwo_courses', $update);
        }

        // For old assignments we may encounter null values in fields where they can't be null, check all values.
        $nullchecks = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat',
        					'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
        					'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook',
        					'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch');

        foreach ($nullchecks as $k => $v) {
            $this->v1assignment->$v = (is_null($this->v1assignment->$v)) ? 0 : $this->v1assignment->$v;
        }
        $this->v1assignment->excludetype = (is_null($this->v1assignment->excludetype)) ? 1 : $this->v1assignment->excludetype;
        $this->v1assignment->perpage = (is_null($this->v1assignment->perpage)) ? 25 : $this->v1assignment->perpage;

        // Begin transaction. If this doesn't complete then nothing is migrated.
        $transaction = $DB->start_delegated_transaction();

        // Insert V1 assignment into V2 table.
        $turnitintooltwoid = $DB->insert_record("turnitintooltwo", $this->v1assignment);

        // Set the title to append migration in process status for V1 assignment.
        $updatetitle = new stdClass();
        $updatetitle->id = $this->v1assignment->id;
        $updatetitle->name = $this->v1assignment->name . ' (Migration in process...)';
        $updatetitle->migrated = 1;
        $DB->update_record('turnitintool', $updatetitle);

        // Hide the V1 assignment.
        $cm = get_coursemodule_from_instance('turnitintool', $this->v1assignment->id);
        set_coursemodule_visible($cm->id, 0);

        // Set up a V2 course module.
        $module = $DB->get_record("modules", array("name" => "turnitintooltwo"));
        $coursemodule = new stdClass();
        $coursemodule->course = $v1course->courseid;
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

        // Update the assignment title with new status.
        $updatetitle->name = $this->v1assignment->name . ' (Migrated)';
        $DB->update_record('turnitintool', $updatetitle);

        // Update the V1 assignment title in the gradebook.
        @include_once($CFG->dirroot."/lib/gradelib.php");
        $params = array();
        $params['itemname'] = $updatetitle->name;
        grade_update('mod/turnitintool', $v1course->courseid, 'mod', 'turnitintool', $this->v1assignment->id, 0, NULL, $params);

        // Update the V2 assignment title in the gradebook.
        $params['itemname'] = $this->v1assignment->name;
        grade_update('mod/turnitintooltwo', $coursemodule->course, 'mod', 'turnitintooltwo', $turnitintooltwoid, 0, NULL, $params);

        // Commit transaction.
        $transaction->allow_commit();

        return (is_int($turnitintooltwoid)) ? $turnitintooltwoid : false;
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
}