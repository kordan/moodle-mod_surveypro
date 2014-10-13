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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/view_import.class.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/data/import_form.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$s = optional_param('s', 0, PARAM_INT);   // surveypro instance ID

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

// -----------------------------
// calculations
// -----------------------------
$importman = new mod_surveypro_importmanager($cm, $surveypro);

// -----------------------------
// define $mform return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
// end of: define $mform return url
// -----------------------------

// -----------------------------
// prepare params for the form
$importform = new surveypro_importform($formurl);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission
if ($importman->formdata = $importform->get_data()) {
    $importman->import_csv();

    $redirecturl = new moodle_url('/mod/surveypro/view.php', array('s' => $surveypro->id, 'cover' => 0));
    redirect($redirecturl);
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/surveypro/view_import.php', array('s' => $surveypro->id));
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
$modulepage = SURVEYPRO_SUBMISSION_IMPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$importman->welcome_message();
$importform->display();

// Finish the page
echo $OUTPUT->footer();
