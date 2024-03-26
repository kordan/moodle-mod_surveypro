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
 * This file contains the surveyprofield_autofill
 *
 * @package   surveyprofield_autofill
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_autofill;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/field/autofill/lib.php');

/**
 * Class to manage each aspect of the autofill item
 *
 * @package   surveyprofield_autofill
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
     * @param \stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'autofill';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['required'] = false;
        $this->insetupform['hideinstructions'] = false;
        $this->insetupform['parentid'] = false;

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
        $referencearray = ['']; // Take care: the first element is already on board.
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
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        $checkboxes = ['hiddenfield'];
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other: special management for autofill contents.
        for ($i = 1; $i < 6; $i++) {
            $index = sprintf('%02d', $i);
            if (!empty($record->{'element'.$index.'select'})) {
                $constantname = 'SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT'.SURVEYPROFIELD_AUTOFILL_CONTENTELEMENT_COUNT;
                // In the meantime, update variables linked to fields of table surveypro_autofill.
                if ($record->{'element'.$index.'select'} == constant($constantname)) {
                    $record->{'element'.$index} = $record->{'element'.$index.'text'};
                } else {
                    $record->{'element'.$index} = $record->{'element'.$index.'select'};
                }
            }
        }
    }

    /**
     * Get can be mandatory.
     *
     * @return whether the item of this plugin can be mandatory
     */
    public static function item_uses_mandatory_dbfield() {
        return false;
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
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function get_plugin_schema() {
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="surveyprofield_autofill">
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

                <!-- <xs:element name="required" type="xs:int"/> -->
                <xs:element name="indent" type="xs:int"/>
                <xs:element name="position" type="xs:int"/>
                <xs:element name="customnumber" type="xs:string" minOccurs="0"/>
                <!-- <xs:element name="hideinstructions" type="xs:int"/> -->
                <xs:element name="variable" type="xs:string"/>
                <xs:element name="extranote" type="xs:string" minOccurs="0"/>

                <xs:element name="hiddenfield" type="xs:int"/>
                <xs:element name="element01" type="xs:string" minOccurs="0"/>
                <xs:element name="element02" type="xs:string" minOccurs="0"/>
                <xs:element name="element03" type="xs:string" minOccurs="0"/>
                <xs:element name="element04" type="xs:string" minOccurs="0"/>
                <xs:element name="element05" type="xs:string" minOccurs="0"/>
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
        global $DB;

        $starstr = get_string('star', 'mod_surveypro');
        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $idprefix = 'id_surveypro_field_autofill_'.$this->sortindex;

        if (!$searchform) {
            // I can not say: "I can write the content as if the record is new because if the record is not new,
            // this value will be overwritten later when defaults will be applied to the form.".
            // This is a label! Defults will not be applied.
            // So, I have to ALWAYS get them now and include them now into the item.

            // Is this a new submission or I am editing an old one?
            $submissionid = $mform->getElementValue('submissionid');
            if ($submissionid) { // I am editing an old submission.
                $wheresql = 'submissionid = :submissionid AND itemid = :itemid';
                $whereparams = ['submissionid' => $submissionid, 'itemid' => $this->itemid];
                $answer = false;
                $answer = $DB->get_record('surveypro_answer', $whereparams);
                if ($answer) {
                    $value = $answer->content;
                } else { // This should never be verified.
                    $message = 'Unexpected lack of answer. ';
                    $message .= 'The submission id '.$submissionid.' exists ';
                    $message .= 'but there is not any answer for the item id '.$this->itemid;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                    $value = 'NULL';
                }
            } else { // I am editing a new submission.
                $value = $this->userform_get_content(0);
            }

            $mform->addElement('hidden', $this->itemname, $value);
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->setDefault($this->itemname, $value);

            if (!$this->hiddenfield) {
                $attributes = ['id' => $idprefix, 'class' => 'indent-'.$this->indent.' label_static'];
                $mform->addElement('mod_surveypro_label', $this->itemname.'_static', $elementlabel, $value, $attributes);
            }
        } else {
            $attributes = [];
            $elementgroup = [];

            $itemname = $this->itemname;
            $attributes['id'] = $idprefix;
            $attributes['class'] = 'indent-'.$this->indent.' autofill_text';
            $elementgroup[] = $mform->createElement('text', $itemname, '', $attributes);

            $itemname = $this->itemname.'_ignoreme';
            $attributes['id'] = $idprefix.'_ignoreme';
            $attributes['class'] = 'autofill_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);

            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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
        // Nothing to do here.
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
    public function userform_get_user_answer($answer, &$olduseranswer, $searchform) {
        if ($searchform) {
            if (isset($answer['ignoreme'])) {
                $olduseranswer->content = null;
            } else {
                if (isset($answer['mainelement'])) {
                    $olduseranswer->content = $answer['mainelement'];
                } else {
                    $a = '$answer = '.$answer;
                    throw new \moodle_exception('unhandledvalue', 'mod_surveypro', null, $a);
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
     * @return associative array with disaggregate element values
     */
    public function userform_get_prefill($fromdb) {
        $prefill = [];

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
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
            $submission = $DB->get_record('surveypro_submission', ['id' => $submissionid], '*', MUST_EXIST);
            $user = $DB->get_record('user', ['id' => $submission->userid]);
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
                        $names = [];
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
        $elementnames = [$this->itemname];

        return $elementnames;
    }
}
