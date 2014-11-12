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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/multiselect/lib.php');

class mod_surveypro_field_multiselect extends mod_surveypro_itembase {

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
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $heightinrows = the height of the multiselect in rows
     */
    public $heightinrows = 4;

    /**
     * $minimumrequired = The minimum number of checkboxes the user is forced to choose in his/her answer
     */
    public $minimumrequired = 0;

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
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'multiselect';
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
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
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

        // begin of: plugin specific settings (eventally overriding general ones)
        // drop empty rows and trim edging rows spaces from each textarea field
        $fieldlist = array('options', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // override few values
        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
        return parent::item_save($record);
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
        $optionstr = get_string('option', 'surveyprofield_multiselect');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
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
        $fieldlist['multiselect'] = array('content', 'options', 'defaultvalue');

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
    <xs:element name="surveyprofield_multiselect">
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
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="minimumrequired"/>
                <xs:element type="xs:int" name="heightinrows"/>
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

        $childparentvalue = array();
        $labels = array();
        foreach ($parentcontents as $parentcontent) {
            $key = array_search($parentcontent, $values);
            if ($key !== false) {
                $childparentvalue[] = $key;
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
        $optioncount = count($values);
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $return = ($actualcount <= $optioncount) ? SURVEYPRO_CONDITIONNEVERMATCH : SURVEYPRO_CONDITIONMALFORMED;
        } else {
            if ($actualcount <= $optioncount) {
                $return = SURVEYPRO_CONDITIONOK;
                foreach ($parentvalues as $parentvalue) {
                    if (!isset($values[$parentvalue])) {
                        $return = SURVEYPRO_CONDITIONNEVERMATCH;
                        break;
                    }
                }
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

        $idprefix = 'id_surveypro_field_multiselect_'.$this->sortindex;

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

        $paramelement = array('size' => $this->heightinrows, 'class' => 'indent-'.$this->indent, 'id' => $idprefix);
        if (!$searchform) {
            if ($this->minimumrequired) {
                $select = $mform->addElement('select', $this->itemname, $elementlabel, $labels, $paramelement);
                $select->setMultiple(true);
            } else {
                $elementgroup = array();
                $select = $mform->createElement('select', $this->itemname, '', $labels, $paramelement);
                $select->setMultiple(true);
                $elementgroup[] = $select;

                unset($paramelement['size']);
                $paramelement['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'surveypro'), $paramelement);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup = array();
            $select = $mform->createElement('select', $this->itemname, '', $labels, $paramelement);
            $select->setMultiple(true);
            $elementgroup[] = $select;

            if (!$this->minimumrequired) {
                unset($paramelement['size']);
                $paramelement['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'surveypro'), $paramelement);
            }

            $paramelement['id'] = $idprefix.'_ignoreme';
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'surveypro'), $paramelement);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
            if (!$this->minimumrequired) {
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            }
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        // defaults
        if (!$searchform) {
            if ($defaults = surveypro_textarea_to_array($this->defaultvalue)) {
                $defaultkeys = array();
                foreach ($defaults as $default) {
                    $defaultkeys[] = array_search($default, $labels);
                }
                $mform->setDefault($this->itemname, $defaultkeys);
            }
            // } else {
            // $mform->setDefault($this->itemname, array());
        }
        // End of: defaults

        // this last item is needed because:
        // the check for the not empty field is performed in the validation routine. (not by JS)
        // (JS validation is never added because I do not want it when the "previous" button is pressed and when an item is disabled even if mandatory)
        // The validation routine is executed ONLY ON ITEM that are actually submitted.
        // For multiselect, nothing is submitted if no item is selected
        // so, if the user neglects the mandatory multiselect AT ALL, it is not submitted and, as conseguence, not validated.
        // TO ALWAYS SUBMIT A MULTISELECT I add a dummy hidden item.
        //
        // TAKE CARE: I choose a name for this item that IS UNIQUE BUT is missing the SURVEYPRO_ITEMPREFIX.'_'
        //            In this way I am sure the item will never be saved in the database
        $placeholderitemname = SURVEYPRO_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';
        $mform->addElement('hidden', $placeholderitemname, 1);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
            if ($this->minimumrequired) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
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

        if ($this->minimumrequired) {
            $errorkey = $this->itemname;

            $answercount = count($data[$this->itemname]);
            if ($answercount < $this->minimumrequired) {
                if ($this->minimumrequired == 1) {
                    $errors[$errorkey] = get_string('lowerthanminimum_one', 'surveyprofield_multiselect');
                } else {
                    $errors[$errorkey] = get_string('lowerthanminimum_more', 'surveyprofield_multiselect', $this->minimumrequired);
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
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'[]';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $indexsubset;
            $disabilitationinfo[] = $mformelementinfo;
            // $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', array(0, 4));
        }

        if ($labelsubset) {
            // only garbage, but user wrote it
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'[]';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $labelsubset;
            $disabilitationinfo[] = $mformelementinfo;
            // $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', array('foo', 'bar'));
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
        // 1) I am a multiselect item
        // 2) in $data I can ONLY find $this->itemname

        // I need to verify (checkbox per checkbox) if they hold the same value the user entered
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 2;3

        $status = true;
        foreach ($labels as $k => $label) {
            $key = array_search($k, $parentvalues);
            if ($key !== false) {
                $status = $status && (isset($data[$this->itemname.'_'.$k]));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_'.$k]));
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
                $fillinginstruction = get_string('restrictions_minimumrequired_one', 'surveyprofield_multiselect');
            } else {
                $fillinginstruction = get_string('restrictions_minimumrequired_more', 'surveyprofield_multiselect', $this->minimumrequired);
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
     * @param $olduserdata
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata, $searchform) {
        if (isset($answer['noanswer'])) {
            $olduserdata->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        if (!isset($answer['mainelement'])) { // only placeholder arrived here
            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
            $olduserdata->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, array_fill(1, count($labels), '0'));
        } else {
            // $answer is an array with the keys of the selected elements
            $olduserdata->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $answer['mainelement']);
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

        if (isset($fromdb->content)) {
            $preset = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);
            $prefill[$this->itemname] = $preset;
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
        // SURVEYPRO_NOANSWERVALUE does not exist here
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

        // $answers is an array like: array(1,1,0,0)
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                if ($format == SURVEYPRO_ITEMSRETURNSVALUES) {
                    $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
                } else { // $format == SURVEYPRO_ITEMRETURNSLABELS
                    $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
                }

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if (isset($answers[$k])) {
                        $output[] = $value;
                    }
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
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
        $elementnames[] = $this->itemname;
        $elementnames[] = SURVEYPRO_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';

        return $elementnames;
    }

    /**
     * get_canbeparent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }
}
