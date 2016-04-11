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
 * This file contains the mod_surveypro_field_character
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/character/lib.php');

/**
 * Class to manage each aspect of the character item
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_field_character extends mod_surveypro_itembase {

    /**
     * @var string $content
     */
    public $content = '';

    /**
     * @var int $contenttrust
     */
    public $contenttrust = 1;

    /**
     * @var string $contentformat
     */
    public $contentformat = '';

    /**
     * @var string $customnumber, the custom number of the item.
     *
     * It usually is 1, 1.1, a, 2.1.a...
     */
    protected $customnumber;

    /**
     * @var int $position, SURVEYPRO_POSITIONLEFT, SURVEYPRO_POSITIONTOP or SURVEYPRO_POSITIONFULLWIDTH
     */
    protected $position;

    /**
     * @var string $extranote, the optional text describing the item
     */
    protected $extranote;

    /**
     * @var bool $required,  O => optional item; 1 => mandatory item;
     */
    protected $required;

    /**
     * @var boolean $hideinstructions, True if the instructions are going to be shown in the form; false otherwise
     */
    protected $hideinstructions;

    /**
     * @var string $variable,  the name of the field storing data in the db table
     */
    protected $variable;

    /**
     * @var int $indent, the indent of the item in the form page
     */
    protected $indent;

    /**
     * @var string $defaultvalue, the value of the field when the form is initially displayed.
     */
    protected $defaultvalue;

    /**
     * @var string $pattern, required pattern for the text
     *
     * One among:
     *     SURVEYPROFIELD_CHARACTER_FREEPATTERN
     *     SURVEYPROFIELD_CHARACTER_EMAILPATTERN
     *     SURVEYPROFIELD_CHARACTER_URLPATTERN
     *     SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN
     */
    protected $pattern;

    /**
     * @var string $pattern_text, required pattern for the text. Mix of: 'A', 'a', '0'
     */
    protected $patterntext;

    /**
     * @var int $minlength, the minimum allowed length
     */
    protected $minlength;

    /**
     * @var int $maxlength, the maximum allowed length
     */
    protected $maxlength;

    /**
     * @var bool canbeparent
     */
    protected static $canbeparent = false;

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database.
     * If evaluateparentcontent is true, load the parentitem parentcontent property too.
     *
     * @param stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $evaluateparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise.
     */
    public function __construct($cm, $surveypro, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'character';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        // Empty list.

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * Item load
     *
     * @param int $itemid
     * @param bool $evaluateparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise.
     * @return void
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid, $evaluateparentcontent)))
        parent::item_load($itemid, $evaluateparentcontent);

        // Multilang load support for builtin surveypro.
        // Whether executed, the 'content' field is ALWAYS handled.
        $this->item_builtin_string_load_support();

        $this->item_custom_fields_to_form();
    }

    /**
     * Item save
     *
     * @param object $record
     * @return void
     */
    public function item_save($record) {
        $this->item_get_common_settings($record);

        // Now execute very specific plugin level actions.

        // Begin of: plugin specific settings (eventually overriding general ones).
        // Set custom fields value as defined for this question plugin.
        $this->item_custom_fields_to_db($record);
        // End of: plugin specific settings (eventually overriding general ones).

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Item get can be parent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function item_get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Character';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'character_001';
        $record->indent = 0;
        $record->pattern = SURVEYPROFIELD_CHARACTER_FREEPATTERN;
        $record->minlength = 0;
    }

    /**
     * Prepare values for the mform of this item
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        switch ($this->pattern) {
            case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
            case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
            case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                break;
            default:
                $this->patterntext = $this->pattern;
                $this->pattern = SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN;
        }
    }

    /**
     * Traslate values from the mform of this item to values for corresponding properties
     *
     * @param object $record
     * @return void
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        if ($record->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
            $record->pattern = $record->pattern_text;

            $record->minlength = strlen($record->pattern_text);
            $record->maxlength = $record->minlength;
        }

        // 2. Override few values.
        if (!isset($record->minlength)) {
            $record->minlength = 0;
        }
        // Maxlength is a PARAM_INT. If the user leaves it empty in the form, maxlength becomes = 0.
        if (empty($record->maxlength)) {
            $record->maxlength = null;
        }

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * This function is called to empty fields when $record->{$field.'_check'} == 1
     *
     * @param object $record
     * @param array $fieldlist
     * @return void
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
     * Get generic property
     *
     * @param string $field
     * @return the content of the field
     */
    public function item_get_generic_property($field) {
        if ($field == 'pattern') {
            if ($this->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
                return $this->patterntext;
            } else {
                return $this->pattern;
            }
        } else {
            return parent::item_get_generic_property($field);
        }
    }

    /**
     * Make the list of multilang plugin fields
     *
     * @return array of fields
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['character'] = array('defaultvalue');

        return $fieldlist;
    }

    /**
     * Return the xml schema for surveypro_<<plugin>> table
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
     * Define the mform element for the outform and the searchform
     *
     * Cool for browsers supporting html 5.
     * if ($this->pattern == SURVEYPROFIELD_CHARACTER_EMAILPATTERN) {
     *     $attributes['type'] = 'email';
     * }
     * if ($this->pattern == SURVEYPROFIELD_CHARACTER_URLPATTERN) {
     *     $attributes['type'] = 'url';
     * }
     * But it doesn't work because "type" property is reserved to mform library
     *
     * @param moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @param int $submissionid
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_character_'.$this->sortindex;

        $thresholdsize = 37;
        $lengthtochar = 1.3;
        $attributes = array('class' => 'indent-'.$this->indent, 'id' => $idprefix);
        if (!empty($this->maxlength)) {
            $attributes['maxlength'] = $this->maxlength;
            if ($this->maxlength < $thresholdsize) {
                $attributes['size'] = $this->maxlength * $lengthtochar;
            } else {
                $attributes['size'] = $thresholdsize * $lengthtochar;
            }
        } else {
            $attributes['size'] = $thresholdsize * $lengthtochar;
        }
        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, $attributes);
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->setDefault($this->itemname, $this->defaultvalue);

            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // -> I do not want JS form validation if the page is submitted through the "previous" button.
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup = array();
            $attributes['id'] = $idprefix.'_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', $attributes);

            unset($attributes['class']);
            $attributes['id'] = $idprefix.'_ignoreme';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'), $attributes);
            $mform->setType($this->itemname, PARAM_RAW);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }
    }

    /**
     * Perform outform and searchform data validation
     *
     * @param array $data
     * @param array $errors
     * @param array $surveypro
     * @param bool $searchform
     * @return void
     */
    public function userform_mform_validation($data, &$errors, $surveypro, $searchform) {
        if ($searchform) {
            return;
        }

        $errorkey = $this->itemname;

        if (empty($data[$this->itemname])) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return;
        }

        if (!empty($data[$this->itemname])) {
            $answerlength = strlen($data[$this->itemname]);
            if (!empty($this->minlength)) {
                if ($answerlength < $this->minlength) {
                    $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyprofield_character');
                }
            }
            if (!empty($this->maxlength)) {
                if ($answerlength > $this->maxlength) {
                    $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyprofield_character');
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
                        // $regex = '~^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$~i';
                        $regex = '~^(http(s?)\:\/\/)?[0-9a-zA-Z]([-.\w]*[0-9a-zA-Z])*(:(0-9)*)*(\/?)([a-zA-Z0-9\‌​-‌​\.\?\,\'\/\\\+&amp;%\$#_]*)?$~i';
                        // if (!surveypro_character_is_valid_url($data[$this->itemname])) {
                        if (!preg_match($regex, $data[$this->itemname])) {
                            $errors[$errorkey] = get_string('uerr_invalidurl', 'surveyprofield_character');
                        }
                        break;
                    case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN: // It is a custom pattern done with "A", "a", "*" and "0".
                        // "A" UPPER CASE CHARACTERS.
                        // "a" lower case characters.
                        // "*" UPPER case, LOWER case or any special characters like '@', ',', '%', '5', ' ' or whatever.
                        // "0" numbers.

                        if ($answerlength != strlen($this->patterntext)) {
                            $errors[$errorkey] = get_string('uerr_badlength', 'surveyprofield_character');
                        }

                        if (!surveyprofield_character_validate_pattern($data[$this->itemname], $this->pattern_text)) {
                            $errors[$errorkey] = get_string('uerr_nopatternmatch', 'surveyprofield_character');
                        }
                        break;
                    case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                        break;
                    default:
                        $message = 'Unexpected $this->pattern = '.$this->pattern;
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                }
            }
        }
        // Return $errors; is not needed because $errors is passed by reference.
    }

    /**
     * Prepare the string with the filling instruction
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $arrayinstruction = array();

        if (!empty($this->minlength)) {
            if (!empty($this->maxlength)) {
                if ($this->minlength == $this->maxlength) {
                    $a = $this->minlength;
                    $arrayinstruction[] = get_string('restrictions_exact', 'surveyprofield_character', $a);
                } else {
                    $a = new stdClass();
                    $a->minlength = $this->minlength;
                    $a->maxlength = $this->maxlength;
                    $arrayinstruction[] = get_string('restrictions_minmax', 'surveyprofield_character', $a);
                }
            } else {
                $a = $this->minlength;
                $arrayinstruction[] = get_string('restrictions_min', 'surveyprofield_character', $a);
            }
        } else {
            if (!empty($this->maxlength)) {
                $a = $this->maxlength;
                $arrayinstruction[] = get_string('restrictions_max', 'surveyprofield_character', $a);
            }
        }

        switch ($this->pattern) {
            case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                $arrayinstruction[] = get_string('restrictions_email', 'surveyprofield_character');
                break;
            case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                $arrayinstruction[] = get_string('restrictions_url', 'surveyprofield_character');
                break;
            case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN:
                $arrayinstruction[] = get_string('restrictions_custom', 'surveyprofield_character', $this->patterntext);
                break;
            case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                break;
            default:
        }

        if (count($arrayinstruction)) {
            $fillinginstruction = implode('; ', $arrayinstruction);
        } else {
            $fillinginstruction = '';
        }

        return $fillinginstruction;
    }

    /**
     * Starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param array $answer
     * @param object $olduseranswer
     * @param bool $searchform
     * @return void
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (isset($answer['ignoreme'])) {
            $olduseranswer->content = null;
            return;
        }

        if (strlen($answer['mainelement']) == 0) {
            $olduseranswer->content = null;
        } else {
            $olduseranswer->content = $answer['mainelement'];
        }
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time
     *
     * @param object $fromdb
     * @return void
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname] = '';
            } else {
                $prefill[$this->itemname] = $fromdb->content;
            }
        }

        return $prefill;
    }

    /**
     * Starting from the info stored into $answer, this function returns the corresponding content for the export file
     *
     * @param object $answer
     * @param string $format
     * @return string - the string for the export file
     */
    public function userform_db_to_export($answer, $format='') {
        // Content.
        $content = trim($answer->content);
        // SURVEYPRO_NOANSWERVALUE does not exist here
        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        // Output.
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
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname);

        return $elementnames;
    }
}
