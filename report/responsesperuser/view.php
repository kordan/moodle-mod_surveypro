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
 * Starting page of the responsesperuser report.
 *
 * @package   surveyproreport_responsesperuser
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use surveyproreport_responsesperuser\groupjumperform;
use surveyproreport_responsesperuser\report;

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->libdir.'/tablelib.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);

if (!empty($id)) {
    $cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

// Get additional specific params.
$groupid = optional_param('groupid', 0, PARAM_INT);

$cm = cm_info::create($cm);
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

    $formurl = new \moodle_url('/mod/surveypro/report/responsesperuser/view.php', ['s' => $cm->instance]);

    $formparams = new \stdClass();
    $formparams->canaccessallgroups = $canaccessallgroups;
    $formparams->addnotinanygroup = $reportman->add_notinanygroup();
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

// Begin of: set $PAGE deatils.
$url = new \moodle_url('/mod/surveypro/reports.php', ['s' => $surveypro->id, 'report' => 'responsesperuser']);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('reports', 'mod_surveypro'), $url);
$PAGE->navbar->add(get_string('pluginname', 'surveyproreport_responsesperuser'));
// Is it useful? $PAGE->add_body_class('mediumwidth');.
// End of: set $PAGE deatils.

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
