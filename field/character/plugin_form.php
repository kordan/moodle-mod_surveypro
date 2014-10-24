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
require_once($CFG->dirroot.'/mod/surveypro/field/character/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    public function definition() {
        // ----------------------------------------
        // start with the common section of the form
        parent::definition();

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // ----------------------------------------
        // item: defaultvalue
        // ----------------------------------------
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->setDefault($fieldname, '');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_RAW);

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // ----------------------------------------
        // item: pattern
        // ----------------------------------------
        $fieldname = 'pattern';
        $options = array();
        $options[SURVEYPROFIELD_CHARACTER_FREEPATTERN] = get_string('free', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_EMAILPATTERN] = get_string('mail', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_URLPATTERN] = get_string('url', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN] = get_string('custompattern', 'surveyprofield_character');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname, '', $options);
        $elementgroup[] = $mform->createElement('text', $fieldname.'_text', '');
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_character'), ' ', false);
        $mform->disabledIf($fieldname.'_text', $fieldname, 'neq', SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_RAW);
        $mform->setType($fieldname.'_text', PARAM_TEXT);

        // ----------------------------------------
        // item: minlength
        // ----------------------------------------
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setDefault($fieldname, '0');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item: maxlength
        // ----------------------------------------
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_INT);

        $this->add_item_buttons();
    }

    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        // Minimum characters <= Maximum characters
        if (!empty($data['minlength'])) {
            if (!empty($data['maxlength'])) {
                if ($data['minlength'] > $data['maxlength']) {
                    $errors['minlength'] = get_string('ierr_mingtmax', 'surveyprofield_character');
                    $errors['maxlength'] = get_string('ierr_maxltmin', 'surveyprofield_character');
                }
            } else {
                // Minimum characters > 0
                if ($data['minlength'] < 0) {
                    $errors['minlength'] = get_string('ierr_minexceeds', 'surveyprofield_character');
                }
            }
        }

        if (!empty($data['defaultvalue'])) {
            // Maximum characters > length of default
            $defaultvaluelength = strlen($data['defaultvalue']);
            if (!empty($data['maxlength'])) {
                if ($defaultvaluelength > $data['maxlength']) {
                    $errors['defaultvalue'] = get_string('ierr_toolongdefault', 'surveyprofield_character');
                }
            }

            // Minimum characters < length of default
            if ($defaultvaluelength < $data['minlength']) {
                $errors['defaultvalue'] = get_string('ierr_tooshortdefault', 'surveyprofield_character');
            }

            // default has to match the text pattern
            switch ($data['pattern']) {
                case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                    break;
                case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                    if (!validate_email($data['defaultvalue'])) {
                        $errors['defaultvalue'] = get_string('ierr_defaultisnotemail', 'surveyprofield_character');
                    }
                    break;
                case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                    if (!surveypro_character_is_valid_url($data['defaultvalue'])) {
                        $errors['defaultvalue'] = get_string('ierr_defaultisnoturl', 'surveyprofield_character');
                    }
                    break;
                case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN:
                    $patternlength = strlen($data['pattern_text']);
                    if ($defaultvaluelength != $patternlength) {
                        $errors['defaultvalue'] = get_string('ierr_defaultbadlength', 'surveyprofield_character', $patternlength);
                    } else if (!surveypro_character_text_match_pattern($data['defaultvalue'], $data['pattern_text'])) {
                        $errors['defaultvalue'] = get_string('ierr_nopatternmatch', 'surveyprofield_character');
                    }
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $data[\'pattern\'] = '.$data['pattern'], DEBUG_DEVELOPER);
            }
        }

        // if pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN, its length has to fall between minlength and maxlength
        if ($data['pattern'] == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
            $patternlength = strlen($data['pattern_text']);
            // pattern can not be empty
            if (!$patternlength) {
                $errors['pattern_group'] = get_string('ierr_patternisempty', 'surveyprofield_character');
            }
            // pattern can be done only from A, a, * and 0
            if (preg_match_all('~[^Aa\*0]~', $data['pattern_text'], $matches)) {
                $denied = array_unique($matches[0]);
                $a = '"'.implode('", "', $denied).'"';
                $errors['pattern_group'] = get_string('ierr_extracharfound', 'surveyprofield_character', $a);
            }
        }

        return $errors;
    }
}