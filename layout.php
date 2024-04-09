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
 * Starting page for item management.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;
use mod_surveypro\utility_mform;

use mod_surveypro\layout_itemsetup;
use mod_surveypro\layout_itemlist;
use mod_surveypro\layout_preview;

use mod_surveypro\utemplate_apply;
use mod_surveypro\mtemplate_apply;

use mod_surveypro\mastertemplate;

use mod_surveypro\local\form\item_chooser;
use mod_surveypro\local\form\utemplate_applyform;
use mod_surveypro\local\form\mtemplate_applyform;
use mod_surveypro\local\form\item_bulkactionform;
use mod_surveypro\local\form\userform;

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$defaultsection = surveypro_get_defaults_section_per_area('layout');

$id = optional_param('id', 0, PARAM_INT);                              // Course_module id.
$s = optional_param('s', 0, PARAM_INT);                                // Surveypro instance id.
$section = optional_param('section', $defaultsection, PARAM_ALPHAEXT); // The section of code to execute.
$edit = optional_param('edit', -1, PARAM_BOOL);

// Verify I used correct names all along the module code.
$validsections = ['preview', 'itemslist', 'itemsetup', 'branchingvalidation'];
if (!in_array($section, $validsections)) {
    $message = 'The section param \''.$section.'\' is invalid.';
    debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
}
// End of: Verify I used correct names all along the module code.

if (!empty($id)) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'surveypro');
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Utilitypage is going to be used in each section. This is the reason why I load it here.
$utilitypageman = new utility_page($cm, $surveypro);

