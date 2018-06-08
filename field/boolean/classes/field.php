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
 * This file contains the surveyprofield_boolean
 *
 * @package   surveyprofield_boolean
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/boolean/lib.php');

/**
 * Class to manage each aspect of the boolean item
 *
 * @package   surveyprofield_boolean
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_boolean_field extends mod_surveypro_itembase {

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
     * @var string Value of the default setting (invite, custom...)
     */
    protected $defaultoption;

    /**
     * @var string Value of the field when the form is initially displayed
     */
    protected $defaultvalue;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var int Either SURVEYPROFIELD_BOOLEAN_USERADIOV or SURVEYPROFIELD_BOOLEAN_USESELECT
     */
    protected $style;

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
        $this->plugin = 'boolean';
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['trimonsave'] = false;
        $this->insetupform['hideinstructions'] = false;

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
     * @param array $record
     * @return void
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
     * Prepare values for the mform of this item.
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.
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
        // Hideinstructions is set by design.
        $record->hideinstructions = 1;

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'trimonsave', 'hideinstructions' were already considered in item_get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * Make the list of constraints the child has to respect in order to create a valid relation
     *
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
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function item_get_downloadformats() {
        $options = array();

        for ($i = 1; $i < 11; $i++) {
            $strname = 'strfbool'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $options[$strname] = get_string($strname, 'surveyprofield_boolean');
        }

        return $options;
    }

    /**
     * Get the format recognized (without any really good reason) as friendly.
     *
     * @return the friendly format
     */
    public function item_get_friendlyformat() {
        return 'strfbool01';
    }

    /**
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist[$this->plugin] = array('content', 'extranote');

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
    <xs:element name="surveyprofield_boolean">
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
                <!-- <xs:element name="hideinstructions" type="xs:int"/> -->
                <xs:element name="variable" type="xs:string"/>
                <xs:element name="extranote" type="xs:string" minOccurs="0"/>
                <!-- <xs:element name="trimonsave" type="xs:int"/> -->

                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="xs:int" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:string"/>
                <xs:element name="style" type="xs:int"/>
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

        $idprefix = 'id_surveypro_field_boolean_'.$this->sortindex;

        $yeslabel = get_string('yes');
        $nolabel = get_string('no');
        $noanswerstr = get_string('noanswer', 'mod_surveypro');

        $attributes = array();
        $attributes['class'] = 'indent-'.$this->indent.' boolean_radio';

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
                $options += array(SURVEYPRO_NOANSWERVALUE => $noanswerstr);
            }
            // End of: element values.

            // Begin of: mform element.
            if ($this->required) {
                if (!$searchform) {
                    // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                    // I do not want JS form validation if the page is submitted through the "previous" button.
                    // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                    // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                    $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname;
                    $mform->_required[] = $starplace;
                }
            }
            $attributes['id'] = $idprefix;
            $mform->addElement('select', $this->itemname, $elementlabel, $options, $attributes);
            // End of: mform element.
        } else { // SURVEYPROFIELD_BOOLEAN_USERADIOV or SURVEYPROFIELD_BOOLEAN_USERADIOH.
            $separator = ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOV) ? '<br />' : ' ';
            $elementgroup = array();

            // Begin of: mform element.
            if (!$searchform) {
                if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                    $choosedotsstr = get_string('choosedots');
                    $attributes['id'] = $idprefix.'_invite';
                    $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $this->itemname, '',
                                                            $choosedotsstr, SURVEYPRO_INVITEVALUE, $attributes);
                    if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                        $attributes['class'] = 'boolean_radio';
                    }
                }
            } else {
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $idprefix.'_ignoreme';
                $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $this->itemname, '',
                                                        $starstr, SURVEYPRO_IGNOREMEVALUE, $attributes);
                if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                    $attributes['class'] = 'boolean_radio';
                }
            }

            $attributes['id'] = $idprefix.'_1';
            $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $this->itemname, '', $yeslabel, '1', $attributes);

            if ($this->style == SURVEYPROFIELD_BOOLEAN_USERADIOH) {
                $attributes['class'] = 'boolean_radio';
            }

            $attributes['id'] = $idprefix.'_0';
            $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $this->itemname, '', $nolabel, '0', $attributes);

            if (!$this->required) {
                $attributes['id'] = $idprefix.'_noanswer';
                $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $this->itemname, '',
                                                        $noanswerstr, SURVEYPRO_NOANSWERVALUE, $attributes);
            }
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            // End of: mform element.
        }

        // Default section.
        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                if ($this->position == SURVEYPRO_POSITIONTOP) {
                    $starplace = $this->itemname.'_extrarow';
                } else {
                    if ($this->style == SURVEYPROFIELD_BOOLEAN_USESELECT) {
                        $starplace = $this->itemname;
                    } else { // SURVEYPROFIELD_BOOLEAN_USERADIOV or SURVEYPROFIELD_BOOLEAN_USERADIOH.
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
     * Perform outform and searchform data validation.
     *
     * @param array $data
     * @param array $errors
     * @param bool $searchform
     * @return void
     */
    public function userform_mform_validation($data, &$errors, $searchform) {
        // This plugin displays as dropdown menu or a radio buttons set. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if ($searchform) {
            return;
        }

        $errorkey = ($this->style == SURVEYPROFIELD_BOOLEAN_USESELECT) ? $this->itemname : $this->itemname.'_group';

        // I need to check value is different from SURVEYPRO_INVITEVALUE even if it is not required.
        if ($data[$this->itemname] == SURVEYPRO_INVITEVALUE) {
            $errors[$errorkey] = get_string('uerr_booleannotset', 'surveyprofield_boolean');
            return;
        }
    }

    /**
     * From childparentvalue defines syntax for disabledIf.
     *
     * @param string $childparentvalue
     * @return array
     */
    public function userform_get_parent_disabilitation_info($childparentvalue) {
        $parentvalues = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $childparentvalue); // 1;1;0;.

        $disabilitationinfo = array();
        $mformelementinfo = new stdClass();
        $mformelementinfo->parentname = $this->itemname;
        $mformelementinfo->operator = 'neq';
        $mformelementinfo->content = $parentvalues[0];
        $disabilitationinfo[] = $mformelementinfo;

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
        return ($data[$this->itemname] == $childparentvalue);
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
        } else {
            $olduseranswer->content = $answer['mainelement'];
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
            $prefill[$this->itemname] = $fromdb->content;
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
        $answers = explode('/', get_string($format, 'surveyprofield_boolean'));
        $return = ($content) ? $answers[0] : $answers[1];

        return $return;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
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
