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
 * This file keeps track of upgrades to the surveypro module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_surveypro_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_surveypro_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013090501) {

        // Surveypro savepoint reached.
        // upgrade_mod_savepoint(true, 2013090501, 'surveypro');
    }

    if ($oldversion < 2014051901) {

        // Define table surveypro_userdata to be renamed to surveypro_answer.
        $table = new xmldb_table('surveypro_userdata');

        // Launch rename table for surveypro_userdata.
        $dbman->rename_table($table, 'surveypro_answer');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2014051901, 'surveypro');
    }

    return true;
}
