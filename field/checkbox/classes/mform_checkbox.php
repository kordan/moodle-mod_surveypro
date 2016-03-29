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
 * checkbox mform element
 *
 * Extends the core mform class for checkbox element
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/checkbox.php');

/**
 * checkbox mform element
 *
 * Extends the core mform class for checkbox element
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_mform_checkbox extends MoodleQuickForm_checkbox {

    /**
     * Class constructor
     *
     * @param string $elementName
     * @param string $elementLabel
     * @param array $attributes
     * @param array $options
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $options);
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
