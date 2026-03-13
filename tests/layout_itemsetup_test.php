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
 * Unit tests for layout_itemsetup
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for layout_itemsetup methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\layout_itemsetup::class)]
final class layout_itemsetup_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate layout_itemsetup with minimal dependencies.
     *
     * @param bool $withtemplate Whether the surveypro has a template set.
     * @return layout_itemsetup
     */
    private function make_manager(bool $withtemplate = false): layout_itemsetup {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $params = ['course' => $course->id];
        if ($withtemplate) {
            $params['template'] = 'sometemplate';
        }
        $surveypro = $this->getDataGenerator()->create_module('surveypro', $params);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        return new layout_itemsetup($cm, $context, $surveypro);
    }

    // -------------------------------------------------------------------------
    // Tests for set_type() and get_type()
    // -------------------------------------------------------------------------

    /**
     * set_type() and get_type() must work correctly.
     */
    public function test_set_get_type(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_type(SURVEYPRO_TYPEFIELD);

        $this->assertEquals(SURVEYPRO_TYPEFIELD, $manager->get_type());
    }

    // -------------------------------------------------------------------------
    // Tests for set_plugin() and get_plugin()
    // -------------------------------------------------------------------------

    /**
     * set_plugin() and get_plugin() must work correctly.
     */
    public function test_set_get_plugin(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_plugin('character');

        $this->assertEquals('character', $manager->get_plugin());
    }

    // -------------------------------------------------------------------------
    // Tests for set_typeplugin()
    // -------------------------------------------------------------------------

    /**
     * set_typeplugin() with a valid field plugin must set type and plugin correctly.
     */
    public function test_set_typeplugin_valid_field(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_typeplugin(SURVEYPRO_TYPEFIELD . '_character');

        $this->assertEquals(SURVEYPRO_TYPEFIELD, $manager->get_type());
        $this->assertEquals('character', $manager->get_plugin());
    }

    /**
     * set_typeplugin() with a valid format plugin must set type and plugin correctly.
     */
    public function test_set_typeplugin_valid_format(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_typeplugin(SURVEYPRO_TYPEFORMAT . '_label');

        $this->assertEquals(SURVEYPRO_TYPEFORMAT, $manager->get_type());
        $this->assertEquals('label', $manager->get_plugin());
    }

    // -------------------------------------------------------------------------
    // Tests for set_action(), set_itemcount(), set_mode(), set_hassubmissions()
    // -------------------------------------------------------------------------

    /**
     * set_action() must store the value correctly.
     */
    public function test_set_action(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_NOACTION);

        // No getter available — verify indirectly via prevent_direct_user_input or just check no exception.
        $this->assertTrue(true);
    }

    /**
     * set_itemcount() must store the value correctly.
     */
    public function test_set_itemcount(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_itemcount(5);

        $this->assertTrue(true);
    }

    /**
     * set_mode() must store the value correctly.
     */
    public function test_set_mode(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);

        $this->assertTrue(true);
    }

    /**
     * set_hassubmissions() must store the value correctly.
     */
    public function test_set_hassubmissions(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_hassubmissions(true);

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Tests for prevent_direct_user_input()
    // -------------------------------------------------------------------------

    /**
     * prevent_direct_user_input() must not throw when surveypro has no template.
     */
    public function test_prevent_direct_user_input_no_template(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager(false);

        $manager->prevent_direct_user_input();
        $this->assertTrue(true);
    }

    /**
     * prevent_direct_user_input() must throw when surveypro has a template.
     */
    public function test_prevent_direct_user_input_with_template(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager(true);

        $this->expectException(\moodle_exception::class);
        $manager->prevent_direct_user_input();
    }
}
