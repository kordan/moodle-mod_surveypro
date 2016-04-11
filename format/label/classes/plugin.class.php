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
 * This file contains the mod_surveypro_format_label
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/format/label/lib.php');

/**
 * Class to manage each aspect of the label item
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_format_label extends mod_surveypro_itembase {

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
     * $fullwidth
     */
    protected $fullwidth;

    /**
     * $leftlabel = label on the left of the label content
     */
    protected $leftlabel;

    /**
     * $labelformat = the text format of the item.
     */
    protected $leftlabelformat;

    /**
     * @var bool canbeparent
     */
    protected static $canbeparent = false;

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
        $this->type = SURVEYPRO_TYPEFORMAT;
        $this->plugin = 'label';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['position'] = false;
        $this->insetupform['extranote'] = false;
        $this->insetupform['required'] = false;
        $this->insetupform['variable'] = false;
        $this->insetupform['hideinstructions'] = false;

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
        $this->item_custom_fields_to_db($record);
        // End of: plugin specific settings (eventually overriding general ones).

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Item_get_pdf_template
     *
     * @return the template to use at response report creation
     */
    public static function item_get_pdf_template() {
        return SURVEYPRO_2COLUMNSTEMPLATE;
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
        $checkboxes = array('fullwidth');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
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
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Label';
        $record->contentformat = 1;
        $record->indent = 0;
        $record->fullwidth = 0;
    }

    /**
     * Item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();
        $fieldlist['label'] = array('content', 'leftlabel');

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
    <xs:element name="surveyproformat_label">
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
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="fullwidth"/>
                <xs:element type="xs:string" name="leftlabel" minOccurs="0"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
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
        // This plugin has $this->insetupform['insearchform'] = false; so it will never be part of a search form.

        if ($this->fullwidth) {
            $content = '';
            // $content .= html_writer::start_tag('fieldset', array('class' => 'hidden'));
            // $content .= html_writer::start_tag('div', array('class' => 'centerpara'));
            $content .= html_writer::start_tag('div', array('class' => 'fitem'));
            $content .= html_writer::start_tag('div', array('class' => 'fstatic fullwidth'));
            // $content .= html_writer::start_tag('div', array('class' => 'indent-'.$this->indent));
            $content .= $this->get_content();
            // $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div');
            // $content .= html_writer::end_tag('div');
            // $content .= html_writer::end_tag('fieldset');
            $mform->addElement('html', $content);
        } else {
            $labelsep = get_string('labelsep', 'langconfig'); // ': '
            $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
            $elementlabel = $elementnumber.$this->leftlabel;
            $option = array('class' => 'indent-'.$this->indent);
            $mform->addElement('mod_surveypro_static', $this->itemname, $elementlabel, $this->get_content(), $option);
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
        // Nothing to do here.
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time
     *
     * @param object $fromdb
     * @return void
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

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
        return '';
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        if ($this->fullwidth) {
            return array();
        } else {
            return array($this->itemname);
        }
    }

    /**
     * Get indent
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
}
