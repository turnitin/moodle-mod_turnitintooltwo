<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/turnitintooltwo/lib.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/v1migration/v1migration.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/tests/unit/classes/v1migration/v1migration_test.php');

/**
 * Tests for lib file.
 *
 * @package turnitintooltwo
 */
class mod_lib_testcase extends advanced_testcase {
    /**
     * Test create submission function returns the expected bollean given a data array.
     */
    public function test_turnitintooltwo_get_course_type() {
        $this->resetAfterTest();

        $response = turnitintooltwo_get_course_type(1);
        $this->assertEquals($response, "V1");

        $response = turnitintooltwo_get_course_type(0);
        $this->assertEquals($response, "TT");

        $response = turnitintooltwo_get_course_type("foo");
        $this->assertEquals($response, "TT");
    }

    /**
     * Test create submission function returns the expected bollean given a data array.
     */
    public function test_turnitintooltwo_cron_migrate_gradebook() {
        $this->resetAfterTest();

        global $DB;

        $v1migrationtest = new mod_turnitintooltwo_v1migration_testcase();

        if (!$v1migrationtest->v1installed()) {
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
        $v1assignment = $v1migrationtest->make_test_module($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Create V2 Assignment.
        $v2assignmenttitle = "Test Assignment";
        $v2assignment = $v1migrationtest->make_test_module($course->id, 'turnitintooltwo', $v2assignmenttitle);

        // Set migrate gradebook to 1 so it will get migrated when we call the function.
        $DB->set_field('turnitintooltwo_submissions', "migrate_gradebook", 1);

        turnitintooltwo_cron_migrate_gradebook();

        /** 
         * Test that we migrate the gradebook when using the cron workflow.
         * There should be no grades that require a migration.
         */
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment->id, 'migrate_gradebook' => 1));
        $this->assertEquals(0, count($submissions));

        // Test that the title gets updated after the migration.
        $updatedassignment = $DB->get_record('turnitintool', array('id' => $v1assignment->id));
        $this->assertEquals("Test Assignment (Migrated)", $updatedassignment->name);
    }
}
