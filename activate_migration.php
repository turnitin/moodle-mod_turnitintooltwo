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
 * @package   turnitintooltwo
 * @copyright 2012 iParadigms LLC
 */

if ($ADMIN->full_tree) {
    $migration_enabled_params = array(
        'plugin' => 'turnitintooltwo',
        'name' => 'migration_enabled'
    );
    $migration_enabled = $DB->get_record('config_plugins', $migration_enabled_params);

    if (empty($migration_enabled)) {
        $activation_entry->plugin = 'turnitintooltwo';
        $activation_entry->name = 'migration_enabled';
        $activation_entry->value  = 1;
        $activation = $DB->insert_record('config_plugins', $activation_entry);
    } else {
        $activation_update->plugin = 'turnitintooltwo';
        $activation_update->name = 'migration_enabled';
        $activation_update->value  = 1;
        $activation = $DB->update_record('config_plugins', $activation_update);
    }

    // TODO This block will need more padding out, probably. Actually, most certainly.
    if ($activation) {
        # Happy path
    } else {
        # Sad Path
    }
} else {
    die(get_string('notadmin', 'turnitintooltwo'));
}