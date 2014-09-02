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
 * Strings for component 'field_shortdate', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    surveypro
 * @subpackage shortdate
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['currentshortdatedefault'] = 'Current short date';
$string['customdefault'] = 'Custom';
$string['defaultoption_help'] = 'This is the short date the remote user will find answered by default. The default for this type of question is mandatory. If "Current short date" is choosed as default, boundaries are not supposed to apply.';
$string['defaultoption'] = 'Default';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['downloadformat_help'] = 'Choose the format of the answer as it appear once user attempts are downloaded';
$string['downloadformat'] = 'Download format';
$string['invitationmonth'] = 'Choose a month';
$string['invitationyear'] = 'Choose a year';
$string['lowerbound_help'] = 'The lowest date the user is allowed to enter';
$string['lowerbound'] = 'Lower bound';
$string['lowerequaltoupper'] = 'Lower and upper bounds need to be different';
$string['lowergreaterthanupper'] = 'Lower bound must be lower than upper bound';
$string['outofrangedefault'] = 'Default does not fall within the specified range';
$string['parentcontentdateoutofrange_err'] = 'Provided short date is out of the range requested to the choosen item';
$string['pluginname'] = 'Short date';
$string['restriction_lower'] = 'Answer is supposed to be greater than {$a}';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upper'] = 'Answer is supposed to be lower-equal than {$a}';
$string['strftime01'] = '%B %Y';
$string['strftime02'] = '%B \'%y';
$string['strftime03'] = '%b %Y';
$string['strftime04'] = '%b \'%y';
$string['strftime05'] = '%m/%Y';
$string['strftime06'] = '%m/%y';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than maximum allowed';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than minimum allowed';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['uerr_shortdatenotset'] = 'Please choose a short date or select "{$a}" checkbox';
$string['uerr_shortdatenotsetrequired'] = 'Short date is not correctly defined';
$string['upperbound_help'] = 'The biggest date the user is allowed to enter';
$string['upperbound'] = 'Upper bound';
$string['userfriendlypluginname'] = 'Date (short) [mm/yyyy]';
