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
 * Strings for component 'field_numeric', language 'en', branch 'MOODLE_31_STABLE'
 *
 * @package    surveypro
 * @subpackage numeric
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allowed'] = 'allowed';
$string['decimalautofix'] = 'exceeding or missing decimals will be dropped out or filled with zeroes';
$string['decimals_help'] = 'The number of decimals places of the request number';
$string['decimals'] = 'Decimal positions';
$string['declaredecimalseparator'] = 'decimal separator is supposed to be \'{$a}\'';
$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default. Blank to leave default unassigned.';
$string['defaultvalue'] = 'Default';
$string['ierr_default_notinteger'] = 'Default is not an integer';
$string['ierr_default_outofrange'] = 'Default does not fall within the specified range';
$string['ierr_defaultsignnotallowed'] = 'Default is supposed to be unsigned';
$string['ierr_lowerequaltoupper'] = 'Lower and upper bounds need to be different';
$string['ierr_lowergreaterthanupper'] = 'Lower bound must be lower than upper bound';
$string['ierr_lowernegative'] = 'Lower bound is supposed to be unsigned';
$string['ierr_notanumber'] = 'This is not a number';
$string['ierr_outofexternalrangedefault'] = 'Default does not fall within the specified range (see "{$a}" help)';
$string['ierr_outofrangedefault'] = 'Default does not fall within the specified range';
$string['ierr_uppernegative'] = 'Upper bound is supposed to be unsigned';
$string['lowerbound_help'] = 'The minimum allowed value. Blank to leave minimum unassigned.';
$string['lowerbound'] = 'Minimum value';
$string['number'] = 'Number ';
$string['pluginname'] = 'Numeric';
$string['restriction_hasdecimals'] = 'has {$a} decimal positions required';
$string['restriction_hassign'] = 'can be negative';
$string['restriction_isinteger'] = 'is supposed to be an integer';
$string['restriction_lower'] = 'Answer is supposed  be greater-equal than {$a}';
$string['restriction_lowerupper'] = 'Answer is supposed to fit between {$a->lowerbound} and {$a->upperbound}';
$string['restriction_upper'] = 'Answer is supposed be lower-equal than {$a}';
$string['restriction_upperlower'] = 'is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['signed_help'] = 'Is the expected number supposed to be signed?';
$string['signed'] = 'Signed value';
$string['uerr_greaterthanmaximum'] = 'Provided value is greater than maximum allowed';
$string['uerr_lowerthanminimum'] = 'Provided value is lower than minimum allowed';
$string['uerr_negative'] = 'Entered value is unsigned';
$string['uerr_notanumber'] = 'Entered value is not a number';
$string['uerr_notinteger'] = 'Entered value is not an integer';
$string['uerr_outofexternalrange'] = 'Provided value is supposed to be lower-equal than {$a->lowerbound} or greater-equal than {$a->upperbound}';
$string['uerr_outofinternalrange'] = 'Provided value does not fall within the specified range';
$string['upperbound_help'] = 'The biggest value the user is allowed to enter.<br /><br />Maximum and minimum values define a range.<br />If "minimum value" is lower than "maximum value" the user is forced to enter a value falling into the range.<br />If "minimum value" is greater than "maximum value" the user input is forced out from the range. i.e. the user input is supposed to be lower-equal than the minimum value OR grater-equal than the maximum value.';
$string['upperbound'] = 'Maximum value';
$string['userfriendlypluginname'] = 'Numeric';
