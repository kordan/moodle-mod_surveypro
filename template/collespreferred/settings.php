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
 * Admin settings for surveyprotemplate_collespreferred
 *
 * @package   surveyprotemplate_collespreferred
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');
require_once($CFG->dirroot.'/mod/surveypro/template/collespreferred/lib.php');

$options = array(
    SURVEYPRO_POSITIONLEFT => get_string('left', 'mod_surveypro'),
    SURVEYPRO_POSITIONTOP => get_string('top', 'mod_surveypro'),
    SURVEYPRO_POSITIONFULLWIDTH => get_string('fullwidth', 'mod_surveypro'),
);

$name = get_string('position', 'surveyprotemplate_collespreferred');
$description = get_string('position_desc', 'surveyprotemplate_collespreferred');
$settings->add(new admin_setting_configselect('surveyprotemplate_collespreferred/position', $name, $description, SURVEYPRO_POSITIONFULLWIDTH, $options));

$options = array(
    SURVEYPROTEMPLATE_COLLESPREFERREDUSERADIO => get_string('useradio', 'surveyprotemplate_collespreferred'),
    SURVEYPROTEMPLATE_COLLESPREFERREDUSESELECT => get_string('useselect', 'surveyprotemplate_collespreferred')
);

$name = new lang_string('useritem', 'surveyprotemplate_collespreferred');
$description = new lang_string('useritem_desc', 'surveyprotemplate_collespreferred');
$settings->add(new admin_setting_configselect('surveyprotemplate_collespreferred/useritem', $name, $description, SURVEYPROTEMPLATE_COLLESPREFERREDUSERADIO, $options));
