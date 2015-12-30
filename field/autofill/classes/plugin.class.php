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
require_once($CFG->dirroot.'/mod/surveypro/field/autofill/lib.php');

class mod_surveypro_field_autofill extends mod_surveypro_itembase {

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
     * $hiddenfield = is the static text visible in the mform?
     */
    public $hiddenfield = false;

    /**
     * $element01 = element for $content
     */
    public $element01 = '';
    public $element01_select = '';
    public $element01_text = '';

    /**
     * $element02 = element for $content
     */
    public $element02 = '';
    public $element02_select = '';
    public $element02_text = '';

    /**
     * $element03 = element for $content
     */
    public $element03 = '';
    public $element03_select = '';
    public $element03_text = '';

    /**
     * $element04 = element for $content
     */
    public $element04 = '';
    public $element04_select = '';
    public $element04_text = '';

    /**
     * $element05 = element for $content
     */
    public $element05 = '';
    public $element05_select = '';
    public $element05_text = '';

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
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'autofill';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = false;

        // other element specific properties
        // nothing

        // override properties depending from $surveypro settings
        // nothing

        // list of fields I do not want to have in the item definition form
        $this->isinitemform['required'] = false;
        $this->isinitemform['hideinstructions'] = false;

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid, $evaluateparentcontent)))
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

        // begin of: plugin specific settings (eventually overriding general ones)
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        $record->hideinstructions = 1;
        $checkboxes = array('hiddenfield');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }
        // end of: plugin specific settings (eventually overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
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
     * item_custom_fields_to_form
     * translates the class properties to form fields value
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for autofill contents
        $referencearray = array(''); // <-- take care, the first element is already on board
        for ($i = 1; $i <= SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            $referencearray[] = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
        }

        $items = array();
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            $fieldname = 'element'.$index.'_select';
            if (in_array($this->{'element'.$index}, $referencearray)) {
                $this->{$fieldname} = $this->{'element'.$index};
            } else {
                $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                $this->{$fieldname} = constant($constantname);
                $fieldname = 'element'.$index.'_text';
                $this->{$fieldname} = $this->{'element'.$index};
            }
        }
    }

    /**
     * item_custom_fields_to_db
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {

        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for autofill contents
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($record->{'element'.$index.'_select'})) {
                $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                // intanto aggiorna le variabili
                // per i campi della tabella surveypro_autofill
                if ($record->{'element'.$index.'_select'} == constant($constantname)) {
                    $record->{'element'.$index} = $record->{'element'.$index.'_text'};
                } else {
                    $record->{'element'.$index} = $record->{'element'.$index.'_select'};
                }
            }
        }
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
     * get_can_be_mandatory
     *
     * @param none
     * @return whether the item of this plugin can be mandatory
     */
    public static function item_get_can_be_mandatory() {
        return false;
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
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        if (!$searchform) {
            // $referencearray = array(''); // <-- take care, the first element is already on board
            // for ($i = 1; $i <= SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT; $i++) {
            //     $referencearray[] = constant('SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.sprintf('%02d', $i));
            // }

            $value = $this->userform_get_content($submissionid);
            $mform->addElement('hidden', $this->itemname, $value);
            $mform->setType($this->itemname, PARAM_RAW);

            if (!$this->hiddenfield) {
                // workaround suggested by Marina Glancy in MDL-42946
                $option = array('class' => 'indent-'.$this->indent);
                $mform->addElement('mod_surveypro_static', $this->itemname.'_static', $elementlabel, $value, $option);
            }
        } else {
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', array('class' => 'indent-'.$this->indent));
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'mod_surveypro'));
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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
     * userform_save_preprocessing
     * starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form
     *
     * @param $answer
     * @param $olduseranswer
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        global $DB;

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

        // get the original user actually making the first submission
        // $userid = $DB->get_field('surveypro_submission', 'userid', array('id' => $olduseranswer->submissionid), IGNORE_MULTIPLE);
        // $user = $DB->get_record('user', array('id' => $userid));

        $olduseranswer->content = $this->userform_get_content($olduseranswer->submissionid);
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

        $prefill[$this->itemname] = $fromdb->content;

        return $prefill;
    }

    /**
     * userform_get_content
     *
     * @param $item
     * @return
     */
    public function userform_get_content($submissionid) {
        global $COURSE, $DB, $USER;

        if ($submissionid) {
            $submission = $DB->get_record('surveypro_submission', array('id' => $submissionid), '*', MUST_EXIST);
            $surveypro = $DB->get_record('surveypro', array('id' => $submission->surveyproid), '*', MUST_EXIST);
            $user = $DB->get_record('user', array('id' => $submission->userid));
        } else {
            $surveypro = $DB->get_record('surveypro', array('id' => $this->cm->instance), '*', MUST_EXIST);
            $user = $USER;
        }

        $label = '';
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($this->{'element'.$index})) {
                switch ($this->{'element'.$index}) {
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT01: // submissionid
                        if ($submissionid) {
                            $label .= $submission->id;
                        } else {
                            // if during string build you find a element that can not be valued now,
                            // overwrite $label, break switch and continue both
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // it is the first time I use it! Coooool :-)
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT02: // submissiontime
                        if ($submissionid) {
                            // $format = get_string('strftimetime', 'langconfig');
                            $format = get_string('strftimedaytime', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // if during string build you find a element that can not be valued now,
                            // overwrite $label, break switch and continue both
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // it is the first time I use it! Coooool :-)
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT03: // submissiondate
                        if ($submissionid) {
                            // $format = get_string('strftimedatefullshort', 'langconfig');
                            $format = get_string('strftimedate', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // if during string build you find a element that can not be valued now,
                            // overwrite $label, break switch and continue both
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // it is the first time I use it! Coooool :-)
                        }
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT04: // submissiondateandtime
                        if ($submissionid) {
                            // $format = get_string('strftimedatetimeshort', 'langconfig');
                            $format = get_string('strftimedatetime', 'langconfig');
                            $label .= userdate($submission->timecreated, $format);
                        } else {
                            // if during string build you find a element that can not be valued now,
                            // overwrite $label, break switch and continue both
                            $label = get_string('latevalue', 'surveyprofield_autofill');
                            break 2; // it is the first time I use it! Coooool :-)
                        }
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT05: // userid
                        $label .= $user->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT06: // userfirstname
                        $label .= $user->firstname;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT07: // userlastname
                        $label .= $user->lastname;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT08: // userfullname
                        $label .= fullname($user);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT09: // usergroupid
                        $usergroups = groups_get_user_groups($COURSE->id, $user->id);
                        $label .= implode(', ', $usergroups[0]);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT10: // usergroupname
                        $names = array();
                        $usergroups = groups_get_user_groups($COURSE->id, $user->id);
                        foreach ($usergroups[0] as $groupid) {
                             $names[] = groups_get_group_name($groupid);
                        }
                        $label .= implode(', ', $names);
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT11: // surveyproid
                        $label .= $surveypro->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT12: // surveyproname
                        $label .= $surveypro->name;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT13: // courseid
                        $label .= $COURSE->id;
                        break;
                    case SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT14: // coursename
                        $label .= $COURSE->name;
                        break;
                    default:                                       // label
                        $label .= $this->{'element'.$index};
                }
            }
        }

        return $label;
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
        $elementnames[] = $this->itemname;
        if (!$this->hiddenfield) {
            $elementnames[] = $this->itemname.'_static';
        }

        return $elementnames;
    }
}
