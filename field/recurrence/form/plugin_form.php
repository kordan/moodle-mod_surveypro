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
require_once($CFG->dirroot.'/mod/surveypro/field/recurrence/lib.php');

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
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;
        $startyear = $this->_customdata->surveypro->startyear;
        $stopyear = $this->_customdata->surveypro->stopyear;

        // ----------------------------------------
        // item: defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $days = array_combine(range(1, 31), range(1, 31));
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_recurrence'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentrecurrencedefault', 'surveyprofield_recurrence'), SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'mod_surveypro'), SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');

        // ----------------------------------------
        // item: defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // ----------------------------------------
        // item: downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_recurrence'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_recurrence');

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // ----------------------------------------
        // item: lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');
        $mform->setDefault($fieldname.'_day', '1');
        $mform->setDefault($fieldname.'_month', '1');

        // ----------------------------------------
        // item: upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');
        $mform->setDefault($fieldname.'_day', '31');
        $mform->setDefault($fieldname.'_month', '12');

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
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $lowerbound = $item->item_recurrence_to_unix_time($data['lowerbound_month'], $data['lowerbound_day']);
        $upperbound = $item->item_recurrence_to_unix_time($data['upperbound_month'], $data['upperbound_day']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyprofield_recurrence');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('lowergreaterthanupper', 'surveyprofield_recurrence');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_recurrence_to_unix_time($data['defaultvalue_month'], $data['defaultvalue_day']);

            if (!$item->item_check_monthday($data['defaultvalue_day'], $data['defaultvalue_month'])) {
                $errors['defaultvalue_group'] = get_string('notvaliddefault', 'surveyprofield_recurrence');
            }
            if (!$item->item_check_monthday($data['lowerbound_day'], $data['lowerbound_month'])) {
                $errors['lowerbound_group'] = get_string('notvalidlowerbound', 'surveyprofield_recurrence');
            }
            if (!$item->item_check_monthday($data['upperbound_day'], $data['upperbound_month'])) {
                $errors['upperbound_group'] = get_string('notvalidupperbound', 'surveyprofield_recurrence');
            }

            // internal range
            if (($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound)) {
                $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyprofield_recurrence');
            }
        }

        // if (default == noanswer && the field is mandatory) => error
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}