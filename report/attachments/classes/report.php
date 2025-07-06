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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_attachments;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\reportbase;

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to manage attachment overview report.
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report extends reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

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
     * Setup_outputtable.
     */
    public function setup_outputtable() {
        $this->outputtable = new \flexible_table('attachmentslist');

        $paramurl = ['s' => $this->cm->instance];
        if ($this->groupid) {
            $paramurl['groupid'] = $this->groupid;
        }
        $baseurl = new \moodle_url('/mod/surveypro/report/attachments/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = [];
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $tablecolumns[] = 'uploads';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = [];
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $tableheaders[] = get_string('uploads', 'surveyproreport_attachments');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true);
        $this->outputtable->no_sorting('uploads');

        $this->outputtable->column_class('picture', 'picture');
        $this->outputtable->column_class('fullname', 'fullname');
        $this->outputtable->column_class('uploads', 'uploads');

        $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
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

        [$sql, $whereparams] = $this->get_submissions_sql();
        $usersubmissions = $DB->get_recordset_sql($sql, $whereparams);

        foreach ($usersubmissions as $usersubmission) {
            $tablerow = [];

            // Picture.
            $tablerow[] = $OUTPUT->user_picture($usersubmission, ['courseid' => $COURSE->id]);

            // User fullname.
            $userfullname = fullname($usersubmission);
            $paramurl = ['id' => $usersubmission->id];
            $url = new \moodle_url('/user/view.php', $paramurl);
            $cellcontent = \html_writer::start_tag('a', ['title' => $userfullname, 'href' => $url]);
            $cellcontent .= $userfullname;
            $cellcontent .= \html_writer::end_tag('a');
            $cellcontent .= ' [id: '.$usersubmission->id.']';
            $tablerow[] = $cellcontent;
            // $tablerow[] = '<a href="'.$url->out().'">'.fullname($usersubmission).' [id: '.$usersubmission->id.']</a>';

            // Users with $usersubmission->submissionid == null have no submissions.
            if (!empty($usersubmission->submissionid)) {
                $paramurl = [];
                $paramurl['s'] = $this->surveypro->id;
                $paramurl['report'] = 'attachments';
                $paramurl['section'] = 'details';
                $paramurl['container'] = $usersubmission->id.'_'.$usersubmission->submissionid;
                // $url = new \moodle_url('/mod/surveypro/report/attachments/uploads.php', $paramurl);
                $url = new \moodle_url('/mod/surveypro/report/attachments/view.php', $paramurl);
                $cellcontent = '('.$submissionidstr.': '.$usersubmission->submissionid.')&nbsp;';
                $cellcontent .= \html_writer::start_tag('a', ['title' => $displayuploadsstr, 'href' => $url]);
                $cellcontent .= s($displayuploadsstr);
                $cellcontent .= \html_writer::end_tag('a');
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
     * Output_data.
     *
     * @return void
     */
    public function output_data() {
        $this->outputtable->print_html();
    }

    /**
     * Check_attachmentitems.
     *
     * @return void
     */
    public function check_attachmentitems() {
        global $OUTPUT, $DB;

        $params = [];
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

    // MARK set.

    /**
     * set_additionalparams.
     *
     * Sets the parameters to be supplied to the url to call the specific report page
     */
    public function set_additionalparams() {
        $this->additionalparams = [
            'optional' => [
                'container' => PARAM_ALPHANUMEXT,
            ],
            'required' => [
                'section' => PARAM_ALPHA,
            ],
        ];
    }

    // MARK get.

    /**
     * Get if this report displays user names.
     *
     * @return boolean
     */
    public static function get_displayusernames() {
        return true;
    }

    /**
     * Get_submissions_sql
     *
     * @return [$sql, $whereparams];
     */
    public function get_submissions_sql() {
        global $COURSE, $DB, $USER;

        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');
        $whereparams = [];
        $sql = 'SELECT s.id as submissionid'.$userfieldsapi->selects.'
                FROM {user} u
                JOIN {surveypro_submission} s ON s.userid = u.id';

        [$middlesql, $whereparams] = $this->get_middle_sql();
        $sql .= $middlesql;

        if ($this->onlypersonaldata) {
            $sql .= ' AND u.id = :userid';
            $whereparams['userid'] = $USER->id;
        }

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort().', submissionid ASC';
        } else {
            $sql .= ' ORDER BY u.lastname ASC, submissionid ASC';
        }

        return [$sql, $whereparams];
    }
}
