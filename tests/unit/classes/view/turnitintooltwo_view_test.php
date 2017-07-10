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
require_once($CFG->dirroot . '/mod/turnitintooltwo/tests/unit/generator/lib.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_view.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_user.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_comms.class.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Tests for inbox
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_view_testcase extends test_lib {

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
     * Test that the v1 migration tab is present in the settings tabs if v1 is installed
     * AND the tool has been activated.
     */
    public function test_draw_settings_menu_v1_installed() {
        global $DB;
        $this->resetAfterTest();
        $turnitintooltwoview = new turnitintooltwo_view();

        // If v1 is not installed then create a fake row to trick Moodle into thinking it's installed.
        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintool'));
        if (!boolval($module)) {
            $module = new stdClass();
            $module->plugin = 'mod_turnitintool';
            $module->name = 'version';
            $module->value = 1001;
            $DB->insert_record('config_plugins', $module);
        }

        // add entry for migration tool activation 
        $activate_params = new stdClass();
        $activate_params->plugin = 'turnitintooltwo';
        $activate_params->name = 'migration_enabled';
        $activate_params->value = 1;
        $activate = $DB->insert_record('config_plugins', $activate_params);

        // Test that tab is present.
        $tabs = $turnitintooltwoview->draw_settings_menu('v1migration');
        $this->assertContains(get_string('v1migrationtitle', 'turnitintooltwo'), $tabs);
    }

    /**
     * Test that the v1 migration tab is present in the settings tabs if v1 is installed
     * AND the tool has been activated.
     */
    public function test_draw_settings_menu_migration_not_activated() {
        global $DB;
        $this->resetAfterTest();
        $turnitintooltwoview = new turnitintooltwo_view();

        // If v1 is not installed then create a fake row to trick Moodle into thinking it's installed.
        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintool'));
        if (!boolval($module)) {
            $module = new stdClass();
            $module->plugin = 'mod_turnitintool';
            $module->name = 'version';
            $module->value = 1001;
            $DB->insert_record('config_plugins', $module);
        }

        // Test that tab is present.
        $tabs = $turnitintooltwoview->draw_settings_menu('v1migration');
        $this->assertNotContains(get_string('v1migrationtitle', 'turnitintooltwo'), $tabs, __FUNCTION__." - Tabs should not have shown the migration option.");
    }

    /**
     * Test that the v1 migration tab is not present in the settings tabs if v1 is not installed,
     */
    public function test_draw_settings_menu_v1_not_installed() {
        global $DB;
        $this->resetAfterTest();
        $turnitintooltwoview = new turnitintooltwo_view();

        // If v1 is installed then temporarily modify the plugin record to trick Moodle into thinking it's not installed.
        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintool'));
        if (boolval($module)) {
            $tmpmodule = new stdClass();
            $tmpmodule->id = $module->id;
            $tmpmodule->plugin = 'mod_turnitintool_tmp';
            $DB->update_record('config_plugins', $tmpmodule);
        }
        
        $tabs = $turnitintooltwoview->draw_settings_menu('v1migration');
        $this->assertNotContains(get_string('v1migrationtitle', 'turnitintooltwo'), $tabs);

        if (boolval($module)) {
            $tmpmodule->plugin = 'mod_turnitintool';
            $DB->update_record('config_plugins', $tmpmodule);
        }
    }

	/**
	 * Test that the submissions table layout conforms to expectations when the user is an instructor.
	 *
	 * @return  void
	 */
	public function test_inbox_table_structure_instructor() {
		global $DB;
		$this->resetAfterTest();
		$course = $this->getDataGenerator()->create_course();

		$turnitintooltwoassignment = $this->make_test_tii_assignment();

		$cmid = $this->make_test_module($turnitintooltwoassignment->turnitintooltwo->course,'turnitintooltwo', $turnitintooltwoassignment->turnitintooltwo->id);
		$cm = $DB->get_record("course_modules", array('id' => $cmid));

		$roles = array("Instructor");
		$testuser = $this->make_test_users(1, $roles);
		$turnitintooltwouser = $testuser['turnitintooltwo_users'][0];

		$partdetails = $this->make_test_parts('turnitintooltwo',$turnitintooltwoassignment->turnitintooltwo->id, 1);
		
		$turnitintooltwoview = new turnitintooltwo_view();
		$table = $turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $partdetails, $turnitintooltwouser);
		
		$this->assertContains(get_string('studentlastname', 'turnitintooltwo'), $table, 'submission table did not contain expected text "'.get_string('studentlastname','turnitintooltwo').'"');
		$this->assertContains("<tbody class=\"empty\"><tr><td colspan=\"16\"></td></tr></tbody>", $table, 'datatable did not contain the expected empty tbody');
	}
 
	public function test_inbox_table_structure_student() {
		global $DB, $USER;
		$this->resetAfterTest();
		$_SESSION["unit_test"] = true;
		
		$USER->firstname = 'unit_test_first_654984';
		$USER->lastname = 'unit_test_last_654984';
		$USER->language = "en_US";
		$USER->firstnamephonetic = "";
		$USER->lastnamephonetic = "";
		$USER->middlename = "";
		$USER->alternatename = "";

		$course = $this->getDataGenerator()->create_course();

		$turnitintooltwoassignment = $this->make_test_tii_assignment();

		$cmid = $this->make_test_module($turnitintooltwoassignment->turnitintooltwo->course,'turnitintooltwo', $turnitintooltwoassignment->turnitintooltwo->id);
		$cm = $DB->get_record("course_modules", array('id' => $cmid));

		$roles = array("Learner");
		$testuser = $this->make_test_users(1, $roles);
		$turnitintooltwouser = $testuser['turnitintooltwo_users'][0];
		$moodleuser = $DB->get_record("turnitintooltwo_users", array("id" => $testuser['joins'][0]));

		$this->enrol_test_user($USER->id, $course->id, "Learner");
		$this->enrol_test_user($moodleuser->userid, $course->id, "Learner");

		$partdetails = $this->make_test_parts('turnitintooltwo',$turnitintooltwoassignment->turnitintooltwo->id, 1);
		
		$turnitintooltwoview = new turnitintooltwo_view();
		$table = $turnitintooltwoview->init_submission_inbox($cm, $turnitintooltwoassignment, $partdetails, $turnitintooltwouser);

		reset($partdetails);
		$partid = key($partdetails);
		
		$this->assertNotContains(get_string('studentlastname', 'turnitintooltwo'), $table, 'submission table contained unexpected text "'.get_string('studentlastname','turnitintooltwo').'"');
		$this->assertContains("<table class=\"submissionsDataTable\" id=\"$partid\">", $table, 'Return did not include the expected table.');
		$this->assertContains("<td class=\"centered_cell cell c0\" style=\"\">$partid</td>", $table, 'Return did not contain the expected student row.');
	}

    /**
     * Tests the visual display of the migration tool activation page.
     *
     * @return void
     */
    public function test_migration_activation_display() {
        $this->resetAfterTest();
        $actual = turnitintooltwo_view::build_migration_activation_page();
        
        $expected = get_string('activatemigrationnotice', 'turnitintooltwo');
        $this->assertContains($expected, $actual, __FUNCTION__.' - migration tool activation page did not show the notice.');
        
        $expected = html_writer::link(
            new moodle_url('/mod/turnitintooltwo/activate_migration.php', array('do_migration' => 1)),
            get_string('activatemigration', 'turnitintooltwo'),
            array('class'=>'btn btn-default', 'role' => 'button')
        );
        $this->assertContains($expected, $actual, __FUNCTION__.'migration tool activation page did not show the button.');
    }
}