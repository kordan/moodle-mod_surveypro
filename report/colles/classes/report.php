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
 * Surveypro class to manage colles report
 *
 * @package   surveyproreport_colles
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class to manage colles report
 *
 * @package   surveyproreport_colles
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_colles_report extends mod_surveypro_reportbase {

    /**
     * @var string $template
     */
    public $template;

    /**
     * @var int $id
     */
    public $area = 0;

    /**
     * @var string $templateuseritem
     */
    public $templateuseritem = '';

    /**
     * @var string $graphtitle
     */
    public $graphtitle = '';

    /**
     * @var array $xlabels
     */
    public $xlabels = array();

    /**
     * @var array $ylabels
     */
    public $ylabels = array();

    /**
     * @var array $trend1
     */
    public $trend1 = array();

    /**
     * @var array $trend1stdev
     */
    public $trend1stdev = array();

    /**
     * @var array $trend2
     */
    public $trend2 = array();

    /**
     * @var array $trend2stdev
     */
    public $trend2stdev = array();

    /**
     * @var array $studenttrend1
     */
    public $studenttrend1 = array();

    /**
     * @var array $studenttrend2
     */
    public $studenttrend2 = array();

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        global $DB;

        parent::__construct($cm, $context, $surveypro);

        $this->template = $DB->get_field('surveypro', 'template', array('id' => $this->surveypro->id));

        // Which plugin has been used to build this master template? Radiobutton or select?
        $guessplugin = array('radiobutton', 'select');
        $where = array('surveyproid' => $surveypro->id, 'plugin' => $guessplugin[0]);
        if ($DB->get_records('surveypro_item', $where, 'id', 'id')) {
            $this->templateuseritem = $guessplugin[0];
        } else {
            $this->templateuseritem = $guessplugin[1];
        }
    }

    /**
     * Set group.
     *
     * @param int $group
     * @return void
     */
    public function set_group($group) {
        $this->group = $group;
    }

    /**
     * Set area.
     *
     * @param int $area
     * @return void
     */
    public function set_area($area) {
        $this->area = $area;
    }

    /**
     * Get the list of mastertemplates to which this report is applicable.
     *
     * If ruturns an empty array, each report is added to admin menu
     * If returns a non empty array, only reports listed will be added to admin menu
     *
     * @return void
     */
    public function allowed_templates() {
        return array('collesactual', 'collespreferred', 'collesactualpreferred');
    }

    /**
     * Has_student_report.
     *
     * @return void
     */
    public function has_student_report() {
        return true;
    }

    /**
     * Get child reports.
     *
     * @param bool $canaccessreports
     * @return $childreports
     */
    public function has_childreports($canaccessreports) {
        if (!$canaccessreports) {
            return false;
        }

        $questionreports = array();
        $questionreports['fieldset_content_01'] = array('type' => 'questions', 'area' => 0);
        $questionreports['fieldset_content_02'] = array('type' => 'questions', 'area' => 1);
        $questionreports['fieldset_content_03'] = array('type' => 'questions', 'area' => 2);
        $questionreports['fieldset_content_04'] = array('type' => 'questions', 'area' => 3);
        $questionreports['fieldset_content_05'] = array('type' => 'questions', 'area' => 4);
        $questionreports['fieldset_content_06'] = array('type' => 'questions', 'area' => 5);

        $childreports = array();
        $childreports['summary'] = array('type' => 'summary');
        $childreports['scales'] = array('type' => 'scales');
        $childreports['areas'] = $questionreports;

        // In order to uncomment the next code to get examples of nested navigation into admin > report block,
        // you have to add strings corresponding to keys to $this->surveypro->template lang file.
        // $subfourtharray = array();
        // $subfourtharray['4.3.1'] = array('type' => 'fourth', 'foo' => 3, 'bar' => 1);
        // $subfourtharray['4.3.2'] = array('type' => 'fourth', 'foo' => 3, 'bar' => 2);
        // $subfourtharray['4.3.3'] = array('type' => 'fourth', 'foo' => 3, 'bar' => 3);

        // $fourtharray = array();
        // $fourtharray['4.1'] = array('type' => 'fourth', 'foo' => 1);
        // $fourtharray['4.2'] = array('type' => 'fourth', 'foo' => 2);
        // $fourtharray['4.3'] = $subfourtharray;

        // $childreports['fourth'] = $fourtharray;

        return $childreports;
    }

    /**
     * Output_html.
     *
     * @param string $nexturl
     * @param string $graphurl
     * @param string $altkey
     * @return void
     */
    public function output_html($nexturl, $graphurl, $altkey) {
        static $strseemoredetail; // Cache the string for the next future.

        if (empty($strseemoredetail)) {
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
     * Get q id per area.
     *
     * @return void
     */
    public function get_qid_per_area() {
        global $DB;

        $qid1area = array(); // Array of id of items referring to the trend 1.
        $qid2area = array(); // Array of id of items referring to the trend 2.
        $sql = 'SELECT i.id, i.sortindex, i.plugin
                FROM {surveypro_item} i
                WHERE i.surveyproid = :surveyproid
                    AND i.plugin = :plugin
                ORDER BY i.sortindex';

        $where = array('surveyproid' => $this->surveypro->id, 'plugin' => $this->templateuseritem);
        $itemseeds = $DB->get_recordset_sql($sql, $where);

        if ($this->template == 'collesactualpreferred') {
            $id1 = array(); // Id of items referring to preferred trend.
            $id2 = array(); // Id of items referring to actual trend.
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
            $id1 = array(); // Id of items referring to the trend 1 (it may be preferred such as actual).
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
     * Output_summarydata.
     *
     * @return void
     */
    public function output_summarydata() {
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);

        if ($canaccessreports) {
            $paramnexturl = array();
            $paramnexturl['s'] = $this->surveypro->id;
            $paramnexturl['type'] = 'scales';
            $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);
        } else {
            $nexturl = null;
        }

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['type'] = 'summary';
        $paramurl['groupid'] = $this->groupid;
        $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);

        $this->output_html($nexturl, $graphurl, 'summaryreport');
        // To debug a graph, open a new broser window and go to:
        // http://localhost/head/mod/surveypro/report/colles/graph.php?id=xxx&type=yyy&groupid=zzz
    }

    /**
     * Fetch_summarydata.
     *
     * @return void
     */
    public function fetch_summarydata() {
        global $DB, $USER;

        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);
        $this->graphtitle = get_string('summary', 'surveyproreport_colles');

        // Begin of: names of areas of investigation.
        for ($i = 1; $i < 7; $i++) {
            $this->xlabels[] = get_string('fieldset_content_0'.$i, 'surveyprotemplate_'.$this->template);
        }
        // End of: names of areas of investigation.

        // Begin of: group question id per area of investigation.
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // End of: group question id per area of investigation.

        // Begin of: options (label of answers).
        $itemid = $qid1area[0][0]; // One of the itemid of the surveypro (the first).
        $item = surveypro_get_item($this->cm, $this->surveypro, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem);
        $this->ylabels = $item->get_content_array(SURVEYPRO_LABELS, 'options');
        // End of: options (label of answers).

        // Begin of: calculate the mean and the standard deviation of answers.
        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area, $qid2area);
        } else {
            $toevaluate = array($qid1area);
        }
        foreach ($toevaluate as $k => $qidarea) {
            foreach ($qidarea as $areaidlist) {
                list($insql, $inparams) = $DB->get_in_or_equal($areaidlist, SQL_PARAMS_NAMED, 'areaid');
                $sql = 'SELECT COUNT(a.id) as answerscount, SUM(a.content) as sumofanswers
                        FROM {user} u
                            JOIN {surveypro_submission} s ON s.userid = u.id
                            JOIN {surveypro_answer} a ON a.submissionid = s.id';

                list($middlesql, $whereparams) = $this->get_middle_sql();
                $sql .= $middlesql;

                $whereparams = array_merge($whereparams, $inparams);
                $sql .= ' AND a.itemid '.$insql;

                $aggregate = $DB->get_record_sql($sql, $whereparams);
                $m = $aggregate->sumofanswers / $aggregate->answerscount;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                $sql = 'SELECT a.content
                        FROM {user} u
                            JOIN {surveypro_submission} s ON s.userid = u.id
                            JOIN {surveypro_answer} a ON a.submissionid = s.id';

                list($middlesql, $whereparams) = $this->get_middle_sql();
                $sql .= $middlesql;

                $whereparams = array_merge($whereparams, $inparams);
                $sql .= ' AND a.itemid '.$insql;

                $answers = $DB->get_recordset_sql($sql, $whereparams);
                $bigsum = 0;
                foreach ($answers as $answer) {
                    $xi = (double)$answer->content;
                    $bigsum += ($xi - $m) * ($xi - $m);
                }
                $answers->close();

                $bigsum /= $aggregate->answerscount;
                if ($k == 0) {
                    $this->trend1stdev[] = sqrt($bigsum);
                }
                if ($k == 1) {
                    $this->trend2stdev[] = sqrt($bigsum);
                }
            }
        }

        if (!$canaccessreports && $canaccessownreports) { // If the user hasn't general right but only canaccessownreports.
            foreach ($toevaluate as $k => $qidarea) {
                foreach ($qidarea as $areaidlist) {
                    list($insql, $whereparams) = $DB->get_in_or_equal($areaidlist, SQL_PARAMS_NAMED, 'areaid');
                    $whereparams['userid'] = $USER->id;
                    $sql = 'SELECT COUNT(a.id) as answerscount, SUM(a.content) as sumofanswers
                            FROM {surveypro_answer} a
                                JOIN {surveypro_submission} s ON s.id = a.submissionid
                            WHERE a.itemid '.$insql.'
                                AND s.userid = :userid';
                    $aggregate = $DB->get_record_sql($sql, $whereparams);

                    if ($aggregate->answerscount) {
                        $m = $aggregate->sumofanswers / $aggregate->answerscount;
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
        // End of: calculate the mean and the standard deviation of answers.
    }

    /**
     * Output_scalesdata.
     *
     * @return void
     */
    public function output_scalesdata() {
        $paramnexturl = array();
        $paramnexturl['s'] = $this->surveypro->id;
        $paramnexturl['type'] = 'questions';

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['groupid'] = $this->groupid;
        $paramurl['type'] = 'scales';

        for ($area = 0; $area < 6; $area++) {
            $paramnexturl['area'] = $area;
            $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);

            $paramurl['area'] = $area;
            $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);

            $this->output_html($nexturl, $graphurl, 'scalesreport');
        }
    }

    /**
     * Fetch_scalesdata.
     *
     * @param int $area
     * @return void
     */
    public function fetch_scalesdata($area=0) {
        global $DB;

        $this->graphtitle = get_string('fieldset_content_0'.($area + 1), 'surveyprotemplate_'.$this->template);

        // Begin of: names of areas of investigation.
        // Short names of questions.
        for ($i = 1; $i < 5; $i++) {
            $index = sprintf('%02d', 4 * $area + $i);
            $key = 'question'.$index.'short';
            $this->xlabels[] = get_string($key, 'surveyproreport_colles');
        }
        // End of: names of areas of investigation.

        // Begin of: group question id per area of investigation.
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // End of: group question id per area of investigation.

        // Begin of: options (label of answers).
        $itemid = $qid1area[0][0]; // One of the itemid of the surveypro (the first).
        $item = surveypro_get_item($this->cm, $this->surveypro, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem);
        $this->ylabels = $item->get_content_array(SURVEYPRO_LABELS, 'options');
        // End of: options (label of answers).

        // Begin of: calculate the mean and the standard deviation of answers.
        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area[$area], $qid2area[$area]);
        } else {
            $toevaluate = array($qid1area[$area]);
        }
        foreach ($toevaluate as $k => $areaidlist) {
            foreach ($areaidlist as $itemid) {
                $sql = 'SELECT COUNT(a.id) as answerscount, SUM(a.content) as sumofanswers
                        FROM {user} u
                            JOIN {surveypro_submission} s ON s.userid = u.id
                            JOIN {surveypro_answer} a ON a.submissionid = s.id';

                list($middlesql, $whereparams) = $this->get_middle_sql();
                $sql .= $middlesql.' AND a.itemid = :itemid';
                $whereparams['itemid'] = $itemid;

                $aggregate = $DB->get_record_sql($sql, $whereparams);
                $m = $aggregate->sumofanswers / $aggregate->answerscount;
                if ($k == 0) {
                    $this->trend1[] = $m;
                }
                if ($k == 1) {
                    $this->trend2[] = $m;
                }

                $sql = 'SELECT a.content
                        FROM {user} u
                            JOIN {surveypro_submission} s ON s.userid = u.id
                            JOIN {surveypro_answer} a ON a.submissionid = s.id';

                list($middlesql, $whereparams) = $this->get_middle_sql();
                $sql .= $middlesql.' AND a.itemid = :itemid';
                $whereparams['itemid'] = $itemid;

                $answers = $DB->get_recordset_sql($sql, $whereparams);
                $bigsum = 0;
                foreach ($answers as $answer) {
                    $xi = (double)$answer->content;
                    $bigsum += ($xi - $m) * ($xi - $m);
                }
                $bigsum /= $aggregate->answerscount;
                if ($k == 0) {
                    $this->trend1stdev[] = sqrt($bigsum);
                }
                if ($k == 1) {
                    $this->trend2stdev[] = sqrt($bigsum);
                }
                $answers->close();
            }
        }
        // End of: calculate the mean and the standard deviation of answers.
    }

    /**
     * Output_questionsdata.
     *
     * @param int $area
     * @return void
     */
    public function output_questionsdata($area) {
        $paramnexturl = array();
        $paramnexturl['s'] = $this->surveypro->id;
        if ($area == 5) {
            $paramnexturl['type'] = 'summary';
        } else {
            $paramnexturl['type'] = 'questions';
            $paramnexturl['area'] = 1 + $area % 5;
        }
        $nexturl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramnexturl);

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        $paramurl['groupid'] = $this->groupid;
        $paramurl['type'] = 'questions';

        $areas = array($area);

        foreach ($areas as $area) {
            $paramurl['area'] = $area;
            for ($qid = 0; $qid < 4; $qid++) { // The question ID: 0..3.
                $paramurl['qid'] = $qid;
                $graphurl = new moodle_url('/mod/surveypro/report/colles/graph.php', $paramurl);
                $this->output_html($nexturl, $graphurl, 'questionsreport');
            }
        }
    }

    /**
     * Fetch questions data.
     *
     * @param int $area
     * @param int $qid
     * @return void
     */
    public function fetch_questionsdata($area, $qid) {
        global $DB;

        // Begin of: group question id per area of investigation.
        list($qid1area, $qid2area) = $this->get_qid_per_area();
        // End of: group question id per area of investigation.

        // Begin of: options (label of answers).
        $itemid = $qid1area[$area][$qid]; // One of the itemid of the surveypro (the first).
        $item = surveypro_get_item($this->cm, $this->surveypro, $itemid, SURVEYPRO_TYPEFIELD, $this->templateuseritem);
        $this->xlabels = $item->get_content_array(SURVEYPRO_LABELS, 'options');
        // End of: options (label of answers).

        // Begin of: graph title.
        $this->graphtitle = strip_tags($item->get_content());
        // End of: graph title.

        // Begin of: calculate trend1 and, maybe, trend2.
        // Starts with empty defaults.
        for ($i = 0; $i < 5; $i++) {
            $this->trend1[] = 0;
            $this->trend2[] = 0;
        }

        if ($this->template == 'collesactualpreferred') {
            $toevaluate = array($qid1area[$area], $qid2area[$area]);
        } else {
            $toevaluate = array($qid1area[$area]);
        }
        foreach ($toevaluate as $k => $areaidlist) {
            $sql = 'SELECT content, count(a.id) as absolute
                    FROM {user} u
                        JOIN {surveypro_submission} s ON s.userid = u.id
                        JOIN {surveypro_answer} a ON a.submissionid = s.id';

            list($middlesql, $whereparams) = $this->get_middle_sql();
            $sql .= $middlesql.' AND a.itemid = :itemid GROUP BY content';
            $whereparams['itemid'] = $areaidlist[$qid];

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
        // End of: calculate trend1 and, maybe, trend2.
    }
}
