<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/turnitintooltwo/lib.php');

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
}
