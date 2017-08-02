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
     * Test that we have the correct course type.
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
     * Test that the cron processes gradebook migrations.
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
        $v1assignment = $v1migrationtest->make_test_assignment($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Create V2 Assignment.
        $v2assignmenttitle = "Test Assignment";
        $v2assignment = $v1migrationtest->make_test_assignment($course->id, 'turnitintooltwo', $v2assignmenttitle);

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

    /**
     * Test that the cron only processes less than 1000 gradebook migrations when there are over 1000 submissions requiring a gradebook migration.
     */
    public function test_turnitintooltwo_cron_migrate_gradebook_1000() {
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
        $v1assignment = $v1migrationtest->make_test_assignment($course->id, 'turnitintool', $v1assignmenttitle);
        $v1migration = new v1migration($course->id, $v1assignment);

        // Create V2 Assignment.
        $v2assignment1 = $v1migrationtest->make_test_assignment($course->id, 'turnitintooltwo', "Test Assignment 1", 400);
        $v2assignment2 = $v1migrationtest->make_test_assignment($course->id, 'turnitintooltwo', "Test Assignment 2", 400);
        $v2assignment3 = $v1migrationtest->make_test_assignment($course->id, 'turnitintooltwo', "Test Assignment 3", 400);

        // Set migrate gradebook to 1 so the assignments will get migrated when we call the function.
        $DB->set_field('turnitintooltwo_submissions', "migrate_gradebook", 1);

        turnitintooltwo_cron_migrate_gradebook();

        /** 
         * Test that we migrate the gradebook when using the cron workflow.
         * There should be 400 grades that require a migration afterwards since migrating the third assignment would take us over 1000.
         */
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('migrate_gradebook' => 1));
        $this->assertEquals(400, count($submissions));

        // Assignments 1 and 2 should have 0 left.
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment1->id, 'migrate_gradebook' => 1));
        $this->assertEquals(0, count($submissions));
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment2->id, 'migrate_gradebook' => 1));
        $this->assertEquals(0, count($submissions));

        // Asssignment 3 should have 400 left.
        $submissions = $DB->get_records('turnitintooltwo_submissions', array('turnitintooltwoid' => $v2assignment3->id, 'migrate_gradebook' => 1));
        $this->assertEquals(400, count($submissions));
    }
}
