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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/tabs.class.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/utemplate.class.php');
require_once($CFG->dirroot.'/mod/surveypro/form/utemplates/apply_form.php');

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

require_course_login($course, true, $cm);

$utemplateid = optional_param('fid', 0, PARAM_INT);
$action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
$confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);

$context = context_module::instance($cm->id);
require_capability('mod/surveypro:applyusertemplates', $context);

// Calculations.
$utemplateman = new mod_surveypro_usertemplate($cm, $context, $surveypro);
$utemplateman->setup($utemplateid, $action, $confirm);

$utemplateman->prevent_direct_user_input();

// Begin of: define $applyutemplate return url.
$paramurl = array('id' => $cm->id);
$formurl = new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurl);
// End of: define $applyutemplate return url.

// Begin of: prepare params for the form.
$formparams = new stdClass();
$formparams->cmid = $cm->id;
$formparams->surveypro = $surveypro;
$formparams->utemplateman = $utemplateman;
$applyutemplate = new mod_surveypro_applyutemplateform($formurl, $formparams);
// End of: prepare params for the form.

// Begin of: manage form submission.
$utemplateman->formdata = $applyutemplate->get_data();
if ($utemplateman->formdata) {
    // Here I don't need to execute validate_xml because xml was validated at upload time
    // Here I only need to verfy that plugin versions still match
    // $utemplateman->check_items_versions();
    $utemplateman->apply_template();
}
// End of: manage form submission.

// Output starts here.
$url = new moodle_url('/mod/surveypro/utemplates_apply.php', array('s' => $surveypro->id));
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();

new mod_surveypro_tabs($cm, $context, $surveypro, SURVEYPRO_TABUTEMPLATES, SURVEYPRO_UTEMPLATES_APPLY);

$utemplateman->friendly_stop();

$riskyediting = ($surveypro->riskyeditdeadline > time());
$utilityman = new mod_surveypro_utility($cm, $surveypro);
if ($utilityman->has_submissions() && $riskyediting) {
    $message = $utilityman->has_submissions_warning();
    echo $OUTPUT->notification($message, 'notifyproblem');
}

$applyutemplate->display();

// Finish the page.
echo $OUTPUT->footer();
