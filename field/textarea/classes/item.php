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
 * This file contains the surveyprofield_textarea
 *
 * @package   surveyprofield_textarea
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_textarea;

defined('MOODLE_INTERNAL') || die();

use core_text;
use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/field/textarea/lib.php');

/**
 * Class to manage each aspect of the textarea item
 *
 * @package   surveyprofield_textarea
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
     * @var boolean True if the user input will be trimmed at save time
     */
    protected $trimonsave;

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
     * @param \stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'textarea';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        // Empty list.

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
        if (!core_text::strlen($record->minlength)) {
            $record->minlength = 0;
        }

        // 2. Override few values.
        if (!core_text::strlen($record->minlength)) {
            $record->minlength = 0;
        }
        if (!core_text::strlen($record->maxlength)) {
            $record->maxlength = null;
        }
        if (empty($record->arearows)) {
            $record->arearows = SURVEYPROFIELD_TEXTAREA_DEFAULTROWS;
        }
        if (empty($record->areacols)) {
            $record->areacols = SURVEYPROFIELD_TEXTAREA_DEFAULTCOLS;
        }

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['useeditor', 'trimonsave'];
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
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
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function get_multilang_fields() {
        $fieldlist = [];
        $fieldlist[$this->plugin] = ['content', 'extranote'];

        return $fieldlist;
    }

    /**
     * Does the user input need trim?
     *
     * @return if this plugin requires a user input trim
     */
    public function get_trimonsave() {
        return $this->trimonsave;
    }

    /**
     * Get use editor.
     *
     * @return the content of $useeditor property
     */
    public function get_useseditor() {
        return $this->useeditor;
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
    <xs:element name="surveyprofield_textarea">
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

                <xs:element name="useeditor" type="xs:int"/>
                <xs:element name="arearows" type="xs:int"/>
                <xs:element name="areacols" type="xs:int"/>
                <xs:element name="trimonsave" type="xs:int"/>
                <xs:element name="minlength" type="xs:int" minOccurs="0"/>
                <xs:element name="maxlength" type="xs:int" minOccurs="0"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    // MARK response.

    /**
     * Report how the sql query does fit for this plugin
     *
     * @param int $itemid
     * @param string $searchrestriction
     * @return the specific where clause for this plugin
     */
    public static function response_get_whereclause($itemid, $searchrestriction) {
        global $DB;

        $whereclause = $DB->sql_like('a.content', ':content_'.$itemid, false);
        $whereparam = '%'.$searchrestriction.'%';

        return [$whereclause, $whereparam];
    }

    /**
     * Returns if the field plugin needs contentformat
     *
     * @return bool
     */
    public static function response_uses_format() {
        return true;
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
        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $idprefix = 'id_surveypro_field_textarea_'.$this->sortindex;

        $attributes = [];
        $attributes['id'] = $idprefix;
        $attributes['rows'] = $this->arearows;
        $attributes['cols'] = $this->areacols;
        if (empty($this->useeditor) || ($searchform)) {
            $fieldname = $this->itemname;
            $attributes['class'] = 'indent-'.$this->indent.' textarea_textarea';
            $attributes['wrap'] = 'virtual';
            if (!$searchform) {
                $mform->addElement('mod_surveypro_textarea_plain', $fieldname, $elementlabel, $attributes);
                $mform->setType($fieldname, PARAM_TEXT);
            } else {
                $elementgroup = [];
                $elementgroup[] = $mform->createElement('mod_surveypro_textarea_plain', $fieldname, $elementlabel, $attributes);

                $itemname = $this->itemname.'_ignoreme';
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $idprefix.'_ignoreme';
                $attributes['class'] = 'textarea_check';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);
                $mform->setType($this->itemname, PARAM_RAW);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
                $mform->setDefault($this->itemname.'_ignoreme', '1');
            }
        } else {
            // Note: $attributes['class'] and $attributes['id'] do not work: MDL_28194.
            $attributes['class'] = 'indent-'.$this->indent.' textarea_editor';
            $fieldname = $this->itemname.'_editor';
            $editoroptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES];
            $mform->addElement('mod_surveypro_textarea_editor', $fieldname, $elementlabel, $attributes, $editoroptions);
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $fieldname;
                $mform->_required[] = $starplace;
            }
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

        if ($this->trimonsave) {
            $itemcontent = trim($itemcontent);
        }

        if (empty($itemcontent)) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return;
        }

        // I don't care if this element is required or not.
        // If the user provides an answer, it has to be compliant with the field validation rules.
        if ( $this->maxlength && (\core_text::strlen($itemcontent) > $this->maxlength) ) {
            $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyprofield_textarea');
        }
        if (\core_text::strlen($itemcontent) < $this->minlength) {
            $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyprofield_textarea');
        }
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $arrayinstruction = [];

        if ($this->minlength > 0) {
            if (isset($this->maxlength) && ($this->maxlength > 0)) {
                $a = new \stdClass();
                $a->minlength = $this->minlength;
                $a->maxlength = $this->maxlength;
                $arrayinstruction[] = get_string('hasminmaxlength', 'surveyprofield_textarea', $a);
            } else {
                $a = $this->minlength;
                $arrayinstruction[] = get_string('hasminlength', 'surveyprofield_textarea', $a);
            }
        } else {
            if (isset($this->maxlength) && ($this->maxlength > 0)) {
                $a = $this->maxlength;
                $arrayinstruction[] = get_string('hasmaxlength', 'surveyprofield_textarea', $a);
            }
        }
        if ($this->trimonsave) {
            $arrayinstruction[] = get_string('inputclean', 'surveypro');
        }

        $fillinginstruction = implode('; ', $arrayinstruction);

        return $fillinginstruction;
    }

    /**
     * Starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form.
     * Here I set $olduseranswer->contentformat as needed.
     *
     * @param array $answer
     * @param object $olduseranswer
     * @param bool $searchform
     * @return void
     */
    public function userform_get_user_answer($answer, &$olduseranswer, $searchform) {
        if (!empty($this->useeditor) && (!$searchform)) {
            $context = \context_module::instance($this->cm->id);
            $olduseranswer->{$this->itemname.'_editor'} = empty($this->trimonsave) ? $answer['editor'] : trim($answer['editor']);

            $editoroptions = ['trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context];
            $olduseranswer = file_postupdate_standard_editor($olduseranswer, $this->itemname, $editoroptions, $context,
                    'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $olduseranswer->id);
            $olduseranswer->content = $olduseranswer->{$this->itemname};
            $olduseranswer->contentformat = $answer['editor']['format'];
        } else {
            $olduseranswer->content = empty($this->trimonsave) ? $answer['mainelement'] : trim($answer['mainelement']);
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
            if (!empty($this->useeditor)) {
                $context = \context_module::instance($this->cm->id);

                $editoroptions = [];
                $editoroptions['trusttext'] = true;
                $editoroptions['subdirs'] = true;
                $editoroptions['maxfiles'] = EDITOR_UNLIMITED_FILES;
                $editoroptions['context'] = $context;
                $fromdb = file_prepare_standard_editor($fromdb, 'content', $editoroptions, $context,
                                                       'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $fromdb->id);

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
        if (!empty($this->useeditor)) {
            $elementnames = [$this->itemname.'_editor'];
        } else {
            $elementnames = [$this->itemname];
        }

        return $elementnames;
    }

    /**
     * Does the user input need trim?
     *
     * @return if this plugin requires a user input trim
     */
    public static function userform_input_needs_trim() {
        return true;
    }

    /**
     * Starting from the info stored into $answer, this function returns the corresponding content for the export file.
     *
     * @param object $answer
     * @param string $format
     * @return string - the string for the export file
     */
    public function userform_db_to_export($answer, $format='') {
        $context = \context_module::instance($this->cm->id);

        // The content of the provided answer.
        $content = $answer->content;

        // Trigger 'answernotsubmitted' and 'answerisnoanswer'.
        $quickresponse = self::userform_standardcontent_to_string($content);
        if (isset($quickresponse)) { // Parent method provided the response.
            return $quickresponse;
        }

        // Output.
        if (core_text::strlen($content)) {
            if ($this->useeditor) {
                $content = file_rewrite_pluginfile_urls(
                           $content, 'pluginfile.php', $context->id,
                           'mod_surveypro', SURVEYPROFIELD_TEXTAREA_FILEAREA, $answer->id);

                $return = format_text($content, FORMAT_MOODLE, ['overflowdiv' => false, 'allowid' => true, 'para' => false]);
            } else {
                $return = $content;
            }
        } else {
            // User is allowed to provide an empty answer.
            if ($format == SURVEYPRO_FRIENDLYFORMAT) {
                $return = get_string('emptyanswer', 'mod_surveypro');
            } else {
                $return = '';
            }
        }

        return $return;
    }
}
