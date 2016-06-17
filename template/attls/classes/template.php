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
 * Surveypro class to manage attls template
 *
 * @package   surveyprotemplate_attls
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/template/attls/lib.php');

/**
 * The class to manage attls template
 *
 * @package   surveyprotemplate_attls
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprotemplate_attls_template {

    /**
     * Apply template settings.
     *
     * @param string $tablename
     * @param object $record
     * @param object $config
     * @return array($tablename, $record)
     */
    public function apply_template_settings($tablename, $record, $config) {
        if ($tablename == 'surveyprofield_radiobutton') {
            $record->position = "$config->position";
        }

        if ($config->useritem == SURVEYPROTEMPLATE_ATTLSUSESELECT) {
            if ($record->plugin == 'radiobutton') {
                $record->plugin = 'select';
            }

            if ($tablename == 'surveyprofield_radiobutton') {
                $tablename = 'surveyprofield_select';
                unset($record->adjustment);
            }
        }

        return array($tablename, $record);
    }
}