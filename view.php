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
 * Starting page to display the surveypro cover page.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_layout;
use mod_surveypro\utility_page;

// Needed only if $section == 'cover'.
use mod_surveypro\cover;

// Needed only if $section == 'submissionslist'.
use mod_surveypro\submissions_list;

// Needed only if $section == 'submissionform'.
use mod_surveypro\utility_mform;
use mod_surveypro\submissions_form;
use mod_surveypro\local\form\userform;

// Needed only if $section == 'searchsubmission'.
use mod_surveypro\submissions_search;
use mod_surveypro\local\form\usersearch;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$id = optional_param('id', 0, PARAM_INT);                      // Course_module id.
$s = optional_param('s', 0, PARAM_INT);                        // Surveypro instance id.
$section = optional_param('section', 'cover', PARAM_ALPHAEXT); // The section of code to execute.

// Verify I used correct names all along the module code.
$validsections = ['cover', 'submissionslist', 'submissionform', 'searchsubmissions'];
if (!in_array($section, $validsections)) {
    $message = 'The section param \''.$section.'\' is invalid.';
    debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
}
// End of: Verify I used correct names all along the module code.

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
require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Utilitypage is going to be used in each section. This is the reason why I load it here.
$utilitypageman = new utility_page($cm, $surveypro);

// MARK cover.
if ($section == 'cover') { // It was view_cover.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);

    // Required capability.
    $canmanageitems = has_capability('mod/surveypro:manageitems', $context);
    $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);

    // Calculations.
    // If you are an admin and no items are in this surveypro, you will be redireted.
    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitylayoutman->noitem_redirect();

    $coverman = new cover($cm, $context, $surveypro);

    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $itemcount = $utilitylayoutman->has_items(0, SURVEYPRO_TYPEFIELD, $canmanageitems, $canaccessreserveditems, true);

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $cm->instance, 'section' => 'cover']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    // $PAGE->navbar->add(get_string('modulename', 'mod_surveypro'), $url); // WHY it is already onboard?
    $PAGE->navbar->add(get_string('surveypro_dashboard', 'mod_surveypro'));
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_view_action_bar();

    if (!$itemcount) { // Admin was redirected. Student gets the alert.
        $message = get_string('noitemsfound', 'mod_surveypro');
        echo $OUTPUT->notification($message, 'notifyproblem');
    } else {
        $coverman->display_cover();
    }
}

// MARK submissionslist.
// This section serves the page to...
// - display the list of all gathered submissions;
// - duplicate a submission;
// - delete a submission;
// - delete all gathered submissions;
// - print to PDF a submission.
if ($section == 'submissionslist') { // It was view_submissions.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);
    $tifirst = optional_param('tifirst', '', PARAM_ALPHA); // First letter of the name.
    $tilast = optional_param('tilast', '', PARAM_ALPHA);   // First letter of the surname.
    // $tsort = optional_param('tsort', '', PARAM_ALPHA);     // Field asked to sort the table for.

    // A response was submitted.
    $justsubmitted = optional_param('justsubmitted', 0, PARAM_INT);
    $formview = optional_param('formview', 0, PARAM_INT);
    $responsestatus = optional_param('responsestatus', 0, PARAM_INT);

    // The list is managed.
    $submissionid = optional_param('submissionid', 0, PARAM_INT);
    $action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
    $mode = optional_param('view', SURVEYPRO_NOMODE, PARAM_INT);
    $confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
    $searchquery = optional_param('searchquery', '', PARAM_RAW);

    if ($action != SURVEYPRO_NOACTION) {
        require_sesskey();
    }

    // Calculations.
    $submissionlistman = new submissions_list($cm, $context, $surveypro);
    $submissionlistman->setup($submissionid, $action, $mode, $confirm, $searchquery);

    if ($action == SURVEYPRO_RESPONSETOPDF) {
        $submissionlistman->submission_to_pdf();
        die();
    }

    // Perform action before PAGE. (The content of the admin block depends on the output of these actions).
    $submissionlistman->actions_execution();

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $cm->instance, 'section' => 'submissionslist']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('modulename', 'mod_surveypro'), $url);
    $PAGE->navbar->add(get_string('surveypro_responses', 'mod_surveypro'));
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_view_action_bar();

    if (!empty($justsubmitted)) {
        $submissionlistman->show_thanks_page($responsestatus, $formview, $justsubmitted);
    } else {
        $submissionlistman->actions_feedback(); // Action feedback after PAGE.

        $submissionlistman->show_action_buttons($tifirst, $tilast);
        $submissionlistman->display_submissions_table();
        $submissionlistman->trigger_event(); // Event: all_submissions_viewed.
    }
}

