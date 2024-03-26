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
 * This file contains the surveyprofield_character
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_character;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/field/character/lib.php');

/**
 * Class to manage each aspect of the character item
 *
 * @package   surveyprofield_character
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends itembase {

    /**
     * @var string $content
     */
    public $content = '';

    /**
     * @var string $contentformat
     */
    public $contentformat = '';

    /**
     * @var string Custom number of the item
     *
     * It usually is 1, 1.1, a, 2.1.a..
     */
    protected $customnumber;

    /**
     * @var int SURVEYPRO_POSITIONLEFT, SURVEYPRO_POSITIONTOP or SURVEYPRO_POSITIONFULLWIDTH
     */
    protected $position;

    /**
     * @var string Optional text with item custom note
     */
    protected $extranote;

    /**
     * @var bool 0 => optional item; 1 => mandatory item;
     */
    protected $required;

    /**
     * @var boolean True if the user input will be trimmed at save time
     */
    protected $trimonsave;

    /**
     * @var boolean True if the instructions are going to be shown in the form; false otherwise
     */
    protected $hideinstructions;

    /**
     * @var string Name of the field storing data in the db table
     */
    protected $variable;

    /**
     * @var int Indent of the item in the form page
     */
    protected $indent;

    /**
     * @var string Value of the field when the form is initially displayed
     */
    protected $defaultvalue;

    /**
     * @var string Required pattern for the text
     *
     * One among:
     *     SURVEYPROFIELD_CHARACTER_FREEPATTERN
     *     SURVEYPROFIELD_CHARACTER_EMAILPATTERN
     *     SURVEYPROFIELD_CHARACTER_URLPATTERN
     *     SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN
     *     SURVEYPROFIELD_CHARACTER_REGEXPATTERN
     */
    protected $pattern;

    /**
     * @var string Required pattern for the text. Mix of: 'A', 'a', '0'
     */
    protected $patterntext;

    /**
     * @var int Minimum allowed length
     */
    protected $minlength;

    /**
     * @var int Maximum allowed length
     */
    protected $maxlength;

    /**
     * @var bool Can this item be parent?
     */
    protected static $canbeparent = false;

    /**
     * Class constructor.
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     * If evaluateparentcontent is true, load the parentitem parentcontent property too
     *
     * @param \stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'character';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        // Empty list.

        if (!empty($itemid)) {
            $this->item_load($itemid, $getparentcontent);
        }
    }

    /**
     * Item load.
     *
     * @param int $itemid
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     * @return void
     */
    public function item_load($itemid, $getparentcontent) {
        parent::item_load($itemid, $getparentcontent);

        // Multilang load support for builtin surveypro.
        // Whether executed, the 'content' field is ALWAYS handled.
        $this->item_builtin_string_load_support();

        $this->item_custom_fields_to_form();
    }

    /**
     * Item save.
     *
     * @param object $record
     * @return void
     */
    public function item_save($record) {
        $this->get_common_settings($record);

        // Now execute very specific plugin level actions.

        // Begin of: plugin specific settings (eventually overriding general ones).
        // Set custom fields value as defined for this question plugin.
        $this->item_custom_fields_to_db($record);
        // End of: plugin specific settings (eventually overriding general ones).

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
     *
     * @param \stdClass $record
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
     * Prepare values for the mform of this item.
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
                if (!surveypro_character_validate_pattern_integrity($this->pattern)) {
                    // If there is no error message from validate_pattern_integrity...
                    $this->pattern = SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN;
                } else {
                    $this->pattern = SURVEYPROFIELD_CHARACTER_REGEXPATTERN;
                }
        }
    }

    /**
     * Traslate values from the mform of this item to values for corresponding properties.
     *
     * @param object $record
     * @return void
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        if ($record->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN) {
            $record->pattern = $record->patterntext;

            $record->minlength = \core_text::strlen($record->patterntext);
            $record->maxlength = $record->minlength;
            unset($record->patterntext);
        }
        if ($record->pattern == SURVEYPROFIELD_CHARACTER_REGEXPATTERN) {
            $record->pattern = $record->patterntext;
            unset($record->patterntext);
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
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['trimonsave'];
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    // MARK get.

    /**
     * Is this item available as a parent?
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * Get the requested property.
     *
     * @param string $field
     * @return the content of the field
     */
    public function get_generic_property($field) {
        if ($field == 'pattern') {
            $condition = ($this->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN);
            $condition = $condition || ($this->pattern == SURVEYPROFIELD_CHARACTER_REGEXPATTERN);
            if ($condition) {
                return $this->patterntext;
            } else {
                return $this->pattern;
            }
        } else {
            return parent::get_generic_property($field);
        }
    }

    /**
     * Make the list of multilang plugin fields.
     *
     * @return array of fields
     */
    public function get_multilang_fields() {
        $fieldlist = [];
        $fieldlist[$this->plugin] = ['content', 'extranote', 'defaultvalue'];

        return $fieldlist;
    }

    /**
     * Does the user input need trim?
     *
     * @return if this plugin requires a user input trim
     */
    public function get_trimonsave() {
        return $this->trimonsave;
    }

    /**
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="surveyprofield_character">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="content" type="xs:string"/>
                <xs:element name="embedded" minOccurs="0" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="filename" type="xs:string"/>
                            <xs:element name="filecontent" type="xs:base64Binary"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element name="contentformat" type="xs:int"/>

                <xs:element name="required" type="xs:int"/>
                <xs:element name="indent" type="xs:int"/>
                <xs:element name="position" type="xs:int"/>
                <xs:element name="customnumber" type="xs:string" minOccurs="0"/>
                <xs:element name="hideinstructions" type="xs:int"/>
                <xs:element name="variable" type="xs:string"/>
                <xs:element name="extranote" type="xs:string" minOccurs="0"/>

                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="trimonsave" type="xs:int"/>
                <xs:element name="pattern" type="xs:string"/>
                <xs:element name="minlength" type="xs:int" minOccurs="0"/>
                <xs:element name="maxlength" type="xs:int" minOccurs="0"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK response.

    /**
     * Report how the sql query does fit for this plugin
     *
     * @param int $itemid
     * @param string $searchrestriction
     * @return the specific where clause for this plugin
     */
    public static function response_get_whereclause($itemid, $searchrestriction) {
        global $DB;

        $whereclause = $DB->sql_like('a.content', ':content_'.$itemid, false);
        $whereparam = '%'.$searchrestriction.'%';

        return [$whereclause, $whereparam];
    }

    // MARK userform.

    /**
     * Define the mform element for the userform and the searchform.
     *
     * Cool for browsers supporting html 5
     * if ($this->pattern == SURVEYPROFIELD_CHARACTER_EMAILPATTERN) {
     *     $attributes['type'] = 'email';
     * }
     * if ($this->pattern == SURVEYPROFIELD_CHARACTER_URLPATTERN) {
     *     $attributes['type'] = 'url';
     * }
     * But it doesn't work because "type" property is reserved to mform library
     *
     * @param \moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $idprefix = 'id_surveypro_field_character_'.$this->sortindex;

        $thresholdsize = 37;
        $lengthtochar = 1.3;
        $attributes = [];
        $attributes['id'] = $idprefix;
        $attributes['class'] = 'indent-'.$this->indent.' character_text';
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
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup = [];
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', $attributes);

            $itemname = $this->itemname.'_ignoreme';
            $starstr = get_string('star', 'mod_surveypro');
            $attributes['id'] = $idprefix.'_ignoreme';
            $attributes['class'] = 'character_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);
            $mform->setType($this->itemname, PARAM_RAW);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }
    }

    /**
     * Perform userform and searchform data validation.
     *
     * @param array $data
     * @param array $errors
     * @param bool $searchform
     * @return void
     */
    public function userform_mform_validation($data, &$errors, $searchform) {
        if ($searchform) {
            return;
        }

        $errorkey = $this->itemname;
        $userinput = empty($this->trimonsave) ? $data[$this->itemname] : trim($data[$this->itemname]);

        if (empty($userinput)) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return;
        }

        $answerlength = \core_text::strlen($userinput);
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
        switch ($this->pattern) {
            case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                break;
            case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                if (!validate_email($userinput)) {
                    $errors[$errorkey] = get_string('uerr_invalidemail', 'surveyprofield_character');
                }
                break;
            case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                if (!surveypro_character_validate_against_url($userinput)) {
                    $errors[$errorkey] = get_string('uerr_invalidurl', 'surveyprofield_character');
                }
                break;
            case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN:
                // Where: "A" UPPER CASE CHARACTERS.
                // Where: "a" lower case characters.
                // Where: "*" UPPER case, LOWER case or any special characters like '@', ',', '%', '5', ' ' or whatever.
                // Where: "0" numbers.
                if (!surveypro_character_validate_against_pattern($userinput, $this->patterntext)) {
                    $errors[$errorkey] = get_string('uerr_nopatternmatch', 'surveyprofield_character');
                }
                break;
            case SURVEYPROFIELD_CHARACTER_REGEXPATTERN:
                if (!surveypro_character_validate_against_regex($userinput, $this->patterntext)) {
                    $errors[$errorkey] = get_string('uerr_noregexmatch', 'surveyprofield_character');
                }
                break;
            default:
                $message = 'Unexpected $this->pattern = '.$this->pattern;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        // Return $errors; is not needed because $errors is passed by reference.
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $arrayinstruction = [];

        if (!empty($this->minlength)) {
            if (!empty($this->maxlength)) {
                if ($this->minlength == $this->maxlength) {
                    $a = $this->minlength;
                    $arrayinstruction[] = get_string('restrictions_exact', 'surveyprofield_character', $a);
                } else {
                    $a = new \stdClass();
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
            case SURVEYPROFIELD_CHARACTER_FREEPATTERN:
                break;
            case SURVEYPROFIELD_CHARACTER_EMAILPATTERN:
                $arrayinstruction[] = get_string('restrictions_email', 'surveyprofield_character');
                break;
            case SURVEYPROFIELD_CHARACTER_URLPATTERN:
                $arrayinstruction[] = get_string('restrictions_url', 'surveyprofield_character');
                break;
            case SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN:
                $arrayinstruction[] = get_string('restrictions_custom', 'surveyprofield_character', $this->patterntext);
                break;
            case SURVEYPROFIELD_CHARACTER_REGEXPATTERN:
                $arrayinstruction[] = get_string('restrictions_regex', 'surveyprofield_character', $this->patterntext);
                break;
            default:
                $message = 'Unexpected $this->pattern = '.$this->pattern;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        if ($this->trimonsave) {
            $arrayinstruction[] = get_string('inputclean', 'surveypro');
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
     * or what to return for the search form.
     * I don't set $olduseranswer->contentformat in order to accept the default db value.
     *
     * @param array $answer
     * @param object $olduseranswer
     * @param bool $searchform
     * @return void
     */
    public function userform_get_user_answer($answer, &$olduseranswer, $searchform) {
        if (isset($answer['ignoreme'])) {
            $olduseranswer->content = null;
            return;
        }

        $userinput = empty($this->trimonsave) ? $answer['mainelement'] : trim($answer['mainelement']);

        $olduseranswer->content = $userinput;
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time.
     *
     * @param object $fromdb
     * @return associative array with disaggregate element values
     */
    public function userform_get_prefill($fromdb) {
        $prefill = [];

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
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
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = [$this->itemname];

        return $elementnames;
    }
}
