<?php

class instructor_message {

    /**
     * Build a modified receipt to send to instructors upon a submission being made.
     * This message must preserve the anonymity of a submission.
     *
     * @param array $input - used to build message
     * @return string
     */
    public function build_instructor_message($input)
    {
        $message = new stdClass();
        $message->submission_title = $input['submission_title'];
        $message->assignment_name = $input['assignment_name'];
        if ( isset($input['assignment_part']) ) {
            $message->assignment_part = ": " . $input['assignment_part'];
        } else {
            $message->assignment_part = "";
        }
        $message->course_fullname = $input['course_fullname'];
        $message->submission_date = $input['submission_date'];
        $message->submission_id = $input['submission_id'];

        return get_string('receipt_instructor_copy', 'turnitintooltwo', $message);
    }

    /**
     * Send instructor message to instructors on course.
     *
     * @param array $instructors
     * @param string $message
     * @return void
     */
    public function send_instructor_message($instructors, $message)
    {
        $subject = get_string('receipt_instructor_copy_subject', 'turnitintooltwo');

        $eventdata = new stdClass();
        $eventdata->component         = 'mod_turnitintooltwo'; //your component name
        $eventdata->name              = 'submission'; //this is the message name from messages.php
        $eventdata->userfrom          = \core_user::get_noreply_user();
        $eventdata->subject           = $subject;
        $eventdata->fullmessage       = $message;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml   = $message;
        $eventdata->smallmessage      = '';
        $eventdata->notification      = 1; //this is only set to 0 for personal messages between users

        foreach ($instructors as $instructor) {

            $eventdata->userto = $instructor->id;
            message_send($eventdata);
        }
        unset($instructor);
    }
}
