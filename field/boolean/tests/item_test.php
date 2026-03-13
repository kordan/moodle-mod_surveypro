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
 * Unit tests for surveyprofield_boolean item
 *
 * @package   surveyprofield_boolean
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_boolean;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_boolean\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_boolean\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_boolean\item with minimal dependencies.
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
    // Tests for get_friendlyformat()
    // -------------------------------------------------------------------------

    /**
     * get_friendlyformat() must return strfbool01.
     */
    public function test_get_friendlyformat(): void {
        $item = $this->make_item();
        $this->assertEquals('strfbool01', $item->get_friendlyformat());
    }

    // -------------------------------------------------------------------------
    // Tests for get_downloadformats()
    // -------------------------------------------------------------------------

    /**
     * get_downloadformats() must return an array with 10 entries.
     */
    public function test_get_downloadformats_count(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertIsArray($result);
        $this->assertCount(10, $result);
    }

    /**
     * get_downloadformats() must contain strfbool01.
     */
    public function test_get_downloadformats_contains_strfbool01(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertArrayHasKey('strfbool01', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return an array with expected keys.
     */
    public function test_get_multilang_fields_has_expected_keys(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
        $this->assertArrayHasKey('surveyprofield_boolean', $result);
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
     * parent_encode_child_parentcontent() with value '0' must return '0'.
     */
    public function test_parent_encode_child_parentcontent_zero(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent('0');

        $this->assertEquals('0', $result);
    }

    /**
     * parent_encode_child_parentcontent() with value '1' must return '1'.
     */
    public function test_parent_encode_child_parentcontent_one(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent('1');

        $this->assertEquals('1', $result);
    }

    /**
     * parent_encode_child_parentcontent() with garbage value must include '>' separator.
     */
    public function test_parent_encode_child_parentcontent_garbage(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent('garbage');

        $this->assertStringContainsString('>', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for parent_decode_child_parentvalue()
    // -------------------------------------------------------------------------

    /**
     * parent_decode_child_parentvalue() with '0' must return '0'.
     */
    public function test_parent_decode_child_parentvalue_zero(): void {
        $item = $this->make_item();
        $result = $item->parent_decode_child_parentvalue('0');

        $this->assertEquals('0', $result);
    }

    /**
     * parent_decode_child_parentvalue() with '1' must return '1'.
     */
    public function test_parent_decode_child_parentvalue_one(): void {
        $item = $this->make_item();
        $result = $item->parent_decode_child_parentvalue('1');

        $this->assertEquals('1', $result);
    }

    /**
     * encode then decode must return the original value.
     */
    public function test_encode_decode_roundtrip(): void {
        $item = $this->make_item();

        foreach (['0', '1'] as $value) {
            $encoded = $item->parent_encode_child_parentcontent($value);
            $decoded = $item->parent_decode_child_parentvalue($encoded);
            $this->assertEquals($value, $decoded);
        }
    }

    // -------------------------------------------------------------------------
    // Tests for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * parent_validate_child_constraints() with '0' must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_child_constraints_zero(): void {
        $item = $this->make_item();
        $result = $item->parent_validate_child_constraints('0');

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * parent_validate_child_constraints() with '1' must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_child_constraints_one(): void {
        $item = $this->make_item();
        $result = $item->parent_validate_child_constraints('1');

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * parent_validate_child_constraints() with invalid value must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_child_constraints_invalid(): void {
        $item = $this->make_item();
        $result = $item->parent_validate_child_constraints('99');

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * parent_validate_child_constraints() with multiple values must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_child_constraints_multiple_values(): void {
        $item = $this->make_item();
        $result = $item->parent_validate_child_constraints('0' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '1');

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_encode_child_parentcontent()
    // -------------------------------------------------------------------------

    /**
     * Encoding an empty string must return an empty string.
     */
    public function test_parent_encode_empty_string(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent('');

        $this->assertEquals('', $result);
    }

    /**
     * Encoding a mix of valid and garbage must include both the valid index and garbage.
     */
    public function test_parent_encode_mixed_valid_and_garbage(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent("0\ngarbage");

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertContains('0', $parts);
        $this->assertContains('>', $parts);
        $this->assertContains('garbage', $parts);
    }

    /**
     * Encoding duplicate values must return only one index due to array_unique.
     */
    public function test_parent_encode_duplicate_values(): void {
        $item = $this->make_item();
        $result = $item->parent_encode_child_parentcontent("0\n0");

        $this->assertEquals('0', $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_decode_child_parentvalue()
    // -------------------------------------------------------------------------

    /**
     * Decoding with garbage after '>' must include the garbage label in result.
     */
    public function test_parent_decode_with_garbage_after_separator(): void {
        $item = $this->make_item();
        $encoded = '>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_decode_child_parentvalue($encoded);

        $this->assertStringContainsString('garbage', $result);
    }

    /**
     * Decoding an out-of-range index must return the index itself as fallback.
     */
    public function test_parent_decode_out_of_range_index(): void {
        $item = $this->make_item();
        $result = $item->parent_decode_child_parentvalue('99');

        $this->assertEquals('99', $result);
    }

    /**
     * Decoding a valid index with garbage after '>' must include both.
     */
    public function test_parent_decode_valid_and_garbage(): void {
        $item = $this->make_item();
        $encoded = '0' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_decode_child_parentvalue($encoded);

        $this->assertStringContainsString('0', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * Parentvalue with '>' and exactly 2 parts must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_separator_two_parts(): void {
        $item = $this->make_item();
        $value = '>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'sometext';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * Parentvalue with '>' and more than 2 parts must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_separator_too_many_parts(): void {
        $item = $this->make_item();
        $value = '0' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'sometext';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }
}
