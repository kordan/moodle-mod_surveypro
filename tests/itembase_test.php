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
 * Unit tests for itembase
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\tests\itembase_test_character_helper;
use mod_surveypro\tests\itembase_test_checkbox_helper;
use mod_surveypro\tests\itembase_test_select_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for itembase methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\itembase::class)]
class itembase_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate itembase_test_character_helper with minimal dependencies.
     *
     * @return itembase_test_character_helper
     */
    private function make_character_item(): itembase_test_character_helper {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);

        return new itembase_test_character_helper($cm, $surveypro, 0, false);
    }

    /**
     * Instantiate itembase_test_select_helper with minimal dependencies.
     *
     * @return itembase_test_select_helper
     */
    private function make_checkbox_item(): itembase_test_checkbox_helper {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);

        return new itembase_test_checkbox_helper($cm, $surveypro, 0, false);
    }

    /**
     * Instantiate itembase_test_select_helper with minimal dependencies.
     *
     * @return itembase_test_select_helper
     */
    private function make_select_item(): itembase_test_select_helper {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);

        return new itembase_test_select_helper($cm, $surveypro, 0, false);
    }

    // -------------------------------------------------------------------------
    // Tests for item_split_unix_time()
    // -------------------------------------------------------------------------

    /**
     * item_split_unix_time() must return an array with the expected keys.
     */
    public function test_item_split_unix_time_returns_expected_keys(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->call_item_split_unix_time(mktime(10, 30, 0, 6, 15, 2024));

        $this->assertArrayHasKey('year', $result);
        $this->assertArrayHasKey('mon', $result);
        $this->assertArrayHasKey('mday', $result);
        $this->assertArrayHasKey('hours', $result);
        $this->assertArrayHasKey('minutes', $result);
    }

    /**
     * item_split_unix_time() must return correct values.
     */
    public function test_item_split_unix_time_correct_values(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->call_item_split_unix_time(gmmktime(10, 30, 0, 6, 15, 2024));

        $this->assertEquals(2024, $result['year']);
        $this->assertEquals(6, $result['mon']);
        $this->assertEquals(15, $result['mday']);
        $this->assertEquals(10, $result['hours']);
        $this->assertEquals(30, $result['minutes']);
    }

    /**
     * item_split_unix_time() must return integer values.
     */
    public function test_item_split_unix_time_returns_integers(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->call_item_split_unix_time(mktime(0, 0, 0, 1, 1, 2024));

        foreach ($result as $value) {
            $this->assertIsInt($value);
        }
    }

    // -------------------------------------------------------------------------
    // Tests for item_uses_form_page()
    // -------------------------------------------------------------------------

    /**
     * item_uses_form_page() must return true for field items.
     */
    public function test_item_uses_form_page_returns_true(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $this->assertTrue($item->item_uses_form_page());
    }

    // -------------------------------------------------------------------------
    // Tests for item_left_position_allowed()
    // -------------------------------------------------------------------------

    /**
     * item_left_position_allowed() must return true by default.
     */
    public function test_item_left_position_allowed_returns_true(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $this->assertTrue($item->item_left_position_allowed());
    }

    // -------------------------------------------------------------------------
    // Tests for has_mandatoryattribute()
    // -------------------------------------------------------------------------

    /**
     * has_mandatoryattribute() must return true by default.
     */
    public function test_has_mandatoryattribute_returns_true(): void {
        $this->resetAfterTest();

        $this->assertTrue(itembase_test_character_helper::has_mandatoryattribute());
    }

    // -------------------------------------------------------------------------
    // Tests for response_uses_format()
    // -------------------------------------------------------------------------

    /**
     * response_uses_format() must return false by default.
     */
    public function test_response_uses_format_returns_false(): void {
        $this->resetAfterTest();

        $this->assertFalse(itembase_test_character_helper::response_uses_format());
    }

    // -------------------------------------------------------------------------
    // Tests for get_pdf_template()
    // -------------------------------------------------------------------------

    /**
     * get_pdf_template() must return SURVEYPRO_3COLUMNSTEMPLATE.
     */
    public function test_get_pdf_template_returns_correct_constant(): void {
        $this->resetAfterTest();

        $this->assertEquals(SURVEYPRO_3COLUMNSTEMPLATE, itembase_test_character_helper::get_pdf_template());
    }

    // -------------------------------------------------------------------------
    // Tests for item_expected_null_fields()
    // -------------------------------------------------------------------------

    /**
     * item_expected_null_fields() must return an array.
     */
    public function test_item_expected_null_fields_returns_array(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->item_expected_null_fields();

        $this->assertIsArray($result);
    }

    /**
     * Fields set to false in insetupform must appear in item_expected_null_fields().
     */
    public function test_item_expected_null_fields_matches_insetupform(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->item_expected_null_fields();

        foreach ($item->insetupform as $field => $value) {
            if (!$value) {
                $this->assertContains($field, $result);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tests for get_base_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_base_multilang_fields() with includemetafields=true must include filename and filecontent.
     */
    public function test_get_base_multilang_fields_with_meta(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->get_base_multilang_fields(true);

        $this->assertContains('content', $result);
        $this->assertContains('filename', $result);
        $this->assertContains('filecontent', $result);
        $this->assertContains('extranote', $result);
    }

    /**
     * get_base_multilang_fields() with includemetafields=false must not include filename and filecontent.
     */
    public function test_get_base_multilang_fields_without_meta(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $result = $item->get_base_multilang_fields(false);

        $this->assertContains('content', $result);
        $this->assertNotContains('filename', $result);
        $this->assertNotContains('filecontent', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for userform_standardcontent_to_string()
    // -------------------------------------------------------------------------

    /**
     * Empty content must return 'answernotsubmitted' string.
     */
    public function test_userform_standardcontent_to_string_empty(): void {
        $this->resetAfterTest();

        $result = itembase_test_character_helper::userform_standardcontent_to_string('');

        $this->assertEquals(get_string('answernotsubmitted', 'mod_surveypro'), $result);
    }

    /**
     * SURVEYPRO_NOANSWERVALUE must return 'answerisnoanswer' string.
     */
    public function test_userform_standardcontent_to_string_noanswer(): void {
        $this->resetAfterTest();

        $result = itembase_test_character_helper::userform_standardcontent_to_string(SURVEYPRO_NOANSWERVALUE);

        $this->assertEquals(get_string('answerisnoanswer', 'mod_surveypro'), $result);
    }

    /**
     * Normal content must return null.
     */
    public function test_userform_standardcontent_to_string_normal_content(): void {
        $this->resetAfterTest();

        $result = itembase_test_character_helper::userform_standardcontent_to_string('some answer');

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_itembase_schema()
    // -------------------------------------------------------------------------

    /**
     * get_itembase_schema() must return a non-empty string.
     */
    public function test_get_itembase_schema_returns_string(): void {
        $this->resetAfterTest();

        $result = itembase_test_character_helper::get_itembase_schema();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    /**
     * get_itembase_schema() must return valid XML.
     */
    public function test_get_itembase_schema_is_valid_xml(): void {
        $this->resetAfterTest();

        $result = itembase_test_character_helper::get_itembase_schema();
        $dom = new \DOMDocument();

        $this->assertTrue($dom->loadXML($result));
    }

    // -------------------------------------------------------------------------
    // Tests for item_is_child()
    // -------------------------------------------------------------------------

    /**
     * item_is_child() must return false when parentid is not set.
     */
    public function test_item_is_child_returns_false_without_parent(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $this->assertFalse($item->item_is_child());
    }

    /**
     * item_is_child() must return true when parentid is set.
     */
    public function test_item_is_child_returns_true_with_parent(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_parentid(42);

        $this->assertTrue($item->item_is_child());
    }

    // -------------------------------------------------------------------------
    // Tests for getter/setter pairs
    // -------------------------------------------------------------------------

    /**
     * set_itemid() and get_itemid() must work correctly.
     */
    public function test_set_get_itemid(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_itemid(42);

        $this->assertEquals(42, $item->get_itemid());
    }

    /**
     * set_type() and get_type() must work correctly.
     */
    public function test_set_get_type(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_type(SURVEYPRO_TYPEFIELD);

        $this->assertEquals(SURVEYPRO_TYPEFIELD, $item->get_type());
    }

    /**
     * set_plugin() and get_plugin() must work correctly.
     */
    public function test_set_get_plugin(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_plugin('character');

        $this->assertEquals('character', $item->get_plugin());
    }

    /**
     * set_required() and get_required() must work correctly.
     */
    public function test_set_get_required(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_required(1);

        $this->assertEquals(1, $item->get_required());
    }

    /**
     * set_hidden() and get_hidden() must work correctly.
     */
    public function test_set_get_hidden(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_hidden(1);

        $this->assertEquals(1, $item->get_hidden());
    }

    /**
     * set_sortindex() and get_sortindex() must work correctly.
     */
    public function test_set_get_sortindex(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_sortindex(5);

        $this->assertEquals(5, $item->get_sortindex());
    }

    /**
     * set_parentid() and get_parentid() must work correctly.
     */
    public function test_set_get_parentid(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_parentid(10);

        $this->assertEquals(10, $item->get_parentid());
    }

    /**
     * set_variable() and get_variable() must work correctly.
     */
    public function test_set_get_variable(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $item->set_variable('myvar');

        $this->assertEquals('myvar', $item->get_variable());
    }

    // -------------------------------------------------------------------------
    // Tests for item_canbesettomandatory()
    // -------------------------------------------------------------------------

    /**
     * item_canbesettomandatory() must return true when no defaultoption or noanswerdefault is set.
     */
    public function test_item_canbesettomandatory_no_properties(): void {
        $this->resetAfterTest();

        $selectitem = $this->make_select_item();
        $this->assertTrue($selectitem->item_canbesettomandatory());
    }

    /**
     * item_canbesettomandatory() must return false when defaultoption is SURVEYPRO_NOANSWERDEFAULT.
     */
    public function test_item_canbesettomandatory_defaultoption_noanswer(): void {
        $this->resetAfterTest();

        $selectitem = $this->make_select_item();
        $selectitem->set_defaultoption(SURVEYPRO_NOANSWERDEFAULT);

        $this->assertFalse($selectitem->item_canbesettomandatory());
    }

    /**
     * item_canbesettomandatory() must return true when defaultoption is SURVEYPRO_CUSTOMDEFAULT.
     */
    public function test_item_canbesettomandatory_defaultoption_custom(): void {
        $this->resetAfterTest();

        $selectitem = $this->make_select_item();
        $selectitem->set_defaultoption(SURVEYPRO_CUSTOMDEFAULT);

        $this->assertTrue($selectitem->item_canbesettomandatory());
    }

    /**
     * item_canbesettomandatory() must return false when noanswerdefault is 1.
     */
    public function test_item_canbesettomandatory_noanswerdefault_true(): void {
        $this->resetAfterTest();

        $checkboxitem = $this->make_checkbox_item();
        $checkboxitem->set_options("first\nsecond"); // Needed to set noanswerdefault.
        $checkboxitem->set_noanswerdefault(1);

        $this->assertFalse($checkboxitem->item_canbesettomandatory());
    }

    /**
     * item_canbesettomandatory() must return true when noanswerdefault is 0.
     */
    public function test_item_canbesettomandatory_noanswerdefault_false(): void {
        $this->resetAfterTest();

        $checkboxitem = $this->make_checkbox_item();
        $checkboxitem->set_noanswerdefault(0);

        $this->assertTrue($checkboxitem->item_canbesettomandatory());
    }

    // -------------------------------------------------------------------------
    // Tests for item_validate_variablename() refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * build_variablename_candidates() without variable must fallback to plugin_001.
     */
    public function test_build_variablename_candidates_without_variable(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $record = new \stdClass();

        [$testname, $basename] = $item->call_build_variablename_candidates($record);

        $this->assertEquals('character_001', $testname);
        $this->assertEquals('character', $basename);
    }

    /**
     * build_variablename_candidates() with a numbered suffix must strip suffix in basename.
     */
    public function test_build_variablename_candidates_strips_numeric_suffix(): void {
        $this->resetAfterTest();

        $item = $this->make_character_item();
        $record = new \stdClass();
        $record->variable = 'myfield_123';

        [$testname, $basename] = $item->call_build_variablename_candidates($record);

        $this->assertEquals('myfield_123', $testname);
        $this->assertEquals('myfield', $basename);
    }
}