// MARK preview.
if ($section == 'preview') {
    // Get additional specific params.
    $submissionid = optional_param('submissionid', 0, PARAM_INT);
    $formpage = optional_param('formpage', 1, PARAM_INT); // Form page number.
    $overflowpage = optional_param('overflowpage', 0, PARAM_INT); // Went the user to a overflow page?

    // Calculations.
    mod_surveypro\utility_mform::register_form_elements();

    $previewman = new layout_preview($cm, $context, $surveypro);
    $previewman->setup($submissionid, $formpage);

    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitylayoutman->add_custom_css();

    // Begin of: define $user_form return url.
    $paramurl = ['s' => $cm->instance, 'section' => 'preview'];
    $formurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    // End of: define $user_form return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->cm = $cm;
    $formparams->surveypro = $surveypro;
    $formparams->submissionid = $submissionid;
    $formparams->mode = SURVEYPRO_PREVIEWMODE;
    $formparams->userformpagecount = $previewman->get_userformpagecount();
    $formparams->canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
    $formparams->formpage = $formpage; // The page of the form to select subset of fields.
    $formparams->userfirstpage = $previewman->get_userfirstpage(); // The user first page.
    $formparams->userlastpage = $previewman->get_userlastpage(); // The user last page.
    $formparams->overflowpage = $overflowpage; // Went the user to a overflow page?
    // End of: prepare params for the form.

    $userform = new userform($formurl, $formparams, 'post', '', ['id' => 'userentry']);

    // Begin of: manage form submission.
    if ($data = $userform->get_data()) {
        $paramurl['submissionid'] = $submissionid;

        // We are in preview. I always have to see page by page with no care to relations.
        // If "previous" button has been pressed, redirect.
        $prevbutton = isset($data->prevbutton);
        if ($prevbutton) {
            $formpage = max(1, $formpage - 1);
            $paramurl['formpage'] = $formpage;
            $paramurl['overflowpage'] = $previewman->get_overflowpage();
            $paramurl['section'] = 'preview';
            $redirecturl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($redirecturl); // Go to the previous page of the form.
        }

        // If "next" button has been pressed, redirect.
        $nextbutton = isset($data->nextbutton);
        if ($nextbutton) {
            $userformpagecount = $previewman->get_userformpagecount();
            $formpage = min($userformpagecount, $formpage + 1);
            $paramurl['formpage'] = $formpage;
            $paramurl['overflowpage'] = $previewman->get_overflowpage();
            $paramurl['section'] = 'preview';
            $redirecturl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($redirecturl); // Go to the next page of the form.
        }
    }
    // End of: manage form submission.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'layout', 'section' => 'preview'];
    if (!empty($submissionid)) {
        $paramurl['submissionid'] = $submissionid;
    }
    $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('layout_preview', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_layout_action_bar();

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
}

// MARK itemslist.
if ($section == 'itemslist') {
    // Get additional specific params.
    $type = optional_param('type', null, PARAM_TEXT);
    $plugin = optional_param('plugin', null, PARAM_TEXT);
    $itemid = optional_param('itemid', 0, PARAM_INT);
    $sortindex = optional_param('sortindex', 0, PARAM_INT);
    $action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
    $mode = optional_param('mode', SURVEYPRO_NOMODE, PARAM_INT);
    $itemtomove = optional_param('itm', 0, PARAM_INT);
    $lastitembefore = optional_param('lib', 0, PARAM_INT);
    $confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
    $nextindent = optional_param('ind', 0, PARAM_INT);
    $parentid = optional_param('pid', 0, PARAM_INT);
    $itemeditingfeedback = optional_param('iefeedback', SURVEYPRO_NOFEEDBACK, PARAM_INT);
    $saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

    // Required capability.
    require_capability('mod/surveypro:manageitems', $context);

    if ($action != SURVEYPRO_NOACTION) {
        require_sesskey();
    }

    // Calculations.
    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitysubmissionman = new utility_submission($cm, $surveypro);
    $hassubmissions = $utilitylayoutman->has_submissions();

    // Define the manager.
    $itemlistman = new layout_itemlist($cm, $context, $surveypro);
    $itemlistman->setup();
    $itemlistman->set_type($type);
    $itemlistman->set_plugin($plugin);
    $itemlistman->set_itemid($itemid);
    $itemlistman->set_sortindex($sortindex);
    $itemlistman->set_action($action);
    $itemlistman->set_mode($mode);
    $itemlistman->set_itemtomove($itemtomove);
    $itemlistman->set_lastitembefore($lastitembefore);
    $itemlistman->set_confirm($confirm);
    $itemlistman->set_nextindent($nextindent);
    $itemlistman->set_parentid($parentid);
    $itemlistman->set_itemeditingfeedback($itemeditingfeedback);
    $itemlistman->set_hassubmissions($hassubmissions);
    $itemlistman->actions_execution();

    // You must count items AFTER actions_execution() otherwise the count may be wrong (when $action == SURVEYPRO_DELETEITEM).
    $itemcount = $utilitylayoutman->has_items(0, 'field', true, true, true);

    $riskyediting = ($surveypro->riskyeditdeadline > time());

    $basecondition = true;
    $basecondition = $basecondition && empty($surveypro->template);
    $basecondition = $basecondition && (!$hassubmissions || $riskyediting);

    // Begin of: New item form.
    $newitemcondition = $basecondition && has_capability('mod/surveypro:additems', $context);
    if ($newitemcondition) {
        $paramurl = ['s' => $cm->instance, 'section' => 'itemsetup', 'mode' => SURVEYPRO_NEWITEM];
        $formurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);

        // Init new item form.
        $newitemform = new item_chooser($formurl);
    }
    // End of: New item form.

    // Templates.
    $templatecondition = $basecondition && (!$itemcount);
    $templatecondition = $templatecondition && has_capability('mod/surveypro:manageitems', $context);
    if ($templatecondition) {
        // Begin of: User templates form.
        $applyman = new utemplate_apply($cm, $context, $surveypro);
        $utemplates = $applyman->get_utemplates_items();
        if (count($utemplates)) {
            $formurl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $cm->instance, 'section' => 'apply']);

            $formparams = new \stdClass();
            $formparams->utemplates = $utemplates;
            $formparams->inlineform = true;
            $utemplateform = new utemplate_applyform($formurl, $formparams);
        }
        // End of: User templates form.

        // Begin of: Master templates form.
        $applyman = new mtemplate_apply($cm, $context, $surveypro);
        $mtemplates = $applyman->get_mtemplates();
        if (count($mtemplates)) {
            $formurl = new \moodle_url('/mod/surveypro/mtemplates.php', ['s' => $cm->instance, 'section' => 'apply']);

            $formparams = new \stdClass();
            $formparams->applyman = $applyman;
            $formparams->inlineform = true;
            $mtemplateform = new mtemplate_applyform($formurl, $formparams);
        }
        // End of: Master templates form.
    }
    // End of: Templates.

    // Begin of: Bulk action form.
    $bulkactioncondition = $basecondition && $itemcount;
    $bulkactioncondition = $bulkactioncondition && has_capability('mod/surveypro:manageitems', $context);
    if ($bulkactioncondition) {
        $paramurl = ['s' => $cm->instance, 'section' => 'itemslist'];
        $formurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);

        // Init bulkaction form.
        $bulkactionform = new item_bulkactionform($formurl, null, 'get');

        // Manage bulkaction form.
        if ($formdata = $bulkactionform->get_data()) {
            $itemlistman->set_action($formdata->bulkaction);
        }
    }
    // End of: Bulk action form.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'layout', 'section' => 'itemslist'];
    $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('layout_items', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    // If you are changing the order of items, move them and don't think to edit blocks.
    if (!$itemtomove) {
        if (($edit != -1) && $PAGE->user_allowed_editing()) {
            $USER->editing = $edit;
        }
    }

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_layout_action_bar();

    if ($hassubmissions) {
        $message = $utilitysubmissionman->get_submissions_warning();
        echo $OUTPUT->notification($message, 'notifyproblem');
    }

    $itemlistman->actions_feedback();
    $itemlistman->display_item_editing_feedback();

    // Display welcome message.
    if (!$itemcount) {
        $message = get_string('welcome_emptysurvey', 'mod_surveypro');
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    // Display addnewitem form.
    if ($newitemcondition) {
        $newitemform->display();
    }

    if ($templatecondition) {
        // Display utemplate form.
        if (count($utemplates)) {
            $utemplateform->display();
        }
        // Display mtemplate form.
        if (count($mtemplates)) {
            $mtemplateform->display();
        }
    }

    // Display bulkaction form.
    if ($bulkactioncondition) {
        $bulkactionform->display();
    }

    $itemlistman->display_items_table();
}

// MARK itemsetup.
if ($section == 'itemsetup') {
    // Get additional specific params.
    $typeplugin = optional_param('typeplugin', null, PARAM_TEXT);
    $type = optional_param('type', null, PARAM_TEXT);
    $plugin = optional_param('plugin', null, PARAM_TEXT);
    $itemid = optional_param('itemid', 0, PARAM_INT);
    $action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
    $mode = optional_param('mode', SURVEYPRO_NOMODE, PARAM_INT); // Ho sostituito SURVEYPRO_NEWRESPONSEMODE con SURVEYPRO_NOMODE?

    // Required capability.
    require_capability('mod/surveypro:additems', $context);

    if ($action != SURVEYPRO_NOACTION) {
        require_sesskey();
    }

    // Calculations.
    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $hassubmissions = $utilitylayoutman->has_submissions();

    $itemsetupman = new layout_itemsetup($cm, $context, $surveypro);
    $itemsetupman->setup();
    if (!empty($typeplugin)) {
        $itemsetupman->set_typeplugin($typeplugin);
    } else {
        $itemsetupman->set_type($type);
        $itemsetupman->set_plugin($plugin);
    }
    $itemsetupman->set_itemid($itemid);
    $itemsetupman->set_action($action);
    $itemsetupman->set_mode($mode);
    $itemsetupman->set_hassubmissions($hassubmissions);

    $itemsetupman->prevent_direct_user_input();

    require_once($CFG->dirroot.'/mod/surveypro/'.$itemsetupman->get_type().'/'.$itemsetupman->get_plugin().'/classes/itemsetupform.php');

    // Begin of: get item.
    $itemtype = $itemsetupman->get_type();
    $itemplugin = $itemsetupman->get_plugin();
    $item = surveypro_get_item($cm, $surveypro, $itemid, $itemtype, $itemplugin, true);
    $item->set_editor();
    // End of: get item.

    // Set $PAGE params.
    $paramurl = [];
    $paramurl['s'] = $surveypro->id;
    $paramurl['area'] = 'layout';
    $paramurl['section'] = 'itemsetup';
    $paramurl['itemid'] = $itemid;
    $paramurl['type'] = $itemsetupman->get_type();
    $paramurl['plugin'] = $itemsetupman->get_plugin();
    $paramurl['mode'] = $mode;

    $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('layout_itemsetup', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Begin of: define $itemform return url.
    $paramurl = ['s' => $cm->instance, 'section' => 'itemsetup'];
    $formurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    // End of: define $itemform return url.

    // Begin of: prepare params for the form.
    $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemplugin.'\itemsetupform';
    $itemform = new $classname($formurl, ['item' => $item], null, null, ['id' => 'itemsetup']);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($itemform->is_cancelled()) {
        $returnurl = new \moodle_url('/mod/surveypro/layout.php', ['s' => $cm->instance, 'section' => 'itemslist']);
        redirect($returnurl);
    }

    if ($fromform = $itemform->get_data()) {
        // Was this item forced to be new?
        if (!empty($fromform->saveasnew)) {
            unset($fromform->itemid);
        }

        $itemid = $item->item_save($fromform);
        $feedback = $item->get_itemeditingfeedback(); // Copy the returned feedback.

        // Overwrite item to get new settings in the object.
        $item = surveypro_get_item($cm, $surveypro, $itemid, $item->get_type(), $item->get_plugin());
        $item->item_update_childrenparentvalue();

        $paramurl = ['s' => $cm->instance, 'section' => 'itemslist', 'iefeedback' => $feedback];
        $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        redirect($returnurl);
    }
    // End of: manage form submission.

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_layout_action_bar();

    $utilitysubmissionman = new utility_submission($cm, $surveypro);
    if ($hassubmissions) {
        $message = $utilitysubmissionman->get_submissions_warning();
        echo $OUTPUT->notification($message, 'notifyproblem');
    }
    $itemsetupman->item_identitycard();

    $data = $item->get_itemform_preset();
    $itemform->set_data($data);

    $itemform->display();
}

// MARK branchingvalidation.
if ($section == 'branchingvalidation') {
    // Get additional specific params.

    // Required capability.
    require_capability('mod/surveypro:additems', $context);

    // Calculations.
    $branchingvalidationman = new layout_branchingvalidation($cm, $context, $surveypro);

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/layout.php', ['s' => $surveypro->id, 'section' => 'branchingvalidation']);

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'layout', 'section' => 'branchingvalidation'];
    $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('layout_branchingvalidation', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_layout_action_bar();

    $branchingvalidationman->display_relations_table();
}

// Finish the page.
echo $OUTPUT->footer();
