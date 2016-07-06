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
 * Strings for component 'surveyprofield_recurrence', language 'en'
 *
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['currentrecurrencedefault'] = 'Current date';
$string['customdefault'] = 'Custom';
$string['defaultoption_help'] = 'This is the recurrence the remote user will find answered by default. The default for this type of question is mandatory. If "Current recurrence" is choosed as default, boundaries are not supposed to apply.';
$string['defaultoption'] = 'Default';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['downloadformat'] = 'Download format';
$string['ierr_invalidrecurrence'] = 'Incorrect value entered';
$string['ierr_lowerequaltoupper'] = 'Lower and upper bounds must be different';
$string['ierr_outofrangedefault'] = 'Default does not fall within the specified range';
$string['ierr_outofexternalrangedefault'] = 'Default does not fall within the specified range (see "{$a}" help)';
$string['inviteday'] = 'Choose a day';
$string['invitemonth'] = 'Choose a month';
$string['inviteyear'] = 'Choose a year';
$string['lowerbound_help'] = 'The lowest recurrence the user is allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['pluginname'] = 'Recurrence';
$string['restriction_lower'] = 'Answer is supposed to be greater-equal than {$a}';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upper'] = 'Answer is supposed be lower-equal than {$a}';
$string['strftime01'] = '%d %B';
$string['strftime02'] = '%d %b';
$string['strftime03'] = '%d/%m';
$string['uerr_incorrectrecurrence'] = 'Provided value does not exist';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['uerr_recurrencenotset'] = 'Please choose a recurrence or select the "{$a}" checkbox';
$string['uerr_recurrencenotsetrequired'] = 'Recurrence is not correctly defined';
$string['upperbound_help'] = 'The biggest recurrence the user is allowed to enter.<br /><br />Maximum and minimum values define a range.<br />If "minimum value" is lower than "maximum value" the user is forced to enter a value falling into the range.<br />If "minimum value" is greater than "maximum value" the user input is forced out from the range. i.e. the user input is supposed to be lower-equal than the minimum value OR grater-equal than the maximum value.';
$string['upperbound'] = 'Upper bound';
$string['userfriendlypluginname'] = 'Recurrence [dd/mm]';
