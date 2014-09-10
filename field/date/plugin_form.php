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
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/date/lib.php');

class surveypro_pluginform extends mod_surveypro_itembaseform {

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
        // item::defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $days = array_combine(range(1, 31), range(1, 31));
        $months = array();
        for ($i=1; $i<=12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }
        $years = array_combine(range($startyear, $stopyear), range($startyear, $stopyear));

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_date'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('currentdatedefault', 'surveyprofield_date'), SURVEYPRO_TIMENOWDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'surveypro'), SURVEYPRO_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('likelast', 'surveypro'), SURVEYPRO_LIKELASTDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_date'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_date');

        // ----------------------------------------
        // item::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $mform->addGroup($elementgroup, $fieldname.'_group', null, ' ', false);
        $mform->disabledIf($fieldname.'_group', 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // ----------------------------------------
        // item::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_date'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_date');

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_date'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_date');
        $mform->setDefault($fieldname.'_day', '1');
        $mform->setDefault($fieldname.'_month', '1');
        $mform->setDefault($fieldname.'_year', $startyear);

        // ----------------------------------------
        // item::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname.'_day', '', $days);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_month', '', $months);
        $elementgroup[] = $mform->createElement('select', $fieldname.'_year', '', $years);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_date'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_date');
        $mform->setDefault($fieldname.'_day', '31');
        $mform->setDefault($fieldname.'_month', '12');
        $mform->setDefault($fieldname.'_year', $stopyear);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $lowerbound = $item->item_date_to_unix_time($data['lowerbound_year'], $data['lowerbound_month'], $data['lowerbound_day']);
        $upperbound = $item->item_date_to_unix_time($data['upperbound_year'], $data['upperbound_month'], $data['upperbound_day']);
        if ($lowerbound == $upperbound) {
            $errors['lowerbound_group'] = get_string('lowerequaltoupper', 'surveyprofield_date');
        }
        if ($lowerbound > $upperbound) {
            $errors['lowerbound_group'] = get_string('lowergreaterthanupper', 'surveyprofield_date');
        }

        // constrain default between boundaries
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            $defaultvalue = $item->item_date_to_unix_time($data['defaultvalue_year'], $data['defaultvalue_month'], $data['defaultvalue_day']);

            // internal range
            if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                $errors['defaultvalue_group'] = get_string('outofrangedefault', 'surveyprofield_date');
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