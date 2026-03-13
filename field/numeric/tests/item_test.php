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
 * Unit tests for surveyprofield_numeric item
 *
 * @package   surveyprofield_numeric
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_numeric;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_numeric\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_numeric\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_numeric\item with minimal dependencies.
     *
     * @return item
     */
    private function make_item(): item {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        return new item($cm, $surveypro, 0, false);
    }

    // -------------------------------------------------------------------------
    // Tests for get_international_number()
    // -------------------------------------------------------------------------

    /**
     * Integer string must return the same value.
     */
    public function test_get_international_number_integer(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('42');

        $this->assertEquals('42', $result);
    }

    /**
     * Float with dot separator must return the same value.
     */
    public function test_get_international_number_dot_separator(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('3.14');

        $this->assertEquals('3.14', $result);
    }

    /**
     * Float with comma separator must convert to dot.
     */
    public function test_get_international_number_comma_separator(): void {
        $item = $this->make_item();
        $item->set_decimalseparator(',');
        $result = $item->get_international_number('3,14');

        $this->assertEquals('3.14', $result);
    }

    /**
     * Negative number must return the same value.
     */
    public function test_get_international_number_negative(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('-5.5');

        $this->assertEquals('-5.5', $result);
    }

    /**
     * Non-numeric string must return false.
     */
    public function test_get_international_number_non_numeric_returns_false(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('garbage');

        $this->assertFalse($result);
    }

    /**
     * Empty string must return false.
     */
    public function test_get_international_number_empty_returns_false(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('');

        $this->assertFalse($result);
    }

    /**
     * String with leading/trailing spaces must be trimmed and return valid number.
     */
    public function test_get_international_number_trims_spaces(): void {
        $item = $this->make_item();
        $item->set_decimalseparator('.');
        $result = $item->get_international_number('  42  ');

        $this->assertEquals('42', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return array with expected keys.
     */
    public function test_get_multilang_fields_has_expected_keys(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
        $this->assertArrayHasKey('surveyprofield_numeric', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_plugin_schema()
    // -------------------------------------------------------------------------

    /**
     * get_plugin_schema() must return valid XML.
     */
    public function test_get_plugin_schema_is_valid_xml(): void {
        $result = item::get_plugin_schema();
        $dom = new \DOMDocument();

        $this->assertTrue($dom->loadXML($result));
    }

    // -------------------------------------------------------------------------
    // Tests for getter/setter pairs
    // -------------------------------------------------------------------------

    /**
     * set_signed() and get_signed() must work correctly.
     */
    public function test_set_get_signed(): void {
        $item = $this->make_item();
        $item->set_signed(1);

        $this->assertEquals(1, $item->get_signed());
    }

    /**
     * set_decimals() and get_decimals() must work correctly.
     */
    public function test_set_get_decimals(): void {
        $item = $this->make_item();
        $item->set_decimals(2);

        $this->assertEquals(2, $item->get_decimals());
    }

    /**
     * set_lowerbound() and get_lowerbound() must work correctly.
     */
    public function test_set_get_lowerbound(): void {
        $item = $this->make_item();
        $item->set_lowerbound('1.5');

        $this->assertEquals('1.5', $item->get_lowerbound());
    }

    /**
     * set_upperbound() and get_upperbound() must work correctly.
     */
    public function test_set_get_upperbound(): void {
        $item = $this->make_item();
        $item->set_upperbound('99.9');

        $this->assertEquals('99.9', $item->get_upperbound());
    }
}
