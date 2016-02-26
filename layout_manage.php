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
require_once($CFG->dirroot.'/mod/surveypro/classes/itemlist.class.php');
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

require_course_login($course, true, $cm);

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
$newitemfeedbackmask = optional_param('nifeedback', SURVEYPRO_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}
$context = context_module::instance($cm->id);
require_capability('mod/surveypro:manageitems', $context);

// Calculations.
$utilityman = new mod_surveypro_utility($cm, $surveypro);
$hassubmissions = $utilityman->has_submissions();
$itemcount = $utilityman->has_input_items(0, true, true, true);

// Define the manager.
$itemlistman = new mod_surveypro_itemlist($cm, $context, $surveypro);
$itemlistman->set_type($type);
$itemlistman->set_plugin($plugin);
$itemlistman->set_itemid($itemid);
$itemlistman->set_sortindex($sortindex);
$itemlistman->set_action($action);
$itemlistman->set_view($view);
$itemlistman->set_itemtomove($itemtomove);
$itemlistman->set_lastitembefore($lastitembefore);
$itemlistman->set_confirm($confirm);
$itemlistman->set_nextindent($nextindent);
$itemlistman->set_parentid($parentid);
$itemlistman->set_newitemfeedbackmask($newitemfeedbackmask);
$itemlistman->set_saveasnew($saveasnew);
$itemlistman->set_hassubmissions($hassubmissions);
$itemlistman->set_itemcount($itemcount);

$itemlistman->actions_execution();
$hassubmissions = $utilityman->has_submissions();
$itemlistman->set_hassubmissions($hassubmissions);

$riskyediting = ($surveypro->riskyeditdeadline > time());

$basecondition = true;
$basecondition = $basecondition && empty($surveypro->template);
$basecondition = $basecondition && (!$hassubmissions || $riskyediting);

// master template form
$mtemplatecondition =  true;
$mtemplatecondition =  $mtemplatecondition && (!$itemcount);
if ($mtemplatecondition) {
    require_once($CFG->dirroot.'/mod/surveypro/classes/mtemplate.class.php');
    require_once($CFG->dirroot.'/mod/surveypro/form/mtemplates/apply_form.php');

    $mtemplateman = new mod_surveypro_mastertemplate($cm, $context, $surveypro);

    $paramurl = array('id' => $cm->id);
    $formurl = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurl);

    $formparams = new stdClass();
    $formparams->mtemplateman = $mtemplateman;
    $formparams->inline = true;

    // Init mtemplateform form.
    $mtemplateform = new mod_surveypro_applymtemplateform($formurl, $formparams);

    // Management is in mtemplates_apply.
}

// new item form
$newitemcondition = $basecondition && has_capability('mod/surveypro:additems', $context);
if ($newitemcondition) {
    $paramurl = array('id' => $cm->id);
    $formurl = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);

    // Init new item form.
    $newitemform = new mod_surveypro_itemtypeform($formurl);

    // Management is in layout_itemsetup.
}

// bulk action form
$bulkactioncondition = $basecondition && ($itemcount);
$bulkactioncondition = $bulkactioncondition && has_capability('mod/surveypro:manageitems', $context);
if ($bulkactioncondition) {
    $paramurl = array('id' => $cm->id);
    $formurl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);

    // Init bulkaction form.
    $bulkactionform = new mod_surveypro_bulkactionform($formurl);

    // Manage bulkaction form.
    if ($formdata = $bulkactionform->get_data()) {
        $itemlistman->set_action($formdata->bulkaction);
    }
}

// Output starts here.
$url = new moodle_url('/mod/surveypro/layout_manage.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// Make bold the navigation menu/link that refers to me.
navigation_node::override_active_url($url);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABITEMS, SURVEYPRO_ITEMS_MANAGE);

if ($hassubmissions) {
    $message = $utilityman->warning_message();
    echo $OUTPUT->notification($message, 'notifyproblem');
}

$itemlistman->actions_feedback();
$itemlistman->item_editing_feedback();

if ($mtemplatecondition) {
    // display mtemplate form
    $message = get_string('beginfromscratch', 'mod_surveypro');
    echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

    // Display addnewitem form.
    $mtemplateform->display();
}

if ($newitemcondition) {
    // Display addnewitem form.
    $newitemform->display();
}

if ($bulkactioncondition) {
    // Display bulkaction form.
    $bulkactionform->display();
}

$itemlistman->display_items_table();

// Finish the page.
echo $OUTPUT->footer();
