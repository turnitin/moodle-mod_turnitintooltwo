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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/v1migration/v1migration.php');

/**
 * Tests for classes/v1migration/v1migration
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_v1migration_testcase extends advanced_testcase {

    /**
     * Test that users get migrated from the v1 to the v2 user table.
     */
    public function test_migrate_users() {
        global $DB;

        if ($this->v1installed()) {
            return false;
        }

        $v1migration = new v1migration();

        $this->resetAfterTest();

        // Generate a new users to migrate.
        $user1 = $this->getDataGenerator()->create_user();

        // Create user in v1 tables.
        $turnitintooluser = new stdClass();
        $turnitintooluser->userid = $user1->id;
        $turnitintooluser->turnitin_uid = 1001;
        $turnitintooluser->turnitin_utp = 1;
        $DB->insert_record('turnitintool_users', $turnitintooluser);

        // Migrate users to v2 tables.
        $v1migration->migrate_users();

        $turnitintooltwousers = $DB->get_records('turnitintooltwo_users', array('userid' => $user1->id));

        $this->assertEquals(1, count($turnitintooltwousers));
    }

    /**
     * Check whether v1 is installed.
     */
    public function v1installed() {
        global $DB;

        $module = $DB->get_record('config_plugins', array('plugin' => 'mod_turnitintool'));
        return boolval($module);
    }
}
