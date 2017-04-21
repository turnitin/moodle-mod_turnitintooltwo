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

global $CFG;
require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/v1migration/v1migration.php');

/**
 * Tests for classes/v1migration/v1migration
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_v1migration_testcase extends advanced_testcase {

    /**
     * Test that users get migrated from the v1 to the v2 user table.
     */
    public function test_migrate_users() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $v1migration = new v1migration(1, 1);

        $this->resetAfterTest();

        // Generate a new users to migrate.
        $user1 = $this->getDataGenerator()->create_user();

        // Create user in v1 tables.
        $turnitintooluser = new stdClass();
        $turnitintooluser->userid = $user1->id;
        $turnitintooluser->turnitin_uid = 1001;
        $turnitintooluser->turnitin_utp = 1;
        $DB->insert_record('turnitintool_users', $turnitintooluser);

        // Migrate users to v2 tables.
        $v1migration->migrate_users();

        $turnitintooltwousers = $DB->get_records('turnitintooltwo_users', array('userid' => $user1->id));

        $this->assertEquals(1, count($turnitintooltwousers));
    }

    /**
     * Check whether v1 is installed.
     */
    public function v1installed() {
        global $DB;

        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintool'));
        return boolval($module);
    }

    /**
     * Make a test Turnitin assignment module for use in various test cases.
     * @param int $courseid Moodle course ID
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     */
    public function make_test_module($courseid, $modname, $assignmentname = "") {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $this->resetAfterTest();

        $assignment = new stdClass();
        $assignment->name = ($assignmentname == "") ? "Test Turnitin Assignment" : $assignmentname;
        $assignment->course = $courseid;

        // Initialise fields.
        $nullcheckfields = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat',
                            'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
                            'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook',
                            'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch', 'excludetype', 'perpage');

        // Set all fields to null.
        foreach ($nullcheckfields as $field) {
            $assignment->$field = null;
        }

        // Set default values and save module.
        $v1migration = new v1migration($courseid, $assignment);
        $v1migration->set_default_values();
        
        $assignment->id = $DB->insert_record($modname, $assignment);

        // Create Assignment Part.
        $partid = $this->make_test_part($modname, $assignment->id);

        // Create Assignment Submission.
        $this->make_test_submission($modname, $partid, $assignment->id);

        // Set up a course module.
        $module = $DB->get_record("modules", array("name" => $modname));
        $coursemodule = new stdClass();
        $coursemodule->course = $courseid;
        $coursemodule->module = $module->id;
        $coursemodule->added = time();
        $coursemodule->instance = $assignment->id;
        $coursemodule->section = 0;

        // Add Course module if a v1 module.
        if ($modname == 'turnitintool') {
            add_course_module($coursemodule);    
        }
        
        return $assignment;
    }

    /**
     * Create a test part on the specified assignment.
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param int $assignmentid Assignment Module ID
     */    
    public function make_test_part($modname, $assignmentid) {
        global $DB;

        $modulevar = $modname.'id';

        $part = new stdClass();
        $part->$modulevar = $assignmentid;
        $part->partname = 'Part 1';
        $part->tiiassignid = 0;
        $part->dtstart = 0;
        $part->dtdue = 0;
        $part->dtpost = 0;
        $part->maxmarks = 0;
        $part->deleted = 0;
        
        $partid = $DB->insert_record($modname.'_parts', $part);
        return $partid;
    }

    /**
     * Create a test submission on the specified assignment part.
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param int $partid Part ID
     * @param int $assignmentid Assignment Module ID
     */    
    public function make_test_submission($modname, $partid, $assignmentid) {
        global $DB;

        $modulevar = $modname.'id';

        $submission = new stdClass();
        $submission->userid = 1;
        $submission->$modulevar = $assignmentid;
        $submission->submission_part = $partid;
        $submission->submission_title = "Test Submission";

        $DB->insert_record($modname.'_submissions', $submission);
    }    

    /**
     * Test the modal that appears when asked to migrate.
     */
    public function test_asktomigrate() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $v1migration = new v1migration(1, 1);

        $this->resetAfterTest();

        // Test migration modal.
        $courseid = 1;
        $turnitintoolid = 1;
        $test = $v1migration->asktomigrate($courseid, $turnitintoolid);

        $this->assertContains('data-courseid="'.$courseid.'"', $test);
        $this->assertContains('data-turnitintoolid="'.$turnitintoolid.'"', $test);
    }

    /**
     * Test that all values which can't be null get initialised.
     */
    public function test_set_default_values() {

        if (!$this->v1installed()) {
            return false;
        }

        // Fields to set to null.
        $nullcheckfields = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat',
                            'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
                            'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook',
                            'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch', 'excludetype', 'perpage');

        // Create Migration Assignment object.
        $v1migration = new v1migration(1, new stdClass());

        // Set all fields to check to null.
        foreach ($nullcheckfields as $field) {
            $v1migration->v1assignment->$field = null;
        }

        $v1migration->set_default_values();
      
        // Assert that all fields are no longer null.
        foreach ($nullcheckfields as $field) {
            $this->assertNotNull($v1migration->v1assignment->$field);
        }        
    }

    /**
     * Test that v1 assignment is hidden and renamed.
     */
    public function test_hide_v1_assignment() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $this->resetAfterTest();

        // Generate a new course.
        $course = $this->getDataGenerator()->create_course();

        // Create Assignment.
        $v1assignment = $this->make_test_module($course->id, 'turnitintool');
        $v1migration = new v1migration($course->id, $v1assignment);

        $v1migration->hide_v1_assignment();

        // Test that assignment has been renamed.
        $updatedassignment = $DB->get_record('turnitintool', array('id' => $v1assignment->id));
        $this->assertContains("(Migration in progress...)", $updatedassignment->name);
        
        // Test that assignment has been hidden.
        $cm = get_coursemodule_from_instance('turnitintool', $v1assignment->id);
        $this->assertEquals(0, $cm->visible);
        $this->assertEquals(0, $cm->visibleold);
    }

    public function test_setup_v2_module() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $this->resetAfterTest();

        // Generate a new course.
        $course = $this->getDataGenerator()->create_course();

        // Create Assignment.
        $v2assignment = $this->make_test_module($course->id, 'turnitintooltwo');
        $v1migration = new v1migration($course->id, $v2assignment);

        $v1migration->setup_v2_module($course->id, $v2assignment->id);

        // Test that assignment has been assigned a course section.
        $cm = get_coursemodule_from_instance('turnitintooltwo', $v2assignment->id);
        $this->assertNotEquals(0, $cm->section);
    }

    /**
     * Test that the assignment gets migrated from the v1 to the v2 tables.
     */
    public function test_migrate_assignment() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $this->resetAfterTest();

        // Generate a new course.
        $course = $this->getDataGenerator()->create_course();

        // Link course to Turnitin.
        $courselink = new stdClass();
        $courselink->courseid = $course->id;
        $courselink->ownerid = 0;
        $courselink->turnitin_ctl = "Test Course";
        $courselink->turnitin_cid = 0;
        $DB->insert_record('turnitintool_courses', $courselink);

        // Create Assignment.
        $v1assignmenttitle = "Test ".uniqid();
        $v1assignment = $this->make_test_module($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Verify there are no v2 assignments, parts or submissions.
        $v2assignments = $DB->get_records('turnitintooltwo');
        $v2parts = $DB->get_records('turnitintooltwo_parts');
        $v2submissions = $DB->get_records('turnitintooltwo_submissions');
        $this->assertEquals(0, count($v2assignments));
        $this->assertEquals(0, count($v2parts));
        $this->assertEquals(0, count($v2submissions));

        $v2assignmentid = $v1migration->migrate();

        // Verify assignment has migrated.
        $v2assignment = $DB->get_record('turnitintooltwo', array('id' => $v2assignmentid));
        $this->assertEquals($v1assignmenttitle, $v2assignment->name);

        // Verify part has migrated.
        $v2parts = $DB->get_records('turnitintooltwo_parts', array('turnitintooltwoid' => $v2assignmentid));
        $this->assertEquals(1, count($v2parts));

        // Verify submission has migrated.
        $v2parts = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignmentid));
        $this->assertEquals(1, count($v2parts));
    }

    /**
     * Test the modal that appears when asked to migrate.
     */
    public function test_migrate_course() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $assignment = new stdClass();
        $v1migration = new v1migration(1, $assignment);

        $this->resetAfterTest();

        // Values for our TII course.
        $v1tiicourse = 9;
        $v2tiicourse = 12;

        // Create a V1 course and get it.
        $course = new stdClass();
        $course->courseid = 1;
        $course->ownerid = 1;
        $course->turnitin_ctl = "Test Course";
        $course->turnitin_cid = $v1tiicourse;
        $course->course_type = "TT";

        // Insert the course to the turnitintooltwo courses table.
        $DB->insert_record('turnitintool_courses', $course);
        $v1course = $DB->get_record('turnitintool_courses', array('courseid' => 1));

        /* Test 1. V1 migration with no existing V2 courses.
           Should create a new course entry in turnitintooltwo_courses table with the same turnitin_cid as above, course type TT.*/
        $response = $v1migration->migrate_course($v1course);
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "TT"));
        $this->assertEquals(1, count($v2courses));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals($course->course_type, $response->course_type);

        // If we attempt to migrate this course again (IE migrating a second assignment on this course), there should still only be one entry.
        $response = $v1migration->migrate_course($v1course);
        $v2course = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "TT"));
        $this->assertEquals(1, count($v2course));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals($course->course_type, $response->course_type);

        // Clear our table.
        $DB->delete_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse));

        /* Test 2. V1 migration with an existing V2 course.
           Should create a new course entry in turnitintooltwo_courses table with the same turnitin_cid as above, course type V1. 
           Legacy field should be set to 1 on these tests. */

        // Create our initial V2 course.
        $v1iicourse = 9;

        $course = new stdClass();
        $course->courseid = 1;
        $course->ownerid = 1;
        $course->turnitin_ctl = "Test Course";
        $course->turnitin_cid = $v2tiicourse;
        $course->course_type = "TT";

        // Insert the course to the turnitintooltwo courses table.
        $DB->insert_record('turnitintooltwo_courses', $course);

        $response = $v1migration->migrate_course($v1course);
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "V1"));
        $this->assertEquals(1, count($v2courses));
        $this->assertEquals(1, count($v1migration->v1assignment->legacy));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals("V1", $response->course_type);

        // We expect 0 results here since we inserted a course type of TT.
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "TT"));
        $this->assertEquals(0, count($v2courses));
        $this->assertEquals(1, count($v1migration->v1assignment->legacy));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals("V1", $response->course_type);

        // If we attempt to migrate this course again (IE migrating a second assignment on this course), there should still only be one entry.
        $response = $v1migration->migrate_course($v1course);
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "V1"));
        $this->assertEquals(1, count($v2courses));
        $this->assertEquals(1, count($v1migration->v1assignment->legacy));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals("V1", $response->course_type);

        // And still 0 results for this one.
        $v2courses = $DB->get_records('turnitintooltwo_courses', array('turnitin_cid' => $v1tiicourse, 'course_type' => "TT"));
        $this->assertEquals(0, count($v2courses));
        $this->assertEquals(1, count($v1migration->v1assignment->legacy));
        $this->assertEquals($course->courseid, $response->courseid);
        $this->assertEquals("V1", $response->course_type);
    }
}
