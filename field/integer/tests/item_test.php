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
 * Unit tests for surveyprofield_integer item
 *
 * @package   surveyprofield_integer
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_integer;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_integer\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_integer\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_integer\item with minimal dependencies.
     *
     * @param int $lowerbound
     * @param int $upperbound
     * @return item
     */
    private function make_item(int $lowerbound = 0, int $upperbound = 100): item {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $item = new item($cm, $surveypro, 0, false);
        $item->set_lowerbound($lowerbound);
        $item->set_upperbound($upperbound);
        return $item;
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
        $this->assertArrayHasKey('surveyprofield_integer', $result);
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
    // Tests for parent_encode_child_parentcontent()
    // -------------------------------------------------------------------------

    /**
     * Encoding a valid integer within bounds must return that integer.
     */
    public function test_parent_encode_valid_integer(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent('42');

        $this->assertEquals('42', $result);
    }

    /**
     * Encoding an integer outside bounds must include '>' separator.
     */
    public function test_parent_encode_out_of_bounds_includes_separator(): void {
        $item = $this->make_item(0, 10);
        $result = $item->parent_encode_child_parentcontent('999');

        $this->assertStringContainsString('>', $result);
        $this->assertStringContainsString('999', $result);
    }

    /**
     * Encoding non-numeric value must include '>' separator.
     */
    public function test_parent_encode_non_numeric_includes_separator(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent('garbage');

        $this->assertStringContainsString('>', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for parent_decode_child_parentvalue()
    // -------------------------------------------------------------------------

    /**
     * Decoding a simple integer must return that integer.
     */
    public function test_parent_decode_simple_integer(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_decode_child_parentvalue('42');

        $this->assertEquals('42', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * Integer within bounds must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_within_bounds(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_validate_child_constraints('50');

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * Integer below lowerbound must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_below_lowerbound(): void {
        $item = $this->make_item(10, 100);
        $result = $item->parent_validate_child_constraints('5');

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * Integer above upperbound must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_above_upperbound(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_validate_child_constraints('999');

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * Multiple values must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_multiple_values_malformed(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_validate_child_constraints('5' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '10');

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }

    /**
     * Value with '>' separator and single label must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_with_separator_nevermatch(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_validate_child_constraints('>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage');

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_encode_child_parentcontent()
    // -------------------------------------------------------------------------

    /**
     * Encoding an empty string must return an empty string.
     */
    public function test_parent_encode_empty_string(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent('');

        $this->assertEquals('', $result);
    }

    /**
     * Encoding a value at lowerbound must be valid.
     */
    public function test_parent_encode_at_lowerbound(): void {
        $item = $this->make_item(10, 100);
        $result = $item->parent_encode_child_parentcontent('10');

        $this->assertEquals('10', $result);
    }

    /**
     * Encoding a value at upperbound must be valid.
     */
    public function test_parent_encode_at_upperbound(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent('100');

        $this->assertEquals('100', $result);
    }

    /**
     * Encoding a mix of valid and garbage must include both.
     */
    public function test_parent_encode_mixed_valid_and_garbage(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent("50\ngarbage");

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertContains('50', $parts);
        $this->assertContains('>', $parts);
        $this->assertContains('garbage', $parts);
    }

    /**
     * Encoding duplicate values must return only one due to array_unique.
     */
    public function test_parent_encode_duplicate_values(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_encode_child_parentcontent("50\n50");

        $this->assertEquals('50', $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_decode_child_parentvalue()
    // -------------------------------------------------------------------------

    /**
     * Decoding with garbage after '>' must include the garbage label in result.
     */
    public function test_parent_decode_with_garbage_after_separator(): void {
        $item = $this->make_item(0, 100);
        $encoded = '>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_decode_child_parentvalue($encoded);

        $this->assertStringContainsString('garbage', $result);
    }

    /**
     * Decoding a valid value with garbage after '>' must include both.
     * Note: integer decode reconstructs indices 0..$key-1, not the original values.
     */
    public function test_parent_decode_valid_and_garbage(): void {
        $item = $this->make_item(0, 100);
        $encoded = '42' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_decode_child_parentvalue($encoded);

        // Before '>' there is 1 element so decode returns index 0.
        $this->assertStringContainsString('0', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * Value at lowerbound must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_at_lowerbound(): void {
        $item = $this->make_item(10, 100);
        $result = $item->parent_validate_child_constraints('10');

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * Value at upperbound must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_at_upperbound(): void {
        $item = $this->make_item(0, 100);
        $result = $item->parent_validate_child_constraints('100');

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * Parentvalue with '>' and exactly 2 parts must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_separator_two_parts(): void {
        $item = $this->make_item(0, 100);
        $value = '>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * Parentvalue with '>' and more than 2 parts must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_separator_too_many_parts(): void {
        $item = $this->make_item(0, 100);
        $value = '42' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }
}
