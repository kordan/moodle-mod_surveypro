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
 * Internal library of functions for module surveypro
 *
 * All the surveypro specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * surveypro_get_item
 *
 * @param $itemid
 * @param $type
 * @param $plugin
 * @param optional $evaluateparentcontent
 * @return $item
 */
function surveypro_get_item($itemid=0, $type='', $plugin='', $evaluateparentcontent=false) {
    global $CFG, $DB, $PAGE;

    $cm = $PAGE->cm;

    if (empty($type) || empty($plugin)) {
        if (empty($itemid)) {
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected empty($itemid)', DEBUG_DEVELOPER);
        }
        $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    }

    require_once($CFG->dirroot.'/mod/surveypro/'.$type.'/'.$plugin.'/classes/plugin.class.php');
    $itemclassname = 'mod_surveypro_'.$type.'_'.$plugin;
    $item = new $itemclassname($cm, $itemid, $evaluateparentcontent);

    return $item;
}

/**
 * surveypro_non_empty_only
 *
 * @param $arrayelement
 * @return int length of the array element
 */
function surveypro_non_empty_only($arrayelement) {
    return strlen(trim($arrayelement)); // returns 0 if the array element is empty
}

/**
 * surveypro_textarea_to_array
 *
 * @param $textareacontent
 * @return $arraytextarea
 */
function surveypro_textarea_to_array($textareacontent) {

    $textareacontent = trim($textareacontent);
    $textareacontent = str_replace("\r", '', $textareacontent);

    $rows = explode("\n", $textareacontent);

    $arraytextarea = array_filter($rows, 'surveypro_non_empty_only');

    return $arraytextarea;
}

/**
 * surveypro_need_group_filtering
 * this function answer the question: do I Need to filter group in my next task?
 *
 * @param $cm
 * @param $context
 * @return $filtergroups
 */
function surveypro_need_group_filtering($cm, $context) {
    global $COURSE, $USER;

    // do I need to filter groups?
    $groupmode = groups_get_activity_groupmode($cm, $COURSE);
    $mygroups = groups_get_all_groups($COURSE->id, $USER->id, $cm->groupingid);
    $mygroups = array_keys($mygroups);

    $filtergroups = true;
    $filtergroups = $filtergroups && ($groupmode == SEPARATEGROUPS);
    $filtergroups = $filtergroups && (count($mygroups));
    $filtergroups = $filtergroups && (!has_capability('moodle/site:accessallgroups', $context));

    return $filtergroups;
}

/**
 * surveypro_fixlength
 *
 * @param $plainstring
 * @param optional $maxlength
 * @param $plainstring
 *
 * @return
 */
function surveypro_fixlength($plainstring, $maxlength=60) {
    $ellipsis = '...';
    $cutlength = $maxlength - strlen($ellipsis);
    if (strlen($plainstring) > $maxlength) {
        $plainstring = substr($plainstring, 0, $cutlength).$ellipsis;
        $return = $plainstring;
    }

    return $plainstring;
}

/**
 * surveypro_groupmates
 *
 * @param $cm: the course module
 * @param optional $userid: the user you want to know his/her groupmates
 * @return: an array with the list of groupmates of the user
 */
function surveypro_groupmates($cm, $userid=0) {
    global $COURSE, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $groupusers = array();
    if ($currentgroups = groups_get_all_groups($COURSE->id, $USER->id, $cm->groupingid)) {
        foreach ($currentgroups as $currentgroup) {
            $groupusers += groups_get_members($currentgroup->id, 'u.id');
        }
    }

    return array_keys($groupusers);
}

MoodleQuickForm::registerElementType('mod_surveypro_editor', "$CFG->dirroot/mod/surveypro/field/textarea/classes/mform_editor.php", 'mod_surveypro_mform_editor');
MoodleQuickForm::registerElementType('mod_surveypro_filemanager', "$CFG->dirroot/mod/surveypro/field/fileupload/classes/mform_filemanager.php", 'mod_surveypro_mform_filemanager');
MoodleQuickForm::registerElementType('mod_surveypro_select', "$CFG->dirroot/mod/surveypro/field/select/classes/mform_select.php", 'mod_surveypro_mform_select');
MoodleQuickForm::registerElementType('mod_surveypro_radio', "$CFG->dirroot/mod/surveypro/field/radiobutton/classes/mform_radio.php", 'mod_surveypro_mform_radio');
MoodleQuickForm::registerElementType('mod_surveypro_checkbox', "$CFG->dirroot/mod/surveypro/field/checkbox/classes/mform_checkbox.php", 'mod_surveypro_mform_checkbox');
MoodleQuickForm::registerElementType('mod_surveypro_advcheckbox', "$CFG->dirroot/mod/surveypro/field/checkbox/classes/mform_advcheckbox.php", 'mod_surveypro_mform_advcheckbox');

MoodleQuickForm::registerElementType('mod_surveypro_static', "$CFG->dirroot/mod/surveypro/format/label/classes/mform_static.php", 'mod_surveypro_mform_static');
