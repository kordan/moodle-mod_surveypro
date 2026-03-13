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
 * Unit tests for surveyproformat_fieldset item
 *
 * @package   surveyproformat_fieldset
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproformat_fieldset;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyproformat_fieldset\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyproformat_fieldset\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyproformat_fieldset\item with minimal dependencies.
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
     * has_mandatoryattribute() must return false for fieldset.
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
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * fieldset must disable many fields in insetupform.
     */
    public function test_insetupform_disabled_fields(): void {
        $item = $this->make_item();

        $this->assertFalse($item->insetupform['common_fs']);
        $this->assertFalse($item->insetupform['contentformat']);
        $this->assertFalse($item->insetupform['required']);
        $this->assertFalse($item->insetupform['indent']);
        $this->assertFalse($item->insetupform['position']);
        $this->assertFalse($item->insetupform['variable']);
        $this->assertFalse($item->insetupform['extranote']);
        $this->assertFalse($item->insetupform['customnumber']);
        $this->assertFalse($item->insetupform['hideinstructions']);
        $this->assertFalse($item->insetupform['insearchform']);
        $this->assertFalse($item->insetupform['parentid']);
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
    // Tests for get_multilang_fields() - possible bug
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return array with surveypro_item key.
     */
    public function test_get_multilang_fields_has_surveypro_item_key(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for getter/setter
    // -------------------------------------------------------------------------

    /**
     * set_defaultstatus() and get_defaultstatus() must work correctly.
     */
    public function test_set_get_defaultstatus(): void {
        $item = $this->make_item();
        $item->set_defaultstatus(1);

        $this->assertEquals(1, $item->get_defaultstatus());
    }
}
