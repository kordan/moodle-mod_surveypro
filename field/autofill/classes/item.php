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

    // Itembase properties.

    /**
     * @var bool $hiddenfield = is the static text visible in the mform?
     */
    protected $hiddenfield;

    /**
     * @var string Element #1 for $content
     */
    protected $element01;

    /**
     * @var string Element #2 for $content
     */
    protected $element02;

    /**
     * @var string Element #3 for $content
     */
    protected $element03;

    /**
     * @var string Element #4 for $content
     */
    protected $element04;

    /**
     * @var string Element #5 for $content
     */
    protected $element05;

    // Service variables.

    /**
     * @var string Select of the element #1
     */
    protected $element01select;

    /**
     * @var string Text of the element #1
     */
    protected $element01text;

    /**
     * @var string Select of the element #2
     */
    protected $element02select;

    /**
     * @var string Text of the element #2
     */
    protected $element02text;

    /**
     * @var string Select of the element #3
     */
    protected $element03select;

    /**
     * @var string Text of the element #3
     */
    protected $element03text;

    /**
     * @var string Select of the element #4
     */
    protected $element04select;

    /**
     * @var string Text of the element #4
     */
    protected $element04text;

    /**
     * @var string Select of the element #5
     */
    protected $element05select;

    /**
     * @var string Text of the element #5
     */
    protected $element05text;

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
        $this->plugin = 'autofill';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
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
        $record->hiddenfield = 0;
        $record->element01 = 'userid';
        // $record->element02;
        // $record->element03;
        // $record->element04;
        // $record->element05;
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
    public function add_plugin_properties_to_record($record) {
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
    public static function has_mandatoryattribute() {
        return false;
    }

    // MARK set.

    /**
     * Set hiddenfield.
     *
     * @param string $hiddenfield
     * @return void
     */
    public function set_hiddenfield($hiddenfield) {
        $this->hiddenfield = $hiddenfield;
    }

    /**
     * Set element01.
     *
     * @param string $element01
     * @return void
     */
    public function set_element01($element01) {
        $this->element01 = $element01;
    }

    /**
     * Set element02.
     *
     * @param string $element02
     * @return void
     */
    public function set_element02($element02) {
        $this->element02 = $element02;
    }

    /**
     * Set element03.
     *
     * @param string $element03
     * @return void
     */
    public function set_element03($element03) {
        $this->element03 = $element03;
    }

    /**
     * Set element04.
     *
     * @param string $element04
     * @return void
     */
    public function set_element04($element04) {
        $this->element04 = $element04;
    }

    /**
     * Set element05.
     *
     * @param string $element05
     * @return void
     */
    public function set_element05($element05) {
        $this->element05 = $element05;
    }

    /**
     * Set element01select.
     *
     * @param string $element01select
     * @return void
     */
    public function set_element01select($element01select) {
        $this->element01select = $element01select;
    }

    /**
     * Set element01text.
     *
     * @param string $element01text
     * @return void
     */
    public function set_element01text($element01text) {
        $this->element01text = $element01text;
    }

    /**
     * Set element02select.
     *
     * @param string $element02select
     * @return void
     */
    public function set_element02select($element02select) {
        $this->element02select = $element02select;
    }

    /**
     * Set element02text.
     *
     * @param string $element02text
     * @return void
     */
    public function set_element02text($element02text) {
        $this->element02text = $element02text;
    }

    /**
     * Set element03select.
     *
     * @param string $element03select
     * @return void
     */
    public function set_element03select($element03select) {
        $this->element03select = $element03select;
    }

    /**
     * Set element03text.
     *
     * @param string $element03text
     * @return void
     */
    public function set_element03text($element03text) {
        $this->element03text = $element03text;
    }

    /**
     * Set element04select.
     *
     * @param string $element04select
     * @return void
     */
    public function set_element04select($element04select) {
        $this->element04select = $element04select;
    }

    /**
     * Set element04text.
     *
     * @param string $element04text
     * @return void
     */
    public function set_element04text($element04text) {
        $this->element04text = $element04text;
    }

    /**
     * Set element05select.
     *
     * @param string $element05select
     * @return void
     */
    public function set_element05select($element05select) {
        $this->element05select = $element05select;
    }

    /**
     * Set element05text.
     *
     * @param string $element05text
     * @return void
     */
    public function set_element05text($element05text) {
        $this->element05text = $element05text;
    }

    // MARK get.

    /**
     * Get hiddenfield.
     *
     * @return $this->hiddenfield
     */
    public function get_hiddenfield() {
        return $this->hiddenfield;
    }

    /**
     * Get element01.
     *
     * @return $this->element01
     */
    public function get_element01() {
        return $this->element01;
    }

    /**
     * Get element02.
     *
     * @return $this->element02
     */
    public function get_element02() {
        return $this->element02;
    }

    /**
     * Get element03.
     *
     * @return $this->element03
     */
    public function get_element03() {
        return $this->element03;
    }

    /**
     * Get element04.
     *
     * @return $this->element04
     */
    public function get_element04() {
        return $this->element04;
    }

    /**
     * Get element05.
     *
     * @return $this->element05
     */
    public function get_element05() {
        return $this->element05;
    }

    /**
     * Get element01select.
     *
     * @return $this->element01select
     */
    public function get_element01select() {
        return $this->element01select;
    }

    /**
     * Get element01text.
     *
     * @return $this->element01text
     */
    public function get_element01text() {
        return $this->element01text;
    }

    /**
     * Get element02select.
     *
     * @return $this->element02select
     */
    public function get_element02select() {
        return $this->element02select;
    }

    /**
     * Get element02text.
     *
     * @return $this->element02text
     */
    public function get_element02text() {
        return $this->element02text;
    }

    /**
     * Get element03select.
     *
     * @return $this->element03select
     */
    public function get_element03select() {
        return $this->element03select;
    }

    /**
     * Get element03text.
     *
     * @return $this->element03text
     */
    public function get_element03text() {
        return $this->element03text;
    }

    /**
     * Get element04select.
     *
     * @return $this->element04select
     */
    public function get_element04select() {
        return $this->element04select;
    }

    /**
     * Get element04text.
     *
     * @return $this->element04text
     */
    public function get_element04text() {
        return $this->element04text;
    }

    /**
     * Get element05select.
     *
     * @return $this->element05select
     */
    public function get_element05select() {
        return $this->element05select;
    }

    /**
     * Get element05text.
     *
     * @return $this->element05text
     */
    public function get_element05text() {
        return $this->element05text;
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = [
            'hiddenfield', 'element01', 'element02', 'element03', 'element04', 'element05',
            'element01select', 'element02select', 'element03select', 'element04select', 'element05select',
            'element01text', 'element02text', 'element03text', 'element04text', 'element05text',
        ];
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
        $fieldlist['surveyprofield_autofill'] = [];

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
                <xs:element name="hiddenfield" type="xs:int" minOccurs="0"/>
                <xs:element name="element01" type="xs:string"/>
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

        $class = ['class' => 'indent-'.$this->indent];
        $baseid = 'id_field_autofill_'.$this->sortindex;
        $attributes = [];
        $elementgroup = [];
        $basename = $this->itemname;

        if (!$searchform) {
            // I can not say: "I can write the content as if the record is new because if the record is not new,
            // this value will be overwritten later when defaults will be applied to the form.".
            // This is a label! Defults will not be applied.
            // So, I have to ALWAYS get them now and include them now into the item.

            // Is this a new submission?
            $submissionid = $mform->getElementValue('submissionid');
            if ($submissionid) { // I am working on an already saved submission.
                $wheresql = 'submissionid = :submissionid AND itemid = :itemid';
                $whereparams = ['submissionid' => $submissionid, 'itemid' => $this->itemid];
                $answer = false;
                $answer = $DB->get_record('surveypro_answer', $whereparams);
                if ($answer) {
                    $value = $answer->content;
                } else {
                    // If the answer does not exist...
                    // it may be I am saving a new content BUT the autofill item is in the second page of the surveypro.
                    $value = $this->userform_get_content(0);
                }
            } else { // I am editing a new submission.
                $value = $this->userform_get_content(0);
            }

            $mform->addElement('hidden', $basename, $value);
            $mform->setType($basename, PARAM_RAW);
            $mform->setDefault($basename, $value);

            if (!$this->hiddenfield) {
                $attributes = ['id' => $baseid];
                $elementgroup[] = $mform->createElement('static', $basename.'_static', '', $value, $attributes);
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, '', false, $class);
            }
        } else {
            $basename = $this->itemname;
            $attributes['id'] = $baseid;

            $elementgroup[] = $mform->createElement('text', $basename, '', $attributes);
            $mform->setType($basename, PARAM_RAW);

            $attributes['id'] = $baseid.'_ignoreme';
            $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

            $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
            $mform->disabledIf($basename.'_group', $basename.'_ignoreme', 'checked');
            $mform->setDefault($basename.'_ignoreme', '1');
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
        return $errors;
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
        return [];
    }
}
