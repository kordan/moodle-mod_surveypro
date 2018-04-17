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
 * @package   surveyprofield_rate
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/rate/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_rate
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_rate_setupform extends mod_surveypro_itembaseform {

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

        // Item: style.
        $fieldname = 'style';
        $options = array(SURVEYPROFIELD_RATE_USERADIO => get_string('useradio', 'surveyprofield_rate'),
                         SURVEYPROFIELD_RATE_USESELECT => get_string('usemenu', 'surveyprofield_rate')
                   );
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $options);
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_INT);

        $textareaoptions = array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65');

        // Item: options.
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $textareaoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW); // PARAM_RAW and not PARAM_TEXT otherwise '<' is not accepted.

        // Item: rates.
        $fieldname = 'rates';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $textareaoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_TEXT);

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $customdefaultstr = get_string('customdefault', 'surveyprofield_rate');
        $invitedefaultstr = get_string('invitedefault', 'mod_surveypro');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $customdefaultstr, SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $invitedefaultstr, SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $noanswerstr, SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_rate'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_rate');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, '', $textareaoptions);
        $mform->setType($fieldname, PARAM_RAW);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_rate'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_INT);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: differentrates.
        $fieldname = 'differentrates';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_rate'));
        $mform->setDefault($fieldname, '0');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_rate');
        $mform->setType($fieldname, PARAM_TEXT);

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
        // Useless: $item = $this->_customdata['item'];.

        $errors = parent::validation($data, $files);

        // Clean inputs.
        $cleanoptions = surveypro_multilinetext_to_array($data['options']);
        $cleanrates = surveypro_multilinetext_to_array($data['rates']);
        $cleandefaultvalue = isset($data['defaultvalue']) ? surveypro_multilinetext_to_array($data['defaultvalue']) : '';

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

        // Each single label has to be unique.
        $arrayunique = array_unique($labels);
        if (count($labels) != count($arrayunique)) {
            $errors['rates'] = get_string('ierr_labelsduplicated', 'surveyprofield_rate');
        }
        // Each single value has to be unique.
        $arrayunique = array_unique($values);
        if (count($values) != count($arrayunique)) {
            $errors['rates'] = get_string('ierr_valuesduplicated', 'surveyprofield_rate');
        }
        // Each single option has to be unique.
        $arrayunique = array_unique($labels);
        if (count($labels) != count($arrayunique)) {
            $errors['options'] = get_string('ierr_optionsduplicated', 'surveyprofield_rate');
        }

        // If a default is required.
        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            // Defaults count has to be equal to the number of the options.
            if (count($cleandefaultvalue) != count($cleanoptions)) {
                $errors['defaultvalue'] = get_string('ierr_invaliddefaultscount', 'surveyprofield_rate');
            }

            // Values in the default field must all be hold among rates ($labels).
            foreach ($cleandefaultvalue as $default) {
                if (!in_array($default, $labels)) {
                    $errors['defaultvalue'] = get_string('ierr_foreigndefaultvalue', 'surveyprofield_rate', $default);
                    break;
                }
            }
        }

        // Editing teacher can not set "noanswer" as default option if the item is mandatory.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        // If differentrates was requested.
        // Count($cleanrates) HAS TO be >= count($cleanrates).
        if (isset($data['differentrates'])) {
            // If I claim for different rates, I must provide a sufficient number of rates.
            if (count($cleanoptions) > count($cleanrates)) {
                $errors['rates'] = get_string('ierr_notenoughrates', 'surveyprofield_rate');
            }

            if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
                // If I claim for different rates, I have to respect the constraint in the default.
                if (count($cleandefaultvalue) > count(array_unique($cleandefaultvalue))) {
                    $errors['defaultvalue'] = get_string('ierr_defaultsduplicated', 'surveyprofield_rate');
                }
            }
        }

        return $errors;
    }
}