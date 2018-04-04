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
 * This file contains the surveyprofield_checkbox
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/checkbox/lib.php');

/**
 * Class to manage each aspect of the checkbox item
 *
 * @package   surveyprofield_checkbox
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_checkbox_field extends mod_surveypro_itembase {

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
     *
     * Take in mind that "required" (for checkbox item) means only: "The 'No answer' checkbox is not displayed".
     * Each checkbox element is intrinsically required.
     * There is noy for a student to jump a standard checkbox element.
     *
     * Example: "What do you take for breakfast?" milk, bread, jam.
     * If the user jumps this element HE/SHE IS STATING THAT HE/SHE DOES NOT TAKE milk AND NOT bread AND NOT jam.
     *
     * If the editing teacher choose to allow the student to jump this question, he HAS TO leave unchecked the "required" propery.
     * In that way the element will be equipped with an additional exclusive "No answer" checkbox.
     * This last checkbox privides to the student the possibility to say "I don't tell you what I take for breakfast!"
     *
     * Note that a checkbox can have $minimumrequired irrespectively of being required or no.
     * Alias: a checkbox element can be not $required and have, at the same time, $minimumrequired > 0.
     * This is perfectly valid.
     *
     * Example: "What do you take for breakfast?" milk, bread, jam.
     * With: $required = 0 and $minimumrequired = 2
     *
     * This means that the student is allowed to select the exclusive "No answer" checkbox and run away BUT
     * IF he/she decides to provide an answer THEN he/she has to select at least 2 checkboxes.
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
     * @var string Text label for the optional option "other" in the form of "$value SURVEYPRO_OTHERSEPARATOR $label"
     */
    protected $labelother;

    /**
     * @var string Value of the field when the form is initially displayed
     */
    protected $defaultvalue;

    /**
     * @var bool $noanswerdefault
     */
    protected $noanswerdefault;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var int Mminimum number of checkboxes the user is forced to choose in his/her answer
     */
    protected $minimumrequired;

    /**
     * @var int Orientation of the list of bottons. Either: SURVEYPRO_VERTICAL or SURVEYPRO_HORIZONTAL
     */
    protected $adjustment;

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
        $this->plugin = 'checkbox';
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

        $this->item_custom_fields_to_form();
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
        // Drop empty rows and trim trailing spaces from each textarea field.
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
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Checkbox';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'checkbox_001';
        $record->indent = 0;
        $record->options = "first\nsecond";
        $record->noanswerdefault = 0;
        $record->downloadformat = SURVEYPRO_ITEMRETURNSLABELS;
        $record->minimumrequired = 0;
        $record->adjustment = 0;
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
     * Prepare values for the mform of this item.
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.
    }

    /**
     * Make the list of constraints the child has to respect in order to create a valid relation
     *
     * @return list of contraints of the plugin (as parent) in text format
     */
    public function item_list_constraints() {
        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $constraints = array();

        $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');
        $optionstr = get_string('option', 'surveyprofield_checkbox');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
        }
        if (!empty($this->labelother)) {
            $labelotherstr = get_string('labelother', 'surveyprofield_checkbox');
            $allowedstr = get_string('allowed', 'surveyprofield_checkbox');
            $constraints[] = $labelotherstr.$labelsep.$allowedstr;
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

        $options[SURVEYPRO_ITEMSRETURNSVALUES] = get_string('returnvalues', 'surveyprofield_checkbox');
        $options[SURVEYPRO_ITEMRETURNSLABELS] = get_string('returnlabels', 'surveyprofield_checkbox');
        $options[SURVEYPRO_ITEMRETURNSPOSITION] = get_string('returnposition', 'surveyprofield_checkbox');

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
        $fieldlist[$this->plugin] = array('content', 'extranote', 'options', 'labelother', 'defaultvalue');

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
    <xs:element name="surveyprofield_checkbox">
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
                <xs:element name="labelother" type="xs:string" minOccurs="0"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="noanswerdefault" type="xs:int"/>
                <xs:element name="downloadformat" type="xs:int"/>
                <xs:element name="minimumrequired" type="xs:int"/>
                <xs:element name="adjustment" type="xs:int"/>
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
        $expectedcount = count($values);
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $condition = empty($this->labelother) ? empty($parentvalues[$actualcount - 1]) : true;
            $condition = $condition && ($actualcount == ($key + 2)); // Only one label is allowed.
            $condition = $condition && ($expectedcount == $key); // Only $expectedcount checkboxes are allowed.
            $return = ($condition) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
        } else {
            $return = ($actualcount == $expectedcount) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONMALFORMED;
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
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_checkbox_'.$this->sortindex;

        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $defaults = surveypro_multilinetext_to_array($this->defaultvalue);

        $attributes = array();
        $attributes['class'] = 'indent-'.$this->indent.' checkbox_check';
        $attributes['group'] = 1;

        $options = array('0', '1');
        $elementgroup = array();
        $i = 0;
        foreach ($labels as $label) {
            $itemname = $this->itemname.'_'.$i;
            $attributes['id'] = $idprefix.'_'.$i;
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $itemname, '', $label, $attributes, $options);

            if ($this->adjustment == SURVEYPRO_HORIZONTAL) {
                $attributes['class'] = 'checkbox_check';
            }

            if (!$searchform) {
                if (in_array($label, $defaults)) {
                    $mform->setDefault($itemname, '1');
                }
            }
            $i++;
        }
        if (!empty($this->labelother)) {
            list($othervalue, $otherlabel) = $this->item_get_other();

            $itemname = $this->itemname.'_other';
            $attributes['id'] = $idprefix.'_other';
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $itemname, '', $otherlabel, $attributes, $options);

            unset($attributes['group']);
            $attributes['id'] = $idprefix.'_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname.'_text', '', $attributes);
            $mform->setType($this->itemname.'_text', PARAM_RAW);

            if (!$searchform) {
                $mform->setDefault($this->itemname.'_text', $othervalue);
                if (in_array($otherlabel, $defaults)) {
                    $mform->setDefault($this->itemname.'_other', '1');
                }
            }
            $mform->disabledIf($this->itemname.'_text', $this->itemname.'_other', 'notchecked');
        }

        if (!$this->required) {
            $itemname = $this->itemname.'_noanswer';
            $attributes['id'] = $idprefix.'_noanswer';
            $noanswerstr = get_string('noanswer', 'surveypro');
            $options = array('0', '1');
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $itemname, '', $noanswerstr, $attributes, $options);
            if (!empty($this->noanswerdefault)) {
                $mform->setDefault($itemname, '1');
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
            if (!$this->required) {
                $separator[] = '<br />';
            }
        } else { // SURVEYPRO_HORIZONTAL.
            $separator = ' ';
        }
        $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

        if (!$this->required) {
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }

        if ($searchform) {
            $itemname = $this->itemname.'_ignoreme';
            $this->item_add_color_unifier($mform);
            $attributes['id'] = $idprefix.'_ignoreme';
            $mform->addElement('mod_surveypro_checkbox', $itemname, '', get_string('star', 'mod_surveypro'), $attributes);
            $mform->setDefault($itemname, '1');

            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
        }

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
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

        $errorkey = $this->itemname.'_group';

        if (!empty($this->labelother)) {
            if (($data[$this->itemname.'_other']) && empty($data[$this->itemname.'_text']) ) {
                $errors[$errorkey] = get_string('uerr_missingothertext', 'surveyprofield_checkbox');
                return;
            }
        }

        // Begin of: get answercount.
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

        $answercount = 0;
        foreach ($labels as $k => $unused) {
            $itemname = $this->itemname.'_'.$k;
            if ($data[$itemname]) { // They are advanced checkbox so I am sure the answer always exist.
                $answercount++;
            }
        }

        if (!empty($this->labelother)) {
            if (($data[$this->itemname.'_other']) && (!empty($data[$this->itemname.'_text']))) {
                $answercount++;
            }
        }
        // End of: get answercount.
        // I don't care if this element is required or not.
        // If the user provides an answer, it has to be compliant with the field validation rules.
        if ($answercount < $this->minimumrequired) {
            if ($this->minimumrequired == 1) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_one', 'surveyprofield_checkbox');
            } else {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_more', 'surveyprofield_checkbox', $this->minimumrequired);
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

        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // For instance: 1;1;0;.

        $indexsubset = array();
        $labelsubset = array();
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $indexsubset = array_slice($parentvalues, 0, $key);
            $labelsubset = array_slice($parentvalues, $key + 1);
        } else {
            $indexsubset = $parentvalues;
        }

        foreach ($indexsubset as $k => $unused) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_'.$k;
            if ($indexsubset[$k] == 1) {
                $mformelementinfo->content = 'notchecked';
            } else {
                $mformelementinfo->content = 'checked';
            }
            $disabilitationinfo[] = $mformelementinfo;
        }
        // If this item foresees the "No answer" checkbox, provide a directive for it too.
        if (!$this->required) {
            $mformelementinfo = new stdClass();
            $mformelementinfo->parentname = $this->itemname.'_noanswer';
            $mformelementinfo->content = 'checked';

            $disabilitationinfo[] = $mformelementinfo;
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
            // Even if no labels were provided
            // I have to add one more $disabilitationinfo if $this->other is not empty.
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
        // I need to verify (checkbox per checkbox) if they hold the same value the user entered.
        $labels = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // For instance: 2;3;shark.

        // Build the local $parentconstrain variable that will be used to evaluate the status.
        $parentconstrain = array();
        if (!empty($this->labelother)) {
            $parentconstrain[$this->itemname.'_other'] = '0';
        }

        $nextisother = false;
        foreach ($parentvalues as $k => $expectedvalue) {
            if ($expectedvalue == '>') {
                $nextisother = true;
                continue;
            }

            if (!$nextisother) {
                $parentconstrain[$this->itemname.'_'.$k] = $expectedvalue;
            } else {
                $parentconstrain[$this->itemname.'_other'] = '1';
                $parentconstrain[$this->itemname.'_text'] = $expectedvalue;
            }
        }
        if (empty($this->mandatory)) {
            $parentconstrain[$this->itemname.'_noanswer'] = '0';
        }
        // End of: Build the local $parentconstrain variable that will be used to evaluate the status.

        $status = true;
        foreach ($parentconstrain as $k => $expectedvalue) {
            $status = $status && ($data[$k] == $expectedvalue);
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
                $fillinginstruction = get_string('restrictions_minimumrequired_one', 'surveyprofield_checkbox');
            } else {
                $a = $this->minimumrequired;
                $fillinginstruction = get_string('restrictions_minimumrequired_more', 'surveyprofield_checkbox', $a);
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
        if ( isset($answer['noanswer']) && ($answer['noanswer'] == 1) ) { // This is correct for input and search form both.
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        if (isset($answer['ignoreme']) && ($answer['ignoreme'] == 1)) { // It is an advcheckbox.
            $olduseranswer->content = null;
            return;
        }

        $return = $answer;
        if (!empty($this->labelother)) {
            $return[] = (isset($answer['other']) && ($answer['other'] == 1)) ? $answer['text'] : '';
            unset($return['other']);
            unset($return['text']);
        }
        if (!$this->required) {
            unset($return['noanswer']);
        }
        $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $return);
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
                $prefill[$this->itemname.'_noanswer'] = 1;
                return $prefill;
            }

            $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);

            // Here $answers is an array like: array(1,1,0,0,'dummytext').
            foreach ($answers as $k => $checkboxvalue) {
                $itemname = $this->itemname.'_'.$k;
                $prefill[$itemname] = $checkboxvalue;
            }
            if (!empty($this->labelother)) {
                // Delete last item of $prefill.
                unset($prefill[$itemname]);

                // Add last element of the $prefill.
                $lastanswer = end($answers);

                if (strlen($lastanswer)) {
                    $prefill[$this->itemname.'_other'] = 1;
                    $prefill[$this->itemname.'_text'] = $lastanswer;
                } else {
                    $prefill[$this->itemname.'_other'] = 0;
                    if ($fromdb->verified) { // If the answer was validated.
                        $prefill[$this->itemname.'_text'] = '';
                    } else {
                        list($othervalue, $otherlabel) = $this->item_get_other();
                        $prefill[$this->itemname.'_text'] = $othervalue;
                    }
                }
            }

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
        // Here $answers is an array like: array(1,1,0,0,'dummytext').
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_VALUES, 'options');

                foreach ($values as $k => $value) {
                    if ($answers[$k] == 1) {
                        $output[] = $value;
                    }
                }
                if (!empty($this->labelother)) {
                    $value = end($answers);
                    if (!empty($value)) {
                        $output[] = $value; // Last element of the array $answers.
                    }
                }

                if (!empty($output)) {
                    $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                } else {
                    $return = get_string('emptyanswer', 'mod_surveypro');
                }
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = array();
                $values = $this->item_get_content_array(SURVEYPRO_LABELS, 'options');

                foreach ($values as $k => $value) {
                    if ($answers[$k] == 1) {
                        $output[] = $value;
                    }
                }
                if (!empty($this->labelother)) {
                    $value = end($answers);
                    if (!empty($value)) {
                        $output[] = $value; // Last element of the array $answers.
                    }
                }

                if (!empty($output)) {
                    $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                } else {
                    $return = get_string('emptyanswer', 'mod_surveypro');
                }
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
        $elementnames[] = $this->itemname.'_group';

        return $elementnames;
    }
}
