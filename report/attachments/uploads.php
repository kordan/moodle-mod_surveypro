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

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/report/attachments/classes/uploadsform.class.php');
require_once($CFG->dirroot.'/mod/surveypro/report/attachments/forms/filter_form.php');
require_once($CFG->dirroot.'/mod/surveypro/report/attachments/forms/uploads_form.php');

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

$itemid = optional_param('itemid', 0, PARAM_INT);  // item ID
$container = optional_param('userid', 0, PARAM_TEXT);  // userid only OR userid_submissionid
$parts = explode('_', $container);
if (count($parts) == 2) {
    $userid = (int)$parts[0];
    $submissionid = (int)$parts[1];
} else {
    $userid = (int)$container;
    $submissionid = optional_param('submissionid', 0, PARAM_INT);  // userid only OR userid_submissionid
    if (!$submissionid) {
        $submissionid = $DB->get_field('surveypro_submission', 'MIN(id)', array('userid' => $userid));
    }
}

// -----------------------------
// calculations
// -----------------------------
$context = context_module::instance($cm->id);
$uploadsformman = new mod_surveyproreport_uploadformmanager($cm, $context, $surveypro, $userid, $itemid, $submissionid);
$uploadsformman->prevent_direct_user_input();

// -----------------------------
// define $filterform return url
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/report/attachments/uploads.php', $paramurl);
// end of: define $user_form return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->surveypro = $surveypro;
$formparams->itemid = $itemid;
$formparams->userid = $userid;
$formparams->submissionid = $submissionid;
$formparams->canaccessadvanceditems = $uploadsformman->canaccessadvanceditems; // Help selecting the fields to show
// end of: prepare params for the form
// -----------------------------

$filterform = new mod_surveyproreport_filterform($formurl, $formparams, 'post', '', array('id' => 'userentry'));

// -----------------------------
// define $uploadsform return url
$formurl = new moodle_url('/mod/surveypro/report/attachments/uploads.php');
// end of: define $user_form return url
// -----------------------------

// -----------------------------
// prepare params for the form
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->surveypro = $surveypro;
$formparams->itemid = $itemid;
$formparams->userid = $userid;
$formparams->submissionid = $submissionid;
$formparams->canaccessadvanceditems = $uploadsformman->canaccessadvanceditems; // Help selecting the fields to show
// end of: prepare params for the form
// -----------------------------

$uploadsform = new mod_surveyproreport_uploadsform($formurl, $formparams, 'post', '', array('id' => 'userentry'));

// -----------------------------
// output starts here
// -----------------------------
$url = new moodle_url('/mod/surveypro/report/attachments/view.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

// make bold the navigation menu/link that refers to me
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
$modulepage = SURVEYPRO_SUBMISSION_REPORT; // needed by tabs.php
require_once($CFG->dirroot.'/mod/surveypro/tabs.php');

$filterform->display();

$prefill = $uploadsformman->get_prefill_data();
$uploadsform->set_data($prefill);
$uploadsform->display();

// Finish the page
echo $OUTPUT->footer();
