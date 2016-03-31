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
 * advcheckbox mform element
 *
 * Extends the core mform class for advcheckbox element
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/advcheckbox.php');

// @codingStandardsIgnoreFile

/**
 * advcheckbox mform element
 *
 * Extends the core mform class for advcheckbox element
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_mform_advcheckbox extends MoodleQuickForm_advcheckbox {

    /**
     * Class constructor
     *
     * @param string $elementName (optional) name of the checkbox
     * @param string $elementLabel (optional) checkbox label
     * @param string $text (optional) Text to put after the checkbox
     * @param mixed $attributes (optional) Either a typical HTML attribute string
     *              or an associative array
     * @param mixed $options (optional) Values to pass if checked or not checked
     */
    public function __construct($elementName=null, $elementLabel=null, $text=null, $attributes=null, $options=null) {
        parent::__construct($elementName, $elementLabel, $text, $attributes, $options);
    }

    /**
     * What to display when element is frozen.
     *
     * @return empty string
     */
    public function getFrozenHtml() {
        $output = parent::getFrozenHtml();

        if (isset($this->_attributes['class'])) {
            $pattern = '~disabled="disabled"~';
            $class = $this->_attributes['class'];
            $replacement = 'disabled="disabled" class="'.$class.'"';
            $output = preg_replace($pattern, $replacement, $output);
        }

        return $output;
    }
}
