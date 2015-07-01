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
require_once($CFG->dirroot.'/mod/surveypro/field/checkbox/lib.php');

class mod_surveypro_field_checkbox extends mod_surveypro_itembase {

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
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEYPRO_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $noanswerdefault = include noanswer among defaults
     */
    public $noanswerdefault = '';

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $minimumrequired = The minimum number of checkboxes the user is forced to choose in his/her answer
     */
    public $minimumrequired = 0;

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
        $this->plugin = 'checkbox';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = true;

        // other element specific properties
        // nothing

        // override properties depending from $surveypro settings
        // nothing

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['required'] = false;

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
        // drop empty rows and trim trailing spaces from each textarea field
        $fieldlist = array('options', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // override few values
        $record->noanswerdefault = isset($record->noanswerdefault) ? 1 : 0;
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
     * item_list_constraints
     * this method prepare the list of constraints the child has to respect in order to create a valid relation
     *
     * @param none
     * @return list of contraints of the plugin (as parent) in text format
     */
    public function item_list_constraints() {
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $constraints = array();

        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $optionstr = get_string('option', 'surveyprofield_checkbox');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
        }
        if (!empty($this->labelother)) {
            $constraints[] = get_string('labelother', 'surveyprofield_checkbox').$labelsep.get_string('allowed', 'surveyprofield_checkbox');
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
        $fieldlist['checkbox'] = array('options', 'defaultvalue');

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
    <xs:element name="surveyprofield_checkbox">
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
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="labelother" minOccurs="0"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="noanswerdefault"/>
                <xs:element type="xs:int" name="downloadformat"/>
                <xs:element type="xs:int" name="minimumrequired"/>
                <xs:element type="xs:int" name="adjustment"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK get

    /**
     * get_required
     *
     * @param none
     * @return bool
     */
    public function get_required() {
        if (empty($this->minimumrequired)) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * get_requiredfieldname
     *
     * @return string the name of the database table field specifying if the item is required
     */
    public static function get_requiredfieldname() {
        return 'minimumrequired';
    }

    // MARK set

    /**
     * set_required
     *
     * @param $value
     * @return
     */
    public function set_required($value) {
        global $DB;

        $DB->set_field('surveypro'.$this->type.'_'.$this->plugin, 'minimumrequired', $value, array('itemid' => $this->itemid));
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

        $childparentvalue = array_fill(0, count($values), 0);
        $labels = array();
        foreach ($parentcontents as $parentcontent) {
            $key = array_search($parentcontent, $values);
            if ($key !== false) {
                $childparentvalue[$key] = 1;
            } else {
                // only garbage, but user wrote it
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
                if ($parentvalues[$i] == '1') {
                    if (isset($values[$i])) {
                        $childparentcontent[] = $values[$i];
                    } else {
                        $childparentcontent[] = 1;
                    }
                }
            }

            $key++;
            // only garbage after the first index, but user wrote it
            for ($i = $key; $i < $actualcount; $i++) {
                $childparentcontent[] = $parentvalues[$i];
            }
        } else {
            foreach ($parentvalues as $k => $parentvalue) {
                if ($parentvalue == '1') {
                    if (isset($values[$k])) {
                        $childparentcontent[] = $values[$k];
                    } else {
                        $childparentcontent[] = $k;
                    }
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
        $expectedcount = count($values);
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $condition = empty($this->labelother) ? empty($parentvalues[$actualcount - 1]) : true;
            $condition = $condition && ($actualcount == ($key + 2)); // only one label is allowed
            $condition = $condition && ($expectedcount == $key); // only $expectedcount checkboxes are allowed
            $return = ($condition) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
        } else {
            $return = ($actualcount == $expectedcount) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
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

        $idprefix = 'id_surveypro_field_checkbox_'.$this->sortindex;

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $defaults = surveypro_textarea_to_array($this->defaultvalue);

        $paramelement = array('class' => 'indent-'.$this->indent, 'group' => 1);

        $elementgroup = array();
        $i = 0;
        foreach ($labels as $value => $label) {
            $uniqueid = $this->itemname.'_'.$i;
            $paramelement['id'] = $idprefix.'_'.$i;
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $uniqueid, '', $label, $paramelement, array('0', '1'));

            if ($this->adjustment == SURVEYPRO_HORIZONTAL) {
                unset($paramelement['class']);
            }

            if (!$searchform) {
                if (in_array($label, $defaults)) {
                    $mform->setDefault($uniqueid, '1');
                }
            }
            $i++;
        }
        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $paramelement['id'] = $idprefix.'_other';
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $this->itemname.'_other', '', $otherlabel, $paramelement, array('0', '1'));

            unset($paramelement['group']);
            $paramelement['id'] = $idprefix.'_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '', $paramelement);
            $mform->setType($this->itemname.'_text', PARAM_RAW);

            if (!$searchform) {
                $mform->setDefault($this->itemname.'_text', $othervalue);
                if (in_array($othervalue, $defaults)) {
                    $mform->setDefault($this->itemname.'_other', '1');
                }
            }
            $mform->disabledIf($this->itemname.'_text', $this->itemname.'_other', 'notchecked');
        }

        if (!$this->minimumrequired) {
            $paramelement['group'] = 1;
            $paramelement['id'] = $idprefix.'_noanswer';
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'surveypro'), $paramelement, array('0', '1'));
            if (!empty($this->noanswerdefault)) {
                $mform->setDefault($this->itemname.'_noanswer', '1');
            }
        }

        if ($this->adjustment == SURVEYPRO_VERTICAL) {
            if (count($labels) > 1) {
                $separator = array_fill(0, count($labels) - 1, '<br />');
            } else {
                $separator = array();
            }
            if (!empty($this->labelother)) {
                $separator[] = '<br />';
                $separator[] = ' ';
            }
            if (!$this->minimumrequired) {
                $separator[] = '<br />';
            }
        } else { // SURVEYPRO_HORIZONTAL
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        if (!$this->minimumrequired) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        if ($searchform) {
            $this->item_add_color_unifier($mform);
            $paramelement['id'] = $idprefix.'_ignoreme';
            $mform->addElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'surveypro'), $paramelement);
            $mform->setDefault($this->itemname.'_ignoreme', '1');

            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
        }

        if (!$searchform) {
            if ($this->minimumrequired) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position == SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_group' : $this->itemname.'_extrarow';
                $mform->_required[] = $starplace;
            }
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

        $errorkey = $this->itemname.'_group';

        if (!empty($this->labelother)) {
            if (($data[$this->itemname.'_other']) && empty($data[$this->itemname.'_text']) ) {
                $errors[$errorkey] = get_string('missingothertext', 'surveyprofield_checkbox');
                return;
            }
        }

        if ($this->minimumrequired) {
            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

            $answercount = 0;
            foreach ($labels as $k => $label) {
                $uniqueid = $this->itemname.'_'.$k;
                if ($data[$uniqueid]) { // they are advanced checkbox
                    $answercount++;
                }
            }

            if (!empty($this->labelother)) {
                if (($data[$this->itemname.'_other']) && (!empty($data[$this->itemname.'_text']))) {
                    $answercount++;
                }
            }

            if ($answercount < $this->minimumrequired) {
                if ($this->minimumrequired == 1) {
                    $errors[$errorkey] = get_string('lowerthanminimum_one', 'surveyprofield_checkbox');
                } else {
                    $errors[$errorkey] = get_string('lowerthanminimum_more', 'surveyprofield_checkbox', $this->minimumrequired);
                }
            }
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
            foreach ($indexsubset as $k => $index) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_'.$k;
                if ($indexsubset[$k] == 1) {
                    $mformelementinfo->content = 'notchecked';
                } else {
                    $mformelementinfo->content = 'checked';
                }
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        if ($labelsubset) {
            foreach ($labelsubset as $k => $label) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_other';
                $mformelementinfo->content = 'notchecked';
                $disabilitationinfo[] = $mformelementinfo;

                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_text';
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $label;
                $disabilitationinfo[] = $mformelementinfo;
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
        // 1) I am a checkbox item
        // 2) in $data I can ONLY find $this->itemname, $this->itemname.'_other', $this->itemname.'_text'

        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 2;3;shark

        $status = true;
        foreach ($labels as $k => $label) {
            $key = array_search($k, $parentvalues);
            if ($key !== false) {
                $status = $status && (isset($data[$this->itemname.'_'.$k]));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_'.$k]));
            }
        }
        if (!empty($this->labelother)) {
            if (array_search($this->itemname.'_text', $parentvalues) !== false) {
                $status = $status && (isset($data[$this->itemname.'_check']));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_check']));
            }
        }

        return $status;
    }

    /**
     * userform_get_filling_instructions
     *
     * @param none
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        if ($this->minimumrequired) {
            if ($this->minimumrequired == 1) {
                $fillinginstruction = get_string('restrictions_minimumrequired_one', 'surveyprofield_checkbox');
            } else {
                $fillinginstruction = get_string('restrictions_minimumrequired_more', 'surveyprofield_checkbox', $this->minimumrequired);
            }
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
        if (isset($answer['ignoreme']) && ($answer['ignoreme'] == 1)) { // it ia an advcheckbox
            $olduseranswer->content = null;
            return;
        }

        if (isset($answer['noanswer']) && ($answer['noanswer'] == 1)) { // it ia an advcheckbox
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        $return = $answer;
        if (!empty($this->labelother)) {
            $return[] = isset($answer['other']) ? $answer['text'] : '';
            unset($return['other']);
            unset($return['text']);
        }
        if (!$this->minimumrequired) {
            unset($return['noanswer']);
        }
        $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $return);
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

        if (isset($fromdb->content)) { // I made some selection
            // count of answers is == count of checkboxes
            $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);

            // if SURVEYPRO_NOANSWERVALUE is returned...
            //  it will be alone and a special prefill needs to be returned
            if ($answers == array(SURVEYPRO_NOANSWERVALUE)) {
                $uniqueid = $this->itemname.'_noanswer';
                $prefill[$uniqueid] = '1';

                return $prefill;
            }

            // here $answers is an array like: array(1,1,0,0,'dummytext')
            foreach ($answers as $k => $checkboxvalue) {
                $uniqueid = $this->itemname.'_'.$k;
                $prefill[$uniqueid] = $checkboxvalue;
            }
            if (!empty($this->labelother)) {
                // delete last item of $prefill
                unset($prefill[$uniqueid]);

                // add last element of the $prefill
                $lastanswer = end($answers);

                if (strlen($lastanswer)) {
                    $prefill[$this->itemname.'_other'] = 1;
                    $prefill[$this->itemname.'_text'] = $lastanswer;
                } else {
                    $prefill[$this->itemname.'_other'] = 0;
                    $prefill[$this->itemname.'_text'] = '';
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
        // content
        $content = $answer->content;
        if ($content === SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
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

        // output
        // $answers is an array like: array(1,1,0,0,'dummytext')
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if ($answers[$k] == 1) {
                        $output[] = $value;
                    }
                }
                if (!empty($this->labelother)) {
                    $value = end($answers);
                    if (!empty($value)) {
                        $output[] = $value; // last element of the array $answers
                    }
                }

                if (!empty($output)) {
                    $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                } else {
                    $return = get_string('emptyanswer', 'surveypro');
                }
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if ($answers[$k] == 1) {
                        $output[] = $value;
                    }
                }
                if (!empty($this->labelother)) {
                    $value = end($answers);
                    if (!empty($value)) {
                        $output[] = $value; // last element of the array $answers
                    }
                }

                if (!empty($output)) {
                    $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                } else {
                    $return = get_string('emptyanswer', 'surveypro');
                }
                break;
            case SURVEYPRO_ITEMRETURNSPOSITION:
                // here I will ALWAYS HAVE 0/1 so each separator is welcome, even ','
                // I do not like pass the idea that ',' can be a separator so, I do not use it
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
        $elementnames = array();
        $elementnames[] = $this->itemname.'_group';

        return $elementnames;
    }
}
