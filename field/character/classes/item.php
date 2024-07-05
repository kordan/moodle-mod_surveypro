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

    // Itembase properties.

    /**
     * @var bool True if the user input will be trimmed at save time
     */
    protected $trimonsave;

    /**
     * @var int Defaultvalue for the item answer
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
     * @var int Minimum allowed text length
     */
    protected $minlength;

    /**
     * @var int Maximum allowed text length
     */
    protected $maxlength;

    // Service variables.

    /**
     * @var string Required pattern for the text. Mix of: 'A', 'a', '0'
     */
    protected $patterntext;

    /**
     * @var bool Does this item use the child table surveypro(field|format)_plugin?
     */
    protected static $usesplugintable = true;

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

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
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
        // Set properties at plugin level and then continue to base level.

        // Set custom fields values as defined by this specific plugin.
        $this->add_plugin_properties_to_record($record);

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
    public function item_add_fields_default_to_child_table(&$record) {
        $record->trimonsave = 0;
        // $record->defaultvalue
        $record->pattern = SURVEYPROFIELD_CHARACTER_FREEPATTERN;
        $record->minlength = 0;
        // $record->maxlength
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
    public function add_plugin_properties_to_record($record) {
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

    // MARK set.

    /**
     * Set trimonsave.
     *
     * @param string $trimonsave
     * @return void
     */
    public function set_trimonsave($trimonsave) {
        $this->trimonsave = $trimonsave;
    }

    /**
     * Set defaultvalue.
     *
     * @param string $defaultvalue
     * @return void
     */
    public function set_defaultvalue($defaultvalue) {
        $this->defaultvalue = $defaultvalue;
    }

    /**
     * Set pattern.
     *
     * @param string $pattern
     * @return void
     */
    public function set_pattern($pattern) {
        $this->pattern = $pattern;
    }

    /**
     * Set minlength.
     *
     * @param string $minlength
     * @return void
     */
    public function set_minlength($minlength) {
        $this->minlength = $minlength;
    }

    /**
     * Set maxlength.
     *
     * @param string $maxlength
     * @return void
     */
    public function set_maxlength($maxlength) {
        $this->maxlength = $maxlength;
    }

    /**
     * Set patterntext.
     *
     * @param string $patterntext
     * @return void
     */
    public function set_patterntext($patterntext) {
        $this->patterntext = $patterntext;
    }

    // MARK get.

    /**
     * Get trimonsave.
     *
     * @return $this->trimonsave
     */
    public function get_trimonsave() {
        return $this->trimonsave;
    }

    /**
     * Get defaultvalue.
     *
     * @return $this->defaultvalue
     */
    public function get_defaultvalue() {
        return $this->defaultvalue;
    }

    /**
     * Get pattern.
     *
     * @return $this->pattern
     */
    public function get_pattern() {
        return $this->pattern;
    }

    /**
     * Get minlength.
     *
     * @return $this->minlength
     */
    public function get_minlength() {
        return $this->minlength;
    }

    /**
     * Get maxlength.
     *
     * @return $this->maxlength
     */
    public function get_maxlength() {
        return $this->maxlength;
    }

    /**
     * Get patterntext.
     *
     * @return $this->patterntext
     */
    public function get_patterntext() {
        return $this->patterntext;
    }

    /**
     * Get the requested property.
     *
     * @param string $field
     * @return the content of the field or false if it is not set.
     */
    public function get_generic_property($field) {
        if (isset($this->{$field})) {
            if ($field == 'pattern') {
                $condition = ($this->pattern == SURVEYPROFIELD_CHARACTER_CUSTOMPATTERN);
                $condition = $condition || ($this->pattern == SURVEYPROFIELD_CHARACTER_REGEXPATTERN);
                if ($condition) {
                    $return = $this->get_patterntext();
                } else {
                    $return = $this->get_pattern();
                }
            } else {
                $return = parent::get_generic_property($field);
            }
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = ['trimonsave', 'defaultvalue', 'pattern', 'patterntext', 'minlength', 'maxlength'];
        $data = $this->get_base_presets($pluginproperties);

        return $data;
    }

    /**
     * Make the list of multilang plugin fields.
     *
     * @param boolean $includemetafields
     * @return array of fields
     */
    public function get_multilang_fields($includemetafields=true) {
        $fieldlist['surveypro_item'] = $this->get_base_multilang_fields($includemetafields);
        $fieldlist['surveyprofield_character'] = ['defaultvalue'];

        return $fieldlist;
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
                <xs:element name="trimonsave" type="xs:int" minOccurs="0"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="pattern" type="xs:string" minOccurs="0"/>
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

        $attributes = [];
        $elementgroup = [];
        $class = ['class' => 'indent-'.$this->indent];
        $baseid = 'id_field_character_'.$this->sortindex;
        $basename = $this->itemname;

        $thresholdsize = 37;
        $lengthtochar = 1.3;
        $attributes['id'] = $baseid;
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
            $elementgroup[] = $mform->createElement('text', $basename, $elementlabel, $attributes);
            $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);

            $mform->setType($basename, PARAM_RAW);
            $mform->setDefault($basename, $this->defaultvalue);

            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $basename.'_extrarow_group' : $basename.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup[] = $mform->createElement('text', $basename, $elementlabel, $attributes);
            $mform->setType($basename, PARAM_RAW);

            $starstr = get_string('star', 'mod_surveypro');
            $attributes['id'] = $baseid.'_ignoreme';
            $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

            $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
            $mform->disabledIf($basename.'_group', $basename.'_ignoreme', 'checked');
            $mform->setDefault($basename.'_ignoreme', '1');
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
            return $errors;
        }

        $errorkey = $this->itemname.'_group';
        $fieldname = $this->itemname;
        if ($this->trimonsave) {
            if (trim($data[$fieldname]) != $data[$fieldname]) {
                $warnings[$errorkey] = get_string('uerr_willbetrimmed', 'mod_surveypro');
            }
        }

        $userinput = empty($this->trimonsave) ? $data[$fieldname] : trim($data[$fieldname]);

        if (empty($userinput)) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return $errors;
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

        if ( $errors && isset($warnings) ) {
            // Always sum $warnings to $errors so if an element has a warning and an error too, the error it will be preferred.
            $errors += $warnings;
        }

        return $errors;
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
        return [$this->itemname.'_group'];
    }
}
