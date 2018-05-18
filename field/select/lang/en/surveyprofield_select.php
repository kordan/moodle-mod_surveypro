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
 * Strings for component 'surveyprofield_select', language 'en'
 *
 * @package   surveyprofield_select
 * @subpackage select
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/lib.php');

$string['allowed'] = 'allowed';
$string['customdefault'] = 'Custom';
$string['defaultoption_help'] = 'This is the value the remote user will find answered by default. The default for this type of question is mandatory so, whether not specified, it will be "Choose...".';
$string['defaultoption'] = 'Default';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['downloadformat'] = 'Download format';
$string['ierr_foreigndefaultvalue'] = 'The default item "{$a}" was not found among options';
$string['ierr_missingdefault'] = 'Custom default is missing. You may like to choose the "{$a}" one.';
$string['ierr_labelsduplicated'] = 'Labels must be different each other';
$string['ierr_valuesduplicated'] = 'Values must be different each other';
$string['labelother_help'] = 'If this question is equipped with the option "other" followed by a text field, enter here the label for that option. You can choose to write this option with the format: label'.SURVEYPRO_OTHERSEPARATOR.'value. The label will be displayed on the screen, the value will be used as default for the text field. If you only specify one word, the field default will be neglected.';
$string['labelother'] = 'Option "other"';
$string['option'] = 'Option';
$string['options_help'] = 'The list of the options for this item. You are allowed to write them as: value'.SURVEYPRO_VALUELABELSEPARATOR.'label in order to define value and label both. The label will be displayed in the drop down menu, the value will be stored in the field. If you only specify one word per line (without separator), value and label will both be valued to that word.';
$string['options'] = 'Options';
$string['parentformat'] = '[label]';
$string['pluginname'] = 'Select';
$string['returnlabels'] = 'label of selected item';
$string['returnposition'] = 'positional answer';
$string['returnvalues'] = 'value of selected item';
$string['uerr_missingothertext'] = 'Please add the text required by your selection';
$string['uerr_optionnotset'] = 'Please choose an option';
$string['userfriendlypluginname'] = 'Select';
