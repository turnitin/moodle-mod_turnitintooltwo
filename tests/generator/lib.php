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

/**
 * turnitintooltwo module data generator class
 *
 * @package mod_turnitintooltwo
 * @category test
 * @copyright 2017 John McGettrick
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_turnitintooltwo_generator extends testing_module_generator {

    /**
     * Create a Turnitin assignment on the course.
     */
    public function create_assignment($record = null, array $options = null) {
        $record = (object)(array)$record;

        $defaultsettings = array(
            'defaultdtstart' => time(),
            'defaultdtdue'   => time(),
            'defaultdtpost'  => time(),
            'anon'           => 0,
            'name'           => 'Test Assignment',
            'grade'          => 100,
            'numparts'       => 1,
            'type'           => 0,
            'grade'          => 100,
            'allowlate'      => 0,
            'reportgenspeed' => 0,
            'submitpapersto' => 1,
            'spapercheck'    => 1,
            'internetcheck'  => 1,
            'journalcheck'   => 1,
            'introformat'    => 1,
            'timecreated'    => time(),
            'timemodified'   => time()
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }

    /**
     * Create a part on the Turnitin assignment.
     */
    public function create_part(array $record = null) {
        global $DB;

        $record = (object)(array)$record;

        $defaultsettings = array(
            'partname' => 'Test Part',
            'dtstart'  => time(),
            'dtdue'    => time(),
            'dtpost'   => time(),
            'maxmarks' => 100,
            'deleted'  => 0
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return $DB->insert_record("turnitintooltwo_parts", $record);
    }
}
