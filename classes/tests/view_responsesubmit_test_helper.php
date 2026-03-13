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
 * Unit tests for view_responsesubmit
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass exposing protected methods for testing.
 */
class view_responsesubmit_test_helper extends \mod_surveypro\view_responsesubmit {
    /**
     * Public wrapper to expose the surveypro object.
     *
     * @return \stdClass
     */
    public function get_surveypro(): \stdClass {
        return $this->surveypro;
    }

    /**
     * Public wrapper to expose the protected resolve_ownership() method for testing.
     *
     * @return array the resolve_ownership array
     */
    public function call_resolve_ownership(): array {
        return $this->resolve_ownership();
    }

    /**
     * Public wrapper to expose the protected is_access_allowed() method for testing.
     *
     * @param bool $ismine
     * @param bool $mysamegroup
     * @param \stdClass|null $submission
     *
     * @return bool
     */
    public function call_is_access_allowed(bool $ismine, bool $mysamegroup, ?\stdClass $submission): bool {
        return $this->is_access_allowed($ismine, $mysamegroup, $submission);
    }

    /**
     * Public wrapper to expose is_edit_access_allowed() for testing.
     *
     * @param bool $ismine
     * @param bool $mysamegroup
     * @param \stdClass|null $submission
     * @param array $capabilities
     * @return bool
     */
    public function call_is_edit_access_allowed(
        bool $ismine,
        bool $mysamegroup,
        ?\stdClass $submission,
        array $capabilities
    ): bool {
        return $this->is_edit_access_allowed($ismine, $mysamegroup, $submission, $capabilities);
    }

    /**
     * Public wrapper to expose is_readonly_access_allowed() for testing.
     *
     * @param bool $ismine
     * @param bool $mysamegroup
     * @param \stdClass|null $submission
     * @param array $capabilities
     * @return bool
     */
    public function call_is_readonly_access_allowed(
        bool $ismine,
        bool $mysamegroup,
        ?\stdClass $submission,
        array $capabilities
    ): bool {
        return $this->is_readonly_access_allowed($ismine, $mysamegroup, $submission, $capabilities);
    }

    /**
     * Public wrapper to expose the protected build_itemhelperinfo() method for testing.
     *
     * @return array the itemhelperinfo array
     */
    public function call_build_itemhelperinfo(): array {
        return $this->build_itemhelperinfo();
    }

    /**
     * Public wrapper to expose the protected get_required_items() method for testing.
     *
     * @return array the list of required items for this surveypro
     */
    public function call_get_required_items(): array {
        return $this->get_required_items();
    }

    /**
     * Public wrapper to expose the protected set_mode() method for testing.
     *
     * @param int $mode
     * @return void
     */
    public function set_mode_for_test(int $mode): void {
        $this->mode = $mode;
    }

    // -------------------------------------------------------------------------
    // Tests for get_message()
    // -------------------------------------------------------------------------

    /**
     * get_message() without mailcontent must return the default newsubmissionbody string.
     */
    public function test_get_message_default(): void {
        global $COURSE;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $this->setUser($user);
        $COURSE = $course;

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $result = $manager->get_message();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString($surveypro->name, $result);
    }

    /**
     * get_message() with mailcontent must replace placeholders.
     */
    public function test_get_message_with_mailcontent(): void {
        global $COURSE;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Mario', 'lastname' => 'Rossi']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'mailcontent' => 'Hello {FIRSTNAME} {LASTNAME}, thank you for submitting {SURVEYPRONAME}.',
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $this->setUser($user);
        $COURSE = $course;

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $result = $manager->get_message();

        $this->assertStringContainsString('Mario', $result);
        $this->assertStringContainsString('Rossi', $result);
        $this->assertStringNotContainsString('{FIRSTNAME}', $result);
        $this->assertStringNotContainsString('{LASTNAME}', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_mode()
    // -------------------------------------------------------------------------

    /**
     * get_mode() must return the mode set via set_mode_for_test().
     */
    public function test_get_mode(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(SURVEYPRO_EDITMODE);

        $this->assertEquals(SURVEYPRO_EDITMODE, $manager->get_mode());
    }
}
