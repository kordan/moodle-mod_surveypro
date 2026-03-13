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
 * Unit tests for view_responsesubmit
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Expose protected methods of itembase for testing via character item.
 */
class itembase_test_character_helper extends \surveyprofield_character\item {
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
     * Public wrapper for build_variablename_candidates().
     *
     * @param \stdClass $record
     * @return array
     */
    public function call_build_variablename_candidates(\stdClass $record): array {
        return $this->build_variablename_candidates($record);
    }
}
