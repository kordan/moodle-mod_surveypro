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
 * Starting page to apply a mastertemplate.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/form/mtemplates/apply_form.php');

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

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:applymastertemplates', $context);

// Calculations.
$mtemplateman = new mod_surveypro_mastertemplate($cm, $context, $surveypro);

// Begin of: define $applymtemplate return url.
$paramurl = array('id' => $cm->id);
$formurl = new \moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurl);
// End of: define $applymtemplate return url.

// Begin of: prepare params for the form.
$formparams = new \stdClass();
$formparams->cmid = $cm->id;
$formparams->surveypro = $surveypro;
$formparams->mtemplateman = $mtemplateman;
$formparams->subform = false;

$applymtemplate = new mod_surveypro_applymtemplateform($formurl, $formparams);
// End of: prepare params for the form.

// Begin of: manage form submission.
if ($applymtemplate->is_cancelled()) {
    $returnurl = new \moodle_url('/mod/surveypro/utemplates_add.php', $paramurl);
    redirect($returnurl);
}

if ($mtemplateman->formdata = $applymtemplate->get_data()) {
    $mtemplateman->apply_template();
}
// End of: manage form submission.

// Output starts here.
$url = new \moodle_url('/mod/surveypro/mtemplate_apply.php', array('s' => $surveypro->id));
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

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABMTEMPLATES, SURVEYPRO_MTEMPLATES_APPLY);

$mtemplateman->friendly_stop();

$riskyediting = ($surveypro->riskyeditdeadline > time());
$utilitylayoutman = new utility_layout($cm, $surveypro);
$utilitysubmissionman = new utility_submission($cm, $surveypro);
if ($utilitylayoutman->has_submissions() && $riskyediting) {
    $message = $utilitysubmissionman->get_submissions_warning();
    echo $OUTPUT->notification($message, 'notifyproblem');
}

$mtemplateman->welcome_apply_message();

$applymtemplate->display();

// Finish the page.
echo $OUTPUT->footer();
