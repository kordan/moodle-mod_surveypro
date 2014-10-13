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
 * Definition of log events
 * NOTE: this is an example how to insert log event during installation/update.
 * It is not really essential to know about it, but these logs were created as example
 * in the previous 1.9 SURVEY.
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$logs = array(
    array('module' => 'surveypro', 'action' => 'add', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'update', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'view', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'view all', 'mtable' => 'surveypro', 'field' => 'name'),

    array('module' => 'surveypro', 'action' => 'all items viewed', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'all submissions deleted', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'all submissions exported', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'all submissions viewed', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'all usertemplates viewed', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'item created', 'mtable' => 'surveypro_item', 'field' => 'plugin'),
    array('module' => 'surveypro', 'action' => 'item deleted', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'item modified', 'mtable' => 'surveypro_item', 'field' => 'plugin'),
    array('module' => 'surveypro', 'action' => 'form previewed', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'submission created', 'mtable' => 'surveypro_submission', 'field' => 'id'),
    array('module' => 'surveypro', 'action' => 'submission deleted', 'mtable' => 'surveypro_submission', 'field' => 'id'),
    array('module' => 'surveypro', 'action' => 'submission downloaded to pdf', 'mtable' => 'surveypro_submission', 'field' => 'id'),
    array('module' => 'surveypro', 'action' => 'submission modified', 'mtable' => 'surveypro_submission', 'field' => 'id'),
    array('module' => 'surveypro', 'action' => 'submission viewed', 'mtable' => 'surveypro_submission', 'field' => 'id'),
    array('module' => 'surveypro', 'action' => 'mastertemplate applied', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'mastertemplate saved', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'usertemplate applied', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'usertemplate exported', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'usertemplate saved', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'usertemplate imported', 'mtable' => 'surveypro', 'field' => 'name'),
    array('module' => 'surveypro', 'action' => 'usertemplate deleted', 'mtable' => 'surveypro', 'field' => 'name'),
);
