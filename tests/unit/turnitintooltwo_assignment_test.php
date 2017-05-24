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
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');

/**
 * Tests for inbox
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_assignment_testcase extends advanced_testcase {

	/**
	 * Test that the title is truncated to the passed in limit.
	 */
	public function test_truncate_title() {
		$turnitintooltwo = new stdClass();
		$turnitintooltwo->id = 1;

		$turnitintooltwoassignment = new turnitintooltwo_assignment(0, $turnitintooltwo);

		// Test that a string under the limit is returned with a suffix added.
		$originaltitle = 'Test String';
		$expectedtitle = 'Test String (Moodle TT)';
		$limit = 100;
		$title = $turnitintooltwoassignment->truncate_title($originaltitle, $limit, 'TT');
		$this->assertEquals($expectedtitle, $title);
		$this->assertLessThan($limit, strlen($title));

		// Test that a string over the limit is returned truncated with a suffix added and is equal to the limit in length.
		$originaltitle = 'Test String is truncated and has a suffix added on the end with brackets showing the moodle coursetype';
		$limit = 30;
		$title = $turnitintooltwoassignment->truncate_title($originaltitle, $limit, 'TT');
		$this->assertContains('Test String', $title);
		$this->assertNotContains('added on the end', $title);
		$this->assertContains('... (Moodle TT)', $title);
		$this->assertEquals($limit, strlen($title));
	}

	/**
	 * Test that a checkbox field is initialised and not overwritten if already set.
	 */
	public function test_set_checkbox_field() {
		$turnitintooltwo = new stdClass();
		$turnitintooltwo->id = 1;

		$turnitintooltwoassignment = new turnitintooltwo_assignment(0, $turnitintooltwo);		
		$turnitintooltwoassignment->set_checkbox_field('testvar1');

		// Verify that checkbox fields are set to 0 by default.
		$this->assertEquals(0, $turnitintooltwoassignment->turnitintooltwo->testvar1);

		// Verify that checkbox fields are set to passed in value.
		$value = 20;
		$turnitintooltwoassignment->set_checkbox_field('testvar2', $value);
		$this->assertEquals($value, $turnitintooltwoassignment->turnitintooltwo->testvar2);

		// Set checkbox fields.
		$turnitintooltwoassignment->turnitintooltwo->testvar1 = 1;

		// Verify that checkbox fields aren't changed as they are already set.
		$turnitintooltwoassignment->set_checkbox_field('testvar1');
		$this->assertEquals(1, $turnitintooltwoassignment->turnitintooltwo->testvar1);
	}

}