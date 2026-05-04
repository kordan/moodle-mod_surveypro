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
 * Unit tests for utility_dates class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_dates;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for utility_dates::get_order_from_format().
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(utility_dates::class)]
#[CoversMethod(utility_dates::class, 'get_order_from_format')]
final class utility_dates_test extends \advanced_testcase
{
    /**
     * Data provider for test_get_date_elements_order.
     *
     * Each entry: [format string, langkey, expected order]
     *
     * @return array
     */
    public static function get_date_elements_order_provider(): array {
        return [
            'italian date: day month year' => [
                '%d %B %Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'english date: month day year' => [
                '%B %d, %Y', 'strftimedate', ['month', 'day', 'year'],
            ],
            'iso date: year month day' => [
                '%Y-%m-%d', 'strftimedate', ['year', 'month', 'day'],
            ],
            // strftimedateshort cases (recurrence: day + month only).
            'italian recurrence: day month' => [
                '%d %B', 'strftimedateshort', ['day', 'month'],
            ],
            'english recurrence: month day' => [
                '%B %d', 'strftimedateshort', ['month', 'day'],
            ],
            // strftimemonthyear cases (shortdate: month + year only).
            'italian shortdate: month year' => [
                '%B %Y', 'strftimemonthyear', ['month', 'year'],
            ],
            'english shortdate: year month' => [
                '%Y %B', 'strftimemonthyear', ['year', 'month'],
            ],
            // strftimedate cases with unusual separators
            'format with slashes: day month year' => [
                '%d/%m/%Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'format with dots: day month year' => [
                '%d.%m.%Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'format with dashes: year month day' => [
                '%Y-%m-%d', 'strftimedate', ['year', 'month', 'day'],
            ],
            'components adjacent without separator' => [
                '%d%m%Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'components adjacent reverse' => [
                '%Y%m%d', 'strftimedate', ['year', 'month', 'day'],
            ],
            // strftimedate cases with short year %y
            'short year: day month short year' => [
                '%d %m %y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'short year: month day short year' => [
                '%m/%d/%y', 'strftimedate', ['month', 'day', 'year'],
            ],
            'short year: month full name then short year' => [
                '%B %d %y', 'strftimedate', ['month', 'day', 'year'],
            ],
            // strftimedate cases with %B and %m both
            'both %B and %m present: %B first' => [
                '%B %m %Y', 'strftimedate', ['month', 'year'],
            ],
            'both %B and %m present: %m first' => [
                '%m %B %Y', 'strftimedate', ['month', 'year'],
            ],
            // strftimedate cases with only one component
            'only day present' => [
                '%d', 'strftimedate', ['day'],
            ],
            'only month full name present' => [
                '%B', 'strftimedate', ['month'],
            ],
            'only month numeric present' => [
                '%m', 'strftimedate', ['month'],
            ],
            'only year full present' => [
                '%Y', 'strftimedate', ['year'],
            ],
            'only year short present' => [
                '%y', 'strftimedate', ['year'],
            ],
            // strftimedate strange cases
            'empty format string' => [
                '', 'strftimedate', [],
            ],
            'format with no known directives' => [
                '%H:%M:%S', 'strftimedate', [],
            ],
            'format with noise only: no percent signs' => [
                'dd/mm/yyyy', 'strftimedate', [],
            ],
            'extra text around format' => [
                'day: %d, month: %m, year: %Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'extra text reverse order' => [
                'year: %Y, month: %m, day: %d', 'strftimedate', ['year', 'month', 'day'],
            ],
            // strftimedate cases from existing languages
            'german date: day month year with dots' => [
                '%d. %B %Y', 'strftimedate', ['day', 'month', 'year'],
            ],
            'japanese date: year month day' => [
                '%Y年%m月%d日', 'strftimedate', ['year', 'month', 'day'],
            ],
            'hungarian date: year month day with dots' => [
                '%Y. %m. %d.', 'strftimedate', ['year', 'month', 'day'],
            ],
        ];
    }

    /**
     * Tests get_order_from_format() with various format strings.
     *
     * @param string $format   The strftime format string to simulate.
     * @param string $langkey  The langconfig key (unused here, format is injected directly).
     * @param array  $expected The expected order of components.
     */
    #[DataProvider('get_date_elements_order_provider')]
    public function test_get_date_elements_order(string $format, string $langkey, array $expected): void {
        $this->resetAfterTest();
        $result = utility_dates::get_order_from_format($format);
        $this->assertEquals($expected, $result);
    }
}
