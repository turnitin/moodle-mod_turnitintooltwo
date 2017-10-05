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
 * Turnitintwo adhoc tasks.
 *
 * @package mod_turnitintooltwo
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_turnitintooltwo\task;

/**
 * Submit queued assignments.
 */
class submit_assignment extends \core\task\adhoc_task
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = 2;

    public function get_component() {
        return 'mod_turnitintooltwo';
    }

    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/mod/turnitintooltwo/lib.php");
        require_once($CFG->dirroot . "/mod/turnitintooltwo/turnitintooltwo_view.class.php");

        $data = (array)$this->get_custom_data();

        // Make sure we are still wanted.
        $submission = $DB->get_record('turnitintooltwo_submissions', array(
            'id' => $data['submissionid']
        ));
        if (!$submission) {
            return true;
        }

        cli_writeln("Processing Turnitintooltwo submission: " . $data['submissionid']);

        $user = $DB->get_record('user', array('id' => $data['userid']));
        \core\session\manager::set_user($user);

        $turnitintooltwo = $DB->get_record('turnitintooltwo', array('id' => $data['tiiid']));
        list($course, $cm) = get_course_and_cm_from_instance($turnitintooltwo, 'turnitintooltwo');

        try {
            $turnitintooltwoassignment = new \turnitintooltwo_assignment($turnitintooltwo->id, $turnitintooltwo);
            $turnitintooltwosubmission = new \turnitintooltwo_submission($data['submissionid'], "moodle", $turnitintooltwoassignment);
            $parts = $turnitintooltwoassignment->get_parts();

            $tiisubmission = $turnitintooltwosubmission->do_tii_submission($cm, $turnitintooltwoassignment);

            // Update submission.
            if ($tiisubmission['success'] == true) {
                $DB->update_record('turnitintooltwo_submissions', array(
                    'id' => $data['submissionid'],
                    'submission_modified' => $data['subtime']
                ));
            }
        } catch (\Exception $e) {
            $tiisubmission = array(
                'success' => false,
                'message' => $e->getMessage()
            );

            cli_writeln($e->getMessage());
        }

        $digitalreceipt = $tiisubmission;
        $digitalreceipt['is_manual'] = 0;
        $digitalreceipt = json_encode($digitalreceipt);

        $this->update_sub_status($data['submissionid'], $tiisubmission['success'], $digitalreceipt);

        if ($tiisubmission['success'] === true) {
            $lockedassignment = new \stdClass();
            $lockedassignment->id = $turnitintooltwoassignment->turnitintooltwo->id;
            $lockedassignment->submitted = 1;
            $DB->update_record('turnitintooltwo', $lockedassignment);

            $lockedpart = new \stdClass();
            $lockedpart->id = $data['submissionpart'];
            $lockedpart->submitted = 1;

            // Disable anonymous marking if post date has passed.
            if ($parts[$data['submissionpart']]->dtpost <= time()) {
                $lockedpart->unanon = 1;
            }

            $DB->update_record('turnitintooltwo_parts', $lockedpart);

            cli_writeln("Finished processing successful submission: " . $data['submissionid']);
        } else {
            turnitintooltwo_add_to_log(
                $course->id,
                "errored submission",
                'view.php?id='.$cm->id,
                "Failed to submit '" . $turnitintooltwosubmission->submission_title . "' " . ($tiisubmission['message'] ?: ''),
                $cm->id,
                $user->id
            );

            cli_writeln("Finished processing unsuccessful submission: " . $data['submissionid']);
        }

        \core\session\manager::set_user(get_admin());

        return $tiisubmission['success'];
    }

    /**
     * Update sub status.
     */
    private function update_sub_status($submissionid, $status, $receipt) {
        global $DB;

        $status = $status === true ? self::STATUS_SUCCESS : self::STATUS_FAILED;

        $record = $DB->get_record('turnitintooltwo_sub_status', array(
            'submissionid' => $submissionid
        ));

        if ($record) {
            $record->status = $status;
            $record->receipt = $receipt;

            return $DB->update_record('turnitintooltwo_sub_status', $record);
        }

        return $DB->insert_record('turnitintooltwo_sub_status', array(
            'submissionid' => $submissionid,
            'status' => $status,
            'receipt' => $receipt
        ));
    }

    /**
     * Setter for $customdata.
     * @param mixed $customdata (anything that can be handled by json_encode)
     * @throws \moodle_exception
     */
    public function set_custom_data($customdata) {
        if (empty($customdata['tiiid'])) {
            throw new \moodle_exception("tiiid cannot be empty!");
        }

        parent::set_custom_data($customdata);
    }
}