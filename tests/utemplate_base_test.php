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
 * Unit tests for utemplate_base
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for utemplate_base methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\utemplate_base::class)]
class utemplate_base_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate utemplate_base with minimal dependencies.
     *
     * @return utemplate_base
     */
    private function make_template(): utemplate_base {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        return new utemplate_base($cm, $context, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for get_label_forcontextid()
    // -------------------------------------------------------------------------

    /**
     * CONTEXT_SYSTEM must return the system label.
     */
    public function test_get_label_forcontextid_system(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_label_forcontextid(CONTEXT_SYSTEM);

        $this->assertEquals(get_string('system', 'mod_surveypro'), $result);
    }

    /**
     * CONTEXT_COURSECAT must return the current category label.
     */
    public function test_get_label_forcontextid_coursecat(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_label_forcontextid(CONTEXT_COURSECAT);

        $this->assertEquals(get_string('currentcategory', 'mod_surveypro'), $result);
    }

    /**
     * CONTEXT_COURSE must return the current course label.
     */
    public function test_get_label_forcontextid_course(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_label_forcontextid(CONTEXT_COURSE);

        $this->assertEquals(get_string('currentcourse', 'mod_surveypro'), $result);
    }

    /**
     * CONTEXT_MODULE must return the module label.
     */
    public function test_get_label_forcontextid_module(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_label_forcontextid(CONTEXT_MODULE);

        $a = get_string('modulename', 'mod_surveypro');
        $this->assertEquals(get_string('module', 'mod_surveypro', $a), $result);
    }

    /**
     * CONTEXT_USER must return the user label.
     */
    public function test_get_label_forcontextid_user(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_label_forcontextid(CONTEXT_USER);

        $this->assertEquals(get_string('user'), $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_sharingcontexts()
    // -------------------------------------------------------------------------

    /**
     * get_sharingcontexts() must return a non-empty array.
     */
    public function test_get_sharingcontexts_returns_array(): void {
        $this->resetAfterTest();

        $template = $this->make_template();
        $result = $template->get_sharingcontexts();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * get_sharingcontexts() must include the user context.
     */
    public function test_get_sharingcontexts_includes_user_context(): void {
        $this->resetAfterTest();

        global $USER;

        $template = $this->make_template();
        $result = $template->get_sharingcontexts();

        $usercontext = \context_user::instance($USER->id);
        $this->assertArrayHasKey($usercontext->id, $result);
    }

    /**
     * get_sharingcontexts() must include the module context.
     */
    public function test_get_sharingcontexts_includes_module_context(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $template = new utemplate_base($cm, $context, $surveypro);
        $result = $template->get_sharingcontexts();

        $this->assertArrayHasKey($context->id, $result);
    }
}
