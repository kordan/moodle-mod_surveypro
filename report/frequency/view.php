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
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/tabs.class.php');
require_once($CFG->dirroot.'/mod/surveypro/report/frequency/classes/report.class.php');
require_once($CFG->dirroot.'/mod/surveypro/report/frequency/form/item_form.php');
require_once($CFG->dirroot.'/mod/surveypro/report/frequency/lib.php');
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

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:accessreports', $context);

// Calculations.
$utilityman = new mod_surveypro_utility($cm, $surveypro);
$hassubmissions = $utilityman->has_submissions();
$reportman = new mod_surveypro_report_frequency($cm, $context, $surveypro);
$reportman->setup_outputtable();

// Begin of: define $mform return url.
$paramurl = array('id' => $cm->id, 'rname' => 'frequency');
$formurl = new moodle_url('/mod/surveypro/report/frequency/view.php', $paramurl);
// End of: define $mform return url.

// Output starts here.
$url = new moodle_url('/mod/surveypro/report/frequency/view.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_REPORT);

// Begin of: stop here if only textareas are in the surveypro.
$reportman->stop_if_textareas_only();
// End of: stop here if only textareas are in the surveypro.

// Begin of: stop here if no submissions are available.
$reportman->nosubmissions_stop();
// End of: stop here if no submissions are available.

// Begin of: prepare params for the form.
$formparams = new stdClass();
$formparams->surveypro = $surveypro;
$formparams->answercount = $hassubmissions;
$mform = new mod_surveypro_chooseitemform($formurl, $formparams);
// End of: prepare params for the form.

// Begin of: display the form.
$mform->display();
// End of: display the form.

// Begin of: manage form submission.
if ($fromform = $mform->get_data()) {
    $reportman->fetch_data($fromform->itemid, $hassubmissions);

    $paramurl = array();
    $paramurl['id'] = $cm->id;
    $paramurl['group'] = 0;
    $paramurl['itemid'] = $fromform->itemid;
    $paramurl['submissionscount'] = $hassubmissions;
    $url = new moodle_url('/mod/surveypro/report/frequency/graph.php', $paramurl);
    // To troubleshoot graph, open a new window in the broser and directly call.
    // For instance: http://localhost/head/mod/surveypro/report/frequency/graph.php?id=xx&group=0&itemid=yyy&submissionscount=1

    $reportman->output_data($url);
}
// End of: manage form submission.

// Finish the page.
echo $OUTPUT->footer();
