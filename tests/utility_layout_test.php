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
 * Unit tests for utility_layout
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\tests\utility_layout_test_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for utility_layout methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\utility_layout::class)]
class utility_layout_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate utility_layout with minimal dependencies.
     *
     * @param array $surveyproparams Optional surveypro parameters.
     * @return array [$utility, $surveypro, $cm]
     */
    private function make_utility(array $surveyproparams = []): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module(
            'surveypro',
            array_merge(['course' => $course->id], $surveyproparams),
        );
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $utility = new utility_layout_test_helper($cm, $surveypro);

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
    // Tests for reset_pages()
    // -------------------------------------------------------------------------

    /**
     * reset_pages() must set formpage to 0 for all items of the surveypro.
     */
    public function test_reset_pages(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);

        // Set formpage to something non-zero first.
        $DB->set_field('surveypro_item', 'formpage', 3, ['surveyproid' => $surveypro->id]);

        $utility->reset_pages();

        $pages = $DB->get_fieldset_select(
            'surveypro_item',
            'formpage',
            'surveyproid = :surveyproid',
            ['surveyproid' => $surveypro->id],
        );
        foreach ($pages as $page) {
            $this->assertEquals(0, $page);
        }
    }

    // -------------------------------------------------------------------------
    // Tests for assign_pages()
    // -------------------------------------------------------------------------

    /**
     * assign_pages() with no items must return 1.
     */
    public function test_assign_pages_no_items(): void {
        $this->resetAfterTest();

        [$utility] = $this->make_utility();
        $result = $utility->assign_pages();

        $this->assertEquals(1, $result);
    }

    /**
     * assign_pages() with items and no pagebreak must assign page 1 to all items.
     */
    public function test_assign_pages_no_pagebreak(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);

        $result = $utility->assign_pages();

        $this->assertEquals(1, $result);
        $pages = $DB->get_fieldset_select(
            'surveypro_item',
            'formpage',
            'surveyproid = :surveyproid AND hidden = 0',
            ['surveyproid' => $surveypro->id]
        );
        foreach ($pages as $page) {
            $this->assertEquals(1, $page);
        }
    }

    /**
     * assign_pages() with a pagebreak must increment the page number.
     */
    public function test_assign_pages_with_pagebreak(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_pagebreak($surveypro);

        $generator->create_item_character($surveypro, ['required' => 0]);

        $result = $utility->assign_pages();

        $this->assertEquals(2, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for has_items()
    // -------------------------------------------------------------------------

    /**
     * With no items has_items() must return false.
     */
    public function test_has_items_no_items(): void {
        $this->resetAfterTest();

        [$utility] = $this->make_utility();

        $this->assertFalse($utility->has_items());
    }

    /**
     * With one visible item has_items() must return true.
     */
    public function test_has_items_with_visible_item(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);

        $this->assertTrue($utility->has_items());
    }

    /**
     * A hidden item must not be counted unless includehidden is true.
     */
    public function test_has_items_hidden_item(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0, 'hidden' => 1]);

        $this->assertFalse($utility->has_items());
        $this->assertTrue($utility->has_items(0, null, true));
    }

    /**
     * With returncount=true has_items() must return the count.
     */
    public function test_has_items_returncount(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);

        $this->assertEquals(2, $utility->has_items(0, null, false, false, true));
    }

    /**
     * Filtering by type must return only items of that type.
     */
    public function test_has_items_filtered_by_type(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);

        $this->assertTrue($utility->has_items(0, 'field'));
        $this->assertFalse($utility->has_items(0, 'format'));
    }

    // -------------------------------------------------------------------------
    // Tests for has_submissions()
    // -------------------------------------------------------------------------

    /**
     * With no submissions has_submissions() must return false.
     */
    public function test_has_submissions_no_submissions(): void {
        $this->resetAfterTest();

        global $USER;

        [$utility, $surveypro] = $this->make_utility();

        $this->assertFalse($utility->has_submissions());
    }

    /**
     * With one submission has_submissions() must return true.
     */
    public function test_has_submissions_with_submission(): void {
        $this->resetAfterTest();

        global $USER;

        [$utility, $surveypro] = $this->make_utility();
        $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);

        $this->assertTrue($utility->has_submissions());
    }

    /**
     * With returncount=true has_submissions() must return the count.
     */
    public function test_has_submissions_returncount(): void {
        $this->resetAfterTest();

        global $USER;

        [$utility, $surveypro] = $this->make_utility();
        $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);

        $this->assertEquals(2, $utility->has_submissions(true));
    }

    /**
     * Filtering by status must return only submissions with that status.
     */
    public function test_has_submissions_filtered_by_status(): void {
        $this->resetAfterTest();

        global $USER;

        [$utility, $surveypro] = $this->make_utility();
        $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSINPROGRESS);

        $this->assertEquals(1, $utility->has_submissions(true, SURVEYPRO_STATUSCLOSED));
        $this->assertEquals(1, $utility->has_submissions(true, SURVEYPRO_STATUSINPROGRESS));
    }

    // -------------------------------------------------------------------------
    // Tests for can_submit_more()
    // -------------------------------------------------------------------------

    /**
     * A user with submit capability and no maxentries limit can always submit more.
     */
    public function test_can_submit_more_no_limit(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'maxentries' => 0,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $this->setUser($user);

        $utility = new utility_layout($cm, $surveypro);
        $this->assertTrue($utility->can_submit_more($user->id));
    }

    /**
     * A user who has reached maxentries must not be able to submit more.
     */
    public function test_can_submit_more_reached_limit(): void {
        $this->resetAfterTest();

        global $USER;

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'maxentries' => 1,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $this->setUser($user);

        $this->make_submission($surveypro->id, $user->id, SURVEYPRO_STATUSCLOSED);

        $utility = new utility_layout($cm, $surveypro);
        $this->assertFalse($utility->can_submit_more($user->id));
    }

    // -------------------------------------------------------------------------
    // Tests for is_newresponse_allowed()
    // -------------------------------------------------------------------------

    /**
     * A student with submit capability and items must be allowed to add a new response.
     */
    public function test_is_newresponse_allowed_basic(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $this->setUser($user);

        $utility = new utility_layout($cm, $surveypro);
        $this->assertTrue($utility->is_newresponse_allowed(1));
    }

    /**
     * When maxentries is reached is_newresponse_allowed() must return false.
     */
    public function test_is_newresponse_allowed_maxentries_reached(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'maxentries' => 2,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $this->setUser($user);

        $utility = new utility_layout($cm, $surveypro);
        // $next = 3 means the user already has 2 submissions.
        $this->assertFalse($utility->is_newresponse_allowed(3));
    }

    /**
     * When surveypro is not yet open is_newresponse_allowed() must return false.
     */
    public function test_is_newresponse_allowed_not_yet_open(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'timeopen' => time() + 3600, // Opens in 1 hour.
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $this->setUser($user);

        $utility = new utility_layout($cm, $surveypro);
        $this->assertFalse($utility->is_newresponse_allowed(1));
    }

    /**
     * When surveypro is closed is_newresponse_allowed() must return false.
     */
    public function test_is_newresponse_allowed_already_closed(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'timeclose' => time() - 3600, // Closed 1 hour ago.
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $this->setUser($user);

        $utility = new utility_layout($cm, $surveypro);
        $this->assertFalse($utility->is_newresponse_allowed(1));
    }

    // -------------------------------------------------------------------------
    // Tests for items_reindex()
    // -------------------------------------------------------------------------

    /**
     * items_reindex() must assign sequential sortindex starting from 1.
     */
    public function test_items_reindex_sequential(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);

        // Mess up sortindex.
        $items = $DB->get_records('surveypro_item', ['surveyproid' => $surveypro->id], 'sortindex');
        $i = 10;
        foreach ($items as $item) {
            $DB->set_field('surveypro_item', 'sortindex', $i, ['id' => $item->id]);
            $i += 10;
        }

        $utility->items_reindex();

        $sortindexes = $DB->get_fieldset_select(
            'surveypro_item',
            'sortindex',
            'surveyproid = :surveyproid ORDER BY sortindex',
            ['surveyproid' => $surveypro->id]
        );

        $this->assertEquals([1, 2, 3], $sortindexes);
    }

    // -------------------------------------------------------------------------
    // Tests for delete_submissions()
    // -------------------------------------------------------------------------

    /**
     * Create an answer record in the DB.
     *
     * @param int $submissionid
     * @param int $itemid
     * @return \stdClass
     */
    private function make_answer(int $submissionid, int $itemid): \stdClass {
        global $DB;

        $answer = new \stdClass();
        $answer->submissionid = $submissionid;
        $answer->itemid = $itemid;
        $answer->verified = 1;
        $answer->timecreated = time();
        $answer->content = 'test';
        $answer->id = $DB->insert_record('surveypro_answer', $answer);

        return $answer;
    }

    /**
     * delete_submissions() must remove the submission and its answers from the DB.
     */
    public function test_delete_submissions_removes_submission(): void {
        $this->resetAfterTest();

        global $DB, $USER, $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $this->make_answer($submission->id, $itemid);

        $utility->delete_submissions(['id' => $submission->id]);

        $this->assertFalse($DB->record_exists('surveypro_submission', ['id' => $submission->id]));
    }

    /**
     * delete_submissions() with surveyproid must remove all submissions of that surveypro.
     */
    public function test_delete_submissions_all_by_surveyproid(): void {
        $this->resetAfterTest();

        global $DB, $USER, $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $submission1 = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $submission2 = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSINPROGRESS);
        $this->make_answer($submission1->id, $itemid);
        $this->make_answer($submission2->id, $itemid);

        $utility->delete_submissions(['surveyproid' => $surveypro->id]);

        $count = $DB->count_records('surveypro_submission', ['surveyproid' => $surveypro->id]);
        $this->assertEquals(0, $count);
    }

    // -------------------------------------------------------------------------
    // Tests for has_search_items()
    // -------------------------------------------------------------------------

    /**
     * With no search items has_search_items() must return false.
     */
    public function test_has_search_items_no_items(): void {
        $this->resetAfterTest();

        [$utility] = $this->make_utility();
        $this->assertFalse($utility->has_search_items());
    }

    /**
     * With one item in search form has_search_items() must return true.
     */
    public function test_has_search_items_with_item(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid1 = $generator->create_item_character($surveypro, ['required' => 0]);
        $itemid2 = $generator->create_item_character($surveypro, ['required' => 0]);
        $DB->set_field('surveypro_item', 'insearchform', 1, ['id' => $itemid1]);
        $DB->set_field('surveypro_item', 'insearchform', 1, ['id' => $itemid2]);

        $this->assertEquals(2, $utility->has_search_items(true));
    }

    /**
     * With returncount=true has_search_items() must return the count.
     */
    public function test_has_search_items_returncount(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $DB->set_field('surveypro_item', 'insearchform', 1, ['id' => $itemid]);

        $this->assertTrue($utility->has_search_items());
    }

    /**
     * Items not in search form must not be counted by has_search_items().
     */
    public function test_has_search_items_not_in_searchform(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0, 'insearchform' => 0]);

        $this->assertFalse($utility->has_search_items());
    }

    // -------------------------------------------------------------------------
    // Tests for delete_items()
    // -------------------------------------------------------------------------

    /**
     * delete_items() must remove the item and its answers from the DB.
     */
    public function test_delete_items_removes_item(): void {
        $this->resetAfterTest();

        global $DB, $USER, $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $this->make_answer($submission->id, $itemid);

        $utility->delete_items(['surveyproid' => $surveypro->id, 'id' => $itemid]);

        $this->assertFalse($DB->record_exists('surveypro_item', ['id' => $itemid]));
    }

    /**
     * delete_items() must also delete answers linked to the deleted item.
     */
    public function test_delete_items_removes_answers(): void {
        $this->resetAfterTest();

        global $DB, $USER, $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $submission = $this->make_submission($surveypro->id, $USER->id, SURVEYPRO_STATUSCLOSED);
        $this->make_answer($submission->id, $itemid);

        $utility->delete_items(['surveyproid' => $surveypro->id, 'id' => $itemid]);

        $this->assertEquals(0, $DB->count_records('surveypro_answer', ['itemid' => $itemid]));
    }

    /**
     * delete_items() with no matching items must not throw.
     */
    public function test_delete_items_no_items(): void {
        $this->resetAfterTest();

        global $COURSE;

        [$utility, $surveypro, $cm] = $this->make_utility();
        $COURSE = get_course($cm->course);

        // No items exist — should not throw.
        $utility->delete_items(['surveyproid' => $surveypro->id]);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Tests for items_set_visibility()
    // -------------------------------------------------------------------------

    /**
     * items_set_visibility() with visibility=0 must set hidden=1 on items.
     */
    public function test_items_set_visibility_hide(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);
        $generator->create_item_character($surveypro, ['required' => 0]);

        $utility->items_set_visibility(['surveyproid' => $surveypro->id], 0);

        $hidden = $DB->count_records('surveypro_item', [
            'surveyproid' => $surveypro->id,
            'hidden' => 1,
        ]);
        $this->assertEquals(2, $hidden);
    }

    /**
     * items_set_visibility() with visibility=1 must set hidden=0 on items.
     */
    public function test_items_set_visibility_show(): void {
        $this->resetAfterTest();

        global $DB;

        [$utility, $surveypro] = $this->make_utility();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0, 'hidden' => 1]);
        $generator->create_item_character($surveypro, ['required' => 0, 'hidden' => 1]);

        $utility->items_set_visibility(['surveyproid' => $surveypro->id, 'hidden' => 1], 1);

        $visible = $DB->count_records('surveypro_item', [
            'surveyproid' => $surveypro->id,
            'hidden' => 0,
        ]);
        $this->assertEquals(2, $visible);
    }

    // -------------------------------------------------------------------------
    // Tests for has_items() refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * build_has_items_whereparams() must include defaults and visibility filters.
     */
    public function test_build_has_items_whereparams_defaults(): void {
        $this->resetAfterTest();

        [$utility, $surveypro] = $this->make_utility();
        $where = $utility->call_build_has_items_whereparams(0, null, false, false);

        $this->assertEquals($surveypro->id, $where['surveyproid']);
        $this->assertEquals(0, $where['hidden']);
        $this->assertEquals(0, $where['reserved']);
        $this->assertArrayNotHasKey('type', $where);
        $this->assertArrayNotHasKey('formpage', $where);
    }

    /**
     * build_has_items_whereparams() must include optional filters when provided.
     */
    public function test_build_has_items_whereparams_with_filters(): void {
        $this->resetAfterTest();

        [$utility] = $this->make_utility();
        $where = $utility->call_build_has_items_whereparams(3, 'field', true, true);

        $this->assertEquals('field', $where['type']);
        $this->assertEquals(3, $where['formpage']);
        $this->assertArrayNotHasKey('hidden', $where);
        $this->assertArrayNotHasKey('reserved', $where);
    }
}
