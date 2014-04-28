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
 * Strings for component 'field_time', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    surveypro
 * @subpackage time
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['currenttimedefault'] = 'Current time';
$string['customdefault'] = 'Custom';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultoption_help'] = 'This is the time the remote user will find answered by default. The default for this type of question is mandatory. If "Current time" is choosed as default, boundaries are not supposed to apply.';
$string['defaultoption'] = 'Default';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['downloadformat'] = 'Download format';
$string['fifteenminutes'] = 'fifteen minutes';
$string['fiveminutes'] = 'five minutes';
$string['invitationhour'] = 'Choose an hour';
$string['invitationminute'] = 'Choose a minute';
$string['lowerbound_help'] = 'The lowest time the user is allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['lowerequaltoupper'] = 'Lower and upper bounds need to be different';
$string['oneminute'] = 'One minute';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['outofexternalrangedefault'] = 'Default does not fall within the specified range (see "{$a}" help)';
$string['parentcontentdateoutofrange_err'] = 'Provided time is out of the range requested to the choosen item';
$string['parentcontentinvalidtime_err'] = 'Provided data is not a regular time';
$string['pluginname'] = 'Time';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upperlower'] = 'Answer is supposed to be greater-equal than {$a->lowerbound} or lower-equal than {$a->upperbound}';
$string['secondssincemidnight'] = 'Seconds since mid night';
$string['step_help'] = 'Step of the minute drop down menu as it appear in the attemp form';
$string['step'] = 'Step';
$string['strftime1'] = '%H:%M';
$string['strftime2'] = '%I:%M %p';
$string['tenminutes'] = 'ten minutes';
$string['thirtyminutes'] = 'thirty minutes';
$string['twentyminutes'] = 'twenty minutes';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than {$a}';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than {$a}';
$string['uerr_outofexternalrange'] = 'Provided value is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['uerr_timenotset'] = 'Please choose a time or select the "{$a}" checkbox';
$string['uerr_timenotsetrequired'] = 'Time is not correctly defined';
$string['upperbound_help'] = 'The biggest time the user is allowed to enter.<br /><br />Upper and lower bound define a range.<br />If "lower bound" is lower than "upper bound" the user is forced to enter a value falling into the range.<br />If "lower bound" is greater than "upper bound" the user input is forced out from the range. i.e. the user input is supposed to be lower-equal than the lower bound OR grater-equal than the upper bound.';
$string['upperbound'] = 'Upper bound';
$string['userfriendlypluginname'] = 'Time';
$string['restriction_lower'] = 'Answer is supposed to be greater-equal than {$a}';
$string['restriction_upper'] = 'Answer is supposed to be lower-equal than {$a}';
