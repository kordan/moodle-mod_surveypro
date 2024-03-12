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
 * Keeps track of upgrades to the surveyproitem textarea
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion Version we are upgrading from
 * @return bool true
 */
function xmldb_surveyprofield_textarea_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {
        // Define key surveyproid (foreign) to be dropped form surveyprofield_textarea.
        $table = new xmldb_table('surveyprofield_textarea');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, ['surveyproid'], 'surveypro', ['id']);

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyprofield_textarea.
        $table = new xmldb_table('surveyprofield_textarea');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyprofield', 'textarea');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016062401) {
        // Define field trimonsave to be added to surveyprofield_textarea.
        $table = new xmldb_table('surveyprofield_textarea');
        $field = new xmldb_field('trimonsave', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Conditionally launch add field trimonsave.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Textarea savepoint reached.
        upgrade_plugin_savepoint(true, 2016062401, 'surveyprofield', 'textarea');
    }

    if ($oldversion < 2024020700) {
        // Reorder the field if the table.

        // Define field nexttrimonsave to be added to surveyprofield_character.
        $table = new xmldb_table('surveyprofield_textarea');
        $oldfield = new xmldb_field('trimonsave');
        $newfield = new xmldb_field('nexttrimonsave', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'areacols');

        // Step 1.
        // Conditionally launch add field nexttrimonsave.
        if (!$dbman->field_exists($table, $newfield)) {
            $dbman->add_field($table, $newfield);
        }

        // Step 2.
        // Copy whatever you find in old trimonsave field to new nexttrimonsave field.
        $sql = 'UPDATE {surveyprofield_textarea} SET nexttrimonsave = trimonsave WHERE id = id';
        $DB->execute($sql);

        // Step 3.
        // Conditionally launch drop old field trimonsave.
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->drop_field($table, $oldfield);
        }

        // Step 4.
        // Launch rename field nexttrimonsave to trimonsave.
        $dbman->rename_field($table, $newfield, 'trimonsave');

        // Character savepoint reached.
        upgrade_plugin_savepoint(true, 2024020700, 'surveyprofield', 'textarea');
    }

    if ($oldversion < 2024022701) {

        // Define field content to be dropped from surveyprofield_textarea.
        $table = new xmldb_table('surveyprofield_textarea');
        $field1 = new xmldb_field('content');
        $field2 = new xmldb_field('contentformat');

        // Copy the content of the dropping fields to the new corresponding fields in surveypro_item.
        $condition = $dbman->field_exists($table, $field1);
        $condition = $condition && $dbman->field_exists($table, $field2);
        if ($condition) {
            $sql = 'UPDATE {surveypro_item} i
                    JOIN {surveyprofield_textarea} f ON f.itemid = i.id
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
        upgrade_plugin_savepoint(true, 2024022701, 'surveyprofield', 'textarea');
    }

    return true;
}
