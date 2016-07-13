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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to manage userspercount report
 *
 * @package   surveyproreport_userspercount
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_userspercount_report extends mod_surveypro_reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Setup_outputtable
     */
    public function setup_outputtable() {
        $this->outputtable = new flexible_table('userspercount');

        $paramurl = array('id' => $this->cm->id, 'rname' => 'users');
        $baseurl = new moodle_url('/mod/surveypro/report/userspercount/view.php', $paramurl);
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
     * Fetch_data
     */
    public function fetch_data() {
        global $DB, $COURSE, $OUTPUT;

        $coursecontext = context_course::instance($COURSE->id);
        $roles = get_roles_used_in_context($coursecontext);
        if (!$role = array_keys($roles)) {
            // Return nothing.
            return;
        }
        $sql = 'SELECT s.userresponses, count(s.userresponses) as userscount
                FROM (SELECT userid, count(userid) as userresponses
                    FROM {surveypro_submission}
                    WHERE surveyproid = :surveyproid
                    GROUP BY userid) s
                GROUP BY userresponses';
        $whereparams = array('surveyproid' => $this->surveypro->id);

        list($where, $filterparams) = $this->outputtable->get_sql_where();
        if ($where) {
            $sql .= ' WHERE '.$where;
            $whereparams = array_merge($whereparams,  $filterparams);
        }

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.userresponses ASC';
        }
        $userspercounts = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($userspercounts as $userspercount) {
            $tablerow = array();

             // Count of responses.
            $tablerow[] = $userspercount->userresponses;

            // Number of users who submittet such count of responses.
            $tablerow[] = $userspercount->userscount;

            // Add row to the table.
            $this->outputtable->add_data($tablerow);
        }

        $userspercounts->close();
    }

    /**
     * Output_data
     */
    public function output_data() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyproreport_userspercount'));
        $this->outputtable->print_html();
    }
}
