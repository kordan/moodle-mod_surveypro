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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT); // Course Module ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

unset($id);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

// Get all required strings
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/surveypro/index.php', array('id' => $course->id));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->shortname);
$PAGE->navbar->add(get_string('modulename', 'surveypro'););
echo $OUTPUT->header();

$eventdata = array('context' => context_course::instance($course->id));
$event = \mod_surveypro\event\course_module_instance_list_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->trigger();

// / Print the header

// / Get all the appropriate data
if (!$surveypros = get_all_instances_in_course('surveypro', $course)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'surveypro')), "$CFG->wwwroot/course/view.php?id=$course->id");
    die();
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($surveypros as $surveypro) {
    $cm = $modinfo->get_cm($surveypro->coursemodule);
    if ($usesections) {
        $printsection = '';
        if ($surveypro->section !== $currentsection) {
            if ($surveypro->section) {
                $printsection = get_section_name($course, $surveypro->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $surveypro->section;
        }
    } else {
        $printsection = html_writer::tag('span', userdate($surveypro->timemodified), array('class' => 'smallinfo'));
    }

    $class = $surveypro->visible ? null : array('class' => 'dimmed'); // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        html_writer::link(new moodle_url('view.php', array('id' => $cm->id)), format_string($surveypro->name), $class),
        format_module_intro('surveypro', $surveypro, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
