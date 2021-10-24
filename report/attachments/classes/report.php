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
 * Surveypro class to manage attachment overview report
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to manage attachment overview report.
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_attachments_report extends mod_surveypro_reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Return if this report applies.
     *
     * true means: the report applies
     * empty($this->surveypro->anonymous) means that reports applies ONLY IF the survey is not anonymous
     *
     * @return boolean
     */
    public function report_apply() {
        return (empty($this->surveypro->anonymous));
    }

    /**
     * Setup_outputtable.
     */
    public function setup_outputtable() {
        $this->outputtable = new flexible_table('attachmentslist');

        $paramurl = array('id' => $this->cm->id);
        if ($this->groupid) {
            $paramurl['groupid'] = $this->groupid;
        }
        $baseurl = new moodle_url('/mod/surveypro/report/attachments/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $tablecolumns[] = 'uploads';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $tableheaders[] = get_string('uploads', 'surveyproreport_attachments');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'lastname', 'ASC'); // Sorted by lastname by default.
        $this->outputtable->no_sorting('uploads');

        $this->outputtable->column_class('picture', 'picture');
        $this->outputtable->column_class('fullname', 'fullname');
        $this->outputtable->column_class('uploads', 'uploads');

        $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
        $this->outputtable->summary = get_string('submissionslist', 'mod_surveypro');
        $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'attachments');
        $this->outputtable->set_attribute('class', 'generaltable');
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

        $displayuploadsstr = get_string('display_uploads', 'surveyproreport_attachments');
        $missinguploadsstr = get_string('missing_uploads', 'surveyproreport_attachments');
        $submissionidstr = get_string('submissionid', 'surveyproreport_attachments');

        list($sql, $whereparams) = $this->get_submissions_sql();
        $usersubmissions = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($usersubmissions as $usersubmission) {
            $tablerow = array();

            // Picture.
            $tablerow[] = $OUTPUT->user_picture($usersubmission, array('courseid' => $COURSE->id));

            // User fullname.
            $paramurl = array('id' => $usersubmission->id);
            $url = new moodle_url('/user/view.php', $paramurl);
            $tablerow[] = '<a href="'.$url->out().'">'.fullname($usersubmission).'</a>';

            // Users with $usersubmission->submissionid == null have no submissions.
            if (!empty($usersubmission->submissionid)) {
                $paramurl = array();
                $paramurl['id'] = $this->cm->id;
                $paramurl['userid'] = $usersubmission->id;
                $paramurl['submissionid'] = $usersubmission->submissionid;
                $url = new moodle_url('/mod/surveypro/report/attachments/uploads.php', $paramurl);
                $cellcontent = '('.$submissionidstr.': '.$usersubmission->submissionid.')&nbsp;';
                $cellcontent .= html_writer::start_tag('a', array('title' => $displayuploadsstr, 'href' => $url));
                $cellcontent .= s($displayuploadsstr);
                $cellcontent .= html_writer::end_tag('a');
                $tablerow[] = $cellcontent;
            } else {
                $tablerow[] = $missinguploadsstr;
            }

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
        global $COURSE, $DB;

        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');
        $whereparams = array();
        $sql = 'SELECT s.id as submissionid'.$userfieldsapi->selects.'
                FROM {user} u
                JOIN {surveypro_submission} s ON s.userid = u.id';

        list($middlesql, $whereparams) = $this->get_middle_sql();
        $sql .= $middlesql;

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort().', submissionid ASC';
        } else {
            $sql .= ' ORDER BY u.lastname ASC, submissionid ASC';
        }

        return array($sql, $whereparams);
    }

    /**
     * Output_data.
     *
     * @return void
     */
    public function output_data() {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyproreport_attachments'));
        $this->outputtable->print_html();
    }

    /**
     * Check_attachmentitems.
     *
     * @return void
     */
    public function check_attachmentitems() {
        global $OUTPUT, $DB;

        $params = array();
        $params['surveyproid'] = $this->surveypro->id;
        $params['plugin'] = 'fileupload';
        $attachmentitems = $DB->count_records('surveypro_item', $params);

        if (!$attachmentitems) {
            $message = get_string('noattachmentitemsfound', 'surveyproreport_attachments');
            echo $OUTPUT->box($message, 'notice centerpara');
            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        if (!empty($this->surveypro->anonymous)) {
            print_error('incorrectaccessdetected', 'mod_surveypro');
        }
    }
}
