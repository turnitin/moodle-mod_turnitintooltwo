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

$module->version   = 2015040102;  // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012062500;
$module->component = 'mod_turnitintooltwo';
$module->maturity  = MATURITY_STABLE;
$module->cron      = 1800;        // Period for cron to check this module in seconds

if (empty($plugin)) {
	$plugin = new StdClass();
}

$plugin->version   = 2015040102;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2012062500;
$plugin->component = 'mod_turnitintooltwo';
$plugin->maturity  = MATURITY_STABLE;
$plugin->cron      = 1800;        // Period for cron to check this module in seconds
