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
 * @package   surveyprofield_time
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/time/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_time
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_itemsetupform extends mod_surveypro_itembaseform {

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
        $item = $this->_customdata->item;
        // Useless: $surveypro = $this->_customdata->surveypro;.

        $hoptions = array();
        for ($i = 0; $i <= 23; $i++) {
            $hoptions[$i] = sprintf("%02d", $i);
        }
        $moptions = array();
        for ($i = 0; $i <= 59; $i++) {
            $moptions[$i] = sprintf("%02d", $i);
        }

        // Item: step.
        $fieldname = 'step';
        $options = array();
        $options[1] = get_string('oneminute', 'surveyprofield_time');
        $options[5] = get_string('fiveminutes', 'surveyprofield_time');
        $options[10] = get_string('tenminutes', 'surveyprofield_time');
        $options[15] = get_string('fifteenminutes', 'surveyprofield_time');
        $options[20] = get_string('twentyminutes', 'surveyprofield_time');
        $options[30] = get_string('thirtyminutes', 'surveyprofield_time');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_time'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_time');
        $mform->setType($fieldname, PARAM_INT);

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('customdefault', 'surveyprofield_time'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('currenttimedefault', 'surveyprofield_time'), SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('likelast', 'mod_surveypro'), SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_time'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_time');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_time'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_time');

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_time'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_time');
        $mform->setDefault($fieldname.'_hour', '0');
        $mform->setDefault($fieldname.'_minute', '0');

        // Item: upperbound.
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_time'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_time');
        $mform->setDefault($fieldname.'_hour', '23');
        $mform->setDefault($fieldname.'_minute', '59');

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
        $item = $this->_customdata->item;
        // Useless: $surveypro = $this->_customdata->surveypro;.

        $errors = parent::validation($data, $files);

        $lowerbound = $item->item_time_to_unix_time($data['lowerbound_hour'], $data['lowerbound_minute']);
        $upperbound = $item->item_time_to_unix_time($data['upperbound_hour'], $data['upperbound_minute']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_time');
        }

        // Constrain default between boundaries.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_time_to_unix_time($data['defaultvalue_hour'], $data['defaultvalue_minute']);

            if ($lowerbound < $upperbound) {
                // Internal range.
                if (($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound)) {
                    $errors['defaultvalue_group'] = get_string('ierr_outofrangedefault', 'surveyprofield_time');
                }
            }

            if ($lowerbound > $upperbound) {
                // External range.
                if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                    $a = get_string('upperbound', 'surveyprofield_time');
                    $errors['defaultvalue_group'] = get_string('ierr_outofexternalrangedefault', 'surveyprofield_time', $a);
                }
            }
        }

        // It under the terms of the GNU General Public License as published by.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}