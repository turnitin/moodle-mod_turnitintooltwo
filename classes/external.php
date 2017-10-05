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
 * Turnitintwo services.
 *
 * @package mod_turnitintooltwo
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_turnitintooltwo;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . "/mod/turnitintooltwo/turnitintooltwo_view.class.php");

use external_api;
use external_value;
use external_single_structure;
use external_function_parameters;

/**
 * Turnitintwo services.
 */
class external extends external_api
{
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_submission_status_parameters() {
        return new external_function_parameters(array(
            'submissionid' => new external_value(
                PARAM_INT,
                'The submission ID',
                VALUE_REQUIRED
            )
        ));
    }

    /**
     * Search a list of modules.
     *
     * @param $modulecode
     * @return array [string]
     * @throws \invalid_parameter_exception
     */
    public static function get_submission_status($submissionid) {
        global $DB, $USER, $PAGE;

        $params = self::validate_parameters(self::get_submission_status_parameters(), array(
            'submissionid' => $submissionid
        ));
        $submissionid = $params['submissionid'];

        $submission = $DB->get_record('turnitintooltwo_submissions', array(
            'id' => $submissionid
        ));
        if (!$submission) {
            return array('status' => 'error', 'message' => 'Could not find submission.');
        }

        // Grab more data.
        $turnitintooltwo = $DB->get_record('turnitintooltwo', array('id' => $submission->turnitintooltwoid));
        list($course, $cm) = get_course_and_cm_from_instance($turnitintooltwo, 'turnitintooltwo');

        // Check this is our submission.
        if ($USER->id !== $submission->userid && !has_capability('mod/turnitintooltwo:grade', \context_module::instance($cm->id))) {
            return array('status' => 'nopermission', 'message' => 'You do not have permission to view that.');
        }

        // What is the status?
        $status = $DB->get_record('turnitintooltwo_sub_status', array(
            'submissionid' => $submissionid
        ));
        if (!$status || !$status->status) {
            return array('status' => 'queued', 'message' => '');
        }

        // Decode the receipt.
        $digitalreceipt = (array)json_decode($status->receipt);

        // Woo!
        if ($status->status == \mod_turnitintooltwo\task\submit_assignment::STATUS_SUCCESS) {
            $turnitintooltwoview = new \turnitintooltwo_view();

            $PAGE->set_context(\context_module::instance($cm->id));
            $digitalreceipt = $turnitintooltwoview->show_digital_receipt($digitalreceipt);
            $digitalreceipt = \html_writer::tag("div", $digitalreceipt, array("id" => "box_receipt"));

            return array(
                'status' => 'success',
                'message' => $digitalreceipt
            );
        }

        return array(
            'status' => 'failed',
            'message' => \html_writer::tag("div", $digitalreceipt["message"], array("class" => "alert alert-danger"))
        );
    }

    /**
     * Returns description of get_submission_status() result value.
     *
     * @return external_description
     */
    public static function get_submission_status_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'status codes'),
                'message' => new external_value(PARAM_RAW, 'digital receipt')
            )
        );
    }
}