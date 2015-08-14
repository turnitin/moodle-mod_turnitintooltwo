<?php

class nonsubmitters_message {

    /**
     * Send non-submitters message to students.
     *
     * @param string $message
     * @return void
     */
    public function send_message($userid, $message) {

        $subject = get_string('nonsubmitters_subject', 'turnitintooltwo');

        $eventdata = new stdClass();
        $eventdata->component         = 'mod_turnitintooltwo'; //your component name
        $eventdata->name              = 'nonsubmitters'; //this is the message name from messages.php
        $eventdata->userfrom          = get_admin();
        $eventdata->userto            = $userid;
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $message;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml   = $message;
        $eventdata->smallmessage      = '';
        $eventdata->notification      = 1; //this is only set to 0 for personal messages between users

        message_send($eventdata);
    }

    /**
     * Build message to send to user
     *
     * @param array $input - used to build message
     * @return string
     */
    public function build_message($input) {
        $message = new stdClass();
        $message->firstname = $input['firstname'];
        $message->lastname = $input['lastname'];
        $message->assignment_name = $input['assignment_name'];
        if ( isset($input['assignment_part']) ) {
            $message->assignment_part = ": " . $input['assignment_part'];
        } else {
            $message->assignment_part = "";
        }
        $message->course_fullname = $input['course_fullname'];
        $message->duedate_part = $input['duedate_part'];

        return get_string('nonsubmitters_message', 'turnitintooltwo', $message);
    }
}
