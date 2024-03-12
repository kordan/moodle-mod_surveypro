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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_page;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;

use mod_surveypro\templatebase;
use mod_surveypro\utemplate_manage;
use mod_surveypro\utemplate_save;
use mod_surveypro\utemplate_import;
use mod_surveypro\utemplate_apply;

use mod_surveypro\local\form\utemplate_createform;
use mod_surveypro\local\form\utemplate_importform;
use mod_surveypro\local\form\utemplate_applyform;

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$defaultsection = surveypro_get_defaults_section_per_area('utemplates');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
$section = optional_param('section', $defaultsection, PARAM_ALPHAEXT); // The section of code to execute.
$edit = optional_param('edit', -1, PARAM_BOOL);

// Verify I used correct names all along the module code.
$validsections = ['manage', 'save', 'import', 'apply'];
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

// MARK manage.
if ($section == 'manage') {
    // Get additional specific params.
    $utemplateid = optional_param('fid', 0, PARAM_INT);
    $action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
    $confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);

    // Required capability.
    require_capability('mod/surveypro:manageusertemplates', $context);

    // Calculations.
    $manageman = new utemplate_manage($cm, $context, $surveypro);
    $manageman->setup($utemplateid, $action, $confirm);

    $manageman->prevent_direct_user_input();

    if ($action == SURVEYPRO_EXPORTUTEMPLATE) {
        $manageman->trigger_event('usertemplate_exported');
        $manageman->export_utemplate();
        die();
    }

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'utemplates', 'section' => 'manage'];
    $url = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('utemplate_manage', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_utemplates_action_bar();

    if ($action == SURVEYPRO_DELETEUTEMPLATE) {
        $manageman->delete_utemplate();
    }

    $manageman->display_usertemplates_table();
    $manageman->trigger_event('all_usertemplates_viewed'); // Event: all_usertemplates_viewed.
}

// MARK save.
if ($section == 'save') {
    // Get additional specific params.
    $utemplateid = optional_param('fid', 0, PARAM_INT);

    // Required capability.
    require_capability('mod/surveypro:saveusertemplates', $context);

    // Calculations.
    $saveman = new utemplate_save($cm, $context, $surveypro);
    $saveman->setup($utemplateid);

    // $saveman->prevent_direct_user_input();
    // is not needed because the check has already been done here with: require_capability('mod/surveypro:saveusertemplates',...

    // Begin of: define $createutemplate return url.
    $formurl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $surveypro->id, 'section' => 'save']);
    // End of: define $createutemplate return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->saveman = $saveman;
    $formparams->defaultname = $surveypro->name;
    $createutemplate = new utemplate_createform($formurl, $formparams);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($saveman->formdata = $createutemplate->get_data()) {
        $saveman->generate_utemplate();
        $saveman->trigger_event('usertemplate_saved');

        $redirecturl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $surveypro->id, 'section' => 'manage']);
        redirect($redirecturl);
    }
    // End of: manage form submission.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'utemplates', 'section' => 'save'];
    $url = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('utemplate_save', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_utemplates_action_bar();

    $saveman->welcome_save_message();

    $record = new \stdClass();
    $record->surveyproid = $surveypro->id;

    $createutemplate->set_data($record);
    $createutemplate->display();
}

// MARK import.
if ($section == 'import') {
    // Get additional specific params.
    $utemplateid = optional_param('fid', 0, PARAM_INT);

    // Required capability.
    require_capability('mod/surveypro:importusertemplates', $context);

    // Calculations.
    $importman = new utemplate_import($cm, $context, $surveypro);
    $importman->setup($utemplateid);

    // $importman->prevent_direct_user_input();
    // is not needed because the check has already been done here with: require_capability('mod/surveypro:importusertemplates', $context);

    // Begin of: define $importutemplate return url.
    $formurl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $cm->instance, 'section' => 'import']);
    // End of: define $importutemplate return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->importman = $importman;
    $formparams->filemanageroptions = $importman->get_filemanager_options();
    $importutemplate = new utemplate_importform($formurl, $formparams);
    // End of: prepare params for the form.

    // Begin of: manage form submission.
    if ($importman->formdata = $importutemplate->get_data()) {
        $importman->upload_utemplate();
        $importman->trigger_event('usertemplate_imported');

        $redirecturl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $surveypro->id, 'section' => 'manage']);
        redirect($redirecturl);
    }
    // End of: manage form submission.

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'utemplates', 'section' => 'import'];
    $url = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('utemplate_import', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_utemplates_action_bar();

    $importman->welcome_import_message();
    $importutemplate->display();
}

