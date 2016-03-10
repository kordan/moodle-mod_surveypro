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
 * @package    surveyprofield
 * @subpackage datetime
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

define('SURVEYPROFIELD_DATETIME_FORMAT', '[dd/mm/yyyy;hh:mm]');

/**
 * surveypro_datetime_check_time
 *
 * @param $hour
 * @param $minute
 * @return void
 */
function surveypro_datetime_check_time($hour, $minute) {
    if ($hour > -1 && $hour < 24 && $minute > -1 && $minute < 60) {
        return true;
    }
}
