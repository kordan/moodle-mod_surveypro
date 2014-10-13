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
 * Strings for component 'field_character', language 'en', branch 'MOODLE_23_STABLE'
 *
 * @package    surveypro
 * @subpackage character
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Short text';
$string['userfriendlypluginname'] = 'Text (short)';
$string['defaultvalue_err'] = 'The default item "{$a}" was not found among options';
$string['defaultvalue_help'] = 'This is the value the remote user will find answered by default';
$string['defaultvalue'] = 'Default';
$string['length_help'] = 'The width of the field in characters';
$string['length'] = 'Field width in characters';
$string['maxlength_help'] = 'The maximum number of characters allowed for the answer to this question';
$string['maxlength'] = 'Maximum characters';
$string['minlength_help'] = 'The minimum number of characters allowed for the answer to this question';
$string['minlength'] = 'Minimum characters';
$string['pattern_help'] = 'If the answer is supposed to fit a specific pattern, define it here using <ul><li>"A" for upper case characters;</li><li>"a" for lower case characters;</li><li>"0" for numbers;</li><li>"*" for to include upper case, lower case, numbers or any other character like, for instance: ,_%."$!\' or spaces.</li></ul>';
$string['pattern'] = 'Text pattern';
$string['free'] = 'free pattern';
$string['mail'] = 'email address';
$string['url'] = 'web page URL';
$string['custompattern'] = 'custom';
$string['restrictions_custom'] = 'Text is supposed to match the following pattern: "{$a}"';
$string['restrictions_email'] = 'Email is expected here';
$string['restrictions_url'] = 'URL is expected here';
$string['restrictions_max'] = 'Text is supposed to be shorter-equal than {$a} characters';
$string['restrictions_min'] = 'Text is supposed to be longer-equal than {$a} characters';
$string['restrictions_minmax'] = 'Text length is supposed to range between {$a->minlength} and {$a->maxlength} characters';

$string['uerr_texttoolong'] = 'Text is too long';
$string['uerr_invalidemail'] = 'Text is not a valid email';
$string['uerr_texttooshort'] = 'Text is too short';
$string['uerr_invalidurl'] = 'Text is not a valid URL';
$string['uerr_invalidpattern'] = 'Text does not match the required pattern. Character {$a->char} does not fit in {$a->pattern}';
$string['uerr_invalidpatternlength'] = 'Text does not match pattern length ({$a} characters)';
$string['uerr_badlength'] = 'Text entered has bad length';
$string['uerr_nopatternmatch'] = 'Text does not match the required pattern';

$string['ierr_mingtmax'] = 'Minimum length has be lower than maximum length';
$string['ierr_maxltmin'] = 'Maximum length has be greater than minimum length';
$string['ierr_minexceeds'] = 'Minimum length has to be positive';
$string['ierr_maxexceeds'] = 'Maximum length has to be shorter-equal than 256 characters';
$string['ierr_toolongdefault'] = 'Default has to be shorter-equal than maximum allowed length';
$string['ierr_tooshortdefault'] = 'Default has to be longer-equal than minimum allowed length';
$string['ierr_nopatternmatch'] = 'Default does not match the required pattern';
$string['ierr_toolongpattern'] = 'Pattern has to be shorter-equal than maximum allowed length';
$string['ierr_tooshortpattern'] = 'Pattern has to be longer-equal than minimum allowed length';
$string['ierr_extracharfound'] = '{$a} characters are not allowed. Please, use only "A", "a", "*" and "0"';
$string['ierr_patternisempty'] = 'pattern is missing';
$string['ierr_defaultisnotemail'] = 'Default does not math email pattern';
$string['ierr_defaultisnoturl'] = 'Default does not appear a valid URL';
$string['ierr_defaultbadlength'] = 'Default is not {$a} character long as implicitly declared in the pattern';

$string['parentcontent_isnotemail'] = 'Parentcontent does not math email pattern';
$string['parentcontent_isnoturl'] = 'Parentcontent does not appear a valid URL';
$string['parentcontent_nomatchingpattern'] = 'Parentcontent does not match the required pattern';
