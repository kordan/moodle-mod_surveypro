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
 * Surveypro crontaskbase class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\task;

/**
 * The base class for items
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class crontaskbase extends \core\task\scheduled_task {
    /**
     * Provide the first part of the query needed from every cron tasks.
     *
     * @param string $surveyprofields - List of fields to select
     *
     * @return array
     */
    public static function get_sqltimewindow($surveyprofields) {
        $timenow = time();

        $fieldslist = implode(', ', $surveyprofields);
        $sql = 'SELECT '.$fieldslist.'
            FROM {surveypro} s
	        JOIN {course} c ON c.id = s.course
        WHERE (
                (c.startdate = 0 AND c.enddate = 0)
                    OR
                (c.startdate < ? AND c.enddate = 0)
                    OR
                (c.startdate = 0 AND c.enddate > ?)
                    OR
                (c.startdate < ? AND c.enddate > ?)
              )
            AND
              (
                ( (s.timeopen IS NULL OR s.timeopen = 0) AND (s.timeclose IS NULL OR s.timeclose = 0) )
                    OR
                ( (s.timeopen IS NOT NULL AND s.timeopen < ?) AND (s.timeclose IS NULL OR s.timeclose = 0) )
                    OR
                ( (s.timeopen IS NULL OR s.timeopen = 0) AND (s.timeclose IS NOT NULL AND s.timeclose > ?) )
                    OR
                ( (s.timeopen IS NOT NULL AND s.timeopen < ?) AND (s.timeclose IS NOT NULL AND s.timeclose > ?) )
              )
              ';
        $whereparams = array_fill(0, 8, $timenow);

        return [$sql, $whereparams];
    }
}
