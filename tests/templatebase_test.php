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
 * Unit tests for templatebase
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for templatebase methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\templatebase::class)]
final class templatebase_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate mtemplate_base with minimal dependencies.
     *
     * @return mtemplate_base
     */
    private function make_template(): mtemplate_base {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        return new mtemplate_base($cm, $context, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for set_xmlvalidationoutcome() and get_xmlvalidationoutcome()
    // -------------------------------------------------------------------------

    /**
     * set_xmlvalidationoutcome() with null must set an empty stdClass.
     */
    public function test_set_xmlvalidationoutcome_null(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->set_xmlvalidationoutcome(null);

        $result = $template->get_xmlvalidationoutcome();
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    /**
     * set_xmlvalidationoutcome() with no argument must set an empty stdClass.
     */
    public function test_set_xmlvalidationoutcome_no_argument(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->set_xmlvalidationoutcome();

        $result = $template->get_xmlvalidationoutcome();
        $this->assertInstanceOf(\stdClass::class, $result);
    }

    /**
     * set_xmlvalidationoutcome() with a value must store and return it correctly.
     */
    public function test_set_xmlvalidationoutcome_with_value(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $error = new \stdClass();
        $error->key = 'missingitemtype';
        $template->set_xmlvalidationoutcome($error);

        $result = $template->get_xmlvalidationoutcome();
        $this->assertEquals('missingitemtype', $result->key);
    }

    // -------------------------------------------------------------------------
    // Tests for get_table_structure()
    // -------------------------------------------------------------------------

    /**
     * Without type and plugin it must return the fields of surveypro_item table.
     */
    public function test_get_table_structure_no_type_no_plugin(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_table_structure();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains('id', $result);
        $this->assertContains('surveyproid', $result);
        $this->assertContains('type', $result);
        $this->assertContains('plugin', $result);
    }

    /**
     * With a valid type and plugin it must return the fields of that plugin table.
     */
    public function test_get_table_structure_with_type_and_plugin(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_table_structure('field', 'character');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains('id', $result);
        $this->assertContains('itemid', $result);
    }

    /**
     * With a plugin without install.xml it must return an empty array.
     */
    public function test_get_table_structure_plugin_without_installxml(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        // pagebreak has no install.xml because it has no attributes.
        $result = $template->get_table_structure('format', 'pagebreak');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_subplugin_versions()
    // -------------------------------------------------------------------------

    /**
     * get_subplugin_versions() must return a non-empty array.
     */
    public function test_get_subplugin_versions_returns_array(): void {
        $this->resetAfterTest();

        $result = mtemplate_base::get_subplugin_versions();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * get_subplugin_versions() must contain known plugins.
     */
    public function test_get_subplugin_versions_contains_known_plugins(): void {
        $this->resetAfterTest();

        $result = mtemplate_base::get_subplugin_versions();

        $this->assertArrayHasKey('field_character', $result);
        $this->assertArrayHasKey('field_boolean', $result);
        $this->assertArrayHasKey('format_label', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for validate_xml()
    // -------------------------------------------------------------------------

    /**
     * Valid XML must produce no validation errors.
     */
    public function test_validate_xml_valid_xml(): void {
        $this->resetAfterTest();

        global $CFG;

        // Use a real master template XML file.
        $templatepath = $CFG->dirroot . '/mod/surveypro/template/';
        $templates = glob($templatepath . '*/template.xml');
        if (empty($templates)) {
            $this->markTestSkipped('No master template XML files found.');
        }

        $template = $this->make_template();
        $xml = file_get_contents($templates[0]);
        $template->validate_xml($xml);

        $outcome = $template->get_xmlvalidationoutcome();
        $this->assertFalse(isset($outcome->key));
    }

    /**
     * XML with missing item type must produce a validation error.
     */
    public function test_validate_xml_missing_type(): void {
        $this->resetAfterTest();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <items>
            <item plugin="character" version="2024010100">
            </item>
        </items>';

        $template = $this->make_template();
        $template->validate_xml($xml);

        $outcome = $template->get_xmlvalidationoutcome();
        $this->assertEquals('missingitemtype', $outcome->key);
    }

    /**
     * XML with missing item plugin must produce a validation error.
     */
    public function test_validate_xml_missing_plugin(): void {
        $this->resetAfterTest();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <items>
            <item type="field" version="2024010100">
            </item>
        </items>';

        $template = $this->make_template();
        $template->validate_xml($xml);

        $outcome = $template->get_xmlvalidationoutcome();
        $this->assertEquals('missingitemplugin', $outcome->key);
    }

    /**
     * XML with missing item version must produce a validation error.
     */
    public function test_validate_xml_missing_version(): void {
        $this->resetAfterTest();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <items>
            <item type="field" plugin="character">
            </item>
        </items>';

        $template = $this->make_template();
        $template->validate_xml($xml);

        $outcome = $template->get_xmlvalidationoutcome();
        $this->assertEquals('missingitemversion', $outcome->key);
    }

    /**
     * XML with invalid type or plugin must produce a validation error.
     */
    public function test_validate_xml_invalid_type_or_plugin(): void {
        $this->resetAfterTest();

        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <items>
            <item type="field" plugin="nonexistentplugin" version="2024010100">
            </item>
        </items>';

        $template = $this->make_template();
        $template->validate_xml($xml);

        $outcome = $template->get_xmlvalidationoutcome();
        $this->assertEquals('invalidtypeorplugin', $outcome->key);
    }
}
