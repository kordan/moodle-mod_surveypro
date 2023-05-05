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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_layout;
use mod_surveypro\tabs;
use mod_surveypro\view_cover;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

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

$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);

$context = \context_module::instance($cm->id);

// Calculations.
$utilitylayoutman = new utility_layout($cm, $surveypro);
$utilitylayoutman->noitem_redirect();

$coverman = new view_cover($cm, $context, $surveypro);

// Output starts here.
$url = new \moodle_url('/mod/surveypro/view.php', ['s' => $surveypro->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

new tabs($cm, $context, $surveypro, SURVEYPRO_TABSUBMISSIONS, SURVEYPRO_SUBMISSION_CPANEL);

// $url2 = new \moodle_url('/mod/surveypro/view_form.php?id='.$USER->id.'&view=1&begin=1');
// redirect($url2);
// Get the first page of the survey
// Get the URL of the survey form
// Check if the user has the capability to view the survey


if (!has_capability('mod/surveypro:view', $context)) {
    throw new moodle_exception('accessdenied', 'error', '', '', get_string('accessdenied', 'surveypro'));
}

$params = array();
$params['id'] = $cm->id;

if (has_capability('moodle/course:viewhiddenactivities', $context)) {
    $suburl = new \moodle_url('/mod/surveypro/view_submissions.php', $params);
    redirect($suburl);
} else {
    $params['view'] = 1;
    $params['begin'] = 1;
    $formurl = new \moodle_url('/mod/surveypro/view_form.php', $params);
    redirect($formurl);
}


echo $OUTPUT->footer();
