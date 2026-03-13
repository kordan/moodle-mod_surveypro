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

use mod_surveypro\tests\view_responselist_test_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for resolve_ownership() and is_access_allowed().
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\view_responselist::class)]
final class view_responselist_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate view_responselist_test_helper with minimal dependencies.
     *
     * @param string $role Role to enrol the user with. Defaults to 'student'.
     *
     * @return view_responselist_test_helper
     */
    private function make_manager(string $role = 'student'): view_responselist_test_helper {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, $role);
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $this->setUser($user);
        $manager = new view_responselist_test_helper($cm, $context, $surveypro);

        return $manager;
    }

    /**
     * Instantiate view_responselist_test_helper with a course and multiple enrolled users.
     *
     * @param int $numusers Number of users to enrol as students. Defaults to 1.
     * @param string $managerrole Role of the user set as current user. Defaults to 'student'.
     *
     * @return array [$manager, $surveypro, $users]
     */
    private function make_manager_with_users(int $numusers = 1, string $managerrole = 'student'): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();

        $users = [];
        for ($i = 0; $i < $numusers; $i++) {
            $user = $this->getDataGenerator()->create_user();
            $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
            $users[] = $user;
        }

        // Create and enrol the manager user with the requested role.
        $manageruser = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($manageruser->id, $course->id, $managerrole);

        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $this->setUser($manageruser);
        $manager = new view_responselist_test_helper($cm, $context, $surveypro);

        return [$manager, $surveypro, $users];
    }

    /**
     * Helper to create a flexible_table mock with no filters and no sort.
     *
     * @return \flexible_table
     */
    private function make_table_mock(): \flexible_table {
        $table = $this->createMock(\flexible_table::class);
        $table->method('get_sql_where')->willReturn(['', []]);
        $table->method('get_sql_sort')->willReturn('');

        return $table;
    }

    /**
     * Helper to create a submission record in the DB.
     *
     * @param int $surveyproid
     * @param int $userid
     * @param int $status
     * @return \stdClass the created submission
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
    // Tests for get_columns_width()
    // -------------------------------------------------------------------------

    /**
     * The sum of the three column widths must equal 100%.
     */
    public function test_get_columns_width_sum_is_100(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        [$col1, $col2, $col3] = $manager->call_get_columns_width();
        $this->assertEqualsWithDelta(100.0, $col1 + $col2 + $col3, 0.01);
    }

    // -------------------------------------------------------------------------
    // Tests for get_header_text()
    // -------------------------------------------------------------------------

    /**
     * The header text must contain the formatted timecreated date.
     */
    public function test_get_header_text_contains_timecreated(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $timecreated = mktime(10, 0, 0, 6, 15, 2024);
        $manager = $this->make_manager();
        $result = $manager->call_get_header_text($user, $timecreated, 0);
        $this->assertStringContainsString(userdate($timecreated), $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_border_style()
    // -------------------------------------------------------------------------

    /**
     * The border style must have a 'T' key with the expected sub-keys.
     */
    public function test_get_border_style_has_expected_keys(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $border = $manager->call_get_border_style();

        $this->assertArrayHasKey('T', $border);
        $this->assertArrayHasKey('width', $border['T']);
        $this->assertArrayHasKey('color', $border['T']);
    }

    // -------------------------------------------------------------------------
    // Tests for get_columns_html()
    // -------------------------------------------------------------------------

    /**
     * The two-column template must contain the @@col1@@ and @@col2@@ placeholders.
     */
    public function test_get_columns_html_twocols_has_placeholders(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        [$twocols, $threecols] = $manager->call_get_columns_html();

        $this->assertStringContainsString('@@col1@@', $twocols);
        $this->assertStringContainsString('@@col2@@', $twocols);
        $this->assertStringNotContainsString('@@col3@@', $twocols);
    }

    /**
     * The three-column template must contain all three placeholders.
     */
    public function test_get_columns_html_threecols_has_placeholders(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        [$twocols, $threecols] = $manager->call_get_columns_html();

        $this->assertStringContainsString('@@col1@@', $threecols);
        $this->assertStringContainsString('@@col2@@', $threecols);
        $this->assertStringContainsString('@@col3@@', $threecols);
    }

    // -------------------------------------------------------------------------
    // Tests for get_row_permissions()
    // -------------------------------------------------------------------------

    /**
     * Owner + inprogress: view=false, edit=true, downloadpdf=false.
     */
    public function test_get_row_permissions_owner_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $permissions = $manager->call_get_row_permissions(true, false, $submission);

        $this->assertFalse($permissions['view']);
        $this->assertTrue($permissions['edit']);
        $this->assertFalse($permissions['downloadpdf']);
    }

    /**
     * Owner + closed: view=true.
     */
    public function test_get_row_permissions_owner_closed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $permissions = $manager->call_get_row_permissions(true, false, $submission);

        $this->assertTrue($permissions['view']);
    }

    /**
     * Not mine, same group, closed: view depends on canseeotherssubmissions.
     */
    public function test_get_row_permissions_notmine_samegroup_closed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $permissions = $manager->call_get_row_permissions(false, true, $submission);

        // Student does not have seeotherssubmissions by default.
        $this->assertFalse($permissions['view']);
    }

    /**
     * Not mine, same group, inprogress: view=false always.
     */
    public function test_get_row_permissions_notmine_samegroup_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $permissions = $manager->call_get_row_permissions(false, true, $submission);

        $this->assertFalse($permissions['view']);
    }

    /**
     * Not mine, different group: all permissions must be false.
     */
    public function test_get_row_permissions_notmine_differentgroup(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $permissions = $manager->call_get_row_permissions(false, false, $submission);

        $this->assertFalse($permissions['view']);
        $this->assertFalse($permissions['edit']);
        $this->assertFalse($permissions['duplicate']);
        $this->assertFalse($permissions['delete']);
        $this->assertFalse($permissions['downloadpdf']);
    }

    /**
     * Teacher (with seeotherssubmissions) + not mine + same group + closed: view=true.
     */
    public function test_get_row_permissions_teacher_notmine_samegroup_closed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $permissions = $manager->call_get_row_permissions(false, true, $submission);

        $this->assertTrue($permissions['view']);
    }

    /**
     * downloadpdf is always false for inprogress submissions, regardless of ownership.
     */
    public function test_get_row_permissions_downloadpdf_false_when_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $permissions = $manager->call_get_row_permissions(true, true, $submission);

        $this->assertFalse($permissions['downloadpdf']);
    }

    /**
     * Return value must always be an array with exactly the five expected keys.
     */
    public function test_get_row_permissions_returns_expected_keys(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $permissions = $manager->call_get_row_permissions(true, true, $submission);

        $this->assertArrayHasKey('view', $permissions);
        $this->assertArrayHasKey('edit', $permissions);
        $this->assertArrayHasKey('duplicate', $permissions);
        $this->assertArrayHasKey('delete', $permissions);
        $this->assertArrayHasKey('downloadpdf', $permissions);
        $this->assertCount(5, $permissions);
    }

    // -------------------------------------------------------------------------
    // Tests for submissions table refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * Table state must show owner columns when capability allows it.
     */
    public function test_get_submissions_table_state_showowner_with_capability(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('student');
        $state = $manager->call_get_submissions_table_state(true);

        $this->assertTrue($state['showowner']);
    }

    /**
     * Table columns must include timemodified when state enables it.
     */
    public function test_get_submissions_table_columns_includes_timemodified(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $columns = $manager->call_get_submissions_table_columns([
            'showowner' => true,
            'showtimemodified' => true,
        ]);

        $this->assertContains('picture', $columns);
        $this->assertContains('fullname', $columns);
        $this->assertContains('timemodified', $columns);
        $this->assertContains('actions', $columns);
    }

    /**
     * Table columns must not include timemodified when state disables it.
     */
    public function test_get_submissions_table_columns_excludes_timemodified(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $columns = $manager->call_get_submissions_table_columns([
            'showowner' => false,
            'showtimemodified' => false,
        ]);

        $this->assertNotContains('picture', $columns);
        $this->assertNotContains('fullname', $columns);
        $this->assertNotContains('timemodified', $columns);
        $this->assertContains('status', $columns);
        $this->assertContains('timecreated', $columns);
        $this->assertContains('actions', $columns);
    }

    /**
     * Submission flags must mark mine and samegroup with access all groups.
     */
    public function test_get_submission_flags_mine_with_accessallgroups(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->userid = 123;

        $flags = $manager->call_get_submission_flags($submission, 123, true, true, []);

        $this->assertTrue($flags['ismine']);
        $this->assertTrue($flags['mysamegroup']);
    }

    /**
     * Submission flags must use groupmates when groups are active.
     */
    public function test_get_submission_flags_notmine_samegroup_from_groupmates(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->userid = 456;

        $flags = $manager->call_get_submission_flags($submission, 123, false, true, [456, 789]);

        $this->assertFalse($flags['ismine']);
        $this->assertTrue($flags['mysamegroup']);
    }

    /**
     * Formatted row values must use "never" when timemodified is zero.
     */
    public function test_format_submission_row_values_uses_never_for_zero_timemodified(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;
        $submission->timecreated = mktime(10, 0, 0, 6, 15, 2024);
        $submission->timemodified = 0;

        $statusmap = [
            SURVEYPRO_STATUSINPROGRESS => 'In progress',
            SURVEYPRO_STATUSCLOSED => 'Closed',
        ];
        $values = $manager->call_format_submission_row_values($submission, $statusmap, 'never');

        $this->assertEquals('In progress', $values['status']);
        $this->assertEquals(userdate($submission->timecreated), $values['timecreated']);
        $this->assertEquals('never', $values['timemodified']);
    }

    /**
     * deleteall button must be visible when all conditions are satisfied.
     */
    public function test_is_deleteall_button_visible_true_when_all_conditions_met(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_searchquery('');

        $result = $manager->call_is_deleteall_button_visible(true, true, true, '', '', 2);

        $this->assertTrue($result);
    }

    /**
     * deleteall button must be hidden when search query is active.
     */
    public function test_is_deleteall_button_visible_false_with_searchquery(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_searchquery('abc');

        $result = $manager->call_is_deleteall_button_visible(true, true, true, '', '', 2);

        $this->assertFalse($result);
    }

    /**
     * deleteall button must be hidden when there are no submissions to delete.
     */
    public function test_is_deleteall_button_visible_false_when_next_is_one(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_searchquery('');

        $result = $manager->call_is_deleteall_button_visible(true, true, true, '', '', 1);

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_submissions_sql()
    // -------------------------------------------------------------------------

    /**
     * A student without seeotherssubmissions must get a SQL filtered on his own userid.
     */
    public function test_get_submissions_sql_student_sees_only_own(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('student');
        $table = $this->make_table_mock();

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringContainsString('ss.userid = :userid', $sql);
        $this->assertArrayHasKey('userid', $params);
    }

    /**
     * A teacher with seeotherssubmissions must get a SQL without userid filter.
     */
    public function test_get_submissions_sql_teacher_sees_all(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $table = $this->make_table_mock();

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringNotContainsString('ss.userid = :userid', $sql);
        $this->assertArrayNotHasKey('userid', $params);
    }

    /**
     * The SQL must always contain the surveyproid filter.
     */
    public function test_get_submissions_sql_always_filters_by_surveyproid(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->make_table_mock();

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringContainsString('ss.surveyproid = :surveyproid', $sql);
        $this->assertArrayHasKey('surveyproid', $params);
    }

    /**
     * When no sort is specified the SQL must default to ORDER BY ss.timecreated.
     */
    public function test_get_submissions_sql_default_sort(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->make_table_mock();

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringContainsString('ORDER BY ss.timecreated', $sql);
    }

    /**
     * When a sort is specified by the table it must be used in the SQL.
     */
    public function test_get_submissions_sql_custom_sort(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->createMock(\flexible_table::class);
        $table->method('get_sql_where')->willReturn(['', []]);
        $table->method('get_sql_sort')->willReturn('ss.timecreated DESC');

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringContainsString('ORDER BY ss.timecreated DESC', $sql);
        $this->assertStringNotContainsString('ORDER BY ss.timecreated', str_replace('ORDER BY ss.timecreated DESC', '', $sql));
    }

    /**
     * When a where filter is specified by the table it must be included in the SQL.
     */
    public function test_get_submissions_sql_table_where_filter(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->createMock(\flexible_table::class);
        $table->method('get_sql_where')->willReturn(['u.firstname = :firstname', ['firstname' => 'Mario']]);
        $table->method('get_sql_sort')->willReturn('');

        [$sql, $params] = $manager->get_submissions_sql($table);

        $this->assertStringContainsString('u.firstname = :firstname', $sql);
        $this->assertArrayHasKey('firstname', $params);
        $this->assertEquals('Mario', $params['firstname']);
    }

    // -------------------------------------------------------------------------
    // Tests for get_counter()
    // -------------------------------------------------------------------------

    /**
     * With no submissions the counters must all be zero.
     */
    public function test_get_counter_no_submissions(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        $this->assertEquals(0, $counter['closedsubmissions']);
        $this->assertEquals(0, $counter['closedusers']);
        $this->assertEquals(0, $counter['inprogresssubmissions']);
        $this->assertEquals(0, $counter['inprogressusers']);
    }

    /**
     * With one closed submission the closed counter must be 1.
     */
    public function test_get_counter_one_closed_submission(): void {
        $this->resetAfterTest();

        [$manager, $surveypro, $users] = $this->make_manager_with_users(1, 'editingteacher');
        $this->make_submission($surveypro->id, $users[0]->id, SURVEYPRO_STATUSCLOSED);
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        $this->assertEquals(1, $counter['closedsubmissions']);
        $this->assertEquals(1, $counter['closedusers']);
        $this->assertEquals(0, $counter['inprogresssubmissions']);
        $this->assertEquals(0, $counter['inprogressusers']);
    }

    /**
     * With one inprogress submission the inprogress counter must be 1.
     */
    public function test_get_counter_one_inprogress_submission(): void {
        $this->resetAfterTest();

        [$manager, $surveypro, $users] = $this->make_manager_with_users(1, 'editingteacher');
        $this->make_submission($surveypro->id, $users[0]->id, SURVEYPRO_STATUSINPROGRESS);
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        $this->assertEquals(0, $counter['closedsubmissions']);
        $this->assertEquals(1, $counter['inprogresssubmissions']);
        $this->assertEquals(1, $counter['inprogressusers']);
    }

    /**
     * With mixed submissions the counters must reflect the correct totals.
     */
    public function test_get_counter_mixed_submissions(): void {
        $this->resetAfterTest();

        [$manager, $surveypro, $users] = $this->make_manager_with_users(2, 'editingteacher');
        $this->make_submission($surveypro->id, $users[0]->id, SURVEYPRO_STATUSCLOSED);
        $this->make_submission($surveypro->id, $users[0]->id, SURVEYPRO_STATUSCLOSED);
        $this->make_submission($surveypro->id, $users[1]->id, SURVEYPRO_STATUSINPROGRESS);
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        $this->assertEquals(2, $counter['closedsubmissions']);
        $this->assertEquals(1, $counter['closedusers']);
        $this->assertEquals(1, $counter['inprogresssubmissions']);
        $this->assertEquals(1, $counter['inprogressusers']);
    }

    /**
     * The returned array must always have all the expected keys.
     */
    public function test_get_counter_returns_expected_keys(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        $this->assertArrayHasKey('enrolled', $counter);
        $this->assertArrayHasKey('allusers', $counter);
        $this->assertArrayHasKey('closedsubmissions', $counter);
        $this->assertArrayHasKey('closedusers', $counter);
        $this->assertArrayHasKey('inprogresssubmissions', $counter);
        $this->assertArrayHasKey('inprogressusers', $counter);
    }

    /**
     * The enrolled counter must reflect the number of enrolled users in the course.
     */
    public function test_get_counter_enrolled_users(): void {
        $this->resetAfterTest();

        [$manager, $surveypro, $users] = $this->make_manager_with_users(3, 'editingteacher');
        $table = $this->make_table_mock();

        $counter = $manager->get_counter($table);

        // At least 3 students + 1 editingteacher must be enrolled.
        $this->assertGreaterThanOrEqual(4, $counter['enrolled']);
    }

    // -------------------------------------------------------------------------
    // Tests for prevent_direct_user_input()
    // -------------------------------------------------------------------------

    /**
     * NOACTION must return without throwing.
     */
    public function test_prevent_direct_user_input_noaction(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_NOACTION);

        // No exception expected.
        $manager->call_prevent_direct_user_input(SURVEYPRO_UNCONFIRMED);
        $this->assertTrue(true);
    }

    /**
     * ACTION_EXECUTED must return without throwing regardless of action.
     */
    public function test_prevent_direct_user_input_action_executed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $manager->call_prevent_direct_user_input(SURVEYPRO_ACTION_EXECUTED);
        $this->assertTrue(true);
    }

    /**
     * CONFIRMED_NO must return without throwing regardless of action.
     */
    public function test_prevent_direct_user_input_confirmed_no(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $manager->call_prevent_direct_user_input(SURVEYPRO_CONFIRMED_NO);
        $this->assertTrue(true);
    }

    /**
     * DELETEALLRESPONSES without deleteotherssubmissions capability must throw.
     */
    public function test_prevent_direct_user_input_deleteall_without_capability(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('student');
        $manager->set_action(SURVEYPRO_DELETEALLRESPONSES);

        $this->expectException(\moodle_exception::class);
        $manager->call_prevent_direct_user_input(SURVEYPRO_UNCONFIRMED);
    }

    /**
     * DELETEALLRESPONSES with deleteotherssubmissions capability must not throw.
     */
    public function test_prevent_direct_user_input_deleteall_with_capability(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $manager->set_action(SURVEYPRO_DELETEALLRESPONSES);

        $manager->call_prevent_direct_user_input(SURVEYPRO_UNCONFIRMED);
        $this->assertTrue(true);
    }

    /**
     * DELETERESPONSE on own submission with capability must not throw.
     */
    public function test_prevent_direct_user_input_delete_own_with_capability(): void {
        $this->resetAfterTest();

        global $USER;

        $manager = $this->make_manager('editingteacher');
        $submission = $this->make_submission(
            $manager->get_surveypro()->id,
            $USER->id,
            SURVEYPRO_STATUSCLOSED
        );
        $manager->set_submissionid($submission->id);
        $manager->set_action(SURVEYPRO_DELETERESPONSE);
        $manager->set_view(SURVEYPRO_NOMODE);

        $manager->call_prevent_direct_user_input(SURVEYPRO_UNCONFIRMED);
        $this->assertTrue(true);
    }

    /**
     * DELETERESPONSE on non-existing submission must throw.
     */
    public function test_prevent_direct_user_input_delete_nonexisting_submission(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $manager->set_submissionid(999999);
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $this->expectException(\moodle_exception::class);
        $manager->call_prevent_direct_user_input(SURVEYPRO_UNCONFIRMED);
    }

    // -------------------------------------------------------------------------
    // Tests for prevent_direct_user_input refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * NOACTION should skip direct user input check.
     */
    public function test_should_skip_direct_user_input_check_noaction(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_NOACTION);

        $this->assertTrue($manager->call_should_skip_direct_user_input_check(SURVEYPRO_UNCONFIRMED));
    }

    /**
     * ACTION_EXECUTED should skip direct user input check.
     */
    public function test_should_skip_direct_user_input_check_action_executed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $this->assertTrue($manager->call_should_skip_direct_user_input_check(SURVEYPRO_ACTION_EXECUTED));
    }

    /**
     * CONFIRMED_NO should skip direct user input check.
     */
    public function test_should_skip_direct_user_input_check_confirmed_no(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $this->assertTrue($manager->call_should_skip_direct_user_input_check(SURVEYPRO_CONFIRMED_NO));
    }

    /**
     * Unconfirmed action different from NOACTION should not skip checks.
     */
    public function test_should_skip_direct_user_input_check_unconfirmed_requires_checks(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $manager->set_action(SURVEYPRO_DELETERESPONSE);

        $this->assertFalse($manager->call_should_skip_direct_user_input_check(SURVEYPRO_UNCONFIRMED));
    }

    /**
     * Action-level permission: owner can delete with own-delete capability.
     */
    public function test_is_action_allowed_for_submission_owner_delete_allowed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $ownership = ['ismine' => true, 'mysamegroup' => true];
        $permissions = [
            'candeleteownsubmissions' => true,
            'candeleteotherssubmissions' => false,
            'canduplicateownsubmissions' => false,
            'canduplicateotherssubmissions' => false,
            'cansavetopdfownsubmissions' => false,
            'cansavetopdfotherssubmissions' => false,
        ];

        $allowed = $manager->call_is_action_allowed_for_submission(
            SURVEYPRO_DELETERESPONSE,
            $submission,
            $ownership,
            $permissions
        );

        $this->assertTrue($allowed);
    }

    /**
     * Action-level permission: inprogress submission cannot be downloaded to PDF.
     */
    public function test_is_action_allowed_for_submission_pdf_denied_for_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $ownership = ['ismine' => true, 'mysamegroup' => true];
        $permissions = [
            'candeleteownsubmissions' => true,
            'candeleteotherssubmissions' => true,
            'canduplicateownsubmissions' => true,
            'canduplicateotherssubmissions' => true,
            'cansavetopdfownsubmissions' => true,
            'cansavetopdfotherssubmissions' => true,
        ];

        $allowed = $manager->call_is_action_allowed_for_submission(
            SURVEYPRO_RESPONSETOPDF,
            $submission,
            $ownership,
            $permissions
        );

        $this->assertFalse($allowed);
    }

    /**
     * View-level permission: readonly denied for inprogress submissions.
     */
    public function test_is_view_allowed_for_submission_readonly_denied_for_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSINPROGRESS;

        $ownership = ['ismine' => true, 'mysamegroup' => true];
        $permissions = [
            'canseeotherssubmissions' => true,
            'caneditownsubmissions' => true,
            'caneditotherssubmissions' => true,
        ];

        $allowed = $manager->call_is_view_allowed_for_submission(
            SURVEYPRO_READONLYMODE,
            $submission,
            $ownership,
            $permissions
        );

        $this->assertFalse($allowed);
    }

    /**
     * View-level permission: edit allowed for others in same group with capability.
     */
    public function test_is_view_allowed_for_submission_edit_allowed_for_samegroup_other(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $submission = new \stdClass();
        $submission->status = SURVEYPRO_STATUSCLOSED;

        $ownership = ['ismine' => false, 'mysamegroup' => true];
        $permissions = [
            'canseeotherssubmissions' => false,
            'caneditownsubmissions' => false,
            'caneditotherssubmissions' => true,
        ];

        $allowed = $manager->call_is_view_allowed_for_submission(
            SURVEYPRO_EDITMODE,
            $submission,
            $ownership,
            $permissions
        );

        $this->assertTrue($allowed);
    }

    // -------------------------------------------------------------------------
    // Tests for get_submissions_overview_data()
    // -------------------------------------------------------------------------

    /**
     * With no submissions all messages must be null and allsubmissions must be 0.
     */
    public function test_get_submissions_overview_data_no_submissions(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(0, 0, 0, 0, 0, 0);

        $this->assertEquals(0, $data['allsubmissions']);
        $this->assertNull($data['enrolledmessage']);
        $this->assertNull($data['totalmessage']);
        $this->assertNull($data['inprogressmessage']);
        $this->assertNull($data['closedmessage']);
    }

    /**
     * The returned array must always have all the expected keys.
     */
    public function test_get_submissions_overview_data_returns_expected_keys(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(0, 0, 0, 0, 0, 0);

        $this->assertArrayHasKey('enrolledmessage', $data);
        $this->assertArrayHasKey('allsubmissions', $data);
        $this->assertArrayHasKey('totalmessage', $data);
        $this->assertArrayHasKey('inprogressmessage', $data);
        $this->assertArrayHasKey('closedmessage', $data);
    }

    /**
     * allsubmissions must be the sum of closed and inprogress.
     */
    public function test_get_submissions_overview_data_allsubmissions_is_sum(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(3, 2, 4, 2, 3, 1);

        $this->assertEquals(7, $data['allsubmissions']);
    }

    /**
     * With submissions and no seeotherssubmissions capability enrolledmessage must be null.
     */
    public function test_get_submissions_overview_data_student_no_enrolledmessage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('student');
        $data = $manager->call_get_submissions_overview_data(3, 1, 2, 1, 1, 1);

        $this->assertNull($data['enrolledmessage']);
    }

    /**
     * With submissions and seeotherssubmissions capability enrolledmessage must not be null.
     */
    public function test_get_submissions_overview_data_teacher_has_enrolledmessage(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $data = $manager->call_get_submissions_overview_data(3, 1, 2, 1, 1, 1);

        $this->assertNotNull($data['enrolledmessage']);
    }

    /**
     * With 1 enrolled user the enrolledmessage must use the singular string.
     */
    public function test_get_submissions_overview_data_one_enrolled_user(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $data = $manager->call_get_submissions_overview_data(1, 1, 1, 1, 0, 0);

        $this->assertEquals(get_string('userenrolled', 'mod_surveypro'), $data['enrolledmessage']);
    }

    /**
     * With more than 1 enrolled user the enrolledmessage must use the plural string.
     */
    public function test_get_submissions_overview_data_many_enrolled_users(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager('editingteacher');
        $data = $manager->call_get_submissions_overview_data(3, 2, 1, 1, 0, 0);

        $this->assertEquals(get_string('usersenrolled', 'mod_surveypro', 3), $data['enrolledmessage']);
    }

    /**
     * With only inprogress submissions closedmessage must be null.
     */
    public function test_get_submissions_overview_data_only_inprogress(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(1, 1, 0, 0, 2, 1);

        $this->assertNull($data['closedmessage']);
        $this->assertNotNull($data['inprogressmessage']);
    }

    /**
     * With only closed submissions inprogressmessage must be null.
     */
    public function test_get_submissions_overview_data_only_closed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(1, 1, 2, 1, 0, 0);

        $this->assertNull($data['inprogressmessage']);
        $this->assertNotNull($data['closedmessage']);
    }

    /**
     * With both inprogress and closed submissions both messages must not be null.
     */
    public function test_get_submissions_overview_data_both_statuses(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(2, 2, 2, 1, 1, 1);

        $this->assertNotNull($data['inprogressmessage']);
        $this->assertNotNull($data['closedmessage']);
    }

    /**
     * totalmessage must not be null when there are submissions.
     */
    public function test_get_submissions_overview_data_totalmessage_not_null(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $data = $manager->call_get_submissions_overview_data(1, 1, 1, 1, 0, 0);

        $this->assertNotNull($data['totalmessage']);
    }

    // -------------------------------------------------------------------------
    // Tests for trigger_event()
    // -------------------------------------------------------------------------

    /**
     * trigger_event() must fire the all_submissions_viewed event.
     */
    public function test_trigger_event_fires_all_submissions_viewed(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $sink = $this->redirectEvents();
        $manager->trigger_event();
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(\mod_surveypro\event\all_submissions_viewed::class, $events[0]);
    }

    /**
     * The event must carry the correct surveypro id as objectid.
     */
    public function test_trigger_event_carries_correct_objectid(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $sink = $this->redirectEvents();
        $manager->trigger_event();
        $events = $sink->get_events();
        $sink->close();

        $this->assertEquals($manager->get_surveypro()->id, $events[0]->objectid);
    }

    // -------------------------------------------------------------------------
    // Tests for replace_http_url()
    // -------------------------------------------------------------------------

    /**
     * Content without any pluginfile URL must be returned unchanged.
     */
    public function test_replace_http_url_no_url(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $content = '<p>Simple text without any image.</p>';

        $result = $manager->call_replace_http_url($content);

        $this->assertEquals($content, $result);
    }

    /**
     * Content with a non-pluginfile URL must be returned unchanged.
     */
    public function test_replace_http_url_non_pluginfile_url(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();
        $content = '<p><img src="http://example.com/image.jpg" /></p>';

        $result = $manager->call_replace_http_url($content);

        $this->assertEquals($content, $result);
    }

    /**
     * Content with a pluginfile URL pointing to a non-existing file
     * must be returned unchanged because get_image_file() returns null.
     */
    public function test_replace_http_url_nonexisting_pluginfile(): void {
        $this->resetAfterTest();

        global $CFG;

        $manager = $this->make_manager();
        $content = '<p><img src="' . $CFG->wwwroot . '/pluginfile.php/999/mod_surveypro/itemcontent/0/fake.jpg" /></p>';

        $result = $manager->call_replace_http_url($content);

        $this->assertEquals($content, $result);
    }

    /**
     * Empty content must be returned unchanged.
     */
    public function test_replace_http_url_empty_content(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $result = $manager->call_replace_http_url('');

        $this->assertEquals('', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_image_file()
    // -------------------------------------------------------------------------

    /**
     * A URL not containing pluginfile.php must return null.
     */
    public function test_get_image_file_non_pluginfile_url(): void {
        $this->resetAfterTest();

        $manager = $this->make_manager();

        $result = $manager->call_get_image_file('http://example.com/image.jpg');

        $this->assertNull($result);
    }

    /**
     * A URL from a different component must return null.
     */
    public function test_get_image_file_wrong_component(): void {
        $this->resetAfterTest();

        global $CFG;

        $manager = $this->make_manager();
        $url = $CFG->wwwroot . '/pluginfile.php/999/mod_assign/itemcontent/0/fake.jpg';

        $result = $manager->call_get_image_file($url);

        $this->assertNull($result);
    }

    /**
     * A non-existing file must return null.
     */
    public function test_get_image_file_nonexisting_file(): void {
        $this->resetAfterTest();

        global $CFG;

        $manager = $this->make_manager();
        $url = $CFG->wwwroot . '/pluginfile.php/999/mod_surveypro/itemcontent/0/nonexistent.jpg';

        $result = $manager->call_get_image_file($url);

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_sqlanswer()
    // -------------------------------------------------------------------------

    /**
     * get_sqlanswer() with one search restriction must return SQL with HAVING matchcount = 1.
     */
    public function test_get_sqlanswer_one_restriction(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0, 'insearchform' => 1]);

        $manager = new view_responselist_test_helper($cm, $context, $surveypro);
        $whereparams = [];
        $searchrestrictions = [$itemid => 'hello'];

        $result = $manager->call_get_sqlanswer($searchrestrictions, $whereparams);

        $this->assertStringContainsString('HAVING COUNT(a.submissionid) = :matchcount', $result);
        $this->assertEquals(1, $whereparams['matchcount']);
        $this->assertArrayHasKey('content_' . $itemid, $whereparams);
    }

    /**
     * get_sqlanswer() with two search restrictions must return SQL with HAVING matchcount = 2.
     */
    public function test_get_sqlanswer_two_restrictions(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid1 = $generator->create_item_character($surveypro, ['required' => 0, 'insearchform' => 1]);
        $itemid2 = $generator->create_item_character($surveypro, ['required' => 0, 'insearchform' => 1]);

        $manager = new view_responselist_test_helper($cm, $context, $surveypro);
        $whereparams = [];
        $searchrestrictions = [$itemid1 => 'hello', $itemid2 => 'world'];

        $result = $manager->call_get_sqlanswer($searchrestrictions, $whereparams);

        $this->assertEquals(2, $whereparams['matchcount']);
        $this->assertArrayHasKey('content_' . $itemid1, $whereparams);
        $this->assertArrayHasKey('content_' . $itemid2, $whereparams);
    }

    /**
     * get_sqlanswer() must return SQL containing GROUP BY a.submissionid.
     */
    public function test_get_sqlanswer_contains_group_by(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0, 'insearchform' => 1]);

        $manager = new view_responselist_test_helper($cm, $context, $surveypro);
        $whereparams = [];
        $result = $manager->call_get_sqlanswer([$itemid => 'test'], $whereparams);

        $this->assertStringContainsString('GROUP BY a.submissionid', $result);
    }
}
