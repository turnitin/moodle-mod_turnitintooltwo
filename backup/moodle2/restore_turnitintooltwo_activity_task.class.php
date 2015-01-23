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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/turnitintooltwo/backup/moodle2/restore_turnitintooltwo_stepslib.php'); // Because it exists (must)

/**
 * choice restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_turnitintooltwo_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step
        $this->add_step(new restore_turnitintooltwo_activity_structure_step('turnitintooltwo_structure', 'turnitintooltwo.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('turnitintooltwo', array('intro'), 'turnitintooltwo');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('TURNITINTOOLVIEWBYID', '/mod/turnitintooltwo/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('TURNITINTOOLINDEX', '/mod/turnitintooltwo/index.php?id=$1', 'course');

        return $rules;
    }

    /**
     * If no user data was restored after everything has been restored then
     * create a new course in Turnitin
     */
    public function after_restore() {
        global $DB;

        if (!empty($_SESSION['tii_course_reset'])) {

            $course = turnitintooltwo_assignment::get_course_data($_SESSION['course_id']);

            // Remove Turnitin link from course
            $turnitin_course = new stdClass();
            $turnitin_course->id = $course->tii_rel_id;
            $turnitin_course->turnitin_cid = 0;
            $DB->update_record('turnitintooltwo_courses', $turnitin_course);

            // Recreate course in Turnitin
            $course->turnitin_cid = 0;
            $tmpassignment = new turnitintooltwo_assignment(0, '', '');
            $turnitin_course = $tmpassignment->create_tii_course($course, $_SESSION['course_owner_id']);

            // Join the course as Instructor
            $owner = new turnitintooltwo_user($_SESSION['course_owner_id'], 'Instructor');
            $owner->join_user_to_class($turnitin_course->turnitin_cid);

            unset($_SESSION['tii_course_reset']);
            unset($_SESSION['course_id']);
            unset($_SESSION['course_owner_id']);
        }

        if (!empty($_SESSION['assignments_to_create'])) {
            foreach($_SESSION["assignments_to_create"] as $new_assignment_id) {
                $assignment = new turnitintooltwo_assignment($new_assignment_id);
                $assignment->unlink_assignment();
                $assignment->edit_moodle_assignment();
            }
            unset($_SESSION['tii_assignment_reset']);
            unset($_SESSION['assignments_to_create']);
        }

    }

    /**
     * Define the restore log rules that will be applied by the {@link restore_logs_processor} when restoring {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('turnitintooltwo', 'view', 'view.php?id={course_module}', '{turnitintooltwo}');
        $rules[] = new restore_log_rule('turnitintooltwo', 'add', 'view.php?id={course_module}', '{turnitintooltwo}');
        $rules[] = new restore_log_rule('turnitintooltwo', 'update', 'view.php?id={course_module}', '{turnitintooltwo}');
        $rules[] = new restore_log_rule('turnitintooltwo', 'delete', 'view.php?id={course_module}', '{turnitintooltwo}');
        $rules[] = new restore_log_rule('turnitintooltwo', 'submit', 'view.php?id={course_module}', '{turnitintooltwo}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied by the {@link restore_logs_processor} when restoring {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();
        return $rules;
    }
}
