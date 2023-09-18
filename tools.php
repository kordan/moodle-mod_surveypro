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
 * Starting page to export data gathered.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;

// Needed only if $section == 'export'.
use mod_surveypro\tools_export;
use mod_surveypro\local\form\submissions_exportform;

// Needed only if $section == 'import'.
use mod_surveypro\tools_import;
use mod_surveypro\local\form\submissions_importform;

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$id = optional_param('id', 0, PARAM_INT);                       // Course_module id.
$s = optional_param('s', 0, PARAM_INT);                         // Surveypro instance id.
$section = optional_param('section', 'export', PARAM_ALPHAEXT); // The section of code to execute.

// Verify I used correct names all along the module code.
$validsections = ['export', 'import'];
if (!in_array($section, $validsections)) {
    $message = 'The section param \''.$section.'\' is invalid.';
    debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
}
// End of: Verify I used correct names all along the module code.

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
// require_course_login($course, false, $cm);
require_login($course);
$context = \context_module::instance($cm->id);

// Utilitypage is going to be used in each section. This is the reason why I load it here.
$utilitypageman = new utility_page($cm, $surveypro);

// MARK export.
if ($section == 'export') { // It was tools_export.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);

    // Required capability.
    require_capability('mod/surveypro:exportresponses', $context);

    // Calculations.
    $exportman = new tools_export($cm, $context, $surveypro);

    // Begin of: define exportform return url.
    $formurl = new \moodle_url('/mod/surveypro/tools.php', ['s' => $cm->instance, 'section' => 'export']);
    // End of: define $mform return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->surveypro = $surveypro;
    $formparams->activityisgrouped = groups_get_activity_groupmode($cm, $course);
    $formparams->context = $context;
    $formparams->attachmentshere = $exportman->are_attachments_onboard();
    $exportform = new submissions_exportform($formurl, $formparams, 'POST', '', ['data-double-submit-protection' => 'off']);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($exportman->formdata = $exportform->get_data()) {
        if (!$exporterror = $exportman->submissions_export()) {
            // All is fine!
            $exportman->trigger_event(); // Event: all_submissions_exported.

            die();
        }
    } else {
        $exporterror = null;
    }
    // End of: manage form submission.

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/tools.php', ['s' => $surveypro->id, 'section' => 'export']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    // $PAGE->navbar->add(get_string('tools', 'mod_surveypro'), $url); // WHY it is already onboard?
    $PAGE->navbar->add(get_string('tools_export', 'mod_surveypro'));
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_tools_action_bar();

    if ($exporterror == SURVEYPRO_NOFIELDSSELECTED) {
        echo $OUTPUT->notification(get_string('nothingtodownload', 'mod_surveypro'), 'notifyproblem');
    }

    if ($exporterror == SURVEYPRO_NORECORDSFOUND) {
        echo $OUTPUT->notification(get_string('emptydownload', 'mod_surveypro'), 'notifyproblem');
    }

    if ($exporterror == SURVEYPRO_NOATTACHMENTFOUND) {
        echo $OUTPUT->notification(get_string('noattachmentfound', 'mod_surveypro'), 'notifyproblem');
    }

    $exportman->welcome_message();
    $exportform->display();
}

// MARK import.
if ($section == 'import') { // It was tools_import.php
    // Get additional specific params.
    $edit = optional_param('edit', -1, PARAM_BOOL);

    // Required capability.
    require_capability('mod/surveypro:importresponses', $context);

    // Calculations.
    $importman = new tools_import($cm, $context, $surveypro);

    // Begin of: define $mform return url.
    $formurl = new \moodle_url('/mod/surveypro/tools.php', ['s' => $cm->instance, 'section' => 'import']);
    // End of: define $mform return url.

    // Begin of: prepare params for the form.
    $importform = new submissions_importform($formurl);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($importman->formdata = $importform->get_data()) {
        $err = $importman->validate_csvcontent();
        if (empty($err)) {
            $importman->import_csv();
            $redirecturl = new \moodle_url('/mod/surveypro/view.php', ['s' => $cm->instance, 'section' => 'submissionslist']);
            redirect($redirecturl);
        }
    }
    // End of: manage form submission.

    // Output starts here.
    $url = new \moodle_url('/mod/surveypro/tools.php', ['s' => $cm->instance, 'section' => 'import']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('tools', 'mod_surveypro'), $url);
    $PAGE->navbar->add(get_string('tools_import', 'mod_surveypro'));
    $PAGE->add_body_class('mediumwidth');
    $utilitypageman->manage_editbutton($edit);

    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_tools_action_bar();

    if (!empty($err)) {
        if (isset($err->a)) {
            $message = get_string($err->key, 'mod_surveypro', $err->a);
        } else {
            $message = get_string($err->key, 'mod_surveypro');
        }
        echo $OUTPUT->notification($message, 'notifyproblem');

        $returnurl = new \moodle_url('/mod/surveypro/tools.php', ['s' => $surveypro->id, 'section' => 'import']);
        echo $OUTPUT->continue_button($returnurl);
    } else {
        $importman->welcome_message();
        $importform->display();
    }
}

// Finish the page.
echo $OUTPUT->footer();
