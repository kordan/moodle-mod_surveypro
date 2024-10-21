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

    // Itembase properties.

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
     * @var int Defaultvalue for the item answer
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

    // Service variables.

    /**
     * @var bool Does this item use the child table surveypro(field|format)_plugin?
     */
    protected static $usesplugintable = true;

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

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
        $this->insetupform['insearchform'] = false;
        // $this->insetupform['position'] = SURVEYPRO_POSITIONLEFT; <-- What does it mean?

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
        // Set properties at plugin level and then continue to base level.

        // Set custom fields values as defined by this specific plugin.
        // Drop empty rows and trim edging rows spaces from each textarea field.
        $fieldlist = ['options', 'rates', 'defaultvalue'];
        $this->item_clean_textarea_fields($record, $fieldlist);

        // Set custom fields values as defined by this specific plugin.
        $this->add_plugin_properties_to_record($record);

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
    public function item_add_fields_default_to_child_table(&$record) {
        $record->options = "first\nsecond";
        $record->rates = "up\ndown";
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        // $record->defaultvalue
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
    public function add_plugin_properties_to_record($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Position and hideinstructions are set by design.
        $record->position = SURVEYPRO_POSITIONTOP;

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['differentrates'];
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
     * Set rates.
     *
     * @param string $rates
     * @return void
     */
    public function set_rates($rates) {
        $this->rates = $rates;
    }

    /**
     * Set defaultoption.
     *
     * @param string $defaultoption
     * @return void
     */
    public function set_defaultoption($defaultoption) {
        $this->defaultoption = $defaultoption;
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
     * Set style.
     *
     * @param string $style
     * @return void
     */
    public function set_style($style) {
        $this->style = $style;
    }

    /**
     * Set differentrates.
     *
     * @param string $differentrates
     * @return void
     */
    public function set_differentrates($differentrates) {
        $this->differentrates = $differentrates;
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
     * Get rates.
     *
     * @return $this->rates
     */
    public function get_rates() {
        return $this->rates;
    }

    /**
     * Get defaultoption.
     *
     * @return $this->defaultoption
     */
    public function get_defaultoption() {
        return $this->defaultoption;
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
     * Get style.
     *
     * @return $this->style
     */
    public function get_style() {
        return $this->style;
    }

    /**
     * Get differentrates.
     *
     * @return $this->differentrates
     */
    public function get_differentrates() {
        return $this->differentrates;
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
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = ['options', 'rates', 'defaultoption', 'defaultvalue', 'downloadformat', 'style', 'differentrates'];
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
        $fieldlist['surveyprofield_rate'] = ['options', 'rates', 'defaultvalue'];

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
                <xs:element name="options" type="xs:string"/>
                <xs:element name="rates" type="xs:string"/>
                <xs:element name="defaultoption" type="xs:int" minOccurs="0"/>
                <xs:element name="defaultvalue" type="xs:string" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:int" minOccurs="0"/>
                <xs:element name="style" type="xs:int" minOccurs="0"/>
                <xs:element name="differentrates" type="xs:int" minOccurs="0"/>
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
        $rates = $this->get_textarea_content(SURVEYPRO_LABELS, 'rates');
        $defaultvalues = $utilityitemman->multilinetext_to_array($this->defaultvalue);

        $class = ['class' => 'indent-'.$this->indent];
        $baseid = 'id_field_rate_'.$this->sortindex;
        $elementgroup = [];
        $attributes = [];
        $basename = $this->itemname;

        if (($this->defaultoption == SURVEYPRO_INVITEDEFAULT)) {
            if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
                $rates += [SURVEYPRO_INVITEVALUE => get_string('choosedots')];
            } else {
                $rates = [SURVEYPRO_INVITEVALUE => get_string('choosedots')] + $rates;
            }
        }

        if ($this->style == SURVEYPROFIELD_RATE_USERADIO) {
            foreach ($options as $row => $option) {
                $elementgroup = [];
                $uniquename = $basename.'_'.$row;
                foreach ($rates as $col => $rate) {
                    $attributes['id'] = $baseid.'_'.$row.'_'.$col;
                    $elementgroup[] = $mform->createElement('radio', $uniquename, '', $rate, $col, $attributes);
                }
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, ' ', false, $class);

                // Don' add a colorunifier div after the last rate element.
                if ($row < $optioncount) {
                    $this->item_add_color_unifier($mform);
                }
            }
        }

        if ($this->style == SURVEYPROFIELD_RATE_USESELECT) {
            foreach ($options as $row => $option) {
                $elementgroup = [];
                $uniquename = $basename.'_'.$row;
                $attributes['id'] = $baseid.'_'.$row;
                $elementgroup[] = $mform->createElement('select', $uniquename, '', $rates, $attributes);
                $mform->addGroup($elementgroup, $uniquename.'_group', $option, '', false, $class);

                // Don't add a colorunifier div after the last rate element.
                if ($row < $optioncount) {
                    $this->item_add_color_unifier($mform);
                }
            }
        }

        if (!$this->required) { // This is the last if it exists.
            $this->item_add_color_unifier($mform);

            $elementgroup = [];
            $noanswerstr = get_string('noanswer', 'mod_surveypro');
            $attributes['id'] = $baseid.'_noanswer';
            $elementgroup[] = $mform->createElement('advcheckbox', $basename.'_noanswer', '', $noanswerstr, $attributes);
            $mform->addGroup($elementgroup, $basename.'_noanswer_group', $noanswerstr, '', false, $class);
        }

        if ($this->required) {
            // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
            // I do not want JS form validation if the page is submitted through the "previous" button.
            // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
            // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
            $mform->_required[] = $basename.'_extrarow_group';
        } else {
            // Disable if $basename.'_noanswer' is selected.
            $optionindex = 0;
            foreach ($options as $row => $option) {
                $uniquename = $basename.'_'.$row;
                $mform->disabledIf($uniquename, $basename.'_noanswer', 'checked');
            }
            if ($this->defaultoption == SURVEYPRO_NOANSWERDEFAULT) {
                $mform->setDefault($basename.'_noanswer', '1');
            }
        }

        switch ($this->defaultoption) {
            case SURVEYPRO_CUSTOMDEFAULT:
                foreach ($options as $row => $option) {
                    $uniquename = $basename.'_'.$row;
                    $defaultindex = array_search($defaultvalues[$row], $rates);
                    $mform->setDefault($uniquename, "$defaultindex");
                }
                break;
            case SURVEYPRO_INVITEDEFAULT:
                foreach ($options as $row => $option) {
                    $uniquename = $basename.'_'.$row;
                    $mform->setDefault($uniquename, SURVEYPRO_INVITEVALUE);
                }
                break;
            case SURVEYPRO_NOANSWERDEFAULT:
                $uniquename = $basename.'_noanswer[checkbox]';
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
            return $errors;
        }

        // If different rates were requested, it is time to verify this.
        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $options = $utilityitemman->multilinetext_to_array($this->options);
        if ((isset($data[$this->itemname.'_noanswer']['checkbox'])) && ($data[$this->itemname.'_noanswer']['checkbox'] == 1)) {
            return $errors; // Nothing to validate.
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
            return $errors;
        }

        if (!empty($this->differentrates)) {
            $optionscount = count($this->get_textarea_content(SURVEYPRO_LABELS, 'options'));
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

        return $errors;
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
                $labels = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');

                $rates = $this->get_textarea_content(SURVEYPRO_VALUES, 'rates');
                foreach ($labels as $col => $label) {
                    $index = $answers[$col];
                    $output[] = $label.SURVEYPROFIELD_RATE_VALUERATESEPARATOR.$rates[$index];
                }
                $return = implode(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, $output);
                break;
            case SURVEYPRO_ITEMRETURNSLABELS:
                $answers = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $content);
                $output = [];
                $labels = $this->get_textarea_content(SURVEYPRO_LABELS, 'options');

                $rates = $this->get_textarea_content(SURVEYPRO_LABELS, 'rates');
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
            $elementnames[] = $this->itemname.'_noanswer';
        }

        return $elementnames;
    }
}
