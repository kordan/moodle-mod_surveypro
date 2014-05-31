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

require_once('../../../../config.php');
require_once($CFG->libdir.'/graphlib.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/classes/report.class.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$type = required_param('type', PARAM_ALPHA); // Group ID
$group = optional_param('group', 0, PARAM_INT); // Group ID
$area = optional_param('area', false, PARAM_INT);  // Student ID
$qid = optional_param('qid', 0, PARAM_INT);  // Group ID

$cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

$url = new moodle_url('/mod/surveypro/report/frequency/graph.php', array('id' => $id));
if ($group !== 0) {
    $url->param('group', $group);
}
$PAGE->set_url($url);

$cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, false, $cm);

$groupmode = groups_get_activity_groupmode($cm, $course);   // Groups are being used
$context = context_module::instance($cm->id);

if ($type == 'summary') {
    require_capability('mod/surveypro:accessownreports', $context);
} else {
    require_capability('mod/surveypro:accessreports', $context);
}

$reportman = new report_colles($cm, $surveypro);
$reportman->setup(true, $group, $area, $qid);

$graph = new graph(SURVEYPRO_GWIDTH, SURVEYPRO_GHEIGHT);
if ($type == 'summary') {
    $reportman->fetch_summarydata();

    // legend for $y_format_params
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    // x axis labels
    $graph->x_data = $reportman->xlabels;
    $graph->y_tick_labels = $reportman->ylabels;

    // $graph1_params
    $graph1_params = array();
    $graph1_params['colour'] = 'ltblue';
    $graph1_params['line'] = 'line';
    $graph1_params['point'] = 'square';
    $graph1_params['shadow_offset'] = 4;
    $graph1_params['legend'] = $legendgraph1;

    // 1st graph
    $graph->y_data['answers1'] = $reportman->trend1;
    $graph->y_format['answers1'] = $graph1_params;

    // $graph2_params
    $graph2_params = array();
    $graph2_params['colour'] = 'ltltblue';
    $graph2_params['bar'] = 'fill';
    $graph2_params['shadow_offset'] = 4;
    $graph2_params['legend'] = 'none';
    $graph2_params['bar_size'] = 0.3;

    // 2nd graph
    $graph->y_data['stdev1'] = $reportman->trend1stdev;
    $graph->y_format['stdev1'] = $graph2_params;

    $graph->offset_relation['stdev1'] = 'answers1';

    if ($reportman->template == 'collesactualpreferred') {
        // $graph3_params (the same as $graph1_params except for...)
        $graph1_params['colour'] = 'ltorange';
        $graph1_params['legend'] = $legendgraph2;

        // 3rd graph
        $graph->y_data['answers2']   = $reportman->trend2;
        $graph->y_format['answers2'] = $graph1_params;

        // $graph4_params (the same as $graph4_params except for...)
        $graph2_params['colour'] = 'ltltorange';
        $graph2_params['bar_size'] = 0.2;

        // 4th graph
        $graph->y_data['stdev2']   = $reportman->trend2stdev;
        $graph->y_format['stdev2'] = $graph2_params;

        $graph->offset_relation['stdev2'] = 'answers2';
    }

    $allowsingle = !$reportman->canaccessreports && $reportman->canaccessownreports;
    if ($allowsingle) { // if the user hasn't general right but only canaccessownreports
        if ($reportman->studenttrend1) { // if the user submitted at least one response
            $labelsep = get_string('labelsep', 'langconfig'); // ': '

            // $graph5_params (the same as $graph1_params except for...)
            $graph1_params['colour'] = 'blue';
            $graph1_params['legend'] = fullname($USER).$labelsep.$legendgraph1;

            // 5rd graph
            $graph->y_data['answers3'] = $reportman->studenttrend1;
            $graph->y_format['answers3'] = $graph1_params;

            if ($reportman->template == 'collesactualpreferred') {
                // $graph6_params (the same as $graph4_params except for...)
                $graph1_params['colour'] = 'orange';
                $graph1_params['legend'] = fullname($USER).$labelsep.$legendgraph2;

                // 6th graph
                $graph->y_data['answers4'] = $reportman->studenttrend2;
                $graph->y_format['answers4'] = $graph1_params;
            }
        }
    }

    // order of graphics
    if ($reportman->template == 'collesactualpreferred') {
        if ($allowsingle && ($reportman->studenttrend1)) { // if the user hasn't general right but only canaccessownreports && submitted at least one response
            $graph->y_order = array('stdev1', 'answers1', 'stdev2', 'answers2', 'answers3', 'answers4');
        } else {
            $graph->y_order = array('stdev1', 'answers1', 'stdev2', 'answers2');
        }
    } else {
        if ($allowsingle && ($reportman->studenttrend1)) { // if the user hasn't general right but only canaccessownreports && submitted at least one response
            $graph->y_order = array('stdev1', 'answers1', 'answers3', 'answers4');
        } else {
            $graph->y_order = array('stdev1', 'answers1');
        }
    }

    $graph->parameter['title'] = $reportman->graphtitle; // 'collespreferred', 'colleasctual'...
    $graph->parameter['legend'] = 'outside-top';
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $countoptions = count($reportman->ylabels);
    $graph->parameter['y_max_left'] = $countoptions - 1;
    $graph->parameter['y_axis_gridlines'] = $countoptions;
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left'] = 1;
    $graph->parameter['x_axis_angle'] = 0;
    $graph->parameter['x_inner_padding'] = 6;

    $graph->draw();
}

