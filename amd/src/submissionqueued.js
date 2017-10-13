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

/*
 * @package    mod_turnitintooltwo
 * @author     Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module mod_turnitintooltwo/submissionqueued
 */
define(['jquery', 'core/ajax'], function($, ajax) {
    var submissionid;
    var timer;

    function check_status() {
        var ajaxpromises = ajax.call([{
            methodname: 'mod_turnitintooltwo_get_submission_status',
            args: {
                submissionid: submissionid
            }
        }]);

        ajaxpromises[0].done(function(data) {
            if (data.status == 'queued') {
                return;
            }

            $("#tiisubstatus").html(data.message);

            window.clearInterval(timer);
        });

        ajaxpromises[0].fail(function(ex) {
            console.log(ex);
            window.clearInterval(timer);
        });
    }

    return {
        init: function(id) {
            submissionid = id;
            timer = window.setInterval(check_status, 1000);
        }
    };
});