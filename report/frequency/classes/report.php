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
     */
    public function setup_outputtable() {
        $this->outputtable = new flexible_table('submissionslist');

        $paramurl = array('id' => $this->cm->id, 'rname' => 'frequency');
        $baseurl = new moodle_url('/mod/surveypro/report/frequency/view.php', $paramurl);
        $this->outputtable->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'answer';
        $tablecolumns[] = 'absolute';
        $tablecolumns[] = 'percentage';
        $this->outputtable->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('content', 'surveyproreport_frequency');
        $tableheaders[] = get_string('absolute', 'surveyproreport_frequency');
        $tableheaders[] = get_string('percentage', 'surveyproreport_frequency');
        $this->outputtable->define_headers($tableheaders);

        $this->outputtable->sortable(false, 'content', 'ASC'); // Sorted by content by default.

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
        $this->outputtable->set_attribute('id', 'submissions');
        $this->outputtable->set_attribute('class', 'generaltable');
        $this->outputtable->set_attribute('align', 'center');
        // $this->outputtable->set_attribute('width', '90%');
        $this->outputtable->setup();
    }

    /**
     * Stop_if_textareas_only
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
            $url = new moodle_url('/mod/surveypro/view.php', array('s' => $this->surveypro->id));
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * Fetch_data.
     *
     * @param int $itemid
     * @param int $submissionscount
     * @return void
     */
    public function fetch_data($itemid) {
        global $DB;

        // TAKE CARE: this is the answer count, not the submissions count! They may be different.
        $whereparams = array('itemid' => $itemid);
        $answercount = $DB->count_records('surveypro_answer', $whereparams);

        list($where, $whereparams) = $this->outputtable->get_sql_where();

        $sql = 'SELECT *, count(ud.id) as absolute
                FROM {surveypro_answer} ud
                WHERE ud.itemid = :itemid
                GROUP BY ud.content
                ORDER BY ud.content';

        $whereparams['itemid'] = $itemid;

        $answers = $DB->get_recordset_sql($sql, $whereparams);

        $dummyitem = surveypro_get_item($this->cm, $this->surveypro, $itemid);

        $decimalseparator = get_string('decsep', 'langconfig');
        foreach ($answers as $answer) {
            $tablerow = array();

            // Answer.
            $itemvalue = new stdClass();
            $itemvalue->id = $answer->id;
            $itemvalue->content = $answer->content;
            $tablerow[] = $dummyitem->userform_db_to_export($itemvalue);

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
     * Output_data.
     *
     * @param moodle_url $url
     * @return void
     */
    public function output_data($url) {
        global $OUTPUT;

        echo $OUTPUT->heading(get_string('pluginname', 'surveyproreport_count'));
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