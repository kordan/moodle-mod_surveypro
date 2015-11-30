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
require_once($CFG->dirroot.'/mod/surveypro/field/time/lib.php');

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
        $hoptions = array();
        for ($i = 0; $i <= 23; $i++) {
            $hoptions[$i] = sprintf("%02d", $i);
        }
        $moptions = array();
        for ($i = 0; $i <= 59; $i++) {
            $moptions[$i] = sprintf("%02d", $i);
        }

        // ----------------------------------------
        // item: step
        // ----------------------------------------
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

        // ----------------------------------------
        // item: defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_time'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currenttimedefault', 'surveyprofield_time'), SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitedefault', 'mod_surveypro'), SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'mod_surveypro'), SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_time'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_TIMENOWDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_time');

        // ----------------------------------------
        // item: defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // ----------------------------------------
        // item: downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_time'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_time');

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
        $elementgroup[] = $mform->createElement('select', $fieldname.'_hour', '', $hoptions);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_minute', '', $moptions);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_time'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_time');
        $mform->setDefault($fieldname.'_hour', '0');
        $mform->setDefault($fieldname.'_minute', '0');

        // ----------------------------------------
        // item: upperbound
        // ----------------------------------------
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

        $lowerbound = $item->item_time_to_unix_time($data['lowerbound_hour'], $data['lowerbound_minute']);
        $upperbound = $item->item_time_to_unix_time($data['upperbound_hour'], $data['upperbound_minute']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyprofield_time');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_time_to_unix_time($data['defaultvalue_hour'], $data['defaultvalue_minute']);

            if ($lowerbound == $upperbound) {
                $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyprofield_time');
            }

            if ($lowerbound < $upperbound) {
                // internal range
                if (($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound)) {
                    $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyprofield_time');
                }
            }

            if ($lowerbound > $upperbound) {
                // external range
                if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                    $a = get_string('upperbound', 'surveyprofield_time');
                    $errors['defaultvalue_group'] = get_string('outofexternalrangedefault', 'surveyprofield_time', $a);
                }
            }
        }

        // if (default == noanswer) but item is required => error
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}