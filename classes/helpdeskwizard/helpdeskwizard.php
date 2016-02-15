<?php

class helpdeskwizard {

	public function read_xml_solutions_file() {
		global $CFG;
		$xml = '';

		try {
	        // Open connection.
	        $ch = curl_init();

	        // Set the url, number of POST vars, POST data.
	        // curl_setopt($ch, CURLOPT_URL, "https://www.turnitin.com/static/resources/files/moodle-helpdesk.xml");
	        curl_setopt($ch, CURLOPT_URL, "http://tii_30.live.iparadigms.com/static/resources/files/moodle-helpdesk.xml");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        if (isset($CFG->proxyhost) AND !empty($CFG->proxyhost)) {
	            curl_setopt($ch, CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
	        }
	        if (isset($CFG->proxyuser) AND !empty($CFG->proxyuser)) {
	            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	            curl_setopt($ch, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', $CFG->proxyuser, $CFG->proxypassword));
	        }

	        // Execute post.
	        $result = curl_exec($ch);

	        // Close connection.
	        curl_close($ch);

	        // Read XML into an array.
	        $xml = simplexml_load_string($result);

	    } catch (Exception $e) {
	        turnitintooltwo_comms::handle_exceptions($e, 'gethelpdeskxmlerror', false);
	    }

		return $xml;
	}

	public function output_wizard($id = 0) {
		global $CFG;

		$xml = $this->read_xml_solutions_file();
		$categories = array();
		$output = "";

		foreach ( $xml as $category => $solutions ) {
			$categoryname = substr($category, 0, strrpos($category, '_'));

        	$categories[ucfirst($categoryname)] = ucfirst(str_replace("_", " ", $categoryname));
        	$selectoptions = "";

        	// Read all issues into the array by category
        	$i = 0;
        	foreach ($solutions as $k => $v) {
        		$solution = array();
        		$i++;
        		foreach ($v as $k2 => $v2) {
        			$solution[$k2] = (string)$v2;
        		}

        		$solutionarray[$categoryname][] = $solution;
        		$selectoptions .= html_writer::tag('option', $solution['issue'], array('value' => $i));

        		// Output a hidden div containing each solution.
        		$issue = html_writer::tag('div', $solution['issue'], array('class' => 'issue'));
        		$answer = html_writer::tag('div', $solution['answer'], array('class' => 'answer'));
        		$link = "";
        		if (!empty($solution['link'])) {
        			$linktext = 'Need further information? '.html_writer::link($solution['link'], 'Read More...');
        			$link = html_writer::tag('div', $linktext, array('class' => 'link'));
        		}
        		$output .= html_writer::tag('div', $issue.$answer.$link,
        										array('id' => 'tii_'.$categoryname.'_'.$i, 'class' => 'tii_solutions'));
        	}

        	// Output a hidden div with options for each category.
        	$output .= html_writer::tag('div', $selectoptions, array('id' => 'tii_'.$categoryname.'_options', 'class' => 'tii_wizard_options'));
        }

        // Header
        $output .= html_writer::tag('h2', 'Turnitin Support Wizard');
        $output .= html_writer::tag('p', 'Use the wizard below to help solve your problem');

        // Category.
		$wizardform = html_writer::tag('p', 'I need help with...');
		$wizardform .= html_writer::select($categories, 'category', '', array('' => 'choosedots'),
                                                array('class' => 'tii_helpdesk_category'));

		// Sub Category.
		$wizardform .= html_writer::tag('p', 'Just a little more information');
		$wizardform .= html_writer::select(array(), 'sub_category', '', array('' => 'choosedots'),
                                                array('class' => 'tii_helpdesk_sub_category'));

		$output .= html_writer::tag('div', $wizardform, array('id' => 'tii_wizard_container'));

		// Blank solution template
		$issue = html_writer::tag('h4', '', array('id' => 'solution_issue'));
		$answer = html_writer::tag('div', '', array('id' => 'solution_answer'));
		$link = html_writer::tag('div', '', array('id' => 'solution_link'));
		$output .= html_writer::tag('div', $issue.$answer.$link, array('id' => 'tii_solution_template'));

		// Show link to support form.
		$formlink = html_writer::tag('h2', 'Did you find your answer?');
		$formlink .= html_writer::tag('button', 'No, I Need More Help', array('id' => 'btn_supportform', 'class' => 'btn'));
		$formlink .= html_writer::tag('div', $id, array('id' => 'tii_helpdesk_mod_id'));
		$output .= html_writer::tag('div', $formlink, array('id' => 'btn_tiisupportform_link'));

		return $output;
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