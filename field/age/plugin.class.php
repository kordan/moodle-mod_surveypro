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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/itembase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/field/age/lib.php');

class mod_surveypro_field_age extends mod_surveypro_itembase {

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
     * $required = boolean. O == optional item; 1 == mandatory item
     */
    public $required = 0;

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
     * $defaultoption
     */
    public $defaultoption = SURVEYPRO_INVITATIONDEFAULT;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = -2635200;

    /**
     * $defaultvalue_year
     */
    public $defaultvalue_year = null;

    /**
     * $defaultvalue_month
     */
    public $defaultvalue_month = null;

    /**
     * $lowerbound = the minimum allowed age
     */
    public $lowerbound = -2635200;

    /**
     * $lowerbound_year
     */
    public $lowerbound_year = null;

    /**
     * $lowerbound_month
     */
    public $lowerbound_month = null;

    /**
     * $upperbound = the maximum allowed age
     */
    public $upperbound = 0;

    /**
     * $flag = features describing the object
     */
    public $flag;

    /**
     * $canbeparent
     */
    public static $canbeparent = false;

    // -----------------------------

    /**
     * Class constructor
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     *
     * @param int $itemid. Optional surveypro_item ID
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'age';

        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

        $this->flag = new stdClass();
        $this->flag->issearchable = true;
        $this->flag->usescontenteditor = true;
        $this->flag->editorslist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA);
        $this->flag->savepositiontodb = false;

        // list of fields I do not want to have in the item definition form
        // EMPTY LIST

        if (!empty($itemid)) {
            $this->item_load($itemid, $evaluateparentcontent);
        }
    }

    /**
     * item_load
     *
     * @param $itemid
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        // Do parent item loading stuff here (surveypro_itembase::item_load($itemid)))
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

        // begin of: plugin specific settings (eventally overriding general ones)
        // set custom fields value as defined for this question plugin
        $this->item_custom_fields_to_db($record);

        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (surveypro_itembase::save($record)))
        return parent::item_save($record);
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
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            $agearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_year'} = $agearray['year'];
            $this->{$field.'_month'} = $agearray['mon'];
        }

        // 3. special management for defaultvalue
        if (!isset($this->defaultvalue)) {
            $this->defaultoption = SURVEYPRO_NOANSWERDEFAULT;
        } else {
            if ($this->defaultvalue == SURVEYPRO_INVITATIONDBVALUE) {
                $this->defaultoption = SURVEYPRO_INVITATIONDEFAULT;
            } else {
                $this->defaultoption = SURVEYPRO_CUSTOMDEFAULT;
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
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
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

        // 3. special management for defaultvalue
        switch ($record->defaultoption) {
            case SURVEYPRO_CUSTOMDEFAULT:
                // $record->defaultvalue has already been set
                break;
            case SURVEYPRO_NOANSWERDEFAULT:
                $record->defaultvalue = null;
                break;
            case SURVEYPRO_INVITATIONDEFAULT:
                $record->defaultvalue = SURVEYPRO_INVITATIONDBVALUE;
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $record->defaultoption = '.$record->defaultoption, DEBUG_DEVELOPER);
        }
        unset($record->defaultvalue_year);
        unset($record->defaultvalue_month);
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

        // element values
        $years = array();
        $months = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                $years[SURVEYPRO_INVITATIONVALUE] = get_string('invitationyear', 'surveyprofield_age');
                $months[SURVEYPRO_INVITATIONVALUE] = get_string('invitationmonth', 'surveyprofield_age');
            }
        } else {
            $years[SURVEYPRO_IGNOREME] = '';
            $months[SURVEYPRO_IGNOREME] = '';
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));
        $months += array_combine(range(0, 11), range(0, 11));
        // End of: element values

        // mform element
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_year', '', $years, array('class' => 'indent-'.$this->indent));
        if ($readonly) {
            $elementgroup[] = $mform->createElement('static', 'yearlabel_'.$this->itemid, null, get_string('years'));
        }
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_month', '', $months);
        if ($readonly) {
            $elementgroup[] = $mform->createElement('static', 'monthlabel_'.$this->itemid, null, get_string('months', 'surveypro'));
        }

        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

            if (!$searchform) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_noanswer', '', get_string('noanswer', 'surveypro'));
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }
        // End of: mform element

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname.'_year', SURVEYPRO_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEYPRO_INVITATIONVALUE);
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
            $mform->setDefault($this->itemname.'_year', SURVEYPRO_IGNOREME); // empty label
            $mform->setDefault($this->itemname.'_month', SURVEYPRO_IGNOREME); // empty label
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
        // this plugin displays as dropdown menu. It will never return empty values.
        // if ($this->required) { if (empty($data[$this->itemname])) { is useless

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // nothing to validate
        }

        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $errorkey = $this->itemname.'_group';

        // verify the content of each drop down menu
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_year'] != SURVEYPRO_INVITATIONVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_INVITATIONVALUE);
        } else {
            // both drop down menues are allowed to be == SURVEYPRO_IGNOREME
            // but not only 1
            $testpassed = true;
            if ($data[$this->itemname.'_year'] == SURVEYPRO_IGNOREME) {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] == SURVEYPRO_IGNOREME);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_IGNOREME);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_agenotsetrequired', 'surveyprofield_age');
            } else {
                $a = get_string('noanswer', 'surveypro');
                $errors[$errorkey] = get_string('uerr_agenotset', 'surveyprofield_age', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // stop here your investigation. I don't further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $userinput = $this->item_age_to_unix_time($data[$this->itemname.'_year'], $data[$this->itemname.'_month']);

        if ($haslowerbound && $hasupperbound) {
            // internal range
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
     * @param $olduserdata
     * @param $searchform
     * @return
     */
    public function userform_save_preprocessing($answer, $olduserdata, $searchform) {
        if (isset($answer['noanswer'])) { // this is correct for input and search form both
            $olduserdata->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                $olduserdata->content = $this->item_age_to_unix_time($answer['year'], $answer['month']);
            } else {
                if ($answer['year'] == SURVEYPRO_IGNOREME) {
                    $olduserdata->content = null;
                } else {
                    $olduserdata->content = $this->item_age_to_unix_time($answer['year'], $answer['month']);
                }
            }
        }
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
        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'surveypro');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'surveypro');
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
