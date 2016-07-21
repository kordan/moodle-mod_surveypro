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
 * Strings for component 'surveyprofield_multiselect', language 'en'
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/lib.php');

$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default';
$string['defaultvalue'] = 'Default';
$string['defaultvalue'] = 'Default';
$string['downloadformat_help'] = 'Use this option to define the format of the value returned by this field.<br />Choosing \'<strong>selection</strong>\' you get a comma separated list of the values corresponding to the selection of the remote user.<br />Choosing \'<strong>positional answer</strong>\' you get an answer made by as much values as the number of the options defined for this field. For each option selected by the remote user you will get a 1 (or the corresponding value whether defined), for each option not selected by the remote user you will get a 0.<br />Example: let us suppose the question: "What do you usually get for breakfast?" with options: "milk, jam, ham, eggs, bread, orange juice". Let us futher suppose that the user selected: "ham" AND "eggs" AND "orange juice".<br />By choosing \'selection\' here, the value returned by this item will be: "ham, eggs, orange juice".<br />By choosing \'positional answer\' here, the value returned by this item will be: "0, 0, 1, 1, 0, 1" because the first and the second options ("milk, jam") were not choosed, the third and the fourth options ("ham, eggs") were selected, the second last ("bread") was not selected and the last one ("orange juice") was choosed by the remote user.';
$string['downloadformat'] = 'Download format';
$string['heightinrows_help'] = 'The number of rows the multiselect will show';
$string['heightinrows'] = 'Height in rows';
$string['ierr_foreigndefaultvalue'] = 'The default item "{$a}" was not found among options';
$string['ierr_minimumrequired'] = 'The minimum number of items to select must be lower than {$a}';
$string['ierr_defaultsduplicated'] = 'Defaults must be different each other';
$string['ierr_optionsduplicated'] = 'Options must be different each other';
$string['ierr_optionswithseparator'] = 'Options can not contain "{$a}"';
$string['minimumrequired_help'] = 'The minimum number of items the user is forced to choose in his/her answer';
$string['minimumrequired'] = 'Minimum required items';
$string['option'] = 'Option';
$string['options_help'] = 'The list of the options for this item. You are allowed to write them as: value'.SURVEYPRO_VALUELABELSEPARATOR.'label in order to define value and label both. The label will be displayed in the element list, the value will be stored in the survey field. If you only specify one word per line (without separator), value and label will both be valued to that word.';
$string['options'] = 'Options';
$string['parentformat'] = '[One<br />label<br />per<br />line]';
$string['pluginname'] = 'Multiple selection';
$string['restrictions_minimumrequired_more'] = 'At least {$a} items have to be selected';
$string['restrictions_minimumrequired_one'] = 'At least 1 item has to be selected';
$string['returnlabels'] = 'label of selected items';
$string['returnposition'] = 'positional answer';
$string['returnvalues'] = 'value of selected items';
$string['uerr_lowerthanminimum_more'] = 'Please select at least {$a} options';
$string['uerr_lowerthanminimum_one'] = 'Please select at least 1 option';
$string['userfriendlypluginname'] = 'Multiple selection';
