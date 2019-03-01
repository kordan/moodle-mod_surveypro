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
 * Starting page to display the user input form.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');require_once($CFG->dirroot.'/mod/surveypro/form/outform/fill_form.php');

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
$submissionid = optional_param('submissionid', 0, PARAM_INT);
$formpage = optional_param('formpage', 0, PARAM_INT); // Form page number.
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);

// Calculations.
mod_surveypro_utility_mform::register_form_elements();

$userformman = new mod_surveypro_view_form($cm, $context, $surveypro);
$userformman->setup($submissionid, $formpage, $view);

$utilitysubmissionman = new mod_surveypro_utility_submission($cm, $surveypro);
$utilitysubmissionman->add_custom_css();

// Begin of: define $user_form return url.
$paramurl = array('id' => $cm->id, 'view' => $view);
$formurl = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
// End of: define $user_form return url.

// Begin of: prepare params for the form.
$formparams = new stdClass();
$formparams->cm = $cm;
$formparams->surveypro = $surveypro;
$formparams->submissionid = $submissionid;
$formparams->maxassignedpage = $userformman->get_maxassignedpage();
$formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
$formparams->formpage = $userformman->get_formpage(); // The page of the form to select subset of fields
$formparams->tabpage = $userformman->get_tabpage(); // The page of the TAB-PAGE structure.
$formparams->readonly = ($userformman->get_tabpage() == SURVEYPRO_SUBMISSION_READONLY);
$formparams->preview = false;
// End of: prepare params for the form.

$editable = ($view == SURVEYPRO_READONLYRESPONSE) ? false : true;
$outform = new mod_surveypro_outform($formurl, $formparams, 'post', '', array('id' => 'userentry'), $editable);

// Begin of: manage form submission.
if ($outform->is_cancelled()) {
    $localparamurl = array('id' => $cm->id, 'view' => $view);
    $redirecturl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
    redirect($redirecturl, get_string('usercanceled', 'mod_surveypro'));
}

if ($userformman->formdata = $outform->get_data()) {
    $userformman->save_user_data(); // SAVE SAVE SAVE SAVE.
    $userformman->notifypeople();

    // If "pause" button has been pressed, redirect.
    $pausebutton = isset($userformman->formdata->pausebutton);
    if ($pausebutton) {
        $localparamurl = array('id' => $cm->id, 'view' => $view);
        $redirecturl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
        redirect($redirecturl); // Go somewhere.
    }

    $paramurl['submissionid'] = $userformman->get_submissionid();

    // If "previous" button has been pressed, redirect.
    $prevbutton = isset($userformman->formdata->prevbutton);
    if ($prevbutton) {
        $userformman->next_not_empty_page(false);
        $paramurl['formpage'] = $userformman->get_nextpageleft();
        $redirecturl = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
        redirect($redirecturl); // Redirect to the first non empty page.
    }

    // If "next" button has been pressed, redirect.
    $nextbutton = isset($userformman->formdata->nextbutton);
    if ($nextbutton) {
        $userformman->next_not_empty_page(true);

        // Ok, I am moving from $userformman->formpage to page $userformman->nextpageright.
        // I need to delete all the answer that were (maybe) written during a previous walk along the surveypro.
        // Answers to each item in a page between ($this->formpage + 1) and ($this->nextpageright - 1) included, must be deleted.
        //
        // Let's suppose the following scenario.
        // 1) User is filling a surveypro divided into 15 pages.
        // 2) User fills all the fields of page 3 and push next to move to the next page.
        // 3) On the basis of current input, $userformman->nextpageright is 4 so page 4 is displayed.
        // 4) User fills all the fields of page 4 and push next to move to the next page.
        // 5) On the basis of current input, $userformman->nextpageright is 5 so page 5 is displayed.
        // 6) Once arrived in page 5 user returns back up to page 3.
        // 7) User changes the answers in page 3 and push next to move to the next page.
        // 8) On the basis of current input, $userformman->nextpageright is 10 so page 10 is displayed.
        // 9) Now that the answers to items in page 3 move me to page 10, for sure answers to items in page 4 must be deleted.
        $userformman->drop_jumped_saved_data();

        $paramurl['formpage'] = $userformman->get_nextpageright();
        $redirecturl = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
        redirect($redirecturl); // Redirect to the first non empty page.
    }

    // If none redirect you, reload THE RIGHT page WITHOUT $paramurl['view'].
    // This is necessary otherwise if the user switches language using the corresponding menu
    // just after a new response is submitted
    // the browser redirects to http://localhost/head_behat/mod/surveypro/view_form.php?s=xxx&view=1&lang=it
    // and not               to http://localhost/head_behat/mod/surveypro/view.php?s=xxx&lang=it
    // alias it goes to the page to get one more response
    // instead of remaining in the view submissions page.
    $paramurl = array();
    $paramurl['s'] = $surveypro->id;
    $paramurl['responsestatus'] = $userformman->get_responsestatus();
    $paramurl['justsubmitted'] = 1;
    $paramurl['formview'] = $userformman->get_view(); // What was I viewing in the form?
    $redirecturl = new moodle_url('/mod/surveypro/view.php', $paramurl);
    redirect($redirecturl); // Redirect to the first non empty page.
}
// End of: manage form submission.

// Output starts here.
$paramurl = array('s' => $surveypro->id, 'view' => $view);
if (!empty($submissionid)) {
    $paramurl['submissionid'] = $submissionid;
}
$url = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, $userformman->get_tabtab(), $userformman->get_tabpage());

$userformman->noitem_stopexecution();
$userformman->nomoresubmissions_stopexecution();
$userformman->warning_submission_copy();
$userformman->display_page_x_of_y();

// Begin of: calculate prefill for fields and prepare standard editors and filemanager.
// If sumission already exists.
$prefill = $userformman->get_prefill_data();
$prefill['formpage'] = $userformman->get_formpage();
// End of: calculate prefill for fields and prepare standard editors and filemanager.

$outform->set_data($prefill);
$outform->display();

// If surveypro is multipage and $userformman->tabpage == SURVEYPRO_READONLYRESPONSE.
// I need to add navigation buttons manually
// Because the surveypro is not displayed as a form but as a simple list of graphic user items.
$userformman->add_readonly_browsing_buttons();

// Finish the page.
echo $OUTPUT->footer();
