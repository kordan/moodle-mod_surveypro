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
 * This file contains the surveyprofield_rate
 *
 * @package   surveyprofield_rate
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_rate;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;
use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/mod/surveypro/field/rate/lib.php');

/**
 * Class to manage each aspect of the rate item
 *
 * @package   surveyprofield_rate
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends itembase {

    /**
     * @var string $content
     */
    public $content = '';

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
     * @var boolean True if the instructions are going to be shown in the form; false otherwise
     */
    protected $hideinstructions;

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
     * @var string list of allowed rates in the form: "$value SURVEYPRO_VALUELABELSEPARATOR $label"
     */
    protected $rates;

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
     * @var bool Style of the rate item: radiobutton or dropdown menu?
     */
    protected $style;

    /**
     * @var bool Force the user to use different rates at answer time
     */
    protected $differentrates;

    /**
     * @var bool Can this item be parent?
     */
    protected static $canbeparent = false;

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
        $this->plugin = 'rate';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['insearchform'] = false;
        $this->insetupform['position'] = SURVEYPRO_POSITIONLEFT;

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
        $this->get_common_settings($record);

        // Now execute very specific plugin level actions.

        // Begin of: plugin specific settings (eventually overriding general ones).
        // Set custom fields value as defined for this question plugin.
        // Drop empty rows and trim edging rows spaces from each textarea field.
        $fieldlist = ['options', 'rates', 'defaultvalue'];
        $this->item_clean_textarea_fields($record, $fieldlist);

        // Set custom fields value as defined for this question plugin.
        $this->item_custom_fields_to_db($record);
        // End of: plugin specific settings (eventually overriding general ones).

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Rate';
        $record->contentformat = 1;
        $record->position = 1;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'rate_001';
        $record->indent = 0;
        $record->options = "first\nsecond";
        $record->rates = "up\ndown";
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->downloadformat = SURVEYPRO_ITEMRETURNSLABELS;
        $record->style = 0;
        $record->differentrates = 0;
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
        // Position and hideinstructions are set by design.
        $record->position = SURVEYPRO_POSITIONTOP;

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['hideinstructions', 'differentrates'];
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * Item_left_position_allowed.
     *
     * @return boolean
     */
    public function item_left_position_allowed() {
        return false;
    }

    // MARK get.

    /**
     * Is this item available as a parent?
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function get_downloadformats() {
        $options = [];

        $options[SURVEYPRO_ITEMSRETURNSVALUES] = get_string('returnvalues', 'surveyprofield_rate');
        $options[SURVEYPRO_ITEMRETURNSLABELS] = get_string('returnlabels', 'surveyprofield_rate');
        $options[SURVEYPRO_ITEMRETURNSPOSITION] = get_string('returnposition', 'surveyprofield_rate');

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
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function get_multilang_fields() {
        $fieldlist = [];
        $fieldlist[$this->plugin] = ['content', 'extranote', 'options', 'rates', 'defaultvalue'];

        return $fieldlist;
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
    <xs:element name="surveyprofield_rate">
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

                <xs:element name="options" type="xs:string"/>
                <xs:element name="rates" type="xs:string"/>
                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:int"/>
                <xs:element name="style" type="xs:int"/>
                <xs:element name="differentrates" type="xs:int"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
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
        // This plugin has $this->insetupform['insearchform'] = false; so it will never be part of a search form.

        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $options = $utilityitemman->multilinetext_to_array($this->options);
        $optioncount = count($options) - 1;
        $rates = $this->get_content_array(SURVEYPRO_LABELS, 'rates');
        $defaultvalues = $utilityitemman->multilinetext_to_array($this->defaultvalue);

        $idprefix = 'id_surveypro_field_rate_'.$this->sortindex;

        if (($this->defaultoption == SURVEYPRO_INVITEDEFAULT)) {
            if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                $rates += [SURVEYPRO_INVITEVALUE => get_string('choosedots')];
            } else {
                $rates = [SURVEYPRO_INVITEVALUE => get_string('choosedots')] + $rates;
            }
        }

        $attributes = [];
        if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
            foreach ($options as $row => $option) {
                $attributes['class'] = 'indent-'.$this->indent.' rate_radio';
                $uniquename = $this->itemname.'_'.$row;
                $elementgroup = [];
                foreach ($rates as $col => $rate) {
                    $attributes['id'] = $idprefix.'_'.$row.'_'.$col;
                    $elementgroup[] = $mform->createElement('mod_surveypro_radiobutton', $uniquename, '', $rate, $col, $attributes);
                    $attributes['class'] = 'rate_radio';
                }
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, ' ', false);

                // Don' add a colorunifier div after the last rate element.
                if ($row < $optioncount) {
                    $this->item_add_color_unifier($mform);
                }
            }
        }

        if ($this->style == SURVEYPROFIELD_RATE_USESELECT) {
            $attributes['class'] = 'indent-'.$this->indent.' rate_select';
            foreach ($options as $row => $option) {
                $uniquename = $this->itemname.'_'.$row;
                $attributes['id'] = $idprefix.'_'.$row;
                $elementgroup = [$mform->createElement('select', $uniquename, '', $rates, $attributes)];
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, '', false);

                // Don't add a colorunifier div after the last rate element.
                if ($row < $optioncount) {
                    $this->item_add_color_unifier($mform);
                }
            }
        }

        if (!$this->required) { // This is the last if it exists.
            $this->item_add_color_unifier($mform);

            // Bloody hack to align the noanswer checkbox according to the indent.
            $elementgroup = [];
            $uniquename = 'checkbox';
            $noanswerstr = get_string('noanswer', 'mod_surveypro');
            $attributes['id'] = $idprefix.'_noanswer';
            $attributes['class'] = 'indent-'.$this->indent.' rate_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_advcheckbox', $uniquename, '', $noanswerstr, $attributes);
            $mform->addGroup($elementgroup, $this->itemname.'_noanswer');
        }

        if ($this->required) {
            // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
            // I do not want JS form validation if the page is submitted through the "previous" button.
            // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
            // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
            $mform->_required[] = $this->itemname.'_extrarow';
        } else {
            // Disable if $this->itemname.'_noanswer' is selected.
            $optionindex = 0;
            foreach ($options as $row => $option) {
                $uniquename = $this->itemname.'_'.$row.'_group';
                $mform->disabledIf($uniquename, $this->itemname.'_noanswer[checkbox]', 'checked');
            }
            if ($this->defaultoption == SURVEYPRO_NOANSWERDEFAULT) {
                $mform->setDefault($this->itemname.'_noanswer[checkbox]', '1');
            }
        }

        switch ($this->defaultoption) {
            case SURVEYPRO_CUSTOMDEFAULT:
                foreach ($options as $row => $option) {
                    $uniquename = $this->itemname.'_'.$row;
                    $defaultindex = array_search($defaultvalues[$row], $rates);
                    $mform->setDefault($uniquename, "$defaultindex");
                }
                break;
            case SURVEYPRO_INVITEDEFAULT:
                foreach ($options as $row => $option) {
                    $uniquename = $this->itemname.'_'.$row;
                    $mform->setDefault($uniquename, SURVEYPRO_INVITEVALUE);
                }
                break;
            case SURVEYPRO_NOANSWERDEFAULT:
                $uniquename = $this->itemname.'_noanswer[checkbox]';
                $mform->setDefault($uniquename, 1);
                break;
            default:
                $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
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
        // This plugin displays as a set of dropdown menu or radio buttons. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if ($searchform) {
            return;
        }

        // If different rates were requested, it is time to verify this.
        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $options = $utilityitemman->multilinetext_to_array($this->options);
        if ((isset($data[$this->itemname.'_noanswer']['checkbox'])) && ($data[$this->itemname.'_noanswer']['checkbox'] == 1)) {
            return; // Nothing to validate.
        }

        $return = false;
        foreach ($options as $optionindex => $unused) {
            $uniquename = $this->itemname.'_'.$optionindex;
            $elementname = $uniquename.'_group';
            if ($data[$uniquename] == SURVEYPRO_INVITEVALUE) {
                $errors[$elementname] = get_string('uerr_optionnotset', 'surveyprofield_rate');
                $return = true;
            }
        }
        if ($return) {
            return;
        }

        if (!empty($this->differentrates)) {
            $optionscount = count($this->get_content_array(SURVEYPRO_LABELS, 'options'));
            $rates = [];
            for ($i = 0; $i < $optionscount; $i++) {
                $rates[] = $data[$this->itemname.'_'.$i];
            }

            $uniquerates = array_unique($rates);
            $duplicaterates = array_diff_assoc($rates, $uniquerates);

            foreach ($duplicaterates as $row => $unused) {
                $elementname = $this->itemname.'_'.$optionindex.'_group';
                $errors[$elementname] = get_string('uerr_duplicaterate', 'surveyprofield_rate');
            }
        }
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        if (!empty($this->differentrates)) {
            $fillinginstruction = get_string('diffratesrequired', 'surveyprofield_rate');
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
        if (isset($answer['noanswer']['checkbox']) && ($answer['noanswer']['checkbox'] == 1)) {
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            $return = [];
            foreach ($answer as $answeredrate) {
                if (!is_array($answeredrate)) {
                    $return[] = $answeredrate;
                }
            }
            $olduseranswer->content = implode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $return);
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
                $prefill[$this->itemname.'_noanswer']['checkbox'] = 1;
            } else {
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $fromdb->content);

                foreach ($answers as $optionindex => $unused) {
                    $uniquename = $this->itemname.'_'.$optionindex;
                    $prefill[$uniquename] = $answers[$optionindex];
                }
            }
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
        // Here $answers is an array like: [1,1,0,0].
        switch ($format) {
            case SURVEYPRO_ITEMSRETURNSVALUES:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = [];
                $labels = $this->get_content_array(SURVEYPRO_LABELS, 'options');

                $rates = $this->get_content_array(SURVEYPRO_VALUES, 'rates');
                foreach ($labels as $col => $label) {
                    $index = $answers[$col];
                    $output[] = $label.SURVEYPROFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = [];
                $labels = $this->get_content_array(SURVEYPRO_LABELS, 'options');

                $rates = $this->get_content_array(SURVEYPRO_LABELS, 'rates');
                foreach ($labels as $col => $label) {
                    $index = $answers[$col];
                    $output[] = $label.SURVEYPROFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSPOSITION:
                // Here I will ALWAYS HAVE 0;1;6;4;0;7 so each separator is welcome, even ','.
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

        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $options = $utilityitemman->multilinetext_to_array($this->options);

        foreach ($options as $row => $option) {
            $elementnames[] = $this->itemname.'_'.$row.'_group';
        }

        if (!$this->required) {
            $elementnames[] = $this->itemname.'_noanswer[checkbox]';
        }

        return $elementnames;
    }
}