if ($type == 'scales') {
    $reportman->fetch_scalesdata($area);

    // legend for $y_format_params
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    $graph->parameter['title'] = $reportman->graphtitle; // 'Relevance'

    // x axis labels
    $graph->x_data = $reportman->xlabels; // array('focus on interesting issues', 'important to my practice'...
    $graph->y_tick_labels = $reportman->ylabels;

    // $graph1_params
    $graph1_params = array();
    $graph1_params['colour'] = 'ltblue';
    $graph1_params['line'] = 'line';
    $graph1_params['point'] = 'square';
    $graph1_params['shadow_offset'] = 4;
    $graph1_params['legend'] = $legendgraph1;

    // 1st graph
    $graph->y_data['answers1'] = $reportman->trend1; // array(1.5, 2.5...
    $graph->y_format['answers1'] = $graph1_params;

    // $graph2_params
    $graph2_params = array();
    $graph2_params['colour'] = 'ltltblue';
    $graph2_params['bar'] = 'fill';
    $graph2_params['shadow_offset'] = 4;
    $graph2_params['legend'] = 'none';
    $graph2_params['bar_size'] = 0.3;

    // 2nd graph
    $graph->y_data['stdev1'] = $reportman->trend1stdev; // array(1.1180339887499, 1.1180339887499...
    $graph->y_format['stdev1'] = $graph2_params;

    $graph->offset_relation['stdev1'] = 'answers1';

    if ($reportman->template == 'collesactualpreferred') {
        // $graph3_params (the same as $graph1_params except for...)
        $graph1_params['colour'] = 'ltorange';
        $graph1_params['legend'] = $legendgraph2;

        // 3rd graph
        $graph->y_data['answers2']   = $reportman->trend2;
        $graph->y_format['answers2'] = $graph1_params;

        // $graph4_params (the same as $graph4_params except for...)
        $graph2_params['colour'] = 'ltltorange';
        $graph2_params['bar_size'] = 0.2;

        // 4th graph
        $graph->y_data['stdev2']   = $reportman->trend2stdev;
        $graph->y_format['stdev2'] = $graph2_params;

        $graph->offset_relation['stdev2'] = 'answers2';

        $graph->y_order = array('stdev1', 'answers1', 'stdev2', 'answers2');
    } else {
        $graph->y_order = array('stdev1', 'answers1');
    }

    $graph->parameter['bar_size'] = 0.15;

    $graph->parameter['legend'] = 'outside-top';
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $countoptions = count($reportman->ylabels);
    $graph->parameter['y_max_left'] = $countoptions - 1;
    $graph->parameter['y_axis_gridlines'] = $countoptions;
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left'] = 1;
    $graph->parameter['x_axis_angle'] = 20;
    //$graph->parameter['x_inner_padding'] = 6;

    $graph->draw();
}

if ($type == 'questions') {
    $reportman->fetch_questionsdata($area, $qid);

    // legend for $y_format_params
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    $graph->parameter['title'] = $reportman->graphtitle; // $item->content

    $graph->x_data = $reportman->xlabels; // array('focus on interesting issues', 'important to my practice'...

    // $graph1_params
    $graph1_params = array();
    $graph1_params['colour'] = 'ltblue';
    $graph1_params['bar'] = 'fill';
    $graph1_params['legend'] = $legendgraph1;
    $graph1_params['bar_size'] = 0.4;

    // 1st graph
    $graph->y_data['answers1'] = $reportman->trend1; // array(1.5, 2.5...
    $graph->y_format['answers1'] = $graph1_params;

    if ($reportman->template == 'collesactualpreferred') {
        // $graph2_params (the same as $graph1_params except for...)
        $graph1_params['colour'] = 'ltorange';
        $graph1_params['legend'] = $legendgraph2;
        $graph1_params['bar_size'] = 0.2;

        // 2nd graph
        $graph->y_data['answers2'] = $reportman->trend2; // array(1.5, 2.5...
        $graph->y_format['answers2'] = $graph1_params;

        $graph->y_order = array('answers1', 'answers2');
    } else {
        $graph->y_order = array('answers1');
    }

    $graph->parameter['legend'] = 'outside-top';
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $countoptions = 1 + max(max($reportman->trend1), max($reportman->trend2));
    $graph->parameter['y_axis_gridlines'] = $countoptions;
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left'] = 0;
    $graph->parameter['x_axis_angle'] = 20;

    $graph->y_tick_labels = null;
    $graph->offset_relation = null;

    $graph->draw_stack();
}

exit;
