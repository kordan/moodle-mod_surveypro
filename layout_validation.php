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
 * Starting page for layout validation.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\layout_itemsetup;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
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

$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:additems', $context);

// Calculations.

$layoutman = new layout_itemsetup($cm, $context, $surveypro);

// Property type is useless, do not set it.
// $layoutman->set_type('');

// Property plugin is useless, do not set it
// $layoutman->set_plugin('');

// Property itemid is useless (it is set to its default), do not set it
// $layoutman->set_itemid(0);

// Property action is useless (it is set to its default), do not set it
// $layoutman->set_action(SURVEYPRO_NOACTION);

// Property view is useless (it is set to its default), do not set it
// $layoutman->set_view(SURVEYPRO_NEWRESPONSE);

// Property itemtomove is useless (it is set to its default), do not set it
// $layoutman->set_itemtomove(0);

// Property lastitembefore is useless (it is set to its default), do not set it
// $layoutman->set_lastitembefore(0);

// Property confirm is useless (it is set to its default), do not set it
// $layoutman->set_confirm(SURVEYPRO_UNCONFIRMED);

// Property nextindent is useless (it is set to its default), do not set it
// $layoutman->set_nextindent(0);

// Property parentid is useless (it is set to its default), do not set it
// $layoutman->set_parentid(0);

// Property itemeditingfeedback is useless (it is set to its default), do not set it
// $layoutman->set_itemeditingfeedback(SURVEYPRO_NOFEEDBACK);

// Property hassubmissions is useless (it is set to its default), do not set it.
// $layoutman->set_hassubmissions($hassubmissions);

// Property itemcount is useless (it is set to its default), do not set it.
// $layoutman->set_itemcount($itemcount);

// Output starts here.
$url = new \moodle_url('/mod/surveypro/layout_validation.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);
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
    $url = new moodle_url($CFG->wwwroot.'/mod/surveypro/layout_validation.php', ['id' => $cm->id, 'edit' => $urlediting]);
    $PAGE->set_button($OUTPUT->single_button($url, $strediting));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABLAYOUT, SURVEYPRO_LAYOUT_VALIDATE);

$layoutman->display_relations_table();

// Finish the page.
echo $OUTPUT->footer();
