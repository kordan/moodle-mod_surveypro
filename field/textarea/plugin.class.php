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
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/textarea/lib.php');

class surveyprofield_textarea extends mod_surveypro_itembase {

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

    /**
     * $position = where does the question go?
     */
    public $position = SURVEYPRO_POSITIONLEFT;

    /**
     * $extranote = an optional text describing the item
     */
    public $extranote = '';

    /**
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

    /**
     * $hideinstructions = boolean. Exceptionally hide filling instructions
     */
    public $hideinstructions = 0;

    /**
     * $variable = the name of the field storing data in the db table
     */
    public $variable = '';

    /**
     * $indent = the indent of the item in the form page
     */
    public $indent = 0;

    // -----------------------------

    /**
     * $useeditor = does the item use html editor?.
     */
    public $useeditor = true;

    /**
     * $arearows = number or rows of the text area?
     */
    public $arearows = 10;

    /**
     * $areacols = number or columns of the text area?
     */
    public $areacols = 60;

    /**
     * $minlength = the minimum allowed text length
     */
    public $minlength = '0';

    /**
     * $maxlength = the maximum allowed text length
     */
    public $maxlength = null;

    /**
     * $flag = features describing the object
     */
    public $flag;

    /**
     * $canbeparent
     */
    public static $canbeparent = false;

    // -----------------------------

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     */
    public function __construct($itemid=0, $evaluateparentcontent) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whether this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'textarea';

        $this->flag = new stdClass();
        $this->flag->issearchable = false;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA);
        $this->flag->savepositiontodb = false;

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['insearchform'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

        // multilang load support for builtin surveypro
        // whether executed, the 'content' field is ALWAYS handled
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

        // -----------------------------
        // Now execute very specific plugin level actions
        // -----------------------------

        // begin of: plugin specific settings (eventally overriding general ones)
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // do preliminary actions on $record values corresponding to fields type checkbox
        $checkboxes = array('useeditor');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }
        if (!strlen($record->minlength)) {
            $record->minlength = 0;
        }
        if (!strlen($record->maxlength)) {
            $record->maxlength = null;
        }
        if (empty($record->arearows)) {
            $record->arearows = SURVEYPROFIELD_TEXTAREA_DEFAULTROWS;
        }
        if (empty($record->areacols)) {
            $record->areacols = SURVEYPROFIELD_TEXTAREA_DEFAULTCOLS;
        }
        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_custom_fields_to_form
     * add checkboxes selection for empty fields
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin
        if (!strlen($record->minlength)) {
            $record->minlength = 0;
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();

        return $fieldlist;
    }

    // MARK get

    /**
     * get_useeditor
     *
     * @param $field
     * @return
     */
    public function get_useeditor() {
        return $this->useeditor;
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
    <xs:element name="surveyprofield_textarea">
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
                <xs:element type="xs:int" name="hideinstructions"/>
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="useeditor"/>
                <xs:element type="xs:int" name="arearows"/>
                <xs:element type="xs:int" name="areacols"/>
                <xs:element type="xs:int" name="minlength" minOccurs="0"/>
                <xs:element type="xs:int" name="maxlength" minOccurs="0"/>
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
        // this plugin has $this->flag->issearchable = false; so it will never be part of a search form
        // TODO: make issearchable true

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        if (!empty($this->useeditor)) {
            $fieldname = $this->itemname.'_editor';
            $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
            $mform->addElement('editor', $fieldname, $elementlabel, array('class' => 'indent-'.$this->indent), $editoroptions);
            $mform->setType($fieldname, PARAM_CLEANHTML);
        } else {
            $fieldname = $this->itemname;
            $textareaoptions = array('maxfiles' => 0, 'maxbytes' => 0, 'trusttext' => false);
            $mform->addElement('textarea', $fieldname, $elementlabel, array('wrap' => 'virtual', 'rows' => $this->arearows, 'cols' => $this->areacols, 'class' => 'indent-'.$this->indent));
            $mform->setType($fieldname, PARAM_TEXT);
        }

        if (!$searchform) {
            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
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
        if ($searchform) {
            return;
        }

        if (!empty($this->useeditor)) {
            $errorkey = $this->itemname.'_editor';
        } else {
            $errorkey = $this->itemname;
        }

        if (!empty($this->useeditor)) {
            $fieldname = $this->itemname.'_editor';
        } else {
            $fieldname = $this->itemname;
        }

        if ($this->required) {
            if (empty($data[$fieldname])) {
                $errors[$errorkey] = get_string('required');
            }
        }

        if ($this->useeditor) {
            $itemcontent = $data[$fieldname]['text'];
        } else {
            $itemcontent = $data[$fieldname];
        }

        if ( $this->maxlength && (strlen($itemcontent) > $this->maxlength) ) {
            $errors[$errorkey] = get_string('texttoolong', 'surveyprofield_textarea');
        }
        if (strlen($itemcontent) < $this->minlength) {
            $errors[$errorkey] = get_string('texttooshort', 'surveyprofield_textarea');
        }
    }

    /**
     * userform_get_filling_instructions
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        if ($this->minlength > 0) {
            if (isset($this->maxlength) && ($this->maxlength > 0)) {
                $a = new StadClass();
                $a->minlength = $this->minlength;
                $a->maxlength = $this->maxlength;
                $fillinginstruction = get_string('hasminmaxlength', 'surveyprofield_textarea', $a);
            } else {
                $a = $this->minlength;
                $fillinginstruction = get_string('hasminlength', 'surveyprofield_textarea', $a);
            }
        } else {
            if (isset($this->maxlength) && ($this->maxlength > 0)) {
                $a = $this->maxlength;
                $fillinginstruction = get_string('hasmaxlength', 'surveyprofield_textarea', $a);
            } else {
                $fillinginstruction = '';
            }
        }

        return $fillinginstruction;
    }

    /**
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param $answer
     * @param $olduserdata
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata, $searchform) {
        if (!empty($this->useeditor)) {
            $olduserdata->{$this->itemname.'_editor'} = $answer['editor'];

            $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
            $olduserdata = file_postupdate_standard_editor($olduserdata, $this->itemname, $editoroptions, $this->context,
                    'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $olduserdata->id);
            $olduserdata->content = $olduserdata->{$this->itemname};
            $olduserdata->contentformat = FORMAT_HTML;
        } else {
            $olduserdata->content = $answer['mainelement'];
        }
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

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if (!empty($this->useeditor)) {
                $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'context' => $this->context);
                $fromdb->contentformat = FORMAT_HTML;
                $fromdb = file_prepare_standard_editor($fromdb, 'content', $editoroptions, $this->context, 'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $fromdb->id);

                $prefill[$this->itemname.'_editor'] = $fromdb->content_editor;
            } else {
                $prefill[$this->itemname] = $fromdb->content;
            }
        }

        return $prefill;
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
        if (!empty($this->useeditor)) {
            $elementnames[] = $this->itemname.'_editor';
        } else {
            $elementnames[] = $this->itemname;
        }

        return $elementnames;
    }
}
