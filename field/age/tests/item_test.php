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
 * Unit tests for surveyprofield_age item
 *
 * @package   surveyprofield_age
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_age;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_age\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_age\item::class)]
final class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_age\item with minimal dependencies.
     *
     * @return item
     */
    private function make_item(): item {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        return new item($cm, $surveypro, 0, false);
    }

    // -------------------------------------------------------------------------
    // Tests for item_age_to_unix_time()
    // -------------------------------------------------------------------------

    /**
     * item_age_to_unix_time() must return an integer.
     */
    public function test_item_age_to_unix_time_returns_integer(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->item_age_to_unix_time(10, 6);

        $this->assertIsInt($result);
    }

    /**
     * item_age_to_unix_time() with year=0 and month=0 must return a consistent value.
     */
    public function test_item_age_to_unix_time_zero_age(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->item_age_to_unix_time(0, 0);

        $this->assertIsInt($result);
    }

    /**
     * item_age_to_unix_time() must be reversible via item_split_unix_time().
     */
    public function test_item_age_to_unix_time_reversible(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $year = 10;
        $month = 6;
        $unixtime = $item->item_age_to_unix_time($year, $month);
        $result = $item->item_split_unix_time($unixtime);

        $this->assertEquals($year, $result['year']);
        $this->assertEquals($month, $result['mon']);
    }

    // -------------------------------------------------------------------------
    // Tests for item_split_unix_time()
    // -------------------------------------------------------------------------

    /**
     * item_split_unix_time() must return an array with year and mon keys.
     */
    public function test_item_split_unix_time_returns_expected_keys(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $unixtime = $item->item_age_to_unix_time(5, 3);
        $result = $item->item_split_unix_time($unixtime);

        $this->assertArrayHasKey('year', $result);
        $this->assertArrayHasKey('mon', $result);
    }

    /**
     * item_split_unix_time() with month=12 must increment year and set month to 0.
     */
    public function test_item_split_unix_time_december_increments_year(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $unixtime = $item->item_age_to_unix_time(5, 12);
        $result = $item->item_split_unix_time($unixtime);

        $this->assertEquals(6, $result['year']);
        $this->assertEquals(0, $result['mon']);
    }

    // -------------------------------------------------------------------------
    // Tests for item_age_to_text()
    // -------------------------------------------------------------------------

    /**
     * item_age_to_text() with only years must return years string.
     */
    public function test_item_age_to_text_years_only(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $agearray = ['year' => 5, 'mon' => 0];
        $result = $item->item_age_to_text($agearray);

        $this->assertStringContainsString('5', $result);
        $this->assertStringContainsString(get_string('years'), $result);
    }

    /**
     * item_age_to_text() with only months must return months string.
     */
    public function test_item_age_to_text_months_only(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $agearray = ['year' => 0, 'mon' => 6];
        $result = $item->item_age_to_text($agearray);

        $this->assertStringContainsString('6', $result);
        $this->assertStringContainsString(get_string('months', 'surveyprofield_age'), $result);
    }

    /**
     * item_age_to_text() with years and months must include both.
     */
    public function test_item_age_to_text_years_and_months(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $agearray = ['year' => 3, 'mon' => 4];
        $result = $item->item_age_to_text($agearray);

        $this->assertStringContainsString('3', $result);
        $this->assertStringContainsString('4', $result);
        $this->assertStringContainsString(get_string('years'), $result);
        $this->assertStringContainsString(get_string('months', 'surveyprofield_age'), $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_composite_fields()
    // -------------------------------------------------------------------------

    /**
     * get_composite_fields() must return the expected fields.
     */
    public function test_get_composite_fields(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->get_composite_fields();

        $this->assertIsArray($result);
        $this->assertContains('defaultvalue', $result);
        $this->assertContains('lowerbound', $result);
        $this->assertContains('upperbound', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return an array with surveypro_item key.
     */
    public function test_get_multilang_fields_has_expected_keys(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
        $this->assertArrayHasKey('surveyprofield_age', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_plugin_schema()
    // -------------------------------------------------------------------------

    /**
     * get_plugin_schema() must return valid XML.
     */
    public function test_get_plugin_schema_is_valid_xml(): void {
        $this->resetAfterTest();

        $result = item::get_plugin_schema();
        $dom = new \DOMDocument();

        $this->assertTrue($dom->loadXML($result));
    }
}
