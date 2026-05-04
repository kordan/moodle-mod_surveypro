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
 * Surveypro utility_page class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

/**
 * The utility dates class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_dates {
    /**
     * Provide the order of date elements starting from the string in langconfig.
     *
     * @param string $langkey A strftime format string e.g. 'strftimedate'.
     * @return array Ordered list of components e.g. ['day', 'month', 'year'].
     */
    public static function get_date_elements_order(string $langkey = 'strftimedate'): array {
        $format = get_string($langkey, 'langconfig');
        return self::get_order_from_format($format);
    }

    /**
     * Parses a strftime format string and returns the ordered date components found.
     * Exposed as public to allow direct unit testing without depending on get_string().
     *
     * @param string $format A strftime format string e.g. '%d %B %Y'.
     * @return array Ordered components e.g. ['day', 'month', 'year'].
     */
    public static function get_order_from_format(string $format): array {
        if (empty($format)) {
            return [];
        }

        $positions = [
            'day'   => strpos($format, '%d'),
            'month' => min(
                ($p = strpos($format, '%B')) !== false ? $p : PHP_INT_MAX,
                ($p = strpos($format, '%m')) !== false ? $p : PHP_INT_MAX
            ),
            'year'  => min(
                ($p = strpos($format, '%Y')) !== false ? $p : PHP_INT_MAX,
                ($p = strpos($format, '%y')) !== false ? $p : PHP_INT_MAX
            ),
        ];
        asort($positions);

        return array_keys(array_filter($positions, fn($p) => $p !== PHP_INT_MAX && $p !== false));
    }
}
