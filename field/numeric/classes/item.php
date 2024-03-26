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
 * This file contains the surveyprofield_numeric
 *
 * @package   surveyprofield_numeric
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_numeric;

defined('MOODLE_INTERNAL') || die();

use core_text;
use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/field/numeric/lib.php');

/**
 * Class to manage each aspect of the numeric item
 *
 * @package   surveyprofield_numeric
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
     * @var string Decimal separator
     */
    protected $decimalseparator;

    /**
     * @var bool Is the number signed?
     */
    protected $signed;

    /**
     * @var int Number lowerbound
     */
    protected $lowerbound;

    /**
     * @var int Number upperbound
     */
    protected $upperbound;

    /**
     * @var int Number of decimals allowed for this number
     */
    protected $decimals;

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
        $this->plugin = 'numeric';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        $this->decimalseparator = get_string('decsep', 'langconfig');

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
        $record->content = 'Numeric';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'numeric_001';
        $record->indent = 0;
        $record->signed = 0;
        $record->decimals = 0;
    }

    /**
     * Prepare values for the mform of this item.
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. float numbers need more attention because I can write them using , or .
        if (core_text::strlen($this->defaultvalue)) {
            $this->defaultvalue = format_float($this->defaultvalue, $this->decimals);
        }
        // Note: $this->lowerbound and $this->upperbound comes from db and are correctly written.
        // I am not going to put them in a field so I can leave them well written...
        // ...instead of changing them according to the local language.
    }

    /**
     * Traslate values from the mform of this item to values for corresponding properties.
     *
     * @param object $record
     * @return void
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other: float numbers need more attention because I can write them using , or .
        if (core_text::strlen($record->defaultvalue)) {
            $record->defaultvalue = $this->get_international_number($record->defaultvalue);
        } else {
            $record->defaultvalue = null;
        }
        if (core_text::strlen($record->lowerbound)) {
            $record->lowerbound = $this->get_international_number($record->lowerbound);
        } else {
            $record->lowerbound = null;
        }
        if (core_text::strlen($record->upperbound)) {
            $record->upperbound = $this->get_international_number($record->upperbound);
        } else {
            $record->upperbound = null;
        }
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
     * @return the content of the field or false if the field is not set.
     */
    public function get_generic_property($field) {
        if (isset($this->{$field})) {
            $condition = false;
            $condition = $condition || ($field == 'lowerbound');
            $condition = $condition || ($field == 'upperbound');
            $condition = $condition || ($field == 'defaultvalue');
            if ($condition) {
                $return = $this->get_international_number($this->{$field});
            } else {
                $return = $this->{$field};
            }
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * get_international_number
     * starting from justanumber written using local decimal separator,
     * this function returns the same number written using the dot as decimal separator.
     *
     * @param double $localnumber
     * @return void or double $internationalnumber
     */
    public function get_international_number($localnumber) {
        $localnumber = trim($localnumber);
        $internationalnumber = str_replace($this->decimalseparator, '.', $localnumber);
        if (is_numeric($internationalnumber)) {
            $return = $internationalnumber;
        } else {
            $return = false;
        }

        return $return;
    }

    /**
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function get_multilang_fields() {
        $fieldlist = [];
        $fieldlist[$this->plugin] = ['content', 'extranote'];

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
    <xs:element name="surveyprofield_numeric">
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

                <xs:element name="defaultvalue" type="xs:decimal" minOccurs="0"/>
                <xs:element name="signed" type="xs:int"/>
                <xs:element name="lowerbound" type="xs:decimal" minOccurs="0"/>
                <xs:element name="upperbound" type="xs:decimal" minOccurs="0"/>
                <xs:element name="decimals" type="xs:int"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK userform.

    /**
     * Define the mform element for the userform and the searchform.
     *
     * @param \moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        $starstr = get_string('star', 'mod_surveypro');
        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $idprefix = 'id_surveypro_field_numeric_'.$this->sortindex;

        $attributes = [];
        $attributes['id'] = $idprefix;
        $attributes['class'] = 'indent-'.$this->indent.' numeric_text';

        // Cool for browsers supporting html 5.
        // $attributes['type'] = 'number';
        // But it doesn't work because "type" property is reserved to mform library.

        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, $attributes);
            $mform->setType($this->itemname, PARAM_RAW); // See: moodlelib.php lines 133+.
            if (core_text::strlen($this->defaultvalue)) {
                $mform->setDefault($this->itemname, "$this->defaultvalue");
            }

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
            $mform->setType($this->itemname, PARAM_RAW);

            $itemname = $this->itemname.'_ignoreme';
            $attributes['id'] = $idprefix.'_ignoreme';
            $attributes['class'] = 'numeric_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);

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

        $draftuserinput = $data[$this->itemname];
        if (!core_text::strlen($draftuserinput)) {
            if (!empty($this->required)) {
                $errors[$errorkey] = get_string('required');
            }
            return;
        }

        $userinput = $this->get_international_number($draftuserinput);
        if (!is_numeric($userinput)) {
            // It is not a number, shouts.
            $errors[$errorkey] = get_string('uerr_notanumber', 'surveyprofield_numeric');
            return;
        } else {
            // If it is < 0 but has been defined as unsigned, shouts.
            if (!$this->signed && ($userinput < 0)) {
                $errors[$errorkey] = get_string('uerr_negative', 'surveyprofield_numeric');
            }
            // If it has decimal but has been defined as integer, shouts.
            $isinteger = (bool)(strval(intval($userinput)) == strval($userinput));
            if (($this->decimals == 0) && (!$isinteger)) {
                $errors[$errorkey] = get_string('uerr_notinteger', 'surveyprofield_numeric');
            }
        }

        $haslowerbound = (core_text::strlen($this->lowerbound));
        $hasupperbound = (core_text::strlen($this->upperbound));

        if ($haslowerbound && $hasupperbound) {
            // Internal range.
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_numeric');
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                if (($userinput > $this->lowerbound) && ($userinput < $this->upperbound)) {
                    $a = new \stdClass();
                    $a->lowerbound = $this->lowerbound;
                    $a->upperbound = $this->upperbound;
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyprofield_numeric', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_numeric');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_numeric');
            }
        }
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $haslowerbound = (core_text::strlen($this->lowerbound));
        $hasupperbound = (core_text::strlen($this->upperbound));
        $arrayinstruction = [];

        if (!empty($this->signed)) {
            $arrayinstruction[] = get_string('restriction_hassign', 'surveyprofield_numeric');
        }

        if ($haslowerbound && $hasupperbound) {
            $a = new \stdClass();
            $a->lowerbound = $this->lowerbound;
            $a->upperbound = $this->upperbound;

            $arrayinstruction[] = get_string('restriction_lowerupper', 'surveyprofield_numeric', $a);
        } else {
            if ($haslowerbound) {
                $a = $this->lowerbound;
                $arrayinstruction[] = get_string('restriction_lower', 'surveyprofield_numeric', $a);
            }

            if ($hasupperbound) {
                $a = $this->upperbound;
                $arrayinstruction[] = get_string('restriction_upper', 'surveyprofield_numeric', $a);
            }
        }

        if (!empty($this->decimals)) {
            $a = $this->decimals;
            $arrayinstruction[] = get_string('restriction_hasdecimals', 'surveyprofield_numeric', $a);
            $arrayinstruction[] = get_string('decimalautofix', 'surveyprofield_numeric');
            // This sentence dials about decimal separator not about the expected value.
            // So I leave it as last sentence.
            $arrayinstruction[] = get_string('declaredecimalseparator', 'surveyprofield_numeric', $this->decimalseparator);
        } else {
            $arrayinstruction[] = get_string('restriction_isinteger', 'surveyprofield_numeric');
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

        $content = trim($answer['mainelement']);
        if (!core_text::strlen($content)) {
            $olduseranswer->content = '';
        } else {
            $content = $this->get_international_number($content);
            $olduseranswer->content = round($content, $this->decimals);
        }
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

        // This number comes from the db so it can ONLY have '.' as decimal separator.
        if (isset($fromdb->content) && !empty($fromdb->content)) {
            $prefill[$this->itemname] = number_format((double)$fromdb->content, $this->decimals, $this->decimalseparator, '');
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

    /**
     * Does the user input need trim?
     *
     * @return if this plugin requires a user input trim
     */
    public static function userform_input_needs_trim() {
        return true;
    }
}
