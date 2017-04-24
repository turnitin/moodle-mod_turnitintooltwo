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

/**
* turnitintooltwo module data generator class
*
* @package mod_turnitintooltwo
* @category test
* @copyright 2017 David Hatton
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
     * @param int $courseid Moodle course ID
     * @param string $modname Module name (turnitintool or turnitintooltwo)
     * @param string $assignmentname Name of the assignment
     */
    public static function make_test_module($courseid, $modname, $assignmentname = "") {
        global $DB;
        if (!$this->v1installed()) {
            return false;
        }
        $this->resetAfterTest();
        $assignment = new stdClass();
        $assignment->name = ($assignmentname == "") ? "Test Turnitin Assignment" : $assignmentname;
        $assignment->course = $courseid;
        // Initialise fields.
        $nullcheckfields = array('grade', 'allowlate', 'reportgenspeed', 'submitpapersto', 'spapercheck', 'internetcheck', 'journalcheck', 'introformat',
                            'studentreports', 'dateformat', 'usegrademark', 'gradedisplay', 'autoupdates', 'commentedittime', 'commentmaxsize',
                            'autosubmission', 'shownonsubmission', 'excludebiblio', 'excludequoted', 'excludevalue', 'erater', 'erater_handbook',
                            'erater_spelling', 'erater_grammar', 'erater_usage', 'erater_mechanics', 'erater_style', 'transmatch', 'excludetype', 'perpage');
        // Set all fields to null.
        foreach ($nullcheckfields as $field) {
            $assignment->$field = null;
        }
        
        $assignment->id = $DB->insert_record($modname, $assignment);
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
        
        return $assignment;
    }
}

