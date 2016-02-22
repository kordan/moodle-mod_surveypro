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
 * @package    surveyprofield
 * @subpackage multiselect
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true
 */
function xmldb_surveyprofield_multiselect_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {

        // Define key surveyproid (foreign) to be dropped form surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, array('surveyproid'), 'surveypro', array('id'));

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
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

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2014090502, 'surveyprofield', 'multiselect');
    }

    if ($oldversion < 2015123000) {

        // Define field required to be added to surveyprofield_multiselect.
        $table = new xmldb_table('surveyprofield_multiselect');
        $field = new xmldb_field('required', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'extranote');

        // Conditionally launch add field noanswerdefault.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2015123000, 'surveyprofield', 'multiselect');
    }

    return true;
}
