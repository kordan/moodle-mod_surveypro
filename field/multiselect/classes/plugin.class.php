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
 * This file contains the mod_surveypro_field_multiselect
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/multiselect/lib.php');

/**
 * Class to manage each aspect of the multiselect item
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_field_multiselect extends mod_surveypro_itembase {

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
     * @var string $variable,  the name of the field storing data in the db table
     */
    protected $variable;

    /**
     * @var int $indent, the indent of the item in the form page
     */
    protected $indent;

    /**
     * $options = list of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    protected $options;

    /**
     * @var string $defaultvalue, the value of the field when the form is initially displayed.
     */
    protected $defaultvalue;

    /**
     * @var string $downloadformat, the format of the content once downloaded
     */
    protected $downloadformat;
    /**
     * $heightinrows = the height of the multiselect in rows
     */
    protected $heightinrows;

    /**
     * $minimumrequired = The minimum number of checkboxes the user is forced to choose in his/her answer
     */
    protected $minimumrequired;

    /**
     * @var bool canbeparent
     */
    protected static $canbeparent = true;

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
        $this->plugin = 'multiselect';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = true;

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
        // Drop empty rows and trim edging rows spaces from each textarea field.
        $fieldlist = array('options', 'defaultvalue');
        $this->item_clean_textarea_fields($record, $fieldlist);

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
     * Traslate values from the mform of this item to values for corresponding properties
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
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Multiple selection';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'multiselect_001';
        $record->indent = 0;
        $record->options = "first\nsecond";
        $record->downloadformat = SURVEYPRO_ITEMRETURNSLABELS;
        $record->minimumrequired = 0;
        $record->heightinrows = 4;
    }

    /**
     * Item_list_constraints
     * this method prepare the list of constraints the child has to respect in order to create a valid relation
     *
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
     * Item_get_friendlyformat
     *
     * @return void
     */
    public function item_get_friendlyformat() {
        return SURVEYPRO_ITEMRETURNSLABELS;
    }

    /**
     * Item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['multiselect'] = array('content', 'options', 'defaultvalue');

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
                <xs:element type="xs:int" name="required"/>
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

    // MARK parent

    /**
     * Parent_encode_child_parentcontent
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
                // Only garbage, but user wrote it.
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
     * @param string $childparentvalue
     * return string $childparentcontent
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
            // Only garbage after the first label, but user wrote it.
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
     * This method, starting from child parentvalue (index/es), declare if the child could be include in the surveypro
     *
     * @param string $childparentvalue
     * @return status of child relation
     *     0 = it will never match
     *     1 = OK
     *     2 = $childparentvalue is malformed
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // See parent method for explanation.

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
     * Define the mform element for the outform and the searchform
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

        $idprefix = 'id_surveypro_field_multiselect_'.$this->sortindex;

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $attributes = array('size' => $this->heightinrows, 'class' => 'indent-'.$this->indent, 'id' => $idprefix);
        if (!$searchform) {
            if ($this->required) {
                $select = $mform->addElement('mod_surveypro_select', $this->itemname, $elementlabel, $labels, $attributes);
                $select->setMultiple(true);
            } else {
                $elementgroup = array();
                $select = $mform->createElement('mod_surveypro_select', $this->itemname, '', $labels, $attributes);
                $select->setMultiple(true);
                $elementgroup[] = $select;

                unset($attributes['size']);
                $attributes['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'mod_surveypro'), $attributes);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '', false);
                // Multiselect uses a special syntax that is different from the syntax of all the other mform groups with disabilitation chechbox
                // $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
                $mform->disabledIf($this->itemname.'[]', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup = array();
            $select = $mform->createElement('mod_surveypro_select', $this->itemname, '', $labels, $attributes);
            $select->setMultiple(true);
            $elementgroup[] = $select;

            if (!$this->required) {
                unset($attributes['size']);
                $attributes['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'mod_surveypro'), $attributes);
            }

            $attributes['id'] = $idprefix.'_ignoreme';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'), $attributes);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
            if (!$this->required) {
                // Multiselect uses a special syntax that is different from the syntax of all the other mform groups with disabilitation chechbox
                // $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
                $mform->disabledIf($this->itemname.'[]', $this->itemname.'_noanswer', 'checked');
            }
            $mform->disabledIf($this->itemname.'[]', $this->itemname.'_ignoreme', 'checked');
            $mform->disabledIf($this->itemname.'_noanswer', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        // Begin of: defaults.
        if (!$searchform) {
            if ($defaults = surveypro_textarea_to_array($this->defaultvalue)) {
                $defaultkeys = array();
                foreach ($defaults as $default) {
                    $defaultkeys[] = array_search($default, $labels);
                }
                $mform->setDefault($this->itemname, $defaultkeys);
            }
        }
        // End of: defaults

        // This last item is needed because:
        // the check for the not empty field is performed in the validation routine (not by JS).
        // For multiselect element, nothing is submitted if no option is selected
        // so, if the user neglects the mandatory multiselect AT ALL, it is not submitted and, as conseguence, not validated.
        // TO ALWAYS SUBMIT A MULTISELECT I add a dummy hidden item.
        //
        // TAKE CARE: I choose a name for this item that IS UNIQUE BUT is missing the SURVEYPRO_ITEMPREFIX.'_'.
        // In this way I am sure the item will never be saved to the database.
        $placeholderitemname = SURVEYPRO_DONTSAVEMEPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';
        $mform->addElement('hidden', $placeholderitemname, 1);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // -> I do not want JS form validation if the page is submitted through the "previous" button.
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
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
        if (isset($data[$this->itemname.'_noanswer']) && ($data[$this->itemname.'_noanswer'] == 1) ) {
            return; // Nothing to validate.
        }

        $errorkey = $this->required ? $this->itemname : $this->itemname.'_group';

        // I don't care if this element is required or not.
        // If the user provides an answer, it has to be compliant with the field validation rules.
        $answercount = (isset($data[$this->itemname])) ? count($data[$this->itemname]) : 0;
        if ($answercount < $this->minimumrequired) {
            if ($this->minimumrequired == 1) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_one', 'surveyprofield_multiselect');
            } else {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_more', 'surveyprofield_multiselect', $this->minimumrequired);
            }
        }
    }

    /**
     * From childparentvalue defines syntax for disabledIf.
     *
     * @param string $childparentvalue
     * @return array
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
            // Only garbage, but user wrote it.
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
     * Userform_child_item_allowed_dynamic
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
        foreach ($labels as $k => $unused) {
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
     * Prepare the string with the filling instruction
     *
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
        if (isset($answer['noanswer'])) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        if (!isset($answer['mainelement'])) { // Only placeholder arrived here.
            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, array_fill(1, count($labels), '0'));
        } else {
            // $answer is an array with the keys of the selected elements
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $answer['mainelement']);
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
                $prefill[$this->itemname.'_noanswer'] = '1';
            } else {
                $preset = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);
                $prefill[$this->itemname] = $preset;
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
        $content = $answer->content;
        // SURVEYPRO_NOANSWERVALUE does not exist here
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
                $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if (isset($answers[$k])) {
                        $output[] = $value;
                    }
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                $standardanswerscount = count($values);
                foreach ($values as $k => $value) {
                    if (isset($answers[$k])) {
                        $output[] = $value;
                    }
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSPOSITION:
                // Here I will ALWAYS HAVE 0/1 so each separator is welcome, even ','.
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
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname.'[]';
        $elementnames[] = SURVEYPRO_DONTSAVEMEPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';

        return $elementnames;
    }
}
