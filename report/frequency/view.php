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
 * Starting page of the frequency report.
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;
use mod_surveypro\utility_layout;
use surveyproreport_frequency\filterform;
use surveyproreport_frequency\report;

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/surveypro/report/frequency/lib.php');
require_once($CFG->libdir.'/tablelib.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);

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

$utilitypageman = new utility_page($cm, $surveypro);

// Set $PAGE params.
// $url = new \moodle_url('/mod/surveypro/reports.php', ['s' => $surveypro->id, 'report' => 'frequency']);
$paramurl = ['s' => $surveypro->id, 'area' => 'reports', 'report' => 'frequency', 'section' => 'view'];
$url = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('pluginname', 'surveyproreport_frequency'));
// Is it useful? $PAGE->add_body_class('mediumwidth');.
// End of: set $PAGE deatils.

$utilitypageman->manage_editbutton($edit);

$reportman = new report($cm, $context, $surveypro);
$reportman->setup();

// Begin of: instance filterform.
$showjumper = $reportman->is_groupjumper_needed();

$formurl = new \moodle_url('/mod/surveypro/report/frequency/view.php', ['s' => $cm->instance]);

$formparams = new \stdClass();
$formparams->surveypro = $surveypro;
$formparams->showjumper = $showjumper;
if ($showjumper) {
    $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

    $jumpercontent = $reportman->get_groupjumper_items();

    $formparams->canaccessallgroups = $canaccessallgroups;
    $formparams->addnotinanygroup = $reportman->add_notinanygroup();
    $formparams->jumpercontent = $jumpercontent;
}

$filterform = new filterform($formurl, $formparams); // No autosubmit, here.
// End of: instance filterform.

// Output starts here.
echo $OUTPUT->header();

$actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
echo $actionbar->draw_reports_action_bar();

$reportman->prevent_direct_user_input();
$reportman->stop_if_textareas_only();

// Begin of: manage form submission.
if ($fromform = $filterform->get_data()) {
    $itemid = $fromform->itemid;
    if ($showjumper) {
        $groupid = $fromform->groupid;
        $reportman->set_groupid($groupid);
    }
}
// End of: manage form submission.

$filterform->display();

if (!empty($itemid)) {
    $reportman->setup_outputtable($itemid);
    $reportman->fetch_data($itemid);

    $paramurl = [];
    $paramurl['id'] = $cm->id;
    if ($showjumper) {
        $paramurl['groupid'] = $groupid;
    }
    $paramurl['itemid'] = $itemid;
    $url = new \moodle_url('/mod/surveypro/report/frequency/graph.php', $paramurl);
    // To troubleshoot graph, open a new window in the broser and directly call
    // http://localhost/head/mod/surveypro/report/frequency/graph.php?id=xx&groupid=0&itemid=yyy
    // address.

    $reportman->output_data($url);
}

// Finish the page.
echo $OUTPUT->footer();
