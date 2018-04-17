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
 * @package   surveyprofield_datetime
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/datetime/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_datetime
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_datetime_setupform extends mod_surveypro_itembaseform {

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
        $item = $this->_customdata['item'];

        $startyear = $item->surveypro->startyear;
        $stopyear = $item->surveypro->stopyear;

        // Item: step.
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

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $separator = array(' ', ' ', ', ', ':');
        $daysrange = range(1, 31);
        $days = array_combine($daysrange, $daysrange);
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
        }
        $yearsrange = range($startyear, $stopyear);
        $years = array_combine($yearsrange, $yearsrange);
        $hoursrange = range(0, 23);
        $hours = array_combine($hoursrange, $hoursrange);
        $minutesrange = range(0, 59);
        $minutes = array_combine($minutesrange, $minutesrange);

        $customdefaultstr = get_string('customdefault', 'surveyprofield_datetime');
        $currentdatetimedefaultstr = get_string('currentdatetimedefault', 'surveyprofield_datetime');
        $invitedefaultstr = get_string('invitedefault', 'mod_surveypro');
        $likelaststr = get_string('likelast', 'mod_surveypro');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $customdefaultstr, SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $currentdatetimedefaultstr, SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $invitedefaultstr, SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $likelaststr, SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $noanswerstr, SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, $separator, false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);
        $mform->setDefault($fieldname.'day', '1');
        $mform->setDefault($fieldname.'month', '1');
        $mform->setDefault($fieldname.'year', $startyear);
        $mform->setDefault($fieldname.'hour', '0');
        $mform->setDefault($fieldname.'minute', '0');

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_datetime'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_datetime');

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');
        $mform->setDefault($fieldname.'day', '1');
        $mform->setDefault($fieldname.'month', '1');
        $mform->setDefault($fieldname.'year', $startyear);
        $mform->setDefault($fieldname.'hour', '0');
        $mform->setDefault($fieldname.'minute', '0');

        // Item: upperbound.
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'year', '', $years);
        $elementgroup[] = $mform->createElement('select', $fieldname.'hour', '', $hours);
        $elementgroup[] = $mform->createElement('select', $fieldname.'minute', '', $minutes);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_datetime'), $separator, false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_datetime');
        $mform->setDefault($fieldname.'day', '31');
        $mform->setDefault($fieldname.'month', '12');
        $mform->setDefault($fieldname.'year', $stopyear);
        $mform->setDefault($fieldname.'hour', '23');
        $mform->setDefault($fieldname.'minute', '59');

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
        $item = $this->_customdata['item'];

        $errors = parent::validation($data, $files);

        $lowerboundday = $data['lowerboundday'];
        $lowerboundmonth = $data['lowerboundmonth'];
        $lowerboundyear = $data['lowerboundyear'];
        if (!mod_surveypro_utility_useritem::date_is_valid($lowerboundday, $lowerboundmonth, $lowerboundyear)) {
            $errors['lowerbound_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
            return $errors;
        }
        $upperboundday = $data['upperboundday'];
        $upperboundmonth = $data['upperboundmonth'];
        $upperboundyear = $data['upperboundyear'];
        if (!mod_surveypro_utility_useritem::date_is_valid($upperboundday, $upperboundmonth, $upperboundyear)) {
            $errors['upperbound_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
            return $errors;
        }
        $lowerboundhour = $data['lowerboundhour'];
        $lowerboundminute = $data['lowerboundminute'];
        $lowerbound = $item->item_datetime_to_unix_time($lowerboundyear, $lowerboundmonth, $lowerboundday,
                                                        $lowerboundhour, $lowerboundminute);
        $upperboundhour = $data['upperboundhour'];
        $upperboundminute = $data['upperboundminute'];
        $upperbound = $item->item_datetime_to_unix_time($upperboundyear, $upperboundmonth, $upperboundday,
                                                        $upperboundhour, $upperboundminute);

        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_datetime');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowergreaterthanupper', 'surveyprofield_datetime');
        }

        // Constraint default between boundaries.
        $defaultvalueday = $data['defaultvalueday'];
        $defaultvaluemonth = $data['defaultvaluemonth'];
        $defaultvalueyear = $data['defaultvalueyear'];
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            if (!mod_surveypro_utility_useritem::date_is_valid($defaultvalueday, $defaultvaluemonth, $defaultvalueyear)) {
                $errors['defaultvalue_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
                return $errors;
            }
            $defaultvaluehour = $data['defaultvaluehour'];
            $defaultvalueminute = $data['defaultvalueminute'];
            $defaultvalue = $item->item_datetime_to_unix_time($defaultvalueyear, $defaultvaluemonth, $defaultvalueday,
                                                              $defaultvaluehour, $defaultvalueminute);

            // Internal range.
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyprofield_datetime');
            }
        }

        // Editing teacher can not set "noanswer" as default option if the item is mandatory.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}
