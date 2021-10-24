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
 * Starting page for item setup.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$id = required_param('id', PARAM_INT); // Course_module id.

if (! $cm = get_coursemodule_from_id('surveypro', $id)) {
    print_error('invalidcoursemodule');
}
$cm = cm_info::create($cm);

if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
    print_error('coursemisconf');
}

require_course_login($course, false, $cm);

if (! $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*')) {
    print_error('invalidcoursemodule');
}

$typeplugin = optional_param('typeplugin', null, PARAM_TEXT);
$type = optional_param('type', null, PARAM_TEXT);
$plugin = optional_param('plugin', null, PARAM_TEXT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$view = optional_param('view', SURVEYPRO_NEWRESPONSE, PARAM_INT);

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:additems', $context);

// Calculations.
$utilitylayoutman = new mod_surveypro_utility_layout($cm, $surveypro);
$hassubmissions = $utilitylayoutman->has_submissions();

$layoutman = new mod_surveypro_layout($cm, $context, $surveypro);
if (!empty($typeplugin)) {
    $layoutman->set_typeplugin($typeplugin);
} else {
    $layoutman->set_type($type);
    $layoutman->set_plugin($plugin);
}
$layoutman->set_itemid($itemid);
$layoutman->set_action($action);
$layoutman->set_view($view);
$layoutman->set_hassubmissions($hassubmissions);
// Property itemtomove is useless (it is set to its default), do not set it.
// $layoutman->set_itemtomove(0);

// Property lastitembefore is useless (it is set to its default), do not set it.
// $layoutman->set_lastitembefore(0);

// Property confirm is useless (it is set to its default), do not set it.
// $layoutman->set_confirm(SURVEYPRO_UNCONFIRMED);

// Property nextindent is useless (it is set to its default), do not set it.
// $layoutman->set_nextindent(0);

// Property parentid is useless (it is set to its default), do not set it.
// $layoutman->set_parentid(0);

// Property itemeditingfeedback is useless (it is set to its default), do not set it.
// $layoutman->set_itemeditingfeedback(SURVEYPRO_NOFEEDBACK);

// Property hassubmissions is useless (it is set to its default), do not set it.
// $layoutman->set_hassubmissions($hassubmissions);

// Property itemcount is useless (it is set to its default), do not set it.
// $layoutman->set_itemcount($itemcount);

$layoutman->prevent_direct_user_input();

require_once($CFG->dirroot.'/mod/surveypro/'.$layoutman->get_type().'/'.$layoutman->get_plugin().'/form/itemsetup_form.php');

// Begin of: get item.
$itemtype = $layoutman->get_type();
$itemplugin = $layoutman->get_plugin();
$item = surveypro_get_item($cm, $surveypro, $itemid, $itemtype, $itemplugin, true);
$item->set_editor();
// End of: get item.

// Begin of: define $itemform return url.
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
// End of: define $itemform return url.

// Begin of: prepare params for the form.
$classname = 'mod_surveypro_'.$itemplugin.'_setupform';
$itemform = new $classname($formurl, array('item' => $item), null, null, array('id' => 'itemsetup'));
// End of: prepare params for the form.

// Begin of: manage form submission.
if ($itemform->is_cancelled()) {
    $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
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

    $paramurl = array('id' => $cm->id, 'iefeedback' => $feedback);
    $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
    redirect($returnurl);
}
// End of: manage form submission.

// Output starts here.
$paramurl = array('id' => $cm->id);
$paramurl['itemid'] = $itemid;
$paramurl['type'] = $layoutman->get_type();
$paramurl['plugin'] = $layoutman->get_plugin();
$paramurl['view'] = $view;
$url = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABLAYOUT, SURVEYPRO_LAYOUT_ITEMSETUP);

$utilitysubmissionman = new mod_surveypro_utility_submission($cm, $surveypro);
if ($hassubmissions) {
    $message = $utilitysubmissionman->get_submissions_warning();
    echo $OUTPUT->notification($message, 'notifyproblem');
}
$layoutman->item_identitycard();

$data = $item->get_itemform_preset();
$itemform->set_data($data);

$itemform->display();

// Finish the page.
echo $OUTPUT->footer();
