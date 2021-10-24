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
 * Starting page to display the surveypro cover page.
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

$edit = optional_param('edit', -1, PARAM_BOOL);

$context = context_module::instance($cm->id);

// Calculations.
$coverman = new mod_surveypro_view_cover($cm, $context, $surveypro);

// Output starts here.
$url = new moodle_url('/mod/surveypro/view_cover.php', array('id' => $cm->id));
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
    $url = new moodle_url($CFG->wwwroot.'/mod/surveypro/view_cover.php', array('id' => $id, 'edit' => $urlediting));
    $PAGE->set_button($OUTPUT->single_button($url, $strediting));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

// Render the activity information.
$completiondetails = \core_completion\cm_completion_details::get_instance($cm, $USER->id);
$activitydates = \core\activity_dates::get_dates_for_module($cm, $USER->id);
echo $OUTPUT->activity_information($cm, $completiondetails, $activitydates);

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_CPANEL);

$coverman->display_cover();

// Finish the page.
echo $OUTPUT->footer();
