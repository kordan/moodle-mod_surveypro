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
 * Keeps track of upgrades to the surveyproitem numeric
 *
 * @package   surveyprofield_numeric
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion Version we are upgrading from
 * @return bool true
 */
function xmldb_surveyprofield_numeric_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {
        // Define key surveyproid (foreign) to be dropped form surveyprofield_numeric.
        $table = new xmldb_table('surveyprofield_numeric');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, ['surveyproid'], 'surveypro', ['id']);

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyprofield_numeric.
        $table = new xmldb_table('surveyprofield_numeric');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Numeric savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyprofield', 'numeric');
    }

    if ($oldversion < 2024022701) {

        // Define field content to be dropped from surveyprofield_numeric.
        $table = new xmldb_table('surveyprofield_numeric');
        $field1 = new xmldb_field('content');
        $field2 = new xmldb_field('contentformat');

        // Copy the content of the dropping fields to the new corresponding fields in surveypro_item.
        $condition = $dbman->field_exists($table, $field1);
        $condition = $condition && $dbman->field_exists($table, $field2);
        if ($condition) {
            $sql = 'UPDATE {surveypro_item} i
                    JOIN {surveyprofield_numeric} f ON f.itemid = i.id
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

        // Numeric savepoint reached.
        upgrade_plugin_savepoint(true, 2024022701, 'surveyprofield', 'numeric');
    }

    if ($oldversion < 2024032800) {

        $table = new xmldb_table('surveyprofield_numeric');

        $fieldnames = ['required', 'indent', 'position', 'customnumber', 'hideinstructions', 'variable', 'extranote'];
        foreach ($fieldnames as $fieldname) {
            // Define field content to be dropped from surveyprofield_numeric.
            $field = new xmldb_field($fieldname);

            // Copy the content of the dropping fields to the new corresponding fields in surveypro_item.
            $condition = $dbman->field_exists($table, $field);
            if ($dbman->field_exists($table, $field)) {
                // Copy the content of the dieing column to the new corresponding column in surveypro_item.
                $sql = 'UPDATE {surveypro_item} i
                        JOIN {surveyprofield_numeric} f ON f.itemid = i.id
                        SET i.'.$fieldname.' = f.'.$fieldname;
                $DB->execute($sql);
            }

            // Conditionally launch drop field content.
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Numeric savepoint reached.
        upgrade_plugin_savepoint(true, 2024032800, 'surveyprofield', 'numeric');
    }

    return true;
}
