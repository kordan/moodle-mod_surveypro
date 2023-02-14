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

use mod_surveypro\tabs;
use mod_surveypro\usertemplate;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

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

$utemplateid = optional_param('fid', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:manageusertemplates', $context);

// Calculations.
$utemplateman = new usertemplate($cm, $context, $surveypro);
$utemplateman->setup($utemplateid, $action, $confirm);

$utemplateman->prevent_direct_user_input();

if ($action == SURVEYPRO_EXPORTUTEMPLATE) {
    $utemplateman->export_utemplate();
    die();
}

// Output starts here.
$url = new \moodle_url('/mod/surveypro/utemplate_manage.php', ['s' => $surveypro->id]);
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
    $url = new \moodle_url($CFG->wwwroot.'/mod/surveypro/utemplate_manage.php', ['id' => $cm->id, 'edit' => $urlediting]);
    $PAGE->set_button($OUTPUT->single_button($url, $strediting));
}

echo $OUTPUT->header();

new tabs($cm, $context, $surveypro, SURVEYPRO_TABUTEMPLATES, SURVEYPRO_UTEMPLATES_MANAGE);

$utemplateman->delete_utemplate();

$utemplateman->display_usertemplates_table();
$utemplateman->trigger_event('all_usertemplates_viewed'); // Event: all_usertemplates_viewed.

// Finish the page.
echo $OUTPUT->footer();
