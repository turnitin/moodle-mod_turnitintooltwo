<?php
/**
 * @package   turnitintooltwo
 * @copyright 2010 iParadigms LLC
 */

function xmldb_turnitintooltwo_upgrade($oldversion) {

    global $CFG, $THEME, $DB, $OUTPUT;

    $result = true;

    // Do necessary DB upgrades here
    //function add_field($name, $type, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $enum=null, $enumvalues=null, $default=null, $previous=null)
    // Newer DB Man ($name, $type=null, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $default=null, $previous=null)
    if ($result && $oldversion < 2009071501) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table = new xmldb_table('turnitintooltwo_submissions');
            $field = new xmldb_field('submission_gmimaged', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'submission_grade');

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_submissions');
            $field = new XMLDBField('submission_gmimaged');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'submission_grade');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2009091401) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table = new xmldb_table('turnitintooltwo');
            $field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, null, '0', 'intro');

            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo');
            $field = new XMLDBField('introformat');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, null, '0', 'intro');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2009092901) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table1 = new xmldb_table('turnitintooltwo');
            $field1 = new xmldb_field('resubmit', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0', 'defaultdtpost');
            if ($dbman->field_exists($table1, $field1)) {
                $dbman->rename_field($table1, $field1, 'anon');
            }

            $table2 = new xmldb_table('turnitintooltwo_submissions');
            $field2 = new xmldb_field('submission_unanon', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, NULL, null, null, null, '0', 'submission_nmlastname');
            $field3 = new xmldb_field('submission_unanonreason', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'submission_unanon');
            $field4 = new xmldb_field('submission_nmuserid', XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null);

            if (!$dbman->field_exists($table2, $field2)) {
                $dbman->add_field($table2, $field2);
            }
            if (!$dbman->field_exists($table2, $field3)) {
                $dbman->add_field($table2, $field3);
            }
            $dbman->change_field_type($table2, $field4);
        } else {
            $table1 = new XMLDBTable('turnitintooltwo');
            $field1 = new XMLDBField('resubmit');
            $field1->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0', 'defaultdtpost');
            $result = $result && rename_field($table1, $field1, 'anon');

            $table2 = new XMLDBTable('turnitintooltwo_submissions');
            $field2 = new XMLDBField('submission_unanon');
            $field3 = new XMLDBField('submission_unanonreason');
            $field4 = new XMLDBField('submission_nmuserid');
            $field2->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, null, null, '0', 'submission_nmlastname');
            $result = $result && add_field($table2, $field2);
            $field3->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'submission_unanon');
            $result = $result && add_field($table2, $field3);
            $field4->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, null);
            $result = $result && change_field_type($table2, $field4);
        }
    }

    if ($result && $oldversion < 2009120501) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();

            // Launch add index userid
            $table = new xmldb_table('turnitintooltwo_submissions');
            $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }

            // Launch add index turnitintooltwoid
            $table = new xmldb_table('turnitintooltwo_submissions');
            $index = new xmldb_index('turnitintooltwoid', XMLDB_INDEX_NOTUNIQUE, array('turnitintooltwoid'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_submissions');

            // Launch add index userid
            $index = new XMLDBIndex('userid');
            $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('userid'));
            if (index_exists($table, $index)) {
                $result = $result && add_index($table, $index);
            }

            // Launch add index turnitintooltwoid
            $index = new XMLDBIndex('turnitintooltwoid');
            $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('turnitintooltwoid'));
            if (index_exists($table, $index)) {
                $result = $result && add_index($table, $index);
            }
        }
    }

    if ($result && $oldversion < 2010012201) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();

            // Fix fields where '' has been used
            $DB->execute("UPDATE {turnitintooltwo_submissions} SET submission_score = NULL WHERE submission_score = ''");
            $DB->execute("UPDATE {turnitintooltwo_submissions} SET submission_grade = NULL WHERE submission_grade = ''");
            $DB->execute("UPDATE {turnitintooltwo_submissions} SET submission_objectid = NULL WHERE submission_objectid = ''");

            $table = new xmldb_table('turnitintooltwo_submissions');
            $field1 = new xmldb_field('submission_score', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, NULL, null, null, null, null, 'submission_objectid');
            $field2 = new xmldb_field('submission_grade', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, NULL, null, null, null, null, 'submission_score');
            $field3 = new xmldb_field('submission_objectid', XMLDB_TYPE_INTEGER, '50', XMLDB_UNSIGNED, NULL, null, null, null, null, 'submission_filename');

            $dbman->change_field_type($table, $field1);
            $dbman->change_field_type($table, $field2);
            $dbman->change_field_type($table, $field3);

        } else {

            $table = new XMLDBTable('turnitintooltwo_submissions');
            $field1 = new XMLDBField('submission_score');
            $field2 = new XMLDBField('submission_grade');
            $field3 = new XMLDBField('submission_objectid');

            // Fix fields where '' has been used
            execute_sql("UPDATE {turnitintooltwo_submissions} SET submission_score = NULL WHERE submission_score = ''");
            execute_sql("UPDATE {turnitintooltwo_submissions} SET submission_grade = NULL WHERE submission_grade = ''");
            execute_sql("UPDATE {turnitintooltwo_submissions} SET submission_objectid = NULL WHERE submission_objectid = ''");

            $field1->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'submission_objectid');
            $result = $result && change_field_type($table, $field1);

            $field2->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, null, null, 'submission_score');
            $result = $result && change_field_type($table, $field2);

            $field3->setAttributes(XMLDB_TYPE_INTEGER, '50', XMLDB_UNSIGNED, null, null, null, null, null, 'submission_filename');
            $result = $result && change_field_type($table, $field3);
        }
    }

    if ($result && $oldversion < 2010021901) {
        require_once($CFG->dirroot."/mod/turnitintooltwo/lib.php");
        $loaderbar=NULL;
        if (turnitintooltwo_check_config()) {
            $tii = new turnitintooltwo_commclass("","FID99","Turnitin","fid99@turnitin.com","2",$loaderbar,false);
            $tii->migrateSRCData();
            if (is_callable(array($DB,'get_manager'))) {
                if (!$tii->getRerror()) {
                    echo $OUTPUT->notification("Migrating Turnitin SRC Namespace: ".$tii->getRmessage(), 'notifysuccess');
                } else {
                    echo $OUTPUT->notification("Migrating Turnitin SRC Namespace: ".$tii->getRmessage());
                }
            } else {
                if (!$tii->getRerror()) {
                    notify($tii->getRmessage(), 'notifysuccess');
                } else {
                    notify($tii->getRmessage());
                }
            }
            $result = $result && !$tii->getRerror();
        }
    }

    if ($result && $oldversion < 2010090701) {
        $table='config';
        $dataobject->name='turnitin_userepository';
        $dataobject->value=1;
        if (is_callable(array($DB,'get_manager'))) {
            $DB->insert_record($table, $dataobject);
        } else {
            insert_record($table, $dataobject);
        }
    }

    if ($result && $oldversion < 2010102601) {
        if (is_callable(array($DB,'get_manager'))) {

            $dbman = $DB->get_manager();
            // Change the field size from 50 to 20 to add oracle compatibility
            $table1 = new xmldb_table('turnitintooltwo_submissions');
            $field1 = new xmldb_field('submission_objectid', XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, NULL, null, null, 'submission_filename');
            $field2 = new xmldb_field('submission_nmuserid', XMLDB_TYPE_CHAR, '100', XMLDB_UNSIGNED, NULL, null, null, 'submission_parent');

            $dbman->change_field_type($table1, $field1);
            $dbman->change_field_type($table1, $field2);

            // Add the exclude small matches db fields
            $table2 = new xmldb_table('turnitintooltwo');
            $field3 = new xmldb_field('excludebiblio', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'shownonsubmission');
            $field4 = new xmldb_field('excludequoted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'excludebiblio');
            $field5 = new xmldb_field('excludevalue', XMLDB_TYPE_INTEGER, '9', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'excludequoted');
            $field6 = new xmldb_field('excludetype', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 1, 'excludevalue');
            $field7 = new xmldb_field('perpage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 25, 'excludetype');

            if (!$dbman->field_exists($table2, $field3)) {
                $dbman->add_field($table2, $field3);
            }
            if (!$dbman->field_exists($table2, $field4)) {
                $dbman->add_field($table2, $field4);
            }
            if (!$dbman->field_exists($table2, $field5)) {
                $dbman->add_field($table2, $field5);
            }
            if (!$dbman->field_exists($table2, $field6)) {
                $dbman->add_field($table2, $field6);
            }
            if (!$dbman->field_exists($table2, $field7)) {
                $dbman->add_field($table2, $field7);
            }

        } else {

            // Change the field size from 50 to 20 to add oracle compatibility
            $table1 = new XMLDBTable('turnitintooltwo_submissions');
            $field1 = new XMLDBField('submission_objectid');
            $field2 = new XMLDBField('submission_nmuserid');
            $field1->setAttributes(XMLDB_TYPE_INTEGER, '20', XMLDB_UNSIGNED, null, null, null, null, null, 'submission_filename');
            $field2->setAttributes(XMLDB_TYPE_CHAR, '100', XMLDB_UNSIGNED, null, null, null, null, null, 'submission_parent');
            $result = $result && change_field_type($table1, $field1);
            $result = $result && change_field_type($table1, $field2);

            // Add the exclude small matches db fields
            $table2 = new XMLDBTable('turnitintooltwo');
            $field3 = new XMLDBField('excludebiblio');
            $field4 = new XMLDBField('excludequoted');
            $field5 = new XMLDBField('excludevalue');
            $field6 = new XMLDBField('excludetype');
            $field7 = new XMLDBField('perpage');

            $field3->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'shownonsubmission');
            $result = $result && add_field($table2, $field3);

            $field4->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'excludebiblio');
            $result = $result && add_field($table2, $field4);

            $field5->setAttributes(XMLDB_TYPE_INTEGER, '9', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 0, 'excludequoted');
            $result = $result && add_field($table2, $field5);

            $field6->setAttributes(XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 1, 'excludevalue');
            $result = $result && add_field($table2, $field6);

            $field7->setAttributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, 25, 'excludetype');
            $result = $result && add_field($table2, $field7);
        }
    }

    if ($result && $oldversion < 2011030101) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();

            $table = new xmldb_table('turnitintooltwo');
            $field1 = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'name');

            $dbman->change_field_type($table, $field1);
        } else {
            $table = new XMLDBTable('turnitintooltwo');
            $field1 = new XMLDBField('grade');

            $field1->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'name');
            $result = $result && change_field_type($table, $field1);
        }
    }

    if ($result && $oldversion < 2011081701) {
    	if (is_callable(array($DB,'get_manager'))) {
    		$dbman = $DB->get_manager();

            // Add erater fields
            $table = new xmldb_table('turnitintooltwo');
            $field1 = new xmldb_field('erater', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'perpage');
            $field2 = new xmldb_field('erater_handbook', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater');
            $field3 = new xmldb_field('erater_dictionary', XMLDB_TYPE_CHAR, '10', XMLDB_UNSIGNED, null, null, null, 'erater_handbook');
            $field4 = new xmldb_field('erater_spelling', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_dictionary');
            $field5 = new xmldb_field('erater_grammar', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_spelling');
            $field6 = new xmldb_field('erater_usage', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_grammar');
            $field7 = new xmldb_field('erater_mechanics', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_usage');
            $field8 = new xmldb_field('erater_style', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_mechanics');

            if (!$dbman->field_exists($table, $field1)) {
            	$dbman->add_field($table, $field1);
            }
            if (!$dbman->field_exists($table, $field2)) {
            	$dbman->add_field($table, $field2);
            }
            if (!$dbman->field_exists($table, $field3)) {
            	$dbman->add_field($table, $field3);
            }
            if (!$dbman->field_exists($table, $field4)) {
            	$dbman->add_field($table, $field4);
            }
            if (!$dbman->field_exists($table, $field5)) {
            	$dbman->add_field($table, $field5);
            }
            if (!$dbman->field_exists($table, $field6)) {
            	$dbman->add_field($table, $field6);
            }
            if (!$dbman->field_exists($table, $field7)) {
            	$dbman->add_field($table, $field7);
            }
            if (!$dbman->field_exists($table, $field8)) {
            	$dbman->add_field($table, $field8);
            }

    	} else {
    		// Add erater fields
    		$table = new XMLDBTable('turnitintooltwo');
    		$field1 = new XMLDBField('erater');
    		$field2 = new XMLDBField('erater_handbook');
    		$field3 = new XMLDBField('erater_dictionary');
    		$field4 = new XMLDBField('erater_spelling');
    		$field5 = new XMLDBField('erater_grammar');
    		$field6 = new XMLDBField('erater_usage');
    		$field7 = new XMLDBField('erater_mechanics');
    		$field8 = new XMLDBField('erater_style');

    		$field1->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'perpage');
    		$result = $result && add_field($table, $field1);

    		$field2->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater');
    		$result = $result && add_field($table, $field2);

    		$field3->setAttributes(XMLDB_TYPE_TEXT, '10', null, null, null, null, null, null, 'erater_handbook');
    		$result = $result && add_field($table, $field3);

    		$field4->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater_dictionary');
    		$result = $result && add_field($table, $field4);

    		$field5->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater_spelling');
    		$result = $result && add_field($table, $field5);

    		$field6->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater_grammar');
    		$result = $result && add_field($table, $field6);

    		$field7->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater_usage');
    		$result = $result && add_field($table, $field7);

    		$field8->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'erater_mechanics');
    		$result = $result && add_field($table, $field8);

    	}
    }

    if ($result && $oldversion < 2012030501) {
    	if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table = new xmldb_table('turnitintooltwo_users');
            // Launch add index userid
            $index = new xmldb_index('userid', XMLDB_INDEX_UNIQUE, array('userid'));
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_users');
            // Launch add index userid
            $index = new XMLDBIndex('userid');
            $index->setAttributes(XMLDB_INDEX_UNIQUE, array('userid'));
            if (index_exists($table, $index)) {
                $result = $result && add_index($table, $index);
            }
        }
    }

    if ($result && $oldversion < 2012042701) {
    	if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table = new xmldb_table('turnitintooltwo_users');
            $field = new xmldb_field('turnitin_utp', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'turnitin_uid');
            if (!$dbman->field_exists($table, $field)) {
            	$dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_users');
            $field = new XMLDBField('turnitin_utp');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, 0, 'turnitin_uid');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2012081301) {
    	if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();
            $table = new xmldb_table('turnitintooltwo_submissions');
            $field = new xmldb_field('submission_transmatch', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'submission_unanonreason');
            if (!$dbman->field_exists($table, $field)) {
            	$dbman->add_field($table, $field);
            }
            $table = new xmldb_table('turnitintooltwo');
            $field = new xmldb_field('transmatch', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, 0, 'erater_style');
            if (!$dbman->field_exists($table, $field)) {
            	$dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_submissions');
            $field = new XMLDBField('submission_transmatch');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, 0, 'submission_unanonreason');
            $result = $result && add_field($table, $field);
            $table = new XMLDBTable('turnitintooltwo');
            $field = new XMLDBField('transmatch');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, 0, 'erater_style');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2013022201) {
        if (is_callable(array($DB, 'get_manager'))) {
            $dbman = $DB->get_manager();

            $table = new xmldb_table('turnitintooltwo');
            $field = new xmldb_field('rubric', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'transmatch');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $table = new xmldb_table('turnitintooltwo_users');
            $field1 = new xmldb_field('instructor_defaults', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, 'turnitin_utp');
            $field2 = new xmldb_field('instructor_rubrics', XMLDB_TYPE_TEXT, 'medium', XMLDB_UNSIGNED, null, null, null, 'instructor_defaults');

            if (!$dbman->field_exists($table, $field1)) {
            	$dbman->add_field($table, $field1);
            }
            if (!$dbman->field_exists($table, $field2)) {
                $dbman->add_field($table, $field2);
            }

            $table = new xmldb_table('turnitintooltwo_courses');
            $field = new xmldb_field('course_type', XMLDB_TYPE_CHAR, '20', XMLDB_UNSIGNED, null, null, 'TT', 'turnitin_cid');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo');
            $field = new XMLDBField('rubric');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, null, null, null, null, null, 'transmatch');
            $result = $result && add_field($table, $field);

            $table = new XMLDBTable('turnitintooltwo_users');
            $field1 = new XMLDBField('instructor_defaults');
            $field2 = new XMLDBField('instructor_rubrics');

            $field1->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'turnitin_utp');
            $result = $result && add_field($table, $field1);

            $field2->setAttributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, null, null, 'instructor_defaults');
            $result = $result && add_field($table, $field2);

            $table = new XMLDBTable('turnitintooltwo_courses');
            $field = new XMLDBField('course_type');
            $field->setAttributes(XMLDB_TYPE_CHAR, '20', null, null, null, null, null, 'TT', 'turnitin_cid');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2013051601) {
        $dbman = $DB->get_manager();

        // Define table turnitintooltwo_peermarks to be created
        $table = new xmldb_table('turnitintooltwo_peermarks');

        // Adding fields to table turnitintooltwo_peermarks
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('parent_tii_assign_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('tiiassignid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dtstart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dtdue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('dtpost', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxmarks', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('instructions', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);
        $table->add_field('distributed_reviews', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('selected_reviews', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('self_review', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('non_submitters_review', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table turnitintooltwo_peermarks
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create table turnitintooltwo_peermarks
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($result && $oldversion < 2013070201) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();

            $table = new xmldb_table('turnitintooltwo_parts');
            $field = new xmldb_field('migrated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'deleted');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo_parts');
            $field = new XMLDBField('migrated');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0, 'deleted');
            $result = $result && add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2014012401) {
        if (is_callable(array($DB,'get_manager'))) {
            $dbman = $DB->get_manager();

            $table = new xmldb_table('turnitintooltwo');
            $field = new xmldb_field('allownonor', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'rubric');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $table = new xmldb_table('turnitintooltwo_submissions');
            $field1 = new xmldb_field('submission_acceptnothing', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'submission_transmatch');
            $field2 = new xmldb_field('submission_orcapable', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'submission_acceptnothing');
            if (!$dbman->field_exists($table, $field1)) {
                $dbman->add_field($table, $field1);
            }
            if (!$dbman->field_exists($table, $field2)) {
                $dbman->add_field($table, $field2);
            }
        } else {
            $table = new XMLDBTable('turnitintooltwo');
            $field = new XMLDBField('allownonor');
            $field->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0, 'rubric');
            $result = $result && add_field($table, $field);
            $table = new XMLDBTable('turnitintooltwo_submissions');
            $field1 = new XMLDBField('submission_acceptnothing');
            $field1->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0, 'submission_transmatch');
            $result = $result && add_field($table, $field1);
            $field2 = new XMLDBField('submission_orcapable');
            $field2->setAttributes(XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null, 0, 'submission_acceptnothing');
            $result = $result && add_field($table, $field2);
        }
    }

    return $result;
}

