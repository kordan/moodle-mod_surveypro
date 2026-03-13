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
 * Unit tests for surveyprofield_fileupload item
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_fileupload;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveyprofield_fileupload\item methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\surveyprofield_fileupload\item::class)]
class item_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveyprofield_fileupload\item with minimal dependencies.
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
    // Tests for insetupform overrides
    // -------------------------------------------------------------------------

    /**
     * fileupload must disable hideinstructions and insearchform in insetupform.
     */
    public function test_insetupform_disabled_fields(): void {
        $item = $this->make_item();

        $this->assertFalse($item->insetupform['hideinstructions']);
        $this->assertFalse($item->insetupform['insearchform']);
    }

    // -------------------------------------------------------------------------
    // Tests for getter/setter pairs
    // -------------------------------------------------------------------------

    /**
     * set_maxfiles() and get_maxfiles() must work correctly.
     */
    public function test_set_get_maxfiles(): void {
        $item = $this->make_item();
        $item->set_maxfiles(3);

        $this->assertEquals(3, $item->get_maxfiles());
    }

    /**
     * set_maxbytes() and get_maxbytes() must work correctly.
     */
    public function test_set_get_maxbytes(): void {
        $item = $this->make_item();
        $item->set_maxbytes(2097152);

        $this->assertEquals(2097152, $item->get_maxbytes());
    }

    /**
     * set_filetypes() and get_filetypes() must work correctly.
     */
    public function test_set_get_filetypes(): void {
        $item = $this->make_item();
        $item->set_filetypes('.pdf,.docx');

        $this->assertEquals('.pdf,.docx', $item->get_filetypes());
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
        $this->assertArrayHasKey('surveyprofield_fileupload', $result);
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
