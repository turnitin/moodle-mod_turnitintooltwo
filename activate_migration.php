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

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__."/turnitintooltwo_view.class.php");
require_once(__DIR__."/classes/v1migration/v1migration.php");

admin_externalpage_setup('managemodules');

// Discern whether the page is displaying its visual side, or doing its back-end work.
$do_migration = optional_param('do_migration', 0, PARAM_INT);

if ($do_migration) {
    $activation = v1migration::activate_migration();
    $urlparams = array('section' => 'modsettingturnitintooltwo');
    if ($activation) {
        $urlparams['activation'] = 'success';
        $urlparams['cmd'] = 'v1migration';
        redirect(new moodle_url('/mod/turnitintooltwo/settings_extras.php', $urlparams));
    } else {
        $urlparams['activation'] = 'failure';
        redirect(new moodle_url('/admin/settings.php', $urlparams));
    }
} else {
    $output = turnitintooltwo_view::build_migration_activation_page();
    echo $output;
}
