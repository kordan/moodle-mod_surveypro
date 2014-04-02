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

/*
 * @package    surveyprofield
 * @subpackage autofill
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT', 15);
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT01', 'submissionid');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT02', 'submissiontime');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT03', 'submissiondate');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT04', 'submissiondateandtime');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT05', 'userid');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT06', 'userfirstname');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT07', 'userlastname');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT08', 'userfullname');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT09', 'usergroupid');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT10', 'usergroupname');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT11', 'surveyproid');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT12', 'surveyproname');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT13', 'courseid');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT14', 'coursename');
define('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT15', 'label');

/*
 * surveypro_autofill_get_elements
 * @param
 * @return
 */
function surveypro_autofill_get_elements($surveyproid) {
    global $COURSE;

    $cm = get_coursemodule_from_instance('surveypro', $surveyproid, $COURSE->id, false, MUST_EXIST);
    $usegroups = groups_get_activity_groupmode($cm, $COURSE);

    $options = array();
    $options[''] = array('' => get_string('choosedots'));

    // submission date and time
    $begin = 1;
    $end = $begin + 3; // 3 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyprofield_autofill');
    }
    $menuitemlabel = get_string('submission', 'surveyprofield_autofill');
    $options[$menuitemlabel] = $subelements;

    // user
    $begin = $end + 1;
    $menuelements = 3; // 3 == ('number of cycles' - 1)
    if ($usegroups) {
        $menuelements += 2; // 'group ID' and 'group name'
    }
    $end = $begin + $menuelements;
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyprofield_autofill');
    }
    $menuitemlabel = get_string('user');
    $options[$menuitemlabel] = $subelements;

    // surveypro
    $begin = $end + 1;
    if (!$usegroups) { // jump last two menu items
        $begin += 2;
    }
    $end = $begin + 1; // 1 == ('number of cycles' - 1)

    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyprofield_autofill');
    }
    $menuitemlabel = get_string('modulename', 'surveypro');
    $options[$menuitemlabel] = $subelements;

    // course
    $begin = $end + 1;
    $end = $begin + 1; // 1 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyprofield_autofill');
    }
    $menuitemlabel = get_string('course');
    $options[$menuitemlabel] = $subelements;

    // submission info

    // custom info
    $begin = $end + 1;
    $end = $begin; // 0 == ('number of cycles' - 1)
    $subelements = array();
    for ($i = $begin; $i <= $end; $i++) {
        $value = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        $subelements[$value] = get_string($value, 'surveyprofield_autofill');
    }
    $menuitemlabel = get_string('custominfo', 'surveyprofield_autofill');
    $options[$menuitemlabel] = $subelements;

    return $options;
}