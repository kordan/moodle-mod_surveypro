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
 * Strings for component 'surveyprofield_checkbox', language 'en'
 *
 * @package   surveyprofield_checkbox
 * @subpackage checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/lib.php');

$string['adjustment_help'] = 'How this list of items will be shown? In horizontal or in vertical?';
$string['adjustment'] = 'Adjustment';
$string['allowed'] = 'allowed';
$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default.';
$string['defaultvalue'] = 'Default';
$string['downloadformat_help'] = 'Use this option to define the format of the value returned by this field.<br />Choosing \'<strong>selection</strong>\' you get a comma separated list of the values corresponding to the selection of the remote user.<br />Choosing \'<strong>positional answer</strong>\' you get an answer made by as much values as the number of the options defined for this field. For each option selected by the remote user you will get a 1 (or the corresponding value whether defined), for each option not selected by the remote user you will get a 0.<br />Example: let us suppose the question: "What do you usually get for breakfast?" with options: "milk, jam, ham, eggs, bread, orange juice". Let us futher suppose that the user selected: "ham" AND "eggs" AND "orange juice".<br />By choosing \'selection\' here, the value returned by this item will be: "ham, eggs, orange juice".<br />By choosing \'positional answer\' here, the value returned by this item will be: "0, 0, 1, 1, 0, 1" because the first and the second options ("milk, jam") were not choosed, the third and the fourth options ("ham, eggs") were selected, the second last ("bread") was not selected and the last one ("orange juice") was choosed by the remote user.';
$string['downloadformat'] = 'Download format';
$string['horizontal'] = 'horizontal';
$string['ierr_defaultsduplicated'] = 'Defaults must be different each other';
$string['ierr_foreigndefaultvalue'] = 'The default item "{$a}" was not found among options';
$string['ierr_labelduplicated'] = 'Labels must be different each other';
$string['ierr_maximumrequired'] = 'The maximun number of checkboxes to select must be lower than {$a}';
$string['ierr_maxrequiredlowerthanminrequired'] = 'Maximum number of checkboxes to select can not be lower than minimum';
$string['ierr_minimumrequired'] = 'The minimum number of checkboxes to select must be lower than {$a}';
$string['ierr_optionswithseparator'] = 'Options can not contain "{$a}"';
$string['ierr_valuesduplicated'] = 'Values must be different each other';
$string['labelother_help'] = 'If this question is equipped with the option "other" followed by a text field, enter here the label for that option. You can choose to write this option with the format: label'.SURVEYPRO_OTHERSEPARATOR.'value. The label will be displayed on the screen, the value will be used as default for the text field. If you only specify one word, the field default will be neglected.';
$string['labelother'] = 'Option "other"';
$string['maximumrequired_help'] = 'The maximum number of checkboxes the user can choose in his/her answer';
$string['maximumrequired'] = 'Maximum allowed options';
$string['minimumrequired_help'] = 'The minimum number of checkboxes the user is forced to choose in his/her answer';
$string['minimumrequired'] = 'Minimum required options';
$string['noanswerdefault_help'] = 'Use this option to include "No answer" among defaults';
$string['noanswerdefault'] = '"No answer" as defaults';
$string['option'] = 'Option';
$string['options_help'] = 'The list of the options for this item. You are allowed to write them as: value'.SURVEYPRO_VALUELABELSEPARATOR.'label in order to define value and label both. The label will be displayed close to the corresponding checkbox, the value will be stored in the survey field. If you only specify one word per line (without separator), value and label will both be valued to that word.';
$string['options'] = 'Options';
$string['parentformat'] = '[One<br />label<br />per<br />line]';
$string['pluginname'] = 'Checkbox';
$string['restrictions_maximumrequired_more'] = 'No more than {$a} checkboxes are allowed';
$string['restrictions_maximumrequired_one'] = 'No more than 1 checkbox is allowed';
$string['restrictions_minimumrequired_more'] = 'At least {$a} checkboxes have to be selected';
$string['restrictions_minimumrequired_one'] = 'At least 1 checkbox has to be selected';
$string['returnlabels'] = 'label of selected items';
$string['returnposition'] = 'positional answer';
$string['returnvalues'] = 'value of selected items';
$string['uerr_greaterthanmaximum_more'] = 'Please tick no more than {$a} options';
$string['uerr_greaterthanmaximum_one'] = 'Please tick no more than 1 option';
$string['uerr_lowerthanminimum_more'] = 'Please tick at least {$a} options';
$string['uerr_lowerthanminimum_one'] = 'Please tick at least 1 option';
$string['uerr_missingothertext'] = 'Please add the text required by your selection';
$string['userfriendlypluginname'] = 'Checkbox';
$string['vertical'] = 'vertical';
