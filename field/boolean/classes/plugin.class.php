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
require_once($CFG->dirroot.'/mod/surveypro/field/boolean/lib.php');

class mod_surveypro_field_boolean extends mod_surveypro_itembase {

    /**
     * Item content stuff.
     */
    public $content = '';
    public $contenttrust = 1;
    public $contentformat = '';

    /**
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    protected $customnumber;

    /**
     * $position = where does the question go?
     */
    protected $position;

    /**
     * $extranote = an optional text describing the item
     */
    protected $extranote;

    /**
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    protected $required;

    /**
     * $variable = the name of the field storing data in the db table
     */
    protected $variable;

    /**
     * $indent = the indent of the item in the form page
     */
    protected $indent;

    /**
     * $defaultoption
     */
    protected $defaultoption;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    protected $defaultvalue;

    /**
     * $downloadformat = the format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * $style = radiobuttons or dropdown menu
     */
    protected $style;

    /**
     * static canbeparent
     */
    protected static $canbeparent = true;

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param stdClass $cm
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent. To include $item->parentcontent (as decoded by the parent item) too.
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'boolean';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent. To include $item->parentcontent (as decoded by the parent item) too.
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
     * item_add_mandatory_plugin_fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return nothing
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Boolean';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->variable = 'boolean_001';
        $record->indent = 0;
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->defaultvalue = 1;
        $record->downloadformat = 'strfbool01';
        $record->style = 2;
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
     * sets record field to store the correct value to db for the age custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Hideinstructions is set by design.
        $record->hideinstructions = 1;

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
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

        $optionstr = get_string('option', 'surveyprofield_boolean');
        $constraints[] = $optionstr.': 0';
        $constraints[] = $optionstr.': 1';

