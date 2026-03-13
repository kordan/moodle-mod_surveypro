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
 * Unit tests for view_responsesearch
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for view_responsesearch methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\view_responsesearch::class)]
class view_responsesearch_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate view_responsesearch with minimal dependencies.
     *
     * @return view_responsesearch
     */
    private function make_manager(): view_responsesearch {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        return new view_responsesearch($cm, $context, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for get_searchparamurl()
    // -------------------------------------------------------------------------

    /**
     * Empty formdata must return null.
     */
    public function test_get_searchparamurl_empty_formdata(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->formdata = new \stdClass();

        $result = $manager->get_searchparamurl();

        $this->assertNull($result);
    }

    /**
     * Formdata with only format elements must return null.
     */
    public function test_get_searchparamurl_only_format_elements(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $formdata = new \stdClass();
        $formdata->{SURVEYPRO_ITEMPREFIX . '_' . SURVEYPRO_TYPEFORMAT . '_label_101'} = 1;
        $manager->formdata = $formdata;

        $result = $manager->get_searchparamurl();

        $this->assertNull($result);
    }

    /**
     * Formdata with only dontsaveme elements must return null.
     */
    public function test_get_searchparamurl_only_dontsaveme_elements(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $formdata = new \stdClass();
        $formdata->{SURVEYPRO_DONTSAVEMEPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_character_102'} = 'ignored';
        $manager->formdata = $formdata;

        $result = $manager->get_searchparamurl();

        $this->assertNull($result);
    }

    /**
     * Formdata with non-item fields must return null.
     */
    public function test_get_searchparamurl_non_item_fields(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $formdata = new \stdClass();
        $formdata->submitbutton = 1;
        $formdata->sesskey = 'abc123';
        $manager->formdata = $formdata;

        $result = $manager->get_searchparamurl();

        $this->assertNull($result);
    }

    /**
     * Formdata with SURVEYPRO_IGNOREMEVALUE must return null.
     */
    public function test_get_searchparamurl_ignoreme_value(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);

        $manager = new view_responsesearch($cm, $context, $surveypro);
        $formdata = new \stdClass();
        $formdata->{SURVEYPRO_ITEMPREFIX . '_' . SURVEYPRO_TYPEFIELD . '_character_' . $itemid} = SURVEYPRO_IGNOREMEVALUE;
        $manager->formdata = $formdata;

        $result = $manager->get_searchparamurl();

        $this->assertNull($result);
    }
}
