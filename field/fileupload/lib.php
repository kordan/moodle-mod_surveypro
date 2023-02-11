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
 * Library for surveyprofield_fileupload
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
 * @param array $options
 * @return bool false if file not found, does not return if found - just send the file
 */
function surveyprofield_fileupload_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    // Prevent_direct_user_input.

    $answerid = (int)array_shift($args);
    // From $answerid to $answer.
    $sql = 'SELECT i.plugin, i.surveyproid
            FROM {surveypro_item} i
              JOIN {surveypro_answer} a ON a.itemid = i.id
            WHERE a.id = :answerid';
    $whereparams = ['answerid' => $answerid];
    $answer = $DB->get_record_sql($sql, $whereparams, MUST_EXIST);

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
