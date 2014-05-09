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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/checkbox/lib.php');

class surveyprofield_checkbox extends mod_surveypro_itembase {

    /*
     * $content = the text content of the item.
     */
    public $content = '';

    /*
     * $contenttrust
     */
    public $contenttrust = 1;

    /*
     * public $contentformat = '';
     */
    public $contentformat = '';

    /*
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    /*
     * $position = where does the question go?
     */
    public $position = SURVEYPRO_POSITIONLEFT;

    /*
     * $extranote = an optional text describing the item
     */
    public $extranote = '';

    /*
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /*
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /*
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    // -----------------------------

    /*
     * $options = list of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    public $options = '';

    /*
     * $labelother = the text label for the optional option "other" in the form of "$value SURVEYPRO_OTHERSEPARATOR $label"
     */
    public $labelother = '';

    /*
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /*
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /*
     * $minimumrequired = The minimum number of checkboxes the user is forced to choose in his/her answer
     */
    public $minimumrequired = 0;

    /*
     * $adjustment = the orientation of the list of options.
     */
    public $adjustment = 0;

    /*
     * $flag = features describing the object
     */
    public $flag;

    /*
     * $canbeparent
     */
    public static $canbeparent = true;

    // -----------------------------

    /*
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional surveypro_item ID
     */
    public function __construct($itemid=0, $evaluateparentcontent) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whether this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'checkbox';

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA);
        $this->flag->savepositiontodb = true;

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['required'] = false;
        $this->isinitemform['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /*
     * item_load
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

        // multilang load support for builtin surveypro
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();
    }

    /*
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
        // drop empty rows and trim trailing spaces from each textarea field
        $fieldlist = array('options', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

        // override few values
        $record->hideinstructions = 1;
        if ($record->minimumrequired > 0) {
            $record->required = 1;
        } else {
            $record->required = 0;
        }
        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /*
     * item_list_constraints
     * this method prepare the list of constraints the child has to respect in order to create a valid relation
     *
     * @return list of contraints of the plugin (as patent) in text format
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

    /*
     * item_get_friendlyformat
     *
     * @return
     */
    public function item_get_friendlyformat() {
        return SURVEYPRO_ITEMRETURNSLABELS;
    }

    /*
     * item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['checkbox'] = array('options', 'defaultvalue');

        return $fieldlist;
    }

    /*
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
                <xs:element type="xs:int" name="required"/>
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:string" name="options"/>
                <xs:element type="xs:string" name="labelother" minOccurs="0"/>
                <xs:element type="xs:string" name="defaultvalue" minOccurs="0"/>
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

    // MARK set

    /*
     * set_required
     *
     * @return
     */
    public function set_required($value) {
        global $DB;

        parent::set_required($value);
        $DB->set_field('surveypro'.$this->type.'_'.$this->plugin, 'minimumrequired', $value, array('itemid' => $this->itemid));
    }

    // MARK parent

    /*
     * parent_encode_child_parentcontent
     *
     * this method is called ONLY at item save time
     * it encodes the child parentcontent to parentindex
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

    /*
     * parent_decode_child_parentvalue
     *
     * this method decodes parentindex to parentcontent
     * @param $childparentvalue
     * return $childparentcontent
     */
    public function parent_decode_child_parentvalue($childparentvalue) {
        /*
         * I can not make ANY assumption about $childparentvalue because of the following explanation:
         * At child save time, I encode its $parentcontent to $parentvalue.
         * The encoding is done through a parent method according to parent values.
         * Once the child is saved, I can return to parent and I can change it as much as I want.
         * For instance by changing the number and the content of its options.
         * At parent save time, the child parentvalue is rewritten
         * -> but it may result in a too short or too long list of keys
         * -> or with a wrong number of unrecognized keys so I need to...
         * ...implement all possible checks to avoid crashes/malfunctions during code execution.
         */

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

    /*
     * parent_validate_child_constraints
     *
     * this method, starting from child parentvalue (index/es), declare if the child could be include in the surveypro
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
            $condition = empty($this->labelother) ? empty($parentvalues[$actualcount-1]) : true;
            $condition = $condition && ($actualcount == ($key+2)); // only one label is allowed
            $condition = $condition && ($expectedcount == $key); // only $expectedcount checkboxes are allowed
            $return = ($condition) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
        } else {
            $return = ($actualcount == $expectedcount) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
        }

        return ($return);
    }

    // MARK userform

    /*
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

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $defaults = surveypro_textarea_to_array($this->defaultvalue);

        $firstclass = array('class' => 'indent-'.$this->indent, 'group' => 1);
        $class = ($this->adjustment == SURVEYPRO_VERTICAL) ? $firstclass : array('group' => 1);

        $elementgroup = array();
        $i = 0;
        foreach ($labels as $value => $label) {
            $uniqueid = $this->itemname.'_'.$i;
            $maybeclass = (count($elementgroup)) ? $class : $firstclass;
            $elementgroup[] = $mform->createElement('advcheckbox', $uniqueid, '', $label, $maybeclass, array('0', '1'));

            if (!$searchform) {
                if (in_array($label, $defaults)) {
                    $mform->setDefault($uniqueid, '1');
                }
            }
            $i++;
        }
        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $elementgroup[] = $mform->createElement('advcheckbox', $this->itemname.'_other', '', $otherlabel, $class, array('0', '1'));
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '');
            $mform->setType($this->itemname.'_text', PARAM_RAW);

            if (!$searchform) {
                $mform->setDefault($this->itemname.'_text', $othervalue);
                if (in_array($othervalue, $defaults)) {
                    $mform->setDefault($this->itemname.'_other', '1');
                }
            }
            $mform->disabledIf($this->itemname.'_text', $this->itemname.'_other', 'notchecked');
        }

        if (!$this->required) {
            $elementgroup[] = $mform->createElement('advcheckbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'surveypro'), $class, array('0', '1'));
        }

        if ($searchform) {
            unset($class['group']);
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'surveypro'), $class);
        }

        if ($this->adjustment == SURVEYPRO_VERTICAL) {
            if (count($labels) > 1) {
                $separator = array_fill(0, count($labels)-1, '<br />');
            } else {
                $separator = array();
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
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        if (!$this->required) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        if ($searchform) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position == SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_group' : $this->itemname.'_extrarow';
                $mform->_required[] = $starplace;
            }
        }
    }

    /*
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

        if ($this->required) {
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

    /*
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
            $labelsubset = array_slice($parentvalues, $key+1);
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
            if ($this->labelother) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname.'_other';
                $mformelementinfo->content = 'checked';
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        return $disabilitationinfo;
    }

    /*
     * userform_child_item_allowed_dynamic
     * this method is called if (and only if) parent item and child item live in the same form page
     * this method has two purposes:
     * - stop userpageform item validation
     * - drop unexpected returned values from $userpageform->formdata
     *
     * as parentitem declare whether my child item is allowed to return a value (is enabled) or is not (is disabled)
     *
     * @param string $childparentvalue:
     * @param array $data:
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
        if ($this->labelother) {
            if (array_search($this->itemname.'_text', $parentvalues) !== false) {
                $status = $status && (isset($data[$this->itemname.'_check']));
            } else {
                $status = $status && (!isset($data[$this->itemname.'_check']));
            }
        }

        return $status;
    }

    /*
     * userform_get_filling_instructions
     *
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

    /*
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
        if (isset($answer['ignoreme']) && ($answer['ignoreme'] == 1)) { // it ia an advcheckbox
            $olduserdata->content = null;
            return;
        }

        if (isset($answer['noanswer']) && ($answer['noanswer'] == 1)) { // it ia an advcheckbox
            $olduserdata->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        $return = $answer;
        if (!empty($this->labelother)) {
            $return[] = isset($answer['other']) ? $answer['text'] : '';
            unset($return['other']);
            unset($return['text']);
        }
        if (!$this->required) {
            unset($return['noanswer']);
        }
        $olduserdata->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $return);
    }

    /*
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

            // here $answers is an array like: array(1,1,0,0,'dummytext')
            foreach ($answers as $k => $checkboxvalue) {
                $uniqueid = $this->itemname.'_'.$k;
                $prefill[$uniqueid] = $checkboxvalue;
            }
            if ($this->labelother) {
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

    /*
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

        // output
        // $answers is an array like: array(1,1,0,0,'dummytext')
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

    /*
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname.'_group';

        return $elementnames;
    }
}
