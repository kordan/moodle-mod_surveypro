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
 * radio mform element
 *
 * Extends the core mform class for a radio type element
 *
 * @package   surveyprofield_radiobutton
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

require_once($CFG->libdir.'/form/radio.php');

// @codingStandardsIgnoreFile

/**
 * radio form element
 *
 * HTML class for a radio type element
 *
 * @package   surveyprofield_radiobutton
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveypromform_radiobutton extends \MoodleQuickForm_radio {

    /**
     * Class constructor.
     *
     * @param string $elementName Optional name of the radio element
     * @param string $elementLabel Optional label for radio element
     * @param string $text Optional Text to put after the radio element
     * @param string $value Optional default value
     * @param mixed $attributes Optional Either a typical HTML attribute string
     *              or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $text=null, $value=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $text, $value, $attributes);
    }

    /**
     * Slightly different container template when frozen.
     *
     * @return string
     */
    public function getElementTemplateType() {
        return 'default';
    }

    /**
     * Returns the disabled field. Accessibility: the return "( )" from parent
     * class is not acceptable for screenreader users, and we DO want a label
     *
     * @return html of the frozen element
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
