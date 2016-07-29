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

/**
 * @package   turnitintooltwo
 * @copyright 2010 iParadigms LLC
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once(__DIR__.'/lib.php');

class mod_turnitintooltwo_mod_form extends moodleform_mod {

    private $updating;
    private $numsubs;
    private $turnitintooltwo;

    public function definition() {
        global $CFG, $DB, $USER, $COURSE;
        $config = turnitintooltwo_admin_config();

        // Module string is useful for product support.
        $modulestring = '<!-- Turnitin Moodle Direct Version: '.turnitintooltwo_get_version().' - (';

        // Get Moodle Course Object.
        $course = turnitintooltwo_assignment::get_course_data($COURSE->id);

        // Create or edit the class in Turnitin.
        if ($course->turnitin_cid == 0) {
            $tiicoursedata = turnitintooltwo_assignment::create_tii_course($course, $USER->id);
            $course->turnitin_cid = $tiicoursedata->turnitin_cid;
            $course->turnitin_ctl = $tiicoursedata->turnitin_ctl;
        } else {
            turnitintooltwo_assignment::edit_tii_course($course);
            $course->turnitin_ctl = $course->fullname . " (Moodle TT)";
        }

        // Join this user to the class as an instructor and get their rubrics.
        $instructor = new turnitintooltwo_user($USER->id, 'Instructor');
        $instructor->join_user_to_class($course->turnitin_cid);
        $instructor->set_user_values_from_tii();
        $instructorrubrics = $instructor->get_instructor_rubrics();

        // Get rubrics that are shared on the account.
        $turnitinclass = new turnitintooltwo_class($course->id);
        $turnitinclass->read_class_from_tii();

        // Merge the arrays, prioitising instructor owned arrays.
        $rubrics = $instructorrubrics + $turnitinclass->sharedrubrics;

        $this->numsubs = 0;
        if (isset($this->_cm->id)) {

            $turnitintooltwoassignment = new turnitintooltwo_assignment($this->_cm->instance);
            $turnitintooltwoassignment->update_assignment_from_tii();

            $this->turnitintooltwo = $DB->get_record("turnitintooltwo", array("id" => $this->_cm->instance));
            $parts = $DB->get_records("turnitintooltwo_parts",
                                        array("turnitintooltwoid" => $this->_cm->instance, "deleted" => 0), 'id');

            $i = 0;
            foreach ($parts as $part) {
                $i++;

                $attributes = array("id", "partname", "dtstart", "dtdue", "dtpost", "maxmarks");
                foreach ($attributes as $att) {
                    $attribute = $att.$i;
                    $this->current->$attribute = $part->$att;
                }
                $attribute = "numsubs".$i;
                $this->current->$attribute = $DB->count_records('turnitintooltwo_submissions',
                                                    array('turnitintooltwoid' => $this->turnitintooltwo->id,
                                                            'submission_part' => $part->id));
                $this->numsubs += $this->current->$attribute;

                $modulestring .= ($modulestring != "(") ? " | " : "";
                $modulestring .= $part->partname.': '.$part->tiiassignid;
            }

            $this->updating = true;

        } else {
            $this->updating = false;

            $instructordefaults = $instructor->get_instructor_defaults();

            if (empty($instructordefaults)) {
                $instructordefaults = array();
            }

            foreach ($instructordefaults as $k => $v) {
                $this->current->$k = $v;
            }
        }

        // Overwrite instructor default repository if admin is forcing repository setting
        switch ($config->repositoryoption) {
            case 2; // Force Standard Repository
                $this->current->submitpapersto = 1;
                break;
            case 3; // Force No Repository
                $this->current->submitpapersto = 0;
                break;
        }

        $modulestring .= ') -->';

        $this->show_form($rubrics, $modulestring, $course->turnitin_cid);
    }

    public function show_form($instructorrubrics, $modulestring = '', $tiicourseid) {
        global $CFG, $OUTPUT, $COURSE, $PAGE;
        $PAGE->requires->string_for_js('changerubricwarning', 'turnitintooltwo');
        $PAGE->requires->string_for_js('closebutton', 'turnitintooltwo');

        $config = turnitintooltwo_admin_config();

        $mform =& $this->_form;

        $script = '';
        // Add in custom Javascript and CSS.
        if ($CFG->branch <= 25) {
            $script .= html_writer::tag('script', '', array("type" => "text/javascript",
                                                "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/jquery-1.8.2.min.js"));
            $script .= html_writer::tag('script', '', array("id" => "plugin_turnitin_script", "type" => "text/javascript",
                                            "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/turnitintooltwo.min.js"));
            $script .= html_writer::tag('script', '', array("type" => "text/javascript",
                                            "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/jquery-ui-1.10.4.custom.min.js"));
            $script .= html_writer::tag('script', '', array("type" => "text/javascript",
                                            "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/jquery.colorbox.js"));
            $script .= html_writer::tag('script', '', array("type" => "text/javascript",
                                            "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/jquery.colorbox.js"));
            $script .= html_writer::tag('script', '', array("type" => "text/javascript",
                                            "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/moment.js"));
        } else {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('turnitintooltwo-turnitintooltwo', 'mod_turnitintooltwo');
            $PAGE->requires->jquery_plugin('turnitintooltwo-colorbox', 'mod_turnitintooltwo');
            $PAGE->requires->jquery_plugin('turnitintooltwo-moment', 'mod_turnitintooltwo');
        }

        $PAGE->requires->string_for_js('anonalert', 'turnitintooltwo');

        $script .= html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                                            "href" => $CFG->wwwroot."/mod/turnitintooltwo/styles.css"));
        $script .= html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                                            "href" => $CFG->wwwroot."/mod/turnitintooltwo/css/colorbox.css"));
        $script .= html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                                            "href" => $CFG->wwwroot."/mod/turnitintooltwo/css/tii-icon-webfont.css"));
        $script .= html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                                            "href" => $CFG->wwwroot."/mod/turnitintooltwo/css/font-awesome.min.css"));

        $mform->addElement('html', $script);

        $config_warning = '';
        if (empty($config->accountid) || empty($config->secretkey) || empty($config->apiurl)) {
            $config_warning = html_writer::tag('div', get_string('configureerror', 'turnitintooltwo'),
                                                array('class' => 'library_not_present_warning'));
        }

        if ($config_warning != '') {
            $mform->addElement('html', $config_warning);
        }

        $noscript = html_writer::tag('noscript', get_string('noscript', 'turnitintooltwo'), array("class" => "warning"));
        $mform->addElement('html', $noscript);

        if (isset($_SESSION["notice"])) {
            $notice = $_SESSION["notice"];
            if (empty($_SESSION["notice"]["type"])) {
                $notice["type"] = "general";
            }
            unset($_SESSION["notice"]);
        } else {
            $notice = null;
        }

        if (!is_null($notice)) {
            $mform->addElement('html', $OUTPUT->box($notice["message"], 'generalbox', $notice["type"]));
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('turnitintooltwoname', 'turnitintooltwo'), array('size' => '64'));
        $mform->setType('name', PARAM_RAW);
        $mform->addRule('name', null, 'required', null, 'client');

        $input = new stdClass();
        $input->length = 40;
        $input->field = get_string('turnitintooltwoname', 'turnitintooltwo');
        $mform->addRule('name', get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 40, 'client');
        $mform->addRule('name', get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 40, 'server');

        if ($CFG->branch >= 29) {
            $this->standard_intro_elements(get_string('turnitintooltwointro', 'turnitintooltwo'));
        } else {
            $this->add_intro_editor(true, get_string('turnitintooltwointro', 'turnitintooltwo'));
        }

        $typeoptions = turnitintooltwo_filetype_array(true);

        $mform->addElement('select', 'type', get_string('type', 'turnitintooltwo'), $typeoptions);
        $mform->addHelpButton('type', 'types', 'turnitintooltwo');
        $mform->addRule('type', get_string('required'), 'required', null, 'client');
        $mform->setDefault('type', $config->default_type);

        $options = array();
        if ($this->updating) {
            $j = $this->current->numparts;
        } else {
            $j = 1;
        }
        for ($i = $j; $i <= 5; $i++) {
            $options[$i] = $i;
        }

        $mform->addElement('select', 'numparts', get_string('numberofparts', 'turnitintooltwo'), $options);
        $mform->addHelpButton('numparts', 'numberofparts', 'turnitintooltwo');
        $mform->setDefault('numparts', $config->default_numparts);

        $mform->addElement('hidden', 'portfolio', 0);
        $mform->setType('portfolio', PARAM_INT);

        $maxbytessite = ($CFG->maxbytes == 0 || $CFG->maxbytes > TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE) ?
                            TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE : $CFG->maxbytes;
        $maxbytescourse = ($COURSE->maxbytes == 0 || $COURSE->maxbytes > TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE) ?
                            TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE : $COURSE->maxbytes;

        $options = get_max_upload_sizes($maxbytessite, $maxbytescourse, TURNITINTOOLTWO_MAX_FILE_UPLOAD_SIZE);

        $mform->addElement('select', 'maxfilesize', get_string('maxfilesize', 'turnitintooltwo'), $options);
        $mform->addHelpButton('maxfilesize', 'maxfilesize', 'turnitintooltwo');

        unset($options);
        for ($i = 0; $i <= 100; $i++) {
            $options[$i] = $i;
        }

        $ynoptions = array( 0 => get_string('no'), 1 => get_string('yes'));

        if ($this->updating AND $config->useanon AND isset($this->turnitintooltwo->anon) AND $this->turnitintooltwo->submitted == 1) {
            $staticout = (isset($this->turnitintooltwo->anon) AND $this->turnitintooltwo->anon) ?
                            get_string('yes') : get_string('no');
            $mform->addElement('static', 'static', get_string('turnitinanon', 'turnitintooltwo'), $staticout);
            $mform->addElement('hidden', 'anon', $this->turnitintooltwo->anon);
            $mform->addHelpButton('anon', 'turnitinanon', 'turnitintooltwo');
        } else if ($config->useanon) {
            $mform->addElement('select', 'anon', get_string('turnitinanon', 'turnitintooltwo'), $ynoptions);
            $mform->addHelpButton('anon', 'turnitinanon', 'turnitintooltwo');
            $anondefault = isset($config->default_anon) ? $config->default_anon : 0;
            $mform->setDefault('anon', $anondefault);
        } else {
            $mform->addElement('hidden', 'anon', 0);
        }
        $mform->setType('anon', PARAM_INT);

        $mform->addElement('select', 'allownonor', get_string('allownonor', 'turnitintooltwo'), $ynoptions);
        $mform->addHelpButton('allownonor', 'allownonor', 'turnitintooltwo');
        $mform->setDefault('allownonor', $config->default_allownonor);

        $mform->addElement('select', 'studentreports', get_string('studentreports', 'turnitintooltwo'), $ynoptions);
        $mform->addHelpButton('studentreports', 'studentreports', 'turnitintooltwo');
        $mform->setDefault('studentreports', $config->default_studentreports);

        if (!empty($config->usegrademark)) {
            $gradedisplayoptions = array(1 => get_string('displaygradesaspercent', 'turnitintooltwo'),
                                         2 => get_string('displaygradesasfraction', 'turnitintooltwo'));
            $mform->addElement('select', 'gradedisplay', get_string('displaygradesas', 'turnitintooltwo'), $gradedisplayoptions);
            $mform->addHelpButton('gradedisplay', 'displaygradesas', 'turnitintooltwo');
            $mform->setDefault('gradedisplay', $config->default_gradedisplay);
        }

        $refreshoptions = array(1 => get_string('yesgrades', 'turnitintooltwo'), 0 => get_string('nogrades', 'turnitintooltwo'));

        $mform->addElement('select', 'autoupdates', get_string('autorefreshgrades', 'turnitintooltwo'), $refreshoptions);
        $mform->addHelpButton('autoupdates', 'autorefreshgrades', 'turnitintooltwo');
        $mform->setDefault('autoupdates', 1);

        $mform->addElement('checkbox', 'set_instructor_defaults', '', " ".get_string('setinstructordefaults', 'turnitintooltwo'));
        $mform->setDefault('set_instructor_defaults', false);
        $mform->addHelpButton('set_instructor_defaults', 'setinstructordefaults', 'turnitintooltwo');

        $dateoptions = array('startyear' => date( 'Y', strtotime( '-6 years' )), 'stopyear' => date( 'Y', strtotime( '+6 years' )),
                    'timezone' => 99, 'applydst' => true, 'step' => 1, 'optional' => false);

        if (!empty($config->usegrademark)) {
            $this->standard_grading_coursemodule_elements();
        }

        if (isset($this->_cm->id)) {
            $turnitintooltwoassignment = new turnitintooltwo_assignment($this->_cm->instance);
            $parts = $turnitintooltwoassignment->get_parts();

            $partsArray = array();
            foreach ($parts as $key => $value) {
                $partsArray[] = $value;
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $mform->addElement('header', 'partdates'.$i, get_string('partname', 'turnitintooltwo')." ".$i);

            if (isset($this->_cm->id) && isset($partsArray[$i-1])) {
                    $partdetails = $turnitintooltwoassignment->get_part_details($partsArray[$i-1]->id);
                    $mform->addElement('html', '<div class="assignment-part-' . $i . '" data-anon="' . $turnitintooltwoassignment->turnitintooltwo->anon . '" data-unanon="' . $partdetails->unanon . '" data-submitted="' . $partdetails->submitted . '" data-part-id="' . $i . '">');
            }

            // Delete part link.
            if ($this->updating && $this->current->numparts > 1 && $i <= $this->current->numparts) {
                $attributes = array('class' => 'delete_link');
                $numsubsattribute = "numsubs".$i;
                if ($this->current->$numsubsattribute > 0) {
                    $fnd = array("\n", "\r");
                    $rep = array('\n', '\r');
                    $string = str_replace($fnd, $rep, get_string('partdeletewarning', 'turnitintooltwo'));
                    $attributes["onclick"] = "return confirm('".$string."');";
                }

                $partidattribute = "id".$i;
                $url = new moodle_url($CFG->wwwroot."/mod/turnitintooltwo/view.php",
                                        array('id' => $this->_cm->id, 'action' => 'delpart',
                                            'part' => $this->current->$partidattribute, 'sesskey' => sesskey()));
                $deletelink = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-trash fa-lg icon_smallmargin')).
                        get_string('deletepart', 'turnitintooltwo'), $attributes);
                $mform->addElement('html', $deletelink);
            }

            $mform->addElement('text', 'partname'.$i, get_string('name'));
            $mform->setType('partname'.$i, PARAM_RAW);
            $mform->setDefault('partname'.$i, get_string('turnitinpart', 'turnitintooltwo', $i));
            $mform->addRule('partname'.$i, null, 'required', null, 'client');
            $input = new stdClass();
            $input->length = 40;
            $input->field =  get_string('partname', 'turnitintooltwo')." ".get_string('name');
            $mform->addRule('partname'.$i, get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 40, 'client');
            $mform->addRule('partname'.$i, get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 40, 'server');

            $mform->addElement('date_time_selector', 'dtstart'.$i, get_string('dtstart', 'turnitintooltwo'), $dateoptions);
            $mform->setDefault('dtstart'.$i, time());

            $mform->addElement('date_time_selector', 'dtdue'.$i, get_string('dtdue', 'turnitintooltwo'), $dateoptions);
            $mform->setDefault('dtdue'.$i, strtotime('+7 days'));

            $mform->addElement('date_time_selector', 'dtpost'.$i, get_string('dtpost', 'turnitintooltwo'), $dateoptions);
            $mform->setDefault('dtpost'.$i, strtotime('+7 days'));

            if (!empty($config->usegrademark)) {
                $mform->addElement('text', 'maxmarks'.$i, get_string('maxmarks', 'turnitintooltwo'));
                $mform->setType('maxmarks'.$i, PARAM_INT);
                $mform->setDefault('maxmarks'.$i, '100');
                $mform->addRule('maxmarks'.$i, null, 'numeric', null, 'client');
            }
        }

        $mform->addElement('header', 'advanced', get_string('turnitinoroptions', 'turnitintooltwo'));

        $mform->addElement('select', 'allowlate', get_string('allowlate', 'turnitintooltwo'), $ynoptions);
        $mform->setDefault('allowlate', $config->default_allowlate);

        $genoptions = array(0 => get_string('genimmediately1', 'turnitintooltwo'),
                            1 => get_string('genimmediately2', 'turnitintooltwo'),
                                2 => get_string('genduedate', 'turnitintooltwo'));
        $mform->addElement('select', 'reportgenspeed', get_string('reportgenspeed', 'turnitintooltwo'), $genoptions);
        $mform->addHelpButton('reportgenspeed', 'reportgenspeed', 'turnitintooltwo');
        $mform->setDefault('reportgenspeed', $config->default_reportgenspeed);

        $mform->addElement('html', html_writer::tag('div', get_string('genspeednote', 'turnitintooltwo'), array('class' => 'tii_genspeednote')));

        $suboptions = array(0 => get_string('norepository', 'turnitintooltwo'),
                            1 => get_string('standardrepository', 'turnitintooltwo'));
        switch ($config->repositoryoption) {
            case 0; // Standard options
                $mform->addElement('select', 'submitpapersto', get_string('submitpapersto', 'turnitintooltwo'), $suboptions);
                $mform->addHelpButton('submitpapersto', 'submitpapersto', 'turnitintooltwo');
                $mform->setDefault('submitpapersto', $config->default_submitpapersto);
                break;
            case 1; // Standard options + Allow Instituional Repository
                $suboptions[2] = get_string('institutionalrepository', 'turnitintooltwo');

                $mform->addElement('select', 'submitpapersto', get_string('submitpapersto', 'turnitintooltwo'), $suboptions);
                $mform->addHelpButton('submitpapersto', 'submitpapersto', 'turnitintooltwo');
                $mform->setDefault('submitpapersto', $config->default_submitpapersto);

                break;
            case 2; // Force Standard Repository
                $mform->addElement('hidden', 'submitpapersto', 1);
                $mform->setType('submitpapersto', PARAM_RAW);
                break;
            case 3; // Force No Repository
                $mform->addElement('hidden', 'submitpapersto', 0);
                $mform->setType('submitpapersto', PARAM_RAW);
                break;
        }

        $mform->addElement('html', html_writer::tag('div', get_string('checkagainstnote', 'turnitintooltwo'), array('class' => 'tii_checkagainstnote')));

        $mform->addElement('select', 'spapercheck', get_string('spapercheck', 'turnitintooltwo'), $ynoptions);
        $mform->addHelpButton('spapercheck', 'spapercheck', 'turnitintooltwo');
        $mform->setDefault('spapercheck', $config->default_spapercheck);

        $mform->addElement('select', 'internetcheck', get_string('internetcheck', 'turnitintooltwo'), $ynoptions);
        $mform->addHelpButton('internetcheck', 'internetcheck', 'turnitintooltwo');
        $mform->setDefault('internetcheck', $config->default_internetcheck);

        $mform->addElement('select', 'journalcheck', get_string('journalcheck', 'turnitintooltwo'), $ynoptions);
        $mform->addHelpButton('journalcheck', 'journalcheck', 'turnitintooltwo');
        $mform->setDefault('journalcheck', $config->default_journalcheck);

        if ($config->repositoryoption == "1") {
            $mform->addElement('select', 'institution_check', get_string('institutionalcheck', 'turnitintooltwo'), $ynoptions);
            $mform->setDefault('institution_check', $config->default_institutioncheck);
        }

        if ($this->numsubs > 0) {

            $staticout = (isset($this->turnitintooltwo->excludebiblio) AND $this->turnitintooltwo->excludebiblio)
                            ? get_string('yes') : get_string('no');
            $mform->addElement('static', 'static', get_string('excludebiblio', 'turnitintooltwo'), $staticout);
            $mform->addElement('hidden', 'excludebiblio', $this->turnitintooltwo->excludebiblio);

            $staticout = (isset($this->turnitintooltwo->excludequoted) AND $this->turnitintooltwo->excludequoted)
                            ? get_string('yes') : get_string('no');
            $mform->addElement('static', 'static', get_string('excludequoted', 'turnitintooltwo'), $staticout);
            $mform->addElement('hidden', 'excludequoted', $this->turnitintooltwo->excludequoted);

            $staticout = (isset($this->turnitintooltwo->excludetype) AND $this->turnitintooltwo->excludetype == 1)
                            ? get_string('excludewords', 'turnitintooltwo') : get_string('excludepercent', 'turnitintooltwo');
            $staticval = (isset($this->turnitintooltwo->excludevalue) AND empty($this->turnitintooltwo->excludevalue))
                            ? get_string('nolimit', 'turnitintooltwo') : $this->turnitintooltwo->excludevalue.' '.$staticout;
            $mform->addElement('static', 'static', get_string('excludevalue', 'turnitintooltwo'), $staticval);
            $mform->addElement('hidden', 'excludevalue', $this->turnitintooltwo->excludevalue);
            $mform->addElement('hidden', 'excludetype', $this->turnitintooltwo->excludetype);

        } else {
            $mform->addElement('select', 'excludebiblio', get_string('excludebiblio', 'turnitintooltwo'), $ynoptions);
            $mform->addHelpButton('excludebiblio', 'excludebiblio', 'turnitintooltwo');
            $mform->setDefault('excludebiblio', $config->default_excludebiblio);

            $mform->addElement('select', 'excludequoted', get_string('excludequoted', 'turnitintooltwo'), $ynoptions);
            $mform->addHelpButton('excludequoted', 'excludequoted', 'turnitintooltwo');
            $mform->setDefault('excludequoted', $config->default_excludequoted);

            $mform->addElement('text', 'excludevalue', get_string('excludevalue', 'turnitintooltwo'), array('size' => '12'));
            $mform->addHelpButton('excludevalue', 'excludevalue', 'turnitintooltwo');
            $input = new stdClass();
            $input->length = 9;
            $input->field = get_string('excludevalue', 'turnitintooltwo');
            $mform->addRule('excludevalue', get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 9, 'client');
            $mform->addRule('excludevalue', get_string('maxlength', 'turnitintooltwo', $input), 'maxlength', 9, 'server');
            $mform->addRule('excludevalue', null, 'numeric', null, 'client');
            $mform->addRule('excludevalue', null, 'numeric', null, 'server');

            $typeoptions = array(1 => get_string('excludewords', 'turnitintooltwo'),
                                    2 => get_string('excludepercent', 'turnitintooltwo'));

            $mform->addElement('select', 'excludetype', '', $typeoptions);
            $mform->setDefault('excludetype', 1);
        }

        $mform->setType('excludebiblio', PARAM_RAW);
        $mform->setType('excludequoted', PARAM_RAW);
        $mform->setType('excludevalue', PARAM_RAW);
        $mform->setType('excludetype', PARAM_RAW);

        if ( isset($config->transmatch) && $config->transmatch == '1') {
            $mform->addElement('select', 'transmatch', get_string('transmatch', 'turnitintooltwo'), $ynoptions);
            $mform->setDefault('transmatch', $config->default_transmatch);
        }

        // Populate Rubric options.
        if (!empty($config->usegrademark)) {
            $mform->addElement('header', 'advanced', get_string('turnitingmoptions', 'turnitintooltwo'));

            $rubricoptions = array('' => get_string('norubric', 'turnitintooltwo')) + $instructorrubrics;
            if (!empty($this->turnitintooltwo->rubric)) {
                $rubricoptions[$this->turnitintooltwo->rubric] = (isset($rubricoptions[$this->turnitintooltwo->rubric])) ?
                                                                    $rubricoptions[$this->turnitintooltwo->rubric] :
                                                                    get_string('otherrubric', 'turnitintooltwo');
            }

            $rubricline = array();
            $rubricline[] = $mform->createElement('select', 'rubric', '', $rubricoptions);
            $rubricline[] = $mform->createElement('static', 'rubric_link', '',
                                                    html_writer::link($CFG->wwwroot.'/mod/turnitintooltwo/extras.php?'.
                                                                    'cmd=rubricmanager&tiicourseid='.$tiicourseid.'&view_context=box',
                                                                        html_writer::tag('i', '', array('class' => 'tiiicon icon-rubric icon-lg icon_margin')).
                                                                        get_string('launchrubricmanager', 'turnitintooltwo'),
                                                                array('class' => 'rubric_manager_launch',
                                                                    'title' => get_string('launchrubricmanager', 'turnitintooltwo'))).
                                                    html_writer::tag('span', '',
                                                                    array('class' => 'launch_form', 'id' => 'rubric_manager_form')));
            $mform->setDefault('rubric', '');
            $mform->addGroup($rubricline, 'rubricline', get_string('attachrubric', 'turnitintooltwo'), array(' '), false);
            $mform->addElement('hidden', 'rubric_warning_seen', '');
            $mform->setType('rubric_warning_seen', PARAM_RAW);

            $mform->addElement('static', 'rubric_note', '', get_string('attachrubricnote', 'turnitintooltwo'));
        } else {
            $mform->addElement('hidden', 'rubric', '');
            $mform->setType('rubric', PARAM_RAW);
        }

        if (!empty($config->usegrademark) && !empty($config->useerater)) {
            $handbookoptions = array(
                                        1 => get_string('erater_handbook_advanced', 'turnitintooltwo'),
                                        2 => get_string('erater_handbook_highschool', 'turnitintooltwo'),
                                        3 => get_string('erater_handbook_middleschool', 'turnitintooltwo'),
                                        4 => get_string('erater_handbook_elementary', 'turnitintooltwo'),
                                        5 => get_string('erater_handbook_learners', 'turnitintooltwo')
                                    );
            $dictionaryoptions = array(
                                        'en_US' => get_string('erater_dictionary_enus', 'turnitintooltwo'),
                                        'en_GB' => get_string('erater_dictionary_engb', 'turnitintooltwo'),
                                        'en' => get_string('erater_dictionary_en', 'turnitintooltwo')
                                    );
            $mform->addElement('select', 'erater', get_string('erater', 'turnitintooltwo'), $ynoptions);
            $mform->setDefault('erater', $config->default_erater);

            $mform->addElement('select', 'erater_handbook', get_string('erater_handbook', 'turnitintooltwo'), $handbookoptions);
            $mform->setDefault('erater_handbook', $config->default_erater_handbook);
            $mform->disabledIf('erater_handbook', 'erater', 'eq', 0);

            $mform->addElement('select', 'erater_dictionary', get_string('erater_dictionary', 'turnitintooltwo'),
                                    $dictionaryoptions);
            $mform->setDefault('erater_dictionary', $config->default_erater_dictionary);
            $mform->disabledIf('erater_dictionary', 'erater', 'eq', 0);

            $mform->addElement('checkbox', 'erater_spelling', get_string('erater_categories', 'turnitintooltwo'),
                                    " ".get_string('erater_spelling', 'turnitintooltwo'));
            $mform->setDefault('erater_spelling', $config->default_erater_spelling);
            $mform->disabledIf('erater_spelling', 'erater', 'eq', 0);

            $mform->addElement('checkbox', 'erater_grammar', '', " ".get_string('erater_grammar', 'turnitintooltwo'));
            $mform->setDefault('erater_grammar', $config->default_erater_grammar);
            $mform->disabledIf('erater_grammar', 'erater', 'eq', 0);

            $mform->addElement('checkbox', 'erater_usage', '', " ".get_string('erater_usage', 'turnitintooltwo'));
            $mform->setDefault('erater_usage', $config->default_erater_usage);
            $mform->disabledIf('erater_usage', 'erater', 'eq', 0);

            $mform->addElement('checkbox', 'erater_mechanics', '', " ".get_string('erater_mechanics', 'turnitintooltwo'));
            $mform->setDefault('erater_mechanics', $config->default_erater_mechanics);
            $mform->disabledIf('erater_mechanics', 'erater', 'eq', 0);

            $mform->addElement('checkbox', 'erater_style', '', " ".get_string('erater_style', 'turnitintooltwo'));
            $mform->setDefault('erater_style', $config->default_erater_style);
            $mform->disabledIf('erater_style', 'erater', 'eq', 0);
        }

        $mform->addElement('hidden', 'ownerid', null);
        $mform->setType('ownerid', PARAM_RAW);

        $mform->addElement('html', $modulestring);

        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
        $this->add_action_buttons();

    }

    /**
     * Custom validation to validate part dates
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $partnames = array();

        foreach ($data as $name => $value) {
            // Get part names from array of data
            if (strstr($name, 'partname')) $partnames[$name] = strtolower(trim($value));
            // We only need part names for number of parts being used
            if (count($partnames) == $data['numparts']) break;
        }

        for ($i = 1; $i <= $data['numparts']; $i++) {
            // Get a copy of the array for unsetting purposes
            $partnamescopy = $partnames;

            $partname = 'partname'.$i;
            unset($partnamescopy[$partname]);

            if (in_array(strtolower($partnames[$partname]), $partnamescopy)) {
                $errors[$partname] = get_string('uniquepartname', 'turnitintooltwo');
            }

            $dtstart = $data['dtstart'.$i];
            $dtdue = $data['dtdue'.$i];
            $dtpost = $data['dtpost'.$i];
            $maxmarks = (empty($data['maxmarks'.$i])) ? 0 : $data['maxmarks'.$i];

            if (!is_int($maxmarks) && $maxmarks > 100) {
                $errors['maxmarks'.$i] = get_string('maxmarkserror', 'turnitintooltwo');
            }

            if ($dtstart < strtotime('1 year ago')) {
                $errors['dtstart'.$i] = get_string('startdatenotyearago', 'turnitintooltwo');
            }

            if ($dtpost < $dtstart) {
                $errors['dtstart'.$i] = get_string('partposterror', 'turnitintooltwo');
            }

            if ($dtstart >= $dtdue) {
                $errors['dtstart'.$i] = get_string('partdueerror', 'turnitintooltwo');
            }
        }

        return $errors;
    }

    /**
     * Handle the form submission
     */
    public function handle() {
        // Do nothing if not submitted or cancelled.
        if (!$this->is_submitted() || $this->is_cancelled()) {
            return;
        }

        // If the validation fails, return to the form.
        if (!$this->is_validated()) {
            return;
        }

        $data = $this->get_data();
    }
}
