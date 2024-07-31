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

/**
 * assign module data generator class
 *
 * @package mod_turnitintooltwo
 * @category test
 * @copyright 2024 Matthias Opitz <m.opitz@ucl.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_turnitintooltwo_generator extends testing_module_generator {

    /**
     * Create a new instance of the turnitintooltwo activity.
     *
     * @param array|stdClass|null $record
     * @param array|null $options
     * @return stdClass
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $defaultsettings = array(
            'turnitintooltwo_migration_status_header'   => 'Migration status test',
            'turnitintooltwo_header'                    => 'Test',
            'turnitintooltwo_accountconfig'             => 0,
            'accountid'                                 => '',
            'secretkey'                                 => '',
            'apiurl'                                    => 0,
            'turnitintooltwo_debugginglogs'             => 'Debbugging logs test',
            'enablediagnostic'                          => 0,
            'enableperformancelogs'                     => 0,
            'turnitintooltwo_accountsettings'           => 'Account settings test',
            'usegrademark'                              => 1,
            'enablepeermark'                            => 1,
            'usegrammar'                                => 0,
            'useanon'                                   => 0,
            'transmatch'                                => 0,
            'repositoryoption'                          => 0,
            'turnitintooltwo_miscsettings'              => 'Misc settings test',
            'agreement'                                 => '',
            'turnitintooltwo_privacy'                   => 'Privacy test',
            'enablepseudo'                              => 0,
            'pseudofirstname'                           => 'Anton',
            'pseudolastname'                            => 0,
            'lastnamegen'                               => 0,
            'pseudosalt'                                => '',
            'pseudoemaildomain'                         => '',
            'defaults'                                  => 'Defaults test',
            'default_type'                              => 0,
            'default_numparts'                          => 1,
            'default_anon'                              => 0,
            'default_transmatch'                        => 0,
            'default_studentreports'                    => 0,
            'default_gradedisplay'                      => 2,
            'default_allownonor'                        => 0,
            'default_allowlate'                         => 0,
            'default_reportgenspeed'                    => 0,
            'default_submitpapersto'                    => 1,
            'default_spapercheck'                       => 1,
            'default_internetcheck'                     => 1,
            'default_journalcheck'                      => 1,
            'default_institutioncheck'                  => 0,
            'default_excludebiblio'                     => 0,
            'default_excludequoted'                     => 0,
            'default_grammar'                           => 0,
            'default_grammar_handbook'                  => 2,
            'default_grammar_dictionary'                => 'en_US',
            'default_grammar_spelling'                  => 0,
            'default_grammar_grammar'                   => 0,
            'default_grammar_usage'                     => 0,
            'default_grammar_mechanics'                 => 0,
            'default_grammar_style'                     => 0,
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