// MARK apply.
if ($section == 'apply') {
    // Get additional specific params.
    $utemplateid = optional_param('fid', 0, PARAM_INT);
    $action = optional_param('act', SURVEYPRO_NOACTION, PARAM_INT);
    $confirm = optional_param('cnf', SURVEYPRO_UNCONFIRMED, PARAM_INT);

    // Required capability.
    require_capability('mod/surveypro:applyusertemplates', $context);

    // Calculations.
    $applyman = new utemplate_apply($cm, $context, $surveypro);
    $applyman->setup($utemplateid, $action, $confirm);

    $applyman->prevent_direct_user_input();
    $utemplates = $applyman->get_utemplates_items();

    if ($action != SURVEYPRO_APPLYUTEMPLATE) {
        // Begin of: define $applyutemplate return url.
        $formurl = new \moodle_url('/mod/surveypro/utemplates.php', ['s' => $cm->instance, 'section' => 'apply']);
        // End of: define $applyutemplate return url.

        // Begin of: prepare params for the form.
        $formparams = new \stdClass();
        $formparams->utemplates = $utemplates;
        $formparams->inlineform = false;
        $applyutemplate = new utemplate_applyform($formurl, $formparams);
        // End of: prepare params for the form.

        // Begin of: manage form submission.
        if ($applyman->formdata = $applyutemplate->get_data()) {
            // Bloody scenario.
            // I upload a usertemplate on monday.
            // I update surveypro on tuesday. This may make monday's usertemplates obsolete.
            // I arrive here and I try to apply an OBSOLETE usertemplate.
            // Somebody has to tell me I can not carry on.
            // $applyman->check_items_versions();
            $applyman->lastminute_template_check();
            $xmlvalidationoutcome = $applyman->get_xmlvalidationoutcome();
            if (!isset($xmlvalidationoutcome->key)) {
                $applyman->apply_template();
            }
        }
        // End of: manage form submission.
    } else {
        if ($confirm == SURVEYPRO_CONFIRMED_YES) {
            $applyman->lastminute_template_check();
            $xmlvalidationoutcome = $applyman->get_xmlvalidationoutcome();
            if (!isset($xmlvalidationoutcome->key)) {
                $applyman->apply_template();

                $paramurl = ['s' => $this->cm->instance, 'section' => 'itemslist'];
                $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
                redirect($returnurl);
            }
        }
    }

    // Set $PAGE params.
    $paramurl = ['s' => $surveypro->id, 'area' => 'utemplates', 'section' => 'apply'];
    $url = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->navbar->add(get_string('utemplate_apply', 'mod_surveypro'));
    // Is it useful? $PAGE->add_body_class('mediumwidth');.
    $utilitypageman->manage_editbutton($edit);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_utemplates_action_bar();

    $applyman->lastminute_stop();
    if ($action == SURVEYPRO_APPLYUTEMPLATE) {
        $applyman->ask_for_confirmation();
    }

    $riskyediting = ($surveypro->riskyeditdeadline > time());
    $utilitylayoutman = new utility_layout($cm, $surveypro);
    $utilitysubmissionman = new utility_submission($cm, $surveypro);
    if ($utilitylayoutman->has_submissions() && $riskyediting) {
        $message = $utilitysubmissionman->get_submissions_warning();
        echo $OUTPUT->notification($message, 'notifyproblem');
    }

    $applyman->welcome_apply_message();
    $applyutemplate->display();
}

// Finish the page.
echo $OUTPUT->footer();
