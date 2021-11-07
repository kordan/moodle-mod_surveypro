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
 * Surveypro class to manage frequency report
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

/**
 * The class to manage frequency report
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_frequency_report extends mod_surveypro_reportbase {

    /**
     * @var flexible_table $outputtable
     */
    public $outputtable = null;

    /**
     * Setup_outputtable
     *
     * @param int $itemid
     * @return void
     */
    public function setup_outputtable($itemid) {
        $this->outputtable = new flexible_table('frequency');

        $paramurl = array('id' => $this->cm->id);
        $paramurl['itemid'] = $itemid;
        $baseurl = new moodle_url('/mod/surveypro/report/frequency/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'content';
        $tablecolumns[] = 'absolute';
        $tablecolumns[] = 'percentage';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('content', 'surveyproreport_frequency');
        $tableheaders[] = get_string('absolute', 'surveyproreport_frequency');
        $tableheaders[] = get_string('percentage', 'surveyproreport_frequency');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(true, 'content', 'ASC'); // Sorted by content by default.
        $this->outputtable->no_sorting('percentage');

        $this->outputtable->column_class('content', 'content');
        $this->outputtable->column_class('absolute', 'absolute');
        $this->outputtable->column_class('percentage', 'percentage');

        // $this->outputtable->initialbars(true);

        // Hide the same info whether in two consecutive rows.
        $this->outputtable->column_suppress('picture');
        $this->outputtable->column_suppress('fullname');

        // General properties for the whole table.
        $this->outputtable->summary = get_string('submissionslist', 'mod_surveypro');
        $this->outputtable->set_attribute('cellpadding', '5');
        $this->outputtable->set_attribute('id', 'frequencies');
        $this->outputtable->set_attribute('class', 'generaltable');
        $this->outputtable->set_attribute('align', 'center');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /**
     * Stop the execution if only textarea elements are in the survey
     */
    public function stop_if_textareas_only() {
        global $DB, $OUTPUT;

        $where = 'surveyproid = :surveyproid AND type = :type AND reserved = :reserved AND hidden = :hidden AND plugin <> :plugin';

        $params = array();
        $params['surveyproid'] = $this->surveypro->id;
        $params['type'] = SURVEYPRO_TYPEFIELD;
        $params['reserved'] = 0;
        $params['hidden'] = 0;
        $params['plugin'] = 'textarea';

        $countfields = $DB->count_records_select('surveypro_item', $where, $params);
        if (!$countfields) {
            echo $OUTPUT->box(get_string('textareasarenotallowed', 'surveyproreport_frequency'));
            $url = new moodle_url('/mod/surveypro/view_submissions.php', array('s' => $this->surveypro->id));
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }
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
     * @param int $itemid
     * @return void
     */
    public function fetch_data($itemid) {
        global $DB, $COURSE;

        list($sql, $whereparams) = $this->get_submissions_sql($itemid);
        $answers = $DB->get_recordset_sql($sql, $whereparams);

        // TAKE CARE: this is the answer count, not the submissions count! They may be different.
        list($sql, $whereparams) = $this->get_answercount_sql($itemid);
        $answercount = $DB->count_records_sql($sql, $whereparams);
        $item = surveypro_get_item($this->cm, $this->surveypro, $itemid);

        $decimalseparator = get_string('decsep', 'langconfig');
        foreach ($answers as $answer) {
            $tablerow = array();

            // Answer.
            $itemvalue = new stdClass();
            $itemvalue->id = $answer->id;
            $itemvalue->content = $answer->content;
            $tablerow[] = $item->userform_db_to_export($itemvalue, SURVEYPRO_FRIENDLYFORMAT);

            // Absolute.
            $tablerow[] = $answer->absolute;

            // Percentage.
            $tablerow[] = number_format(100 * $answer->absolute / $answercount, 2, $decimalseparator, ' ').'%';

            // Add row to the table.
            $this->outputtable->add_data($tablerow);
        }

        $answers->close();
    }

    /**
     * Get_submissions_sql
     *
     * @param int $itemid
     * @return array($sql, $whereparams);
     */
    public function get_submissions_sql($itemid) {
        global $COURSE, $DB;

        $whereparams = array();
        $sql = 'SELECT '.$DB->sql_compare_text('a.content', 255).', MIN(a.id) as id, COUNT(a.id) as absolute
                FROM {user} u
                    JOIN {surveypro_submission} s ON s.userid = u.id
                    JOIN {surveypro_answer} a ON a.submissionid = s.id';

        list($middlesql, $whereparams) = $this->get_middle_sql();
        $sql .= $middlesql;

        $sql .= ' AND a.itemid = :itemid';
        $whereparams['itemid'] = $itemid;

        $sql .= ' GROUP BY '.$DB->sql_compare_text('a.content', 255);

        // The query for the graph doesn't make use of $this->outputtable.
        if (isset($this->outputtable) && $this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort();
        } else {
            $sql .= ' ORDER BY '.$DB->sql_compare_text('a.content', 255);
        }

        return array($sql, $whereparams);
    }

    /**
     * Get_answercount_sql
     *
     * @param int $itemid
     * @return array($sql, $whereparams);
     */
    public function get_answercount_sql($itemid) {
        global $COURSE, $DB;

        $sql = 'SELECT COUNT(\'x\')
                FROM {user} u
                    JOIN {surveypro_submission} s ON s.userid = u.id
                    JOIN {surveypro_answer} a ON a.submissionid = s.id';

        list($middlesql, $whereparams) = $this->get_middle_sql();
        $sql .= $middlesql;

        $sql .= ' AND a.itemid = :itemid';
        $whereparams['itemid'] = $itemid;

        return array($sql, $whereparams);
    }

    /**
     * Output_data.
     *
     * @param moodle_url $url
     * @return void
     */
    public function output_data($url) {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyproreport_frequency'));
        $this->outputtable->print_html();
        if ($this->outputtable->started_output) {
            $this->print_graph($url);
        }
    }

    /**
     * Display the graph.
     *
     * @param moodle_url $graphurl
     * @return void
     */
    public function print_graph($graphurl) {
        global $CFG;

        if (empty($CFG->gdversion)) {
            echo '('.get_string('gdneed').')';
        } else {
            $imgparams = array();
            $imgparams['class'] = 'resultgraph';
            $imgparams['height'] = SURVEYPROREPORT_FREQUENCY_GHEIGHT;
            $imgparams['width'] = SURVEYPROREPORT_FREQUENCY_GWIDTH;
            $imgparams['src'] = $graphurl;
            $imgparams['alt'] = get_string('pluginname', 'surveyproreport_frequency');

            $content = html_writer::start_tag('div', array('class' => 'centerpara'));
            $content .= html_writer::empty_tag('img', $imgparams);
            $content .= html_writer::end_tag('div');
            echo $content;
        }
    }
}
