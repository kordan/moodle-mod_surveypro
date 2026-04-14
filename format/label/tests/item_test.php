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
 * Unit tests for surveyproformat_label item
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproformat_label;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyproformat_label\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyproformat_label\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyproformat_label\item with minimal dependencies.
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
    // Tests for has_mandatoryattribute()
    // -------------------------------------------------------------------------

    /**
     * has_mandatoryattribute() must return false for label.
     */
    public function test_has_mandatoryattribute_returns_false(): void {
        $this->resetAfterTest();
        $this->assertFalse(item::has_mandatoryattribute());
    }

    // -------------------------------------------------------------------------
    // Tests for get_pdf_template()
    // -------------------------------------------------------------------------

    /**
     * get_pdf_template() must return SURVEYPRO_2COLUMNSTEMPLATE.
     */
    public function test_get_pdf_template_returns_2columns(): void {
        $this->resetAfterTest();
        $this->assertEquals(SURVEYPRO_2COLUMNSTEMPLATE, item::get_pdf_template());
    }

    // -------------------------------------------------------------------------
    // Tests for get_indent()
    // -------------------------------------------------------------------------

    /**
     * get_indent() must return false when fullwidth is true.
     */
    public function test_get_indent_returns_false_when_fullwidth(): void {
        $item = $this->make_item();
        $item->set_fullwidth(1);

        $this->assertFalse($item->get_indent());
    }

    /**
     * get_indent() must return indent value when fullwidth is false.
     */
    public function test_get_indent_returns_indent_when_not_fullwidth(): void {
        $item = $this->make_item();
        $item->set_fullwidth(0);
        $item->set_indent(2);

        $this->assertEquals(2, $item->get_indent());
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
        $this->assertArrayHasKey('surveyproformat_label', $result);
    }

    /**
     * get_multilang_fields() with includemetafields=true must include filename and filecontent.
     */
    public function test_get_multilang_fields_with_meta(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields(true);

        $this->assertContains('filename', $result['surveypro_item']);
        $this->assertContains('filecontent', $result['surveypro_item']);
    }

    /**
     * get_multilang_fields() with includemetafields=false must not include filename and filecontent.
     */
    public function test_get_multilang_fields_without_meta(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields(false);

        $this->assertNotContains('filename', $result['surveypro_item']);
        $this->assertNotContains('filecontent', $result['surveypro_item']);
    }

    /**
     * get_multilang_fields() must include leftlabel in plugin fields.
     */
    public function test_get_multilang_fields_includes_leftlabel(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertContains('leftlabel', $result['surveyproformat_label']);
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
     * set_fullwidth() and get_fullwidth() must work correctly.
     */
    public function test_set_get_fullwidth(): void {
        $item = $this->make_item();
        $item->set_fullwidth(1);

        $this->assertEquals(1, $item->get_fullwidth());
    }

    /**
     * set_leftlabel() and get_leftlabel() must work correctly.
     */
    public function test_set_get_leftlabel(): void {
        $item = $this->make_item();
        $item->set_leftlabel('My label');

        $this->assertEquals('My label', $item->get_leftlabel());
    }

    // -------------------------------------------------------------------------
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * label must disable specific fields in insetupform.
     */
    public function test_insetupform_disabled_fields(): void {
        $item = $this->make_item();

        $this->assertFalse($item->insetupform['required']);
        $this->assertFalse($item->insetupform['position']);
        $this->assertFalse($item->insetupform['variable']);
        $this->assertFalse($item->insetupform['extranote']);
        $this->assertFalse($item->insetupform['hideinstructions']);
        $this->assertFalse($item->insetupform['parentid']);
    }
}
