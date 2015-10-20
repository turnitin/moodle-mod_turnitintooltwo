<?php

class nonsubmitters_message {

    /**
     * Send non-submitters message to students.
     *
     * @param string $message
     * @return void
     */
    public function send_message($userid, $subject, $message) {
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
}
