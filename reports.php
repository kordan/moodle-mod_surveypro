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
 * Starting page for item management.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
$report = optional_param('report', null, PARAM_TEXT); // Requested report. Section is the report name.

if (!empty($id)) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'surveypro');
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

if (isset($report)) {
    $classname = 'surveyproreport_'.$report.'\report';
    $reportman = new $classname($cm, $context, $surveypro);
    $reportman->setup();

    $reportman->set_additionalparams();
    $paramurl = $reportman->get_paramurl();

    $returnurl = new \moodle_url('/mod/surveypro/report/'.$report.'/view.php', $paramurl);
    redirect($returnurl);
}

// If you are still here, redirect to the first report (if it exists).
// $report is not set otherwise I would not be here. I can overwrite the variable.
$report = surveypro_get_first_allowed_report();
if (!empty($report)) {
    if (!isset($section)) {
        // Use default. Default is ALWAYS 'view'.
        $section = 'view';
    }
    $paramurl = ['s' => $cm->instance, 'report' => $report, 'section' => $section];
    $returnurl = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
    redirect($returnurl);
}

// You should never arrive here because you were redirected to a report.
// If no report is available, warn the user.

// Set $PAGE params.
$url = new \moodle_url('/mod/surveypro/reports.php', ['s' => $surveypro->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('reports', 'mod_surveypro'), $url);
// Is it useful? $PAGE->add_body_class('mediumwidth');.

// Output starts here.
echo $OUTPUT->header();

echo $OUTPUT->box('noreportsfound', 'generalbox description', 'intro', 'centerpara');

$actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
echo $actionbar->draw_reports_action_bar();

// Finish the page.
echo $OUTPUT->footer();
