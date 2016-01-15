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
require_once($CFG->dirroot.'/mod/surveypro/field/numeric/lib.php');

class mod_surveypro_field_numeric extends mod_surveypro_itembase {

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

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $decimalseparator
     */
    public $decimalseparator = '.';

    /**
     * $signed = will be, the expected number, signed
     */
    public $signed = 0;

    /**
     * $lowerbound = the minimun allowed value
     */
    public $lowerbound = '';

    /**
     * $upperbound = the maximum allowed value
     */
    public $upperbound = '';

    /**
     * $decimals = number of decimals allowed for this number
     */
    public $decimals = 0;

    /**
     * static canbeparent
     */
    public static $canbeparent = false;

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

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'numeric';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // It is already true from parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        $this->decimalseparator = get_string('decsep', 'langconfig');

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        // Empty list.

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

        // Multilang load support for builtin surveypro.
        // Whether executed, the 'content' field is ALWAYS handled.
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

        // Now execute very specific plugin level actions.

        // Begin of: plugin specific settings (eventually overriding general ones).
        // Set custom fields value as defined for this question plugin.
        $this->item_custom_fields_to_db($record);
        // End of: plugin specific settings (eventually overriding general ones).

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
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
     * add checkboxes selection for empty fields
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. float numbers need more attention because I can write them using , or .
        if (strlen($this->defaultvalue)) {
            $this->defaultvalue = format_float($this->defaultvalue, $this->decimals);
        }
        if (strlen($this->lowerbound)) {
            $this->lowerbound = format_float($this->lowerbound, $this->decimals);
        }
        if (strlen($this->upperbound)) {
            $this->upperbound = format_float($this->upperbound, $this->decimals);
        }
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        $checkboxes = array('signed');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 3. Set values corresponding to checkboxes.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other: float numbers need more attention because I can write them using , or .
        if (strlen($record->defaultvalue)) {
            $record->defaultvalue = unformat_float($record->defaultvalue, true);
        } else {
            unset($record->defaultvalue);
        }
        if (strlen($record->lowerbound)) {
            $record->lowerbound = unformat_float($record->lowerbound, true);
        } else {
            unset($record->lowerbound);
        }
        if (strlen($record->upperbound)) {
            $record->upperbound = unformat_float($record->upperbound, true);
        } else {
            unset($record->upperbound);
        }
    }

    /**
     * item_atomize_number
     * starting from justanumber, this function returns it splitted into an array
     *
     * @param $justanumber
     * @return
     */
    public function item_atomize_number($justanumber) {
        $pattern = '~^\s*(-?)([0-9]+)'.get_string('decsep', 'langconfig').'?([0-9]*)\s*$~';
        preg_match($pattern, $justanumber, $matches);

        return $matches;
    }

    /**
     * item_get_generic_property
     *
     * @param $field
     * @return the content of the field
     */
    public function item_get_generic_property($field) {
        $doublefields = array('lowerbound', 'upperbound', 'defaultvalue');
        if (in_array($field, $doublefields)) {
            $value = parent::item_get_generic_property($field);
            return unformat_float($value, true);
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
    <xs:element name="surveyprofield_numeric">
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

                <xs:element type="xs:decimal" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="signed"/>
                <xs:element type="xs:decimal" name="lowerbound" minOccurs="0"/>
                <xs:element type="xs:decimal" name="upperbound" minOccurs="0"/>
                <xs:element type="xs:int" name="decimals"/>
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

        $idprefix = 'id_surveypro_field_numeric_'.$this->sortindex;

        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, array('class' => 'indent-'.$this->indent, 'id' => $idprefix));
            $mform->setType($this->itemname, PARAM_RAW); // See: moodlelib.php lines 133+.
            if (strlen($this->defaultvalue)) {
                $mform->setDefault($this->itemname, "$this->defaultvalue");
            }

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
            $attributes = array('class' => 'indent-'.$this->indent, 'id' => $idprefix);
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', $attributes);
            $attributes = array('id' => $idprefix.'_ignoreme');
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'), $attributes);
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

        $draftuserinput = $data[$this->itemname];
        if ($this->required) {
            if (strlen($draftuserinput) == 0) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if (empty($draftuserinput)) {
            return;
        }

        if (strlen($draftuserinput)) {
            $matches = $this->item_atomize_number($draftuserinput);
            if (empty($matches)) {
                // It is not a number, shouts.
                $errors[$errorkey] = get_string('uerr_notanumber', 'surveyprofield_numeric');
                return;
            } else {
                $userinput = unformat_float($draftuserinput, true);
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
        }

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));

        if ($haslowerbound && $hasupperbound) {
            if ($this->lowerbound < $this->upperbound) {
                // Internal range.
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_numeric');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                if (($userinput > $this->lowerbound) && ($userinput < $this->upperbound)) {
                    $format = get_string($this->item_get_friendlyformat(), 'surveyprofield_numeric');
                    $a = new stdClass();
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
     * userform_get_filling_instructions
     *
     * @param none
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));
        $arrayinstruction = array();

        if (!empty($this->signed)) {
            $arrayinstruction[] = get_string('restriction_hassign', 'surveyprofield_numeric');
        }

        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = $this->lowerbound;
            $a->upperbound = $this->upperbound;

            if ($this->lowerbound < $this->upperbound) {
                $arrayinstruction[] = get_string('restriction_lowerupper', 'surveyprofield_numeric', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                $arrayinstruction[] = get_string('restriction_upperlower', 'surveyprofield_numeric', $a);
            }
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

        if (!$searchform) {
            if (strlen($answer['mainelement']) == 0) {
                $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            } else {
                $userinput = unformat_float($answer['mainelement'], true);
                $olduseranswer->content = round($userinput, $this->decimals);
            }
        } else {
            // In the SEARCH form the remote user entered something very wrong.
            // Remember: in the search form NO VALIDATION IS PERFORMED.
            // User is free to waste his/her time as he/she likes.
            $olduseranswer->content = $answer['mainelement'];
        }
    }

    /**
     * this method is called from surveypro_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     *
     * @param $fromdb
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
            $prefill[$this->itemname] = number_format((double)$fromdb->content, $this->decimals, $this->decimalseparator, '');
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
        $content = trim($answer->content);

        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if (strlen($content) == 0) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        return $content;
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
