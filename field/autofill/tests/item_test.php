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
 * Unit tests for surveyprofield_autofill item
 *
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_autofill;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_autofill\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_autofill\item::class)]
class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_autofill\item with minimal dependencies.
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
    // Tests for has_mandatoryattribute()
    // -------------------------------------------------------------------------

    /**
     * autofill has_mandatoryattribute() must return false.
     */
    public function test_has_mandatoryattribute_returns_false(): void {
        $this->resetAfterTest();

        $this->assertFalse(item::has_mandatoryattribute());
    }

    // -------------------------------------------------------------------------
    // Tests for get_multilang_fields()
    // -------------------------------------------------------------------------

    /**
     * get_multilang_fields() must return an array with expected keys.
     */
    public function test_get_multilang_fields_has_expected_keys(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $result = $item->get_multilang_fields();

        $this->assertArrayHasKey('surveypro_item', $result);
        $this->assertArrayHasKey('surveyprofield_autofill', $result);
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

    // -------------------------------------------------------------------------
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * autofill must disable required, hideinstructions and parentid in insetupform.
     */
    public function test_insetupform_disabled_fields(): void {
        $this->resetAfterTest();

        $item = $this->make_item();

        $this->assertFalse($item->insetupform['required']);
        $this->assertFalse($item->insetupform['hideinstructions']);
        $this->assertFalse($item->insetupform['parentid']);
    }

    // -------------------------------------------------------------------------
    // Tests for getter/setter pairs
    // -------------------------------------------------------------------------

    /**
     * set_element01() and get_element01() must work correctly.
     */
    public function test_set_get_element01(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $item->set_element01('userid');

        $this->assertEquals('userid', $item->get_element01());
    }

    /**
     * set_hiddenfield() and get_hiddenfield() must work correctly.
     */
    public function test_set_get_hiddenfield(): void {
        $this->resetAfterTest();

        $item = $this->make_item();
        $item->set_hiddenfield(1);

        $this->assertEquals(1, $item->get_hiddenfield());
    }
}
