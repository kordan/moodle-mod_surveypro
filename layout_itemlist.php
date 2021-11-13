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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\layout_itemsetup;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/selectitem_form.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/bulk_action_form.php');

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
$cm = cm_info::create($cm);

$type = optional_param('type', null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$sortindex = optional_param('sortindex', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);
$itemtomove = optional_param('itm', 0, PARAM_INT);
$lastitembefore = optional_param('lib', 0, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pid', 0, PARAM_INT);
$itemeditingfeedback = optional_param('iefeedback', SURVEYPRO_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);
$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:manageitems', $context);

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}

// Calculations.
$utilitylayoutman = new utility_layout($cm, $surveypro);
$utilitysubmissionman = new utility_submission($cm, $surveypro);
$hassubmissions = $utilitylayoutman->has_submissions();
$itemcount = $utilitylayoutman->layout_has_items(0, SURVEYPRO_TYPEFIELD, true, true, true);

// Define the manager.
$layoutman = new layout_itemsetup($cm, $context, $surveypro);
$layoutman->set_type($type);
$layoutman->set_plugin($plugin);
$layoutman->set_itemid($itemid);
$layoutman->set_sortindex($sortindex);
$layoutman->set_action($action);
$layoutman->set_view($view);
$layoutman->set_itemtomove($itemtomove);
$layoutman->set_lastitembefore($lastitembefore);
$layoutman->set_confirm($confirm);
$layoutman->set_nextindent($nextindent);
$layoutman->set_parentid($parentid);
$layoutman->set_itemeditingfeedback($itemeditingfeedback);
$layoutman->set_hassubmissions($hassubmissions);
$layoutman->actions_execution();

$hassubmissions = $utilitylayoutman->has_submissions();
$layoutman->set_hassubmissions($hassubmissions);

$riskyediting = ($surveypro->riskyeditdeadline > time());

$basecondition = true;
$basecondition = $basecondition && empty($surveypro->template);
$basecondition = $basecondition && (!$hassubmissions || $riskyediting);

// Master template form.
if (!$itemcount) { // The surveypro is empty.
    require_once($CFG->dirroot.'/mod/surveypro/form/mtemplates/apply_form.php');

    $mtemplateman = new mod_surveypro_mastertemplate($cm, $context, $surveypro);

    $paramurl = array('id' => $cm->id);
    $formurl = new \moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurl);

    $formparams = new \stdClass();
    $formparams->mtemplateman = $mtemplateman;
    $formparams->subform = true;

    // Init mtemplateform form.
    $mtemplateform = new mod_surveypro_applymtemplateform($formurl, $formparams);

    // Management is in mtemplate_apply.
}

// New item form.
$newitemcondition = $basecondition && has_capability('mod/surveypro:additems', $context);
if ($newitemcondition) {
    $paramurl = array('id' => $cm->id);
    $formurl = new \moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);

    // Init new item form.
    $newitemform = new mod_surveypro_itemtypeform($formurl);

    // Management is in layout_itemsetup.
}

// Bulk action form.
$bulkactioncondition = $basecondition && ($itemcount);
$bulkactioncondition = $bulkactioncondition && has_capability('mod/surveypro:manageitems', $context);
if ($bulkactioncondition) {
    $paramurl = array('id' => $cm->id);
    $formurl = new \moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);

    // Init bulkaction form.
    $bulkactionform = new mod_surveypro_bulkactionform($formurl);

    // Manage bulkaction form.
    if ($formdata = $bulkactionform->get_data()) {
        $layoutman->set_action($formdata->bulkaction);
    }
}

// Output starts here.
$paramurl = array('s' => $surveypro->id);
if ($itemtomove) {
    $paramurl['itemid'] = $itemid;
    $paramurl['type'] = $type;
    $paramurl['plugin'] = $plugin;
    $paramurl['view'] = $view;
    $paramurl['itm'] = $itemtomove;
}
$url = new \moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// If you are changing the order of items, move them and don't think to edit blocks.
if (!$itemtomove) {
    if (($edit != -1) and $PAGE->user_allowed_editing()) {
        $USER->editing = $edit;
    }
    if ($PAGE->user_allowed_editing()) {
        // Change URL parameter and block display string value depending on whether editing is enabled or not
        if ($PAGE->user_is_editing()) {
            $urlediting = 'off';
            $strediting = get_string('blockseditoff');
        } else {
            $urlediting = 'on';
            $strediting = get_string('blocksediton');
        }
        $url = new \moodle_url($CFG->wwwroot.'/mod/surveypro/layout_itemlist.php', ['id' => $cm->id, 'edit' => $urlediting]);
        $PAGE->set_button($OUTPUT->single_button($url, $strediting));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABLAYOUT, SURVEYPRO_LAYOUT_ITEMS);

if ($hassubmissions) {
    $message = $utilitysubmissionman->get_submissions_warning();
    echo $OUTPUT->notification($message, 'notifyproblem');
}

$layoutman->actions_feedback();
$layoutman->display_item_editing_feedback();

if (!$itemcount) {
    // Display welcome message.
    $message = get_string('welcome_emptysurvey', 'mod_surveypro');
    echo $OUTPUT->notification($message, 'notifymessage');
}

if ($newitemcondition) {
    // Display addnewitem form.
    $newitemform->display();
}

if ($bulkactioncondition) {
    // Display bulkaction form.
    $bulkactionform->display();
}

if (!$itemcount) {
    // Display mtemplate form.
    $mtemplateform->display();
}

$layoutman->display_items_table();

// Finish the page.
echo $OUTPUT->footer();
