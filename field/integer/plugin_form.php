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
require_once($CFG->dirroot.'/mod/surveypro/field/integer/lib.php');

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
        $maximuminteger = get_config('surveyprofield_integer', 'maximuminteger');
        $integers = array_combine(range(0, $maximuminteger), range(0, $maximuminteger));

        // ----------------------------------------
        // item: defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_integer'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'surveypro'), SURVEYPRO_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $separator = array(' ', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_integer'), $separator, false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_integer');

        // ----------------------------------------
        // item: defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('select', $fieldname, null, $integers);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item: lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_integer'), $integers);
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_integer');

        // ----------------------------------------
        // item: upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_integer'), $integers);
        $mform->setDefault($fieldname, "$maximuminteger");
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_integer');

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $lowerbound = $data['lowerbound'];
        $upperbound = $data['upperbound'];
        if ($lowerbound == $upperbound) {
            $errors['lowerbound'] = get_string('lowerequaltoupper', 'surveyprofield_integer');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound'] = get_string('lowergreaterthanupper', 'surveyprofield_integer');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $data['defaultvalue'];

            // only internal range is allowed for integers
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue'] = get_string('outofrangedefault', 'surveyprofield_integer');
            }
        }

        // if (default == noanswer && the field is mandatory) => error
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'surveypro');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'surveypro', $a);
        }

        return $errors;
    }
}