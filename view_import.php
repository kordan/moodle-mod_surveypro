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
 * Starting page to import user data.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');require_once($CFG->dirroot.'/mod/surveypro/form/data/import_form.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module id.
$s = optional_param('s', 0, PARAM_INT);   // Surveypro instance id.

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
require_capability('mod/surveypro:importdata', $context);

// Calculations.
$importman = new mod_surveypro_view_import($cm, $context, $surveypro);

// Begin of: define $mform return url.
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
// End of: define $mform return url.

// Begin of: prepare params for the form.
$importform = new mod_surveypro_importform($formurl);
// End of: prepare params for the form.

// Begin of: manage form submission.
if ($importman->formdata = $importform->get_data()) {
    $err = $importman->validate_csvcontent();
    if (empty($err)) {
        $importman->import_csv();
        $redirecturl = new moodle_url('/mod/surveypro/view.php', array('s' => $surveypro->id));
        redirect($redirecturl);
    }
}
// End of: manage form submission.

// Output starts here.
$PAGE->set_url('/mod/surveypro/view_import.php', array('s' => $surveypro->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_IMPORT);

if (!empty($err)) {
    if (isset($err->a)) {
        $message = get_string($err->key, 'mod_surveypro', $err->a);
    } else {
        $message = get_string($err->key, 'mod_surveypro');
    }
    echo $OUTPUT->notification($message, 'notifyproblem');

    $returnurl = new moodle_url('/mod/surveypro/view_import.php', array('s' => $surveypro->id));
    echo $OUTPUT->continue_button($returnurl);
} else {
    $importman->welcome_message();
    $importform->display();
}

// Finish the page.
echo $OUTPUT->footer();
