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
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_comms.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/tests/generator/lib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Tests for inbox
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_view_testcase extends advanced_testcase {

	/**
	 * Test that the page layout is set to standard so that the header displays.
	 * 
	 * @return  void
	 */
	public function test_output_header() {
		global $PAGE;
		$this->resetAfterTest();

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
	 * @return  void
	 */
	public function test_inbox_table_instructor() {
		global $DB;
		$this->resetAfterTest();
		$course = $this->getDataGenerator()->create_course();

		$turnitintooltwoassignment = test_lib::make_test_tii_assignment();

		$cmid = test_lib::make_test_module($turnitintooltwoassignment->turnitintooltwo->course,'turnitintooltwo', $turnitintooltwoassignment->get_id());
		$cm = $DB->get_record("course_modules", array('id' => $cmid));

		$turnitintooltwouser = new turnitintooltwo_user(1, 'instructor', false, 'site', false);
		
		$partdetails = test_lib::make_test_parts('turnitintooltwo',$turnitintooltwoassignment->turnitintooltwo->id, 1);
		
		$turnitintooltwoview = new turnitintooltwo_view();
		$table = $turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $partdetails, $turnitintooltwouser);

		$this->assertContains(get_string('studentlastname', 'turnitintooltwo'), $table, 'submission table did not contain expected text "'.get_string('studentlastname','turnitintooltwo').'"');
		$this->assertContains(get_string('studentfirstname', 'turnitintooltwo'), $table, 'submission table did not contain expected text "'.get_string('studentfirstname','turnitintooltwo').'"');
	}

	public function test_inbox_table_student() {
		global $DB;
		$this->resetAfterTest();
		$course = $this->getDataGenerator()->create_course();

		$turnitintooltwoassignment = test_lib::make_test_tii_assignment();

		$cmid = test_lib::make_test_module($turnitintooltwoassignment->turnitintooltwo->course,'turnitintooltwo', $turnitintooltwoassignment->get_id());
		$cm = $DB->get_record("course_modules", array('id' => $cmid));

		$turnitintooltwouser = new turnitintooltwo_user(1, 'learner', false, 'site', false);
		
		$partdetails = test_lib::make_test_parts('turnitintooltwo',$turnitintooltwoassignment->turnitintooltwo->id, 1);
		
		$turnitintooltwoview = new turnitintooltwo_view();
		$table = $turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $partdetails, $turnitintooltwouser);

		$this->assertNotContains(get_string('studentlastname', 'turnitintooltwo'), $table, 'submission table contained unexpected text "'.get_string('studentlastname','turnitintooltwo').'"');
		$this->assertNotContains(get_string('studentfirstname', 'turnitintooltwo'), $table, 'submission table contained unexpected text "'.get_string('studentfirstname','turnitintooltwo').'"');

	}
}