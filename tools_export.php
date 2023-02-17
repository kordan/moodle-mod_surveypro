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
 * Starting page to export data gathered.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\tabs;
use mod_surveypro\tools_export;
use mod_surveypro\local\form\submissionexportform;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module id.
$s = optional_param('s', 0, PARAM_INT);   // Surveypro instance id.

if (!empty($id)) {
    $cm = get_coursemodule_from_id('surveypro', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}
$cm = cm_info::create($cm);

$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:exportresponses', $context);

// Calculations.
$exportman = new tools_export($cm, $context, $surveypro);

// Begin of: define exportform return url.
$paramurl = ['id' => $cm->id];
$formurl = new \moodle_url('/mod/surveypro/tools_export.php', $paramurl);
// End of: define $mform return url.

// Begin of: prepare params for the form.
$formparams = new \stdClass();
$formparams->surveypro = $surveypro;
$formparams->activityisgrouped = groups_get_activity_groupmode($cm, $course);
$formparams->context = $context;
$formparams->attachmentshere = $exportman->are_attachments_onboard();
$exportform = new submissionexportform($formurl, $formparams, 'POST', '', ['data-double-submit-protection' => 'off']);
// End of: prepare params for the form.

// Begin of: manage form submission.
if ($exportman->formdata = $exportform->get_data()) {
    if (!$exporterror = $exportman->submissions_export()) {
        // All is fine!
        $exportman->trigger_event(); // Event: all_submissions_exported.

        die();
    }
} else {
    $exporterror = null;
}
// End of: manage form submission.

// Output starts here.
$PAGE->set_url('/mod/surveypro/tools_export.php', ['s' => $surveypro->id]);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();
// echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

new tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_EXPORT);

if ($exporterror == SURVEYPRO_NOFIELDSSELECTED) {
    echo $OUTPUT->notification(get_string('nothingtodownload', 'mod_surveypro'), 'notifyproblem');
}

if ($exporterror == SURVEYPRO_NORECORDSFOUND) {
    echo $OUTPUT->notification(get_string('emptydownload', 'mod_surveypro'), 'notifyproblem');
}

if ($exporterror == SURVEYPRO_NOATTACHMENTFOUND) {
    echo $OUTPUT->notification(get_string('noattachmentfound', 'mod_surveypro'), 'notifyproblem');
}

$exportman->welcome_message();
$exportform->display();

// Finish the page.
echo $OUTPUT->footer();
