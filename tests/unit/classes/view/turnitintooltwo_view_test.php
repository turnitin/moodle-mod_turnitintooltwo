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
 * Unit tests for (some of) mod/turnitintooltwo/view.php.
 *
 * @package    mod_turnitintooltwo
 * @copyright  2017 Turnitin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_view.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_user.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/tests/generator/lib.php');

/**
 * Tests for inbox
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_view_testcase extends advanced_testcase {

	/**
	 * Test that the page layout is set to standard so that the header displays.
	 */
	public function test_output_header() {
		global $PAGE;
		$turnitintooltwoview = new turnitintooltwo_view();

		$pageurl = '/fake/url/';
		$pagetitle = 'Fake Title';
		$pageheading = 'Fake Heading';
		$turnitintooltwoview->output_header($pageurl, $pagetitle, $pageheading, true);

		$this->assertContains($pageurl, (string)$PAGE->url);
		$this->assertEquals($pagetitle, $PAGE->title);
		$this->assertEquals($pageheading, $PAGE->heading);
	}

	/**
	 * Test that the submissions table layout conforms to expectations when the user is an instructor.
	 *
	 * @return void
	 */
	public function test_inbox_table_instructor() {
		global $PAGE;
		$turnitintooltwoview = new turnitintooltwo_view();

		// Create Assignment Part.
        $partid = $this->make_test_part($modname, $assignment->id);

		// Set up a course module.
        $module = $DB->get_record("modules", array("name" => $modname));
        $coursemodule = new stdClass();
        $coursemodule->course = $courseid;
        $coursemodule->module = $module->id;
        $coursemodule->added = time();
        $coursemodule->instance = $assignment->id;
        $coursemodule->section = 0;
        // Add Course module if a v1 module.
        if ($modname == 'turnitintool') {
            add_course_module($coursemodule);    
        }

		$cm;
		$turnitintooltwoassignment = new turnitintooltwo_assignment(0, $turnitintooltwo);
		$partdetails;
		$turnitintooltwouser = new turnitintooltwo_user();
		$turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $partdetails, $turnitintooltwouser);

	}

	/**
     * Create a test part on the specified assignment.
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param int $assignmentid Assignment Module ID
     */    
    public function make_test_part($modname, $assignmentid) {
        global $DB;
        $modulevar = $modname.'id';
        $part = new stdClass();
        $part->$modulevar = $assignmentid;
        $part->partname = 'Part 1';
        $part->tiiassignid = 0;
        $part->dtstart = 0;
        $part->dtdue = 0;
        $part->dtpost = 0;
        $part->maxmarks = 0;
        $part->deleted = 0;
        
        $partid = $DB->insert_record($modname.'_parts', $part);
        return $partid;
    }

}