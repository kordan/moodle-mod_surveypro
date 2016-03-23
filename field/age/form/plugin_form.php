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
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/age/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    /*
     * definition
     *
     * @param none
     * @return void
     */
    public function definition() {
        // Start with common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $maximumage = get_config('surveyprofield_age', 'maximumage');

        $years = array_combine(range(0, $maximumage), range(0, $maximumage));
        $months = array_combine(range(0, 11), range(0, 11));

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('customdefault', 'surveyprofield_age'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_age'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_age');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_age'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_age');
        $mform->setDefault($fieldname.'_year', '0');
        $mform->setDefault($fieldname.'_month', '0');
        // $mform->disabledIf($fieldname.'_group', $fieldname.'_select', 'neq', constant($constantname));

        // Item: upperbound.
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_age'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_age');
        $mform->setDefault($fieldname.'_year', $maximumage);
        $mform->setDefault($fieldname.'_month', '11');

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
        // Get _customdata.
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        // "noanswer" default option is not allowed when the item is mandator
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        $lowerbound = $item->item_age_to_unix_time($data['lowerbound_year'], $data['lowerbound_month']);
        $upperbound = $item->item_age_to_unix_time($data['upperbound_year'], $data['upperbound_month']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_age');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowergreaterthanupper', 'surveyprofield_age');
        }

        // Constrain default between boundaries.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_age_to_unix_time($data['defaultvalue_year'], $data['defaultvalue_month']);

            // Internal range.
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyprofield_age');
            }
        }

        return $errors;
    }
}