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
 * editor form element
 *
 * HTML class for a editor type element
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/textarea.php');

// @codingStandardsIgnoreFile

/**
 * editor form element
 *
 * HTML class for a editor type element
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveypromform_textarea_plain extends MoodleQuickForm_textarea {

    /**
     * Constructor.
     *
     * @param string $elementName Optional name of the editor
     * @param string $elementLabel Optional editor label
     * @param array $attributes Optional either a typical HTML attribute string
     *              or an associative array
     * @param array $options set of options to initalize filepicker
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $options);
    }

    /**
     * Returns type of editor element.
     *
     * @return string
     */
    public function getElementTemplateType() {
        return 'default';
    }

    /**
     * Returns HTML for editor form element.
     *
     * My intervention only replaces <div> with <div class="indent-x"> AT THE BEGINNING of $output
     * I use the output of parent::toHtml() to get advantages of future updates to core mform class
     * I search for simple <div> without attributes so that if moodle HQ will ever fix this issue in the core code,
     * my intervention will result in nothing without adding useless or dangerous modifications
     *
     * @return string
     */
    public function toHtml() {
        // The core code is ONLY MISSING the class in the first <div>.
        // I add it with a simple replace.
        $output = parent::toHtml(); // Core code.

        $tabs = $this->_getTabs();
        $pattern = '~^'.$tabs.'<div>~';
        $class = empty($this->_attributes['class']) ? 'indent-0' : $this->_attributes['class'];
        $replacement = $tabs.'<div class="'.$class.'">';
        $output = preg_replace($pattern, $replacement, $output);
        return $output;
    }

    /**
     * What to display when element is frozen.
     *
     * @return html of the frozen element
     */
    public function getFrozenHtml() {
        $value = htmlspecialchars($this->getValue());
        $value = nl2br($value);
        $value = strlen($value) ? $value : '&nbsp;';

        $class = array();
        $class['id'] = $this->getAttribute('id');
        $class['name'] = $this->getAttribute('name');
        if (empty($this->_attributes['class'])) {
            $class['class'] = 'indent-0 ';
        } else {
            $class['class'] = $this->_attributes['class'];
        }
        $class['readonly'] = 'true';

        $output = $this->_getTabs();
        // $output .= html_writer::tag('div', $value, $class);
        $output .= html_writer::start_tag('textarea', $class);
        $output .= $value;
        $output .= html_writer::end_tag('textarea');
        $output .= $this->_getPersistantData();
        return $output;
    }
}
