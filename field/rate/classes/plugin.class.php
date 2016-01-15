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
require_once($CFG->dirroot.'/mod/surveypro/field/rate/lib.php');

class mod_surveypro_field_rate extends mod_surveypro_itembase {

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
     * $options = list of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /**
     * $rates = list of allowed rates in the form: "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    public $rates = '';

    /**
     * $defaultoption
     */
    public $defaultoption = SURVEYPRO_INVITEDEFAULT;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $style = how is this rate item displayed? with radiobutton or with dropdown menu?
     */
    public $style = 0;

    /**
     * $allowsamerate = is the user allowed to provide two equal rates for two different options?
     */
    public $differentrates = false;

    /**
     * static canbeparent
     */
    public static $canbeparent = false;

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int optional $itemid
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'rate';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // It is already true from parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings..
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->isinitemform['insearchform'] = false;
        $this->isinitemform['position'] = SURVEYPRO_POSITIONLEFT;

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
        // Drop empty rows and trim edging rows spaces from each textarea field.
        $fieldlist = array('options', 'rates', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

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
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Position and hideinstructions are set by design.
        $record->position = SURVEYPRO_POSITIONTOP;
        $record->hideinstructions = 1;

        // 3. Set values corresponding to checkboxes.
        $checkboxes = array('differentrates');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * item_left_position_allowed
     *
     * @param none
     * @return: boolean
     */
    public function item_left_position_allowed() {
        return false;
    }

    /**
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     *
     * @param $record
     * @return
     */
    public function item_generate_standard_default($options=null, $rates=null, $differentrates=null) {

        if (is_null($options)) {
            $options = $this->options;
        }
        if (is_null($rates)) {
            $rates = $this->rates;
        }
        if (is_null($differentrates)) {
            $differentrates = $this->differentrates;
        }

        if ($optionscount = count(surveypro_textarea_to_array($options))) {
            $ratesarray = surveypro_textarea_to_array($rates);
            if ($differentrates) {
                $default = array();
                foreach ($ratesarray as $k => $singlerate) {
                    if (strpos($singlerate, SURVEYPRO_VALUELABELSEPARATOR) === false) {
                        $defaultrate = $singlerate;
                    } else {
                        $pair = explode(SURVEYPRO_VALUELABELSEPARATOR, $singlerate);
                        $defaultrate = $pair[0];
                    }
                    $default[] = $defaultrate;
                    if (count($default) == $optionscount) {
                        break;
                    }
                }
            } else {
                $firstrate = array_shift($ratesarray);

                if (strpos($firstrate, SURVEYPRO_VALUELABELSEPARATOR) === false) {
                    $defaultrate = $firstrate;
                } else {
                    $pair = explode(SURVEYPRO_VALUELABELSEPARATOR, $firstrate);
                    $defaultrate = $pair[0];
                }

                $default = array_fill(1, $optionscount, $defaultrate);
            }
            return implode("\n", $default);
        }
    }

    /**
     * item_get_friendlyformat
     *
     * @param none
     * @return
     */
    public function item_get_friendlyformat() {
        return SURVEYPRO_ITEMRETURNSLABELS;
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
        $fieldlist['rate'] = array('content', 'options', 'rates', 'defaultvalue');

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
    <xs:element name="surveyprofield_rate">
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

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="rates"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="downloadformat"/>
                <xs:element type="xs:int" name="style"/>
                <xs:element type="xs:int" name="differentrates"/>
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
        // This plugin has $this->isinitemform['insearchform'] = false; so it will never be part of a search form.

        $options = surveypro_textarea_to_array($this->options);
        $optioncount = count($options) - 1;
        $rates = $this->item_get_content_array(SURVEYPRO_LABELS, 'rates');
        $defaultvalues = surveypro_textarea_to_array($this->defaultvalue);

        $idprefix = 'id_surveypro_field_rate_'.$this->sortindex;

        if (($this->defaultoption == SURVEYPRO_INVITEDEFAULT)) {
            if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                $rates += array(SURVEYPRO_INVITEVALUE => get_string('choosedots'));
            } else {
                $rates = array(SURVEYPRO_INVITEVALUE => get_string('choosedots')) + $rates;
            }
        }

        if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
            foreach ($options as $k => $option) {
                $attributes = array('class' => 'indent-'.$this->indent);
                $uniquename = $this->itemname.'_'.$k;
                $elementgroup = array();
                foreach ($rates as $j => $rate) {
                    $attributes['id'] = $idprefix.'_'.$k.'_'.$j;
                    $elementgroup[] = $mform->createElement('mod_surveypro_radio', $uniquename, '', $rate, $j, $attributes);
                    unset($attributes['class']);
                }
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, ' ', false);
                $this->item_add_color_unifier($mform, $k, $optioncount);
            }
        }

        $attributes = array('class' => 'indent-'.$this->indent);
        if ($this->style == SURVEYPROFIELD_RATE_USESELECT) {
            foreach ($options as $k => $option) {
                $uniquename = $this->itemname.'_'.$k;
                $attributes['id'] = $idprefix.'_'.$k;
                $mform->addElement('mod_surveypro_select', $uniquename, $option, $rates, $attributes);
                $this->item_add_color_unifier($mform, $k, $optioncount);
            }
        }

        if (!$this->required) { // This is the last if it exists
            $attributes['id'] = $idprefix.'_noanswer';
            $mform->addElement('mod_surveypro_checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'mod_surveypro'), $attributes);
        }

        if ($this->required) {
            // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
            // -> I do not want JS form validation if the page is submitted through the "previous" button.
            // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
            // Simply add a dummy star to the item and the footer note about mandatory fields.
            $mform->_required[] = $this->itemname.'_extrarow';
        } else {
            // Disable if $this->itemname.'_noanswer' is selected.
            $optionindex = 0;
            foreach ($options as $option) {
                if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                    $uniquename = $this->itemname.'_'.$optionindex.'_group';
                } else {
                    $uniquename = $this->itemname.'_'.$optionindex;
                }

                $mform->disabledIf($uniquename, $this->itemname.'_noanswer', 'checked');
                $optionindex++;
            }
            if ($this->defaultoption == SURVEYPRO_NOANSWERDEFAULT) {
                $mform->setDefault($this->itemname.'_noanswer', '1');
            }
        }

        switch ($this->defaultoption) {
            case SURVEYPRO_CUSTOMDEFAULT:
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $defaultindex = array_search($defaultvalues[$k], $rates);
                    $mform->setDefault($uniquename, "$defaultindex");
                }
                break;
            case SURVEYPRO_INVITEDEFAULT:
                foreach ($options as $k => $option) {
                    $uniquename = $this->itemname.'_'.$k;
                    $mform->setDefault($uniquename, SURVEYPRO_INVITEVALUE);
                }
                break;
            case SURVEYPRO_NOANSWERDEFAULT:
                $uniquename = $this->itemname.'_noanswer';
                $mform->setDefault($uniquename, SURVEYPRO_NOANSWERVALUE);
                break;
            default:
                $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
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
        // This plugin displays as a set of dropdown menu or radio buttons. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($searchform) {
            return;
        }

        // If different rates were requested, it is time to verify this.
        $options = surveypro_textarea_to_array($this->options);

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // Nothing to validate.
        }

        $optionindex = 0;
        $return = false;
        foreach ($options as $option) {
            $uniquename = $this->itemname.'_'.$optionindex;
            if ($data[$uniquename] == SURVEYPRO_INVITEVALUE) {
                if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                    $elementname = $uniquename.'_group';
                } else {
                    $elementname = $uniquename;
                }
                $errors[$elementname] = get_string('uerr_optionnotset', 'surveyprofield_rate');
                $return = true;
            }
            $optionindex++;
        }
        if ($return) {
            return;
        }

        if (!empty($this->differentrates)) {
            $optionscount = count($this->item_get_content_array(SURVEYPRO_LABELS, 'options'));
            $rates = array();
            for ($i = 0; $i < $optionscount; $i++) {
                $rates[] = $data[$this->itemname.'_'.$i];
            }

            $uniquerates = array_unique($rates);
            $duplicaterates = array_diff_assoc($rates, $uniquerates);

            foreach ($duplicaterates as $k => $v) {
                if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                    $elementname = $this->itemname.'_'.$k.'_group';
                } else {
                    $elementname = $this->itemname.'_'.$k;
                }
                $errors[$elementname] = get_string('uerr_duplicaterate', 'surveyprofield_rate');
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

        if (!empty($this->differentrates)) {
            $fillinginstruction = get_string('diffratesrequired', 'surveyprofield_rate');
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
        if (isset($answer['noanswer'])) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            $return = array();
            foreach ($answer as $answeredrate) {
                $return[] = $answeredrate;
            }
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $return);
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
        // [surveypro_field_rate_157_0] => italian: 3
        // [surveypro_field_rate_157_1] => english: 2
        // [surveypro_field_rate_157_2] => french: 1
        // [surveypro_field_rate_157_noanswer] => 0

        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
            } else {
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);

                foreach ($answers as $optionindex => $value) {
                    $uniquename = $this->itemname.'_'.$optionindex;
                    $prefill[$uniquename] = $answers[$optionindex];
                }
            }
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
        // Content.
        $content = $answer->content;
        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        // Format.
        if ($format == SURVEYPRO_FIRENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // $answers is an array like: array(1,1,0,0)
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                $rates = $this->item_get_content_array(SURVEYPRO_VALUES, 'rates');
                foreach ($labels as $k => $label) {
                    $index = $answers[$k];
                    $output[] = $label.SURVEYPROFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                $rates = $this->item_get_content_array(SURVEYPRO_LABELS, 'rates');
                foreach ($labels as $k => $label) {
                    $index = $answers[$k];
                    $output[] = $label.SURVEYPROFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSPOSITION:
                // Here I will ALWAYS HAVE 0;1;6;4;0;7 so each separator is welcome, even ','.
                // I do not like pass the idea that ',' can be a separator so, I do not use it.
                $return = $content;
                break;
            default:
                $message = 'Unexpected $format = '.$format;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
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
        $elementnames = array();

        $options = surveypro_textarea_to_array($this->options);
        if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
            foreach ($options as $k => $option) {
                $elementnames[] = $this->itemname.'_'.$k.'_group';
            }
        }

        if ($this->style == SURVEYPROFIELD_RATE_USESELECT) {
            foreach ($options as $k => $option) {
                $elementnames[] = $this->itemname.'_'.$k;
            }
        }

        if (!$this->required) {
            $elementnames[] = $this->itemname.'_noanswer';
        }

        return $elementnames;
    }
}
