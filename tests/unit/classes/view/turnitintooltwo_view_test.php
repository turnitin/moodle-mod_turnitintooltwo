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
     * Test that the v1 migration tab is present in the settings tabs if v1 is installed.
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
            $DB->insert_record('config_plugins', $module);
        }

        // Test that tab is present.
        $tabs = $turnitintooltwoview->draw_settings_menu('v1migration');
        $this->assertContains(get_string('v1migrationtitle', 'turnitintooltwo'), $tabs);
    }

    /**
     * Test that the v1 migration tab is not present in the settings tabs if v1 is not installed.
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

}