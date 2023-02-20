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
 * Starting page to import a user template.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\tabs;
use mod_surveypro\usertemplate;
use mod_surveypro\local\form\utemplateimportform;

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
$edit = optional_param('edit', -1, PARAM_BOOL);

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Required capability.
require_capability('mod/surveypro:importusertemplates', $context);

// Params never passed but needed by called class.
$action = SURVEYPRO_NOACTION;
$confirm = SURVEYPRO_UNCONFIRMED;

// Calculations.
$utemplateman = new usertemplate($cm, $context, $surveypro);
$utemplateman->setup($utemplateid, $action, $confirm);

// $utemplateman->prevent_direct_user_input();
// is not needed because the check has already been done here with: require_capability('mod/surveypro:importusertemplates', $context);

// Begin of: define $importutemplate return url.
$paramurl = ['id' => $cm->id];
$formurl = new \moodle_url('/mod/surveypro/utemplate_import.php', $paramurl);
// End of: define $importutemplate return url.

// Begin of: prepare params for the form.
$formparams = new \stdClass();
$formparams->utemplateman = $utemplateman;
$formparams->filemanageroptions = $utemplateman->get_filemanager_options();
$importutemplate = new utemplateimportform($formurl, $formparams);
// End of: prepare params for the form.

// Begin of: manage form submission.
if ($utemplateman->formdata = $importutemplate->get_data()) {
    $utemplateman->upload_utemplate();
    $utemplateman->trigger_event('usertemplate_imported');

    $redirecturl = new \moodle_url('/mod/surveypro/utemplate_manage.php', ['s' => $surveypro->id]);
    redirect($redirecturl);
}
// End of: manage form submission.

// Output starts here.
$url = new \moodle_url('/mod/surveypro/utemplate_import.php', ['s' => $surveypro->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title($surveypro->name);
$PAGE->set_heading($course->shortname);

echo $OUTPUT->header();
// echo $OUTPUT->heading(format_string($surveypro->name), 2, null);

new tabs($cm, $context, $surveypro, SURVEYPRO_TABUTEMPLATES, SURVEYPRO_UTEMPLATES_IMPORT);

$utemplateman->welcome_import_message();
$importutemplate->display();

// Finish the page.
echo $OUTPUT->footer();
