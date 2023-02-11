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
 * @package   surveyprofield_boolean
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_boolean;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\local\form\itemsetupbaseform;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/field/boolean/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_boolean
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class itemsetupform extends itemsetupbaseform {

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
        $options = array();
        $options[SURVEYPROFIELD_BOOLEAN_USERADIOH] = get_string('useradioh', 'surveyprofield_boolean');
        $options[SURVEYPROFIELD_BOOLEAN_USERADIOV] = get_string('useradiov', 'surveyprofield_boolean');
        $options[SURVEYPROFIELD_BOOLEAN_USESELECT] = get_string('usemenu', 'surveyprofield_boolean');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_boolean'), $options);
        $mform->setDefault($fieldname, SURVEYPROFIELD_BOOLEAN_USERADIOH);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_boolean');
        $mform->setType($fieldname, PARAM_INT);

        // Item: defaultoption.
        $fieldname = 'defaultoption';
        $customdefaultstr = get_string('customdefault', 'surveyprofield_boolean');
        $invitedefaultstr = get_string('invitedefault', 'mod_surveypro');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $customdefaultstr, SURVEYPRO_CUSTOMDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $invitedefaultstr, SURVEYPRO_INVITEDEFAULT);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $noanswerstr, SURVEYPRO_NOANSWERDEFAULT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_boolean'), ' ', false);
        $mform->setDefault($fieldname, SURVEYPRO_INVITEDEFAULT);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_boolean');

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $options = ['1' => get_string('yes'), '0' => get_string('no')];
        $mform->addElement('select', $fieldname, null, $options);
        $mform->disabledIf($fieldname, 'defaultoption', 'neq', SURVEYPRO_CUSTOMDEFAULT);

        // Item: downloadformat.
        $fieldname = 'downloadformat';
        $options = $item->get_downloadformats();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_boolean'), $options);
        $mform->setDefault($fieldname, $item->get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_boolean');

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

        // Editing teacher can not set "noanswer" as default option if the item is mandatory.
        if ( ($data['defaultoption'] == SURVEYPRO_NOANSWERDEFAULT) && isset($data['required']) ) {
            $a = get_string('noanswer', 'mod_surveypro');
            $errors['defaultoption_group'] = get_string('ierr_notalloweddefault', 'mod_surveypro', $a);
        }

        return $errors;
    }
}
