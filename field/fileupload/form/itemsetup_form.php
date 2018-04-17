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
 * Surveypro pluginform class.
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_fileupload_setupform extends mod_surveypro_itembaseform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        // Start with common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        // Useless: $item = $this->_customdata['item'];.

        // Item: maxfiles.
        $fieldname = 'maxfiles';
        $countrange = range(1, 5);
        $options = array_combine($countrange, $countrange);
        $options[EDITOR_UNLIMITED_FILES] = get_string('unlimited', 'mod_surveypro');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // Item: maxbytes.
        $fieldname = 'maxbytes';
        $options = get_max_upload_sizes();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'), $options);
        $mform->setDefault($fieldname, '1048576');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_INT);

        // Item: filetypes.
        $fieldname = 'filetypes';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_fileupload'));
        $mform->setDefault($fieldname, '*');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_fileupload');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // Useless: $item = $this->_customdata['item'];.

        $errors = parent::validation($data, $files);

        if ($data['filetypes'] == '*') {
            return $errors;
        }

        $filetypes = array_map('trim', explode(',', $data['filetypes']));
        foreach ($filetypes as $filetype) {
            if (!$filetype) {
                $errors['filetypes'] = get_string('ierr_extensionisempty', 'surveyprofield_fileupload');
                break;
            }
            if ($filetype == '*') {
                $errors['filetypes'] = get_string('ierr_staramongextensions', 'surveyprofield_fileupload');
                break;
            } else {
                if ($filetype[0] != '.') {
                    $errors['filetypes'] = get_string('ierr_extensionmissingdot', 'surveyprofield_fileupload');
                    break;
                }
                if (strpos($filetype, '.', 1) !== false) {
                    $errors['filetypes'] = get_string('ierr_extensiononlyonedot', 'surveyprofield_fileupload');
                    break;
                }
                if (preg_match('~[^a-z0-9]~', substr($filetype, 1))) {
                    $errors['filetypes'] = get_string('ierr_dirtyextension', 'surveyprofield_fileupload');
                    break;
                }
            }
        }

        return $errors;
    }
}