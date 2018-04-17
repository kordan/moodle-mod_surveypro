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
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/recurrence/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_recurrence_setupform extends mod_surveypro_itembaseform {

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

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $daysrange = range(1, 31);
        $days = array_combine($daysrange, $daysrange);
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
        }

        $customdefaultstr = get_string('customdefault', 'surveyprofield_recurrence');
        $currentrecurrencedefaultstr = get_string('currentrecurrencedefault', 'surveyprofield_recurrence');
        $invitedefaultstr = get_string('invitedefault', 'mod_surveypro');
        $likelaststr = get_string('likelast', 'mod_surveypro');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $customdefaultstr, SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $currentrecurrencedefaultstr, SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $invitedefaultstr, SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $likelaststr, SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $noanswerstr, SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);
        $mform->setDefault($fieldname.'day', '1');
        $mform->setDefault($fieldname.'month', '1');

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_recurrence'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_recurrence');

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');
        $mform->setDefault($fieldname.'day', '1');
        $mform->setDefault($fieldname.'month', '1');

        // Item: upperbound.
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'month', '', $months);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_recurrence'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_recurrence');
        $mform->setDefault($fieldname.'day', '31');
        $mform->setDefault($fieldname.'month', '12');

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

        if (!mod_surveypro_utility_useritem::date_is_valid($data['lowerboundday'], $data['lowerboundmonth'])) {
            $errors['lowerbound_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
            return $errors;
        }
        if (!mod_surveypro_utility_useritem::date_is_valid($data['upperboundday'], $data['upperboundmonth'])) {
            $errors['upperbound_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
            return $errors;
        }

        $lowerbound = $item->item_recurrence_to_unix_time($data['lowerboundmonth'], $data['lowerboundday']);
        $upperbound = $item->item_recurrence_to_unix_time($data['upperboundmonth'], $data['upperboundday']);

        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_recurrence');
        }

        // Constrain default between boundaries.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            if (!mod_surveypro_utility_useritem::date_is_valid($data['defaultvalueday'], $data['defaultvaluemonth'])) {
                $errors['defaultvalue_group'] = get_string('ierr_invalidinput', 'mod_surveypro');
                return $errors;
            }
            $defaultvalue = $item->item_recurrence_to_unix_time($data['defaultvaluemonth'], $data['defaultvalueday']);

            if ($lowerbound < $upperbound) {
                // Internal range.
                if (($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound)) {
                    $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyprofield_recurrence');
                }
            }

            if ($lowerbound > $upperbound) {
                // External range.
                if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                    $a = get_string('upperbound', 'surveyprofield_recurrence');
                    $errors['defaultvalue_group'] = get_string('ierr_outofexternalrangedefault', 'surveyprofield_recurrence', $a);
                }
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