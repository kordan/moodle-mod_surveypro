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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/multiselect/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

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
        // item::options
        // ----------------------------------------
        $fieldname = 'options';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_multiselect'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_multiselect');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW); // PARAM_RAW and not PARAM_TEXT otherwise '<' is not accepted

        // ----------------------------------------
        // item::defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveyprofield_multiselect'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_multiselect');
        $mform->setType($fieldname, PARAM_TEXT);

        // ----------------------------------------
        // item::heightinrows
        // ----------------------------------------
        $fieldname = 'heightinrows';
        $options = array_combine(range(3, 12), range(3, 12));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_multiselect'), $options);
        $mform->setDefault($fieldname, '4');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_multiselect');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item::minimumrequired
        // ----------------------------------------
        $fieldname = 'minimumrequired';
        $options = array_combine(range(0, 9), range(0, 9));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_multiselect'), $options);
        $mform->setDefault($fieldname, 0);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_multiselect');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item::downloadformat
        // ----------------------------------------
        $fieldname = 'downloadformat';
        $options = array(SURVEYPRO_ITEMSRETURNSVALUES => get_string('returnvalues', 'surveyprofield_multiselect'),
                         SURVEYPRO_ITEMRETURNSLABELS => get_string('returnlabels', 'surveyprofield_multiselect'),
                         SURVEYPRO_ITEMRETURNSPOSITION => get_string('returnposition', 'surveyprofield_multiselect'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyprofield_multiselect'), $options);
        $mform->setDefault($fieldname, $item->item_get_friendlyformat());
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_multiselect');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // clean inputs
        $cleanoptions = surveypro_textarea_to_array($data['options']);
        $cleandefaultvalue = surveypro_textarea_to_array($data['defaultvalue']);

        // build $value array (I do not care about $label) starting from $cleanoptions
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

        // -----------------------------
        // first check
        // each item of default has to be among options item OR has to be == to otherlabel value
        // this also verify (helped by the second check) that the number of default is not gretr than the number of options
        // -----------------------------
        if (!empty($data['defaultvalue'])) {
            foreach ($cleandefaultvalue as $default) {
                if (!in_array($default, $labels)) {
                    $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyprofield_multiselect', $default);
                    break;
                }
            }
        }

        // -----------------------------
        // second check
        // each single option item has to be unique
        // each single default item has to be unique
        // -----------------------------
        $arrayunique = array_unique($cleanoptions);
        if (count($cleanoptions) != count($arrayunique)) {
            $errors['options'] = get_string('optionsduplicated_err', 'surveyprofield_multiselect');
        }
        $arrayunique = array_unique($cleandefaultvalue);
        if (count($cleandefaultvalue) != count($arrayunique)) {
            $errors['defaultvalue'] = get_string('defaultvalue_err', 'surveyprofield_multiselect', $default);
        }

        // -----------------------------
        // third check
        // SURVEYPRO_DBMULTICONTENTSEPARATOR can not be contained into values
        // -----------------------------
        foreach ($values as $value) {
            if (strpos($value, SURVEYPRO_DBMULTICONTENTSEPARATOR) !== false) {
                $errors['options'] = get_string('optionswithseparator_err', 'surveyprofield_multiselect', SURVEYPRO_DBMULTICONTENTSEPARATOR);
                break;
            }
        }

        // -----------------------------
        // fourth check
        // minimumrequired has to be lower than count($cleanoptions)
        // -----------------------------
        if ($data['minimumrequired'] > count($cleanoptions)-1) {
            $errors['minimumrequired'] = get_string('minimumrequired_err', 'surveyprofield_multiselect', count($cleanoptions));
        }

        return $errors;
    }
}