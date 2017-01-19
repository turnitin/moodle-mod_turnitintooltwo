<?php

/**
 * Unit tests for mod_turnitintooltwo classes/digitalreceipt/instructor_message
 */

defined('MOODLE_INTERNAL') || die();

class mod_turnitintooltwo_instructor_message_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/digitalreceipt/instructor_message.php');
    }

    public function test_build_instructor_message() {
        $instructor_message = new instructor_message();

        $data = [
            'submission_title' => 'lol',
            'assignment_name' => 'lol2',
            'course_fullname' => 'lol3',
            'submission_date' => 'lol4',
            'submission_id' => 'lol5'
        ];

        $this->assertEquals(
            "A submission entitled <strong>lol</strong> has been made to assignment <strong>lol2</strong> in the class <strong>lol3</strong>.<br /><br />Submission ID: <strong>lol5</strong><br />Submission Date: <strong>lol4</strong><br />",
            $instructor_message->build_instructor_message($data)
        );

    }
}
