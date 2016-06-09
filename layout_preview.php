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
 * Starting page for layout preview.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/outform/fill_form.php');

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
$formpage = optional_param('formpage', 0, PARAM_INT); // Form page number.
$submissionid = optional_param('submissionid', 0, PARAM_INT);

// Calculations.
$previewman = new mod_surveypro_layout_preview($cm, $context, $surveypro);
$previewman->setup($submissionid, $formpage);

$utilityman = new mod_surveypro_utility($cm, $surveypro);
$utilityman->add_custom_css();

// Begin of: define $user_form return url.
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
// End of: define $user_form return url.

// Begin of: prepare params for the form.
$formparams = new stdClass();
$formparams->cm = $cm;
$formparams->surveypro = $surveypro;
$formparams->submissionid = $submissionid;
$formparams->maxassignedpage = $previewman->get_maxassignedpage();
$formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context, null, true);
$formparams->formpage = $previewman->get_formpage(); // The page of the form to select subset of fields
$formparams->tabpage = SURVEYPRO_LAYOUT_PREVIEW; // This is the page of the TAB-PAGE structure.
$formparams->readonly = false;
$formparams->preview = true;
// End of: prepare params for the form.

$userform = new mod_surveypro_outform($formurl, $formparams, 'post', '', array('id' => 'userentry'));

// Begin of: manage form submission.
if ($data = $userform->get_data()) {
    $paramurl['submissionid'] = $submissionid;

    // If "previous" button has been pressed, redirect.
    $prevbutton = isset($data->prevbutton);
    if ($prevbutton) {
        $paramurl['formpage'] = --$formpage;
        $redirecturl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
        redirect($redirecturl); // Go to the previous page of the form.
    }

    // If "next" button has been pressed, redirect.
    $nextbutton = isset($data->nextbutton);
    if ($nextbutton) {
        $paramurl['formpage'] = ++$formpage;
        $redirecturl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
        redirect($redirecturl); // Go to the next page of the form.
    }
}
// End of: manage form submission.

// Output starts here.
$paramurl = array('s' => $surveypro->id);
if (!empty($submissionid)) {
    $paramurl['submissionid'] = $submissionid;
}
$url = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABLAYOUT, SURVEYPRO_LAYOUT_PREVIEW);

$previewman->noitem_stopexecution();
$previewman->message_preview_mode();
$previewman->display_page_x_of_y();

// Begin of: calculate prefill for fields and prepare standard editors and filemanager.
$prefill = $previewman->get_prefill_data();
$prefill['formpage'] = $previewman->get_formpage();
// End of: calculate prefill for fields and prepare standard editors and filemanager.

$userform->set_data($prefill);
$userform->display();

$previewman->message_preview_mode();

// Finish the page.
echo $OUTPUT->footer();
