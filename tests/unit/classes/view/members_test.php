<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/turnitintooltwo/classes/view/members.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_assignment.class.php');
require_once($CFG->dirroot . '/mod/turnitintooltwo/turnitintooltwo_view.class.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/lti/lib.php');

/**
 * Tests for classes/view/members
 *
 * @package turnitintooltwo
 */
class mod_turnitintooltwo_view_members_testcase extends externallib_advanced_testcase {
    /**
     * Test display role given returns as the expected Turnitin role
     */
    public function test_get_role_for_display_role() {
        $members = new members_view();

        $role = $members->get_role_for_display_role(null);
        $this->assertEquals('Learner', $role);

        $role = $members->get_role_for_display_role("tutors");
        $this->assertEquals('Instructor', $role);

        $role = $members->get_role_for_display_role("students");
        $this->assertEquals('Learner', $role);
    }

    /**
     * Test given a role the correct intro message for the members view is
     * generated.
     */
    public function test_build_intro_message() {
        $members = new members_view();

        $message = $members->build_intro_message();
        $this->assertEquals('<div id="general" class="box generalbox boxaligncenter">The selected Users below are enrolled on this Turnitin Class. Enrolled students can gain access to this class by logging in to the Turnitin web site.</div>', $message);

        $message = $members->build_intro_message("students");
        $this->assertEquals('<div id="general" class="box generalbox boxaligncenter">The selected Users below are enrolled on this Turnitin Class. Enrolled students can gain access to this class by logging in to the Turnitin web site.</div>', $message);

        $message = $members->build_intro_message("tutors");
        $this->assertEquals('<div id="general" class="box generalbox boxaligncenter">The selected Tutors below are enrolled as tutors on this Turnitin Class. Enrolled tutors can gain access to this class by logging in to the Turnitin web site.</div>', $message);
    }

    public function test_build_members_table() {
        $observer = $this->getMockBuilder(turnitintooltwo_view::class)
             ->setMethods(['init_tii_member_by_role_table'])
             ->getMock();

        $observer->expects($this->exactly(3))
             ->method('init_tii_member_by_role_table')
             ->willReturn('<table>fake table!</table>')
             ->withConsecutive(
                [$this->equalTo('fakemodule'), $this->equalTo('faketiiassignment'), $this->equalTo('Learner')],
                [$this->equalTo('fakemodule'), $this->equalTo('faketiiassignment'), $this->equalTo('Instructor')],
                [$this->equalTo('fakemodule'), $this->equalTo('faketiiassignment'), $this->equalTo('Learner')]
            );

        $members = new members_view('fakecourse', 'fakemodule', $observer, 'faketiiassignment');

        $table = $members->build_members_table('Learner');
        $this->assertEquals('<table>fake table!</table>', $table);

        $table = $members->build_members_table('Instructor');
        $this->assertEquals('<table>fake table!</table>', $table);

        $table = $members->build_members_table();
        $this->assertEquals('<table>fake table!</table>', $table);
    }

    public function test_build_add_tutors_form() {
        $faketiiview = $this->getMockBuilder(turnitintooltwo_view::class)
             ->setMethods(['show_add_tii_tutors_form'])
             ->getMock();

        $faketiiview->expects($this->once())
             ->method('show_add_tii_tutors_form')
             ->willReturn('<form>fake form!</form>')
             ->withConsecutive(
                [$this->equalTo('fakemodule'), $this->equalTo('faketutors')]
            );

        // Create a stub for the SomeClass class.
        $faketiiassignment = $this->createMock(turnitintooltwo_assignment::class);

        // Configure the stub.
        $faketiiassignment->method('get_tii_users_by_role')
             ->willReturn('faketutors');

        $members = new members_view(null, 'fakemodule', $faketiiview, $faketiiassignment);

        $form = $members->build_add_tutors_form("foobar");

        $this->assertEquals('', $form);

        $form = $members->build_add_tutors_form("tutors");
        $this->assertEquals('<form>fake form!</form>', $form);
    }
}