// MARK submissionform.
// This section serves the page to...
// - add a new submission;
// - edit already submitted submissions.
// - view in readonly mode.
if ($section == 'submissionform') { // It was view_form.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);
    $submissionid = optional_param('submissionid', 0, PARAM_INT);
    $formpage = optional_param('formpage', 1, PARAM_INT); // Form page number.
    $mode = optional_param('mode', SURVEYPRO_NOMODE, PARAM_INT);
    $overflowpage = optional_param('overflowpage', 0, PARAM_INT); // Went the user to a overflow page?
    $begin = optional_param('begin', 0, PARAM_INT);

    // Calculations.
    mod_surveypro\utility_mform::register_form_elements();

    $userformman = new submissions_form($cm, $context, $surveypro);
    $userformman->setup($submissionid, $formpage, $mode);

    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitylayoutman->add_custom_css();

    // Begin of: define $user_form return url.
    $paramurl = ['s' => $cm->instance, 'mode' => $mode, 'section' => 'submissionform'];
    $formurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
    // End of: define $user_form return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->cm = $cm;
    $formparams->surveypro = $surveypro;
    $formparams->submissionid = $submissionid;
    $formparams->mode = $mode;
    $formparams->userformpagecount = $userformman->get_userformpagecount();
    $formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
    $formparams->userfirstpage = $userformman->get_userfirstpage(); // The user first page
    $formparams->userlastpage = $userformman->get_userlastpage(); // The user last page
    $formparams->overflowpage = $overflowpage; // Went the user to a overflow page?
    // End of: prepare params for the form.

    if ($begin == 1) {
        $userformman->next_not_empty_page(true, 0); // True means direction = right.
        $nextpage = $userformman->get_nextpage(); // The page of the form to select subset of fields
        $userformman->set_formpage($nextpage);
    }
    $formparams->formpage = $userformman->get_formpage(); // The page of the form to select subset of fields
    // End of: prepare params for the form.

    $editable = ($mode == SURVEYPRO_READONLYMODE) ? false : true;
    $userform = new userform($formurl, $formparams, 'post', '', ['id' => 'userentry'], $editable);

    // Begin of: manage form submission.
    if ($userform->is_cancelled()) {
        $localparamurl = ['s' => $cm->instance, 'mode' => $mode, 'section' => 'submissionslist'];
        $redirecturl = new \moodle_url('/mod/surveypro/view.php', $localparamurl);
        redirect($redirecturl, get_string('usercanceled', 'mod_surveypro'));
    }

    if ($userformman->formdata = $userform->get_data()) {
        $userformman->save_user_data(); // SAVE SAVE SAVE SAVE.

        // If "pause" button has been pressed, redirect.
        $pausebutton = isset($userformman->formdata->pausebutton);
        if ($pausebutton) {
            $localparamurl = ['s' => $cm->instance, 'mode' => $mode, 'section' => 'submissionslist'];
            $redirecturl = new \moodle_url('/mod/surveypro/view.php', $localparamurl);
            redirect($redirecturl); // Go somewhere.
        }

        $paramurl['submissionid'] = $userformman->get_submissionid();
        $paramurl['section'] = 'submissionform';

        // If "previous" button has been pressed, redirect.
        $prevbutton = isset($userformman->formdata->prevbutton);
        if ($prevbutton) {
            $userformman->next_not_empty_page(false);
            $paramurl['formpage'] = $userformman->get_nextpage();
            $paramurl['overflowpage'] = $userformman->get_overflowpage();
            $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            redirect($redirecturl); // Redirect to the first non empty page.
        }

        // If "next" button has been pressed, redirect.
        $nextbutton = isset($userformman->formdata->nextbutton);
        if ($nextbutton) {
            $userformman->next_not_empty_page(true);
            $paramurl['formpage'] = $userformman->get_nextpage();
            $paramurl['overflowpage'] = $userformman->get_overflowpage();
            $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            redirect($redirecturl); // Redirect to the first non empty page.
        }

        // Surveypro has been submitted. Notify people.
        $userformman->notifypeople();

        // If none redirected you, reload THE RIGHT page WITHOUT $paramurl['mode'].
        // This is necessary otherwise if the user switches language using the corresponding menu
        // just after a new response is submitted
        // the browser redirects to http://localhost/head_behat/mod/surveypro/view.php?s=xxx&view=1&lang=it&section=submissionform
        // and not               to http://localhost/head_behat/mod/surveypro/view.php?s=xxx&lang=it&section=collectedsubmissions
        // alias it goes to the page to get one more response
        // instead of remaining in the view submissions page.
        $paramurl = array();
        $paramurl['s'] = $surveypro->id;
        // $paramurl['responsestatus'] = $userformman->get_responsestatus();
        $paramurl['justsubmitted'] = 1 + $userformman->get_userdeservesthanks();
        $paramurl['formview'] = $userformman->get_mode(); // In which way am I using this form?
        $paramurl['section'] = 'submissionslist';
        $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
        redirect($redirecturl);
    }
    // End of: manage form submission.

    // Output starts here.
    $paramurl = ['s' => $cm->instance, 'mode' => $mode, 'section' => 'submissionform'];
    if (!empty($submissionid)) {
        $paramurl['submissionid'] = $submissionid;
    }
    $url = new \moodle_url('/mod/surveypro/view.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_view_action_bar();

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

    // If surveypro is multipage and $userformman->tabpage == SURVEYPRO_READONLYMODE.
    // I need to add navigation buttons manually
    // Because the surveypro is not displayed as a form but as a simple list of graphic user items.
    $userformman->add_readonly_browsing_buttons();
}

// MARK searchsubmissions.
if ($section == 'searchsubmissions') { // It was view_search.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);
    $formpage = optional_param('formpage', 1, PARAM_INT); // Form page number.

    // Required capability.
    require_capability('mod/surveypro:searchsubmissions', $context);

    // Calculations.
    mod_surveypro\utility_mform::register_form_elements();

    $submissionsearchman = new submissions_search($cm, $context, $surveypro);

    // Begin of: define $searchform return url.
    $paramurl = ['s' => $cm->instance, 'section' => 'searchsubmissions'];
    $formurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
    // End of: define $searchform return url.

    // Begin of: prepare params for the search form.
    $formparams = new \stdClass();
    $formparams->cm = $cm;
    $formparams->surveypro = $surveypro;
    $formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
    $searchform = new usersearch($formurl, $formparams, 'post', '', ['id' => 'usersearch']);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($searchform->is_cancelled()) {
        $paramurl = ['s' => $cm->instance, 'section' => 'submissionslist'];
        $returnurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
        redirect($returnurl);
    }

    if ($submissionsearchman->formdata = $searchform->get_data()) {
        // In this routine I do not execute a real search.
        // I only define the param searchquery for the url.
        $paramurl = ['s' => $cm->instance, 'section' => 'submissionslist'];
        if ($searchquery = $submissionsearchman->get_searchparamurl()) {
            $paramurl['searchquery'] = $searchquery;
        }
        $returnurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
        redirect($returnurl);
    }
    // End of: manage form submission.

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $cm->instance, 'section' => 'searchsubmissions']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_view_action_bar();

    $searchform->display();
}

// Finish the page.
echo $OUTPUT->footer();
