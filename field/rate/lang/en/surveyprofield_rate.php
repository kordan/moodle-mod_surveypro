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
 * Strings for component 'surveyprofield_rate', language 'en'
 *
 * @package   surveyprofield_rate
 * @subpackage rate
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['customdefault'] = 'Custom';
$string['defaultoption'] = 'Default';
$string['defaultoption_help'] = 'This is the value the remote user will find answered by default. The default for this type of question is mandatory so, whether not specified, it will be "Choose...".';
$string['differentrates'] = 'Force different rates';
$string['differentrates_help'] = 'Force the user to rate each element with a different value';
$string['diffratesrequired'] = 'Scores are supposed to be different each other';
$string['downloadformat'] = 'Download format';
$string['downloadformat_help'] = 'Use this option to define the format of the value returned by this field.<br>Choosing \'<strong>selection</strong>\' you get a comma separated list of the values corresponding to the selection of the remote user.<br>Choosing \'<strong>positional answer</strong>\' you get an answer made by as much values as the number of the options defined for this field. For each option selected by the remote user you will get a 1 (or the corresponding value whether defined), for each option not selected by the remote user you will get a 0.<br>Example: let us suppose the question: "What do you usually get for breakfast?" with options: "milk, jam, ham, eggs, bread, orange juice". Let us futher suppose that the user selected: "ham" AND "eggs" AND "orange juice".<br>By choosing \'selection\' here, the value returned by this item will be: "ham, eggs, orange juice".<br>By choosing \'positional answer\' here, the value returned by this item will be: "0, 0, 1, 1, 0, 1" because the first and the second options ("milk, jam") were not choosed, the third and the fourth options ("ham, eggs") were selected, the second last ("bread") was not selected and the last one ("orange juice") was choosed by the remote user.';
$string['ierr_defaultsduplicated'] = 'Defaults have to be different when different rates is required';
$string['ierr_foreigndefaultvalue'] = 'The default item "{$a}" was not found among rates';
$string['ierr_invaliddefaultscount'] = 'Number of defaults has to be equal to the number of options';
$string['ierr_labelsduplicated'] = 'Rates must be different each other';
$string['ierr_notenoughrates'] = 'Number of rates is not enough to force different rates';
$string['ierr_optionsduplicated'] = 'Options must be different each other';
$string['ierr_singleoption'] = 'A single option is not allowed';
$string['ierr_singlerate'] = 'A single rate is not allowed';
$string['ierr_valuesduplicated'] = 'Values must be different each other';
$string['options'] = 'Options';
$string['options_help'] = 'The list of the options for this item.';
$string['pluginname'] = 'Rate';
$string['privacy:metadata'] = 'The "Rate" field plugin does not store any personal data.';
$string['rates'] = 'Rates';
$string['rates_help'] = 'The list of values to rate the options of this question. You can choose to write them with the format: value::label. The label will be displayed on the screen, the value will be stored in the survey field. If you only specify one word per line, value and label will both be valued to that word. (Take care: the separator "::" is defined in lib.php and can be changed by a developer)';
$string['returnlabels'] = 'list of options with corresponding labels of rates';
$string['returnposition'] = 'positional answer';
$string['returnvalues'] = 'list of options with corresponding values of rates';
$string['style'] = 'Element style';
$string['style_help'] = 'You can choose whether you want to allow the rate of elements using drop down menus or radio button. The overall result will be affected by this option.';
$string['uerr_duplicaterate'] = 'Duplicate rate is not allowed';
$string['uerr_optionnotset'] = 'Please choose an option';
$string['usemenu'] = 'dropdown menu';
$string['useradio'] = 'radio buttons';
$string['userfriendlypluginname'] = 'Rate';
