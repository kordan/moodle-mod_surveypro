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
 * Unit tests for surveyproformat_pagebreak item
 *
 * @package   surveyproformat_pagebreak
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproformat_pagebreak;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyproformat_pagebreak\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyproformat_pagebreak\item::class)]
class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyproformat_pagebreak\item with minimal dependencies.
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
     * has_mandatoryattribute() must return false for pagebreak.
     */
    public function test_has_mandatoryattribute_returns_false(): void {
        $this->resetAfterTest();
        $this->assertFalse(item::has_mandatoryattribute());
    }

    // -------------------------------------------------------------------------
    // Tests for item_uses_form_page()
    // -------------------------------------------------------------------------

    /**
     * item_uses_form_page() must return false for pagebreak.
     */
    public function test_item_uses_form_page_returns_false(): void {
        $item = $this->make_item();
        $this->assertFalse($item->item_uses_form_page());
    }

    // -------------------------------------------------------------------------
    // Tests for get_plugin_schema()
    // -------------------------------------------------------------------------

    /**
     * get_plugin_schema() must return an empty string for pagebreak.
     */
    public function test_get_plugin_schema_returns_empty_string(): void {
        $this->resetAfterTest();
        $this->assertEquals('', item::get_plugin_schema());
    }

    // -------------------------------------------------------------------------
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * pagebreak must disable all standard fields in insetupform including content.
     */
    public function test_insetupform_disabled_fields(): void {
        $item = $this->make_item();

        $this->assertFalse($item->insetupform['common_fs']);
        $this->assertFalse($item->insetupform['content']);
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
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return array with surveypro_item key.
     */
    public function test_get_multilang_fields_has_surveypro_item_key(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
    }
}
