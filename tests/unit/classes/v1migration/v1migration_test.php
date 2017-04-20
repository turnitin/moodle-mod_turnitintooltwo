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
    public function make_test_module($courseid, $modname) {
        global $DB;

        if (!$this->v1installed()) {
            return false;
        }

        $this->resetAfterTest();

        $assignment = new stdClass();
        $assignment->name = "Test Turnitin Assignment";
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
}
