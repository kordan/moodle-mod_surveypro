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
 * Keeps track of upgrades to the surveyproitem fileupload
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion Version we are upgrading from
 * @return bool true
 */
function xmldb_surveyprofield_fileupload_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {

        // Define key surveyproid (foreign) to be dropped form surveyprofield_fileupload.
        $table = new xmldb_table('surveyprofield_fileupload');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, array('surveyproid'), 'surveypro', array('id'));

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyprofield_fileupload.
        $table = new xmldb_table('surveyprofield_fileupload');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyprofield', 'fileupload');
    }

    if ($oldversion < 2016072001) {

        // Define field hideinstructions to be added to surveyprofield_fileupload.
        $table = new xmldb_table('surveyprofield_fileupload');
        $field = new xmldb_field('hideinstructions', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'required');

        // Conditionally launch add field hideinstructions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Fileupload savepoint reached.
        upgrade_plugin_savepoint(true, 2016072001, 'surveyprofield', 'fileupload');
    }

    // Moodle core added the list of allowed extensions to fileupload elements, so my instructions are no longer needed.
    if ($oldversion < 2018042401) {

        // Define field hideinstructions to be dropped from surveyprofield_fileupload.
        $table = new xmldb_table('surveyprofield_fileupload');
        $field = new xmldb_field('hideinstructions');

        // Conditionally launch drop field hideinstructions.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2018042401, 'surveyprofield', 'fileupload');
    }

    if ($oldversion < 2018060501) {

        // Changing precision of field filetypes on table surveyprofield_fileupload to (64).
        $table = new xmldb_table('surveyprofield_fileupload');
        $field = new xmldb_field('filetypes', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'maxbytes');

        // Launch change of precision for field filetypes.
        $dbman->change_field_precision($table, $field);

        // Fileupload savepoint reached.
        upgrade_plugin_savepoint(true, 2018060501, 'surveyprofield', 'fileupload');
    }

    return true;
}
