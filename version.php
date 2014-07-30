<?php
/**
 * @package   turnitintooltwo
 * @copyright 2012 iParadigms LLC
 */

$module->version   = 2014012405;  // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012062500;
$module->component = 'mod_turnitintooltwo';
$module->maturity  = MATURITY_STABLE;
$module->release  = '2.3+';
$module->cron      = 1800;        // Period for cron to check this module in seconds

if (empty($plugin)) {
	$plugin = new StdClass();
}

$plugin->version   = 2014012405;  // The current module version (Date: YYYYMMDDXX)
$plugin->requires  = 2012062500;
$plugin->component = 'mod_turnitintooltwo';
$plugin->maturity  = MATURITY_STABLE;
$plugin->release  = '2.3+';
$plugin->cron      = 1800;        // Period for cron to check this module in seconds