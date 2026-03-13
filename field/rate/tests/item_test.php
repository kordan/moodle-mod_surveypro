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
 * Unit tests for surveyprofield_rate item
 *
 * @package   surveyprofield_rate
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_rate;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_rate\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_rate\item::class)]
class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_rate\item with minimal dependencies.
     *
     * @return item
     */
    private function make_item(): item {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $item = new item($cm, $surveypro, 0, false);
        $item->set_options("first\nsecond\nthird");
        $item->set_rates("low\nmid\nhigh");
        return $item;
    }

    // -------------------------------------------------------------------------
    // Tests for item_left_position_allowed()
    // -------------------------------------------------------------------------

    /**
     * item_left_position_allowed() must return false for rate items.
     */
    public function test_item_left_position_allowed_returns_false(): void {
        $item = $this->make_item();
        $this->assertFalse($item->item_left_position_allowed());
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
        $this->assertArrayHasKey('surveyprofield_rate', $result);
    }

    /**
     * get_multilang_fields() must include options, rates and defaultvalue.
     */
    public function test_get_multilang_fields_plugin_fields(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertContains('options', $result['surveyprofield_rate']);
        $this->assertContains('rates', $result['surveyprofield_rate']);
        $this->assertContains('defaultvalue', $result['surveyprofield_rate']);
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
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * rate must disable insearchform in insetupform.
     */
    public function test_insetupform_insearchform_disabled(): void {
        $item = $this->make_item();

        $this->assertFalse($item->insetupform['insearchform']);
    }
}
