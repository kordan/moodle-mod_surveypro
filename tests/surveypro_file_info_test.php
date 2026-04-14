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
 * Unit tests for surveypro_file_info
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for surveypro_file_info methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\surveypro_file_info::class)]
final class surveypro_file_info_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate surveypro_file_info with minimal dependencies.
     *
     * @return surveypro_file_info
     */
    private function make_file_info(): surveypro_file_info {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $browser = $this->createMock(\file_browser::class);
        $areas = [
            SURVEYPRO_ITEMCONTENTFILEAREA => SURVEYPRO_ITEMCONTENTFILEAREA,
            SURVEYPRO_STYLEFILEAREA => SURVEYPRO_STYLEFILEAREA,
        ];
        $filearea = SURVEYPRO_ITEMCONTENTFILEAREA;

        return new surveypro_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    // -------------------------------------------------------------------------
    // Tests for is_writable()
    // -------------------------------------------------------------------------

    /**
     * is_writable() must always return false.
     */
    public function test_is_writable_returns_false(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $this->assertFalse($fileinfo->is_writable());
    }

    // -------------------------------------------------------------------------
    // Tests for is_directory()
    // -------------------------------------------------------------------------

    /**
     * is_directory() must always return true.
     */
    public function test_is_directory_returns_true(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $this->assertTrue($fileinfo->is_directory());
    }

    // -------------------------------------------------------------------------
    // Tests for get_params()
    // -------------------------------------------------------------------------

    /**
     * get_params() must return an array with the expected keys.
     */
    public function test_get_params_returns_expected_keys(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $params = $fileinfo->get_params();

        $this->assertArrayHasKey('contextid', $params);
        $this->assertArrayHasKey('component', $params);
        $this->assertArrayHasKey('filearea', $params);
        $this->assertArrayHasKey('itemid', $params);
        $this->assertArrayHasKey('filepath', $params);
        $this->assertArrayHasKey('filename', $params);
    }

    /**
     * get_params() must return mod_surveypro as component.
     */
    public function test_get_params_component_is_mod_surveypro(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $params = $fileinfo->get_params();

        $this->assertEquals('mod_surveypro', $params['component']);
    }

    /**
     * get_params() must return the correct filearea.
     */
    public function test_get_params_filearea_is_correct(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $params = $fileinfo->get_params();

        $this->assertEquals(SURVEYPRO_ITEMCONTENTFILEAREA, $params['filearea']);
    }

    // -------------------------------------------------------------------------
    // Tests for get_visible_name()
    // -------------------------------------------------------------------------

    /**
     * get_visible_name() must return the label corresponding to the filearea.
     */
    public function test_get_visible_name_returns_correct_label(): void {
        $this->resetAfterTest();

        $fileinfo = $this->make_file_info();
        $result = $fileinfo->get_visible_name();

        $this->assertEquals(SURVEYPRO_ITEMCONTENTFILEAREA, $result);
    }
}
