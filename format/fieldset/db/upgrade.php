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
 * Keeps track of upgrades to the surveyproitem fieldset
 *
 * @package   surveyproformat_fieldset
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
function xmldb_surveyproformat_fieldset_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {

        // Define key surveyproid (foreign) to be dropped form surveyproformat_fieldset.
        $table = new xmldb_table('surveyproformat_fieldset');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, array('surveyproid'), 'surveypro', array('id'));

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyproformat_fieldset.
        $table = new xmldb_table('surveyproformat_fieldset');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyproformat', 'fieldset');
    }

    if ($oldversion < 2018091301) {

        // Define field defaultstatus to be added to surveyproformat_fieldset.
        $table = new xmldb_table('surveyproformat_fieldset');
        $field = new xmldb_field('defaultstatus', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '2', 'content');

        // Conditionally launch add field defaultstatus.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Fieldset savepoint reached.
        upgrade_plugin_savepoint(true, 2018091301, 'surveyproformat', 'fieldset');
    }

    return true;
}

