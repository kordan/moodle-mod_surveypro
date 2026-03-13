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
 * Unit tests for surveyprofield_textarea item
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_textarea;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_textarea\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_textarea\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_textarea\item with minimal dependencies.
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
    // Tests for response_uses_format()
    // -------------------------------------------------------------------------

    /**
     * response_uses_format() must return true for textarea.
     */
    public function test_response_uses_format_returns_true(): void {
        $this->resetAfterTest();
        $this->assertTrue(item::response_uses_format());
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
        $this->assertArrayHasKey('surveyprofield_textarea', $result);
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
     * set_trimonsave() and get_trimonsave() must work correctly.
     */
    public function test_set_get_trimonsave(): void {
        $item = $this->make_item();
        $item->set_trimonsave(1);

        $this->assertEquals(1, $item->get_trimonsave());
    }

    /**
     * set_useeditor() and get_useeditor() must work correctly.
     */
    public function test_set_get_useeditor(): void {
        $item = $this->make_item();
        $item->set_useeditor(1);

        $this->assertEquals(1, $item->get_useeditor());
    }

    /**
     * set_arearows() and get_arearows() must work correctly.
     */
    public function test_set_get_arearows(): void {
        $item = $this->make_item();
        $item->set_arearows(15);

        $this->assertEquals(15, $item->get_arearows());
    }

    /**
     * set_areacols() and get_areacols() must work correctly.
     */
    public function test_set_get_areacols(): void {
        $item = $this->make_item();
        $item->set_areacols(80);

        $this->assertEquals(80, $item->get_areacols());
    }

    /**
     * set_minlength() and get_minlength() must work correctly.
     */
    public function test_set_get_minlength(): void {
        $item = $this->make_item();
        $item->set_minlength(10);

        $this->assertEquals(10, $item->get_minlength());
    }

    /**
     * set_maxlength() and get_maxlength() must work correctly.
     */
    public function test_set_get_maxlength(): void {
        $item = $this->make_item();
        $item->set_maxlength(500);

        $this->assertEquals(500, $item->get_maxlength());
    }
}
