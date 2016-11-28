<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

class helpdeskwizard {

    /**
     * Read the remote xml file containg helpdesk solutions.
     *
     * @return string - xml containing helpdesk solutions.
     */
    public function read_xml_solutions_file() {
        global $CFG;
        $xml = '';

        try {

            // Check if solutions exist in the user's language.
            $tiiurl = "https://www.turnitin.com";
            $solutionsurl = $tiiurl."/static/resources/files/moodle_helpdesk/moodle-helpdesk-".current_language().".xml";
            $ch = curl_init($solutionsurl);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            $result = curl_exec($ch);

            // If there is no solutions file then use English.
            if ($result !== false) {
                $statuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                if ($statuscode != 200) {
                    $solutionsurl = $tiiurl."/static/resources/files/moodle_helpdesk/moodle-helpdesk-en.xml";
                }
            }

            // Get correct language file.
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $solutionsurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
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
            turnitintooltwo_comms::handle_exceptions($e, 'gethelpdeskxmlerror', true);
        }

        return $xml;
    }

    /**
     * Output HTML helpdesk wizard.
     *
     * @param int - $id module id, 0 by default to indicate that it's not been accessed from a module.
     * @return mixed - html containing helpdesk solutions.
     */
    public function output_wizard($id = 0) {
        $xml = $this->read_xml_solutions_file();
        $categories = array();
        $output = "";

        foreach ($xml as $category => $solutions) {
            $categoryname = substr($category, 0, strrpos($category, '_'));

            $categories[ucfirst($categoryname)] = ucfirst(str_replace("_", " ", $categoryname));
            $selectoptions = "";

            // Read all issues into the array by category.
            $i = 0;
            foreach ($solutions as $solution) {
                $solution = array();
                $i++;
                foreach ($solution as $k => $v) {
                    $solution[$k] = (string)$v;
                }

                $selectoptions .= html_writer::tag('option', $solution['issue'], array('value' => $i));

                // Output a hidden div containing each solution.
                $issue = html_writer::tag('div', $solution['issue'], array('class' => 'issue'));
                $answer = html_writer::tag('div', $solution['answer'], array('class' => 'answer'));
                $link = "";
                if (!empty($solution['link'])) {
                    $linktext = 'Need further information?';
                    $linktag = html_writer::link($solution['link'], 'Read More...', array('target' => '_blank'));
                    $link = html_writer::tag('div', $linktext.' '.$linktag, array('class' => 'link'));
                } else {
                    $link = html_writer::tag('div', "", array('class' => 'link'));
                }
                $output .= html_writer::tag('div', $issue.$answer.$link,
                                                array('id' => 'tii_'.$categoryname.'_'.$i, 'class' => 'tii_solutions'));
            }

            // Output a hidden div with options for each category.
            $output .= html_writer::tag('div', $selectoptions,
                array('id' => 'tii_'.$categoryname.'_options', 'class' => 'tii_wizard_options'));
        }

        // Header.
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

        // Blank solution template.
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

    /**
     * Output HTML helpdesk wizard.
     *
     * @param array - $params from helpdesk wizard to pass to Turnitin.
     * @return html - iframe linking to Turnitin support form.
     */
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