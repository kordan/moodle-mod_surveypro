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
 * This file contains the surveyproformat_label
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproformat_label;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/format/label/lib.php');

/**
 * Class to manage each aspect of the label item
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends itembase {

    // Itembase properties.

    /**
     * @var int The label has a dedicated row
     */
    protected $fullwidth;

    /**
     * @var string Label on the left of the label content
     */
    protected $leftlabel;

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
        $this->type = SURVEYPRO_TYPEFORMAT;
        $this->plugin = 'label';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
        $this->insetupform['required'] = false;
        $this->insetupform['position'] = false;
        $this->insetupform['required'] = false;
        $this->insetupform['position'] = false;
        $this->insetupform['variable'] = false;
        $this->insetupform['extranote'] = false;
        $this->insetupform['hideinstructions'] = false;
        $this->insetupform['parentid'] = false;
        $this->insetupform['parentvalue'] = false;

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

        // Set custom fields values as defined by this specific plugin.
        $this->add_plugin_properties_to_record($record);

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Returns if this item has the mandatory attribute.
     *
     * @return bool
     */
    public static function has_mandatoryattribute() {
        return false;
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
        $checkboxes = ['fullwidth'];
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
        $record->fullwidth = 0;
        // $record->leftlabel
    }

    // MARK set.

    /**
     * Set fullwidth.
     *
     * @param string $fullwidth
     * @return void
     */
    public function set_fullwidth($fullwidth) {
        $this->fullwidth = $fullwidth;
    }

    /**
     * Set leftlabel.
     *
     * @param string $leftlabel
     * @return void
     */
    public function set_leftlabel($leftlabel) {
        $this->leftlabel = $leftlabel;
    }

    // MARK get.

    /**
     * Get fullwidth.
     *
     * @return $this->fullwidth
     */
    public function get_fullwidth() {
        return $this->fullwidth;
    }

    /**
     * Get leftlabel.
     *
     * @return $this->leftlabel
     */
    public function get_leftlabel() {
        return $this->leftlabel;
    }

    /**
     * Get indent.
     *
     * @return void
     */
    public function get_indent() {
        if ($this->fullwidth) {
            return false;
        } else {
            return $this->indent;
        }
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = ['fullwidth', 'leftlabel'];
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
        if ($includemetafields) {
            $fieldlist['surveypro_item'] = ['content', 'filename', 'filecontent'];
        } else {
            $fieldlist['surveypro_item'] = ['content'];
        }
        $fieldlist['surveyproformat_label'] = ['leftlabel'];

        return $fieldlist;
    }

    /**
     * get_pdf_template.
     *
     * @return the template to use at response report creation
     */
    public static function get_pdf_template() {
        return SURVEYPRO_2COLUMNSTEMPLATE;
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
    <xs:element name="surveyproformat_label">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="fullwidth" type="xs:int" minOccurs="0"/>
                <xs:element name="leftlabel" type="xs:string" minOccurs="0"/>
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

        if ($this->fullwidth) {
            $content = '';
            $content .= \html_writer::start_tag('div', ['class' => 'fitem']);
            $content .= \html_writer::start_tag('div', ['class' => 'fstatic fullwidth label_static']);
            $content .= $this->get_content();
            $content .= \html_writer::end_tag('div');
            $content .= \html_writer::end_tag('div');
            $mform->addElement('html', $content);
        } else {
            $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
            $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
            $elementlabel = $elementnumber.$this->leftlabel;

            $elementgroup = [];
            $class = ['class' => 'indent-'.$this->indent];
            $elementgroup[] = $mform->createElement('static', $this->itemname, $elementlabel, $this->get_content());
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, '', false, $class);
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
        // Nothing to do here.
        return $errors;
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time.
     *
     * @param object $fromdb
     * @return associative array with disaggregate element values
     */
    public function userform_get_prefill($fromdb) {
        $prefill = [];

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
        return '';
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = [];

        return $elementnames;
    }
}
