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
require_once($CFG->dirroot.'/mod/surveypro/classes/tabs.class.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/view_userform.class.php');
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
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);
$submissionid = optional_param('submissionid', 0, PARAM_INT);

// Calculations.
$userformman = new mod_surveypro_userformmanager($cm, $context, $surveypro);
$userformman->set_submissionid($submissionid);
$userformman->set_view($view);
$userformman->set_formpage($formpage);

$userformman->prevent_direct_user_input();
$userformman->trigger_event($view);

$userformman->surveypro_add_custom_css();

$pageallowesubmission = ($userformman->modulepage != SURVEYPRO_SUBMISSION_READONLY);
$pageallowesubmission = $pageallowesubmission && ($userformman->modulepage != SURVEYPRO_ITEMS_PREVIEW);

// Begin of: define $user_form return url.
$paramurl = array('id' => $cm->id, 'view' => $view);
$formurl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
// End of: define $user_form return url.

// Begin of: prepare params for the form.
$formparams = new stdClass();
$formparams->cm = $cm; // Required to call surveypro_get_item.
$formparams->surveypro = $surveypro;
$formparams->submissionid = $submissionid;
$formparams->firstpageright = $userformman->firstpageright;
$formparams->maxassignedpage = $userformman->maxassignedpage;
$formparams->canaccessadvanceditems = $userformman->canaccessadvanceditems; // Help selecting the fields to show
$formparams->formpage = $userformman->formpage;
$formparams->modulepage = $userformman->modulepage; // This is the page to get corresponding fields.
$formparams->readonly = ($userformman->modulepage == SURVEYPRO_SUBMISSION_READONLY);
$formparams->preview = ($view == SURVEYPRO_PREVIEWSURVEYFORM);
// End of: prepare params for the form.

// if ($view == SURVEYPRO_READONLYRESPONSE) {$editable = false} else {$editable = true}
$userform = new mod_surveypro_submissionform($formurl, $formparams, 'post', '', array('id' => 'userentry'), ($view != SURVEYPRO_READONLYRESPONSE));

// Begin of: manage form submission.
if ($userform->is_cancelled()) {
    $localparamurl = array('id' => $cm->id, 'view' => $view, 'cover' => 0);
    $redirecturl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
    redirect($redirecturl, get_string('usercanceled', 'mod_surveypro'));
}

if ($userformman->formdata = $userform->get_data()) {
    if ($view != SURVEYPRO_PREVIEWSURVEYFORM) {
        $userformman->save_user_data(); // <-- SAVE SAVE SAVE SAVE.
        $userformman->notifypeople();
    }

    // If "pause" button has been pressed, redirect.
    $pausebutton = isset($userformman->formdata->pausebutton);
    if ($pausebutton) {
        $localparamurl = array('id' => $cm->id, 'view' => $view, 'cover' => 0);
        $redirecturl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
        redirect($redirecturl); // Go somewhere.
    }

    $paramurl['submissionid'] = $userformman->submissionid;

    // If "previous" button has been pressed, redirect.
    $prevbutton = isset($userformman->formdata->prevbutton);
    if ($prevbutton) {
        $userformman->next_not_empty_page(false, $userformman->formpage, $userformman->modulepage);
        $paramurl['formpage'] = $userformman->firstpageleft;
        $redirecturl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
        redirect($redirecturl); // -> go to the first non empty previous page of the form
    }

    // If "next" button has been pressed, redirect.
    $nextbutton = isset($userformman->formdata->nextbutton);
    if ($nextbutton) {
        $userformman->next_not_empty_page(true, $userformman->formpage, $userformman->modulepage);

        // Ok, I am leaving page $userformman->formpage
        // to go to page $userformman->firstpageright.
        // I need to delete all the answer that were (maybe) written during a previous walk along the surveypro.
        // Data of each item in a page between ($this->formpage + 1), ($this->firstpageright - 1) included, must be deleted.
        //
        // Example: I am leaving page 3. On the basis of current input $userformman->firstpageright is 10.
        // Maybe yesterday I had different data in $userformman->formpage = 3 and on that basis I was redirected to page 4.
        // Now that data of $userformman->formpage = 3 redirects me to page 10, for sure answers to items in page 4 have to be deleted.
        $userformman->drop_jumped_saved_data();

        $paramurl['formpage'] = $userformman->firstpageright;
        $redirecturl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
        redirect($redirecturl); // -> go to the first non empty next page of the form
    }
}
// End of: manage form submission.

// Output starts here.
$paramurl = array('s' => $surveypro->id, 'view' => $view);
if (!empty($submissionid)) {
    $paramurl['submissionid'] = $submissionid;
}
$url = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// Make bold the navigation menu/link that refers to me.
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$tabman = new mod_surveypro_tabs($cm, $context, $surveypro, $userformman->moduletab, $userformman->modulepage);

// Begin of: if surveypro is without items, alert and stop.
if (!$userformman->hasitems) {
    $userformman->noitem_stopexecution();
}
// End of: if surveypro is without items, alert and stop.

// Begin of: is the user allowed to submit one more surveypro?
if ($pageallowesubmission) {
    if (!$userformman->submissions_allowed()) {
        $userformman->submissions_exceeded_stopexecution();
    }
    // } else {
    // I am editing an "in progress" submission.
    // You are always allowed to carry on with your "in progress" submission.
}
// End of: is the user allowed to submit one more surveypro?

// Begin of: manage the thanks page.
if ($pageallowesubmission) {
    $userformman->manage_thanks_page();
}
// End of: manage the thanks page.

// Begin of: display an alert to explain why buttons are missing.
$userformman->message_preview_mode();
// End of: display an alert to explain why buttons are missing.

// Begin of: display orientation text: page xx of yy.
$userformman->display_page_x_of_y();
// End of: display orientation text: page xx of yy

// Begin of: calculate prefill for fields and prepare standard editors and filemanager.
// If sumission already exists.
$prefill = $userformman->get_prefill_data();
$prefill['formpage'] = $userformman->formpage;
// End of: calculate prefill for fields and prepare standard editors and filemanager.

$userform->set_data($prefill);
$userform->display();

// If surveypro is multipage and $userformman->modulepage == SURVEYPRO_READONLYRESPONSE.
// I need to add navigation buttons manually
// Because the surveypro is not displayed as a form but as a simple list of graphic user items.
$userformman->add_readonly_browsing_buttons();

// Begin of: display an alert to explain why buttons are missing.
$userformman->message_preview_mode();
// End of: display an alert to explain why buttons are missing.

// Finish the page.
echo $OUTPUT->footer();
