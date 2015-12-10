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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/character/lib.php');

class mod_surveypro_field_character extends mod_surveypro_itembase {

    /**
     * $content = the text content of the item.
     */
    public $content = '';

    /**
     * $contenttrust
     */
    public $contenttrust = 1;

    /**
     * public $contentformat = '';
     */
    public $contentformat = '';

    /**
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /**
     * $position = where does the question go?
     */
    public $position = SURVEYPRO_POSITIONLEFT;

    /**
     * $extranote = an optional text describing the item
     */
    public $extranote = '';

    /**
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /**
     * $hideinstructions = boolean. Exceptionally hide filling instructions
     */
    public $hideinstructions = 0;

    /**
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /**
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    // -----------------------------

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $pattern = a string defining which character is expected in each position of the incoming string
     * [a regular expression?]
     */
    public $pattern = '';
    public $pattern_text = '';


    /**
     * $minlength = the minimum allowed length
     */
    public $minlength = '0';

    /**
     * $maxlength = the maximum allowed length
     */
    public $maxlength = null;

    /**
     * static canbeparent
     */
    public static $canbeparent = false;

    // -----------------------------

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param stdClass $cm
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'character';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = false;

        // other element specific properties
        // nothing

        // override properties depending from $surveypro settings
        // nothing

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid, $evaluateparentcontent)))
        parent::item_load($itemid, $evaluateparentcontent);

        // multilang load support for builtin surveypro
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();

        $this->item_custom_fields_to_form();
    }

    /**
     * item_save
     *
     * @param $record
     * @return
     */
    public function item_save($record) {
        $this->item_get_common_settings($record);

        // -----------------------------
        // Now execute very specific plugin level actions
        // -----------------------------

        // begin of: plugin specific settings (eventually overriding general ones)
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        if (!isset($record->minlength)) {
            $record->minlength = 0;
        }
        // maxlength is a PARAM_INT. If the user leaves it empty in the form, maxlength becomes = 0
        if (empty($record->maxlength)) {
            $record->maxlength = null;
        }
        // end of: plugin specific settings (eventually overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_get_canbeparent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function item_get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * item_custom_fields_to_form
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        switch ($this->pattern) {
            case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
            case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
            case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                break;
            default:
                $this->pattern_text = $this->pattern;
                $this->pattern = SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN;
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        if ($record->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
            $record->pattern = $record->pattern_text;

            $record->minlength = strlen($record->pattern_text);
            $record->maxlength = $record->minlength;
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_fields_with_checkbox_todb
     * this function is called to empty fields where $record->{$field.'_check'} == 1
     *
     * @param $record
     * @param $fieldlist
     * @return
     */
    public function item_fields_with_checkbox_todb($record, $fieldlist) {
        foreach ($fieldlist as $fieldbase) {
            if (isset($record->{$fieldbase.'_check'})) {
                $record->{$fieldbase} = null;
                $record->{$fieldbase.'_text'} = null;
            }
        }
    }

    /**
     * item_get_generic_property
     *
     * @param $field
     * @return the content of the field
     */
    public function item_get_generic_property($field) {
        if ($field == 'pattern') {
            if ($this->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
                return $this->pattern_text;
            } else {
                return $this->pattern;
            }
        } else {
            return parent::item_get_generic_property($field);
        }
    }

    /**
     * item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @param none
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['character'] = array('defaultvalue');

        return $fieldlist;
    }

    /**
     * item_get_plugin_schema
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function item_get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="surveyprofield_character">
        <xs:complexType>
            <xs:sequence>
                <xs:element type="xs:string" name="content"/>
                <xs:element name="embedded" minOccurs="0" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element type="xs:string" name="filename"/>
                            <xs:element type="xs:base64Binary" name="filecontent"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element type="xs:int" name="contentformat"/>

                <xs:element type="xs:string" name="customnumber" minOccurs="0"/>
                <xs:element type="xs:int" name="position"/>
                <xs:element type="xs:string" name="extranote" minOccurs="0"/>
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="pattern"/>
                <xs:element type="xs:int" name="minlength" minOccurs="0"/>
                <xs:element type="xs:int" name="maxlength" minOccurs="0"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK userform

    /**
     * userform_mform_element
     *
     * @param $mform
     * @param $searchform
     * @param $readonly
     * @param $submissionid
     * @return
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_character_'.$this->sortindex;

        $thresholdsize = 37;
        $lengthtochar = 1.3;
        $paramelement = array('class' => 'indent-'.$this->indent, 'id' => $idprefix);
        if (!empty($this->maxlength)) {
            $paramelement['maxlength'] = $this->maxlength;
            if ($this->maxlength < $thresholdsize) {
                $paramelement['size'] = $this->maxlength * $lengthtochar;
            } else {
                $paramelement['size'] = $thresholdsize * $lengthtochar;
            }
        } else {
            $paramelement['size'] = $thresholdsize * $lengthtochar;
        }
        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, $paramelement);
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->setDefault($this->itemname, $this->defaultvalue);

            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup = array();
            $paramelement['id'] = $idprefix.'_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', $paramelement);

            unset($paramelement['class']);
            $paramelement['id'] = $idprefix.'_ignoreme';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'), $paramelement);
            $mform->setType($this->itemname, PARAM_RAW);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }
    }

    /**
     * userform_mform_validation
     *
     * @param $data
     * @param &$errors
     * @param $surveypro
     * @param $searchform
     * @return
     */
    public function userform_mform_validation($data, &$errors, $surveypro, $searchform) {
        if ($searchform) {
            return;
        }

        $errorkey = $this->itemname;

        if ($this->required) {
            if (empty($data[$this->itemname])) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if ($this->pattern == SURVEYPROFIELD_CHARACTER_FREEPATTERN) {
            return;
        }

        if (!empty($data[$this->itemname])) {
            $fieldlength = strlen($data[$this->itemname]);
            if (!empty($this->maxlength)) {
                if ($fieldlength > $this->maxlength) {
                    $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyprofield_character');
                }
            }
            if (!empty($this->minlength)) {
                if ($fieldlength < $this->minlength) {
                    $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyprofield_character');
                }
            }
            if (!empty($data[$this->itemname]) && !empty($this->pattern)) {
                switch ($this->pattern) {
                    case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                        if (!validate_email($data[$this->itemname])) {
                            $errors[$errorkey] = get_string('uerr_invalidemail', 'surveyprofield_character');
                        }
                        break;
                    case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                        $regex = '~^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i';
                        $regex = '~^(http(s?)\:\/\/)?[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)([a-zA-Z0-9\‌​-‌​\.\?\,\'\/\\\+&amp;%\$#_]*)?$~i';
                        // if (!surveypro_character_is_valid_url($data[$this->itemname])) {
                        if (!preg_match($regex, $data[$this->itemname])) {
                            $errors[$errorkey] = get_string('uerr_invalidurl', 'surveyprofield_character');
                        }
                        break;
                    case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN: // it is a custom pattern done with "A", "a", "*" and "0"
                        // "A" UPPER CASE CHARACTERS
                        // "a" lower case characters
                        // "*" UPPER case, LOWER case or any special characters like '@', ',', '%', '5', ' ' or whatever
                        // "0" numbers

                        if ($fieldlength != strlen($this->pattern_text)) {
                            $errors[$errorkey] = get_string('uerr_badlength', 'surveyprofield_character');
                        }

                        if (!surveypro_character_text_match_pattern($data[$this->itemname], $this->pattern_text)) {
                            $errors[$errorkey] = get_string('uerr_nopatternmatch', 'surveyprofield_character');
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->pattern = '.$this->pattern, DEBUG_DEVELOPER);
                }
            }
        }
        // return $errors; is not needed because $errors is passed by reference
    }

    /**
     * userform_get_filling_instructions
     *
     * @param none
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        if ($this->pattern == SURVEYPROFIELD_CHARACTER_FREEPATTERN) {
            if (!empty($this->minlength)) {
                if (!empty($this->maxlength)) {
                    $a = new stdClass();
                    $a->minlength = $this->minlength;
                    $a->maxlength = $this->maxlength;
                    $fillinginstruction = get_string('restrictions_minmax', 'surveyprofield_character', $a);
                } else {
                    $a = $this->minlength;
                    $fillinginstruction = get_string('restrictions_min', 'surveyprofield_character', $a);
                }
            } else {
                if (!empty($this->maxlength)) {
                    $a = $this->maxlength;
                    $fillinginstruction = get_string('restrictions_max', 'surveyprofield_character', $a);
                } else {
                    $fillinginstruction = '';
                }
            }
        } else {
            switch ($this->pattern) {
                case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                    $fillinginstruction = get_string('restrictions_email', 'surveyprofield_character');
                    break;
                case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                    $fillinginstruction = get_string('restrictions_url', 'surveyprofield_character');
                    break;
                default:
                    $fillinginstruction = get_string('restrictions_custom', 'surveyprofield_character', $this->pattern_text);
            }
        }

        return $fillinginstruction;
    }

    /**
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param $answer
     * @param $olduseranswer
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (isset($answer['ignoreme'])) {
            $olduseranswer->content = null;
            return;
        }

        if (strlen($answer['mainelement']) == 0) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            $olduseranswer->content = $answer['mainelement'];
        }
    }

    /**
     * this method is called from surveypro_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     *
     * @param $formdb
     * @return
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
            $prefill[$this->itemname] = '';
        } else {
            $prefill[$this->itemname] = $fromdb->content;
        }

        return $prefill;
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     *
     * @param $answers
     * @param $format
     * @return
     */
    public function userform_db_to_export($answer, $format='') {
        // content
        $content = trim($answer->content);
        // SURVEYPRO_NOANSWERVALUE does not exist here
        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'mod_surveypro');
        }

        // output
        if (strlen($content)) {
            $return = $content;
        } else {
            if ($format == SURVEYPRO_FIRENDLYFORMAT) {
                $return = get_string('emptyanswer', 'mod_surveypro');
            } else {
                $return = '';
            }
        }

        return $return;
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname);

        return $elementnames;
    }
}
