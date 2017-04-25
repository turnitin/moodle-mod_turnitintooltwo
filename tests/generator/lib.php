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

defined(‘MOODLE_INTERNAL’) || die();
global $CFG;
require_once $CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php';

/**
* Turnitintooltwo module data generator class
*
* @category  Test
* @package  mod_turnitintooltwo
* @copyright  2017 David Hatton
* @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
abstract class test_lib {

    /**
     * Create a test part on the specified assignment.
     * 
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param int $assignmentid Assignment Module ID
     * 
     * @return array $parts_created - list of part ids that have been added to the assignment
     */    
    public static function make_test_parts($modname, $assignmentid, $number_of_parts) {
        global $DB;
        $modulevar = $modname.'id';
        $part = new stdClass();
        $part->$modulevar = $assignmentid;
        $part->tiiassignid = 0;
        $part->dtstart = 0;
        $part->dtdue = 0;
        $part->dtpost = 0;
        $part->maxmarks = 0;
        $part->deleted = 0;
        
        $parts_created = array();
        for ($i=0; $i < $number_of_parts; $i++) { 
            $part->partname = uniqid("Part - ", false);
            $partid = $DB->insert_record($modname.'_parts', $part);
            array_push($parts_created, $partid);
        }

        return $parts_created;
    }

    /**
     * Make a test Turnitin assignment module for use in various test cases.
     * @param int $courseid - Moodle course ID
     * @param string $modname - Module name (turnitintool or turnitintooltwo)
     * @param int $assignmentid - Assignment id to which the coursemodule should be added
     * 
     * @return  int $cm - id of the course module added
     */
    public static function make_test_module($courseid, $modname, $assignmentid) {
        global $DB;

        // Set up a course module.
        $module = $DB->get_record("modules", array("name" => $modname));
        $coursemodule = new stdClass();
        $coursemodule->course = $courseid;
        $coursemodule->module = $module->id;
        $coursemodule->added = time();
        $coursemodule->instance = $assignmentid;
        $coursemodule->section = 0;
        
        $cm = add_course_module($coursemodule);    
        
        return $cm;
    }
}

