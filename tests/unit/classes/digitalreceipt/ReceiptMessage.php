<?php

/**
 * Unit tests for mod_turnitintooltwo classes/digitalreceipt/receipt_message
 */

defined('MOODLE_INTERNAL') || die();

class mod_turnitintooltwo_receipt_message_testcase extends advanced_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/digitalreceipt/receipt_message.php');
    }

    public function test_build_receipt_message() {
        global $DB;

        $this->resetAfterTest();            // Reset the DB when finished
        $this->preventResetByRollback();    // Messaging doesn't support rollback

        $sink = $this->redirectMessages();  // Collect the emails

        $userOne = $this->getDataGenerator()->create_user();

        $receipt_message = new receipt_message();

        $receipt_message->send_message($userOne, "Test message for email");

        $this->assertEquals(1, $sink->count()); // One email sent

        $messages = $sink->get_messages();

        var_dump($messages[0]);

        $this->assertEquals("A submission entitled <strong>lol</strong> has been made to assignment <strong>lol2</strong> in the class <strong>lol3</strong>.<br /><br />Submission ID: <strong>lol5</strong><br />Submission Date: <strong>lol4</strong><br />",
            $messages[0]
        );

        // $this->assertEquals(
        //     "A submission entitled <strong>lol</strong> has been made to assignment <strong>lol2</strong> in the class <strong>lol3</strong>.<br /><br />Submission ID: <strong>lol5</strong><br />Submission Date: <strong>lol4</strong><br />",
        //     $instructor_message->build_instructor_message($data)
        // );

    }
}
