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
 * Starting page of the lateusers report.
 *
 * @package   surveyproreport_lateusers
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\tabs;
use surveyproreport_lateusers\groupjumperform;
use surveyproreport_lateusers\report;

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
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
$cm = cm_info::create($cm);

$groupid = optional_param('groupid', 0, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:accessreports', $context);

$reportman = new report($cm, $context, $surveypro);
$reportman->set_groupid($groupid);
$reportman->setup_outputtable();

// Begin of: define $mform return url.
$showjumper = $reportman->is_groupjumper_needed();
if ($showjumper) {
    $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

    $jumpercontent = $reportman->get_groupjumper_items();

    $paramurl = array('id' => $cm->id);
    $formurl = new \moodle_url('/mod/surveypro/report/lateusers/view.php', $paramurl);

    $formparams = new \stdClass();
    $formparams->canaccessallgroups = $canaccessallgroups;
    // Bloody tricky trap!
    // ALWAYS set $formparams->addnotinanygroup to false, here!
    // Don't set it to $reportman->add_notinanygroup();!

    // It is a logical fail. You can not ask for users...
    // "not in any group" (alias: "not enrolled") && "still not submitting"
    // because guest user will ALWAYS be counted.
    // If a user is "not enrolled", of course he still didn't submit.

    // The "not in any group" item, here, IS A NONSENSE
    // it is needed when users...
    // ACTUALLY HAVE submissions EVEN IF they are not enrolled
    // but in this report I look for users WITHOUT submissions.
    $formparams->addnotinanygroup = false;
    $formparams->jumpercontent = $jumpercontent;
    $attributes = array('id' => 'surveypro_jumperform');
    $groupfilterform = new groupjumperform($formurl, $formparams, null, null, $attributes);

    $PAGE->requires->js_amd_inline("
    require(['jquery'], function($) {
        $('#id_groupid').change(function() {
            $('#surveypro_jumperform').submit();
        });
    });");
}
// End of: prepare params for the form.

// Output starts here.
$url = new \moodle_url('/mod/surveypro/report/lateusers/view.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
if (($edit != -1) and $PAGE->user_allowed_editing()) {
    $USER->editing = $edit;
}
if ($PAGE->user_allowed_editing()) {
    // Change URL parameter and block display string value depending on whether editing is enabled or not
    if ($PAGE->user_is_editing()) {
        $urlediting = 'off';
        $strediting = get_string('blockseditoff');
    } else {
        $urlediting = 'on';
        $strediting = get_string('blocksediton');
    }
    $url = new \moodle_url($CFG->wwwroot.'/mod/surveypro/report/lateusers/view.php', ['id' => $cm->id, 'edit' => $urlediting]);
    $PAGE->set_button($OUTPUT->single_button($url, $strediting));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);

$surveyproreportlist = get_plugin_list('surveyproreport');
$reportkey = array_search('lateusers', array_keys($surveyproreportlist));
new tabs($cm, $context, $surveypro, SURVEYPRO_TABREPORTS, $reportkey);

$reportman->prevent_direct_user_input();

if ($showjumper) {
    $groupfilterform->set_data(array('groupid' => $groupid));
    $groupfilterform->display();
}

$reportman->fetch_data();
$reportman->output_data();

// Finish the page.
echo $OUTPUT->footer();
