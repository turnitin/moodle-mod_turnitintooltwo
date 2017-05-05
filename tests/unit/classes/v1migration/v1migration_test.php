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
     * Test that the v1 migration can be set to the relevant value.
     */
    public function test_set_settings_menu_v1_installed() {
        global $DB;
        $this->resetAfterTest();
        
        // Are values saved correctly.
        $saved = v1migration::togglemigrationstatus( 0 );
        $this->assertTrue($saved);
        $saved = v1migration::togglemigrationstatus( 1 );
        $this->assertTrue($saved);
        $saved = v1migration::togglemigrationstatus( 2 );
        $this->assertTrue($saved);

        // If we pass in an invalid value (which should never happen) then it will be converted to 0 to prevent an unnecessary error.
        $saved = v1migration::togglemigrationstatus( 'test' );
        $module = $DB->get_record('config_plugins', array('plugin' => 'turnitintooltwo', 'name' => 'enablemigrationtool'));
        $this->assertEquals(0, $module->value);
    }

    /**
     * Make a test Turnitin assignment module for use in various test cases.
     * @param int $courseid Moodle course ID
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param string $assignmentname The name of the assignment.
     * @param string The number of submissions to make.
     */
    public function make_test_module($courseid, $modname, $assignmentname = "", $submissions = 1) {
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
        $this->make_test_submission($modname, $partid, $assignment->id, $submissions);

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
     * @param int $amount Number of submissions to make.
     */    
    public function make_test_submission($modname, $partid, $assignmentid, $amount = 1) {
        global $DB;

        $modulevar = $modname.'id';

        for ($i = 1; $i <= $amount; $i++) {
            $submission = new stdClass();
            $submission->userid = $i;
            $submission->$modulevar = $assignmentid;
            $submission->submission_part = $partid;
            $submission->submission_title = "Test Submission " . $i;

            $DB->insert_record($modname.'_submissions', $submission);
        }
    }    

    /**
     * Test the migrate modal.
     */
    public function test_migrate_modal() {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $v1migration = new v1migration(1, 1);

        $this->resetAfterTest();

        // Test migration modal.
        $courseid = 1;
        $turnitintoolid = 1;
        $test = $v1migration->migrate_modal($courseid, $turnitintoolid);

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

    /**
     * Test that the gradebook updates perform.
     */
    public function test_migrate_gradebook() {
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

        // Create V1 Assignment.
        $v1assignmenttitle = "Test Assignment (Migrated)";
        $v1assignment = $this->make_test_module($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Create V2 Assignment.
        $v2assignmenttitle = "Test Assignment";
        $v2assignment = $this->make_test_module($course->id, 'turnitintooltwo', $v2assignmenttitle);

        // Set migrate gradebook to 1 so it will get migrated when we call the function.
        $DB->set_field('turnitintooltwo_submissions', "migrate_gradebook", 1);

        // Test that this gradebook update was performed.
        $response = $v1migration->migrate_gradebook($v2assignment->id);
        $this->assertEquals("migrated", $response);

        // There should be no grades that require a migration.
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment->id, 'migrate_gradebook' => 1));
        $this->assertEquals(0, count($submissions));

        // Create V2 Assignment with 201 submissions.
        $v2assignmenttitle = "Test Assignment";
        $v2assignment = $this->make_test_module($course->id, 'turnitintooltwo', $v2assignmenttitle, 201);

        $DB->set_field('turnitintooltwo_submissions', "migrate_gradebook", 1);

        // Test that we return cron when there are more than 200 submissions.
        $response = $v1migration->migrate_gradebook($v2assignment->id);
        $this->assertEquals("cron", $response);

        // All grades should still require migration.
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment->id, 'migrate_gradebook' => 1));
        $this->assertEquals(201, count($submissions));

        // Test that we return migrated when using the cron workflow.
        $response = $v1migration->migrate_gradebook($v2assignment->id, "cron");
        $this->assertEquals("migrated", $response);

        // There should be no grades that require a migration.
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment->id, 'migrate_gradebook' => 1));
        $this->assertEquals(0, count($submissions));
    }

    /**
     * Test that the titles have been updated after migrating.
     */
    public function test_update_titles_post_migration() {
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

        // Create V1 Assignment.
        $v1assignmenttitle = "Test Assignment (Migration in progress...)";
        $v1assignment = $this->make_test_module($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Test that the title gets updated after the migration.
        $response = $v1migration->update_titles_post_migration(1);
        $updatedassignment = $DB->get_record('turnitintool', array('id' => $v1assignment->id));
        $this->assertEquals("Test Assignment (Migrated)", $updatedassignment->name);
    }
}
