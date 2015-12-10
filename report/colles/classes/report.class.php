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
 * Defines the version of surveypro autofill subplugin
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    surveyproreport
 * @subpackage colles
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/reportbase.class.php');

class mod_surveypro_report_colles extends mod_surveypro_reportbase {
    /**
     * canaccessreports
     */
    public $canaccessreports;

    /**
     * canaccessownreports
     */
    public $canaccessownreports;

    /**
     * template
     */
    public $template;

    /**
     * group
     */
    public $group = 0;

    /**
     * sid
     */
    public $area = 0;

    /**
     * templateuseritem
     */
    public $templateuseritem = '';

    /**
     * qid
     */
    public $qid = 0;

    /**
     * graphtitle
     */
    public $graphtitle = '';

    /**
     * xlabels
     */
    public $xlabels = array();

    /**
     * ylabels
     */
    public $ylabels = array();

    /**
     * trend1
     */
    public $trend1 = array();

    /**
     * trend1stdev
     */
    public $trend1stdev = array();

    /**
     * trend2
     */
    public $trend2 = array();

    /**
     * trend2stdev
     */
    public $trend2stdev = array();

    /**
     * studenttrend1
     */
    public $studenttrend1 = array();

    /**
     * studenttrend2
     */
    public $studenttrend2 = array();

    /**
     * iarea
     */
    public $iarea;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        global $DB;

        parent::__construct($cm, $context, $surveypro);

        $this->template = $DB->get_field('surveypro', 'template', array('id' => $this->surveypro->id));
        $this->canaccessreports = has_capability('mod/surveypro:accessreports', $context, null, true);
        $this->canaccessownreports = has_capability('mod/surveypro:accessownreports', $context, null, true);

        // Which plugin has been used to build this master template? Radiobutton or select?
        $guessplugin = array('radiobutton', 'select');
        $where = array('surveyproid' => $surveypro->id, 'plugin' => $guessplugin[0]);
        if ($DB->get_records('surveypro_item', $where, 'id', 'id')) {
            $this->templateuseritem = $guessplugin[0];
        } else {
            $this->templateuseritem = $guessplugin[1];
        }

