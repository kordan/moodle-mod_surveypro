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
 * @package   surveyproreport_missing
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');


/**
 * The class to missing count report
 *
 * @package   surveyproreport_missing
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_missing_class extends mod_surveypro_reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Setup_outputtable
     */
    public function setup_outputtable() {
        $this->outputtable = new flexible_table('missingusers');

        $paramurl = array('id' => $this->cm->id, 'rname' => 'missing');
        $baseurl = new moodle_url('/mod/surveypro/report/missing/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'lastname', 'ASC'); // Sorted by lastname by default.

        $this->outputtable->column_class('picture', 'picture');
        $this->outputtable->column_class('fullname', 'fullname');

        $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
        $this->outputtable->summary = get_string('submissionslist', 'mod_surveypro');
        $this->outputtable->set_attribute('cellpadding', '5');
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
        $sql = 'SELECT DISTINCT '.user_picture::fields('u').'
                FROM {user} u
                  JOIN (SELECT id, userid
                        FROM {role_assignments}
                        WHERE contextid = '.$coursecontext->id.'
                          AND roleid IN ('.implode(',', $role).')) ra ON u.id = ra.userid
                  LEFT JOIN (SELECT id, userid
                             FROM {surveypro_submission}
                             WHERE surveyproid = :surveyproid
                             GROUP BY userid) s ON s.userid = u.id
                WHERE ISNULL(s.id)';
        $whereparams = array('surveyproid' => $this->surveypro->id);

        list($where, $filterparams) = $this->outputtable->get_sql_where();
        if ($where) {
            $sql .= ' AND '.$where;
            $whereparams = array_merge($whereparams,  $filterparams);
        }

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.lastname ASC';
        }
        $usersubmissions = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($usersubmissions as $usersubmission) {
            $tablerow = array();

            // Picture.
            $tablerow[] = $OUTPUT->user_picture($usersubmission, array('courseid' => $COURSE->id));

            // User fullname.
            $paramurl = array('id' => $usersubmission->id, 'course' => $COURSE->id);
            $url = new moodle_url('/user/view.php', $paramurl);
            $tablerow[] = '<a href="'.$url->out().'">'.fullname($usersubmission).'</a>';

            // Add row to the table.
            $this->outputtable->add_data($tablerow);
        }

        $usersubmissions->close();
    }

    /**
     * Output_data
     */
    public function output_data() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyproreport_missing'));
        $this->outputtable->print_html();
    }
}