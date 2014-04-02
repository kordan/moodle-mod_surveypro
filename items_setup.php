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

/*
 * Prints a particular instance of surveypro
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/itemlist.class.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$s = optional_param('s', 0, PARAM_INT);  // surveypro instance ID

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
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$view = optional_param('view', SURVEYPRO_SUBMITRESPONSE, PARAM_INT);

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}

// params never passed but needed by called class
$itemtomove = 0;
$userfeedback = SURVEYPRO_NOFEEDBACK;
$lastitembefore = 0;
$confirm = SURVEYPRO_UNCONFIRMED;
$nextindent = 0;
$parentid = 0;
$saveasnew = null;

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:additems', $context);

// -----------------------------
// calculations
// -----------------------------
$itemlistman = new mod_surveypro_itemlist($cm, $context, $surveypro, $type, $plugin, $itemid, $action, $view, $itemtomove,
                                           $lastitembefore, $confirm, $nextindent, $parentid, $userfeedback, $saveasnew);
$itemlistman->prevent_direct_user_input();

require_once($CFG->dirroot.'/mod/surveypro/'.$itemlistman->type.'/'.$itemlistman->plugin.'/plugin.class.php');
require_once($CFG->dirroot.'/mod/surveypro/'.$itemlistman->type.'/'.$itemlistman->plugin.'/plugin_form.php');

// -----------------------------
// get item
$itemclass = 'surveypro'.$itemlistman->type.'_'.$itemlistman->plugin;
$item = new $itemclass($itemlistman->itemid, true);

$item->item_set_editor();
// end of: get item
// -----------------------------

// -----------------------------
// define $itemform return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('items_setup.php', $paramurl);
// end of: define $itemform return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->surveypro = $surveypro; // needed to setup date boundaries in date fields
$formparams->item = $item; // needed in many situations
$itemform = new surveypro_pluginform($formurl, $formparams);
// end of: prepare params for the form
// -----------------------------

// -----------------------------
// manage form submission
if ($itemform->is_cancelled()) {
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}

if ($fromform = $itemform->get_data()) {
    // was this item forced to be new?
    if (!empty($fromform->saveasnew)) {
        unset($fromform->itemid);
    }

    $itemid = $item->item_save($fromform);
    $feedback = $item->userfeedback; // copy the returned feedback

    // overwrite item to get new settings in the object
    $item = surveypro_get_item($itemid, $item->type, $item->plugin);
    $item->item_update_childrenparentvalue();

    $paramurl = array('id' => $cm->id, 'ufd' => $feedback);
    $returnurl = new moodle_url('items_manage.php', $paramurl);
    redirect($returnurl);
}
// end of: manage form submission
// -----------------------------

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/surveypro/items_setup.php', array('id' => $cm->id));
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABITEMS; // needed by tabs.php
$modulepage = SURVEYPRO_ITEMS_SETUP; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

if ($itemlistman->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'surveypro'));
}
$itemlistman->item_welcome();

$itemform->set_data($item);
$itemform->display();

// Finish the page
echo $OUTPUT->footer();
