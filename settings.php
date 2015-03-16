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

if ($ADMIN->fulltree) {
    include_once(__DIR__.'/lib.php');
    require_once(__DIR__.'/settingslib.php');
    require_once(__DIR__."/turnitintooltwo_view.class.php");

    $turnitintooltwoview = new turnitintooltwo_view();

    $config = turnitintooltwo_admin_config();

    $library_warning = '';
    if (!extension_loaded('XMLWriter')) {
        $library_warning = html_writer::tag('div', get_string('noxmlwriterlibrary', 'turnitintooltwo'),
                                                array('class' => 'library_not_present_warning'));
    }

    $tabmenu = $turnitintooltwoview->draw_settings_menu($module, 'settings').
                html_writer::tag('noscript', get_string('noscript', 'turnitintooltwo')).$library_warning.
                html_writer::tag('link', '', array("rel" => "stylesheet", "type" => "text/css",
                                            "href" => $CFG->wwwroot."/mod/turnitintooltwo/css/styles.css"));

    $current_section = optional_param('section', '', PARAM_ALPHAEXT);

    $version = (empty($module->version)) ? $module->versiondisk : $module->version;

    if ($current_section == 'modsettingturnitintooltwo') {
        if ($CFG->branch <= 25) {
            $tabmenu .= html_writer::tag('script', '', array("type" => "text/javascript",
                                                    "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/jquery-1.8.2.min.js")).
                        html_writer::tag('script', '', array("type" => "text/javascript",
                                                    "src" => $CFG->wwwroot."/mod/turnitintooltwo/jquery/turnitintooltwo_settings.js"));
        } else {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('turnitintooltwo-turnitintooltwo_settings', 'mod_turnitintooltwo');
        }

        $PAGE->requires->string_for_js('upgradeavailable', 'turnitintooltwo');

        if (is_siteadmin()) {
            $data = turnitintooltwo_updateavailable($version);

            if ($data['update']) {
                $upgrade = html_writer::tag('a', get_string('upgradeavailable', 'turnitintooltwo'), array('href' => $data['file']));
            } else {
                $upgrade = html_writer::tag('span', get_string('upgradenotavailable', 'turnitintooltwo'), array('class' => 'tii_no_upgrade'));
                $upgrade .= html_writer::tag('a', $OUTPUT->pix_icon('refresh', get_string('checkingupgrade', 'turnitintooltwo'), 'mod_turnitintooltwo'), array('href' => '#', 'class' => 'tii_upgrade_check', 'id' => 'version_'.$version));
            }
        }

        $upgrade .= html_writer::tag('span', $OUTPUT->pix_icon('loader', get_string('checkingupgrade', 'turnitintooltwo'), 'mod_turnitintooltwo'), array('class' => 'tii_upgrading_check'));
    }

    // Offline mode provided by Androgogic. Set tiioffline in config.php.
    $offlinecomment = '';
    if (!empty($CFG->tiioffline)) {
        $offlinecomment = html_writer::start_tag('div', array('class' => 'offline_status'));
        $offlinecomment .= $OUTPUT->box(get_string('offlinestatus', 'turnitintooltwo'), 'offline');
        $offlinecomment .= html_writer::end_tag('div');
    }

    // Test connection to turnitin link
    $testconnection = html_writer::start_tag('div', array('class' => 'test_connection', 'style' => 'display: none;'));
    $testconnection .= $OUTPUT->box($OUTPUT->pix_icon('globe', get_string('connecttest', 'turnitintooltwo'),
                                                'mod_turnitintooltwo')." ".
                                                    html_writer::tag('span',
                                                        get_string('connecttest', 'turnitintooltwo')),
                                                '', 'test_link');

    $testconnection .= $OUTPUT->box($OUTPUT->pix_icon('loader', get_string('testingconnection', 'turnitintooltwo'),
                                                        'mod_turnitintooltwo')." ".html_writer::tag('span',
                                                                    get_string('testingconnection', 'turnitintooltwo')),
                                                '', 'testing_container');
    $testconnection .= $OUTPUT->box('', '', 'test_result');
    $testconnection .= html_writer::end_tag('div');

    $desc = '('.get_string('moduleversion', 'turnitintooltwo').': '.$version.')';

    if ($current_section == 'modsettingturnitintooltwo') {
        $desc .= ' - '.$upgrade;
    }

    $settings->add(new admin_setting_heading('turnitintooltwo_header', $desc, $tabmenu));

    $settings->add(new admin_setting_configtext_int_only('turnitintooltwo/accountid',
                                                    get_string("turnitinaccountid", "turnitintooltwo"),
                                                    get_string("turnitinaccountid_desc", "turnitintooltwo"), ''));

    $settings->add(new admin_setting_config_tii_secret_key('turnitintooltwo/secretkey',
                                                        get_string("turnitinsecretkey", "turnitintooltwo"),
                                                        get_string("turnitinsecretkey_desc", "turnitintooltwo"), '', 'PARAM_TEXT'));

    $testoptions = array(
        'https://api.turnitin.com' => 'https://api.turnitin.com',
        'https://submit.ac.uk' => 'https://submit.ac.uk',
        'https://sandbox.turnitin.com' => 'https://sandbox.turnitin.com'
    );

    // Add to moodle config.php file
    //
    // $CFG->turnitinqa = true;
    // $CFG->turnitinqaurls = array(
    //     'https://sprint.turnitin.com'
    // );
    if (!empty($CFG->turnitinqa)) {
        foreach ($CFG->turnitinqaurls as $url) {
            $testoptions[$url] = $url;
        }
    }

    $settings->add(new admin_setting_configselect('turnitintooltwo/apiurl',
                                                    get_string("turnitinapiurl", "turnitintooltwo"),
                                                    get_string("turnitinapiurl_desc", "turnitintooltwo").$offlinecomment.$testconnection, 0, $testoptions));

    $ynoptions = array(0 => get_string('no'), 1 => get_string('yes'));

    $settings->add(new admin_setting_configselect('turnitintooltwo/enablediagnostic', get_string('turnitindiagnostic', 'turnitintooltwo'),
                        get_string('turnitindiagnostic_desc', 'turnitintooltwo'), 0, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/enableperformancelogs', get_string('enableperformancelogs', 'turnitintooltwo'),
                        get_string('enableperformancelogs_desc', 'turnitintooltwo'), 0, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/usegrademark',
                                                    get_string('turnitinusegrademark', 'turnitintooltwo'),
                                                    get_string('turnitinusegrademark_desc', 'turnitintooltwo'),
                                                    1, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/enablepeermark',
                                                    get_string('turnitinenablepeermark', 'turnitintooltwo'),
                                                    get_string('turnitinenablepeermark_desc', 'turnitintooltwo'),
                                                    1, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/useerater',
                                                    get_string('turnitinuseerater', 'turnitintooltwo'),
                                                    get_string('turnitinuseerater_desc', 'turnitintooltwo'),
                                                    0, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/useanon',
                                                    get_string('turnitinuseanon', 'turnitintooltwo'),
                                                    get_string('turnitinuseanon_desc', 'turnitintooltwo'),
                                                    0, $ynoptions));

    $settings->add(new admin_setting_configselect('turnitintooltwo/transmatch',
                                                    get_string('transmatch', 'turnitintooltwo'),
                                                    get_string('transmatch_desc', 'turnitintooltwo'),
                                                    0, $ynoptions));

    $repositoryoptions = array(
            0 => get_string('repositoryoptions_0', 'turnitintooltwo'),
            1 => get_string('repositoryoptions_1', 'turnitintooltwo'),
            2 => get_string('repositoryoptions_2', 'turnitintooltwo'),
            3 => get_string('repositoryoptions_3', 'turnitintooltwo')
        );

    $settings->add(new admin_setting_configselect('turnitintooltwo/repositoryoption',
                                                    get_string('turnitinrepositoryoptions', 'turnitintooltwo'),
                                                    get_string('turnitinrepositoryoptions_desc', 'turnitintooltwo'),
                                                    0, $repositoryoptions));

    if (empty($config->agreement)) {
        $config->agreement = get_string('turnitintooltwoagreement_default', 'turnitintooltwo');
    }

    $settings->add(new admin_setting_configtextarea('turnitintooltwo/agreement',
                                                    get_string('turnitintooltwoagreement', 'turnitintooltwo'),
                                                    get_string('turnitintooltwoagreement_desc', 'turnitintooltwo'), ''));

    // Following are values for student privacy settings.
    $settings->add(new admin_setting_heading('turnitintooltwo_privacy', get_string('studentdataprivacy', 'turnitintooltwo'),
                       get_string('studentdataprivacy_desc', 'turnitintooltwo')));

    if ($DB->count_records('turnitintooltwo_users') > 0 AND isset($config->enablepseudo)) {
        $selectionarray = ($config->enablepseudo == 1) ? array(1 => get_string('yes')) : array(0 => get_string('no'));
        $pseudoselect = new admin_setting_configselect('turnitintooltwo/enablepseudo',
                                                        get_string('enablepseudo', 'turnitintooltwo'),
                                                        get_string('enablepseudo_desc', 'turnitintooltwo'),
                                                        0, $selectionarray);
        $pseudoselect->nosave = true;
    } else if ($DB->count_records('turnitintooltwo_users') > 0) {
        $pseudoselect = new admin_setting_configselect('turnitintooltwo/enablepseudo',
                                                        get_string('enablepseudo', 'turnitintooltwo'),
                                                        get_string('enablepseudo_desc', 'turnitintooltwo'),
                                                        0, array( 0 => get_string('no', 'turnitintooltwo')));
    } else {
        $pseudoselect = new admin_setting_configselect('turnitintooltwo/enablepseudo',
                                                        get_string('enablepseudo', 'turnitintooltwo'),
                                                        get_string('enablepseudo_desc', 'turnitintooltwo'),
                                                        0, $ynoptions);
    }
    $settings->add($pseudoselect);

    if (isset($config->enablepseudo) AND $config->enablepseudo) {
        $config->pseudofirstname = ( isset( $config->pseudofirstname ) ) ?
                                        $config->pseudofirstname : get_string('defaultcoursestudent');

        $settings->add(new admin_setting_configtext('turnitintooltwo/pseudofirstname',
                                                        get_string('pseudofirstname', 'turnitintooltwo'),
                                                        get_string('pseudofirstname_desc', 'turnitintooltwo'),
                                                        get_string('defaultcoursestudent')));

        $lnoptions = array( 0 => get_string('user') );

        $userprofiles = $DB->get_records('user_info_field');
        foreach ($userprofiles as $profile) {
            $lnoptions[$profile->id] = get_string('profilefield', 'admin').': '.$profile->name;
        }

        $settings->add(new admin_setting_configselect('turnitintooltwo/pseudolastname',
                                                        get_string('pseudolastname', 'turnitintooltwo'),
                                                        get_string('pseudolastname_desc', 'turnitintooltwo'),
                                                        0, $lnoptions));

        $settings->add(new admin_setting_configselect('turnitintooltwo/lastnamegen',
                                                        get_string('psuedolastnamegen', 'turnitintooltwo'),
                                                        get_string('psuedolastnamegen_desc', 'turnitintooltwo' ),
                                                        0, $ynoptions));

        $settings->add(new admin_setting_configtext('turnitintooltwo/pseudosalt',
                                                        get_string('pseudoemailsalt', 'turnitintooltwo'),
                                                        get_string('pseudoemailsalt_desc', 'turnitintooltwo'), ''));

        $settings->add(new admin_setting_configtext('turnitintooltwo/pseudoemaildomain',
                                                        get_string('pseudoemaildomain', 'turnitintooltwo'),
                                                        get_string('pseudoemaildomain_desc', 'turnitintooltwo'), ''));
    }

    // Following are default values for new instance.

    $settings->add(new admin_setting_heading('turnitintooltwo/defaults',
                                                get_string('defaults', 'turnitintooltwo'),
                                                get_string('defaults_desc', 'turnitintooltwo')));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_type',
                                                    get_string('type', 'turnitintooltwo'),
                                                    '', 0, turnitintooltwo_filetype_array()));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_numparts',
                                                    get_string('numberofparts', 'turnitintooltwo'),
                                                    '', 1, array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)));

    $options = array();
    $scales = get_scales_menu();
    foreach ($scales as $value => $scale) {
        $options[-$value] = $scale;
    }
    for ($i = 100; $i >= 1; $i--) {
        $options[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('turnitintooltwo/default_grade', get_string('overallgrade', 'turnitintooltwo'),
                       '', 100, $options));

    if (!empty($config->useanon)) {
        $settings->add(new admin_setting_configselect('turnitintooltwo/default_anon', get_string('anon', 'turnitintooltwo'),
                        '', 0, $ynoptions ));
    }

    if (!empty($config->transmatch)) {
        $settings->add(new admin_setting_configselect('turnitintooltwo/default_transmatch',
                                                        get_string('transmatch', 'turnitintooltwo'),
                                                        '', 0, $ynoptions ));
    }

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_studentreports',
                                                    get_string('studentreports', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_allownonor',
                                                    get_string('allownonor', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_allowlate',
                                                    get_string('allowlate', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $genoptions = array(0 => get_string('genimmediately1', 'turnitintooltwo'),
                        1 => get_string('genimmediately2', 'turnitintooltwo'),
                        2 => get_string('genduedate', 'turnitintooltwo'));
    $settings->add(new admin_setting_configselect('turnitintooltwo/default_reportgenspeed',
                                                    get_string('reportgenspeed', 'turnitintooltwo'),
                                                    '', 0, $genoptions ));

    $suboptions = array( 0 => get_string('norepository', 'turnitintooltwo'), 
                        1 => get_string('standardrepository', 'turnitintooltwo'));

    if (!isset($config->repositoryoption)) {
        $config->repositoryoption = 0;
    }

    switch ($config->repositoryoption) {
        case 0; // Standard options
            $settings->add(new admin_setting_configselect('turnitintooltwo/default_submitpapersto',
                                                    get_string('submitpapersto', 'turnitintooltwo'),
                                                    '', 1, $suboptions ));
            break;
        case 1; // Standard options + Allow Instituional Repository
            $suboptions[2] = get_string('institutionalrepository', 'turnitintooltwo');

            $settings->add(new admin_setting_configselect('turnitintooltwo/default_submitpapersto',
                                                    get_string('submitpapersto', 'turnitintooltwo'),
                                                    '', 1, $suboptions ));
            break;
    }

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_spapercheck',
                                                    get_string('spapercheck', 'turnitintooltwo'),
                                                    '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_internetcheck',
                                                    get_string('internetcheck', 'turnitintooltwo'),
                                                    '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_journalcheck',
                                                    get_string('journalcheck', 'turnitintooltwo'),
                                                    '', 1, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_institutioncheck',
                                                    get_string('institutionalchecksettings', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_excludebiblio',
                                                    get_string('excludebiblio', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_excludequoted',
                                                    get_string('excludequoted', 'turnitintooltwo'),
                                                    '', 0, $ynoptions ));

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_erater', get_string('erater', 'turnitintooltwo'),
                       '', 0, $ynoptions ));

    $handbookoptions = array(
                                1 => get_string('erater_handbook_advanced', 'turnitintooltwo'),
                                2 => get_string('erater_handbook_highschool', 'turnitintooltwo'),
                                3 => get_string('erater_handbook_middleschool', 'turnitintooltwo'),
                                4 => get_string('erater_handbook_elementary', 'turnitintooltwo'),
                                5 => get_string('erater_handbook_learners', 'turnitintooltwo')
                            );

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_erater_handbook',
                                                    get_string('erater_handbook', 'turnitintooltwo'),
                                                    '', 2, $handbookoptions ));

    $dictionaryoptions = array(
                                'en_US' => get_string('erater_dictionary_enus', 'turnitintooltwo'),
                                'en_GB' => get_string('erater_dictionary_engb', 'turnitintooltwo'),
                                'en' => get_string('erater_dictionary_en', 'turnitintooltwo')
                            );

    $settings->add(new admin_setting_configselect('turnitintooltwo/default_erater_dictionary',
                                                    get_string('erater_dictionary', 'turnitintooltwo'),
                                                    '', 'en_US', $dictionaryoptions ));

    $settings->add(new admin_setting_configcheckbox('turnitintooltwo/default_erater_spelling',
                                                    get_string('eraternoun', 'turnitintooltwo').' '.
                                                        get_string('erater_spelling', 'turnitintooltwo'), '', false));

    $settings->add(new admin_setting_configcheckbox('turnitintooltwo/default_erater_grammar',
                                                    get_string('eraternoun', 'turnitintooltwo').' '.
                                                        get_string('erater_grammar', 'turnitintooltwo'), '', false));

    $settings->add(new admin_setting_configcheckbox('turnitintooltwo/default_erater_usage',
                                                    get_string('eraternoun', 'turnitintooltwo').' '.
                                                        get_string('erater_usage', 'turnitintooltwo'),
                                                        '', false));

    $settings->add(new admin_setting_configcheckbox('turnitintooltwo/default_erater_mechanics',
                                                    get_string('eraternoun', 'turnitintooltwo').' '.
                                                        get_string('erater_mechanics', 'turnitintooltwo'),
                                                        '', false));

    $settings->add(new admin_setting_configcheckbox('turnitintooltwo/default_erater_style',
                                                    get_string('eraternoun', 'turnitintooltwo').' '.
                                                      get_string('erater_style', 'turnitintooltwo'),
                                                    '', false));
}
