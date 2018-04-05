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
 * This file contains the surveyprofield_fileupload
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

/**
 * Class to manage each aspect of the fileupload item
 *
 * @package   surveyprofield_fileupload
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_fileupload_field extends mod_surveypro_itembase {

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
     * @var int Maximum number of files allowed to upload
     */
    protected $maxfiles;

    /**
     * @var int Maximum allowed size of the file to upload
     */
    protected $maxbytes;

    /**
     * @var string List of allowed file extension
     */
    protected $filetypes;

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
     * @param stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid=0, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'fileupload';
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['trimonsave'] = false;
        $this->insetupform['insearchform'] = false;
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
    }

    /**
     * Item save.
     *
     * @param object $record
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
     * Traslate values from the mform of this item to values for corresponding properties.
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
        // Take care: 'required', 'trimonsave', 'hideinstructions' were already considered in item_get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
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
    <xs:element name="surveyprofield_fileupload">
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

                <xs:element name="maxfiles" type="xs:int"/>
                <xs:element name="maxbytes" type="xs:int"/>
                <xs:element name="filetypes" type="xs:string"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
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
        // This plugin has $this->insetupform['insearchform'] = false; so it will never be part of a search form.

        $fieldname = $this->itemname.'_filemanager';

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_fileupload_'.$this->sortindex;

        $filetypes = array_map('trim', explode(',', $this->filetypes));

        $attributes = array();
        $attributes['id'] = $idprefix;
        $attributes['class'] = 'indent-'.$this->indent.' fileupload_filemanager'; // Does not work: MDL-28194.
        $attributes['maxbytes'] = $this->maxbytes;
        $attributes['accepted_types'] = $filetypes;
        $attributes['subdirs'] = false;
        $attributes['maxfiles'] = $this->maxfiles;
        $mform->addElement('mod_surveypro_fileupload', $fieldname, $elementlabel, null, $attributes);

        if ($this->required) {
            // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
            // I do not want JS form validation if the page is submitted through the "previous" button.
            // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
            // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
            $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname;
            $mform->_required[] = $starplace;
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
        if (!empty($answer)) {
            $context = context_module::instance($this->cm->id);

            $attributes = array();
            $attributes['maxbytes'] = $this->maxbytes;
            $attributes['accepted_types'] = $this->filetypes;
            $attributes['subdirs'] = false;
            $attributes['maxfiles'] = $this->maxfiles;
            file_save_draft_area_files($answer['filemanager'], $context->id, 'surveyprofield_fileupload',
                SURVEYPROFIELD_FILEUPLOAD_FILEAREA, $olduseranswer->id, $attributes);

            $olduseranswer->content = ''; // Nothing is expected here.
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

        $context = context_module::instance($this->cm->id);
        $fieldname = $this->itemname.'_filemanager';

        $draftitemid = 0;
        $attributes = array();
        $attributes['maxbytes'] = $this->maxbytes;
        $attributes['accepted_types'] = $this->filetypes;
        $attributes['subdirs'] = false;
        $attributes['maxfiles'] = $this->maxfiles;

        $filearea = SURVEYPROFIELD_FILEUPLOAD_FILEAREA;
        file_prepare_draft_area($draftitemid, $context->id, 'surveyprofield_fileupload', $filearea, $fromdb->id, $attributes);

        $prefill[$fieldname] = $draftitemid;

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
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname.'_filemanager';

        return $elementnames;
    }
}
