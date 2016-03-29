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

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/csvlib.class.php');

class mod_surveypro_importform extends moodleform {

    /**
     * definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Submissionimport: settingsheader.
        $mform->addElement('header', 'settingsheader', get_string('upload'));

        // Submissionimport: csvfile.
        // Here I use filepicker because I want ONE, and only ONE, file to import.
        $fieldname = 'csvfile';
        $mform->addElement('filepicker', $fieldname.'_filepicker', get_string('file'));
        $mform->addRule($fieldname.'_filepicker', null, 'required');

        // Submissionimport: csvcontent.
        $fieldname = 'csvsemantic';
        $a = get_string('downloadformat', 'mod_surveypro');
        $options = array();
        $options[SURVEYPRO_LABELS] = get_string('answerlabel', 'mod_surveypro');
        $options[SURVEYPRO_VALUES] = get_string('answervalue', 'mod_surveypro');
        $options[SURVEYPRO_POSITIONS] = get_string('answerposition', 'mod_surveypro');
        $options[SURVEYPRO_ITEMDRIVEN] = get_string('itemdrivensemantic', 'mod_surveypro', $a);
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
        $mform->setDefault($fieldname, 'label');

        // Submissionimport: csvdelimiter.
        $fieldname = 'csvdelimiter';
        $options = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'tool_uploaduser'), $options);
        if (array_key_exists('cfg', $options)) {
            $mform->setDefault($fieldname, 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault($fieldname, 'semicolon');
        } else {
            $mform->setDefault($fieldname, 'comma');
        }

        // Submissionimport: encoding.
        $fieldname = 'encoding';
        $options = core_text::get_encodings();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'tool_uploaduser'), $options);
        $mform->setDefault($fieldname, 'UTF-8');

        $this->add_action_buttons(false, get_string('dataimport', 'mod_surveypro'));
    }
}
