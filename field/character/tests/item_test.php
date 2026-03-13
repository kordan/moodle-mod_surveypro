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
 * Unit tests for surveyprofield_character item
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_character;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_character\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_character\item::class)]
class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_character\item with minimal dependencies.
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
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return array with expected keys.
     */
    public function test_get_multilang_fields_has_expected_keys(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
        $this->assertArrayHasKey('surveyprofield_character', $result);
    }

    /**
     * get_multilang_fields() must include defaultvalue in the plugin fieldlist.
     */
    public function test_get_multilang_fields_includes_defaultvalue(): void {
        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertContains('defaultvalue', $result['surveyprofield_character']);
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
     * set_defaultvalue() and get_defaultvalue() must work correctly.
     */
    public function test_set_get_defaultvalue(): void {
        $item = $this->make_item();
        $item->set_defaultvalue('hello');

        $this->assertEquals('hello', $item->get_defaultvalue());
    }

    /**
     * set_pattern() and get_pattern() must work correctly.
     */
    public function test_set_get_pattern(): void {
        $item = $this->make_item();
        $item->set_pattern(SURVEYPROFIELD_CHARACTER_FREEPATTERN);

        $this->assertEquals(SURVEYPROFIELD_CHARACTER_FREEPATTERN, $item->get_pattern());
    }

    /**
     * set_minlength() and get_minlength() must work correctly.
     */
    public function test_set_get_minlength(): void {
        $item = $this->make_item();
        $item->set_minlength(5);

        $this->assertEquals(5, $item->get_minlength());
    }

    /**
     * set_maxlength() and get_maxlength() must work correctly.
     */
    public function test_set_get_maxlength(): void {
        $item = $this->make_item();
        $item->set_maxlength(20);

        $this->assertEquals(20, $item->get_maxlength());
    }

    /**
     * set_patterntext() and get_patterntext() must work correctly.
     */
    public function test_set_get_patterntext(): void {
        $item = $this->make_item();
        $item->set_patterntext('A0a');

        $this->assertEquals('A0a', $item->get_patterntext());
    }

    // -------------------------------------------------------------------------
    // Tests for get_generic_property()
    // -------------------------------------------------------------------------

    /**
     * get_generic_property('pattern') with FREEPATTERN must return the pattern constant.
     */
    public function test_get_generic_property_pattern_free(): void {
        $item = $this->make_item();
        $item->set_pattern(SURVEYPROFIELD_CHARACTER_FREEPATTERN);

        $result = $item->get_generic_property('pattern');

        $this->assertEquals(SURVEYPROFIELD_CHARACTER_FREEPATTERN, $result);
    }

    /**
     * get_generic_property('pattern') with CUSTOMPATTERN must return patterntext.
     */
    public function test_get_generic_property_pattern_custom_returns_patterntext(): void {
        $item = $this->make_item();
        $item->set_pattern(SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN);
        $item->set_patterntext('A0a');

        $result = $item->get_generic_property('pattern');

        $this->assertEquals('A0a', $result);
    }

    /**
     * get_generic_property('pattern') with REGEXPATTERN must return patterntext.
     */
    public function test_get_generic_property_pattern_regex_returns_patterntext(): void {
        $item = $this->make_item();
        $item->set_pattern(SURVEYPROFIELD_CHARACTER_REGEXPATTERN);
        $item->set_patterntext('~^[a-z]+$~');

        $result = $item->get_generic_property('pattern');

        $this->assertEquals('~^[a-z]+$~', $result);
    }

    /**
     * get_generic_property() with a non-pattern field must use parent behaviour.
     */
    public function test_get_generic_property_non_pattern_field(): void {
        $item = $this->make_item();
        $item->set_minlength(3);

        $result = $item->get_generic_property('minlength');

        $this->assertEquals(3, $result);
    }
}
