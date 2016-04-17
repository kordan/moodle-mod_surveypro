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
 * Starting page to display graphs of the frequency report.
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../../config.php');
require_once($CFG->libdir.'/graphlib.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/frequency/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID
$itemid = required_param('itemid', PARAM_INT); // Item ID
$submissionscount = required_param('submissionscount', PARAM_INT); // Submissions count
$group = optional_param('group', 0, PARAM_INT); // Group ID

$cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

$context = context_module::instance($cm->id);

require_login($course, false, $cm);
require_capability('mod/surveypro:accessreports', $context);

$groupmode = groups_get_activity_groupmode($cm, $course);   // Groups are being used

$item = surveypro_get_item($cm, $surveypro, $itemid);

$whereparams = array('itemid' => $itemid);
$sql = 'SELECT content, count(id) as absolute
        FROM {surveypro_answer}
        WHERE itemid = :itemid
        GROUP BY content
        ORDER BY content';
$answers = $DB->get_recordset_sql($sql, $whereparams);

$counted = 0;
$content = array();
$absolute = array();
foreach ($answers as $answer) {
    $content[] = $item->userform_db_to_export($answer);
    $absolute[] = $answer->absolute;
    $counted += $answer->absolute;
}
if ($counted < $submissionscount) {
    $content[] = get_string('answernotpresent', 'surveyproreport_frequency');
    $absolute[] = ($submissionscount - $counted);
}

$answers->close();

$graph = new graph(SURVEYPROREPORT_FREQUENCY_GWIDTH, SURVEYPROREPORT_FREQUENCY_GHEIGHT);
$graph->parameter['title'] = '';

$graph->x_data = $content;
$graph->y_data['answers1'] = $absolute;
$graph->y_format['answers1'] = array('colour' => 'ltblue', 'bar' => 'fill', 'legend' => strip_tags($item->get_content()), 'bar_size' => 0.4);

$graph->parameter['legend'] = 'outside-left';
$graph->parameter['inner_padding'] = 20;
$graph->parameter['legend_size'] = 9;
$graph->parameter['legend_border'] = 'black';
$graph->parameter['legend_offset'] = 4;

$graph->y_order = array('answers1');

// $graph->parameter['x_axis_gridlines'] can not be set to a number because X axis is not numeric
$graph->parameter['y_axis_gridlines'] = 2 + max($absolute);
$graph->parameter['y_resolution_left'] = 1;
$graph->parameter['y_decimal_left'] = 0;
$graph->parameter['y_max_left'] = 1 + max($absolute);
$graph->parameter['y_max_right'] = 1 + max($absolute);
$graph->parameter['x_axis_angle'] = 0;
$graph->parameter['shadow'] = 'none';

// $graph->y_tick_labels = $absolute;
$graph->y_tick_labels = null;
$graph->offset_relation = null;

$graph->draw_stack();

exit;
