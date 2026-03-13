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
 * Unit tests for mtemplate_save
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for mtemplate_save methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\mtemplate_save::class)]
class mtemplate_save_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate mtemplate_save with minimal dependencies.
     *
     * @param string $templatename Optional template name for formdata.
     * @return mtemplate_save
     */
    private function make_template(string $templatename = 'My Test Template'): mtemplate_save {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $template = new mtemplate_save($cm, $context, $surveypro);
        $template->formdata = new \stdClass();
        $template->formdata->mastertemplatename = $templatename;

        return $template;
    }

    // -------------------------------------------------------------------------
    // Tests for replace_package()
    // -------------------------------------------------------------------------

    /**
     * replace_package() must replace templatemaster with the plugin name.
     */
    public function test_replace_package_replaces_templatemaster(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $content = 'This is templatemaster content';
        $result = $template->replace_package($content, 'myplugin');

        $this->assertStringContainsString('myplugin', $result);
        $this->assertStringNotContainsString('templatemaster', $result);
    }

    /**
     * replace_package() must replace the package annotation.
     */
    public function test_replace_package_replaces_package_annotation(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $content = ' * @package   mod_surveypro';
        $result = $template->replace_package($content, 'myplugin');

        $this->assertStringContainsString('surveyprotemplate_myplugin', $result);
        $this->assertStringNotContainsString('mod_surveypro', $result);
    }

    /**
     * replace_package() must return the content unchanged if nothing to replace.
     */
    public function test_replace_package_no_replacement_needed(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $content = 'Nothing to replace here.';
        $result = $template->replace_package($content, 'myplugin');

        $this->assertEquals($content, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_plugin_name()
    // -------------------------------------------------------------------------

    /**
     * A normal name must be lowercased.
     */
    public function test_get_plugin_name_lowercase(): void {
        $this->resetAfterTest();

        $template = $this->make_template('MyTemplate');
        $result = $template->get_plugin_name();

        $this->assertEquals(strtolower($result), $result);
    }

    /**
     * Spaces must be removed.
     */
    public function test_get_plugin_name_removes_spaces(): void {
        $this->resetAfterTest();

        $template = $this->make_template('my template name');
        $result = $template->get_plugin_name();

        $this->assertStringNotContainsString(' ', $result);
    }

    /**
     * Dashes must be replaced with underscores.
     */
    public function test_get_plugin_name_replaces_dashes(): void {
        $this->resetAfterTest();

        $template = $this->make_template('my-template-name');
        $result = $template->get_plugin_name();

        $this->assertStringNotContainsString('-', $result);
        $this->assertStringContainsString('_', $result);
    }

    /**
     * Double underscores must be collapsed to single underscore.
     */
    public function test_get_plugin_name_collapses_double_underscores(): void {
        $this->resetAfterTest();

        $template = $this->make_template('my__template');
        $result = $template->get_plugin_name();

        $this->assertStringNotContainsString('__', $result);
    }

    /**
     * Leading non-letter characters must be removed.
     */
    public function test_get_plugin_name_removes_leading_non_letters(): void {
        $this->resetAfterTest();

        $template = $this->make_template('123mytemplate');
        $result = $template->get_plugin_name();

        $this->assertMatchesRegularExpression('/^[a-z]/', $result);
    }

    /**
     * A completely invalid name must fall back to a safe default.
     */
    public function test_get_plugin_name_invalid_name_fallback(): void {
        $this->resetAfterTest();

        $template = $this->make_template('123456');
        $result = $template->get_plugin_name();

        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[a-z]/', $result);
    }

    /**
     * Result must only contain valid characters (lowercase letters, digits, underscores).
     */
    public function test_get_plugin_name_valid_characters_only(): void {
        $this->resetAfterTest();

        $template = $this->make_template('My Template Name 2024!');
        $result = $template->get_plugin_name();

        $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]*$/', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for add_entry_in_langtree()
    // -------------------------------------------------------------------------

    /**
     * Adding a new entry must return the correct key.
     */
    public function test_add_entry_in_langtree_new_entry(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->add_entry_in_langtree('character', 'content', 'Hello world');

        $this->assertEquals('character_content_01', $result);
    }

    /**
     * Adding a second entry for the same key must increment the index.
     */
    public function test_add_entry_in_langtree_second_entry(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->add_entry_in_langtree('character', 'content', 'First');
        $result = $template->add_entry_in_langtree('character', 'content', 'Second');

        $this->assertEquals('character_content_02', $result);
    }

    /**
     * Adding entries for different plugins must not interfere.
     */
    public function test_add_entry_in_langtree_different_plugins(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result1 = $template->add_entry_in_langtree('character', 'content', 'Hello');
        $result2 = $template->add_entry_in_langtree('boolean', 'content', 'World');

        $this->assertEquals('character_content_01', $result1);
        $this->assertEquals('boolean_content_01', $result2);
    }

    // -------------------------------------------------------------------------
    // Tests for get_lang_file_content()
    // -------------------------------------------------------------------------

    /**
     * With empty langtree get_lang_file_content() must return only newlines.
     */
    public function test_get_lang_file_content_empty_langtree(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_lang_file_content();

        $this->assertEquals("\n\n", $result);
    }

    /**
     * With one entry get_lang_file_content() must return a valid PHP string assignment.
     */
    public function test_get_lang_file_content_one_entry(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->add_entry_in_langtree('character', 'content', 'Hello world');
        $result = $template->get_lang_file_content();

        $this->assertStringContainsString('$string[', $result);
        $this->assertStringContainsString('Hello world', $result);
    }

    /**
     * Single quotes in content must be escaped.
     */
    public function test_get_lang_file_content_escapes_single_quotes(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->add_entry_in_langtree('character', 'content', "It's a test");
        $result = $template->get_lang_file_content();

        $this->assertStringContainsString("\\'", $result);
    }

    // -------------------------------------------------------------------------
    // Tests for xml_get_field_content()
    // -------------------------------------------------------------------------

    /**
     * xml_get_field_content() with a non-multilang field must return the field content.
     */
    public function test_xml_get_field_content_non_multilang_field(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $template = $this->make_template();

        $item = new \surveyprofield_character\item($cm, $surveypro, 0, false);
        $item->set_required(1);

        $multilangfields = $item->get_multilang_fields();
        $result = $template->xml_get_field_content($item, 'required', $multilangfields);

        $this->assertEquals('1', $result);
    }

    /**
     * xml_get_field_content() with a multilang field must return the langtree key.
     */
    public function test_xml_get_field_content_multilang_field_returns_key(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $template = $this->make_template();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_boolean($surveypro, ['required' => 0]);

        $item = new \surveyprofield_boolean\item($cm, $surveypro, $itemid, false);
        $multilangfields = $item->get_multilang_fields();

        $template->build_langtree($multilangfields, $item);

        $result = $template->xml_get_field_content($item, 'content', $multilangfields);

        $this->assertMatchesRegularExpression('/^boolean_content_\d{2}$/', $result);
    }

    /**
     * xml_get_field_content() with empty multilangfields must return the field content directly.
     */
    public function test_xml_get_field_content_empty_multilangfields(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $template = $this->make_template();

        $item = new \surveyprofield_character\item($cm, $surveypro, 0, false);
        $item->set_required(1);

        $result = $template->xml_get_field_content($item, 'required', []);

        $this->assertEquals('1', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for build_langtree()
    // -------------------------------------------------------------------------

    /**
     * build_langtree() must populate langtree with content field.
     */
    public function test_build_langtree_populates_content(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $template = $this->make_template();

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_boolean($surveypro, ['required' => 0]);

        $item = new \surveyprofield_boolean\item($cm, $surveypro, $itemid, false);
        $multilangfields = $item->get_multilang_fields();

        $template->build_langtree($multilangfields, $item);

        $result = $template->add_entry_in_langtree('boolean', 'content', 'extra');
        $this->assertEquals('boolean_content_02', $result);
    }

    /**
     * build_langtree() must skip filename and filecontent fields.
     */
    public function test_build_langtree_skips_filename_filecontent(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $template = $this->make_template();

        $item = new \surveyprofield_character\item($cm, $surveypro, 0, false);
        $multilangfields = ['surveypro_item' => ['filename', 'filecontent']];

        $template->build_langtree($multilangfields, $item);

        // Nothing should have been added to langtree — first entry must still be _01.
        $result = $template->add_entry_in_langtree('character', 'filename', 'test.png');
        $this->assertEquals('character_filename_01', $result);
    }
}
