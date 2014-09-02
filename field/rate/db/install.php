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
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php
 *
 * @package    surveyprofield
 * @subpackage rate
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 */
function xmldb_surveyprofield_rate_install() {
    // global $CFG, $DB;
    // require_once(dirname(__FILE__) . '/upgradelib.php');
}

/**
 * Post installation procedure recovery
 */
function xmldb_surveyprofield_rate_install_recovery() {
    // global $CFG, $DB;
    // require_once(dirname(__FILE__) . '/upgradelib.php');

    // continue upgrading from old surveypro 1.x if needed
    // surveyprofield_rate_upgrade_legacy();
}
