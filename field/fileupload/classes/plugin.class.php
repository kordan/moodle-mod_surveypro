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
require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

class mod_surveypro_field_fileupload extends mod_surveypro_itembase {

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
     * $maxfiles = the maximum number of files allowed to upload
     */
    protected $maxfiles;

    /**
     * $maxbytes = the maximum allowed size of the file to upload
     */
    protected $maxbytes;

    /**
     * $filetypes = list of allowed file extension
     */
    protected $filetypes;

    /**
     * static canbeparent
     */
    protected static $canbeparent = false;

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param stdClass $cm
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent. To include $item->parentcontent (as decoded by the parent item) too.
     */
    public function __construct($cm, $surveypro, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'fileupload';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['insearchform'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent. To include $item->parentcontent (as decoded by the parent item) too.
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
     * item_save
     *
     * @param $record
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
     * item_get_canbeparent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function item_get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     *
     * @param $record
     * @return void
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * item_add_mandatory_plugin_fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Attachment';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->variable = 'fileupload_001';
        $record->indent = 0;
        $record->maxfiles = 1;
        $record->maxbytes = 1048576;
        $record->filetypes = '*';
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
    <xs:element name="surveyprofield_fileupload">
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

                <xs:element type="xs:int" name="maxfiles"/>
                <xs:element type="xs:int" name="maxbytes"/>
                <xs:element type="xs:string" name="filetypes"/>
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
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        // This plugin has $this->insetupform['insearchform'] = false; so it will never be part of a search form.

        $fieldname = $this->itemname.'_filemanager';

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_fileupload_'.$this->sortindex;

        $filetypes = array_map('trim', explode(',', $this->filetypes));

        $attributes = array();
        $attributes['maxbytes'] = $this->maxbytes;
        $attributes['accepted_types'] = $filetypes;
        $attributes['subdirs'] = false;
        $attributes['maxfiles'] = $this->maxfiles;
        $attributes['id'] = $idprefix;
        $attributes['class'] = 'indent-'.$this->indent; // Does not work: MDL-28194.
        $mform->addElement('mod_surveypro_filemanager', $fieldname, $elementlabel, null, $attributes);

        if ($this->required) {
            // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
            // -> I do not want JS form validation if the page is submitted through the "previous" button.
            // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
            // Simply add a dummy star to the item and the footer note about mandatory fields.
            $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
            $mform->_required[] = $starplace;
        }
    }

    /**
     * userform_mform_validation
     *
     * @param $data
     * @param &$errors
     * @param $surveypro
     * @param $searchform
     * @return void
     */
    public function userform_mform_validation($data, &$errors, $surveypro, $searchform) {
        if ($searchform) {
            return;
        }

        if ($this->required) {
            $errorkey = $this->itemname.'_filemanager';

            $fieldname = $this->itemname.'_filemanager';
            if (empty($data[$fieldname])) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }
    }

    /**
     * userform_get_filling_instructions
     *
     * @param none
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        if ($this->filetypes != '*') {
            // $filetypelist = preg_replace('/([a-zA-Z0-9]+,)([^\s])/', "$1 $2", $this->filetypes);
            $filetypelist = preg_replace('~,(?! )~', ', ', $this->filetypes); // Credits to Sam Marshall

            $fillinginstruction = get_string('fileextensions', 'surveyprofield_fileupload').$filetypelist;
        } else {
            $fillinginstruction = '';
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
     * @param $olduseranswer
     * @param $searchform
     * @return void
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (!empty($answer)) {
            $context = context_module::instance($this->cm->id);
            $fieldname = $this->itemname.'_filemanager';

            $attributes = array();
            $attributes['maxbytes'] = $this->maxbytes;
            $attributes['accepted_types'] = $this->filetypes;
            $attributes['subdirs'] = false;
            $attributes['maxfiles'] = $this->maxfiles;
            file_save_draft_area_files($answer['filemanager'], $context->id, 'surveyprofield_fileupload', SURVEYPROFIELD_FILEUPLOAD_FILEAREA, $olduseranswer->id, $attributes);

            $olduseranswer->content = ''; // Nothing is expected here.
        }
    }

    /**
     * this method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time
     *
     * userform_set_prefill
     *
     * @param $fromdb
     * @return void
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        $context = context_module::instance($this->cm->id);
        $fieldname = $this->itemname.'_filemanager';

        $draftitemid = 0;
        $attributes = array();
        $attributes['maxbytes'] = $this->maxbytes;
        $attributes['accepted_types'] = $this->filetypes;
        $attributes['subdirs'] = false;
        $attributes['maxfiles'] = $this->maxfiles;
        file_prepare_draft_area($draftitemid, $context->id, 'surveyprofield_fileupload', SURVEYPROFIELD_FILEUPLOAD_FILEAREA, $fromdb->id, $attributes);

        $prefill[$fieldname] = $draftitemid;

        return $prefill;
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     *
     * @param $answers
     * @param $format
     * @return void
     */
    public function userform_db_to_export($answer, $format='') {
        // SURVEYPRO_NOANSWERVALUE does not exist here
        $context = context_module::instance($this->cm->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'surveyprofield_fileupload', SURVEYPROFIELD_FILEUPLOAD_FILEAREA, $answer->id);
        $filename = array();
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $filename[] = $file->get_filename();
        }

        return implode(',', $filename);
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return void
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname.'_filemanager');

        return $elementnames;
    }
}
