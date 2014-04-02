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
require_once($CFG->dirroot.'/mod/surveypro/field/rate/lib.php');

class surveypro_pluginform extends mod_surveypro_itembaseform {

    public function definition() {
        // ----------------------------------------
        // start with common section of the form
        parent::definition();

        // ----------------------------------------
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // item::style
        // ----------------------------------------
        $fieldname = 'style';
        $options = array(SURVEYPROFIELD_RATE_USERADIO => get_string('useradio', 'surveyprofield_rate'),
                         SURVEYPROFIELD_RATE_USESELECT => get_string('usemenu', 'surveyprofield_rate')
                   );
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $options);
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW); // PARAM_RAW and not PARAM_TEXT otherwise '<' is not accepted

        // ----------------------------------------
        // item::rates
        // ----------------------------------------
        $fieldname = 'rates';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_rate'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // item::defaultoption
        // ----------------------------------------
        $fieldname = 'defaultoption';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('customdefault', 'surveyprofield_rate'), SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('invitationdefault', 'surveypro'), SURVEYPRO_INVITATIONDEFAULT);
        $elementgroup[] = $mform->createElement('radio', 'defaultoption', '', get_string('noanswer', 'surveypro'), SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_rate'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITATIONDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_rate');

        // ----------------------------------------
        // item::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, '', array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->setType($fieldname, PARAM_RAW);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // ----------------------------------------
        // item::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array(SURVEYPRO_ITEMSRETURNSVALUES => get_string('returnvalues', 'surveyprofield_rate'),
                         SURVEYPRO_ITEMRETURNSLABELS => get_string('returnlabels', 'surveyprofield_rate'),
                         SURVEYPRO_ITEMRETURNSPOSITION => get_string('returnposition', 'surveyprofield_rate'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_INT);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item::differentrates
        // ----------------------------------------
        $fieldname = 'differentrates';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_rate'));
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // clean inputs
        $cleanoptions = surveypro_textarea_to_array($data['options']);
        $cleanrates = surveypro_textarea_to_array($data['rates']);
        $cleandefaultvalue = isset($data['defaultvalue']) ? surveypro_textarea_to_array($data['defaultvalue']) : '';

        // if a default is required
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            // il numero dei default deve essere pari al numero delle opzioni
            if (count($cleandefaultvalue) != count($cleanoptions)) {
                $errors['defaultvalue_group'] = get_string('defaults_wrongdefaultsnumber', 'surveyprofield_rate');
            }

            $values = array();
            $labels = array();
            foreach ($cleanrates as $rate) {
                if (strpos($rate, SURVEYPRO_VALUELABELSEPARATOR) === false) {
                    $values[] = $rate;
                    $labels[] = $rate;
                } else {
                    $pair = explode(SURVEYPRO_VALUELABELSEPARATOR, $rate);
                    $values[] = $pair[0];
                    $labels[] = $pair[1];
                }
            }

            // values in the default field must all be hold among rates ($labels)
            foreach ($cleandefaultvalue as $default) {
                if (!in_array($default, $labels)) {
                    $errors['defaultvalue_group'] = get_string('default_notamongrates', 'surveyprofield_rate', $default);
                    break;
                }
            }
        }

        // if (default == noanswer) but item is required => error
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'surveypro');
            $errors['defaultvalue_group'] = get_string('notalloweddefault', 'surveypro', $a);
        }

        // if differentrates was requested
        // count($cleanrates) HAS TO be >= count($cleanrates)
        if (isset($data['differentrates'])) {
            // if I claim for different rates, I must provide a sufficient number of rates
            if (count($cleanoptions) > count($cleanrates)) {
                $errors['rates'] = get_string('notenoughrares', 'surveyprofield_rate');
            }

            if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
                // if I claim for different rates, I have to respect the constraint in the default
                if (count($cleandefaultvalue) > count(array_unique($cleandefaultvalue))) {
                    $errors['defaultvalue_group'] = get_string('deafultsnotunique', 'surveyprofield_rate');
                }
            }
        }

        return $errors;
    }
}