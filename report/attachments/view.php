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
 * Starting page of the attachment overview report.
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Needed only if $section == 'view'.
use surveyproreport_attachments\groupjumperform;
use surveyproreport_attachments\report;

// Needed only if $section == 'details'.
use surveyproreport_attachments\filterform;
use surveyproreport_attachments\form;

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->libdir.'/tablelib.php');

$id = optional_param('id', 0, PARAM_INT);
$s = optional_param('s', 0, PARAM_INT);
$section = optional_param('section', 'view', PARAM_TEXT); // The section of code to execute.

// Verify I used correct names all along the module code.
$validsections = ['view', 'details'];
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
require_course_login($course, false, $cm);
$context = \context_module::instance($cm->id);

// MARK view.
if ($section == 'view') { // It was view_cover.php
    $groupid = optional_param('groupid', 0, PARAM_INT);

    // Required capability.
    require_capability('mod/surveypro:accessreports', $context);

    // Begin of: set $PAGE deatils.
    $url = new \moodle_url('/mod/surveypro/reports.php', ['s' => $surveypro->id, 'report' => 'attachments', 'section' => 'view']);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->add_body_class('mediumwidth');
    // End of: set $PAGE deatils.

    $reportman = new report($cm, $context, $surveypro);
    $reportman->set_groupid($groupid);
    $reportman->setup_outputtable();

    // Begin of: instance groupfilterform.
    $showjumper = $reportman->is_groupjumper_needed();
    if ($showjumper) {
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

        $jumpercontent = $reportman->get_groupjumper_items();

        $formurl = new \moodle_url('/mod/surveypro/report/attachments/view.php', ['s' => $cm->instance]);

        $formparams = new \stdClass();
        $formparams->canaccessallgroups = $canaccessallgroups;
        $formparams->addnotinanygroup = $reportman->add_notinanygroup();
        $formparams->jumpercontent = $jumpercontent;
        $attributes = ['id' => 'surveypro_jumperform'];
        $groupfilterform = new groupjumperform($formurl, $formparams, null, null, $attributes);

        $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {
            $('#id_groupid').change(function() {
                $('#surveypro_jumperform').submit();
            });
        });");
    }
    // End of: instance groupfilterform.

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_reports_action_bar();

    $reportman->prevent_direct_user_input('attachments');
    $reportman->check_attachmentitems();

    if ($showjumper) {
        $groupfilterform->set_data(['groupid' => $groupid]);
        $groupfilterform->display();
    }

    $reportman->fetch_data();
    $reportman->output_data();
}

// MARK details.
if ($section == 'details') { // It was report/attachments/uploads.php
    $container = required_param('container', PARAM_ALPHANUMEXT);
    $itemid = optional_param('itemid', 0, PARAM_INT);  // Item id.
    $changeuser = optional_param('changeuser', 0, PARAM_TEXT);

    // Required capability.
    $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $context);
    $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $context);

    if ($changeuser) {
        $returnurl = new \moodle_url('/mod/surveypro/report/attachments/view.php', ['s' => $cm->instance]);
        redirect($returnurl);
    }

    // I am forced to use the container because I must choose user and submission at once from filterform.container drop down menu.
    $parts = explode('_', $container);
    $userid = (int)$parts[0];
    $submissionid = (int)$parts[1];
    if (!$submissionid) {
        $submissionid = $DB->get_field('surveypro_submission', 'MIN(id)', ['userid' => $userid, 'surveyproid' => $surveypro->id]);
    }

    // Begin of: set $PAGE deatils.
    $paramurl = ['s' => $cm->instance, 'report' => 'attachments', 'section' => 'details', 'container' => $container];
    $url = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
    $PAGE->set_url($url);
    $PAGE->set_context($context);
    $PAGE->set_cm($cm);
    $PAGE->set_title($surveypro->name);
    $PAGE->set_heading($course->shortname);
    $PAGE->add_body_class('mediumwidth');
    // End of: set $PAGE deatils.

    // Calculations.
    $uploadsformman = new form($cm, $context, $surveypro);
    $uploadsformman->prevent_direct_user_input();
    $uploadsformman->set_userid($userid);
    $uploadsformman->set_itemid($itemid);
    $uploadsformman->set_submissionid($submissionid);

    // Begin of: define $filterform return url.
    $formurl = new \moodle_url('/mod/surveypro/report/attachments/view.php', ['s' => $cm->instance, 'section' => 'details']);
    // End of: define $user_form return url.

    // Begin of: prepare params for the form.
    $formparams = new \stdClass();
    $formparams->surveypro = $surveypro;
    $formparams->itemid = $itemid;
    $formparams->userid = $userid;
    $formparams->submissionid = $submissionid;
    $formparams->container = $userid.'_'.$submissionid;
    $formparams->canaccessreserveditems = $canaccessreserveditems;
    $formparams->canviewhiddenactivities = $canviewhiddenactivities;
    // End of: prepare params for the form.

    $filterform = new filterform($formurl, $formparams, 'post', '', ['id' => 'userentry']);

    // Output starts here.
    echo $OUTPUT->header();

    $actionbar = new \mod_surveypro\output\action_bar($cm, $context, $surveypro);
    echo $actionbar->draw_reports_action_bar();

    $filterform->display();

    $uploadsformman->display_attachment($submissionid, $itemid);
}
// Finish the page.
echo $OUTPUT->footer();
