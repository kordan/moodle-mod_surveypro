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
 * Starting page to display graphs of the colles report.
 *
 * @package   surveyproreport_colles
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use surveyproreport_colles\report;

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->libdir.'/graphlib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/lib.php');

$id = optional_param('id', 0, PARAM_INT);                      // Course_module id.
$s = optional_param('s', 0, PARAM_INT);                        // Surveypro instance id.
$type = required_param('type', PARAM_ALPHA); // Report type.

if (!empty($id)) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'surveypro');
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

$groupid = optional_param('groupid', 0, PARAM_INT); // Group ID.
$areaidx = optional_param('areaidx', 0, PARAM_INT);  // Report areaidx.
$qid = optional_param('qid', 0, PARAM_INT);  // Question ID.

require_login($course, false, $cm);

$context = \context_module::instance($cm->id);

if ($type == 'summary') {
    if (!has_capability('mod/surveypro:accessreports', $context)) {
        require_capability('mod/surveypro:accessownreports', $context);
    }
}

$reportman = new report($cm, $context, $surveypro);
$reportman->setup();
$reportman->set_areaidx($areaidx);
$reportman->set_groupid($groupid);

$graph = new graph(SURVEYPROREPORT_COLLES_GWIDTH, SURVEYPROREPORT_COLLES_GHEIGHT);
if ($type == 'summary') {
    $canaccessreports = has_capability('mod/surveypro:accessreports', $context);
    $canaccessownreports = has_capability('mod/surveypro:accessownreports', $context);

    $reportman->fetch_summarydata();

    // Legend for $y_format_params.
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    // X axis labels.
    $graph->x_data = $reportman->xlabels;
    $graph->y_tick_labels = $reportman->ylabels;

    // $graph1params.
    $graph1params = [];
    $graph1params['colour'] = 'ltblue';
    $graph1params['line'] = 'line';
    $graph1params['point'] = 'square';
    $graph1params['shadow_offset'] = 4;
    $graph1params['legend'] = $legendgraph1;

    // 1st graph.
    $graph->y_data['answers1'] = $reportman->trend1;
    $graph->y_format['answers1'] = $graph1params;

    // $graph2params.
    $graph2params = [];
    $graph2params['colour'] = 'ltltblue';
    $graph2params['bar'] = 'fill';
    $graph2params['shadow_offset'] = 4;
    $graph2params['legend'] = 'none';
    $graph2params['bar_size'] = 0.3;

    // 2nd graph.
    $graph->y_data['stdev1'] = $reportman->trend1stdev;
    $graph->y_format['stdev1'] = $graph2params;

    $graph->offset_relation['stdev1'] = 'answers1';

    if ($reportman->template == 'collesactualpreferred') {
        // $graph3params (the same as $graph1params except for...).
        $graph1params['colour'] = 'ltorange';
        $graph1params['legend'] = $legendgraph2;

        // 3rd graph.
        $graph->y_data['answers2'] = $reportman->trend2;
        $graph->y_format['answers2'] = $graph1params;

        // $graph4params (the same as $graph2params except for...).
        $graph2params['colour'] = 'ltltorange';
        $graph2params['bar_size'] = 0.2;

        // 4th graph.
        $graph->y_data['stdev2'] = $reportman->trend2stdev;
        $graph->y_format['stdev2'] = $graph2params;

        $graph->offset_relation['stdev2'] = 'answers2';
    }

    $allowsingle = !$canaccessreports && $canaccessownreports;
    if ($allowsingle) { // If the user hasn't general right but only canaccessownreports.
        if ($reportman->studenttrend1) { // If the user submitted at least one response.
            $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.

            // $graph5params (the same as $graph1params except for...).
            $graph1params['colour'] = 'blue';
            $graph1params['legend'] = fullname($USER).$labelsep.$legendgraph1;

            // 5rd graph.
            $graph->y_data['answers3'] = $reportman->studenttrend1;
            $graph->y_format['answers3'] = $graph1params;

            if ($reportman->template == 'collesactualpreferred') {
                // $graph6params (the same as $graph1params except for...).
                $graph1params['colour'] = 'orange';
                $graph1params['legend'] = fullname($USER).$labelsep.$legendgraph2;

                // 6th graph.
                $graph->y_data['answers4'] = $reportman->studenttrend2;
                $graph->y_format['answers4'] = $graph1params;
            }
        }
    }

    // Order of graphics.
    if ($reportman->template == 'collesactualpreferred') {
        if ($allowsingle && ($reportman->studenttrend1)) {
            // If the user hasn't general right but only canaccessownreports && submitted at least one response.
            $graph->y_order = ['stdev1', 'answers1', 'stdev2', 'answers2', 'answers3', 'answers4'];
        } else {
            $graph->y_order = ['stdev1', 'answers1', 'stdev2', 'answers2'];
        }
    } else {
        if ($allowsingle && ($reportman->studenttrend1)) {
            // If the user hasn't general right but only canaccessownreports && submitted at least one response.
            $graph->y_order = ['stdev1', 'answers1', 'answers3'];
        } else {
            $graph->y_order = ['stdev1', 'answers1'];
        }
    }

    $graph->parameter['title'] = $reportman->graphtitle; // 'collespreferred', 'collesctual'...
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
    $reportman->fetch_scalesdata($areaidx);

    // Legend for $y_format_params.
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    $graph->parameter['title'] = $reportman->graphtitle; // 'Relevance'.

    // X axis labels.
    $graph->x_data = $reportman->xlabels; // array('focus on interesting issues', 'important to my practice'...
    $graph->y_tick_labels = $reportman->ylabels;

    // $graph1params.
    $graph1params = [];
    $graph1params['colour'] = 'ltblue';
    $graph1params['line'] = 'line';
    $graph1params['point'] = 'square';
    $graph1params['shadow_offset'] = 4;
    $graph1params['legend'] = $legendgraph1;

    // 1st graph.
    $graph->y_data['answers1'] = $reportman->trend1; // array(1.5, 2.5...
    $graph->y_format['answers1'] = $graph1params;

    // $graph2params.
    $graph2params = [];
    $graph2params['colour'] = 'ltltblue';
    $graph2params['bar'] = 'fill';
    $graph2params['shadow_offset'] = 4;
    $graph2params['legend'] = 'none';
    $graph2params['bar_size'] = 0.3;

    // 2nd graph.
    $graph->y_data['stdev1'] = $reportman->trend1stdev; // array(1.1180339887499, 1.1180339887499...
    $graph->y_format['stdev1'] = $graph2params;

    $graph->offset_relation['stdev1'] = 'answers1';

    if ($reportman->template == 'collesactualpreferred') {
        // $graph3params (the same as $graph1params except for...).
        $graph1params['colour'] = 'ltorange';
        $graph1params['legend'] = $legendgraph2;

        // 3rd graph.
        $graph->y_data['answers2'] = $reportman->trend2;
        $graph->y_format['answers2'] = $graph1params;

        // $graph4params (the same as $graph2params except for...).
        $graph2params['colour'] = 'ltltorange';
        $graph2params['bar_size'] = 0.2;

        // 4th graph.
        $graph->y_data['stdev2'] = $reportman->trend2stdev;
        $graph->y_format['stdev2'] = $graph2params;

        $graph->offset_relation['stdev2'] = 'answers2';

        $graph->y_order = ['stdev1', 'answers1', 'stdev2', 'answers2'];
    } else {
        $graph->y_order = ['stdev1', 'answers1'];
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
    // $graph->parameter['x_inner_padding'] = 6;

    $graph->draw();
}

if ($type == 'questions') {
    $reportman->fetch_questionsdata($areaidx, $qid);

    // Legend for $y_format_params.
    if ($reportman->template == 'collesactualpreferred') {
        $legendgraph1 = get_string('collespreferred', 'surveyproreport_colles');
        $legendgraph2 = get_string('collesactual', 'surveyproreport_colles');
    } else {
        $legendgraph1 = get_string($reportman->template, 'surveyproreport_colles');
    }

    $graph->parameter['title'] = $reportman->graphtitle; // $item->content.

    $graph->x_data = $reportman->xlabels; // array('focus on interesting issues', 'important to my practice'...

    // $graph1params.
    $graph1params = [];
    $graph1params['colour'] = 'ltblue';
    $graph1params['bar'] = 'fill';
    $graph1params['legend'] = $legendgraph1;
    $graph1params['bar_size'] = 0.4;

    // 1st graph.
    $graph->y_data['answers1'] = $reportman->trend1; // array(1.5, 2.5...
    $graph->y_format['answers1'] = $graph1params;

    if ($reportman->template == 'collesactualpreferred') {
        // $graph2params (the same as $graph1params except for...).
        $graph1params['colour'] = 'ltorange';
        $graph1params['legend'] = $legendgraph2;
        $graph1params['bar_size'] = 0.2;

        // 2nd graph.
        $graph->y_data['answers2'] = $reportman->trend2; // array(1.5, 2.5...
        $graph->y_format['answers2'] = $graph1params;

        $graph->y_order = ['answers1', 'answers2'];
    } else {
        $graph->y_order = ['answers1'];
    }

    $graph->parameter['legend'] = 'outside-top';
    $graph->parameter['legend_border'] = 'black';
    $graph->parameter['legend_offset'] = 4;

    $countoptions = 1 + max(max($reportman->trend1), max($reportman->trend2));
    $graph->parameter['y_axis_gridlines'] = min(20, 2 + $countoptions);
    $graph->parameter['y_max_left'] = 1 + $countoptions;
    $graph->parameter['y_max_right'] = 1 + $countoptions;
    $graph->parameter['y_resolution_left'] = 1;
    $graph->parameter['y_decimal_left'] = 0;
    $graph->parameter['x_axis_angle'] = 20;

    $graph->y_tick_labels = null;
    $graph->offset_relation = null;

    $graph->draw_stack();
}

exit;