        return implode($constraints, '<br />');
    }

    /**
     * item_get_downloadformats
     *
     * @param none
     * @return
     */
    public function item_get_downloadformats() {
        $option = array();
        $option['strfbool01'] = get_string('strfbool01', 'surveyprofield_boolean'); // yes/no.
        $option['strfbool02'] = get_string('strfbool02', 'surveyprofield_boolean'); // Yes/No.
        $option['strfbool03'] = get_string('strfbool03', 'surveyprofield_boolean'); // y/n.
        $option['strfbool04'] = get_string('strfbool04', 'surveyprofield_boolean'); // Y/N.
        $option['strfbool05'] = get_string('strfbool05', 'surveyprofield_boolean'); // up/down.
        $option['strfbool06'] = get_string('strfbool06', 'surveyprofield_boolean'); // true/false.
        $option['strfbool07'] = get_string('strfbool07', 'surveyprofield_boolean'); // True/False.
        $option['strfbool08'] = get_string('strfbool08', 'surveyprofield_boolean'); // T/F.
        $option['strfbool09'] = get_string('strfbool09', 'surveyprofield_boolean'); // 1/0.
        $option['strfbool10'] = get_string('strfbool10', 'surveyprofield_boolean'); // +/-.

        return $option;
    }

    /**
     * item_get_friendlyformat
     *
     * @param none
     * @return
     */
    public function item_get_friendlyformat() {
        return 'strfbool01';
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
    <xs:element name="surveyprofield_boolean">
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

                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:int" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="style"/>
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
        $values = array('0', '1');

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
        $values = array('0', '1');
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
            // Only garbage but user wrote it.
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
        // See parent method for explanation.

        $values = array('0', '1');
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue);
        $actualcount = count($parentvalues);

        $key = array_search('>', $parentvalues);
        if ($key !== false) {
            $return = ($actualcount == 2) ? SURVEYPRO_CONDITIONNEVERMATCH : SURVEYPRO_CONDITIONMALFORMED;
        } else {
            if ($actualcount == 1) {
                $k = $parentvalues[0];
                $return = (isset($values[$k])) ? SURVEYPRO_CONDITIONOK : SURVEYPRO_CONDITIONNEVERMATCH;
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

        $idprefix = 'id_surveypro_field_boolean_'.$this->sortindex;

        $yeslabel = get_string('yes');
        $nolabel = get_string('no');

        if ($this->style == SURVEYPROFIELD_BOOLEAN_USESELECT) {
            // Begin of: element values.
            $options = array();
            if (!$searchform) {
                if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                    $options[SURVEYPRO_INVITEVALUE] = get_string('choosedots');
                }
            } else {
                $options[SURVEYPRO_IGNOREMEVALUE] = '';
            }
            $options['1'] = $yeslabel;
            $options['0'] = $nolabel;
            if (!$this->required) {
                $options += array(SURVEYPRO_NOANSWERVALUE => get_string('noanswer', 'mod_surveypro'));
            }
            // End of: element values

            // Begin of: mform element.
            if ($this->required) {
                if (!$searchform) {
                    // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                    // -> I do not want JS form validation if the page is submitted through the "previous" button.
                    // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                    // Simply add a dummy star to the item and the footer note about mandatory fields.
                    $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                    $mform->_required[] = $starplace;
                }
            }
            $mform->addElement('mod_surveypro_select', $this->itemname, $elementlabel, $options, array('class' => 'indent-'.$this->indent, 'id' => $idprefix));
            // End of: mform element.
        } else { // SURVEYPROFIELD_BOOLEAN_USERADIOV or SURVEYPROFIELD_BOOLEAN_USERADIOH.
            $separator = ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOV) ? '<br />' : ' ';
            $elementgroup = array();

            // Begin of: mform element.
            $attributes = array('class' => 'indent-'.$this->indent);

            if (!$searchform) {
                if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                    $attributes['id'] = $idprefix.'_invite';
                    $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('choosedots'), SURVEYPRO_INVITEVALUE, $attributes);
                    if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                        unset($attributes['class']);
                    }
                }
            } else {
                $attributes['id'] = $idprefix.'_ignoreme';
                $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('star', 'mod_surveypro'), SURVEYPRO_IGNOREMEVALUE, $attributes);
                if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                    unset($attributes['class']);
                }
            }

            $attributes['id'] = $idprefix.'_1';
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', $yeslabel, '1', $attributes);

            if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                unset($attributes['class']);
            }

            $attributes['id'] = $idprefix.'_0';
            $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', $nolabel, '0', $attributes);

            if (!$this->required) {
                $attributes['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('mod_surveypro_radio', $this->itemname, '', get_string('noanswer', 'mod_surveypro'), SURVEYPRO_NOANSWERVALUE, $attributes);
            }
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            // End of: mform element.
        }

        // Default section.
        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // -> I do not want JS form validation if the page is submitted through the "previous" button.
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Simply add a dummy star to the item and the footer note about mandatory fields.
                if ($this->position != SURVEYPRO_POSITIONLEFT) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    if ($this->style == SURVEYPROFIELD_BOOLEAN_USESELECT) {
                        $starplace = $this->itemname;
                    } else { // SURVEYPROFIELD_BOOLEAN_USERADIOV or SURVEYPROFIELD_BOOLEAN_USERADIOH
                        $starplace = $this->itemname.'_group';
                    }
                }
                $mform->_required[] = $starplace;
            }

            switch ($this->defaultoption) {
                case SURVEYPRO_INVITEDEFAULT:
                    $mform->setDefault($this->itemname, SURVEYPRO_INVITEVALUE);
                    break;
                case SURVEYPRO_CUSTOMDEFAULT:
                    $mform->setDefault($this->itemname, $this->defaultvalue);
                    break;
                case SURVEYPRO_NOANSWERDEFAULT:
                    $mform->setDefault($this->itemname, SURVEYPRO_NOANSWERVALUE);
                    break;
                default:
                    $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        } else {
            $mform->setDefault($this->itemname, SURVEYPRO_IGNOREMEVALUE);
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
        // This plugin displays as dropdown menu or a radio buttons set. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if ($searchform) {
            return;
        }

        $errorkey = ($this->style != SURVEYPROFIELD_BOOLEAN_USESELECT) ? $this->itemname.'_group' : $this->itemname;

        // I need to check value is different from SURVEYPRO_INVITEVALUE even if it is not required
        if ($data[$this->itemname] == SURVEYPRO_INVITEVALUE) {
            $errors[$errorkey] = get_string('uerr_booleannotset', 'surveyprofield_boolean');
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
            // Only garbage after the first index, but user wrote it.
            foreach ($indexsubset as $index) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname;
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $index;
                $disabilitationinfo[] = $mformelementinfo;
            }
        }

        // Only garbage but user wrote it.
        if ($labelsubset) {
            foreach ($labelsubset as $k => $label) {
                $mformelementinfo = new stdClass();
                $mformelementinfo->parentname = $this->itemname;
                $mformelementinfo->operator = 'neq';
                $mformelementinfo->content = $label;
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
     * @param string $childparentvalue:
     * @param array $data:
     * @return boolean: true: if the item is welcome; false: if the item must be dropped out
     */
    public function userform_child_item_allowed_dynamic($childparentvalue, $data) {
        // 1) I am a boolean item
        // 2) in $data I can ONLY find $this->itemname
        return ($data[$this->itemname] == $childparentvalue);
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
            if ($answer['mainelement'] == SURVEYPRO_INVITEVALUE) {
                $olduseranswer->content = null;
            } else {
                $olduseranswer->content = $answer['mainelement'];
            }
        }
    }

    /**
     * this method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time
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
            $prefill[$this->itemname] = $fromdb->content;
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

        // Output.
        $answers = explode('/', get_string($format, 'surveyprofield_boolean'));
        $return = ($content) ? $answers[0] : $answers[1];

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
        if ($this->style == SURVEYPROFIELD_BOOLEAN_USESELECT) {
            $elementnames[] = $this->itemname;
        } else {
            $elementnames[] = $this->itemname.'_group';
        }

        return $elementnames;
    }
}
