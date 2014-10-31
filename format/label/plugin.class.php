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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/format/label/lib.php');

class mod_surveypro_format_label extends mod_surveypro_itembase {

    /**
     * $content = the text content of the item.
     */
    public $content = '';

    /**
     * $contenttrust
     */
    public $contenttrust = 1;

    /**
     * public $contentformat = '';
     */
    public $contentformat = '';

    /**
     * $customnumber = the custom number of the item.
     * It usually is 1. 1.1, a, 2.1.a...
     */
    public $customnumber = '';

    // -----------------------------

    /**
     * $fullwidth
     */
    public $fullwidth = '';

    /**
     * $leftlabel = label on the left of the label content
     */
    public $leftlabel = '';

    /**
     * $labelformat = the text format of the item.
     */
    public $leftlabelformat = '';

    /**
     * static canbeparent
     */
    public static $canbeparent = false;

    // -----------------------------

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param stdClass $cm
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFORMAT;
        $this->plugin = 'label';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = false;

        // other element specific properties
        // nothing

        // override properties depending from $surveypro settings
        // nothing

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['position'] = false;
        $this->isinitemform['extranote'] = false;
        $this->isinitemform['required'] = false;
        $this->isinitemform['variable'] = false;
        $this->isinitemform['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (surveypro_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

        // multilang load support for builtin surveypro
        // whether executed, the 'content' field is ALWAYS handled
        $this->item_builtin_string_load_support();
    }

    /**
     * item_save
     *
     * @param $record
     * @return
     */
    public function item_save($record) {
        $this->item_get_common_settings($record);

        // -----------------------------
        // Now execute very specific plugin level actions
        // -----------------------------

        // begin of: plugin specific settings (eventally overriding general ones)
        // do preliminary actions on $record values corresponding to fields type checkbox
        $checkboxes = array('fullwidth');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }
        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_get_multilang_fields
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
     * item_get_plugin_schema
     * Return the xml schema for surveypro_<<plugin>> table.
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
     * userform_mform_element
     *
     * @param $mform
     * @param $searchform
     * @param $readonly
     * @param $submissionid
     * @return
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        // this plugin has $this->isinitemform['insearchform'] = false; so it will never be part of a search form

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

            // workaround suggested by Marina Glancy in MDL-42946
            $content = html_writer::tag('span', $this->get_content(), array('class' => 'indent-'.$this->indent));

            $mform->addElement('static', $this->itemname, $elementlabel, $content);
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
        // nothing to do here
    }

    /**
     * this method is called from surveypro_set_prefill (in locallib.php) to set $prefill at user form display time
     * (defaults are set in userform_mform_element)
     *
     * userform_set_prefill
     *
     * @param $fromdb
     * @return
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

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
        return '';
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return
     */
    public function userform_get_root_elements_name() {
        if ($this->fullwidth) {
            return array();
        } else {
            return array($this->itemname);
        }
    }

    /**
     * get_indent
     *
     * @param none
     * @return
     */
    public function get_indent() {
        if ($this->fullwidth) {
            return false;
        } else {
            return $this->indent;
        }
    }

    /**
     * get_canbeparent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }
}
