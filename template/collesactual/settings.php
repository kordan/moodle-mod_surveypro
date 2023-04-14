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
 * Admin settings for surveyprotemplate_collesactual
 *
 * @package   surveyprotemplate_collesactual
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');
require_once($CFG->dirroot.'/mod/surveypro/template/collesactual/lib.php');

$options = array(
    SURVEYPRO_POSITIONLEFT => get_string('left', 'mod_surveypro'),
    SURVEYPRO_POSITIONTOP => get_string('top', 'mod_surveypro'),
    SURVEYPRO_POSITIONFULLWIDTH => get_string('fullwidth', 'mod_surveypro'),
);

$name = get_string('position', 'surveyprotemplate_collesactual');
$description = get_string('position_desc', 'surveyprotemplate_collesactual');
$settings->add(new admin_setting_configselect('surveyprotemplate_collesactual/position', $name, $description, SURVEYPRO_POSITIONFULLWIDTH, $options));

$options = array(
    SURVEYPROTEMPLATE_COLLESACTUALUSERADIO => get_string('useradio', 'surveyprotemplate_collesactual'),
    SURVEYPROTEMPLATE_COLLESACTUALUSESELECT => get_string('useselect', 'surveyprotemplate_collesactual')
);

$name = new lang_string('useritem', 'surveyprotemplate_collesactual');
$description = new lang_string('useritem_desc', 'surveyprotemplate_collesactual');
$settings->add(new admin_setting_configselect('surveyprotemplate_collesactual/useritem', $name, $description, SURVEYPROTEMPLATE_COLLESACTUALUSERADIO, $options));
