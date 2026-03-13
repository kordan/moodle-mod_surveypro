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
class view_responselist_test_helper extends \mod_surveypro\view_responselist {
    /**
     * Public wrapper to expose the surveypro object.
     *
     * @return \stdClass
     */
    public function get_surveypro(): \stdClass {
        return $this->surveypro;
    }

    /**
     * Public wrapper to expose the protected get_columns_width() method for testing.
     *
     * @return array the three column widths
     */
    public function call_get_columns_width(): array {
        return $this->get_columns_width();
    }

    /**
     * Public wrapper to expose the protected get_header_text() method for testing.
     *
     * @param \stdClass $user
     * @param int $timecreated
     * @param int $timemodified
     * @return string
     */
    public function call_get_header_text(\stdClass $user, int $timecreated, int $timemodified): string {
        return $this->get_header_text($user, $timecreated, $timemodified);
    }

    /**
     * Public wrapper to expose the protected get_border_style() method for testing.
     *
     * @return array the border style array
     */
    public function call_get_border_style(): array {
        return $this->get_border_style();
    }

    /**
     * Public wrapper to expose the protected get_columns_html() method for testing.
     *
     * @return array the two and three columns templates
     */
    public function call_get_columns_html(): array {
        return $this->get_columns_html();
    }

    /**
     * Public wrapper to expose the protected get_row_permissions() method for testing.
     *
     * @param bool $ismine
     * @param bool $mysamegroup
     * @param \stdClass $submission
     * @return array
     */
    public function call_get_row_permissions(bool $ismine, bool $mysamegroup, \stdClass $submission): array {
        return $this->get_row_permissions($ismine, $mysamegroup, $submission);
    }

    /**
     * Public wrapper to expose the protected get_submissions_table_state() method for testing.
     *
     * @param bool $canalwaysseeowner
     * @return array
     */
    public function call_get_submissions_table_state(bool $canalwaysseeowner): array {
        return $this->get_submissions_table_state($canalwaysseeowner);
    }

    /**
     * Public wrapper to expose the protected get_submissions_table_columns() method for testing.
     *
     * @param array $tablestate
     * @return array
     */
    public function call_get_submissions_table_columns(array $tablestate): array {
        return $this->get_submissions_table_columns($tablestate);
    }

    /**
     * Public wrapper to expose the protected get_submission_flags() method for testing.
     *
     * @param \stdClass $submission
     * @param int $currentuserid
     * @param bool $canaccessallgroups
     * @param bool $groupmode
     * @param array $mygroupmates
     * @return array
     */
    public function call_get_submission_flags(
        \stdClass $submission,
        int $currentuserid,
        bool $canaccessallgroups,
        bool $groupmode,
        array $mygroupmates
    ): array {
        return $this->get_submission_flags($submission, $currentuserid, $canaccessallgroups, $groupmode, $mygroupmates);
    }

    /**
     * Public wrapper to expose the protected format_submission_row_values() method for testing.
     *
     * @param \stdClass $submission
     * @param array $status
     * @param string $neverstr
     * @return array
     */
    public function call_format_submission_row_values(\stdClass $submission, array $status, string $neverstr): array {
        return $this->format_submission_row_values($submission, $status, $neverstr);
    }

    /**
     * Public wrapper to expose is_deleteall_button_visible() method for testing.
     *
     * @param bool $candeleteownsubmissions
     * @param bool $candeleteotherssubmissions
     * @param bool $canenjoydeleteallsubmissionsbutton
     * @param string $tifirst
     * @param string $tilast
     * @param int $next
     * @return bool
     */
    public function call_is_deleteall_button_visible(
        bool $candeleteownsubmissions,
        bool $candeleteotherssubmissions,
        bool $canenjoydeleteallsubmissionsbutton,
        string $tifirst,
        string $tilast,
        int $next
    ): bool {
        return $this->is_deleteall_button_visible(
            $candeleteownsubmissions,
            $candeleteotherssubmissions,
            $canenjoydeleteallsubmissionsbutton,
            $tifirst,
            $tilast,
            $next
        );
    }

    /**
     * Public wrapper to expose the protected prevent_direct_user_input() method for testing.
     *
     * @param int $confirm
     * @return void
     */
    public function call_prevent_direct_user_input(int $confirm): void {
        $this->prevent_direct_user_input($confirm);
    }

    /**
     * Public wrapper to expose should_skip_direct_user_input_check() for testing.
     *
     * @param int $confirm
     * @return bool
     */
    public function call_should_skip_direct_user_input_check(int $confirm): bool {
        return $this->should_skip_direct_user_input_check($confirm);
    }

    /**
     * Public wrapper to expose is_action_allowed_for_submission() for testing.
     *
     * @param int $action
     * @param \stdClass $submission
     * @param array $ownership
     * @param array $permissions
     * @return bool
     */
    public function call_is_action_allowed_for_submission(
        int $action,
        \stdClass $submission,
        array $ownership,
        array $permissions
    ): bool {
        return $this->is_action_allowed_for_submission($action, $submission, $ownership, $permissions);
    }

    /**
     * Public wrapper to expose is_view_allowed_for_submission() for testing.
     *
     * @param int $view
     * @param \stdClass $submission
     * @param array $ownership
     * @param array $permissions
     * @return bool
     */
    public function call_is_view_allowed_for_submission(
        int $view,
        \stdClass $submission,
        array $ownership,
        array $permissions
    ): bool {
        return $this->is_view_allowed_for_submission($view, $submission, $ownership, $permissions);
    }

    /**
     * Public wrapper to expose the protected get_submissions_overview_data() method for testing.
     *
     * @param int $enrolledusers
     * @param int $distinctusers
     * @param int $countclosed
     * @param int $closedusers
     * @param int $countinprogress
     * @param int $inprogressusers
     * @return array
     */
    public function call_get_submissions_overview_data(
        int $enrolledusers,
        int $distinctusers,
        int $countclosed,
        int $closedusers,
        int $countinprogress,
        int $inprogressusers
    ): array {
        return $this->get_submissions_overview_data(
            $enrolledusers,
            $distinctusers,
            $countclosed,
            $closedusers,
            $countinprogress,
            $inprogressusers
        );
    }

    /**
     * Public wrapper to expose the protected replace_http_url() method for testing.
     *
     * @param string $content
     * @return string
     */
    public function call_replace_http_url(string $content): string {
        return $this->replace_http_url($content);
    }

    /**
     * Public wrapper to expose the protected get_image_file() method for testing.
     *
     * @param string $fileurl
     * @return \stored_file|null
     */
    public function call_get_image_file(string $fileurl) {
        return $this->get_image_file($fileurl);
    }

    /**
     * Public wrapper to expose the protected get_sqlanswer() method for testing.
     *
     * @param array $searchrestrictions
     * @param array $whereparams
     * @return string
     */
    public function call_get_sqlanswer(array $searchrestrictions, array &$whereparams): string {
        return $this->get_sqlanswer($searchrestrictions, $whereparams);
    }
}
