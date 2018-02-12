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
 * Library for surveyprofield_character
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

// Patterns.
define('SURVEYPROFIELD_CHARACTER_FREEPATTERN'  , 'PATTERN_FREE');
define('SURVEYPROFIELD_CHARACTER_EMAILPATTERN' , 'PATTERN_EMAIL');
define('SURVEYPROFIELD_CHARACTER_URLPATTERN'   , 'PATTERN_URL');
define('SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN', 'PATTERN_CUSTOM');
define('SURVEYPROFIELD_CHARACTER_REGEXPATTERN' , 'PATTERN_REGEX');

/**
 * Validate the passed text against the known pattern
 *
 * @param string $pattern
 * @return bool, false if the validation passes, the error message otherwise.
 */
function surveypro_character_validate_pattern_integrity($pattern) {
    $message = false;

    // Pattern can not be empty.
    if (!strlen($pattern)) {
        $message = get_string('ierr_patternisempty', 'surveyprofield_character');
    }
    // Pattern can be done only using 'A', 'a', '*' and '0'.
    if (preg_match_all('~[^Aa\*0]~', $pattern, $matches)) {
        $denied = array_unique($matches[0]);
        $a = '"'.implode('", "', $denied).'"';
        $message = get_string('ierr_extracharfound', 'surveyprofield_character', $a);
    }

    return $message;
}

/**
 * Validate the passed text against the known regex
 *
 * @param string $regex
 * @return bool|string, false if the validation passes, the error message otherwise.
 */
function surveypro_character_validate_regex_integrity($regex) {
    $test = @preg_match($regex, null);

    if ($test === false) {
        $message = get_string('uerr_invalidregex', 'surveyprofield_character', $regex);
    } else {
        $message = false;
    }

    return $message;
}

/**
 * Validate the passed text as an url
 *
 * @param string $url
 * @return bool, True if the string hold a correct url; false otherwise.
 */
function surveypro_character_validate_against_url($url) {
    // Which one is better here?
    // First option: return (filter_var($url, FILTER_VALIDATE_URL) !== false);
    // Second option: $regex = '~^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i';
    // Third option.
    $regex = '~^(http(s?)\:\/\/)?[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)([a-zA-Z0-9\‌​-‌​\.\?\,\'\/\\\+&amp;%\$#_]*)?$~i';

    // Function preg_match() returns 1 if the pattern matches given subject, 0 if it does not, or false if an error occurred.
    return preg_match($regex, $url);
}

/**
 * Validate the passed text against the known pattern
 *
 * @param string $userinput
 * @param string $pattern
 * @return bool, True if the text matches the pattern; false otherwise.
 */
function surveypro_character_validate_against_pattern($userinput, $pattern) {
    // Replace free characters.
    $pos = -1;
    while ($pos = strpos($pattern, '*', $pos + 1)) {
        $text = substr_replace($text, '*', $pos, 1);
    }

    // Build the pattern matching the text provided.
    $regex = array('~[A-Z]~', '~[a-z]~', '~[0-9]~');
    $replacement = array('A', 'a', '0');
    $reconstructed = preg_replace($regex, $replacement, $userinput);

    if (strcmp($reconstructed, $pattern) === 0) {
        $return = true;
    } else {
        $return = false;
    }

    return $return;
}

/**
 * Validate the passed text against the known regex
 *
 * @param string $userinput
 * @param string $regex
 * @return bool, True if the text matches the pattern; false otherwise.
 */
function surveypro_character_validate_against_regex($userinput, $regex) {
    preg_match($regex, $userinput, $matches);

    if ( $matches && (strcmp(reset($matches), $userinput) === 0) ) {
        $return = true;
    } else {
        $return = false;
    }

    return $return;
}
