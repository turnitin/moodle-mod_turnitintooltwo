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

class members_view {
    public $course;
    public $coursemodule;
    public $turnitintooltwoassignment;
    public $turnitintooltwoview;

    public function __construct($course=null, $coursemodule=null, $turnitintooltwoview=null, $turnitintooltwoassignment=null) {
        $this->course = $course;
        $this->coursemodule = $coursemodule;
        $this->turnitintooltwoview = $turnitintooltwoview;
        $this->turnitintooltwoassignment = $turnitintooltwoassignment;
    }

    /**
     *
     */
    public function build_members_view($displayrole = "students") {
        $output  = "";
        $istutor = $this->is_tutor();
        $memberrole = $this->get_role_for_display_role($displayrole);

        if (!$istutor) {
            turnitintooltwo_print_error('permissiondeniederror', 'turnitintooltwo');
            exit();
        }

        // wrapper element for strong CSS selectors
        if ($displayrole == "tutors") {
            $output .= html_writer::start_tag("div", array("class" => "members members-instructors"));
        } else {
            $output .= html_writer::start_tag("div", array("class" => "members members-students"));
        }

        $output .= $this->build_intro_message($displayrole);
        $output .= $this->build_members_table($memberrole);
        $output .= $this->build_add_tutors_form($displayrole);

        $output .= html_writer::end_tag("div");

        return $output;
    }

    public function is_tutor () {
        return has_capability('mod/turnitintooltwo:grade', context_module::instance($this->coursemodule->id));
    }

    public function get_role_for_display_role ($displayrole) {
        return $displayrole == "tutors" ? 'Instructor' : 'Learner';
    }

    public function build_intro_message ($displayrole = "students") {
        global $OUTPUT;

        $message = "";

        if ($displayrole == "tutors") {
            $introtextnkey = 'turnitintutors_desc';
        } else {
            $introtextnkey = 'turnitinstudents_desc';
        }

        $introtext = get_string($introtextnkey, 'turnitintooltwo');

        return $OUTPUT->box($introtext, 'generalbox boxaligncenter', 'general');;
    }

    public function build_members_table ($role="Learner") {
        $turnitintooltwoassignment = $this->turnitintooltwoassignment;
        $turnitintooltwoview = $this->turnitintooltwoview;
        $coursemodule = $this->coursemodule;

        return $turnitintooltwoview->init_tii_member_by_role_table($coursemodule, $turnitintooltwoassignment, $role);
    }

    public function build_add_tutors_form ($displayrole) {
        // early escape only show the add tutors in the tutors members list
        if ($displayrole != "tutors") {
            return "";
        }

        $tutors = $this->turnitintooltwoassignment->get_tii_users_by_role("Instructor", "mdl");
        return $this->turnitintooltwoview->show_add_tii_tutors_form($this->coursemodule, $tutors);
    }
}
