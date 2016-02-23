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
require_once($CFG->dirroot.'/mod/surveypro/field/age/lib.php');

class mod_surveypro_field_age extends mod_surveypro_itembase {

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
     * $defaultoption
     */
    protected $defaultoption;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    protected $defaultvalue;
    protected $defaultvalue_year;
    protected $defaultvalue_month;

    /**
     * $lowerbound = the minimum allowed age
     */
    protected $lowerbound;
    protected $lowerbound_year;
    protected $lowerbound_month;

    /**
     * $upperbound = the maximum allowed age
     */
    protected $upperbound;
    protected $upperbound_year;
    protected $upperbound_month;

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
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // List of properties set to static values..
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'age';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

        // Override properties depending from $surveypro settings..
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        // Empty list.

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent. To include $item->parentcontent (as decoded by the parent item) too.
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid, $evaluateparentcontent)))
        parent::item_load($itemid, $evaluateparentcontent);

        // Multilang load support for builtin surveypro.
        // Whether executed, the 'content' field is ALWAYS handled.
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
     * item_add_mandatory_plugin_fields
     * Copy mandatory fields to $record.
     *
     * @param stdClass $record
     * @return nothing
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record['content'] = 'Age [yy/mm]';
        $record['contentformat'] = 1;
        $record['position'] = 0;
        $record['required'] = 0;
        $record['hideinstructions'] = 0;
        $record['variable'] = 'age_001';
        $record['indent'] = 0;
        $record['defaultoption'] = SURVEYPRO_INVITEDEFAULT;
        $record['lowerbound'] = -2148552000;
        $record['upperbound'] = 1193918400;
    }

    /**
     * item_force_coherence
     * verify the validity of contents of the record
     * for instance: age not greater than maximumage
     *
     * @param stdClass $record
     * @return nothing
     */
    public function item_force_coherence($record) {
        if (isset($record['defaultvalue'])) {
            $maxyear = get_config('surveyprofield_age', 'maximumage');
            $maximumage = $this->item_age_to_unix_time($maxyear, 11);
            if ($record['defaultvalue'] > $maximumage) {
                $record['defaultvalue'] = $maximumage;
            }
        }
    }

    /**
     * item_split_unix_time
     *
     * @param $time
     * @param $applyusersettings
     * @return
     */
    public function item_split_unix_time($time, $applyusersettings=false) {
        $getdate = parent::item_split_unix_time($time, $applyusersettings);

        $getdate['year'] -= SURVEYPROFIELD_AGE_YEAROFFSET;
        if ($getdate['mon'] == 12) {
            $getdate['year']++;
            $getdate['mon'] = 0;
        }

        return $getdate;
    }

    /**
     * item_age_to_unix_time
     *
     * @param $year
     * @param $month
     * @return
     */
    public function item_age_to_unix_time($year, $month) {
        $year += SURVEYPROFIELD_AGE_YEAROFFSET;
        return (gmmktime(12, 0, 0, $month, 1, $year)); // This is GMT
    }

    /**
     * item_custom_fields_to_form
     * translates the age class property $fieldlist in $field.'_year' and $field.'_month'
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (!empty($this->{$field})) {
                $agearray = $this->item_split_unix_time($this->{$field});
                $this->{$field.'_year'} = $agearray['year'];
                $this->{$field.'_month'} = $agearray['mon'];
            }
        }
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the age custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_year'}) && isset($record->{$field.'_month'})) {
                $record->{$field} = $this->item_age_to_unix_time($record->{$field.'_year'}, $record->{$field.'_month'});
                unset($record->{$field.'_year'});
                unset($record->{$field.'_month'});
            } else {
                $record->{$field} = null;
            }
        }

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        $checkboxes = array('required', 'hideinstructions');
        foreach ($checkboxes as $checkbox) {
            $record->{$checkbox} = (isset($record->{$checkbox})) ? 1 : 0;
        }

        // 4. Other.
    }

    /**
     * item_composite_fields
     * get the list of composite fields
     *
     * @param none
     * @return
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /**
     * item_age_to_text
     * starting from an agearray returns the corresponding age in text format
     *
     * @param $agearray
     * @return
     */
    public function item_age_to_text($agearray) {
        $stryears = get_string('years');
        $strmonths = get_string('months', 'surveyprofield_age');

        $return = '';
        if (!empty($agearray['year'])) {
            $return .= $agearray['year'].' '.$stryears;
            if (!empty($agearray['mon'])) {
                $return .= ' '.get_string('and', 'surveyprofield_age').' '.$agearray['mon'].' '.$strmonths;
            }
        } else {
            $return .= $agearray['mon'].' '.$strmonths;
        }

        return $return;
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
    <xs:element name="surveyprofield_age">
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

                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="unixtime" name="defaultvalue" minOccurs="0"/>
                <xs:element type="unixtime" name="lowerbound"/>
                <xs:element type="unixtime" name="upperbound"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
    <xs:simpleType name="unixtime">
        <xs:restriction base="xs:string">
            <xs:pattern value="-?\d{0,10}"/>
        </xs:restriction>
    </xs:simpleType>
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
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_age_'.$this->sortindex;

        // Begin of: element values.
        $years = array();
        $months = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $years[SURVEYPRO_INVITEVALUE] = get_string('inviteyear', 'surveyprofield_age');
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_age');
            }
        } else {
            $years[SURVEYPRO_IGNOREMEVALUE] = '';
            $months[SURVEYPRO_IGNOREMEVALUE] = '';
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));
        $months += array_combine(range(0, 11), range(0, 11));
        // End of: element values

        // Begin of: mform element.
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_year', '', $years, array('class' => 'indent-'.$this->indent, 'id' => $idprefix.'_year'));
        if ($readonly) {
            $elementgroup[] = $mform->createElement('mod_surveypro_static', 'yearlabel_'.$this->itemid, null, get_string('years'), array('class' => 'inline'));
        }
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_month', '', $months, array('id' => $idprefix.'_month'));
        if ($readonly) {
            $elementgroup[] = $mform->createElement('mod_surveypro_static', 'monthlabel_'.$this->itemid, null, get_string('months', 'mod_surveypro'), array('class' => 'inline'));
        }

        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

            if (!$searchform) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // -> I do not want JS form validation if the page is submitted through the "previous" button.
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $attributes = array('id' => $idprefix.'_noanswer');
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'mod_surveypro'), $attributes);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }
        // End of: mform element.

        // Begin of: default section.
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $mform->setDefault($this->itemname.'_year', SURVEYPRO_INVITEVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEYPRO_INVITEVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEYPRO_CUSTOMDEFAULT:
                        $agearray = $this->item_split_unix_time($this->defaultvalue);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $agearray = $this->item_split_unix_time($this->lowerbound);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                }
                $mform->setDefault($this->itemname.'_year', $agearray['year']);
                $mform->setDefault($this->itemname.'_month', $agearray['mon']);
            }
        } else {
            $mform->setDefault($this->itemname.'_year', SURVEYPRO_IGNOREMEVALUE);
            $mform->setDefault($this->itemname.'_month', SURVEYPRO_IGNOREMEVALUE);
            if (!$this->required) {
                $mform->setDefault($this->itemname.'_noanswer', '0');
            }
        }
        // End of: default section
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
        // This plugin displays as dropdown menu. It will never return empty values.
        // Because of this, if ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // Nothing to validate.
        }

        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $errorkey = $this->itemname.'_group';

        // Begin of: verify the content of each drop down menu.
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_year'] != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_INVITEVALUE);
        } else {
            // Both drop down menues are allowed to be == SURVEYPRO_IGNOREMEVALUE.
            // But not only 1.
            $testpassed = true;
            if ($data[$this->itemname.'_year'] == SURVEYPRO_IGNOREMEVALUE) {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] == SURVEYPRO_IGNOREMEVALUE);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_IGNOREMEVALUE);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_agenotsetrequired', 'surveyprofield_age');
            } else {
                $a = get_string('noanswer', 'mod_surveypro');
                $errors[$errorkey] = get_string('uerr_agenotset', 'surveyprofield_age', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $userinput = $this->item_age_to_unix_time($data[$this->itemname.'_year'], $data[$this->itemname.'_month']);

        if ($haslowerbound && $hasupperbound) {
            // Internal range.
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_age');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_age');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_age');
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
        $maximumage = get_config('surveyprofield_age', 'maximumage');

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $a = '';
        $lowerbound = $this->item_split_unix_time($this->lowerbound);
        $upperbound = $this->item_split_unix_time($this->upperbound);

        $fillinginstruction = '';
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = $this->item_age_to_text($lowerbound);
            $a->upperbound = $this->item_age_to_text($upperbound);

            $fillinginstruction .= get_string('restriction_lowerupper', 'surveyprofield_age', $a);
        } else {
            if ($haslowerbound) {
                $a = $this->item_age_to_text($lowerbound);
                $fillinginstruction .= get_string('restriction_lower', 'surveyprofield_age', $a);
            }

            if ($hasupperbound) {
                $a = $this->item_age_to_text($upperbound);
                $fillinginstruction .= get_string('restriction_upper', 'surveyprofield_age', $a);
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
     * @param $olduseranswer
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (isset($answer['noanswer'])) { // This is correct for input and search form both.
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                if (($answer['year'] == SURVEYPRO_INVITEVALUE) || ($answer['month'] == SURVEYPRO_INVITEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_age_to_unix_time($answer['year'], $answer['month']);
                }
            } else {
                if (($answer['year'] == SURVEYPRO_IGNOREMEVALUE) || ($answer['month'] == SURVEYPRO_IGNOREMEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_age_to_unix_time($answer['year'], $answer['month']);
                }
            }
        }
    }

    /**
     * this method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time
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
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
            } else {
                $datearray = $this->item_split_unix_time($fromdb->content);
                $prefill[$this->itemname.'_month'] = $datearray['mon'];
                $prefill[$this->itemname.'_year'] = $datearray['year'];
            }
        }

        return $prefill;
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     *
     * @param $answers
     * @param $format
     * @return
     */
    public function userform_db_to_export($answer, $format='') {
        $content = $answer->content;
        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        $agearray = $this->item_split_unix_time($content);
        return $this->item_age_to_text($agearray);
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname.'_group');

        return $elementnames;
    }
}
