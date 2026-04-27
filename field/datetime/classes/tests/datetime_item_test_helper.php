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
 * Unit tests for surveyprofield_date item
 *
 * @package   surveyprofield_datetime
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_datetime\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass exposing protected methods for testing.
 */
class datetime_item_test_helper extends \surveyprofield_datetime\item {
    /**
     * Public wrapper for item_split_unix_time().
     *
     * @param int $unixtime
     * @return array
     */
    public function call_item_split_unix_time(int $unixtime): array {
        return $this->item_split_unix_time($unixtime);
    }

    /**
     * Public wrapper for get_strftime_format_keys().
     *
     * @return array
     */
    public function call_get_strftime_format_keys(): array {
        return $this->get_strftime_format_keys();
    }
}
