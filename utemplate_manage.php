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
 * Starting page to manage user templates.
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

$utemplateid = optional_param('fid', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:manageusertemplates', $context);

// Calculations.
$utemplateman = new mod_surveypro_usertemplate($cm, $context, $surveypro);
$utemplateman->setup($utemplateid, $action, $confirm);

$utemplateman->prevent_direct_user_input();

if ($action == SURVEYPRO_EXPORTUTEMPLATE) {
    $utemplateman->export_utemplate();
    die();
}

// Output starts here.
$url = new moodle_url('/mod/surveypro/utemplate_manage.php', array('id' => $cm->id));
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

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABUTEMPLATES, SURVEYPRO_UTEMPLATES_MANAGE);

$utemplateman->delete_utemplate();

$utemplateman->display_usertemplates_table();
$utemplateman->trigger_event('all_usertemplates_viewed'); // Event: all_usertemplates_viewed.

// Finish the page.
echo $OUTPUT->footer();