        $this->iarea = new stdClass();
    }

    /**
     * set_group
     *
     * @param int $group
     * @return none
     */
    public function set_group($group) {
        $this->group = $group;
    }

    /**
     * set_area
     *
     * @param int $area
     * @return none
     */
    public function set_area($area) {
        $this->area = $area;
    }

    /**
     * set_qid
     *
     * @param int $qid
     * @return none
     */
    public function set_qid($qid) {
        $this->qid = $qid;
    }

    /**
     * restrict_templates
     *
     * @param none
     * @return none
     */
    public function restrict_templates() {
        return array('collesactual', 'collespreferred', 'collesactualpreferred');
    }

    /**
     * has_student_report
     *
     * @param none
     * @return none
     */
    public function has_student_report() {
        return true;
    }

    /**
     * get_childreports
     *
     * @param bool $canaccessreports
     * @return none
     */
    public function get_childreports($canaccessreports) {
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

    /**
     * output_html
     *
     * @param string $nexturl
     * @param string $graphurl
     * @param string $altkey
     * @return none
     */
    public function output_html($nexturl, $graphurl, $altkey) {
        static $strseemoredetail;

        if (empty($strseemoredetail)) {     // Cache the string for the next future
            $strseemoredetail = get_string('seemoredetail', 'surveyproreport_colles');
        }

        $imgparams = array();
        $imgparams['class'] = 'resultgraph';
        $imgparams['height'] = SURVEYPROREPORT_COLLES_GHEIGHT;
        $imgparams['width'] = SURVEYPROREPORT_COLLES_GWIDTH;
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

    /**
     * get_qid_per_area
     *
     * @param none
     * @return none
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

        $whereparams = array('surveyproid' => $this->surveypro->id, 'plugin' => $this->templateuseritem); // was static 'radiobutton'
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

    /**
     * output_summarydata
     *
     * @param none
     * @return none
     */
    public function output_summarydata() {
        if ($this->canaccessreports) {
            $paramnexturl = array();
            $paramnexturl['s'] = $this->surveypro->id;
            $paramnexturl['type'] = 'scales';
            // $paramnexturl['group'] = 0;
            // $paramnexturl['area'] = 0;
            $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);
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

    /**
     * fetch_summarydata
     *
     * @param none
     * @return none
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
        $item = surveypro_get_item($this->cm, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem); // was static 'radiobutton'
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
                $sql = 'SELECT COUNT(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                        FROM {surveypro_answer} ud
                        WHERE ud.itemid IN ('.implode(',', $areaidlist).')';
                $aggregate = $DB->get_record_sql($sql);
                $m = $aggregate->sumofanswers / $aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                $sql = 'SELECT ud.content
                        FROM {surveypro_answer} ud
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
                    $sql = 'SELECT COUNT(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                            FROM {surveypro_answer} ud
                              JOIN {surveypro_submission} ss ON ss.id = ud.submissionid
                            WHERE ud.itemid IN ('.implode(',', $areaidlist).')
                              AND ss.userid = :userid';
                    $aggregate = $DB->get_record_sql($sql, $whereparams);

                    if ($aggregate->countofanswers) {
                        $m = $aggregate->sumofanswers / $aggregate->countofanswers;
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

    /**
     * output_scalesdata
     *
     * @param none
     * @return none
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
            $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);

            $paramurl['area'] = $area;
            $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);

            $this->output_html($nexturl, $graphurl, 'scalesreport');
        }
    }

    /**
     * fetch_scalesdata
     *
     * @param int $area
     * @return none
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
        $item = surveypro_get_item($this->cm, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem); // was static 'radiobutton'
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
                // Changed to a shorter version on September 25, 2014.
                // Older version will be deleted as soon as the wew one will be checked.
                // $sql = 'SELECT COUNT(ud.id) as countofanswers, SUM(ud.content) as sumofanswers
                //         FROM {surveypro_answer} ud
                //         WHERE ud.itemid = :itemid';
                // $whereparams = array('itemid' => $itemid);
                // $aggregate = $DB->get_record_sql($sql, $whereparams);

                // verified on October 17. It seems it arrived the time to delete the long version of the query.
                $whereparams = array('itemid' => $itemid);
                $aggregate = $DB->get_record('surveypro_answer', $whereparams, 'COUNT(id) as countofanswers, SUM(content) as sumofanswers');
                $m = $aggregate->sumofanswers / $aggregate->countofanswers;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                // Changed to a shorter version on September 25, 2014.
                // Older version will be deleted as soon as the wew one will be checked.
                // $sql = 'SELECT ud.content
                //         FROM {surveypro_answer} ud
                //         WHERE ud.itemid = :itemid';
                // $answers = $DB->get_recordset_sql($sql, $whereparams);
                // $whereparams = array('itemid' => $itemid); // already defined
                $answers = $DB->get_recordset('surveypro_answer', $whereparams, '', 'content');
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

    /**
     * output_questionsdata
     *
     * @param int $area
     * @return none
     */
    public function output_questionsdata($area=false) {
        $paramnexturl = array();
        $paramnexturl['s'] = $this->surveypro->id;
        $paramnexturl['type'] = 'summary';
        // $paramnexturl['group'] = 0;
        $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['group'] = 0;
        $paramurl['type'] = 'questions';

        if ($area === false) {
            $areas = array(0, 1, 2, 3, 4, 5);
        } else {
            $areas = array($area);
        }

        foreach ($areas as $area) {
            $paramurl['area'] = $area;
            for ($qid = 0; $qid < 4; $qid++) { // 0..3
                $paramurl['qid'] = $qid;
                $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);
                $this->output_html($nexturl, $graphurl, 'questionsreport');
            }
        }
    }

    /**
     * fetch_questionsdata
     *
     * @param int $area
     * @param int $qid
     * @return none
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
        $item = surveypro_get_item($this->cm, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem); // was static 'radiobutton'
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
                    FROM {surveypro_answer}
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
