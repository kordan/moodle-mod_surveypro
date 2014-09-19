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
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/itemlist.class.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/selectitem_form.php');

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
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);
$itemtomove = optional_param('itm', 0, PARAM_INT);
$lastitembefore = optional_param('lib', 0, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
$nextindent = optional_param('ind', 0, PARAM_INT);
$parentid = optional_param('pid', 0, PARAM_INT);
$userfeedback = optional_param('ufd', SURVEYPRO_NOFEEDBACK, PARAM_INT);
$saveasnew = optional_param('saveasnew', null, PARAM_TEXT);

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}
$context = context_module::instance($cm->id);
require_capability('mod/surveypro:manageitems', $context);

// -----------------------------
// calculations
// -----------------------------

// -----------------------------
// the form showing the drop down menu with the list of master templates
$itemcount = $DB->count_records('surveypro_item', array('surveyproid' => $surveypro->id));
if (!$itemcount) {
    require_once($CFG->dirroot.'/mod/surveypro/classes/mtemplate.class.php');
    require_once($CFG->dirroot.'/mod/surveypro/forms/mtemplates/apply_form.php');

    $mtemplateman = new mod_surveypro_mastertemplate($surveypro, $context);

    // -----------------------------
    // define $applymtemplate return url
    $paramurl = array('id' => $cm->id);
    $formurl = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurl);
    // end of: define $applymtemplate return url
    // -----------------------------

    // -----------------------------
    // prepare params for the form
    $formparams = new stdClass();
    $formparams->cmid = $cm->id;
    $formparams->surveypro = $surveypro;
    $formparams->mtemplateman = $mtemplateman;
    $formparams->inline = true;

    $applymtemplate = new surveypro_applymtemplateform($formurl, $formparams);
    // end of: prepare params for the form
    // -----------------------------

    // -----------------------------
    // manage form submission
    if ($applymtemplate->is_cancelled()) {
        $returnurl = new moodle_url('/mod/surveypro/utemplates_add.php', $paramurl);
        redirect($returnurl);
    }

    if ($mtemplateman->formdata = $applymtemplate->get_data()) {
        $mtemplateman->apply_template();
    }
    // end of: manage form submission
    // -----------------------------
}
// end of: the form showing the drop down menu with the list of master templates
// -----------------------------

// -----------------------------
// the form showing the drop down menu with the list of items
$itemlistman = new mod_surveypro_itemlist($cm, $context, $surveypro, $type, $plugin);
$itemlistman->set_itemid($itemid);
$itemlistman->set_action($action);
$itemlistman->set_view($view);
$itemlistman->set_itemtomove($itemtomove);
$itemlistman->set_lastitembefore($lastitembefore);
$itemlistman->set_confirm($confirm);
$itemlistman->set_nextindent($nextindent);
$itemlistman->set_parentid($parentid);
$itemlistman->set_userfeedback($userfeedback);
$itemlistman->set_saveasnew($saveasnew);

// I need to execute this method before the page load because it modifies TAB elements
$itemlistman->drop_multilang();

// -----------------------------
// output starts here
// -----------------------------
$url = new moodle_url('/mod/surveypro/items_manage.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABITEMS; // needed by tabs.php
$modulepage = SURVEYPRO_ITEMS_MANAGE; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$itemlistman->manage_actions();

$itemlistman->display_user_feedback();

if ($itemlistman->hassubmissions) {
    echo $OUTPUT->notification(get_string('hassubmissions_alert', 'surveypro'), 'notifymessage');
}

// add master templates selection form
if (!$itemcount) {
    $message = get_string('beginfromscratch', 'surveypro');
    echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');

    $applymtemplate->display();
}

// add item form
if (!$itemlistman->surveypro->template) {
    $riskyediting = ($surveypro->riskyeditdeadline > time());

    if (!$itemlistman->hassubmissions || $riskyediting) {
        if (has_capability('mod/surveypro:additems', $context)) {
            $paramurl = array('id' => $cm->id);
            $formurl = new moodle_url('/mod/surveypro/items_setup.php', $paramurl);

            $itemtype = new surveypro_itemtypeform($formurl);
            $itemtype->display();
        }
    }
}

$itemlistman->manage_items();
$itemlistman->trigger_event($itemcount); // event: all_items_viewed

// Finish the page
echo $OUTPUT->footer();
