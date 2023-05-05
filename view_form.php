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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_layout;
use mod_surveypro\tabs;
use mod_surveypro\utility_mform;
use mod_surveypro\view_form;
use mod_surveypro\local\form\userform;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

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

$submissionid = optional_param('submissionid', 0, PARAM_INT);
$formpage = optional_param('formpage', 1, PARAM_INT); // Form page number.
$overflowpage = optional_param('overflowpage', 0, PARAM_INT); // Went the user to a overflow page?
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);
$begin = optional_param('begin', 0, PARAM_INT);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Calculations.
mod_surveypro\utility_mform::register_form_elements();

$userformman = new view_form($cm, $context, $surveypro);
$userformman->setup($submissionid, $formpage, $view);

$utilitylayoutman = new utility_layout($cm, $surveypro);
$utilitylayoutman->add_custom_css();

// Begin of: define $user_form return url.
$paramurl = ['id' => $cm->id, 'view' => $view];
$formurl = new \moodle_url('/mod/surveypro/view_form.php', $paramurl);
// End of: define $user_form return url.

// Begin of: prepare params for the form.
$formparams = new \stdClass();
$formparams->cm = $cm;
$formparams->surveypro = $surveypro;
$formparams->submissionid = $submissionid;
$formparams->userformpagecount = $userformman->get_userformpagecount();
$formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
$formparams->userfirstpage = $userformman->get_userfirstpage(); // The user first page.
$formparams->userlastpage = $userformman->get_userlastpage(); // The user last page.
$formparams->overflowpage = $overflowpage; // Went the user to a overflow page?
$formparams->tabpage = $userformman->get_tabpage(); // The page of the TAB-PAGE structure.
$formparams->readonly = ($userformman->get_tabpage() == SURVEYPRO_SUBMISSION_READONLY);
$formparams->preview = false;
if ($begin == 1) {
    $userformman->next_not_empty_page(true, 0); // True means direction = right.
    $nextpage = $userformman->get_nextpage(); // The page of the form to select subset of fields.
    $userformman->set_formpage($nextpage);
}
$formparams->formpage = $userformman->get_formpage(); // The page of the form to select subset of fields.
// End of: prepare params for the form.

$editable = ($view == SURVEYPRO_READONLYRESPONSE) ? false : true;
$userform = new userform($formurl, $formparams, 'post', '', ['id' => 'userentry'], $editable);

// Begin of: manage form submission.
if ($userform->is_cancelled()) {
    $localparamurl = ['id' => $cm->id, 'view' => $view];
    $redirecturl = new \moodle_url('/mod/surveypro/view_submissions.php', $localparamurl);
    redirect($redirecturl, get_string('usercanceled', 'mod_surveypro'));
}

if ($userformman->formdata = $userform->get_data()) {
    $userformman->save_user_data(); // SAVE SAVE SAVE SAVE.

    // If "pause" button has been pressed, redirect.
    $pausebutton = isset($userformman->formdata->pausebutton);
    if ($pausebutton) {
        $localparamurl = ['id' => $cm->id, 'view' => $view];
        $redirecturl = new \moodle_url('/mod/surveypro/view_submissions.php', $localparamurl);
        redirect($redirecturl); // Go somewhere.
    }

    $paramurl['submissionid'] = $userformman->get_submissionid();

    // If "previous" button has been pressed, redirect.
    $prevbutton = isset($userformman->formdata->prevbutton);
    if ($prevbutton) {
        $userformman->next_not_empty_page(false);
        $paramurl['formpage'] = $userformman->get_nextpage();
        $paramurl['overflowpage'] = $userformman->get_overflowpage();
        $redirecturl = new \moodle_url('/mod/surveypro/view_form.php', $paramurl);
        redirect($redirecturl); // Redirect to the first non empty page.
    }

    // If "next" button has been pressed, redirect.
    $nextbutton = isset($userformman->formdata->nextbutton);
    if ($nextbutton) {
        $userformman->next_not_empty_page(true);
        $paramurl['formpage'] = $userformman->get_nextpage();
        $paramurl['overflowpage'] = $userformman->get_overflowpage();
        $redirecturl = new \moodle_url('/mod/surveypro/view_form.php', $paramurl);
        redirect($redirecturl); // Redirect to the first non empty page.
    }

    // Surveypro has been submitted. Notify people.
    $userformman->notifypeople();

    // If none redirected you, reload THE RIGHT page WITHOUT $paramurl['view'].
    // This is necessary otherwise if the user switches language using the corresponding menu
    // just after a new response is submitted
    // the browser redirects to http://localhost/head_behat/mod/surveypro/view_form.php?s=xxx&view=1&lang=it
    // and not               to http://localhost/head_behat/mod/surveypro/view_submissions.php?s=xxx&lang=it
    // alias it goes to the page to get one more response
    // instead of remaining in the view submissions page.
    $paramurl = array();
    $paramurl['s'] = $surveypro->id;
    $paramurl['responsestatus'] = $userformman->get_responsestatus();
    $paramurl['justsubmitted'] = 1 + $userformman->get_userdeservesthanks();
    $paramurl['formview'] = $userformman->get_view(); // What was I viewing in the form?

    // Redirect to charts tool.
    $paramurl = array();
    $paramurl['id'] = $cm->id;
    if ($showjumper) {
        $paramurl['groupid'] = $groupid;
    }
    $redirecturl = new moodle_url('/lib/charts/script.php'); 
    redirect($redirecturl); // Redirect to the first non empty page.

}
// End of: manage form submission.
// Output starts here.
$paramurl = ['s' => $surveypro->id, 'view' => $view];
if (!empty($submissionid)) {
    $paramurl['submissionid'] = $submissionid;
}
$url = new moodle_url('/calendar/view.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

if (has_capability('moodle/course:viewhiddenactivities', $context)) {
    new tabs($cm, $context, $surveypro, $userformman->get_tabtab(), $userformman->get_tabpage());
}

$userformman->noitem_stopexecution();
$userformman->nomoresubmissions_stopexecution();
$userformman->warning_submission_copy();
$userformman->display_page_x_of_y();

// Begin of: calculate prefill for fields and prepare standard editors and filemanager.
// If sumission already exists.
$prefill = $userformman->get_prefill_data();
$prefill['formpage'] = $userformman->get_formpage();
// End of: calculate prefill for fields and prepare standard editors and filemanager.

$userform->set_data($prefill);
$userform->display();

// If surveypro is multipage and $userformman->tabpage == SURVEYPRO_READONLYRESPONSE.
// I need to add navigation buttons manually
// Because the surveypro is not displayed as a form but as a simple list of graphic user items.
$userformman->add_readonly_browsing_buttons();

// Finish the page.
echo $OUTPUT->footer();
