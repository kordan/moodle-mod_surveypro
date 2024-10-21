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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_multiselect;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;
use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/mod/surveypro/field/multiselect/lib.php');

/**
 * Class to manage each aspect of the multiselect item
 *
 * @package   surveyprofield_multiselect
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends itembase {

    // Itembase properties.

    /**
     * @var string List of options in the form of "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    protected $options;

    /**
     * @var int Defaultvalue for the item answer
     */
    protected $defaultvalue;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var string noanswerdefault
     */
    protected $noanswerdefault;

    /**
     * @var int Height of the multiselect in rows
     */
    protected $heightinrows;

    /**
     * @var int Minimum number of items the user is allowed to choose in his/her answer
     */
    protected $minimumrequired;

    /**
     * @var int Maximum number of items the user is forced to choose in his/her answer
     */
    protected $maximumrequired;

    // Service variables.

    /**
     * @var bool Does this item use the child table surveypro(field|format)_plugin?
     */
    protected static $usesplugintable = true;

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
     * @param \stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'multiselect';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
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
    }

    /**
     * Item save.
     *
     * @param object $record
     * @return void
     */
    public function item_save($record) {
        // Set properties at plugin level and then continue to base level.

        // Drop empty rows and trim edging rows spaces from each textarea field.
        $fieldlist = ['options', 'defaultvalue'];
        $this->item_clean_textarea_fields($record, $fieldlist);

        // Set custom fields values as defined by this specific plugin.
        $this->add_plugin_properties_to_record($record);

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Traslate values from the mform of this item to values for corresponding properties.
     *
     * @param object $record
     * @return void
     */
    public function add_plugin_properties_to_record($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['noanswerdefault'];
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_add_fields_default_to_child_table(&$record) {
        $record->options = "first\nsecond";
        // $record->defaultvalue
        // $record->noanswerdefault
        $record->downloadformat = SURVEYPRO_ITEMRETURNSLABELS;
        $record->minimumrequired = 0;
        // $record->maximumrequired
        $record->heightinrows = 4;
    }

    /**
     * Make the list of constraints the child has to respect in order to create a valid relation
     *
     * @return list of contraints of the plugin (as parent) in text format
     */
    public function item_list_constraints() {
        $constraints = [];

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $values = $this->get_textarea_content(SURVEYPRO_VALUES, 'options');
        $optionstr = get_string('option', 'surveyprofield_multiselect');
        foreach ($values as $value) {
            $constraints[] = $optionstr.$labelsep.$value;
        }

        return implode('<br>', $constraints);
    }

    // MARK set.

    /**
     * Set options.
     *
     * @param string $options
     * @return void
     */
    public function set_options($options) {
        $this->options = $options;
    }

    /**
     * Set defaultvalue.
     *
     * @param string $defaultvalue
     * @return void
     */
    public function set_defaultvalue($defaultvalue) {
        $this->defaultvalue = $defaultvalue;
    }

    /**
     * Set downloadformat.
     *
     * @param string $downloadformat
     * @return void
     */
    public function set_downloadformat($downloadformat) {
        $this->downloadformat = $downloadformat;
    }

    /**
     * Set noanswerdefault.
     *
     * @param string $noanswerdefault
     * @return void
     */
    public function set_noanswerdefault($noanswerdefault) {
        $this->noanswerdefault = $noanswerdefault;
    }

    /**
     * Set heightinrows.
     *
     * @param string $heightinrows
     * @return void
     */
    public function set_heightinrows($heightinrows) {
        $this->heightinrows = $heightinrows;
    }

    /**
     * Set minimumrequired.
     *
     * @param string $minimumrequired
     * @return void
     */
    public function set_minimumrequired($minimumrequired) {
        $this->minimumrequired = $minimumrequired;
    }

    /**
     * Set maximumrequired.
     *
     * @param string $maximumrequired
     * @return void
     */
    public function set_maximumrequired($maximumrequired) {
        $this->maximumrequired = $maximumrequired;
    }

    // MARK get.

    /**
     * Get options.
     *
     * @return $this->options
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Get defaultvalue.
     *
     * @return $this->defaultvalue
     */
    public function get_defaultvalue() {
        return $this->defaultvalue;
    }

    /**
     * Get downloadformat.
     *
     * @return $this->downloadformat
     */
    public function get_downloadformat() {
        return $this->downloadformat;
    }

    /**
     * Get noanswerdefault.
     *
     * @return $this->noanswerdefault
     */
    public function get_noanswerdefault() {
        return $this->noanswerdefault;
    }

    /**
     * Get heightinrows.
     *
     * @return $this->heightinrows
     */
    public function get_heightinrows() {
        return $this->heightinrows;
    }

    /**
     * Get minimumrequired.
     *
     * @return $this->minimumrequired
     */
    public function get_minimumrequired() {
        return $this->minimumrequired;
    }

    /**
     * Get maximumrequired.
     *
     * @return $this->maximumrequired
     */
    public function get_maximumrequired() {
        return $this->maximumrequired;
    }

    /**
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function get_downloadformats() {
        $options = [];

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
    public function get_friendlyformat() {
        return SURVEYPRO_ITEMRETURNSLABELS;
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = [
            'options', 'defaultvalue', 'noanswerdefault', 'downloadformat', 'minimumrequired', 'maximumrequired', 'heightinrows',
        ];
        $data = $this->get_base_presets($pluginproperties);

        return $data;
    }

    /**
     * Make the list of the fields using multilang
     *
     * @param boolean $includemetafields
     * @return array of fields
     */
    public function get_multilang_fields($includemetafields=true) {
        $fieldlist['surveypro_item'] = $this->get_base_multilang_fields($includemetafields);
        $fieldlist['surveyprofield_multiselect'] = ['options', 'defaultvalue'];

        return $fieldlist;
    }

    /**
     * Get if the plugin uses the position of options to save user answers.
     *
     * @return bool The plugin uses the position of options to save user answers.
     */
    public function get_uses_positional_answer() {
        return true;
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
    <xs:element name="surveyprofield_multiselect">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="options" type="xs:string"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="noanswerdefault" type="xs:int" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:string" minOccurs="0"/>
                <xs:element name="minimumrequired" type="xs:int" minOccurs="0"/>
                <xs:element name="maximumrequired" type="xs:int" minOccurs="0"/>
                <xs:element name="heightinrows" type="xs:int" minOccurs="0"/>
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
        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $parentcontents = array_unique($utilityitemman->multilinetext_to_array($childparentcontent));
        $values = $this->get_textarea_content(SURVEYPRO_VALUES, 'options');

        $childparentvalue = array_fill(0, count($values), 0);
        $labels = [];
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
        $values = $this->get_textarea_content(SURVEYPRO_VALUES, 'options');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $childparentcontent = [];
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

        $values = $this->get_textarea_content(SURVEYPRO_VALUES, 'options');
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
     * Define the mform element for the userform and the searchform.
     *
     * @param \moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $starstr = get_string('star', 'mod_surveypro');
        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $attributes = [];
        $elementgroup = [];
        $baseid = 'id_field_multiselect_'.$this->sortindex;
        $class = ['class' => 'indent-'.$this->indent];
        $basename = $this->itemname;

        $labels = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');
        $attributes['id'] = $baseid;
        $attributes['size'] = $this->heightinrows;

        $select = $mform->createElement('select', $basename, '', $labels, $attributes);
        $select->setMultiple(true);
        $elementgroup[] = $select;

        unset($attributes['size']); // No longer needed.

        if (!$searchform) {
            if ($this->required) {
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, '', false, $class);
            } else {
                $attributes['id'] = $baseid.'_noanswer';
                $elementgroup[] = $mform->createElement('checkbox', $basename.'_noanswer', '', $noanswerstr, $attributes);

                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, '', false, $class);
                // Multiselect uses a special syntax
                // that is different from all the other mform group with disabilitation chechbox syntax.
                // $mform->disabledIf($basename.'_group', $basename.'_noanswer', 'checked');.
                $mform->disabledIf($basename.'[]', $basename.'_noanswer', 'checked');
            }
        } else {
            if (!$this->required) {
                $attributes['id'] = $baseid.'_noanswer';
                $elementgroup[] = $mform->createElement('checkbox', $basename.'_noanswer', '', $noanswerstr, $attributes);
            }

            $attributes['id'] = $baseid.'_ignoreme';
            $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

            $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, '<br>', false, $class);
            if (!$this->required) {
                // Multiselect uses a special syntax
                // that is different from all the other mform group with disabilitation chechbox syntax.
                // $mform->disabledIf($basename.'_group', $basename.'_noanswer', 'checked');.
                $mform->disabledIf($basename.'[]', $basename.'_noanswer', 'checked');
            }
            $mform->disabledIf($basename.'[]', $basename.'_ignoreme', 'checked');
            $mform->disabledIf($basename.'_noanswer', $basename.'_ignoreme', 'checked');
            $mform->setDefault($basename.'_ignoreme', '1');
        }

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $basename.'_extrarow_group' : $basename.'_group';
                $mform->_required[] = $starplace;
            }
        }

        // Begin of: defaults.
        if (!$searchform) {
            if ($defaults = $utilityitemman->multilinetext_to_array($this->defaultvalue)) {
                $defaultkeys = [];
                foreach ($defaults as $default) {
                    $defaultkeys[] = array_search($default, $labels);
                }
                $mform->setDefault($basename, $defaultkeys);
            }
            if (!empty($this->noanswerdefault)) {
                $mform->setDefault($basename.'_noanswer', '1');
            }
        }
        // End of: defaults.

        // This last item is needed because the check for "not empty" field is performed in the validation routine (not by JS).
        // For multiselect element, nothing is submitted if no option is selected
        // so, if the user neglects the mandatory multiselect AT ALL, it is not submitted and, as conseguence, not validated.
        // TO ALWAYS SUBMIT A MULTISELECT I add a dummy hidden item.
        //
        // Take care: I choose a name for this item that IS UNIQUE BUT is missing the SURVEYPRO_ITEMPREFIX.'_'.
        // In this way I am sure it will be used as indicator that something has to be done on the element.
        $placeholderitemname = SURVEYPRO_PLACEHOLDERPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
        $mform->addElement('hidden', $placeholderitemname, 1);
        $mform->setType($placeholderitemname, PARAM_INT);
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
            return $errors;
        }
        if (isset($data[$this->itemname.'_noanswer']) && ($data[$this->itemname.'_noanswer'] == 1) ) {
            return $errors; // Nothing to validate.
        }

        $errorkey = $this->itemname.'_group';

        // I don't care if this element is required or not.
        // If the user provides an answer, it has to be compliant with the field validation rules.
        $answercount = (isset($data[$this->itemname])) ? count($data[$this->itemname]) : 0;
        if ($answercount < $this->minimumrequired) {
            if ($this->minimumrequired == 1) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_one', 'surveyprofield_multiselect');
            } else {
                $a = $this->minimumrequired;
                $errors[$errorkey] = get_string('uerr_lowerthanminimum_more', 'surveyprofield_multiselect', $a);
            }
        }
        if (($this->maximumrequired) && ($answercount > $this->maximumrequired)) {
            if ($this->maximumrequired == 1) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum_one', 'surveyprofield_multiselect');
            } else {
                $a = $this->maximumrequired;
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum_more', 'surveyprofield_multiselect', $a);
            }
        }

        return $errors;
    }

    /**
     * From childparentvalue defines syntax for disabledIf.
     *
     * @param string $childparentvalue
     * @return array
     */
    public function userform_get_parent_disabilitation_info($childparentvalue) {
        $disabilitationinfo = [];

        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 1;0;1;0.

        $indexsubset = [];
        $labelsubset = [];
        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $indexsubset = array_slice($parentvalues, 0, $key);
            $labelsubset = array_slice($parentvalues, $key + 1);
        } else {
            $indexsubset = array_keys($parentvalues, '1');
        }

        if ($indexsubset) {
            $mformelementinfo = new \stdClass();
            $mformelementinfo->parentname = $this->itemname.'[]';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $indexsubset;
            $disabilitationinfo[] = $mformelementinfo;
            // Example: $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', [0, 2]);.
        }

        // If this item foresees the "No answer" checkbox, provide a directive for it too.
        if (!$this->required) {
            $mformelementinfo = new \stdClass();
            $mformelementinfo->parentname = $this->itemname.'_noanswer';
            $mformelementinfo->content = 'checked';

            $disabilitationinfo[] = $mformelementinfo;
        }

        if ($labelsubset) {
            // Only garbage, but user wrote it.
            $mformelementinfo = new \stdClass();
            $mformelementinfo->parentname = $this->itemname.'[]';
            $mformelementinfo->operator = 'neq';
            $mformelementinfo->content = $labelsubset;
            $disabilitationinfo[] = $mformelementinfo;
            // Example: $reference = ['foo', 'bar'].
            // Example: $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', $reference);
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

        $arrayinstruction = [];

        if ($this->minimumrequired) {
            if ($this->minimumrequired == 1) {
                $arrayinstruction[] = get_string('restrictions_minimumrequired_one', 'surveyprofield_multiselect');
            } else {
                $arrayinstruction[] = get_string('restrictions_minimumrequired_more', 'surveyprofield_multiselect', $this->minimumrequired);
            }
        }
        if ($this->maximumrequired) {
            if ($this->maximumrequired == 1) {
                $arrayinstruction[] = get_string('restrictions_maximumrequired_one', 'surveyprofield_multiselect');
            } else {
                $a = $this->maximumrequired;
                $arrayinstruction[] = get_string('restrictions_maximumrequired_more', 'surveyprofield_multiselect', $a);
            }
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
        if (isset($answer['noanswer'])) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
            return;
        }

        if (!isset($answer['mainelement'])) { // Only placeholder arrived here.
            $labels = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, array_fill(1, count($labels), '0'));
        } else {
            // Here $answer is an array with the keys of the selected elements.
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $answer['mainelement']);

            $labels = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');
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
    public function userform_get_prefill($fromdb) {
        $prefill = [];

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = '1';
                return $prefill;
            }

            $contentarray = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);
            $preset = [];
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
            $format = $this->get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // Output.
        // Here $answers is an array like: [2,4].
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = [];
                $values = $this->get_textarea_content(SURVEYPRO_VALUES, 'options');
                foreach ($answers as $k => $answer) {
                    if ($answer == 1) {
                        $output[] = $values[$k];
                    }
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = [];
                $values = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');

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
        $elementnames = [];
        $elementnames[] = $this->itemname.'[]';
        $elementnames[] = SURVEYPRO_DONTSAVEMEPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid.'_placeholder';

        return $elementnames;
    }
}
