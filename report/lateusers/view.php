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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;
use surveyproreport_lateusers\groupjumperform;
use surveyproreport_lateusers\report;

require_once(dirname(__FILE__).'/../../../../config.php');
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

// Get additional specific params.
$groupid = optional_param('groupid', 0, PARAM_INT);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

$utilitypageman = new utility_page($cm, $surveypro);

// Set $PAGE params.
$paramurl = ['s' => $surveypro->id, 'area' => 'reports', 'report' => 'lateusers', 'section' => 'view'];
$url = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('pluginname', 'surveyproreport_lateusers'));
// Is it useful? $PAGE->add_body_class('mediumwidth');.
// End of: set $PAGE deatils.

$utilitypageman->manage_editbutton($edit);

$reportman = new report($cm, $context, $surveypro);
$reportman->setup();
$reportman->set_groupid($groupid);
$reportman->setup_outputtable();

// Begin of: define $mform return url.
$showjumper = $reportman->is_groupjumper_needed();
if ($showjumper) {
    $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

    $jumpercontent = $reportman->get_groupjumper_items();

    $formurl = new \moodle_url('/mod/surveypro/report/lateusers/view.php', ['s' => $cm->instance]);

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
    $attributes = ['id' => 'surveypro_jumperform'];
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
echo $OUTPUT->header();

$actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
echo $actionbar->draw_reports_action_bar();

$reportman->prevent_direct_user_input();

if ($showjumper) {
    $groupfilterform->set_data(['groupid' => $groupid]);
    $groupfilterform->display();
}

$reportman->fetch_data();
$reportman->output_data();

// Finish the page.
echo $OUTPUT->footer();
