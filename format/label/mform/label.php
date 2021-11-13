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
 * static form element
 *
 * HTML class for a static type element
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/static.php');

// @codingStandardsIgnoreFile

/**
 * static form element
 *
 * HTML class for a static type element
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveypromform_label extends MoodleQuickForm_static {

    /**
     * Class constructor.
     *
     * @param string $elementName Select name attribute
     * @param mixed $elementLabel Label(s) for the select
     * @param mixed $options Data to be used to populate options
     * @param mixed $attributes Either a typical HTML attribute string
     *              or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $options);
        $this->_options['class'] = !isset($attributes['class']) ? 'indent-0' : $attributes['class'];
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
     * @return string
     */
    public function toHtml() {
        $output = parent::toHtml(); // Core code.

        // Even if the simpler way to pass the class is:
        // $output = \html_writer::tag('div', $output, $this->_options);
        // I create the array from scratch in order to
        // drop any other potentially dangerous element of the original $this->_options array.
        $class = array('class' => $this->_options['class']);
        $output = \html_writer::tag('div', $output, $class);

        return $output;
    }
}
