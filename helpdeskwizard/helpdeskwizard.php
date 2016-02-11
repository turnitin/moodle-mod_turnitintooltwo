<?php

class helpdeskwizard {

	public function output_wizard() {
		return '';
	}

	public function output_form( $params = array() ) {
		global $USER;

		// Add standard parameters.
		$params['user'] = 'Instructor';
		$params['name'] = $USER->firstname.' '.$USER->lastname;
		$params['email'] = $USER->email;
		$params['integration'] = 'Moodle';
        $params['auth'] = 'ok';

		// Build URL to open Helpdesk form.
        $supportformurl = TURNITIN_SUPPORT_FORM.'?';
        foreach ($params as $k => $v) {
            $supportformurl .= $k.'='.urlencode($v).'&';
        }
        $supportformurl = substr($supportformurl, 0 , -1);

        // Output HTML iframe containing Turnitin Helpdesk form.
		return html_writer::tag('iframe', '',
	                                array(
	                                    'src' => $supportformurl,
	                                    'width' => '100%',
	                                    'height' => '1048'
	                                    ));
	}
}