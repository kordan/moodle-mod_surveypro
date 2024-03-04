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
 * Keeps track of upgrades to the surveyproitem label
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs upgrade of the database structure and data
 *
 * @param int $oldversion Version we are upgrading from
 * @return bool true
 */
function xmldb_surveyproformat_label_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014051701) {
        // Define key surveyproid (foreign) to be dropped form surveyproformat_label.
        $table = new xmldb_table('surveyproformat_label');
        $key = new xmldb_key('surveyproid', XMLDB_KEY_FOREIGN, ['surveyproid'], 'surveypro', ['id']);

        // Launch drop key surveyproid.
        $dbman->drop_key($table, $key);

        // Define field surveyproid to be dropped from surveyproformat_label.
        $table = new xmldb_table('surveyproformat_label');
        $field = new xmldb_field('surveyproid');

        // Conditionally launch drop field surveyproid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2014051701, 'surveyproformat', 'label');
    }

    if ($oldversion < 2024011101) {
        // Drop any parent child relation in EACH past surveypro.
        // I am confident I woll not find any.
        $sql = 'UPDATE {surveypro_item}
                SET parentid = :parentid, parentvalue = :parentvalue
                WHERE plugin = :plugin';
        $whereparams = ['parentid' => null, 'parentvalue' => null, 'plugin' => 'label'];
        $DB->execute($sql, $whereparams);

        // Surveypro savepoint reached.
        upgrade_plugin_savepoint(true, 2024011101, 'surveyproformat', 'label');
    }

    return true;
}
