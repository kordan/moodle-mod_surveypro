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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/db/upgradelib.php');

/**
 * xmldb_surveypro_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_surveypro_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Moodle v2.6.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2014051901) {

        // Define table surveypro_userdata to be renamed to surveypro_answer.
        $table = new xmldb_table('surveypro_userdata');

        // Launch rename table for surveypro_userdata.
        $dbman->rename_table($table, 'surveypro_answer');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2014051901, 'surveypro');
    }

    // Moodle v2.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015060401) {

        // Define field verified to be added to surveypro_answer.
        $table = new xmldb_table('surveypro_answer');
        $field = new xmldb_field('verified', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'itemid');

        // Conditionally launch add field verified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2015060401, 'surveypro');
    }

    if ($oldversion < 2015070201) {
        $DB->set_field('surveypro_answer', 'verified', '1');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2015070201, 'surveypro');
    }

    if ($oldversion < 2015090901) {
        $oldcontents = array('__invItat10n__', '__n0__Answer__', '__1gn0rE__me__');
        $newcontents = array('@@_INVITE_@@', '@@_NOANSW_@@', '@@_IGNORE_@@');

        $sql = 'UPDATE {surveypro_answer} SET content = :newcontent WHERE content = '.$DB->sql_compare_text(':oldcontent');
        foreach ($oldcontents as $k => $oldcontent) {
            $DB->execute($sql, array('oldcontent' => $oldcontent, 'newcontent' => $newcontents[$k]));
        }

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2015090901, 'surveypro');
    }

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2015111904) {
        // Move settings to use plugintype prefix.
        $settings = $DB->get_records('config_plugins', array('plugin' => 'surveypro'));

        foreach ($settings as $setting) {
            set_config($setting->name, $setting->value, 'mod_surveypro');
            unset_config($setting->name, 'surveypro');
        }

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2015111904, 'surveypro');
    }

    if ($oldversion < 2015112301) {
        unset_config('requiremodintro', 'mod_surveypro');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2015112301, 'surveypro');
    }

    if ($oldversion < 2016031501) {

        // Rename field advanced on table surveypro_item to reserved.
        $table = new xmldb_table('surveypro_item');
        $field = new xmldb_field('advanced', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'insearchform');

        // Launch rename field advanced.
        $dbman->rename_field($table, $field, 'reserved');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016031501, 'surveypro');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016061705) {
        uninstall_plugin('surveyproreport', 'attachments_overview');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016061705, 'surveypro');
    }

    if ($oldversion < 2016070101) {
        uninstall_plugin('surveyproreport', 'count');
        uninstall_plugin('surveyproreport', 'submitting');
        uninstall_plugin('surveyproreport', 'missing');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016070101, 'surveypro');
    }

    if ($oldversion < 2016100601) {
        $where = $DB->sql_compare_text('content').' = :content';
        $DB->delete_records_select('surveypro_answer', $where, array('content' => '@@_ANINDB_@@'));

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016100601, 'surveypro');
    }

    if ($oldversion < 2016101700) {
        // Changing the default of field contentformat on table surveypro_answer to null.
        $table = new xmldb_table('surveypro_answer');
        $field = new xmldb_field('content', XMLDB_TYPE_TEXT, null, null, null, null, null, 'verified');

        // Launch change of default for field contentformat.
        $dbman->change_field_default($table, $field);

        // Changing the default of field contentformat on table surveypro_answer to drop it.
        $table = new xmldb_table('surveypro_answer');
        $field = new xmldb_field('contentformat', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'content');

        // Launch change of default for field contentformat.
        $dbman->change_field_default($table, $field);

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016101700, 'surveypro');
    }

    if ($oldversion < 2016102500) {
        // Delete answers that are not supposed to be in the database
        // because answers to NOT PERMITTED child items.
        surveypro_delete_supposed_blank_answers();

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2016102500, 'surveypro');
    }

    if ($oldversion < 2018020200) {
        global $DB;

        if ($surveypros = $DB->get_records('surveypro', null, 'id', 'id, course')) {
            foreach ($surveypros as $surveypro) {
                surveypro_old_restore_fix($surveypro);
            }
        }

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2018020200, 'surveypro');
    }

    if ($oldversion < 2018021200) {

        // Define field keepinprogress to be added to surveypro.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('keepinprogress', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'saveresume');

        // Conditionally launch add field keepinprogress.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2018021200, 'surveypro');
    }

    if ($oldversion < 2018022801) {

        // Define field notifycontent to be added to surveypro.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifycontent', XMLDB_TYPE_TEXT, null, null, null, null, null, 'notifymore');

        // Conditionally launch add field notifycontent.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field notifycontentformat to be added to surveypro.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifycontentformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'notifycontent');

        // Conditionally launch add field notifycontentformat.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->set_field('surveypro', 'notifycontentformat', '1');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2018022801, 'surveypro');
    }

    if ($oldversion < 2018032002) {

        // Rename field thankshtmlformat on table surveypro to thankspageformat.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('thankshtmlformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'thankshtml');

        // Launch rename field thankshtmlformat.
        $dbman->rename_field($table, $field, 'thankspageformat');

        // Rename field thankshtml on table surveypro to thankspage.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('thankshtml', XMLDB_TYPE_TEXT, null, null, null, null, null, 'notifycontentformat');

        // Launch rename field thankshtml.
        $dbman->rename_field($table, $field, 'thankspage');

        // Rename field notifycontent on table surveypro to NEWNAMEGOESHERE.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifycontentformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'notifycontent');

        // Launch rename field notifycontent.
        $dbman->rename_field($table, $field, 'mailcontentformat');

        // Rename field notifycontent on table surveypro to NEWNAMEGOESHERE.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifycontent', XMLDB_TYPE_TEXT, null, null, null, null, null, 'notifymore');

        // Launch rename field notifycontent.
        $dbman->rename_field($table, $field, 'mailcontent');

        // Rename field notifymore on table surveypro to NEWNAMEGOESHERE.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifymore', XMLDB_TYPE_TEXT, null, null, null, null, null, 'notifyrole');

        // Launch rename field notifymore.
        $dbman->rename_field($table, $field, 'mailextraaddresses');

        // Rename field notifyrole on table surveypro to NEWNAMEGOESHERE.
        $table = new xmldb_table('surveypro');
        $field = new xmldb_field('notifyrole', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'maxentries');

        // Launch rename field notifyrole.
        $dbman->rename_field($table, $field, 'mailroles');

        // Surveypro savepoint reached.
        upgrade_mod_savepoint(true, 2018032002, 'surveypro');
    }

    return true;
}
