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
require_once($CFG->dirroot.'/mod/surveypro/field/datetime/lib.php');

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

        // ----------------------------------------
        $startyear = $this->_customdata->surveypro->startyear;
        $stopyear = $this->_customdata->surveypro->stopyear;

        // ----------------------------------------
        // item: step
        // ----------------------------------------
        $fieldname = 'step';
        $options = array();
        $options[1] = get_string('oneminute', 'surveyprofield_datetime');
        $options[5] = get_string('fiveminutes', 'surveyprofield_datetime');
        $options[10] = get_string('tenminutes', 'surveyprofield_datetime');
        $options[15] = get_string('fifteenminutes', 'surveyprofield_datetime');
        $options[20] = get_string('twentyminutes', 'surveyprofield_datetime');
        $options[30] = get_string('thirtyminutes', 'surveyprofield_datetime');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_datetime'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_datetime');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $separator = array(' ', ' ', ', ', ':');
        $days = array_combine(range(1, 31), range(1, 31));
        // $months = array_combine(range(0, 11), range(0, 11));
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));
        $hours = array_combine(range(0, 23), range(0, 23));
        $minutes = array_combine(range(0, 59), range(0, 59));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_datetime'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentdatetimedefault', 'surveyprofield_datetime'), SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'mod_surveypro'), SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_TIMENOWDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');

        // ----------------------------------------
        // item: defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, $separator, false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // ----------------------------------------
        // item: downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_datetime'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_datetime');

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
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');
        $mform->setDefault($fieldname.'_year', $startyear);
        $mform->setDefault($fieldname.'_month', '1');
        $mform->setDefault($fieldname.'_day', '1');
        $mform->setDefault($fieldname.'_hour', '0');
        $mform->setDefault($fieldname.'_minute', '0');

        // ----------------------------------------
        // item: upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');
        $mform->setDefault($fieldname.'_year', $stopyear);
        $mform->setDefault($fieldname.'_month', '12');
        $mform->setDefault($fieldname.'_day', '31');
        $mform->setDefault($fieldname.'_hour', '23');
        $mform->setDefault($fieldname.'_minute', '59');

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

        $lowerbound = $item->item_datetime_to_unix_time($data['lowerbound_year'], $data['lowerbound_month'],
                $data['lowerbound_day'], $data['lowerbound_hour'], $data['lowerbound_minute']);
        $upperbound = $item->item_datetime_to_unix_time($data['upperbound_year'], $data['upperbound_month'],
                $data['upperbound_day'], $data['upperbound_hour'], $data['upperbound_minute']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_datetime');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowergreaterthanupper', 'surveyprofield_datetime');
        }

        // Constraint default between boundaries.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_datetime_to_unix_time($data['defaultvalue_year'], $data['defaultvalue_month'],
                    $data['defaultvalue_day'], $data['defaultvalue_hour'], $data['defaultvalue_minute']);

            // internal range
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyprofield_datetime');
            }
        }

        // if (default == noanswer && the field is mandatory) => error
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultvalue_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}
