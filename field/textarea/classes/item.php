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

    // Itembase properties.

    /**
     * @var bool True if the user input will be trimmed at save time
     */
    protected $trimonsave;

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
        $this->plugin = 'textarea';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
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
        // Set properties at plugin level and then continue to base level.

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
        // $record->trimonsave
        $record->useeditor = 0;
        $record->arearows = 10;
        $record->areacols = 60;
        $record->minlength = 0;
        // $record->maxlength
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

    // MARK set.

    /**
     * Set trimonsave.
     *
     * @param string $trimonsave
     * @return void
     */
    public function set_trimonsave($trimonsave) {
        $this->trimonsave = $trimonsave;
    }

    /**
     * Set useeditor.
     *
     * @param string $useeditor
     * @return void
     */
    public function set_useeditor($useeditor) {
        $this->useeditor = $useeditor;
    }

    /**
     * Set arearows.
     *
     * @param string $arearows
     * @return void
     */
    public function set_arearows($arearows) {
        $this->arearows = $arearows;
    }

    /**
     * Set areacols.
     *
     * @param string $areacols
     * @return void
     */
    public function set_areacols($areacols) {
        $this->areacols = $areacols;
    }

    /**
     * Set minlength.
     *
     * @param string $minlength
     * @return void
     */
    public function set_minlength($minlength) {
        $this->minlength = $minlength;
    }

    /**
     * Set maxlength.
     *
     * @param string $maxlength
     * @return void
     */
    public function set_maxlength($maxlength) {
        $this->maxlength = $maxlength;
    }

    // MARK get.

    /**
     * Get trimonsave.
     *
     * @return $this->trimonsave
     */
    public function get_trimonsave() {
        return $this->trimonsave;
    }

    /**
     * Get useeditor.
     *
     * @return $this->useeditor
     */
    public function get_useeditor() {
        return $this->useeditor;
    }

    /**
     * Get arearows.
     *
     * @return $this->arearows
     */
    public function get_arearows() {
        return $this->arearows;
    }

    /**
     * Get areacols.
     *
     * @return $this->areacols
     */
    public function get_areacols() {
        return $this->areacols;
    }

    /**
     * Get minlength.
     *
     * @return $this->minlength
     */
    public function get_minlength() {
        return $this->minlength;
    }

    /**
     * Get maxlength.
     *
     * @return $this->maxlength
     */
    public function get_maxlength() {
        return $this->maxlength;
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = ['trimonsave', 'useeditor', 'arearows', 'areacols', 'minlength', 'maxlength'];
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
        $fieldlist['surveyprofield_textarea'] = [];

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
    <xs:element name="surveyprofield_textarea">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="trimonsave" type="xs:int" minOccurs="0"/>
                <xs:element name="useeditor" type="xs:int" minOccurs="0"/>
                <xs:element name="arearows" type="xs:int" minOccurs="0"/>
                <xs:element name="areacols" type="xs:int" minOccurs="0"/>
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

        $attributes = [];
        $elementgroup = [];
        $class = ['class' => 'indent-'.$this->indent];
        $baseid = 'id_field_textarea_'.$this->sortindex;
        $basename = $this->itemname;

        $attributes['id'] = $baseid;
        $attributes['rows'] = $this->arearows;
        $attributes['cols'] = $this->areacols;
        if (empty($this->useeditor) || ($searchform)) {
            $attributes['wrap'] = 'virtual';
            if (!$searchform) {
                $elementgroup[] = $mform->createElement('textarea', $basename, $elementlabel, $attributes);
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
                $mform->setType($basename, PARAM_TEXT);
            } else {
                $elementgroup[] = $mform->createElement('textarea', $basename, $elementlabel, $attributes);

                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $baseid.'_ignoreme';
                $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
                $mform->disabledIf($basename.'_group', $basename.'_ignoreme', 'checked');
                $mform->setDefault($basename.'_ignoreme', '1');
            }
        } else {
            $fieldname = $basename.'_editor';
            $editoroptions = ['trusttext' => true, 'subdirs' => true, 'maxfiles' => EDITOR_UNLIMITED_FILES];
            $elementgroup[] = $mform->createElement('editor', $basename.'_editor', $elementlabel, $attributes, $editoroptions);
            $mform->addGroup($elementgroup, $basename.'_editor'.'_group', $elementlabel, ' ', false, $class);
            $mform->setType($basename.'_editor', PARAM_CLEANHTML);
        }

        if (!$searchform) {
            if ($this->required) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                if ($this->position == SURVEYPRO_POSITIONTOP) {
                    $starplace = $basename.'_extrarow_group';
                } else {
                    $starplace = empty($this->useeditor) ? $basename.'_group' : $basename.'_editor_group';
                }
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
            return $errors;
        }

        if (!empty($this->useeditor)) {
            $errorkey = $this->itemname.'_editor_group';
            $fieldname = $this->itemname.'_editor';
            $userinput = $data[$fieldname]['text'];
        } else {
            $errorkey = $this->itemname.'_group';
            $fieldname = $this->itemname;
            $userinput = $data[$fieldname];
        }

        // If trimonsave is asked, make the validation on trimmed text.
        if ($this->trimonsave) {
            if (trim($userinput) != $userinput) {
                $warnings[$errorkey] = get_string('uerr_willbetrimmed', 'mod_surveypro');
            }

            // The variable $userinput is not going to be saved. I can freely modify it.
            $userinput = trim($userinput);
        }

        // If $userinput is empty the story stops here. It doesn't matter if there were requirements on the answer.
        if (empty($userinput)) {
            if ($this->required) {
                $errors[$errorkey] = get_string('required');
            }
            return $errors;
        }

        // I don't care if this element is required or not.
        // If the user provided an answer, it has to be compliant with the field validation rules.
        if ( $this->maxlength && (\core_text::strlen($userinput) > $this->maxlength) ) {
            $errors[$errorkey] = get_string('uerr_texttoolong', 'surveyprofield_textarea');
        }
        if (\core_text::strlen($userinput) < $this->minlength) {
            $errors[$errorkey] = get_string('uerr_texttooshort', 'surveyprofield_textarea');
        }

        if ( $errors && isset($warnings) ) {
            // Always sum $warnings to $errors so if an element has a warning and an error too, the error it will be preferred.
            $errors = array_merge($warnings, $errors);
        }

        return $errors;
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
