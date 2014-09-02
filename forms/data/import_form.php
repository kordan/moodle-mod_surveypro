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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/csvlib.class.php');

class surveypro_importform extends moodleform {

    public function definition() {
        $mform = $this->_form;

        // ----------------------------------------
        // submissionimport::settingsheader
        // ----------------------------------------
        $mform->addElement('header', 'settingsheader', get_string('upload'));

        // ----------------------------------------
        // submissionimport::csvfile
        // ----------------------------------------
        // here I use filepicker because I want ONE, and only ONE, file to import
        $fieldname = 'csvfile';
        $mform->addElement('filepicker', $fieldname.'_filepicker', get_string('file'));
        $mform->addRule($fieldname.'_filepicker', null, 'required');

        // ----------------------------------------
        // submissionimport::csvcontent
        // ----------------------------------------
        $fieldname = 'csvsemantic';
        $a = get_string('downloadformat', 'surveypro');
        $options = array();
        $options[SURVEYPRO_LABELS] = get_string('answerlabel', 'surveypro');
        $options[SURVEYPRO_VALUES] = get_string('answervalue', 'surveypro');
        $options[SURVEYPRO_POSITIONS] = get_string('answerposition', 'surveypro');
        $options['itemdriven'] = get_string('itemdrivensemantic', 'surveypro', $a);
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
        $mform->setDefault($fieldname, 'label');

        // ----------------------------------------
        // submissionimport::csvdelimiter
        // ----------------------------------------
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

        // ----------------------------------------
        // submissionimport::encoding
        // ----------------------------------------
        $fieldname = 'encoding';
        $options = core_text::get_encodings();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'tool_uploaduser'), $options);
        $mform->setDefault($fieldname, 'UTF-8');

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('dataimport', 'surveypro'));
    }

//     public function validation($data, $files) {
//         global $USER;
//
//         $mform = $this->_form;
//
//         // $surveypro = $this->_customdata->surveypro;
//         // $importman = $this->_customdata->importman;
//
//         $errors = parent::validation($data, $files);
//
//         //$csvcontent = $this->get_file_content('importfile_filepicker');
// echo '<textarea rows="10" cols="100">'.$csvcontent.'</textarea>';
//         $csvfilename = $this->get_name('importfile_filepicker');
// echo '$csvfilename = '.$csvfilename.'<br />';
//         if (!$importman->validate_csv($csvcontent, $data->encoding, $data->delimiter_name)) {
//             $errors['importfile_filepicker'] = get_string('invalidcsvfile', 'surveypro', $csvfilename);
//             return $errors;
//         }
//
//         return $errors;
//     }
}
