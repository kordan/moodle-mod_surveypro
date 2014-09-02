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
 * @copyright  2013 kordan <kordan@mclink.it>
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
function surveypro_get_item($itemid, $type='', $plugin='', $evaluateparentcontent=true) {
    global $CFG, $DB;

    if (empty($type) || empty($plugin)) {
        $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    }

    require_once($CFG->dirroot.'/mod/surveypro/'.$type.'/'.$plugin.'/plugin.class.php');
    $classname = 'surveypro'.$type.'_'.$plugin;
    $item = new $classname($itemid, $evaluateparentcontent);

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
    global $COURSE;

    // do I need to filter groups?
    $groupmode = groups_get_activity_groupmode($cm, $COURSE);
    $mygroups = surveypro_get_my_groups_simple();

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
 * surveypro_get_my_groups_simple
 * this function is simpler and less resource asking than the core groups_get_my_groups();
 * I really don't need all the infos returned by the core function
 *
 * @param optional $userid: the user you want to know his/her groups
 * @return: an array with the list of the group the user belongs to
 */
function surveypro_get_my_groups_simple($userid=0) {
    global $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $mygroups = $DB->get_records('groups_members', array('userid' => $userid), 'groupid', 'groupid');

    return array_keys($mygroups);
}

/**
 * surveypro_groupmates
 *
 * @param optional $userid: the user you want to know his/her groupmates
 * @return: an array with the list of groupmates of the user
 */
function surveypro_groupmates($userid=0) {
    global $DB, $USER;

    if (empty($userid)) {
        $userid = $USER->id;
    }

    if ($mygroups = surveypro_get_my_groups_simple($userid)) {
        $sql = 'SELECT DISTINCT gm.userid
            FROM {groups_members} gm
                JOIN {groups} g ON gm.groupid = g.id
            WHERE g.id IN ('.implode(',', $mygroups).')
            ORDER BY gm.userid ASC';

        $groupmates = $DB->get_records_sql($sql);

        return array_keys($groupmates);
    } else  {
        return array();
    }
}
