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
 * This file contains the surveyprofield_multiselect
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/multiselect/lib.php');

/**
 * Class to manage each aspect of the multiselect item
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_multiselect_field extends mod_surveypro_itembase {

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
     * @var string Name of the field storing data in the db table
     */
    protected $variable;

    /**
     * @var int Indent of the item in the form page
     */
    protected $indent;

    /**
     * @var string List of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    protected $options;

    /**
     * @var string Value of the field when the form is initially displayed
     */
    protected $defaultvalue;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var int Height of the multiselect in rows
     */
    protected $heightinrows;

    /**
     * @var int Minimum number of checkboxes the user is forced to choose in his/her answer
     */
    protected $minimumrequired;

    /**
     * @var bool Can this item be parent?
     */
    protected static $canbeparent = true;

    /**
     * Class constructor.
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     * If evaluateparentcontent is true, load the parentitem parentcontent property too
     *
     * @param stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid=0, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'multiselect';
        $this->savepositiontodb = true;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['trimonsave'] = false;

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
    }

    /**
     * Item save.
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
     * Is this item available as a parent?
     *
     * @return the content of the static property "canbeparent"
     */
    public static function item_get_canbeparent() {
        return self::$canbeparent;
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
        // Take care: 'required', 'trimonsave', 'hideinstructions' were already considered in item_get_common_settings.
        $checkboxes = array('noanswerdefault');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
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
     * Make the list of constraints the child has to respect in order to create a valid relation
     *
     * @return list of contraints of the plugin (as parent) in text format
     */
    public function item_list_constraints() {
        $constraints = array();

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $optionstr = get_string('option', 'surveyprofield_multiselect');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
        }

        return implode($constraints, '<br />');
    }

    /**
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function item_get_downloadformats() {
        $options = array();

        $options[SURVEYPRO_ITEMSRETURNSVALUES] = get_string('returnvalues', 'surveyprofield_multiselect');
        $options[SURVEYPRO_ITEMRETURNSLABELS] = get_string('returnlabels', 'surveyprofield_multiselect');
        $options[SURVEYPRO_ITEMRETURNSPOSITION] = get_string('returnposition', 'surveyprofield_multiselect');

        return $options;
    }

    /**
     * Get the format recognized (without any really good reason) as friendly.
     *
     * @return the friendly format
     */
    public function item_get_friendlyformat() {
        return SURVEYPRO_ITEMRETURNSLABELS;
    }

    /**
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist[$this->plugin] = array('content', 'extranote', 'options', 'defaultvalue');

        return $fieldlist;
    }

    /**
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
                <!-- <xs:element name="trimonsave" type="xs:int"/> -->

                <xs:element name="options" type="xs:string"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="noanswerdefault" type="xs:int"/>
                <xs:element name="downloadformat" type="xs:string"/>
                <xs:element name="minimumrequired" type="xs:int"/>
                <xs:element name="heightinrows" type="xs:int"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK parent.

    /**
     * Translate the parentcontent of the child item to the corresponding parentvalue.
     *
     * @param string $childparentcontent
     * return string childparentvalue
     */
    public function parent_encode_child_parentcontent($childparentcontent) {
        $parentcontents = array_unique(surveypro_multilinetext_to_array($childparentcontent));
        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');

        $childparentvalue = array_fill(0, count($values), 0);
        $labels = array();
        foreach ($parentcontents as $parentcontent) {
            $key = array_search($parentcontent, $values);
            if ($key !== false) {
                $childparentvalue[$key] = 1;
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
     * For instance by changing the number and the content of its options
     * At parent save time, the child parentvalue is rewritten
     * -> but it may result in a too short or too long list of keys
     * -> or with a wrong number of unrecognized keys so I need to..
     * ...implement all possible checks to avoid crashes/malfunctions during code execution
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
                if ($parentvalues[$i] == '1') {
                    if (isset($values[$i])) {
                        $childparentcontent[] = $values[$i];
                    } else {
                        $childparentcontent[] = 1;
                    }
                }
            }

            $key++;
            // Only garbage after the first label, but user wrote it.
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
     * This method, starting from child parentvalue (index/es), declares if the child could be included in the surveypro.
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

    // MARK userform.

    /**
     * Define the mform element for the outform and the searchform.
     *
     * @param moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $starstr = get_string('star', 'mod_surveypro');
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_multiselect_'.$this->sortindex;

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $attributes = array();
        $attributes['id'] = $idprefix;
        $attributes['class'] = 'indent-'.$this->indent.' multiselect_select';
        $attributes['size'] = $this->heightinrows;
        if (!$searchform) {
            if ($this->required) {
                $select = $mform->addElement('mod_surveypro_select', $this->itemname, $elementlabel, $labels, $attributes);
                $select->setMultiple(true);
            } else {
                $elementgroup = array();
                $select = $mform->createElement('mod_surveypro_select', $this->itemname, '', $labels, $attributes);
                $select->setMultiple(true);
                $elementgroup[] = $select;

                $itemname = $this->itemname.'_noanswer';
                $attributes['id'] = $idprefix.'_noanswer';
                $attributes['class'] = 'multiselect_check';
                unset($attributes['size']);
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $noanswerstr, $attributes);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '', false);
                // Multiselect uses a special syntax
                // that is different from the syntax of all the other mform groups with disabilitation chechbox.
                // $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
                $mform->disabledIf($this->itemname.'[]', $this->itemname.'_noanswer', 'checked');
            }
        } else {
            $elementgroup = array();
            $select = $mform->createElement('mod_surveypro_select', $this->itemname, '', $labels, $attributes);
            $select->setMultiple(true);
            $elementgroup[] = $select;

            $attributes['class'] = 'multiselect_check';
            unset($attributes['size']);

            if (!$this->required) {
                $itemname = $this->itemname.'_noanswer';
                $attributes['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $noanswerstr, $attributes);
            }

            $itemname = $this->itemname.'_ignoreme';
            $attributes['id'] = $idprefix.'_ignoreme';
            $attributes['class'] = 'multiselect_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);

            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '<br />', false);
            if (!$this->required) {
                // Multiselect uses a special syntax
                // that is different from the syntax of all the other mform groups with disabilitation chechbox.
                // $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');.
                $mform->disabledIf($this->itemname.'[]', $this->itemname.'_noanswer', 'checked');
            }
            $mform->disabledIf($this->itemname.'[]', $this->itemname.'_ignoreme', 'checked');
            $mform->disabledIf($this->itemname.'_noanswer', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
        }

        // Begin of: defaults.
        if (!$searchform) {
            if ($defaults = surveypro_multilinetext_to_array($this->defaultvalue)) {
                $defaultkeys = array();
                foreach ($defaults as $default) {
                    $defaultkeys[] = array_search($default, $labels);
                }
                $mform->setDefault($this->itemname, $defaultkeys);
            }
            $itemname = $this->itemname.'_noanswer';
            if (!empty($this->noanswerdefault)) {
                $mform->setDefault($itemname, '1');
            }
        }
        // End of: defaults.

        // This last item is needed because the check for the not empty field is performed in the validation routine (not by JS).
        // For multiselect element, nothing is submitted if no option is selected
        // so, if the user neglects the mandatory multiselect AT ALL, it is not submitted and, as conseguence, not validated.
        // TO ALWAYS SUBMIT A MULTISELECT I add a dummy hidden item.
        //
        // Take care: I choose a name for this item that IS UNIQUE BUT is missing the SURVEYPRO_ITEMPREFIX.'_'.
        // In this way I am sure it will be used as indicator that something has to be done on the element.
        $placeholderitemname = SURVEYPRO_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
        $mform->addElement('hidden', $placeholderitemname, 1);
        $mform->setType($placeholderitemname, PARAM_INT);

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        }
    }

    /**
     * Perform outform and searchform data validation.
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

        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 1;0;1;0.

        $indexsubset = array();
        $labelsubset = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $indexsubset = array_slice($parentvalues, 0, $key);
            $labelsubset = array_slice($parentvalues, $key + 1);
        } else {
            $indexsubset = array_keys($parentvalues, '1');
        }

        if ($indexsubset) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'[]';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $indexsubset;
            $disabilitationinfo[] = $mformelementinfo;
            // $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', array(0, 2));
        }

        // If this item foresees the "No answer" checkbox, provide a directive for it too.
        if (!$this->required) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_noanswer';
            $mformelementinfo->content = 'checked';

            $disabilitationinfo[] = $mformelementinfo;
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
     * Dynamically decide if my child (living in my same page) is allowed or not.
     *
     * This method is called if (and only if) parent item and child item live in the same form page.
     * This method has two purposes:
     * - stop userpageform item validation
     * - drop unexpected returned values from $userpageform->formdata
     *
     * As parentitem I declare whether my child item is allowed to return a value (is enabled) or is not (is disabled).
     *
     * @param string $childparentvalue
     * @param array $data
     * @return boolean: true: if the item is welcome; false: if the item must be dropped out
     */
    public function userform_is_child_allowed_dynamic($childparentvalue, $data) {
        // I need to verify (item per item) if they hold the same value the user entered.
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 2;3.

        if (isset($data[$this->itemname])) {
            if (count(array_diff($data[$this->itemname], $parentvalues))) {
                $status = false;
            } else {
                $status = true;
            }
        } else {
            // If $data[$this->itemname] is not set
            // this means that either:
            // 1. User answered "No answer".
            // 2. User submitted the multiselect without selecting any item.
            // In both cases, $status = false.
            $status = false;
        }

        return $status;
    }

    /**
     * Prepare the string with the filling instruction.
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
     * or what to return for the search form.
     * I don't set $olduseranswer->contentformat in order to accept the default db value.
     *
     * @param array $answer
     * @param object $olduseranswer
     * @param bool $searchform
     * @return void
     */
    public function userform_save_preprocessing($answer, &$olduseranswer, $searchform) {
        if (isset($answer['noanswer'])) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        if (!isset($answer['mainelement'])) { // Only placeholder arrived here.
            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, array_fill(1, count($labels), '0'));
        } else {
            // Here $answer is an array with the keys of the selected elements.
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $answer['mainelement']);

            $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
            $itemcount = count($labels);
            $contentarray = array_fill(0, $itemcount, 0);
            foreach ($answer['mainelement'] as $k) {
                $contentarray[$k] = 1;
            }

            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $contentarray);
        }
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time.
     *
     * @param object $fromdb
     * @return associative array with disaggregate element values
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = '1';
                return $prefill;
            }

            $contentarray = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);
            $preset = array();
            foreach ($contentarray as $k => $v) {
                if ($v == 1) {
                    $preset[] = $k;
                }
            }
            $preset = implode(',', $preset);
            $prefill[$this->itemname] = $preset;
        }

        // If the "No answer" checkbox is part of the element GUI...
        if ($this->noanswerdefault) {
            $prefill[$this->itemname.'_noanswer'] = 0;
        }

        return $prefill;
    }

    /**
     * Starting from the info stored into $answer, this function returns the corresponding content for the export file.
     *
     * @param object $answer
     * @param string $format
     * @return string - the string for the export file
     */
    public function userform_db_to_export($answer, $format='') {
        // The content of the provided answer.
        $content = $answer->content;

        // Trigger 'answernotsubmitted' and 'answerisnoanswer'.
        $quickresponse = self::userform_standardcontent_to_string($content);
        if (isset($quickresponse)) { // Parent method provided the response.
            return $quickresponse;
        }

        // Format.
        if ($format == SURVEYPRO_FRIENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // Output.
        // Here $answers is an array like: array(2,4).
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
                foreach ($answers as $k => $answer) {
                    if ($answer == 1) {
                        $output[] = $values[$k];
                    }
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                foreach ($answers as $k => $answer) {
                    if ($answer == 1) {
                        $output[] = $values[$k];
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
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
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
