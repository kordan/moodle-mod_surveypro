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
 * This file contains the mod_surveypro_field_autofill
 *
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/autofill/lib.php');

/**
 * Class to manage each aspect of the autofill item
 *
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_field_autofill extends mod_surveypro_itembase {

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
     * @var bool $hiddenfield = is the static text visible in the mform?
     */
    protected $hiddenfield;

    /**
     * @var string Element #1 for $content
     */
    protected $element01;

    /**
     * @var string Select of the element #1
     */
    protected $element01select;

    /**
     * @var string Text of the element #1
     */
    protected $element01text;

    /**
     * @var string Element #2 for $content
     */
    protected $element02;

    /**
     * @var string Select of the element #2
     */
    protected $element02select;

    /**
     * @var string Text of the element #2
     */
    protected $element02text;

    /**
     * @var string Element #3 for $content
     */
    protected $element03;

    /**
     * @var string Select of the element #3
     */
    protected $element03select;

    /**
     * @var string Text of the element #3
     */
    protected $element03text;

    /**
     * @var string Element #4 for $content
     */
    protected $element04;

    /**
     * @var string Select of the element #4
     */
    protected $element04select;

    /**
     * @var string Text of the element #4
     */
    protected $element04text;

    /**
     * @var string Element #5 for $content
     */
    protected $element05;

    /**
     * @var string Select of the element #5
     */
    protected $element05select;

    /**
     * @var string Text of the element #5
     */
    protected $element05text;

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
        $this->plugin = 'autofill';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['required'] = false;
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
        $record->content = 'Autofill';
        $record->contentformat = 1;
        $record->position = 0;
        $record->variable = 'autofill_001';
        $record->indent = 0;
        $record->hiddenfield = 0;
        $record->element01 = 'userid';
    }

    /**
     * Prepare values for the mform of this item.
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        // Nothing to do: they don't exist in this plugin.

        // 3. special management for autofill contents
        $referencearray = array(''); // <-- take care, the first element is already on board
        for ($i = 1; $i <= SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            $referencearray[] = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        }

        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index.'select';
            if (in_array($this->{'element'.$index}, $referencearray)) {
                $this->{$fieldname} = $this->{'element'.$index};
            } else {
                $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                $this->{$fieldname} = constant($constantname);
                $fieldname = 'element'.$index.'text';
                $this->{$fieldname} = $this->{'element'.$index};
            }
        }
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
        // Hideinstructions is set by design.
        $record->hideinstructions = 1;

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings.
        $checkboxes = array('hiddenfield');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other: special management for autofill contents
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($record->{'element'.$index.'_select'})) {
                $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                // In the meantime, update variables linked to fields of table surveypro_autofill.
                if ($record->{'element'.$index.'_select'} == constant($constantname)) {
                    $record->{'element'.$index} = $record->{'element'.$index.'_text'};
                } else {
                    $record->{'element'.$index} = $record->{'element'.$index.'_select'};
                }
            }
        }
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
    <xs:element name="surveyprofield_autofill">
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
                <xs:element type="xs:string" name="variable"/>
                <xs:element type="xs:int" name="indent"/>

                <xs:element type="xs:int" name="hiddenfield"/>
                <xs:element type="xs:string" name="element01" minOccurs="0"/>
                <xs:element type="xs:string" name="element02" minOccurs="0"/>
                <xs:element type="xs:string" name="element03" minOccurs="0"/>
                <xs:element type="xs:string" name="element04" minOccurs="0"/>
                <xs:element type="xs:string" name="element05" minOccurs="0"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    /**
     * Get can be mandatory.
     *
     * @return whether the item of this plugin can be mandatory
     */
    public static function item_uses_mandatory_dbfield() {
        return false;
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
        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_autofill_'.$this->sortindex;

        if (!$searchform) {
            // At this level I ALWAYS write the content as if the record is new.
            // If the record is not new, this value will be overwritten later at default apply time.
            $value = $this->userform_get_content(0);

            $mform->addElement('hidden', $this->itemname, $value);
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->setDefault($this->itemname, $value);

            if (!$this->hiddenfield) {
                $attributes = array();
                $attributes['id'] = $idprefix;
                $attributes['class'] = 'indent-'.$this->indent.' autofill_text';
                $attributes['disabled'] = 'disabled';
                $mform->addElement('text', $this->itemname.'_static', $elementlabel, $attributes);
                $mform->setType($this->itemname.'_static', PARAM_RAW);
                $mform->setDefault($this->itemname.'_static', $value);
            }
        } else {
            $attributes = array();
            $elementgroup = array();

            $attributes['id'] = $idprefix;
            $attributes['class'] = 'indent-'.$this->indent.' autofill_text';
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', $attributes);

            $attributes['id'] = $idprefix.'_ignoreme';
            $attributes['class'] = 'autofill_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'), $attributes);

            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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
        // Nothing to do here.
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
        if ($searchform) {
            if (isset($answer['ignoreme'])) {
                $olduseranswer->content = null;
            } else {
                if (isset($answer['mainelement'])) {
                    $olduseranswer->content = $answer['mainelement'];
                } else {
                    $a = '$answer = '.$answer;
                    print_error('unhandledvalue', 'mod_surveypro', null, $a);
                }
            }
            return;
        }

        $olduseranswer->content = $this->userform_get_content($olduseranswer->submissionid);
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
            $prefill[$this->itemname] = $fromdb->content;
            if (!$this->hiddenfield) {
                $prefill[$this->itemname.'_static'] = $fromdb->content;
            }
        }

        return $prefill;
    }


    /**
     * Get the content for the autofill element.
     *
     * @param int $submissionid
     * @return string
     */
    public function userform_get_content($submissionid) {
        global $COURSE, $DB, $USER;

        if ($submissionid) {
            $submission = $DB->get_record('surveypro_submission', array('id' => $submissionid), '*', MUST_EXIST);
            $user = $DB->get_record('user', array('id' => $submission->userid));
        } else {
            $user = $USER;
        }

        $label = '';
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($this->{'element'.$index})) {
                switch ($this->{'element'.$index}) {
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT01: // Submissionid.
                        if ($submissionid) {
                            $label .= $submission->id;
                        } else {
                            // If during string build you find an element that cannot be evaluated now,
                            // overwrite $label, break switch and continue both.
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // It is the first time I use it! Coooool :-).
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT02: // Submissiontime.
                        if ($submissionid) {
                            $format = get_string('strftimedaytime', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // If during string build you find an element that cannot be evaluated now,
                            // overwrite $label, break switch and continue both.
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // It is the first time I use it! Coooool :-).
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT03: // Submissiondate.
                        if ($submissionid) {
                            $format = get_string('strftimedate', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // If during string build you find an element that cannot be evaluated now,
                            // overwrite $label, break switch and continue both.
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // It is the first time I use it! Coooool :-).
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT04: // Submissiondateandtime.
                        if ($submissionid) {
                            $format = get_string('strftimedatetime', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // If during string build you find an element that cannot be evaluated now,
                            // overwrite $label, break switch and continue both.
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // It is the first time I use it! Coooool :-).
                        }
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT05: // Userid.
                        $label .= $user->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT06: // Userfirstname.
                        $label .= $user->firstname;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT07: // Userlastname.
                        $label .= $user->lastname;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT08: // Userfullname.
                        $label .= fullname($user);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT09: // Usergroupid.
                        $usergroups = groups_get_user_groups($COURSE->id, $user->id);
                        $label .= implode(', ', $usergroups[0]);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT10: // Usergroupname.
                        $names = array();
                        $usergroups = groups_get_user_groups($COURSE->id, $user->id);
                        foreach ($usergroups[0] as $groupid) {
                             $names[] = groups_get_group_name($groupid);
                        }
                        $label .= implode(', ', $names);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT11: // Surveyproid.
                        $label .= $this->surveypro->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT12: // Surveyproname.
                        $label .= $this->surveypro->name;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT13: // Courseid.
                        $label .= $COURSE->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT14: // Coursename.
                        $label .= $COURSE->name;
                        break;
                    default:                                       // Label.
                        $label .= $this->{'element'.$index};
                }
            }
        }

        return $label;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname;
        if (!$this->hiddenfield) {
            $elementnames[] = $this->itemname.'_static';
        }

        return $elementnames;
    }
}
