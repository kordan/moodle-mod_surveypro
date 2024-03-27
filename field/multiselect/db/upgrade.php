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
 * Keeps track of upgrades to the surveyproitem multiselect
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion Version we are upgrading from
 * @return bool true
 */
function xmldb_surveyprofield_multiselect_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {
        // Define key surveyproid (foreign) to be dropped form surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, ['surveyproid'], 'surveypro', ['id']);

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2014090502) {
        // Define field hideinstructions to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('hideinstructions', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Conditionally launch add field hideinstructions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field minimumrequired to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('minimumrequired', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'downloadformat');

        // Conditionally launch add field minimumrequired.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field surveyproid to be dropped from surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('required');

        // Conditionally launch drop field required.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2014090502, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2015123000) {
        // Define field required to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('required', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'extranote');

        // Conditionally launch add field required.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2015123000, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2017062301) {
        // Define field noanswerdefault to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('noanswerdefault', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'defaultvalue');

        // Conditionally launch add field noanswerdefault.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2017062301, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2018091301) {
        // Define field maximumrequired to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('maximumrequired', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'minimumrequired');

        // Conditionally launch add field maximumrequired.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2018091301, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2023111401) {
        // Changing the default of field noanswerdefault on table surveyprofield_multiselect to 0.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('noanswerdefault', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'defaultvalue');

        // Launch change of default for field noanswerdefault.
        $dbman->change_field_default($table, $field);

        // Update old records still having noanswerdefault == 2.
        $sql = 'UPDATE {surveyprofield_multiselect}
                SET noanswerdefault = :newnoanswerdefault
                WHERE noanswerdefault = :oldnoanswerdefault';
        $whereparams = ['newnoanswerdefault' => 1, 'oldnoanswerdefault' => 2];

        $DB->execute($sql, $whereparams);

        // Multiselect savepoint reached.
        upgrade_plugin_savepoint(true, 2023111401, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2024022701) {

        // Define field content to be dropped from surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field1 = new xmldb_field('content');
        $field2 = new xmldb_field('contentformat');

        // Copy the content of the dropping fields to the new corresponding fields in surveypro_item.
        $condition = $dbman->field_exists($table, $field1);
        $condition = $condition && $dbman->field_exists($table, $field2);
        if ($condition) {
            $sql = 'UPDATE {surveypro_item} i
                    JOIN {surveyprofield_multiselect} f ON f.itemid = i.id
                    SET i.content = f.content,
                        i.contentformat = f.contentformat';
            $DB->execute($sql);
        }

        // Conditionally launch drop field content.
        if ($dbman->field_exists($table, $field1)) {
            $dbman->drop_field($table, $field1);
        }

        // Conditionally launch drop field content.
        if ($dbman->field_exists($table, $field2)) {
            $dbman->drop_field($table, $field2);
        }

        // Age savepoint reached.
        upgrade_plugin_savepoint(true, 2024022701, 'surveyprofield', 'multiselect');
    }

    return true;
}
