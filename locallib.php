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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * Load the class of the specified item
 *
 * @param object $cm
 * @param object $surveypro
 * @param int $itemid
 * @param string $type
 * @param string $plugin
 * @param bool $getparentcontent
 * @return $item object
 */
function surveypro_get_item($cm, $surveypro, $itemid=0, $type='', $plugin='', $getparentcontent=false) {
    global $CFG, $DB;

    if (!empty($itemid)) {
        $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'surveyproid, type, plugin', MUST_EXIST);
        if ($cm->instance != $itemseed->surveyproid) {
            $message = 'Mismatch between passed itemid ('.$itemid.') and corresponding cm->instance ('.$cm->instance.')';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    if (empty($type) || empty($plugin)) {
        if (empty($itemid)) {
            $message = 'Unexpected empty($itemid)';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    } else {
        if (isset($itemseed)) {
            if ($type != $itemseed->type) {
                $message = 'Mismatch between passed type ('.$type.') and found type ('.$itemseed->type.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
            if ($plugin != $itemseed->plugin) {
                $message = 'Mismatch between passed plugin ('.$plugin.') and found plugin ('.$itemseed->plugin.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
    }

    $classname = 'surveypro'.$type.'_'.$plugin.'_'.$type;
    $item = new $classname($cm, $surveypro, $itemid, $getparentcontent);

    return $item;
}

/**
 * Copy the content of multiline textarea to an array line by line
 *
 * @param string $textareacontent
 * @return array
 */
function surveypro_multilinetext_to_array($textareacontent) {
    // begin with a simple trim to drop each starting and closing empty row and spaces.
    $textareacontent = trim($textareacontent);

    // \r are not welcome.
    $textareacontent = str_replace("\r", '', $textareacontent);

    // Use preg_replace (and not str_replace) because of eventual multiple instances of "\n\n".
    $textareacontent = preg_replace('~\n\n+~', "\n", $textareacontent);

    if (!strlen($textareacontent)) {
        return array();
    }

    // Build the array.
    $rows = explode("\n", $textareacontent);

    // Trim each its line.
    $rows = array_map('trim', $rows);

    // Trim each part whether exists.
    foreach ($rows as $k => $row) {
        if (preg_match('~^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$~', $row, $match)) {
            $value = $match[1];
            $label = $match[2];
            $rows[$k] = trim($value).SURVEYPRO_VALUELABELSEPARATOR.trim($label);
        }
    }

    return $rows;
}

/**
 * surveypro_need_group_filtering
 * this function answer the question: do I Need to filter group in my next task?
 *
 * @param object $cm
 * @param object $context
 * @return $filtergroups
 */
function surveypro_need_group_filtering($cm, $context) {
    global $COURSE, $USER;

    // Do I need to filter groups?
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
 * cut down a string and close it with ellipsis
 *
 * @param string $plainstring
 * @param int $maxlength
 *
 * @return void
 */
function surveypro_cutdownstring($plainstring, $maxlength=60) {
    if (strlen($plainstring) > $maxlength) {
        $ellipsis = '...';
        $cutlength = $maxlength - strlen($ellipsis);
        $plainstring = substr($plainstring, 0, $cutlength).$ellipsis;
    }

    return $plainstring;
}

/**
 * surveypro_groupmates
 *
 * @param object $cm
 * @param int $userid Optional $userid: the user you want to know his/her groupmates
 * @return Array with the list of groupmates of the user
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
