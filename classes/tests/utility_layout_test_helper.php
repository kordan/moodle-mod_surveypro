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
 * Unit test helper for utility_layout.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass exposing protected methods for testing.
 */
class utility_layout_test_helper extends \mod_surveypro\utility_layout {
    /**
     * Public wrapper for build_has_items_whereparams().
     *
     * @param int $formpage
     * @param string|null $type
     * @param bool $includehidden
     * @param bool $includereserved
     * @return array
     */
    public function call_build_has_items_whereparams(
        int $formpage,
        ?string $type,
        bool $includehidden,
        bool $includereserved
    ): array {
        return $this->build_has_items_whereparams($formpage, $type, $includehidden, $includereserved);
    }

    /**
     * Public wrapper for count_or_presence().
     *
     * @param string $table
     * @param array $whereparams
     * @param bool $returncount
     * @return int|bool
     */
    public function call_count_or_presence(string $table, array $whereparams, bool $returncount) {
        return $this->count_or_presence($table, $whereparams, $returncount);
    }
}
