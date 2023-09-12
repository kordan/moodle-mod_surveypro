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
 * Starting page for attachment overview report.
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\tabs;
use surveyproreport_attachments\filterform;
use surveyproreport_attachments\form;

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');

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

$itemid = optional_param('itemid', 0, PARAM_INT);  // Item id.
$container = optional_param('container', 0, PARAM_TEXT);  // Userid only OR userid_submissionid.
$changeuser = optional_param('changeuser', 0, PARAM_TEXT);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
$canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
$canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $context);

if ($changeuser) {
    $returnurl = new \moodle_url('/mod/surveypro/report/attachments/view.php', ['s' => $cm->instance]);
    redirect($returnurl);
}

$parts = explode('_', $container);
$userid = (int)$parts[0];
$submissionid = (int)$parts[1];
if (!$submissionid) {
    $submissionid = $DB->get_field('surveypro_submission', 'MIN(id)', ['userid' => $userid, 'surveyproid' => $surveypro->id]);
}

// Calculations.
$uploadsformman = new form($cm, $context, $surveypro);
$uploadsformman->prevent_direct_user_input();
$uploadsformman->set_userid($userid);
$uploadsformman->set_itemid($itemid);
$uploadsformman->set_submissionid($submissionid);

// Begin of: define $filterform return url.
$formurl = new \moodle_url('/mod/surveypro/report/attachments/uploads.php', ['s' => $cm->instance]);
// End of: define $user_form return url.

// Begin of: prepare params for the form.
$formparams = new \stdClass();
$formparams->surveypro = $surveypro;
$formparams->itemid = $itemid;
$formparams->userid = $userid;
$formparams->submissionid = $submissionid;
$formparams->container = $userid.'_'.$submissionid;
$formparams->canaccessreserveditems = $canaccessreserveditems;
$formparams->canviewhiddenactivities = $canviewhiddenactivities;
// End of: prepare params for the form.

$filterform = new filterform($formurl, $formparams, 'post', '', ['id' => 'userentry']);

// Output starts here.
$paramurl = ['s' => $surveypro->id, 'userid' => $userid, 'submissionid' => $submissionid];
$url = new \moodle_url('/mod/surveypro/report/attachments/uploads.php', $paramurl);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
$PAGE->add_body_class('mediumwidth');

// Make bold the navigation menu/link that refers to me.
$url = new \moodle_url('/mod/surveypro/report/attachments/view.php', ['s' => $surveypro->id]);
navigation_node::override_active_url($url);

echo $OUTPUT->header();

$surveyproreportlist = get_plugin_list('surveyproreport');
$reportkey = array_search('attachments', array_keys($surveyproreportlist));

$actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
echo $actionbar->draw_reports_action_bar();

$filterform->display();

$uploadsformman->display_attachment($submissionid, $itemid);

// Finish the page.
echo $OUTPUT->footer();
