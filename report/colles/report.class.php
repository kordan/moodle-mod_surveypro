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

/*
 * Defines the version of surveypro autofill subplugin
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    surveyproreport
 * @subpackage colles
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/reportbase.class.php');

class report_colles extends mod_surveypro_reportbase {
    /*
     * template
     */
    public $template;

    /*
     * group
     */
    public $group = 0;

    /*
     * sid
     */
    public $area = 0;

    /*
     * qid
     */
    public $qid = 0;

    /*
     * graphtitle
     */
    public $graphtitle = '';

    /*
     * xlabels
     */
    public $xlabels = array();

    /*
     * ylabels
     */
    public $ylabels = array();

    /*
     * trend1
     */
    public $trend1 = array();

    /*
     * trend1stdev
     */
    public $trend1stdev = array();

    /*
     * trend2
     */
    public $trend2 = array();

    /*
     * trend2stdev
     */
    public $trend2stdev = array();

    /*
     * studenttrend1
     */
    public $studenttrend1 = array();

    /*
     * studenttrend2
     */
    public $studenttrend2 = array();

    /*
     * iarea
     */
    public $iarea;

    /*
     * setup
     */
    function setup($hassubmissions, $group, $area, $qid) {
        global $DB;

        $this->template = $DB->get_field('surveypro', 'template', array('id' => $this->surveypro->id));
        $this->hassubmissions = $hassubmissions;
        $this->group = $group;
        $this->area = $area;
        $this->qid = $qid;
        $this->iarea = new stdClass();
    }

    /*
     * restrict_templates
     */
    public static function restrict_templates() {
        return array('collesactual', 'collespreferred', 'collesactualpreferred');
    }

    /*
     * has_student_report
     */
    public static function has_student_report() {
        return true;
    }

    /*
     * get_childreports
     */
    public static function get_childreports($canaccessreports) {
        if ($canaccessreports) {
            $childreports = array();
            $childreports['summary'] = array('type' => 'summary');
            $childreports['scales'] = array('type' => 'scales');
            $childreports['questions'] = array('type' => 'questions');

            return $childreports;
        } else {
            return false;
        }
    }

    /*
     * output_html
     */
    public function output_html($nexturl, $graphurl, $altkey) {
        static $strseemoredetail;
        if (empty($strseemoredetail)) {     // Cache the string for the next future
            $strseemoredetail = get_string('seemoredetail', 'surveyproreport_colles');
        }

        $imgparams = array();
        $imgparams['class'] = 'resultgraph';
        $imgparams['height'] = SURVEYPRO_GHEIGHT;
        $imgparams['width'] = SURVEYPRO_GWIDTH;
        $imgparams['src'] = $graphurl;
        $imgparams['alt'] = get_string($altkey, 'surveyproreport_colles');

        $content = html_writer::start_tag('div', array('class' => 'centerpara'));
        if ($nexturl) {
            $content .= html_writer::start_tag('a', array('title' => $strseemoredetail, 'href' => $nexturl));
        }
        $content .= html_writer::empty_tag('img', $imgparams);
        if ($nexturl) {
            $content .= html_writer::end_tag('a');
        }
        $content .= html_writer::end_tag('div');
        echo $content;
    }

    /*
     * get_qid_per_area
     */
    public function get_qid_per_area() {
        global $DB;

        $qid1area = array(); // array of id of items referring to the trend 1
        $qid2area = array(); // array of id of items referring to the trend 2
        $sql = 'SELECT si.id, si.sortindex, si.plugin
                FROM {surveypro_item} si
                WHERE si.surveyproid = :surveyproid
                    AND si.plugin = :plugin
                ORDER BY si.sortindex';

        $whereparams = array('surveyproid' => $this->surveypro->id, 'plugin' => 'radiobutton');
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        if ($this->template == 'collesactualpreferred') {
            $id1 = array(); // id of items referring to preferred trend
            $id2 = array(); // id of items referring to actual trend
            $i = 0;
            foreach ($itemseeds as $itemseed) {
                $i++;
                if ($i % 2) {
                    $id1[] = $itemseed->id;
                } else {
                    $id2[] = $itemseed->id;
                }
                if (count($id1) == 4) {
                    $qid1area[] = $id1;
                    $id1 = array();
                }
                if (count($id2) == 4) {
                    $qid2area[] = $id2;
                    $id2 = array();
                }
            }
        } else {
            $id1 = array(); // id of items referring to the trend 1 (it may be preferred such as actual)
            foreach ($itemseeds as $itemseed) {
                $id1[] = $itemseed->id;
                if (count($id1) == 4) {
                    $qid1area[] = $id1;
                    $id1 = array();
                }
            }
        }
        $itemseeds->close();

        return array($qid1area, $qid2area);
    }

    /*
     * output_summarydata
     */
    public function output_summarydata() {
        if ($this->canaccessreports) {
            $paramnexturl = array();
            $paramnexturl['s'] = $this->surveypro->id;
            $paramnexturl['type'] = 'scales';
            // $paramnexturl['group'] = 0;
            // $paramnexturl['area'] = 0;
            $nexturl = new moodle_url('view.php', $paramnexturl);
        } else {
            $nexturl = null;
        }

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['group'] = 0;
        $paramurl['type'] = 'summary';
        $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);

        $this->output_html($nexturl, $graphurl, 'summaryreport');
    }

    /*
     * fetch_summarydata
     */
    public function fetch_summarydata() {
        global $DB, $USER;

        $this->graphtitle = get_string('summary', 'surveyproreport_colles');

        // names of areas of investigation
        for ($i = 1; $i < 7; $i++) {
            $this->xlabels[] = get_string('fieldset_content_0'.$i, 'surveyprotemplate_'.$this->template);
        }
        // end of: names of areas of investigation

        // group question id per area of investigation
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // end of: group question id per area of investigation

        // options (label of answers)
        $itemid = $qid1area[0][0]; // one of the itemid of the surveypro (the first)
        $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, 'radiobutton');
        $this->ylabels = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
        // end of: options (label of answers)

        // calculate the mean and the standard deviation of answers
        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area, $qid2area);
        } else {
            $toevaluate = array($qid1area);
        }
        foreach ($toevaluate as $k => $qidarea) {
            foreach ($qidarea as $areaidlist) {
                $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                        FROM {surveypro_userdata} ud
                        WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
                $aggregate = $DB->get_record_sql($sql);
                $m = $aggregate->sumofanswers/$aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                $sql = 'SELECT ud.content
                        FROM {surveypro_userdata} ud
                        WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
                $answers = $DB->get_recordset_sql($sql);
                $bigsum = 0;
                foreach ($answers as $answer) {
                    $xi = (double)$answer->content;
                    $bigsum += ($xi - $m) * ($xi - $m);
                }
                $answers->close();

                $bigsum /= $aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1stdev[] = sqrt($bigsum);
                }
                if ($k == 1) {
                    $this->trend2stdev[] = sqrt($bigsum);
                }
            }
        }

        if (!$this->canaccessreports && $this->canaccessownreports) { // if the user hasn't general right but only canaccessownreports
            $whereparams = array('userid' => $USER->id);

            foreach ($toevaluate as $k => $qidarea) {
                foreach ($qidarea as $areaidlist) {
                    $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                            FROM {surveypro_userdata} ud
                                JOIN {surveypro_submission} ss ON ss.id = ud.submissionid
                            WHERE ud.itemid IN ('.implode(',', $areaidlist).')
                            AND ss.userid = :userid';
                    $aggregate = $DB->get_record_sql($sql, $whereparams);

                    if ($aggregate->countofanswers) {
                        $m = $aggregate->sumofanswers/$aggregate->countofanswers;
                        if ($k == 0) {
                            $this->studenttrend1[] = $m;
                        }
                        if ($k == 1) {
                            $this->studenttrend2[] = $m;
                        }
                    } else {
                        if ($k == 0) {
                            $this->studenttrend1stdev[] = null;
                        }
                        if ($k == 1) {
                            $this->studenttrend2stdev[] = null;
                        }
                    }
                }
            }
        }
        // end of: calculate the mean and the standard deviation of answers
    }

    /*
     * output_scalesdata
     */
    public function output_scalesdata() {
        $paramnexturl = array();
        $paramnexturl['s'] = $this->surveypro->id;
        $paramnexturl['type'] = 'questions';
        // $paramnexturl['group'] = 0;
        // $paramnexturl['area'] = 0;

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['group'] = 0;
        $paramurl['type'] = 'scales';

        for ($area = 0; $area < 6; $area++) { // 0..5
            $paramnexturl['area'] = $area;
            $paramurl['area'] = $area;
            $nexturl = new moodle_url('view.php', $paramnexturl);
            $graphurl = new moodle_url('graph.php', $paramurl);

            $this->output_html($nexturl, $graphurl, 'scalesreport');
        }
    }

    /*
     * fetch_scalesdata
     */
    public function fetch_scalesdata($area=false) {
        global $DB;

        if ($area === false) { // $area MUST BE provided
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected null $area', DEBUG_DEVELOPER);
        }

        $this->graphtitle = get_string('fieldset_content_0'.($area + 1), 'surveyprotemplate_'.$this->template);

        // short names of questions
        $name = array();
        for ($i = 1; $i < 5; $i++) {
            $index = sprintf('%02d', 4 * $area + $i);
            $key = 'question'.$index.'short';
            $this->xlabels[] = get_string($key, 'surveyproreport_colles');
        }
        // end of: names of areas of investigation

        // group question id per area of investigation
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // end of: group question id per area of investigation

        // options (label of answers)
        $itemid = $qid1area[0][0]; // one of the itemid of the surveypro (the first)
        $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, 'radiobutton');
        $this->ylabels = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
        // end of: options (label of answers)

        // calculate the mean and the standard deviation of answers
        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area[$area], $qid2area[$area]);
        } else {
            $toevaluate = array($qid1area[$area]);
        }
        foreach ($toevaluate as $k => $areaidlist) {
            foreach ($areaidlist as $itemid) {
                $whereparams = array('itemid' => $itemid);
                $sql = 'SELECT count(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                        FROM {surveypro_userdata} ud
                        WHERE ud.itemid = :itemid';
                $aggregate = $DB->get_record_sql($sql, $whereparams);
                $m = $aggregate->sumofanswers/$aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                $sql = 'SELECT ud.content
                        FROM {surveypro_userdata} ud
                        WHERE ud.itemid = :itemid';
                $answers = $DB->get_recordset_sql($sql, $whereparams);
                $bigsum = 0;
                foreach ($answers as $answer) {
                    $xi = (double)$answer->content;
                    $bigsum += ($xi - $m) * ($xi - $m);
                }
                $bigsum /= $aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1stdev[] = sqrt($bigsum);
                }
                if ($k == 1) {
                    $this->trend2stdev[] = sqrt($bigsum);
                }
                $answers->close();
            }
        }
        // end of: calculate the mean and the standard deviation of answers
    }

    /*
     * output_questionsdata
     */
    public function output_questionsdata($area=false) {
        $paramnexturl = array();
        $paramnexturl['s'] = $this->surveypro->id;
        $paramnexturl['type'] = 'summary';
        // $paramnexturl['group'] = 0;
        $nexturl = new moodle_url('view.php', $paramnexturl);

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['group'] = 0;
        $paramurl['type'] = 'questions';

        if ($area === false) {
            $areabegin = 0;
            $areaend = 6;
        } else {
            $areabegin = $area;
            $areaend = $area + 1;
        }

        for ($area = $areabegin; $area < $areaend; $area++) { // 1..6 or just one $area
            $paramurl['area'] = $area;
            for ($qid = 0; $qid < 4; $qid++) { // 0..3
                $paramurl['qid'] = $qid;
                $graphurl = new moodle_url('graph.php', $paramurl);
                $this->output_html($nexturl, $graphurl, 'questionsreport');
            }
        }
    }

    /*
     * fetch_questionsdata
     */
    public function fetch_questionsdata($area=false, $qid=false) {
        global $DB;

        if ($area === false) { // $area MUST BE provided
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected null $area', DEBUG_DEVELOPER);
        }
        if ($qid === false) { // $area MUST BE provided
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected null $qid', DEBUG_DEVELOPER);
        }

        // group question id per area of investigation
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // end of: group question id per area of investigation

        // options (label of answers)
        $itemid = $qid1area[$area][$qid]; // one of the itemid of the surveypro (the first)
        $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, 'radiobutton');
        $this->xlabels = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
        // end of: options (label of answers)

        // graph title
        $this->graphtitle = strip_tags($item->get_content());
        // end of: graph title

        // starts with empty defaults
        for ($i = 0; $i < 5; $i++) { // 0..4
            $this->trend1[] = 0;
            $this->trend2[] = 0;
        }

        // calculate trend1 and, maybe, trend2
        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area[$area], $qid2area[$area]);
        } else {
            $toevaluate = array($qid1area[$area]);
        }
        foreach ($toevaluate as $k => $areaidlist) {
            $whereparams = array('itemid' => $areaidlist[$qid]);
            $sql = 'SELECT content, count(id) as absolute
                    FROM {surveypro_userdata}
                    WHERE itemid = :itemid
                    GROUP BY content';
            $aggregates = $DB->get_records_sql($sql, $whereparams);

            if ($k == 0) {
                foreach ($aggregates as $aggregate) {
                    $this->trend1[$aggregate->content] = $aggregate->absolute;
                }
            }
            if ($k == 1) {
                foreach ($aggregates as $aggregate) {
                    $this->trend2[$aggregate->content] = $aggregate->absolute;
                }
            }
        }
        // end of: calculate trend1 and, maybe, trend2
    }
}