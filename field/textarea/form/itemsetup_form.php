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
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/textarea/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_textarea_setupform extends mod_surveypro_itembaseform {

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

        // Item: useeditor.
        $fieldname = 'useeditor';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->setType($fieldname, PARAM_INT);

        // Item: arearows.
        $fieldname = 'arearows';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->setDefault($fieldname, SURVEYPROFIELD_TEXTAREA_DEFAULTROWS);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);

        // Item: areacols.
        $fieldname = 'areacols';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, SURVEYPROFIELD_TEXTAREA_DEFAULTCOLS);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: minlength.
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 0);

        // Item: maxlength.
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->setType($fieldname, PARAM_RAW);

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

        if (strlen($data['maxlength'])) {
            $isinteger = (bool)(strval(intval($data['maxlength'])) == strval($data['maxlength']));
            if (!$isinteger) {
                $errors['maxlength'] = get_string('ierr_maxlengthnotinteger', 'surveyprofield_textarea');
            } else {
                if ($data['maxlength'] <= $data['minlength']) {
                    $errors['maxlength'] = get_string('ierr_maxlengthlowerthanminlength', 'surveyprofield_textarea');
                }
            }
        }

        return $errors;
    }
}