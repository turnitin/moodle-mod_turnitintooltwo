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

        // Correct user was sent an email
        $this->assertEquals($userOne->id, $messages[0]->useridto);
        $this->assertEquals("This is your Turnitin Digital Receipt", $messages[0]->subject);
        $this->assertEquals("Test message for email", $messages[0]->fullmessage);
        $this->assertEquals("Test message for email", $messages[0]->fullmessagehtml);
        $this->assertEquals(1, $messages[0]->fullmessageformat); // HTML format
    }
}
