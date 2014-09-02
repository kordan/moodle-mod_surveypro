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
 * @package    surveyprofield
 * @subpackage fileupload
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

define('SURVEYPROFIELD_FILEUPLOAD_FILEAREA', 'fileuploadfiles');

/**
 * Serves fileupload submissions and other files.
 *
 * @param mixed $course course or id of the course
 * @param mixed $cm course module or id of the course module
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - just send the file
 */
function surveyprofield_fileupload_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    // prevent_direct_user_input

    $answerid = (int)array_shift($args);
    // from $answerid to $answer
    $sql = 'SELECT plugin, surveyproid
            FROM {surveypro_item} si
                JOIN {surveypro_answer} a ON a.itemid = si.id
            WHERE a.id = :answerid';
    $sqlparams = array('answerid' => $answerid);
    $answer = $DB->get_record_sql($sql, $sqlparams, MUST_EXIST);

    if ($cm->instance != $answer->surveyproid) {
        return false;
    }

    if ($answer->plugin != 'fileupload') {
        return false;
    }

    $relativepath = implode('/', $args);

    $fullpath = "/{$context->id}/surveyprofield_fileupload/$filearea/$answerid/$relativepath";

    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Download MUST be forced - security!
    send_stored_file($file, 0, 0, true);
}
