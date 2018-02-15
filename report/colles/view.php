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
 * Starting page of the colles report.
 *
 * @package   surveyproreport_colles
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/report/delayedusers/form/groupjumper_form.php');
require_once($CFG->dirroot.'/mod/surveypro/report/colles/lib.php');
require_once($CFG->libdir.'/tablelib.php');

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

$type = optional_param('type', 'summary', PARAM_ALPHA);  // Type of graph.
$area = optional_param('area', false, PARAM_INT);  // Area ID.

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
if ($type == 'summary') {
    if (!has_capability('mod/surveypro:accessreports', $context)) {
        require_capability('mod/surveypro:accessownreports', $context);
    }
} else {
    require_capability('mod/surveypro:accessreports', $context);
}

$reportman = new surveyproreport_colles_report($cm, $context, $surveypro);
$reportman->set_area($area);

// Begin of: define $mform return url.
$showjumper = $reportman->is_groupjumper_needed();
if ($showjumper) {
    $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

    $jumpercontent = $reportman->get_groupjumper_items();

    $paramurl = array('id' => $cm->id);
    $paramurl['type'] = $type;
    $paramurl['area'] = $area;
    $formurl = new moodle_url('/mod/surveypro/report/colles/view.php', $paramurl);

    $formparams = new stdClass();
    $formparams->canaccessallgroups = $canaccessallgroups;
    $formparams->addnotinanygroup = $reportman->add_notinanygroup();
    $formparams->jumpercontent = $jumpercontent;
    $attributes = array('id' => 'surveypro_jumperform');
    $groupfilterform = new mod_surveypro_colles_groupjumper($formurl, $formparams, null, null, $attributes);

    $PAGE->requires->js_amd_inline("
    require(['jquery'], function($) {
        $('#id_groupid').change(function() {
            $('#surveypro_jumperform').submit();
        });
    });");
}
// End of: prepare params for the form.

// Output starts here.
$paramurl = array('s' => $surveypro->id, 'type' => $type);
if ( ($type == 'questions') && ($area !== false) ) { // Area can be zero.
    $paramurl['area'] = $area;
}

$url = new moodle_url('/mod/surveypro/report/colles/view.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_REPORT);

$reportman->nosubmissions_stop();

// Begin of: manage form submission.
if ( $showjumper && ($fromform = $groupfilterform->get_data()) ) {
    $reportman->set_groupid($fromform->groupid);
}
// End of: manage form submission.

if ($showjumper) {
    $groupfilterform->display();
}

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

// Finish the page.
echo $OUTPUT->footer();
