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
 * This file is part of the Surveypro module for Moodle
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // Course ID.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);

// Get all required strings.
$strname = get_string('name');
$strsurveypro = get_string('modulename', 'mod_surveypro');
$strintro = get_string('moduleintro');
$strdataplural  = get_string('modulenameplural', 'mod_surveypro');
$inprogress = get_string('inprogresssubmissions', 'mod_surveypro');
$closed = get_string('closedsubmissions', 'mod_surveypro');

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/surveypro/index.php', array('id' => $id));
$PAGE->navbar->add($strsurveypro, new moodle_url('/mod/data/index.php', array('id' => $id)));
$PAGE->set_title($strsurveypro);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($strdataplural, 2);

\mod_surveypro\event\course_module_instance_list_viewed::create_from_course($course)->trigger();

// Get all the appropriate data.
if (!$surveypros = get_all_instances_in_course('surveypro', $course)) {
    $url = new moodle_url('/course/view.php', array('id' => $course->id));
    notice(get_string('thereareno', 'moodle', $strdataplural), $url);
    die();
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index surveypro_index';

if ($course->format == 'weeks') {
    $strweek = get_string('week');
    $table->head = array ($strweek, $strname, $strintro, $inprogress, $closed);
} else if ($course->format == 'topics') {
    $strtopic = get_string('topic');
    $table->head = array ($strtopic, $strname, $strintro, $inprogress, $closed);
} else {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head = array ($strsectionname, $strname, $strintro, $inprogress, $closed);
}
$table->colclasses = array('col1', 'col2', 'col3', 'col4', 'col5');

$currentsection = -1;
$sectionvisible = $DB->get_records_menu('course_sections', array('course' => $course->id), '', 'section, visible');

$sql = 'SELECT surveyproid, COUNT(id) as responses
        FROM {surveypro_submission}
        WHERE status = :status
        GROUP BY surveyproid';
$whereparams = array('status' => SURVEYPRO_STATUSINPROGRESS);
$inprogresssubmissions = $DB->get_records_sql_menu($sql, $whereparams);

$whereparams = array('status' => SURVEYPRO_STATUSCLOSED);
$closedsubmissions = $DB->get_records_sql_menu($sql, $whereparams);

foreach ($surveypros as $surveypro) {
    if ($surveypro->section != $currentsection) {
        if ($surveypro->section) {
            $printsection = get_section_name($course, $surveypro->section);
            $sectionclass = $sectionvisible[$surveypro->section] ? null : array('class' => 'dimmed');
        }
        $currentsection = $surveypro->section;
    } else {
        $printsection = '';
    }

    if (empty($sectionclass)) { // The section is visible.
        $cellclass = $surveypro->visible ? null : array('class' => 'dimmed');
    } else {
        $cellclass = array('class' => 'dimmed');
    }

    $url = new moodle_url('/mod/surveypro/view_submissions.php', array('id' => $surveypro->coursemodule));
    $inprogressresp = isset($inprogresssubmissions[$surveypro->id]) ? $inprogresssubmissions[$surveypro->id] : 0;
    $closedresp = isset($closedsubmissions[$surveypro->id]) ? $closedsubmissions[$surveypro->id] : 0;

    $content = array(html_writer::tag('span', $printsection, $sectionclass),
        html_writer::link($url, format_string($surveypro->name), $cellclass),
        html_writer::tag('span', format_module_intro('surveypro', $surveypro, $surveypro->coursemodule), $cellclass),
        html_writer::tag('span', $inprogressresp, $cellclass),
        html_writer::tag('span', $closedresp, $cellclass)
    );

    $table->data[] = $content;
}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer();
