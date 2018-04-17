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
 * @package   surveyprofield_numeric
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/numeric/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_numeric
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_numeric_setupform extends mod_surveypro_itembaseform {

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
        // Useless: $item = $this->_customdata['item'];.

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_TEXT); // Maybe I use ',' as decimal separator so it is not a INT and not a FLOAT.

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: signed.
        $fieldname = 'signed';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_INT);

        // Item: decimals.
        $fieldname = 'decimals';
        $decimalsrange = range(0, 8);
        $options = array_combine($decimalsrange, $decimalsrange);
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_numeric'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');

        // Item: lowerbound.
        $fieldname = 'lowerbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

        // Item: upperbound.
        $fieldname = 'upperbound';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_numeric'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_numeric');
        $mform->setType($fieldname, PARAM_RAW);

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

        $draftnumber = $data['lowerbound'];
        // Get lowerbound.
        if (strlen($draftnumber)) {
            if (!$lowerbound = $item->item_get_correct_number($draftnumber)) {
                $errors['lowerbound'] = get_string('ierr_notanumber', 'surveyprofield_numeric');
                return $errors;
            }
        }

        $draftnumber = $data['upperbound'];
        // Get upperbound.
        if (strlen($draftnumber)) {
            if (!$upperbound = $item->item_get_correct_number($draftnumber)) {
                $errors['upperbound'] = get_string('ierr_notanumber', 'surveyprofield_numeric');
                return $errors;
            }
        }

        if (isset($lowerbound) && isset($upperbound)) {
            if ($lowerbound == $upperbound) {
                $errors['lowerbound'] = get_string('ierr_lowerequaltoupper', 'surveyprofield_numeric');
            }
            if ($lowerbound > $upperbound) {
                $errors['lowerbound'] = get_string('ierr_lowergreaterthanupper', 'surveyprofield_numeric');
            }
        }

        if (!isset($data['signed'])) {
            if (isset($lowerbound) && ($lowerbound < 0)) {
                $errors['lowerbound'] = get_string('ierr_lowernegative', 'surveyprofield_numeric');
            }
            if (isset($upperbound) && ($upperbound < 0)) {
                $errors['upperbound'] = get_string('ierr_uppernegative', 'surveyprofield_numeric');
            }
        }

        $draftnumber = $data['defaultvalue'];
        // Get defaultvalue.
        if (strlen($draftnumber)) {
            if (!$defaultvalue = $item->item_get_correct_number($draftnumber)) {
                $errors['defaultvalue'] = get_string('ierr_notanumber', 'surveyprofield_numeric');
            } else {
                // Constrain default between boundaries.
                // If it is < 0 but has been defined as unsigned, shouts.
                if ((!isset($data['signed'])) && ($defaultvalue < 0)) {
                    $errors['defaultvalue'] = get_string('ierr_defaultsignnotallowed', 'surveyprofield_numeric');
                }

                $isinteger = (bool)(strval(intval($defaultvalue)) == strval($defaultvalue));
                // If it has decimal but has been defined as integer, shouts.
                if ( ($data['decimals'] == 0) && (!$isinteger) ) {
                    $errors['defaultvalue'] = get_string('ierr_default_notinteger', 'surveyprofield_numeric');
                }

                if (isset($lowerbound) && isset($upperbound)) {
                    // Internal range.
                    if ( ($defaultvalue < $lowerbound) || ($defaultvalue > $upperbound) ) {
                        $errors['defaultvalue'] = get_string('ierr_outofrangedefault', 'surveyprofield_numeric');
                    }
                } else {
                    if (isset($lowerbound)) {
                        // If defaultvalue is < $this->lowerbound, shouts.
                        if ($defaultvalue < $lowerbound) {
                            $errors['defaultvalue'] = get_string('ierr_default_outofrange', 'surveyprofield_numeric');
                        }
                    }

                    if (isset($upperbound)) {
                        // If defaultvalue is > $this->upperbound, shouts.
                        if ($defaultvalue > $upperbound) {
                            $errors['defaultvalue'] = get_string('ierr_default_outofrange', 'surveyprofield_numeric');
                        }
                    }
                }
            }
        }

        return $errors;
    }
}