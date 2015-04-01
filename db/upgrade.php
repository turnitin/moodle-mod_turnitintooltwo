<?php
/**
 * @package   turnitintooltwo
 * @copyright 2010 iParadigms LLC
 */

function xmldb_turnitintooltwo_upgrade($oldversion) {

    global $CFG, $THEME, $DB, $OUTPUT;

    $result = true;
    $dbman = $DB->get_manager();

    // Do necessary DB upgrades here
    // Newer DB Man field ($name, $type=null, $precision=null, $unsigned=null, $notnull=null, $sequence=null, $default=null, $previous=null)
    if ($result && $oldversion < 2014012401) {
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
    }

    if ($result && $oldversion < 2014012404) {
        $table = new xmldb_table('turnitintooltwo_users');
        $field = new xmldb_field('user_agreement_accepted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'instructor_rubrics');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2014012405) {
        $table = new xmldb_table('turnitintooltwo');
        $field = new xmldb_field('submitted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, null, null, 0, 'anon');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add new indexes to tables
        $table = new xmldb_table('turnitintooltwo_parts');
        $index = new xmldb_index('turnitintooltwoid', XMLDB_INDEX_NOTUNIQUE, array('turnitintooltwoid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('tiiassignid', XMLDB_INDEX_NOTUNIQUE, array('tiiassignid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('turnitintooltwo_courses');
        $index = new xmldb_index('courseid-course_type', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'course_type'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $table = new xmldb_table('turnitintooltwo_peermarks');
        $index = new xmldb_index('parent_tii_assign_id', XMLDB_INDEX_NOTUNIQUE, array('parent_tii_assign_id'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        $index = new xmldb_index('tiiassignid', XMLDB_INDEX_NOTUNIQUE, array('tiiassignid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    if ($result && $oldversion < 2014012412) {
        $table = new xmldb_table('turnitintooltwo');
        $field = new xmldb_field('needs_updating', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'allownonor');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($result && $oldversion < 2015040101) {
        $table = new xmldb_table('turnitintooltwo_parts');
        $field = new xmldb_field('unanon', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'migrated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('submitted', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'unanon');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('turnitintooltwo_submissions');
        $index = new xmldb_index('submission_objectid', XMLDB_INDEX_NOTUNIQUE, array('submission_objectid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    return $result;
}