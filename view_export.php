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
 * Prints a particular instance of surveypro
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/view_export.class.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/data/export_form.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$s = optional_param('s', 0, PARAM_INT);  // surveypro instance ID

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
require_capability('mod/surveypro:exportdata', $context);

// -----------------------------
// calculations
// -----------------------------
$exportman = new mod_surveypro_exportmanager($cm, $surveypro);

// -----------------------------
// define exportform return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('view_export.php', $paramurl);
// end of: define $mform return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->surveypro = $surveypro;
$formparams->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $context, null, true);
$exportform = new surveypro_exportform($formurl, $formparams);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission
if ($exportman->formdata = $exportform->get_data()) {
    if (!$exporterror = $exportman->surveypro_export()) {
        // All is fine!
        $exportman->trigger_event(); // event: all_submissions_exported

        die();
    }
} else {
    $exporterror = null;
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/surveypro/view.php', array('id' => $cm->id));
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
$modulepage = SURVEYPRO_SUBMISSION_EXPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

if ($exporterror == SURVEYPRO_NOFIELDSSELECTED) {
    echo $OUTPUT->box(get_string('nothingtodownload', 'surveypro'), 'generalbox boxaligncenter boxwidthnormal');
}

if ($exporterror == SURVEYPRO_NORECORDSFOUND) {
    echo $OUTPUT->box(get_string('emptydownload', 'surveypro'), 'generalbox boxaligncenter boxwidthnormal');
}

$exportform->display();

// Finish the page
echo $OUTPUT->footer();
