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

		$originaltitle = 'Test String';
		$expectedtitle = 'Test String (Moodle TT)';
		$title = $turnitintooltwoassignment->truncate_title($originaltitle, 100, 'TT');
		$this->assertEquals($expectedtitle, $title);

		$originaltitle = 'Test String is truncated and has a suffix added on the end with brackets showing the moodle coursetype';
		$title = $turnitintooltwoassignment->truncate_title($originaltitle, 30, 'TT');
		$this->assertContains('Test String', $title);
		$this->assertNotContains('added on the end', $title);
		$this->assertContains('... (Moodle TT)', $title);
	}

}