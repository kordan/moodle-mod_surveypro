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
 * Starting page to create a mastertemplate
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;

use mod_surveypro\mtemplate_apply;
use mod_surveypro\mtemplate_save;

use mod_surveypro\local\form\mtemplate_createform;
use mod_surveypro\local\form\mtemplate_applyform;

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$defaultsection = surveypro_get_defaults_section_per_area('mtemplates');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
$section = optional_param('section', $defaultsection, PARAM_ALPHAEXT); // The section of code to execute.
$edit = optional_param('edit', -1, PARAM_BOOL);

// Verify I used correct names all along the module code.
$validsections = ['save', 'apply'];
if (!in_array($section, $validsections)) {
    $message = 'The section param \''.$section.'\' is invalid.';
    debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
}
// End of: Verify I used correct names all along the module code.

if (!empty($id)) {
    [$course, $cm] = get_course_and_cm_from_cmid($id, 'surveypro');
    $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $surveypro = $DB->get_record('surveypro', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $surveypro->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $course->id, false, MUST_EXIST);
}

require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// Utilitypage is going to be used in each section. This is the reason why I load it here.
$utilitypageman = new utility_page($cm, $surveypro);

// MARK save.
if ($section == 'save') {
    // Get additional specific params.

    // Required capability.
    require_capability('mod/surveypro:savemastertemplates', $context);

    // Calculations.
    $saveman = new mtemplate_save($cm, $context, $surveypro);

    // Start of: define $createmtemplate return url.
    $formurl = new \moodle_url('/mod/surveypro/mtemplates.php', ['s' => $cm->instance, 'section' => 'save']);
    // End of: define $createutemplate return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->defaultname = $surveypro->name;
    $createmtemplate = new mtemplate_createform($formurl, $formparams);
    // End of: define $createmtemplate return url.

    // Start of: manage form submission.
    if ($saveman->formdata = $createmtemplate->get_data()) {
        $saveman->download_mtemplate();
        $saveman->trigger_event('mastertemplate_saved');
        exit(0);
    }
    // End of: manage form submission.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'mtemplates', 'section' => 'save'];
    $url = new \moodle_url('/mod/surveypro/mtemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('mtemplate_save', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_mtemplates_action_bar();

    echo $OUTPUT->notification(get_string('currenttotemplate', 'mod_surveypro'), 'notifymessage');

    $record = new \stdClass();
    $record->surveyproid = $surveypro->id;

    $createmtemplate->set_data($record);
    $createmtemplate->display();
}

// MARK apply.
if ($section == 'apply') {
    // Get additional specific params.

    // Required capability.
    require_capability('mod/surveypro:applymastertemplates', $context);

    // Calculations.
    $applyman = new mtemplate_apply($cm, $context, $surveypro);

    // Begin of: define $applymtemplate return url.
    $formurl = new \moodle_url('/mod/surveypro/mtemplates.php', ['s' => $cm->instance, 'section' => 'apply']);
    // End of: define $applymtemplate return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->cmid = $cm->id;
    $formparams->surveypro = $surveypro;
    $formparams->applyman = $applyman;
    $formparams->inlineform = false;
    $applymtemplate = new mtemplate_applyform($formurl, $formparams);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($applyman->formdata = $applymtemplate->get_data()) {
        $applyman->set_mastertemplate($applyman->formdata->mastertemplate);
        $applyman->lastminute_template_check();
        if (!isset($applyman->xmlvalidationoutcome->key)) {
            $applyman->apply_template();
            $applyman->trigger_event('mastertemplate_applied');
        }
    }
    // End of: manage form submission.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'mtemplates', 'section' => 'apply'];
    $url = new \moodle_url('/mod/surveypro/mtemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('mtemplate_apply', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_mtemplates_action_bar();

    $applyman->lastminute_stop();

    $applyman->friendly_stop();

    $riskyediting = ($surveypro->riskyeditdeadline > time());
    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitysubmissionman = new utility_submission($cm, $surveypro);
    if ($utilitylayoutman->has_submissions() && $riskyediting) {
        $message = $utilitysubmissionman->get_submissions_warning();
        echo $OUTPUT->notification($message, 'notifyproblem');
    }

    $applyman->welcome_apply_message();

    $applymtemplate->display();
}

// Finish the page.
echo $OUTPUT->footer();

