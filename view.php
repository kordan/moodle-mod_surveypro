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
require_once($CFG->dirroot.'/mod/surveypro/classes/view_manage.class.php');

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

$submissionid = optional_param('submissionid', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$view = optional_param('view', SURVEYPRO_NOVIEW, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
$searchquery = optional_param('searchquery', '', PARAM_RAW);
$cover = optional_param('cover', null, PARAM_INT);

if ($cover == 1) {
    $paramurl = array('s' => $this->surveypro->id);
    $redirecturl = new moodle_url('/mod/surveypro/view_cover.php', $paramurl);
    redirect($redirecturl);
}

if ($action != SURVEYPRO_NOACTION) {
    require_sesskey();
}

// -----------------------------
// calculations
// -----------------------------
$submissionman = new mod_surveypro_submissionmanager($cm, $surveypro, $submissionid, $action, $view, $confirm, $searchquery);
if ($cover === null) {
    if ($submissionman->canmanageitems) {
        if (!$submissionman->itemsfound) {
            $paramurl = array('s' => $surveypro->id);
            $redirecturl = new moodle_url('/mod/surveypro/items_manage.php', $paramurl);
            redirect($redirecturl);
        } // else: carry on
    } else {
        if ($submissionman->itemsfound) {
            $paramurl = array('s' => $surveypro->id);
            $redirecturl = new moodle_url('/mod/surveypro/view_cover.php', $paramurl);
            redirect($redirecturl);
            // } else {
            // if (!$submissionman->itemsfound) { just below will stop execution
        }
    }
}
$submissionman->prevent_direct_user_input($confirm);
$submissionman->submission_to_pdf();

// -----------------------------
// output starts here
// -----------------------------
$PAGE->set_url('/mod/surveypro/view.php', array('s' => $surveypro->id, 'cover' => 0));
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

if (!$submissionman->itemsfound) {
    $submissionman->noitem_stopexecution();
}
$submissionman->manage_actions(); // action feedback before tabs

$moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
$modulepage = SURVEYPRO_SUBMISSION_MANAGE; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$submissionman->show_action_buttons();
$submissionman->manage_submissions();
$submissionman->trigger_event(); // event: all_submissions_viewed

// Finish the page
echo $OUTPUT->footer();
