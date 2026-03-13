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
 * Unit tests for utility_submission
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for utility_submission methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\utility_submission::class)]
final class utility_submission_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate utility_submission with minimal dependencies.
     *
     * @return array [$utility, $surveypro, $cm]
     */
    private function make_utility(): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $utility = new utility_submission($cm, $surveypro);

        return [$utility, $surveypro, $cm];
    }

    /**
     * Create a submission record in the DB.
     *
     * @param int $surveyproid
     * @param int $userid
     * @param int $status
     * @return \stdClass
     */
    private function make_submission(int $surveyproid, int $userid, int $status): \stdClass {
        global $DB;

        $submission = new \stdClass();
        $submission->surveyproid = $surveyproid;
        $submission->userid = $userid;
        $submission->timecreated = time();
        $submission->timemodified = 0;
        $submission->status = $status;
        $submission->id = $DB->insert_record('surveypro_submission', $submission);

        return $submission;
    }

    // -------------------------------------------------------------------------
    // Tests for submissions_set_status()
    // -------------------------------------------------------------------------

    /**
     * Setting status to closed must update the DB record.
     */
    public function test_submissions_set_status_to_closed(): void {
        $this->resetAfterTest();

        global $DB, $USER;

        [$utility, $surveypro] = $this->make_utility();
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSINPROGRESS);

        $utility->submissions_set_status(['id' => $submission->id], SURVEYPRO_STATUSCLOSED);

        $updated = $DB->get_record('surveypro_submission', ['id' => $submission->id]);
        $this->assertEquals(SURVEYPRO_STATUSCLOSED, $updated->status);
    }

    /**
     * Setting status to inprogress must update the DB record.
     */
    public function test_submissions_set_status_to_inprogress(): void {
        $this->resetAfterTest();

        global $DB, $USER;

        [$utility, $surveypro] = $this->make_utility();
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);

        $utility->submissions_set_status(['id' => $submission->id], SURVEYPRO_STATUSINPROGRESS);

        $updated = $DB->get_record('surveypro_submission', ['id' => $submission->id]);
        $this->assertEquals(SURVEYPRO_STATUSINPROGRESS, $updated->status);
    }

    /**
     * Without surveyproid in whereparams it must be added automatically.
     */
    public function test_submissions_set_status_adds_surveyproid(): void {
        $this->resetAfterTest();

        global $DB, $USER;

        [$utility, $surveypro] = $this->make_utility();
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSINPROGRESS);

        // Pass whereparams without surveyproid.
        $utility->submissions_set_status(['id' => $submission->id], SURVEYPRO_STATUSCLOSED);

        $updated = $DB->get_record('surveypro_submission', ['id' => $submission->id]);
        $this->assertEquals(SURVEYPRO_STATUSCLOSED, $updated->status);
    }

    /**
     * Empty whereparams must not throw and must use surveyproid as filter.
     */
    public function test_submissions_set_status_empty_whereparams(): void {
        $this->resetAfterTest();

        global $DB, $USER;

        [$utility, $surveypro] = $this->make_utility();
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSINPROGRESS);

        $utility->submissions_set_status([], SURVEYPRO_STATUSCLOSED);

        $updated = $DB->get_record('surveypro_submission', ['id' => $submission->id]);
        $this->assertEquals(SURVEYPRO_STATUSCLOSED, $updated->status);
    }

    // -------------------------------------------------------------------------
    // Tests for get_submissions_warning()
    // -------------------------------------------------------------------------

    /**
     * The warning message must always contain the base alert string.
     */
    public function test_get_submissions_warning_contains_base_message(): void {
        $this->resetAfterTest();

        global $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);

        $message = $utility->get_submissions_warning();

        $this->assertStringContainsString(
            get_string('hassubmissions_alert', 'mod_surveypro'),
            $message
        );
    }

    /**
     * When keepinprogress is disabled the danger message must be included.
     */
    public function test_get_submissions_warning_danger_when_no_keepinprogress(): void {
        $this->resetAfterTest();

        global $COURSE;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'keepinprogress' => 0,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $COURSE = $course;

        $utility = new utility_submission($cm, $surveypro);
        $message = $utility->get_submissions_warning();

        $this->assertStringContainsString(
            get_string('hassubmissions_danger', 'mod_surveypro'),
            $message
        );
    }

    /**
     * When keepinprogress is enabled the danger message must NOT be included.
     */
    public function test_get_submissions_warning_no_danger_when_keepinprogress(): void {
        $this->resetAfterTest();

        global $COURSE;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'keepinprogress' => 1,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $COURSE = $course;

        $utility = new utility_submission($cm, $surveypro);
        $message = $utility->get_submissions_warning();

        $this->assertStringNotContainsString(
            get_string('hassubmissions_danger', 'mod_surveypro'),
            $message
        );
    }

    // -------------------------------------------------------------------------
    // Tests for get_used_plugin_list()
    // -------------------------------------------------------------------------

    /**
     * With no items the plugin list must be empty.
     */
    public function test_get_used_plugin_list_no_items(): void {
        $this->resetAfterTest();

        [$utility] = $this->make_utility();
        $result = $utility->get_used_plugin_list();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * With one item the plugin list must contain that plugin.
     */
    public function test_get_used_plugin_list_one_item(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);

        $result = $utility->get_used_plugin_list();

        $this->assertContains('character', $result);
    }

    /**
     * With items of different plugins the list must contain all plugins without duplicates.
     */
    public function test_get_used_plugin_list_multiple_plugins(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_integer($surveypro, ['required' => 0]);

        $result = $utility->get_used_plugin_list();

        $this->assertContains('character', $result);
        $this->assertContains('integer', $result);
        $this->assertCount(2, $result);
    }

    /**
     * Filtering by type must return only plugins of that type.
     */
    public function test_get_used_plugin_list_filtered_by_type(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);

        $result = $utility->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        $this->assertContains('character', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_groupmates()
    // -------------------------------------------------------------------------

    /**
     * A user not in any group must get an empty groupmates list.
     */
    public function test_get_groupmates_no_groups(): void {
        $this->resetAfterTest();

        global $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);

        $result = $utility->get_groupmates();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * A user in a group must get the list of groupmates including themselves.
     */
    public function test_get_groupmates_with_group(): void {
        $this->resetAfterTest();

        global $COURSE;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);

        $group = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user1->id, 'groupid' => $group->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user2->id, 'groupid' => $group->id]);

        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $COURSE = $course;
        $this->setUser($user1);

        $utility = new utility_submission($cm, $surveypro);
        $result = $utility->get_groupmates();

        $this->assertContains((int)$user1->id, $result);
        $this->assertContains((int)$user2->id, $result);
    }

    /**
     * A user in two groups must get groupmates from both groups.
     */
    public function test_get_groupmates_multiple_groups(): void {
        $this->resetAfterTest();

        global $COURSE;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $user3 = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user1->id, $course->id);
        $this->getDataGenerator()->enrol_user($user2->id, $course->id);
        $this->getDataGenerator()->enrol_user($user3->id, $course->id);

        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user1->id, 'groupid' => $group1->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user2->id, 'groupid' => $group1->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user1->id, 'groupid' => $group2->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $user3->id, 'groupid' => $group2->id]);

        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $COURSE = $course;
        $this->setUser($user1);

        $utility = new utility_submission($cm, $surveypro);
        $result = $utility->get_groupmates();

        $this->assertContains((int)$user1->id, $result);
        $this->assertContains((int)$user2->id, $result);
        $this->assertContains((int)$user3->id, $result);
    }
}
