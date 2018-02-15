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
 * @package   surveyprofield_radiobutton
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/radiobutton/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_radiobutton
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_radiobutton_setupform extends mod_surveypro_itembaseform {

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

        $textareaoptions = array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65');

        // Item: options.
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_radiobutton'), $textareaoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_radiobutton');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW); // PARAM_RAW and not PARAM_TEXT otherwise '<' is not accepted.

        // Item: labelother.
        $fieldname = 'labelother';
        $attributes = array('maxlength' => '64', 'size' => '50');
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_radiobutton'), $attributes);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_radiobutton');
        $mform->setType($fieldname, PARAM_TEXT);

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $customdefaultstr = get_string('customdefault', 'surveyprofield_radiobutton');
        $invitedefaultstr = get_string('invitedefault', 'mod_surveypro');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $customdefaultstr, SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $invitedefaultstr, SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $noanswerstr, SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_radiobutton'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_radiobutton');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $elementgroup = array();
        $mform->addElement('text', $fieldname, '');
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);
        $mform->setType($fieldname, PARAM_RAW);

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->item_get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_radiobutton'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_radiobutton');
        $mform->setType($fieldname, PARAM_INT);

        // Item: adjustment.
        $fieldname = 'adjustment';
        $options = array();
        $options[SURVEYPRO_HORIZONTAL] = get_string('horizontal', 'surveyprofield_radiobutton');
        $options[SURVEYPRO_VERTICAL] = get_string('vertical', 'surveyprofield_radiobutton');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_radiobutton'), $options);
        $mform->setDefault($fieldname, SURVEYPRO_VERTICAL);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_radiobutton');
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
        // Useless: $item = $this->_customdata->item;.
        // Useless: $surveypro = $this->_customdata->surveypro;.

        $errors = parent::validation($data, $files);

        // Clean inputs.
        // First of all get the value from the field.
        $cleanoptions = surveypro_multilinetext_to_array($data['options']);
        $cleanlabelother = trim($data['labelother']);
        $cleandefaultvalue = isset($data['defaultvalue']) ? trim($data['defaultvalue']) : '';

        // Build $value and $label arrays starting from $cleanoptions and $cleanlabelother.
        $values = array();
        $labels = array();

        foreach ($cleanoptions as $option) {
            if (strpos($option, SURVEYPRO_VALUELABELSEPARATOR) === false) {
                $values[] = trim($option);
                $labels[] = trim($option);
            } else {
                $pair = explode(SURVEYPRO_VALUELABELSEPARATOR, $option);
                $values[] = $pair[0];
                $labels[] = $pair[1];
            }
        }
        if (!empty($cleanlabelother)) {
            if (strpos($cleanlabelother, SURVEYPRO_OTHERSEPARATOR) === false) {
                $values[] = '';
                $labels[] = $cleanlabelother;
            } else {
                $pair = explode(SURVEYPRO_OTHERSEPARATOR, $cleanlabelother);
                $values[] = $pair[1];
                $labels[] = $pair[0];
            }
        }

        // First check.
        // Each single value has to be unique.
        $arrayunique = array_unique($values);
        if (count($values) != count($arrayunique)) {
            $errors['options'] = get_string('ierr_valuesduplicated', 'surveyprofield_radiobutton');
        }
        // Each single label has to be unique.
        $arrayunique = array_unique($labels);
        if (count($labels) != count($arrayunique)) {
            $errors['options'] = get_string('ierr_labelsduplicated', 'surveyprofield_radiobutton');
        }

        // Second check.
        // Editing teacher can not set "noanswer" as default option if the item is mandatory.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        if ($data['defaultoption'] == SURVEYPRO_CUSTOMDEFAULT) {
            if (empty($data['defaultvalue'])) {
                // Third check.
                // User asks for SURVEYPRO_CUSTOMDEFAULT but doesn't provide it.
                $a = get_string('invitedefault', 'mod_surveypro');
                $errors['defaultoption_group'] = get_string('ierr_missingdefault', 'surveyprofield_radiobutton', $a);
            } else {
                // Fourth check.
                // Each item of default has to also be among options OR has to be == to otherlabel value.
                if (!in_array($cleandefaultvalue, $labels)) {
                    $a = $cleandefaultvalue;
                    $errors['defaultvalue'] = get_string('ierr_foreigndefaultvalue', 'surveyprofield_radiobutton', $a);
                }
            }
        }

        return $errors;
    }
}