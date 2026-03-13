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

namespace mod_surveypro;

use mod_surveypro\tests\view_responsesubmit_test_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for resolve_ownership() and is_access_allowed().
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\view_responsesubmit::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\formbase::class)]
class view_responsesubmit_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create a minimal surveypro activity and return [$cm, $surveypro, $context].
     */
    private function create_surveypro_activity(): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        return [$cm, $surveypro, $context];
    }

    /**
     * Instantiate view_responsesubmit_test_helper with minimal dependencies.
     *
     * @param int $submissionid Optional submission ID, defaults to 0
     * @param string $role Role to enrol the user with. Defaults to 'student'.
     *
     * @return view_responsesubmit_test_helper
     */
    private function make_manager(int $submissionid = 0, string $role = 'student'): view_responsesubmit_test_helper {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role);
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $this->setUser($user);
        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $manager->set_submissionid($submissionid);

        return $manager;
    }

    // -------------------------------------------------------------------------
    // Tests for resolve_ownership()
    // -------------------------------------------------------------------------

    /**
     * When submissionid is 0 (new submission), both flags must be false.
     */
    public function test_resolve_ownership_no_submission(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager(0);
        [$ismine, $mysamegroup] = $manager->call_resolve_ownership();

        $this->assertFalse($ismine);
        $this->assertFalse($mysamegroup);
    }

    /**
     * When the submission belongs to the current user and there are no groups,
     * $ismine must be true and $mysamegroup must be true.
     */
    public function test_resolve_ownership_mine_no_groups(): void {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();

        [$cm, $surveypro, $context] = $this->create_surveypro_activity();

        $this->setUser($user);

        $submission = new \stdClass();
        $submission->surveyproid = $surveypro->id;
        $submission->userid = $user->id;
        $submission->timecreated = time();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $submission->id = $DB->insert_record('surveypro_submission', $submission);

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $manager->set_submissionid($submission->id);

        [$ismine, $mysamegroup] = $manager->call_resolve_ownership();

        $this->assertTrue($ismine);
        $this->assertTrue($mysamegroup);
    }

    /**
     * When the submission belongs to another user and there are no groups,
     * $ismine must be false and $mysamegroup must be true
     * (no group mode means everyone is in the same "group").
     */
    public function test_resolve_ownership_not_mine_no_groups(): void {
        global $DB;

        $this->resetAfterTest();

        $owner = $this->getDataGenerator()->create_user();
        $viewer = $this->getDataGenerator()->create_user();
        $this->setUser($viewer);

        [$cm, $surveypro, $context] = $this->create_surveypro_activity();

        $submission = new \stdClass();
        $submission->surveyproid = $surveypro->id;
        $submission->userid = $owner->id;
        $submission->timecreated = time();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $submission->id = $DB->insert_record('surveypro_submission', $submission);

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $manager->set_submissionid($submission->id);

        [$ismine, $mysamegroup] = $manager->call_resolve_ownership();

        $this->assertFalse($ismine);
        $this->assertTrue($mysamegroup);
    }

    /**
     * When the submission belongs to another user in a different group,
     * $ismine must be false and $mysamegroup must be false.
     *
     * Note: This test creates a course with SEPARATEGROUPS.
     * The resolve_ownership() method in surveypro calls groups_get_all_groups()
     * which uses $COURSE to determine the group mode of the current course.
     * When I run the surveypro code, $COURSE points to the current course but
     * in the unit test $COURSE points to an empty object or who knows what.
     * That’s why I have to set $COURSE = $course;
     */
    public function test_resolve_ownership_not_mine_different_group(): void {
        global $DB, $COURSE;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course([
            'groupmode' => SEPARATEGROUPS,
            'groupmodeforce' => 1,
        ]);
        $owner = $this->getDataGenerator()->create_user();
        $viewer = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user($owner->id, $course->id);
        $this->getDataGenerator()->enrol_user($viewer->id, $course->id);

        $group1 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $group2 = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $owner->id, 'groupid' => $group1->id]);
        $this->getDataGenerator()->create_group_member(['userid' => $viewer->id, 'groupid' => $group2->id]);

        $this->setAdminUser();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $submission = new \stdClass();
        $submission->surveyproid = $surveypro->id;
        $submission->userid = $owner->id;
        $submission->timecreated = time();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $submission->id = $DB->insert_record('surveypro_submission', $submission);

        $this->setUser($viewer);

        $COURSE = $course;

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $manager->set_submissionid($submission->id);

        [$ismine, $mysamegroup] = $manager->call_resolve_ownership();

        $this->assertFalse($ismine);
        $this->assertFalse($mysamegroup);
    }

    /**
     * A non-existing submissionid must throw moodle_exception.
     */
    public function test_resolve_ownership_invalid_submissionid(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager(999999);

        $this->expectException(\moodle_exception::class);
        $manager->call_resolve_ownership();
    }

    // -------------------------------------------------------------------------
    // Tests for is_access_allowed()
    // -------------------------------------------------------------------------

    /**
     * NEWRESPONSEMODE: a user with submit capability must be allowed.
     */
    public function test_is_access_allowed_newresponse_with_capability(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        $this->setAdminUser();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $manager->set_mode_for_test(SURVEYPRO_NEWRESPONSEMODE);
        $manager->set_submissionid(0);

        $result = $manager->call_is_access_allowed(false, false, null);
        $this->assertTrue($result);
    }

    /**
     * EDITMODE + STATUSINPROGRESS: owner must be allowed.
     */
    public function test_is_access_allowed_edit_inprogress_owner(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(SURVEYPRO_EDITMODE);

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $result = $manager->call_is_access_allowed(true, false, $submission);
        $this->assertTrue($result);
    }

    /**
     * EDITMODE + STATUSINPROGRESS: a user from a different group must NOT be allowed.
     */
    public function test_is_access_allowed_edit_inprogress_different_group(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(SURVEYPRO_EDITMODE);

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $result = $manager->call_is_access_allowed(false, false, $submission);
        $this->assertFalse($result);
    }

    /**
     * READONLYMODE + STATUSINPROGRESS: always denied.
     */
    public function test_is_access_allowed_readonly_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(SURVEYPRO_READONLYMODE);

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $result = $manager->call_is_access_allowed(true, true, $submission);
        $this->assertFalse($result);
    }

    /**
     * READONLYMODE + STATUSCLOSED: owner must be allowed.
     */
    public function test_is_access_allowed_readonly_closed_owner(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(SURVEYPRO_READONLYMODE);

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $result = $manager->call_is_access_allowed(true, false, $submission);
        $this->assertTrue($result);
    }

    /**
     * Unknown mode: must always return false.
     */
    public function test_is_access_allowed_unknown_mode(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_mode_for_test(999);

        $result = $manager->call_is_access_allowed(true, true, null);
        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Tests for is_access_allowed refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * Edit mode helper must deny when submission is null.
     */
    public function test_is_edit_access_allowed_null_submission_is_denied(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $capabilities = [
            'canseeotherssubmissions' => true,
            'caneditownsubmissions' => true,
            'caneditotherssubmissions' => true,
        ];

        $this->assertFalse($manager->call_is_edit_access_allowed(true, true, null, $capabilities));
    }

    /**
     * Edit mode helper must allow owner for inprogress submissions.
     */
    public function test_is_edit_access_allowed_owner_inprogress_is_allowed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $capabilities = [
            'canseeotherssubmissions' => false,
            'caneditownsubmissions' => false,
            'caneditotherssubmissions' => false,
        ];

        $this->assertTrue($manager->call_is_edit_access_allowed(true, false, $submission, $capabilities));
    }

    /**
     * Edit mode helper must use editothers capability for closed submissions.
     */
    public function test_is_edit_access_allowed_closed_samegroup_uses_editothers_capability(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;
        $capabilities = [
            'canseeotherssubmissions' => true,
            'caneditownsubmissions' => false,
            'caneditotherssubmissions' => true,
        ];

        $this->assertTrue($manager->call_is_edit_access_allowed(false, true, $submission, $capabilities));
    }

    /**
     * Readonly helper must deny inprogress submissions.
     */
    public function test_is_readonly_access_allowed_inprogress_is_denied(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $capabilities = [
            'canseeotherssubmissions' => true,
            'caneditownsubmissions' => true,
            'caneditotherssubmissions' => true,
        ];

        $this->assertFalse($manager->call_is_readonly_access_allowed(true, true, $submission, $capabilities));
    }

    /**
     * Readonly helper must allow same-group users when capability is present.
     */
    public function test_is_readonly_access_allowed_closed_samegroup_with_capability(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;
        $capabilities = [
            'canseeotherssubmissions' => true,
            'caneditownsubmissions' => false,
            'caneditotherssubmissions' => false,
        ];

        $this->assertTrue($manager->call_is_readonly_access_allowed(false, true, $submission, $capabilities));
    }

    // -------------------------------------------------------------------------
    // Tests for build_itemhelperinfo()
    // -------------------------------------------------------------------------

    /**
     * A single simple element must produce one entry in itemhelperinfo
     * with contentperelement['mainelement'] set.
     */
    public function test_build_itemhelperinfo_single_simple_element(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'surveypro_field_character_101'} = 'hello';
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayHasKey(101, $result);
        $this->assertEquals('field', $result[101]->type);
        $this->assertEquals('character', $result[101]->plugin);
        $this->assertEquals(101, $result[101]->itemid);
        $this->assertEquals('hello', $result[101]->contentperelement['mainelement']);
    }

    /**
     * An element with an option suffix (e.g. datetime with _day, _month, _year)
     * must produce one entry with multiple contentperelement keys.
     */
    public function test_build_itemhelperinfo_multi_element(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'surveypro_field_datetime_102_day'} = 15;
        $formdata->{'surveypro_field_datetime_102_month'} = 6;
        $formdata->{'surveypro_field_datetime_102_year'} = 2024;
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayHasKey(102, $result);
        $this->assertEquals('datetime', $result[102]->plugin);
        $this->assertEquals(15, $result[102]->contentperelement['day']);
        $this->assertEquals(6, $result[102]->contentperelement['month']);
        $this->assertEquals(2024, $result[102]->contentperelement['year']);
    }

    /**
     * A placeholder element without its corresponding real field
     * must produce one entry with contentperelement['mainelement'] = null.
     */
    public function test_build_itemhelperinfo_placeholder_without_real_field(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'placeholder_field_character_103_placeholder'} = 1;
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayHasKey(103, $result);
        $this->assertNull($result[103]->contentperelement['mainelement']);
    }

    /**
     * A placeholder element WITH its corresponding real field already present
     * must NOT override the real field value.
     */
    public function test_build_itemhelperinfo_placeholder_with_real_field(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'placeholder_field_character_104_placeholder'} = 1;
        $formdata->{'surveypro_field_character_104'} = 'real value';
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayHasKey(104, $result);
        $this->assertEquals('real value', $result[104]->contentperelement['mainelement']);
    }

    /**
     * Elements with SURVEYPRO_DONTSAVEMEPREFIX must be ignored.
     */
    public function test_build_itemhelperinfo_dontsaveme_prefix_is_ignored(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'nosave_field_character_105'} = 'ignored';
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayNotHasKey(105, $result);
    }

    /**
     * Elements of type SURVEYPRO_TYPEFORMAT must be ignored.
     */
    public function test_build_itemhelperinfo_format_type_is_ignored(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'surveypro_format_fieldset_106'} = 1;
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertArrayNotHasKey(106, $result);
    }

    /**
     * Non-item fields like buttons and 's' must be silently ignored.
     */
    public function test_build_itemhelperinfo_non_item_fields_are_ignored(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->savebutton = 1;
        $formdata->nextbutton = 1;
        $formdata->formpage = 2;
        $formdata->s = 42;
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertEmpty($result);
    }

    /**
     * Multiple items mixed together must each produce their own entry.
     */
    public function test_build_itemhelperinfo_multiple_items(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $formdata = new \stdClass();
        $formdata->{'surveypro_field_character_201'} = 'foo';
        $formdata->{'surveypro_field_integer_202'} = 42;
        $formdata->{'surveypro_field_datetime_203_day'} = 10;
        $manager->formdata = $formdata;

        $result = $manager->call_build_itemhelperinfo();

        $this->assertCount(3, $result);
        $this->assertArrayHasKey(201, $result);
        $this->assertArrayHasKey(202, $result);
        $this->assertArrayHasKey(203, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_required_items()
    // -------------------------------------------------------------------------

    /**
     * A surveypro with no items must return an empty array.
     */
    public function test_get_required_items_no_items(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $result = $manager->call_get_required_items();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * A surveypro with one mandatory item must return exactly that item.
     */
    public function test_get_required_items_one_mandatory_item(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1]);

        $result = $manager->call_get_required_items();

        $this->assertCount(1, $result);
    }

    /**
     * A surveypro with one optional item must return an empty array.
     */
    public function test_get_required_items_optional_item_not_returned(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 0]);

        $result = $manager->call_get_required_items();

        $this->assertEmpty($result);
    }

    /**
     * A hidden mandatory item must NOT be returned.
     */
    public function test_get_required_items_hidden_mandatory_not_returned(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1, 'hidden' => 1]);

        $result = $manager->call_get_required_items();

        $this->assertEmpty($result);
    }

    /**
     * A reserved mandatory item must NOT be returned to a user without the accessreserveditems capability.
     */
    public function test_get_required_items_reserved_not_returned_without_capability(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager(0, 'student');
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1, 'reserved' => 1]);

        $result = $manager->call_get_required_items();

        $this->assertEmpty($result);
    }

    /**
     * A reserved mandatory item MUST be returned to a user with the accessreserveditems capability.
     */
    public function test_get_required_items_reserved_returned_with_capability(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager(0, 'editingteacher');
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1, 'reserved' => 1]);

        $result = $manager->call_get_required_items();

        $this->assertCount(1, $result);
    }

    /**
     * Multiple mandatory items must all be returned.
     */
    public function test_get_required_items_multiple_mandatory_items(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1]);
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1]);
        $generator->create_item_character($manager->get_surveypro(), ['required' => 0]);

        $result = $manager->call_get_required_items();

        $this->assertCount(2, $result);
    }

    /**
     * Each returned item must have the expected fields: id, parentid, parentvalue.
     */
    public function test_get_required_items_returned_item_has_expected_fields(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $manager = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($manager->get_surveypro(), ['required' => 1]);

        $result = $manager->call_get_required_items();
        $item = reset($result);

        $this->assertObjectHasProperty('id', $item);
        $this->assertObjectHasProperty('parentid', $item);
        $this->assertObjectHasProperty('parentvalue', $item);
        $this->assertObjectNotHasProperty('reserved', $item);
    }

    // -------------------------------------------------------------------------
    // Tests for formbase getters and setters
    // -------------------------------------------------------------------------

    /**
     * set_submissionid() and get_submissionid() must work correctly.
     */
    public function test_formbase_submissionid(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_submissionid(42);

        $this->assertEquals(42, $manager->get_submissionid());
    }

    /**
     * set_formpage() and get_formpage() must work correctly.
     */
    public function test_formbase_formpage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_formpage(3);

        $this->assertEquals(3, $manager->get_formpage());
    }

    /**
     * set_userformpagecount() and get_userformpagecount() must work correctly.
     */
    public function test_formbase_userformpagecount(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_userformpagecount(5);

        $this->assertEquals(5, $manager->get_userformpagecount());
    }

    /**
     * set_nextpage() and get_nextpage() must work correctly.
     */
    public function test_formbase_nextpage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_nextpage(2);

        $this->assertEquals(2, $manager->get_nextpage());
    }

    /**
     * set_userfirstpage() and get_userfirstpage() must work correctly.
     */
    public function test_formbase_userfirstpage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_userfirstpage(1);

        $this->assertEquals(1, $manager->get_userfirstpage());
    }

    /**
     * set_userlastpage() and get_userlastpage() must work correctly.
     */
    public function test_formbase_userlastpage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_userlastpage(4);

        $this->assertEquals(4, $manager->get_userlastpage());
    }

    /**
     * set_overflowpage() and get_overflowpage() must work correctly.
     */
    public function test_formbase_overflowpage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_overflowpage(1);

        $this->assertEquals(1, $manager->get_overflowpage());
    }

    // -------------------------------------------------------------------------
    // Tests for get_message()
    // -------------------------------------------------------------------------

    public function test_get_message_default(): void {
        global $USER, $COURSE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $COURSE = $course;
        $this->setUser($user);

        $manager = new view_responsesubmit_test_helper($cm, $context, $surveypro);
        $result = $manager->get_message();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString($surveypro->name, $result);
    }

    public function test_get_message_with_mailcontent(): void {
        global $USER, $COURSE;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Mario', 'lastname' => 'Rossi']);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', [
            'course' => $course->id,
            'mailcontent' => 'Hello {FIRSTNAME} {LASTNAME}, thank you for submitting {SURVEYPRONAME}.',
        ]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $COURSE = $course;
        $this->setUser($user);

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
