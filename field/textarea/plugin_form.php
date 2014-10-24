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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/textarea/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

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
        // item: useeditor
        // ----------------------------------------
        $fieldname = 'useeditor';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: arearows
        // ----------------------------------------
        $fieldname = 'arearows';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->setDefault($fieldname, SURVEYPROFIELD_TEXTAREA_DEFAULTROWS);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: areacols
        // ----------------------------------------
        $fieldname = 'areacols';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, SURVEYPROFIELD_TEXTAREA_DEFAULTCOLS);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item: minlength
        // ----------------------------------------
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_INT);
        $mform->setDefault($fieldname, 0);

        // ----------------------------------------
        // item: maxlength
        // ----------------------------------------
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_textarea'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_textarea');
        $mform->setType($fieldname, PARAM_RAW);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);
        if (strlen($data['maxlength'])) {
            $isinteger = (bool)(strval(intval($data['maxlength'])) == strval($data['maxlength']));
            if (!$isinteger) {
                $errors['maxlength'] = get_string('maxlengthnotinteger', 'surveyprofield_textarea');
            } else {
                if ($data['maxlength'] <= $data['minlength']) {
                    if (!$data['maxlength']) {
                        $errors['maxlength'] = get_string('maxlengthlowerthanminlength', 'surveyprofield_textarea');
                    }
                }
            }
        }

        return $errors;
    }
}