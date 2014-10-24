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

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/lib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/classes/report.class.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
if (!empty($id)) {
    $cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', array('id' => $s), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $surveypro->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

$type = optional_param('type', 'summary', PARAM_ALPHA);  // type of graph
$group = optional_param('group', 0, PARAM_INT);  // Group ID
$area = optional_param('area', false, PARAM_INT);  // Student ID
$qid = optional_param('qid', 0, PARAM_INT);  // 0..3 the question in the area

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);

$paramurl = array('type' => $type, 's' => $surveypro->id);
if (!empty($group)) {
    $paramurl['group'] = $group;
}
if ($area) {
    $paramurl['area'] = $area;
}
if (!empty($qid)) {
    $paramurl['qid'] = $qid;
}

if ($type == 'summary') {
    if (!has_capability('mod/surveypro:accessreports', $context)) {
        require_capability('mod/surveypro:accessownreports', $context);
    }
} else {
    require_capability('mod/surveypro:accessreports', $context);
}

// -----------------------------
// output starts here
// -----------------------------
$url = new moodle_url('/mod/surveypro/report/colles/view.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
$modulepage = SURVEYPRO_SUBMISSION_REPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$hassubmissions = surveypro_count_submissions($surveypro->id);
$reportman = new mod_surveypro_report_colles($cm, $surveypro);
$reportman->setup($hassubmissions, $group, $area, $qid);
$reportman->check_submissions();
switch ($type) {
    case 'summary':
        $reportman->output_summarydata();
        break;
    case 'scales':
        $reportman->output_scalesdata();
        break;
    case 'questions':
        $reportman->output_questionsdata($area);
        break;
    case 'question':
    case 'students':
    case 'student':
        break;
}

// Finish the page
echo $OUTPUT->footer();
