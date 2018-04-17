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
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/autofill/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_autofill_setupform extends mod_surveypro_itembaseform {

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

        $surveyproid = $this->_customdata['item']->surveypro->id;

        // Item: contentelement$i.
        $options = surveypro_autofill_get_elements($surveyproid);
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index;

            $elementgroup = array();
            $elementgroup[] = $mform->createElement('selectgroups', $fieldname.'select', '', $options);
            $elementgroup[] = $mform->createElement('text', $fieldname.'text', '');
            $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_autofill'), ' ', false);
            $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;

            $mform->disabledIf($fieldname.'text', $fieldname.'select', 'neq', constant($constantname));

            $mform->addHelpButton($fieldname.'_group', 'contentelement_group', 'surveyprofield_autofill');
            $mform->setType($fieldname.'text', PARAM_TEXT);
        }
        $mform->addRule('element01_group', get_string('required'), 'required', null, 'client');

        // Item: hiddenfield.
        $fieldname = 'hiddenfield';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_autofill'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_autofill');
        $mform->setType($fieldname, PARAM_INT);

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

        $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index;
            if ( ($data[$fieldname.'select'] == constant($constantname)) && (!$data[$fieldname.'text']) ) {
                $errors[$fieldname.'_group'] = get_string('ierr_contenttext', 'surveyprofield_autofill');
            }
        }

        return $errors;
    }
}