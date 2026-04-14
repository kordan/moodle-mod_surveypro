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
 * Unit tests for mtemplate_apply
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for mtemplate_apply methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\mtemplate_apply::class)]
final class mtemplate_apply_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate mtemplate_apply with minimal dependencies.
     *
     * @return mtemplate_apply
     */
    private function make_template(): mtemplate_apply {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        return new mtemplate_apply($cm, $context, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for get_mtemplates()
    // -------------------------------------------------------------------------

    /**
     * get_mtemplates() must return an array.
     */
    public function test_get_mtemplates_returns_array(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_mtemplates();

        $this->assertIsArray($result);
    }

    /**
     * get_mtemplates() must return only enabled templates.
     */
    public function test_get_mtemplates_only_enabled(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_mtemplates();

        foreach ($result as $name => $unused) {
            $disabled = get_config('surveyprotemplate_' . $name, 'disabled');
            $this->assertFalse((bool)$disabled);
        }
    }

    /**
     * get_mtemplates() must return templates sorted alphabetically.
     */
    public function test_get_mtemplates_sorted(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_mtemplates();

        if (count($result) > 1) {
            $sorted = $result;
            asort($sorted);
            $this->assertEquals($sorted, $result);
        } else {
            $this->assertTrue(true);
        }
    }

    // -------------------------------------------------------------------------
    // Tests for set_mastertemplate()
    // -------------------------------------------------------------------------

    /**
     * set_mastertemplate() must execute without errors.
     */
    public function test_set_mastertemplate(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $template->set_mastertemplate('sometemplate');

        $this->assertTrue(true);
    }
}
