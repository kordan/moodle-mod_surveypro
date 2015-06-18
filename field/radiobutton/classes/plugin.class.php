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
require_once($CFG->dirroot.'/mod/surveypro/field/radiobutton/lib.php');

class mod_surveypro_field_radiobutton extends mod_surveypro_itembase {

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
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /**
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    // -----------------------------

    /**
     * $options = list of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /**
     * $defaultoption
     */
    public $defaultoption = SURVEYPRO_INVITATIONDEFAULT;

    /**
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEYPRO_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $adjustment = the orientation of the list of options.
     */
    public $adjustment = 0;

    /**
     * static canbeparent
     */
    public static $canbeparent = true;

    // -----------------------------

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param stdClass $cm
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent: add also 'parentcontent' among other item elements
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'radiobutton';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = true;

        // other element specific properties
        // nothing

        // override properties depending from $surveypro settings
        // nothing

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent: add also 'parentcontent' among other item elements
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

        // begin of: plugin specific settings (eventally overriding general ones)
        // drop empty rows and trim trialing spaces from each row of each textarea field
        $fieldlist = array('options');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        $record->hideinstructions = 1;
        // end of: plugin specific settings (eventally overriding general ones)

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
        // nothing to do: they don't exist in this plugin

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
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        if ($record->defaultoption != SURVEYPRO_CUSTOMDEFAULT) {
            $record->defaultvalue = null;
        }
    }

    /**
     * item_generate_standard_default
     * sets record field to store the correct value to db for the date custom item
     *
     * @param none
     * @return
     */
    public function item_generate_standard_default() {
        $optionarray = surveypro_textarea_to_array($this->options);
        $firstoption = reset($optionarray);

        if (preg_match('~^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$~', $firstoption, $match)) { // do not warn: it can never be equal to zero
            // print_object($match);
            $default = $match[1];
        } else {
            $default = $firstoption;
        }

        return $default;
    }

    /**
     * item_list_constraints
     * this method prepare the list of constraints the child has to respect in order to create a valid relation
     *
     * @param none
     * @return list of contraints of the plugin (as parent) in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $optionstr = get_string('option', 'surveyprofield_radiobutton');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyprofield_radiobutton').$labelsep.get_string('allowed', 'surveyprofield_radiobutton');
        }

        return implode($constraints, '<br />');
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
        $fieldlist['radiobutton'] = array('content', 'options', 'labelother', 'defaultvalue');

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
    <xs:element name="surveyprofield_radiobutton">
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
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="labelother" minOccurs="0"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="downloadformat"/>
                <xs:element type="xs:int" name="adjustment"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK parent

    /**
     * parent_encode_child_parentcontent
     *
     * this method is called ONLY at item save time
     * it encodes the child parentcontent to parentindex
     *
     * @param $childparentcontent
     * return childparentvalue
     */
    public function parent_encode_child_parentcontent($childparentcontent) {
        $parentcontents = array_unique(surveypro_textarea_to_array($childparentcontent));
        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');

        $childparentvalue = array();
        $labels = array();
        foreach ($parentcontents as $parentcontent) {
            $key = array_search($parentcontent, $values);
            if ($key !== false) {
                $childparentvalue[] = $key;
            } else {
                $labels[] = $parentcontent;
            }
        }
        if (!empty($labels)) {
            $childparentvalue[] = '>';
            $childparentvalue = array_merge($childparentvalue, $labels);
        }

        return implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
    }

    /**
     * parent_decode_child_parentvalue
     *
     * I can not make ANY assumption about $childparentvalue because of the following explanation:
     * At child save time, I encode its $parentcontent to $parentvalue.
     * The encoding is done through a parent method according to parent values.
     * Once the child is saved, I can return to parent and I can change it as much as I want.
     * For instance by changing the number and the content of its options.
     * At parent save time, the child parentvalue is rewritten
     * -> but it may result in a too short or too long list of keys
     * -> or with a wrong number of unrecognized keys so I need to...
     * ...implement all possible checks to avoid crashes/malfunctions during code execution.
     *
     * this method decodes parentindex to parentcontent
     *
     * @param $childparentvalue
     * return $childparentcontent
     */
    public function parent_decode_child_parentvalue($childparentvalue) {

        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $childparentcontent = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            for ($i = 0; $i < $key; $i++) {
                $k = $parentvalues[$i];
                if (isset($values[$k])) {
                    $childparentcontent[] = $values[$k];
                } else {
                    $childparentcontent[] = $k;
                }
            }

            $key++;
            // only garbage after the first label, but user wrote it
            for ($i = $key; $i < $actualcount; $i++) {
                $childparentcontent[] = $parentvalues[$i];
            }
        } else {
            foreach ($parentvalues as $parentvalue) {
                if (isset($values[$parentvalue])) {
                    $childparentcontent[] = $values[$parentvalue];
                } else {
                    $childparentcontent[] = $parentvalue;
                }
            }
        }

        return implode("\n", $childparentcontent);
    }

    /**
     * parent_validate_child_constraints
     *
     * this method, starting from child parentvalue (index/es), declare if the child could be include in the surveypro
     *
     * @param $childparentvalue
     * @return status of child relation
     *     0 = it will never match
     *     1 = OK
     *     2 = $childparentvalue is malformed
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // see parent method for explanation

        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            if ($actualcount == 2) {
                $return = empty($this->labelother) ? SURVEYPRO_CONDITIONNEVERMATCH : SURVEYPRO_CONDITIONOK;
            } else {
                $return = SURVEYPRO_CONDITIONMALFORMED;
            }
        } else {
            if ($actualcount == 1) {
                $k = $parentvalues[0];
                $return = isset($values[$k]) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONNEVERMATCH;
            } else {
                $return = SURVEYPRO_CONDITIONMALFORMED;
            }
        }

        return ($return);
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

        $idprefix = 'id_surveypro_field_radiobutton_'.$this->sortindex;

        $paramelement = array('class' => 'indent-'.$this->indent);
        $elementgroup = array();

        // mform elements
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                $paramelement['id'] = $idprefix.'_invitation';
                $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('choosedots'), SURVEYPRO_INVITATIONVALUE, $paramelement);
                if ($this->adjustment == SURVEYPRO_HORIZONTAL) {
                    unset($paramelement['class']);
                }
            }
        } else {
            $paramelement['id'] = $idprefix.'_ignoreme';
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('star', 'surveypro'), SURVEYPRO_IGNOREMEVALUE, $paramelement);
            if ($this->adjustment == SURVEYPRO_HORIZONTAL) {
                unset($paramelement['class']);
            }
        }

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $labelcount = count($labels);
        foreach ($labels as $k => $label) {
            $paramelement['id'] = $idprefix.'_'."$k";
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', $label, "$k", $paramelement);
            if ($this->adjustment == SURVEYPRO_HORIZONTAL) {
                unset($paramelement['class']);
            }
        }

        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();
            $labels['other'] = $othervalue;

            $paramelement['id'] = $idprefix.'_other';
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', $otherlabel, 'other', $paramelement);

            $paramelement['id'] = $idprefix.'_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '', $paramelement);
            $mform->setType($this->itemname.'_text', PARAM_RAW);
            $mform->disabledIf($this->itemname.'_text', $this->itemname, 'neq', 'other');
        }

        if (!$this->required) {
            $paramelement['id'] = $idprefix.'_noanswer';
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('noanswer', 'surveypro'), SURVEYPRO_NOANSWERVALUE, $paramelement);
        }
        // End of: mform elements

        // definition of separator
        if ($this->adjustment == SURVEYPRO_VERTICAL) {
            $separator = array_fill(0, $labelcount - 1, '<br />');
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                array_unshift($separator, '<br />');
            }
            if (!empty($this->labelother)) {
                $separator[] = '<br />';
                $separator[] = ' ';
            }
            if (!$this->required) {
                $separator[] = '<br />';
            }
        } else { // SURVEYPRO_HORIZONTAL
            $separator = ' ';
        }
        // End of: definition of separator
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        // default section
        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }

            switch ($this->defaultoption) {
                case SURVEYPRO_CUSTOMDEFAULT:
                    $key = array_search($this->defaultvalue, $labels);
                    if ($key !== false) {
                        $mform->setDefault($this->itemname, "$key");
                    }
                    break;
                case SURVEYPRO_INVITATIONDEFAULT:
                    $mform->setDefault($this->itemname, SURVEYPRO_INVITATIONVALUE);
                    break;
                case SURVEYPRO_NOANSWERDEFAULT:
                    $mform->setDefault($this->itemname, SURVEYPRO_NOANSWERVALUE);
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption, DEBUG_DEVELOPER);
            }
        } else {
            $mform->setDefault($this->itemname, SURVEYPRO_IGNOREMEVALUE);
        }
        // $this->itemname.'_text' has to ALWAYS get a default (if it exists) even if it is not selected
        if (!empty($this->labelother)) {
            $mform->setDefault($this->itemname.'_text', $othervalue);
        }
        // End of: default section
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
        // this plugin displays as a set of radio buttons. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if ($searchform) {
            return;
        }

        $errorkey = $this->itemname.'_group';

        if ( ($data[$this->itemname] == 'other') && empty($data[$this->itemname.'_text']) ) {
            $errors[$errorkey] = get_string('missingothertext', 'surveyprofield_radiobutton');
            return;
        }

        // I need to check value is different from SURVEYPRO_INVITATIONVALUE even if it is not required
        if ($data[$this->itemname] == SURVEYPRO_INVITATIONVALUE) {
            $errors[$errorkey] = get_string('uerr_optionnotset', 'surveyprofield_radiobutton');
            return;
        }
    }

    /**
     * userform_get_parent_disabilitation_info
     * from childparentvalue defines syntax for disabledIf
     *
     * @param: $childparentvalue
     * @return
     */
    public function userform_get_parent_disabilitation_info($childparentvalue) {
        $disabilitationinfo = array();

        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 1;1;0;

        $indexsubset = array();
        $labelsubset = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $indexsubset = array_slice($parentvalues, 0, $key);
            $labelsubset = array_slice($parentvalues, $key + 1);
        } else {
            $indexsubset = $parentvalues;
        }

        if ($indexsubset) {
            // only garbage after the first index, but user wrote it
            foreach ($indexsubset as $k => $index) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname;
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $index;
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        if ($labelsubset) {
            foreach ($labelsubset as $k => $label) {
                // only garbage after the first label, but user wrote it
                if (!empty($this->labelother)) {
                    $mformelementinfo = new stdClass();
                    $mformelementinfo->parentname = $this->itemname;
                    $mformelementinfo->operator = 'neq';
                    $mformelementinfo->content = 'other';
                    $disabilitationinfo[] = $mformelementinfo;

                    $mformelementinfo = new stdClass();
                    $mformelementinfo->parentname = $this->itemname.'_text';
                    $mformelementinfo->operator = 'neq';
                    $mformelementinfo->content = $label;
                    $disabilitationinfo[] = $mformelementinfo;
                }
            }
        } else {
            // even if no labels were provided
            // I have to add one more $disabilitationinfo if $this->other is not empty
            if (!empty($this->labelother)) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_other';
                $mformelementinfo->content = 'checked';
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        return $disabilitationinfo;
    }

    /**
     * userform_child_item_allowed_dynamic
     * this method is called if (and only if) parent item and child item live in the same form page
     * this method has two purposes:
     * - stop userpageform item validation
     * - drop unexpected returned values from $userpageform->formdata
     *
     * as parentitem declare whether my child item is allowed to return a value (is enabled) or is not (is disabled)
     *
     * @param string $childparentvalue
     * @param array $data
     * @return boolean: true: if the item is welcome; false: if the item must be dropped out
     */
    public function userform_child_item_allowed_dynamic($childparentvalue, $data) {
        // 1) I am a radiobutton item
        // 2) in $data I can ONLY find $this->itemname, $this->itemname.'_text'

        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        // $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 2 OR shark

        if ( ($this->labelother) && ($data[$this->itemname] == count($labels)) ) {
            return ($data[$this->itemname.'_text'] = $childparentvalue);
        } else {
            return ($data[$this->itemname] = $childparentvalue);
        }
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
        if (isset($answer['mainelement'])) {
            switch ($answer['mainelement']) {
                case 'other':
                    $olduseranswer->content = $answer['text'];
                    break;
                case '':
                    $olduseranswer->content = null;
                    break;
                default:
                    $olduseranswer->content = $answer['mainelement'];
                    break;
            }
            return;
        }

        $a = '$answer = '.$answer;
        print_error('unhandledvalue', 'surveypro', null, $a);
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

        if (isset($fromdb->content)) {
            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
            if (array_key_exists($fromdb->content, $labels)) {
                $prefill[$this->itemname] = $fromdb->content;
            } else {
                if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                    $prefill[$this->itemname] = SURVEYPRO_NOANSWERVALUE;
                } else {
                    // it is, for sure, the content of _text
                    $prefill[$this->itemname] = 'other';
                    $prefill[$this->itemname.'_text'] = $fromdb->content;
                }
            }
        } else {
            // nothing was set
            // do not accept defaults but overwrite them
            // but... if this is a group of radio buttons, how can it be empty($fromdb->content)?
            // Because user selected "Not answering" or question was disabled
            $prefill[$this->itemname] = '';
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
        $content = $answer->content;
        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'surveypro');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'surveypro');
        }

        // format
        if ($format == SURVEYPRO_FIRENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
                if (array_key_exists($content, $values)) {
                    $return = $values[$content];
                } else {
                    $return = $content;
                }
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
                if (array_key_exists($content, $values)) {
                    $return = $values[$content];
                } else {
                    $return = $content;
                }
                break;
            case SURVEYPRO_ITEMRETURNSPOSITION:
                $return = $content;
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $format = '.$format, DEBUG_DEVELOPER);
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
        $elementnames = array($this->itemname.'_group');

        return $elementnames;
    }
}
