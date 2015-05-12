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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    /*
     * definition
     *
     * @param none
     * @return none
     */
    public function definition() {
        // ----------------------------------------
        // start with common section of the form
        parent::definition();

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        // item: maxfiles
        // ----------------------------------------
        $fieldname = 'maxfiles';
        $options = array_combine(range(1, 5), range(1, 5));
        $options[EDITOR_UNLIMITED_FILES] = get_string('unlimited', 'surveypro');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: maxbytes
        // ----------------------------------------
        $fieldname = 'maxbytes';
        $options = get_max_upload_sizes();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: filetypes
        // ----------------------------------------
        $fieldname = 'filetypes';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'));
        $mform->setDefault($fieldname, '*');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        $filetypes = array_map('trim', explode(',', $data['filetypes']));
        foreach ($filetypes as $filetype) {
            if (!$filetype) {
                $errors['filetypes'] = get_string('extensionisempty', 'surveyprofield_fileupload');
                break;
            }
            if ($filetype != '*') {
                if ($filetype[0] != '.') {
                    $errors['filetypes'] = get_string('extensionmissingdot', 'surveyprofield_fileupload');
                    break;
                }
                $testtype = str_replace('.', '', $filetype, $count);
                if ($count > 1) {
                    $errors['filetypes'] = get_string('extensiononlyonedot', 'surveyprofield_fileupload');
                    break;
                }
            }
        }
        return $errors;
    }
}