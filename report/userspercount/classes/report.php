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
 * Surveypro class to manage userspercount report
 *
 * @package   surveyproreport_userspercount
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_userspercount;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\reportbase;

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to manage userspercount report
 *
 * @package   surveyproreport_userspercount
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report extends reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Is this report equipped with student reports.
     *
     * @return boolean
     */
    public static function get_hasstudentreport() {
        return false;
    }

    /**
     * Does the current report apply to the passed mastertemplates?
     *
     * @param string $mastertemplate
     * @return boolean
     */
    public function report_applies_to($mastertemplate) {
        return true;
    }

    /**
     * Get if this report displays user names.
     *
     * @return boolean
     */
    public static function get_displayusernames() {
        return false;
    }

    /**
     * Setup_outputtable
     */
    public function setup_outputtable() {
        $this->outputtable = new \flexible_table('userspercount');

        $paramurl = ['s' => $this->cm->instance];
        $baseurl = new \moodle_url('/mod/surveypro/report/userspercount/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'userresponses';
        $tablecolumns[] = 'userscount';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('userresponses', 'surveyproreport_userspercount');
        $tableheaders[] = get_string('users');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'userresponses', 'ASC'); // Sorted by userresponses by default.

        $this->outputtable->column_class('userresponses', 'userresponses');
        $this->outputtable->column_class('userscount', 'userscount');

        $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
        $this->outputtable->summary = get_string('submissionslist', 'mod_surveypro');
        // $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'userattempts');
        $this->outputtable->set_attribute('class', 'generaltable');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /**
     * Fetch_data.
     *
     * This is the idea supporting the code.
     *
     * Teachers is the role of users usually accessing reports.
     * They are "teachers" so they care about "students" and nothing more.
     * If, at import time, some records go under the admin ownership
     * the teacher is not supposed to see them because admin is not a student.
     * In this case, if the teacher wants to see submissions owned by admin, HE HAS TO ENROLL ADMIN with some role.
     *
     * Different is the story for the admin.
     * If an admin wants to make a report, he will see EACH RESPONSE SUBMITTED
     * without care to the role of the owner of the submission.
     *
     * @return void
     */
    public function fetch_data() {
        global $DB, $COURSE, $OUTPUT;

        list($sql, $whereparams) = $this->get_submissions_sql();
        $userspercounts = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($userspercounts as $userspercount) {
            $tablerow = array();

             // Count of responses.
            $tablerow[] = $userspercount->userresponses;

            // Number of users who submittet such count of responses.
            $tablerow[] = $userspercount->usercount;

            // Add row to the table.
            $this->outputtable->add_data($tablerow);
        }

        $userspercounts->close();
    }

    /**
     * Get_submissions_sql
     *
     * @return [$sql, $whereparams];
     */
    public function get_submissions_sql() {

        list($middlesql, $whereparams) = $this->get_middle_sql();

        $subquery = 'SELECT s.userid, COUNT(s.userid) as userresponses
                FROM {surveypro_submission} s
                    JOIN {user} u ON u.id = s.userid
                    '.$middlesql.'
                GROUP BY s.userid';

        $sql = 'SELECT userresponses, count(userresponses) as usercount
                FROM ('.$subquery.') as rpu
                GROUP BY userresponses';

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY u.lastname ASC';
        }

        return [$sql, $whereparams];
    }

    /**
     * Output_data
     */
    public function output_data() {
        $this->outputtable->print_html();
    }
}
