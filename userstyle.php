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
 * @package    surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true); // session not used here

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

$id = optional_param('id', 0, PARAM_INT);   // database id
$cmid = optional_param('cmid', 0, PARAM_INT);   // database id
$lifetime = 600;                                   // Seconds to cache this stylesheet

$PAGE->set_url('/mod/surveypro/css.php', array('id'=>$id));

if ($surveypro = $DB->get_record('surveypro', array('id' => $id))) {
    $fs = get_file_storage();
    $context = context_module::instance($cmid);

    $files = $fs->get_area_files($context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false);

    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
    header('Expires: ' . gmdate("D, d M Y H:i:s", time() + $lifetime) . ' GMT');
    header('Cache-control: max_age = '. $lifetime);
    header('Pragma: ');
    header('Content-type: text/css; charset=utf-8');  // Correct MIME type

    // test
    // echo 'body {background-color:green;}';

    foreach ($files as $file) {
        if ($file->is_directory()) {
            continue;
        }
        echo $file->get_content();
    }
}