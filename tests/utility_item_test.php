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
 * Unit tests for utility_item
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for utility_item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\utility_item::class)]
final class utility_item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate utility_item with minimal dependencies.
     *
     * @return utility_item
     */
    private function make_utility(): utility_item {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);

        return new utility_item($cm, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for multilinetext_to_array()
    // -------------------------------------------------------------------------

    /**
     * Null input must return an empty array.
     */
    public function test_multilinetext_to_array_null(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array(null);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Empty string must return an empty array.
     */
    public function test_multilinetext_to_array_empty_string(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array('');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * A string with only spaces must return an empty array.
     */
    public function test_multilinetext_to_array_only_spaces(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array('   ');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * A single line must return an array with one element.
     */
    public function test_multilinetext_to_array_single_line(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array('hello');

        $this->assertCount(1, $result);
        $this->assertEquals('hello', $result[0]);
    }

    /**
     * Multiple lines must return an array with one element per line.
     */
    public function test_multilinetext_to_array_multiple_lines(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array("milk\nsugar\njam");

        $this->assertCount(3, $result);
        $this->assertEquals('milk', $result[0]);
        $this->assertEquals('sugar', $result[1]);
        $this->assertEquals('jam', $result[2]);
    }

    /**
     * Multiple blank lines between content must be collapsed to one.
     */
    public function test_multilinetext_to_array_multiple_blank_lines(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array("milk\n\n\nsugar");

        $this->assertCount(2, $result);
        $this->assertEquals('milk', $result[0]);
        $this->assertEquals('sugar', $result[1]);
    }

    /**
     * Leading and trailing spaces on each line must be trimmed.
     */
    public function test_multilinetext_to_array_trims_each_line(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array("  milk  \n  sugar  ");

        $this->assertEquals('milk', $result[0]);
        $this->assertEquals('sugar', $result[1]);
    }

    /**
     * Windows line endings (\r\n) must be handled correctly.
     */
    public function test_multilinetext_to_array_windows_line_endings(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $result = $utility->multilinetext_to_array("milk\r\nsugar\r\njam");

        $this->assertCount(3, $result);
        $this->assertEquals('milk', $result[0]);
        $this->assertEquals('sugar', $result[1]);
        $this->assertEquals('jam', $result[2]);
    }

    /**
     * Lines with value::label separator must have both parts trimmed.
     */
    public function test_multilinetext_to_array_valuelabel_separator(): void {
        $this->resetAfterTest();

        $utility = $this->make_utility();
        $separator = SURVEYPRO_VALUELABELSEPARATOR;
        $result = $utility->multilinetext_to_array("1 {$separator} one\n2 {$separator} two");

        $this->assertCount(2, $result);
        $this->assertEquals('1' . $separator . 'one', $result[0]);
        $this->assertEquals('2' . $separator . 'two', $result[1]);
    }

    // -------------------------------------------------------------------------
    // Tests for date_is_valid()
    // -------------------------------------------------------------------------

    /**
     * A valid date must return true.
     */
    public function test_date_is_valid_valid_date(): void {
        $this->resetAfterTest();
        $this->assertTrue(utility_item::date_is_valid(15, 6, 2024));
    }

    /**
     * An invalid day must return false.
     */
    public function test_date_is_valid_invalid_day(): void {
        $this->resetAfterTest();
        $this->assertFalse(utility_item::date_is_valid(32, 1, 2024));
    }

    /**
     * An invalid month must return false.
     */
    public function test_date_is_valid_invalid_month(): void {
        $this->resetAfterTest();
        $this->assertFalse(utility_item::date_is_valid(15, 13, 2024));
    }

    /**
     * February 29 on a leap year must return true.
     */
    public function test_date_is_valid_feb29_leap_year(): void {
        $this->resetAfterTest();
        $this->assertTrue(utility_item::date_is_valid(29, 2, 2024));
    }

    /**
     * February 29 on a non-leap year must return false.
     */
    public function test_date_is_valid_feb29_non_leap_year(): void {
        $this->resetAfterTest();
        $this->assertFalse(utility_item::date_is_valid(29, 2, 2023));
    }

    /**
     * February 30 must always return false.
     */
    public function test_date_is_valid_feb30(): void {
        $this->resetAfterTest();
        $this->assertFalse(utility_item::date_is_valid(30, 2, 2024));
    }

    /**
     * Day 0 must return false.
     */
    public function test_date_is_valid_day_zero(): void {
        $this->resetAfterTest();
        $this->assertFalse(utility_item::date_is_valid(0, 1, 2024));
    }

    /**
     * Without year parameter must use default year and return true for valid day/month.
     */
    public function test_date_is_valid_default_year(): void {
        $this->resetAfterTest();
        $this->assertTrue(utility_item::date_is_valid(15, 6));
    }

    // -------------------------------------------------------------------------
    // Tests for get_regexp()
    // -------------------------------------------------------------------------

    /**
     * get_regexp() must return a non-empty string.
     */
    public function test_get_regexp_returns_string(): void {
        $this->resetAfterTest();
        $result = utility_item::get_regexp();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * get_regexp() must return a valid regex.
     */
    public function test_get_regexp_is_valid_regex(): void {
        $this->resetAfterTest();
        $result = utility_item::get_regexp();
        $this->assertNotFalse(@preg_match($result, ''));
    }

    // -------------------------------------------------------------------------
    // Tests for get_item_parts()
    // -------------------------------------------------------------------------

    /**
     * A valid surveypro field element name must be parsed correctly.
     */
    public function test_get_item_parts_valid_field_element(): void {
        $this->resetAfterTest();
        $elementname = SURVEYPRO_ITEMPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_character_101';
        $match = utility_item::get_item_parts($elementname);

        $this->assertNotEmpty($match);
        $this->assertEquals(SURVEYPRO_ITEMPREFIX, $match['prefix']);
        $this->assertEquals(SURVEYPRO_TYPEFIELD, $match['type']);
        $this->assertEquals('character', $match['plugin']);
        $this->assertEquals('101', $match['itemid']);
    }

    /**
     * A valid surveypro field element name with option must parse the option correctly.
     */
    public function test_get_item_parts_valid_field_element_with_option(): void {
        $this->resetAfterTest();
        $elementname = SURVEYPRO_ITEMPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_datetime_102_day';
        $match = utility_item::get_item_parts($elementname);

        $this->assertNotEmpty($match);
        $this->assertEquals('102', $match['itemid']);
        $this->assertEquals('day', $match['option']);
    }

    /**
     * A placeholder element name must be parsed correctly.
     */
    public function test_get_item_parts_placeholder_element(): void {
        $this->resetAfterTest();
        $elementname = SURVEYPRO_PLACEHOLDERPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_character_103_placeholder';
        $match = utility_item::get_item_parts($elementname);

        $this->assertNotEmpty($match);
        $this->assertEquals(SURVEYPRO_PLACEHOLDERPREFIX, $match['prefix']);
        $this->assertEquals('103', $match['itemid']);
    }

    /**
     * A dontsaveme element name must be parsed correctly.
     */
    public function test_get_item_parts_dontsaveme_element(): void {
        $this->resetAfterTest();
        $elementname = SURVEYPRO_DONTSAVEMEPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_character_104';
        $match = utility_item::get_item_parts($elementname);

        $this->assertNotEmpty($match);
        $this->assertEquals(SURVEYPRO_DONTSAVEMEPREFIX, $match['prefix']);
    }

    /**
     * A non-item element name like a button must return an empty array.
     */
    public function test_get_item_parts_non_item_element(): void {
        $this->resetAfterTest();
        $match = utility_item::get_item_parts('savebutton');

        $this->assertEmpty($match);
    }

    /**
     * A format type element name must be parsed correctly.
     */
    public function test_get_item_parts_format_element(): void {
        $this->resetAfterTest();
        $elementname = SURVEYPRO_ITEMPREFIX . '_' . SURVEYPRO_TYPEFORMAT . '_label_105';
        $match = utility_item::get_item_parts($elementname);

        $this->assertNotEmpty($match);
        $this->assertEquals(SURVEYPRO_TYPEFORMAT, $match['type']);
        $this->assertEquals('label', $match['plugin']);
        $this->assertEquals('105', $match['itemid']);
    }
}
