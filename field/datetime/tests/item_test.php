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
 * Unit tests for surveyprofield_datetime item
 *
 * @package   surveyprofield_datetime
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_datetime;

defined('MOODLE_INTERNAL') || die();

use surveyprofield_datetime\tests\datetime_item_test_helper;

/**
 * Unit tests for surveyprofield_datetime\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_datetime\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate datetime_item_test_helper with minimal dependencies.
     *
     * @return datetime_item_test_helper
     */
    private function make_item(): datetime_item_test_helper {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        return new datetime_item_test_helper($cm, $surveypro, 0, false);
    }

    // -------------------------------------------------------------------------
    // Tests for item_datetime_to_unix_time()
    // -------------------------------------------------------------------------

    /**
     * item_datetime_to_unix_time() must return an integer.
     */
    public function test_item_datetime_to_unix_time_returns_integer(): void {
        $item = $this->make_item();
        $result = $item->item_datetime_to_unix_time(2024, 6, 15, 10, 30);

        $this->assertIsInt($result);
    }

    /**
     * item_datetime_to_unix_time() must be reversible via item_split_unix_time().
     */
    public function test_item_datetime_to_unix_time_reversible(): void {
        $item = $this->make_item();
        $unixtime = $item->item_datetime_to_unix_time(2024, 6, 15, 10, 30);
        $result = $item->call_item_split_unix_time($unixtime);

        $this->assertEquals(2024, $result['year']);
        $this->assertEquals(6, $result['mon']);
        $this->assertEquals(15, $result['mday']);
        $this->assertEquals(10, $result['hours']);
        $this->assertEquals(30, $result['minutes']);
    }

    /**
     * item_datetime_to_unix_time() for different datetimes must return different values.
     */
    public function test_item_datetime_to_unix_time_different_values(): void {
        $item = $this->make_item();
        $dt1 = $item->item_datetime_to_unix_time(2024, 1, 1, 0, 0);
        $dt2 = $item->item_datetime_to_unix_time(2024, 1, 1, 0, 1);

        $this->assertGreaterThan($dt1, $dt2);
    }

    // -------------------------------------------------------------------------
    // Tests for get_composite_fields()
    // -------------------------------------------------------------------------

    /**
     * get_composite_fields() must return the expected fields.
     */
    public function test_get_composite_fields(): void {
        $item = $this->make_item();
        $result = $item->get_composite_fields();

        $this->assertIsArray($result);
        $this->assertContains('defaultvalue', $result);
        $this->assertContains('lowerbound', $result);
        $this->assertContains('upperbound', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_friendlyformat()
    // -------------------------------------------------------------------------

    /**
     * get_friendlyformat() must return strftime01.
     */
    public function test_get_friendlyformat(): void {
        $item = $this->make_item();
        $this->assertEquals('strftime01', $item->get_friendlyformat());
    }

    // -------------------------------------------------------------------------
    // Tests for get_downloadformats()
    // -------------------------------------------------------------------------

    /**
     * get_downloadformats() must return an array with 13 entries.
     */
    public function test_get_downloadformats_count(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertIsArray($result);
        $this->assertCount(13, $result);
    }

    /**
     * get_downloadformats() must contain unixtime key.
     */
    public function test_get_downloadformats_contains_unixtime(): void {
        $item = $this->make_item();
        $result = $item->get_downloadformats();

        $this->assertArrayHasKey('unixtime', $result);
    }

    /**
     * get_strftime_format_keys() must return 12 keys from strftime01 to strftime12.
     */
    public function test_get_strftime_format_keys_sequence(): void {
        $item = $this->make_item();
        $keys = $item->call_get_strftime_format_keys();

        $this->assertCount(12, $keys);
        $this->assertEquals('strftime01', $keys[0]);
        $this->assertEquals('strftime12', $keys[11]);
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
        $this->assertArrayHasKey('surveyprofield_datetime', $result);
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
}
