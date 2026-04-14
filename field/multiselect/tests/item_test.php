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
 * Unit tests for surveyprofield_multiselect item
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_multiselect;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_multiselect\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_multiselect\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_multiselect\item with minimal dependencies.
     *
     * @param string $options
     * @return item
     */
    private function make_item(string $options = "first\nsecond\nthird"): item {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $item = new item($cm, $surveypro, 0, false);
        $item->set_options($options);
        return $item;
    }

    // -------------------------------------------------------------------------
    // Tests for get_uses_positional_answer()
    // -------------------------------------------------------------------------

    /**
     * get_uses_positional_answer() must return true.
     */
    public function test_get_uses_positional_answer(): void {
        $item = $this->make_item();
        $this->assertTrue($item->get_uses_positional_answer());
    }

    // -------------------------------------------------------------------------
    // Tests for get_friendlyformat()
    // -------------------------------------------------------------------------

    /**
     * get_friendlyformat() must return SURVEYPRO_ITEMRETURNSLABELS.
     */
    public function test_get_friendlyformat(): void {
        $item = $this->make_item();
        $this->assertEquals(SURVEYPRO_ITEMRETURNSLABELS, $item->get_friendlyformat());
    }

    // -------------------------------------------------------------------------
    // Tests for get_downloadformats()
    // -------------------------------------------------------------------------

    /**
     * get_downloadformats() must return an array with 3 entries.
     */
    public function test_get_downloadformats_count(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /**
     * get_downloadformats() must contain expected keys.
     */
    public function test_get_downloadformats_keys(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertArrayHasKey(SURVEYPRO_ITEMSRETURNSVALUES, $result);
        $this->assertArrayHasKey(SURVEYPRO_ITEMRETURNSLABELS, $result);
        $this->assertArrayHasKey(SURVEYPRO_ITEMRETURNSPOSITION, $result);
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
        $this->assertArrayHasKey('surveyprofield_multiselect', $result);
    }

    /**
     * get_multilang_fields() must include options and defaultvalue.
     */
    public function test_get_multilang_fields_plugin_fields(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertContains('options', $result['surveyprofield_multiselect']);
        $this->assertContains('defaultvalue', $result['surveyprofield_multiselect']);
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
     * Encoding 'first' must set first bit to 1.
     */
    public function test_parent_encode_first_option(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $result = $item->parent_encode_child_parentcontent('first');

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertEquals('1', $parts[0]);
        $this->assertEquals('0', $parts[1]);
        $this->assertEquals('0', $parts[2]);
    }

    /**
     * Encoding garbage must include '>' separator.
     */
    public function test_parent_encode_garbage_includes_separator(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $result = $item->parent_encode_child_parentcontent('garbage');

        $this->assertStringContainsString('>', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for encode/decode roundtrip
    // -------------------------------------------------------------------------

    /**
     * encode then decode must return the original value.
     */
    public function test_encode_decode_roundtrip(): void {
        $item = $this->make_item("first\nsecond\nthird");

        foreach (['first', 'second', 'third'] as $value) {
            $encoded = $item->parent_encode_child_parentcontent($value);
            $decoded = $item->parent_decode_child_parentvalue($encoded);
            $this->assertEquals($value, $decoded);
        }
    }

    // -------------------------------------------------------------------------
    // Tests for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * Valid parentvalue with correct count must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_ok(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $encoded = $item->parent_encode_child_parentcontent('first');
        $result = $item->parent_validate_child_constraints($encoded);

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }

    /**
     * Parentvalue with too many values must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_too_many_values_malformed(): void {
        $item = $this->make_item("first\nsecond\nthird");
        // 4 values instead of 3.
        $malformed = '1' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
                   . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
                   . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0';
        $result = $item->parent_validate_child_constraints($malformed);

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }

    // da qui è la parte di oggi.

    // -------------------------------------------------------------------------
    // Edge cases for parent_encode_child_parentcontent()
    // -------------------------------------------------------------------------

    /**
     * Encoding two valid options on separate lines must set both bits to 1.
     */
    public function test_parent_encode_multiple_options(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $result = $item->parent_encode_child_parentcontent("first\nsecond");

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertEquals('1', $parts[0]);
        $this->assertEquals('1', $parts[1]);
        $this->assertEquals('0', $parts[2]);
    }

    /**
     * Encoding an empty string must produce all zeros with no '>' separator.
     */
    public function test_parent_encode_empty_string(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $result = $item->parent_encode_child_parentcontent('');

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertNotContains('>', $parts);
        foreach (array_slice($parts, 0, 3) as $bit) {
            $this->assertEquals('0', $bit);
        }
    }

    /**
     * Encoding a mix of valid and garbage must include '>' and the garbage label.
     */
    public function test_parent_encode_mixed_valid_and_garbage(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $result = $item->parent_encode_child_parentcontent("first\ngarbage");

        $parts = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $result);
        $this->assertEquals('1', $parts[0]);
        $this->assertContains('>', $parts);
        $this->assertContains('garbage', $parts);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_decode_child_parentvalue()
    // -------------------------------------------------------------------------

    /**
     * Decoding with garbage after '>' must include the garbage label in result.
     */
    public function test_parent_decode_with_garbage_after_separator(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $encoded = '1' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_decode_child_parentvalue($encoded);

        $this->assertStringContainsString('first', $result);
        $this->assertStringContainsString('garbage', $result);
    }

    /**
     * Decoding all zeros must return an empty string.
     */
    public function test_parent_decode_all_zeros(): void {
        $item = $this->make_item("first\nsecond\nthird");
        $encoded = '0' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
                 . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0';
        $result = $item->parent_decode_child_parentvalue($encoded);

        $this->assertEquals('', $result);
    }

    // -------------------------------------------------------------------------
    // Edge cases for parent_validate_child_constraints()
    // -------------------------------------------------------------------------

    /**
     * Parentvalue with '>' and actualcount <= optioncount must return SURVEYPRO_CONDITIONNEVERMATCH.
     */
    public function test_parent_validate_separator_nevermatch(): void {
        $item = $this->make_item("first\nsecond\nthird");
        // 2 parts with '>': actualcount=2 <= optioncount=3 → CONDITIONNEVERMATCH.
        $value = '>' . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONNEVERMATCH, $result);
    }

    /**
     * Parentvalue with '>' and actualcount > optioncount must return SURVEYPRO_CONDITIONMALFORMED.
     */
    public function test_parent_validate_separator_malformed(): void {
        $item = $this->make_item("first\nsecond\nthird");
        // 5 parts with '>': actualcount=5 > optioncount=3 → CONDITIONMALFORMED.
        $value = '1' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . '>'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . 'garbage';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONMALFORMED, $result);
    }

    /**
     * Parentvalue without '>' and correct count must return SURVEYPRO_CONDITIONOK.
     */
    public function test_parent_validate_no_separator_correct_count(): void {
        $item = $this->make_item("first\nsecond\nthird");
        // 3 parts, all valid indices: actualcount=3 == optioncount=3 → CONDITIONOK.
        $value = '0' . SURVEYPRO_DBMULTICONTENTSEPARATOR . '1'
               . SURVEYPRO_DBMULTICONTENTSEPARATOR . '0';
        $result = $item->parent_validate_child_constraints($value);

        $this->assertEquals(SURVEYPRO_CONDITIONOK, $result);
    }
}
