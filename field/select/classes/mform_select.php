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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/form/select.php');

class mod_surveypro_mform_select extends MoodleQuickForm_select {

    /**
     * Class constructor
     *
     * @param string $elementName
     * @param string $elementLabel
     * @param array $attributes
     * @param array $options
     */
    public function __construct($elementName=null, $elementLabel=null, $options=null, $attributes=null) {
        parent::__construct($elementName, $elementLabel, $options, $attributes);
    }

    /**
     * Slightly different container template when frozen. Don't want to use a label tag
     * with a for attribute in that case for the element label but instead use a div.
     * Templates are defined in renderer constructor.
     *
     * @return string
     */
    function getElementTemplateType() {
        return 'default';
    }

    /**
     * What to display when element is frozen.
     *
     * @return empty string
     */
    public function getFrozenHtml() {
        $values = array();
        if (is_array($this->_values)) {
            foreach ($this->_values as $key => $val) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                    if ((string)$val == (string)$this->_options[$i]['attr']['value']) {
                        $values[$key] = $this->_options[$i]['text'];
                        break;
                    }
                }
            }
        }

        $attributes = array('disabled' => 'disabled');
        if (isset($this->_attributes['class'])) {
            $attributes['class'] = $this->_attributes['class'];
        }
        if (isset($this->_attributes['multiple'])) {
            $attributes['multiple'] = 'multiple';
            $output = html_writer::start_tag('select', $attributes);
            foreach ($this->_options as $option) {
                if (in_array($option['text'], $values)) {
                    $attributes = array('value' => $option['attr']['value']);
                    $output .= html_writer::tag('option', $option['text'], $attributes);
                }
            }
        } else {
            $value = $values[0];
            $output = html_writer::start_tag('select', $attributes);
            foreach ($this->_options as $option) {
                if ($option['text'] == $value) {
                    $attributes = array('value' => $option['attr']['value']);
                    $output .= html_writer::tag('option', $option['text'], $attributes);
                    break;
                }
            }
        }
        $output .= html_writer::end_tag('select');

        return $output;
    }
}
