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
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/integer/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    /**
     * definition
     *
     * @return void
     */
    public function definition() {
        // Start with common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $maximuminteger = get_config('surveyprofield_integer', 'maximuminteger');
        $integers = array_combine(range(0, $maximuminteger), range(0, $maximuminteger));

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('customdefault', 'surveyprofield_integer'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $separator = array(' ', ' ');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_integer'), $separator, false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_integer');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $mform->addElement('select', $fieldname, null, $integers);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_integer'), $integers);
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_integer');

        // Item: upperbound.
        $fieldname = 'upperbound';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_integer'), $integers);
        $mform->setDefault($fieldname, "$maximuminteger");
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_integer');

        $this->add_item_buttons();
    }

    /**
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $lowerbound = $data['lowerbound'];
        $upperbound = $data['upperbound'];
        if ($lowerbound == $upperbound) {
            $errors['lowerbound'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_integer');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound'] = get_string('ierr_lowergreaterthanupper', 'surveyprofield_integer');
        }

        // Constrain default between boundaries.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $data['defaultvalue'];

            // Only internal range is allowed for integers.
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue'] = get_string('ierr_outofrangedefault', 'surveyprofield_integer');
            }
        }

        // If (default == noanswer && the field is mandatory) => error.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}