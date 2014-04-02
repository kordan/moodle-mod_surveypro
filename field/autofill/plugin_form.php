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
require_once($CFG->dirroot.'/mod/surveypro/field/autofill/lib.php');

class surveypro_pluginform extends mod_surveypro_itembaseform {

    public function definition() {
        // ----------------------------------------
        // I close with the common section of the form
        parent::definition();

        // ----------------------------------------
        // $item = $this->_customdata->item;
        $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // item::contentelement$i
        // ----------------------------------------
        $options = surveypro_autofill_get_elements($surveypro->id);
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index;

            $elementgroup = array();
            $elementgroup[] = $mform->createElement('selectgroups', $fieldname.'_select', '', $options);
            $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');
            $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_autofill'), ' ', false);
            $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;

            $mform->disabledIf($fieldname.'_text', $fieldname.'_select', 'neq', constant($constantname));

            $mform->addHelpButton($fieldname.'_group', 'contentelement_group', 'surveyprofield_autofill');
            $mform->setType($fieldname.'_text', PARAM_TEXT);
        }
        $mform->addRule('element01_group', get_string('required'), 'required', null, 'client');

        // ----------------------------------------
        // item::hiddenfield
        // ----------------------------------------
        $fieldname = 'hiddenfield';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyprofield_autofill'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_autofill');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $errors = parent::validation($data, $files);

        $uniontext = '';
        $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index;
            if ( ($data[$fieldname.'_select'] == constant($constantname)) && (!$data[$fieldname.'_text']) ) {
                $errors[$fieldname.'_group'] = get_string('contenttext_err', 'surveyprofield_autofill');
            }
        }

        return $errors;
    }
}