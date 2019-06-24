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
 * Surveypro subplugin types declaration
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
$subplugins = array('surveyprofield' => 'mod/surveypro/field',
                    'surveyproformat' => 'mod/surveypro/format',
                    'surveyprotemplate' => 'mod/surveypro/template',
                    'surveyproreport' => 'mod/surveypro/report');

// TODO: this file is here because of compatibility with versions earlier than 3.7.
// Remember to drop it once $plugin->requires will be set to require Moodle 3.8 in versione.php.
// As debugging tool, instead of dropping it, you should consider the replacement of the code with the following one:

// debugging('Use of subplugins.php has been deprecated. Please provide a subplugins.json instead.', DEBUG_DEVELOPER);
// $subplugins = (array) json_decode(file_get_contents(__DIR__ . "/subplugins.json"))->plugintypes;
