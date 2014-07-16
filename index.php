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
 * @copyright 2010 iParadigms LLC
 */

require_once("../../config.php");
require_once("lib.php");

require_once("turnitintooltwo_view.class.php");
$turnitintooltwoview = new turnitintooltwo_view();

// Load Javascript and CSS.
$turnitintooltwoview->load_page_components();

$id = required_param('id', PARAM_INT);   // Course id.

// Get course data.
if (!$course = $DB->get_record("course", array("id" => $id))) {
    turnitintooltwo_print_error('courseiderror', 'turnitintooltwo');
}

require_login($course->id);

// Print the header.
$extranavigation = array(array('title' => get_string("modulenameplural", "turnitintooltwo"), 'url' => null));
$turnitintooltwoview->output_header(null, $course, $_SERVER["REQUEST_URI"], get_string("modulenameplural", "turnitintooltwo"),
                                        $SITE->fullname, $extranavigation, '', '', true);

echo $turnitintooltwoview->show_assignments($course);

echo $OUTPUT->footer();