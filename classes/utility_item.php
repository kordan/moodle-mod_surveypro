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
 * Surveypro user item utility class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;

/**
 * The utility_useritem class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_item {

    /**
     * @var object Course module object
     */
    protected $cm;

    /**
     * @var object Context object
     */
    protected $context;

    /**
     * @var object Surveypro object
     */
    protected $surveypro;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $surveypro
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        $this->context = \context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * Copy the content of multiline textarea to an array line by line
     *
     * @param string $textareacontent
     * @return array
     */
    public function multilinetext_to_array($textareacontent) {
        // Begin with a simple trim to drop each starting and closing empty row and spaces.
        $textareacontent = trim($textareacontent);

        // \r are not welcome.
        $textareacontent = str_replace("\r", '', $textareacontent);

        // Use preg_replace (and not str_replace) because of eventual multiple instances of "\n\n".
        $textareacontent = preg_replace('~\n\n+~', "\n", $textareacontent);

        if (!core_text::strlen($textareacontent)) {
            return array();
        }

        // Build the array.
        $rows = explode("\n", $textareacontent);

        // Trim each its line.
        $rows = array_map('trim', $rows);

        // Trim each part whether exists.
        foreach ($rows as $k => $row) {
            if (preg_match('~^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$~', $row, $match)) {
                $value = $match[1];
                $label = $match[2];
                $rows[$k] = trim($value).SURVEYPRO_VALUELABELSEPARATOR.trim($label);
            }
        }

        return $rows;
    }

    /**
     * Verify that the passed date (in terms of $day, $month and $year) is valid
     *
     * @param int $day
     * @param int $month
     * @param int $year
     * @return boolean true if the date is valid, false otherwise.
     */
    public static function date_is_valid($day, $month, $year=2001) {
        return checkdate($month, $day, $year);
    }

    /**
     * Convert an mform element name to type, plugin, item id and optional info
     *
     * @param string $elementname The string to parse
     * @return array $match
     */
    public static function get_item_parts($elementname) {
        preg_match(self::get_regexp(), $elementname, $match);

        return $match;
    }

    /**
     * Provide the regex to convert an mform element name to type, plugin, item id and optional info
     *
     * @return string $regex
     */
    public static function get_regexp() {
        $regex = '~';
        $regex .= '(?P<prefix>'.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_PLACEHOLDERPREFIX.'|'.SURVEYPRO_DONTSAVEMEPREFIX.')';
        $regex .= '_';
        $regex .= '(?P<type>'.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')';
        $regex .= '_';
        $regex .= '(?P<plugin>[^_]+)';
        $regex .= '_';
        $regex .= '(?P<itemid>\d+)';
        $regex .= '_?';
        $regex .= '(?P<option>[\d\w]+)?';
        $regex .= '~';

        return $regex;
    }
}
