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
 * This file contains the mod_surveypro_field_textarea
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/textarea/lib.php');

/**
 * Class to manage each aspect of the textarea item
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_field_textarea extends mod_surveypro_itembase {

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
     * @var bool Does the item use html editor?
     */
    protected $useeditor;

    /**
     * @var int Number or rows of the text area?
     */
    protected $arearows;

    /**
     * @var int Number or columns of the text area?
     */
    protected $areacols;

    /**
     * @var int Minimum allowed text length
     */
    protected $minlength;

    /**
     * @var int Maximum allowed text length
     */
    protected $maxlength;

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
        $this->plugin = 'textarea';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['insearchform'] = false;

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
     * Item get can be parent.
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
        $record->content = 'Text (long)';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'textarea_001';
        $record->indent = 0;
        $record->useeditor = 0;
        $record->arearows = 10;
        $record->areacols = 60;
        $record->minlength = 0;
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
        if (!strlen($record->minlength)) {
            $record->minlength = 0;
        }

        // 2. Override few values.
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

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings.
        $checkboxes = array('useeditor');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = parent::item_get_multilang_fields();

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

    // MARK get.

    /**
     * Get use editor.
     *
     * @return the content of the $useeditor property
     */
    public function get_useeditor() {
        return $this->useeditor;
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
        // TODO: make $this->insetupform['insearchform'] = true;

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_textarea_'.$this->sortindex;

        $attributes = array();
        $attributes['id'] = $idprefix;
        if (empty($this->useeditor)) {
            $fieldname = $this->itemname;
            $attributes['class'] = 'indent-'.$this->indent.' textarea_textarea';
            $attributes['wrap'] = 'virtual';
            $attributes['rows'] = $this->arearows;
            $attributes['cols'] = $this->areacols;
            $mform->addElement('textarea', $fieldname, $elementlabel, $attributes);
            $mform->setType($fieldname, PARAM_TEXT);
        } else {
            // $attributes['class'] and $attributes['id'] do not work: MDL_28194
            $attributes['class'] = 'indent-'.$this->indent.' textarea_editor';
            $fieldname = $this->itemname.'_editor';
            $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES);
            $mform->addElement('mod_surveypro_editor', $fieldname, $elementlabel, $attributes, $editoroptions);
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $fieldname;
                $mform->_required[] = $starplace;
            }
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

        if (!empty($this->useeditor)) {
            $errorkey = $this->itemname.'_editor';
            $fieldname = $this->itemname.'_editor';
            $itemcontent = $data[$fieldname]['text'];
        } else {
            $errorkey = $this->itemname;
            $fieldname = $this->itemname;
            $itemcontent = $data[$fieldname];
        }

        if (empty($itemcontent)) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return;
        }

        // I don't care if this element is required or not.
        // If the user provides an answer, it has to be compliant with the field validation rules.
        if ( $this->maxlength && (strlen($itemcontent) > $this->maxlength) ) {
            $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyprofield_textarea');
        }
        if (strlen($itemcontent) < $this->minlength) {
            $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyprofield_textarea');
        }
    }

    /**
     * Prepare the string with the filling instruction.
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
     * Starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param array $answer
     * @param object $olduseranswer
     * @param bool $searchform
     * @return void
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (!empty($this->useeditor)) {
            $context = context_module::instance($this->cm->id);
            $olduseranswer->{$this->itemname.'_editor'} = $answer['editor'];

            $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
            $olduseranswer = file_postupdate_standard_editor($olduseranswer, $this->itemname, $editoroptions, $context,
                    'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $olduseranswer->id);
            $olduseranswer->content = $olduseranswer->{$this->itemname};
            $olduseranswer->contentformat = FORMAT_HTML;
        } else {
            $olduseranswer->content = $answer['mainelement'];
        }
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time.
     *
     * @param object $fromdb
     * @return void
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // $fromdb may be boolean false for not existing data
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if (!empty($this->useeditor)) {
                $context = context_module::instance($this->cm->id);

                $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES, 'context' => $context);
                $fromdb->contentformat = FORMAT_HTML;
                $fromdb = file_prepare_standard_editor($fromdb, 'content', $editoroptions, $context, 'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $fromdb->id);

                $prefill[$this->itemname.'_editor'] = $fromdb->content_editor;
            } else {
                $prefill[$this->itemname] = $fromdb->content;
            }
        }

        return $prefill;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
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
