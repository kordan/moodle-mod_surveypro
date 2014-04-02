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

/*
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
require_once($CFG->dirroot.'/mod/surveypro/field/numeric/lib.php');

class surveypro_pluginform extends mod_surveypro_itembaseform {

    public function definition() {
        // ----------------------------------------
        // start with common section of the form
        parent::definition();

        // ----------------------------------------
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // item::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_TEXT); // maybe I use ',' as decimal separator so it is not a INT and not a FLOAT

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item::signed
        // ----------------------------------------
        $fieldname = 'signed';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item::decimals
        // ----------------------------------------
        $fieldname = 'decimals';
        $options = array_combine(range(0, 8), range(0, 8));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_numeric'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');

        // ----------------------------------------
        // item::lowerbound
        // ----------------------------------------
        $fieldname = 'lowerbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

        // ----------------------------------------
        // item::upperbound
        // ----------------------------------------
        $fieldname = 'upperbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $draftnumber = $data['lowerbound'];
        // get lowerbound
        if (strlen($draftnumber)) {
            $matches = $item->item_atomize_number($draftnumber);
            if (empty($matches)) {
                $errors['lowerbound'] = get_string('lowerbound_notanumber', 'surveyprofield_numeric');
                return $errors;
            } else {
                $lowerbound = unformat_float($draftnumber, true);
            }
        }

        $draftnumber = $data['upperbound'];
        // get upperbound
        if (strlen($draftnumber)) {
            $matches = $item->item_atomize_number($draftnumber);
            if (empty($matches)) {
                $errors['upperbound'] = get_string('upperbound_notanumber', 'surveyprofield_numeric');
                return $errors;
            } else {
                $upperbound = unformat_float($draftnumber, true);
            }
        }

        if (isset($lowerbound) && isset($upperbound)) {
            if ($lowerbound == $upperbound) {
                $errors['lowerbound'] = get_string('lowerequaltoupper', 'surveyprofield_numeric');
            }
            if ($lowerbound > $upperbound) {
                $errors['lowerbound'] = get_string('lowergreaterthanupper', 'surveyprofield_numeric');
            }
        }

        if (!isset($data['signed'])) {
            if (isset($lowerbound) && ($lowerbound < 0)) {
                $errors['lowerbound'] = get_string('lowernegative', 'surveyprofield_numeric');
            }

            if (isset($upperbound) && ($upperbound < 0)) {
                $errors['upperbound'] = get_string('uppernegative', 'surveyprofield_numeric');
            }
        }

        $draftnumber = $data['defaultvalue'];
        // get defaultvalue
        if (strlen($draftnumber)) {
            $matches = $item->item_atomize_number($draftnumber);
            if (empty($matches)) {
                $errors['defaultvalue'] = get_string('default_notanumber', 'surveyprofield_numeric');
            } else {
                $defaultvalue = unformat_float($draftnumber, true);

                // constrain default between boundaries
                // if it is < 0 but has been defined as unsigned, shouts
                if ((!isset($data['signed'])) && ($defaultvalue < 0)) {
                    $errors['defaultvalue'] = get_string('defaultsignnotallowed', 'surveyprofield_numeric');
                }

                $isinteger = (bool)(strval(intval($defaultvalue)) == strval($defaultvalue));
                // if it has decimal but has been defined as integer, shouts
                if ( ($data['decimals'] == 0) && (!$isinteger) ) {
                    $errors['defaultvalue'] = get_string('default_notinteger', 'surveyprofield_numeric');
                }

                if (isset($lowerbound) && isset($upperbound)) {
                    if ($lowerbound < $upperbound) {
                        // internal range
                        if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                            $errors['defaultvalue'] = get_string('outofrangedefault', 'surveyprofield_numeric');
                        }
                    }

                    if ($lowerbound > $upperbound) {
                        // external range
                        if (($defaultvalue > $upperbound) && ($defaultvalue < $lowerbound)) {
                            $a = get_string('upperbound', 'surveyprofield_numeric');
                            $errors['defaultvalue'] = get_string('outofexternalrangedefault', 'surveyprofield_numeric', $a);
                        }
                    }
                } else {
                    if (isset($lowerbound)) {
                        // if defaultvalue is < $this->lowerbound, shouts
                        if ($defaultvalue < $lowerbound) {
                            $errors['defaultvalue'] = get_string('default_outofrange', 'surveyprofield_numeric');
                        }
                    }

                    if (isset($upperbound)) {
                        // if defaultvalue is > $this->upperbound, shouts
                        if ($defaultvalue > $upperbound) {
                            $errors['defaultvalue'] = get_string('default_outofrange', 'surveyprofield_numeric');
                        }
                    }
                }
            }
        }

        return $errors;
    }
}