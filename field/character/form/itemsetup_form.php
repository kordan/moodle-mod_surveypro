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
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/field/character/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_character_setupform extends mod_surveypro_itembaseform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        // Start with the common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        // Useless: $item = $this->_customdata['item'];.

        // Item: defaultvalue.
        $fieldname = 'defaultvalue';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->setDefault($fieldname, '');
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_RAW);

        // Here I open a new fieldset.
        $fieldname = 'validation';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Item: pattern.
        $fieldname = 'pattern';
        $options = array();
        $options[SURVEYPROFIELD_CHARACTER_FREEPATTERN] = get_string('free', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_EMAILPATTERN] = get_string('mail', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_URLPATTERN] = get_string('url', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN] = get_string('custompattern', 'surveyprofield_character');
        $options[SURVEYPROFIELD_CHARACTER_REGEXPATTERN] = get_string('regex', 'surveyprofield_character');
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname, '', $options);
        $elementgroup[] = $mform->createElement('text', $fieldname.'text', '', array('size' => 55));
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveyprofield_character'), ' ', false);
        // $mform->setDefault($fieldname, SURVEYPROFIELD_CHARACTER_FREEPATTERN);
        $mform->disabledIf($fieldname.'text', $fieldname, 'eq', SURVEYPROFIELD_CHARACTER_FREEPATTERN);
        $mform->disabledIf($fieldname.'text', $fieldname, 'eq', SURVEYPROFIELD_CHARACTER_EMAILPATTERN);
        $mform->disabledIf($fieldname.'text', $fieldname, 'eq', SURVEYPROFIELD_CHARACTER_URLPATTERN);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_RAW);
        $mform->setType($fieldname.'text', PARAM_TEXT);

        // Item: minlength.
        $fieldname = 'minlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->setDefault($fieldname, 0);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
        $mform->setType($fieldname, PARAM_INT);

        // Item: maxlength.
        $fieldname = 'maxlength';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyprofield_character'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyprofield_character');
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

        // Minimum characters <= Maximum characters.
        if (!empty($data['minlength'])) {
            if (!empty($data['maxlength'])) {
                if ($data['minlength'] > $data['maxlength']) {
                    $errors['minlength'] = get_string('ierr_mingtmax', 'surveyprofield_character');
                }
            } else {
                // Minimum characters > 0.
                if ($data['minlength'] < 0) {
                    $errors['minlength'] = get_string('ierr_minexceeds', 'surveyprofield_character');
                }
            }
        }

        if (!empty($data['defaultvalue'])) {
            // Maximum characters > length of default.
            $defaultvaluelength = strlen($data['defaultvalue']);
            if (!empty($data['maxlength'])) {
                if ($defaultvaluelength > $data['maxlength']) {
                    $errors['defaultvalue'] = get_string('ierr_toolongdefault', 'surveyprofield_character');
                }
            }

            // Minimum characters < length of default.
            if ($defaultvaluelength < $data['minlength']) {
                $errors['defaultvalue'] = get_string('ierr_tooshortdefault', 'surveyprofield_character');
            }

            // Default has to match the text pattern.
            switch ($data['pattern']) {
                case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                    break;
                case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                    if (!validate_email($data['defaultvalue'])) {
                        $errors['defaultvalue'] = get_string('ierr_defaultisnotemail', 'surveyprofield_character');
                    }
                    break;
                case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                    if (!surveypro_character_validate_against_url($data['defaultvalue'])) {
                        $errors['defaultvalue'] = get_string('ierr_defaultisnoturl', 'surveyprofield_character');
                    }
                    break;
                case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN:
                    if (!surveypro_character_validate_against_pattern($data['defaultvalue'], $this->patterntext)) {
                        $errors['defaultvalue'] = get_string('ierr_nopatternmatch', 'surveyprofield_character');;
                    }
                case SURVEYPROFIELD_CHARACTER_REGEXPATTERN:
                    if (!surveypro_character_validate_against_regex($data['defaultvalue'], $data['patterntext'])) {
                        $errors['defaultvalue'] = get_string('ierr_noregexmatch', 'surveyprofield_character');
                    }
                    break;
                default:
                    $message = 'Unexpected $data[\'pattern\'] = '.$data['pattern'];
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }

        if ($data['pattern'] == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
            if ($message = surveypro_character_validate_pattern_integrity($data['patterntext'])) {
                $errors['pattern_group'] = $message;
            }
        }
        if ($data['pattern'] == SURVEYPROFIELD_CHARACTER_REGEXPATTERN) {
            // Pattern is supposed to be a valid regular expression.
            if ($message = surveypro_character_validate_regex_integrity($data['patterntext'])) {
                $errors['pattern_group'] = $message;
            }
        }

        return $errors;
    }
}