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
 * Surveypro class to missing count report
 *
 * @package   surveyproreport_responsesperuser
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\reportbase;

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to missing count report
 *
 * @package   surveyproreport_responsesperuser
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_responsesperuser_report extends reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Setup_outputtable
     */
    public function setup_outputtable() {
        $this->outputtable = new \flexible_table('responsesperuser');

        $paramurl = array('id' => $this->cm->id);
        if ($this->groupid) {
            $paramurl['groupid'] = $this->groupid;
        }
        $baseurl = new \moodle_url('/mod/surveypro/report/responsesperuser/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $tablecolumns[] = 'attempts';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $tableheaders[] = get_string('submissions', 'mod_surveypro');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'lastname', 'ASC'); // Sorted by lastname by default.

        $this->outputtable->column_class('picture', 'picture');
        $this->outputtable->column_class('fullname', 'fullname');
        $this->outputtable->column_class('attempts', 'attempts');

        $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
        $this->outputtable->summary = get_string('submissionslist', 'mod_surveypro');
        // $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'responsesperuser');
        $this->outputtable->set_attribute('class', 'generaltable');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /**
     * Fetch_data
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
        $usersubmissions = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($usersubmissions as $usersubmission) {
            $tablerow = array();

            // Picture.
            $tablerow[] = $OUTPUT->user_picture($usersubmission, array('courseid' => $COURSE->id));

            // User fullname.
            $paramurl = array('id' => $usersubmission->id, 'course' => $COURSE->id);
            $url = new \moodle_url('/user/view.php', $paramurl);
            $tablerow[] = '<a href="'.$url->out().'">'.fullname($usersubmission).'</a>';

            // User attempts.
            $tablerow[] = isset($usersubmission->attempts) ? $usersubmission->attempts : '--';

            // Add row to the table.
            $this->outputtable->add_data($tablerow);
        }

        $usersubmissions->close();
    }

    /**
     * Get_submissions_sql
     *
     * @return array($sql, $whereparams);
     */
    public function get_submissions_sql() {
        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');

        $submissiontable = 'SELECT userid, surveyproid, count(userid) as attempts
                            FROM {surveypro_submission}
                            GROUP BY userid, surveyproid';
        $sql = 'SELECT s.attempts'.$userfieldsapi->selects.'
                FROM {user} u
                JOIN ('.$submissiontable.') s ON s.userid = u.id';

        list($middlesql, $whereparams) = $this->get_middle_sql();
        $sql .= $middlesql;

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY u.lastname ASC';
        }

        return array($sql, $whereparams);
    }

    /**
     * Output_data
     */
    public function output_data() {
        $this->outputtable->print_html();
    }
}
