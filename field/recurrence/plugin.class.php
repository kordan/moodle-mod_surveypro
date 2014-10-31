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
require_once($CFG->dirroot.'/mod/surveypro/field/recurrence/lib.php');

class mod_surveypro_field_recurrence extends mod_surveypro_itembase {

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
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    public $defaultoption = SURVEYPRO_INVITATIONDEFAULT;

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;
    public $defaultvalue_month = null;
    public $defaultvalue_day = null;

    /**
     * $lowerbound = the minimum allowed recurrence
     */
    public $lowerbound = 0;
    public $lowerbound_month = null;
    public $lowerbound_day = null;

    /**
     * $upperbound = the maximum allowed recurrence
     */
    public $upperbound = 0;
    public $upperbound_month = null;
    public $upperbound_day = null;

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
     * @param bool $evaluateparentcontent. Is the parent item evaluation needed?
     */
    public function __construct($cm, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // list of constant element attributes
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'recurrence';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // it is already true from parent class
        $this->savepositiontodb = false;

        // other element specific properties
        $this->lowerbound = $this->item_recurrence_to_unix_time(1, 1);
        $this->upperbound = $this->item_recurrence_to_unix_time(12, 31);
        $this->defaultvalue = $this->lowerbound;

        // override properties depending from $surveypro settings
        // nothing

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

        // Do parent item saving stuff here (surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_recurrence_to_unix_time
     *
     * @param $month
     * @param $day
     * @return
     */
    public function item_recurrence_to_unix_time($month, $day) {
        return (gmmktime(12, 0, 0, $month, $day, SURVEYPROFIELD_RECURRENCE_YEAROFFSET)); // This is GMT
    }

    /**
     * item_custom_fields_to_form
     * translates the recurrence class property $fieldlist in $field.'_month' and $field.'_day'
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        global $surveypro;

        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                switch ($field) {
                    case 'defaultvalue':
                        continue 2; // it may be; continues switch and foreach too
                    case 'lowerbound':
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                }
            }
            $recurrencearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'_month'} = $recurrencearray['mon'];
            $this->{$field.'_day'} = $recurrencearray['mday'];
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the recurrence custom item
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
            if (isset($record->{$field.'_month'}) && isset($record->{$field.'_day'})) {
                $record->{$field} = $this->item_recurrence_to_unix_time($record->{$field.'_month'}, $record->{$field.'_day'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_day'});
            } else {
                $record->{$field} = null;
            }
        }

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
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
     * item_get_downloadformats
     *
     * @param none
     * @return
     */
    public function item_get_downloadformats() {
        $option = array();
        $timenow = time();

        $option['strftime1'] = userdate($timenow, get_string('strftime1', 'surveyprofield_recurrence')); // 21 Giugno
        $option['strftime2'] = userdate($timenow, get_string('strftime2', 'surveyprofield_recurrence')); // 21 Giu
        $option['strftime3'] = userdate($timenow, get_string('strftime3', 'surveyprofield_recurrence')); // 21/06
        $option['unixtime'] = get_string('unixtime', 'surveypro');

        return $option;
    }

    /**
     * item_check_monthday
     *
     * @param $day
     * @param $month
     * @return
     */
    public function item_check_monthday($day, $month) {
        if ($month == 2) {
            return ($day <= 29);
        }

        $longmonths = array(1, 3, 5, 7, 8, 10, 12);
        if (in_array($month, $longmonths)) {
            return ($day <= 31);
        } else {
            return ($day <= 30);
        }
    }

    /**
     * item_get_friendlyformat
     *
     * @param none
     * @return
     */
    public function item_get_friendlyformat() {
        return 'strftime3';
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
    <xs:element name="surveyprofield_recurrence">
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
                <xs:element type="xs:string" name="downloadformat"/>
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
        global $DB, $USER;

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        // element values
        $days = array();
        $months = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                $days[SURVEYPRO_INVITATIONVALUE] = get_string('invitationday', 'surveyprofield_recurrence');
                $months[SURVEYPRO_INVITATIONVALUE] = get_string('invitationmonth', 'surveyprofield_recurrence');
            }
        } else {
            $days[SURVEYPRO_IGNOREME] = '';
            $months[SURVEYPRO_IGNOREME] = '';
        }
        $days += array_combine(range(1, 31), range(1, 31));
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // january, february, march...
        }
        // End of: element values

        // mform element
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_day', '', $days, array('class' => 'indent-'.$this->indent));
        $elementgroup[] = $mform->createElement('select', $this->itemname.'_month', '', $months);

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

        // default section
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITATIONDEFAULT) {
                $mform->setDefault($this->itemname.'_day', SURVEYPRO_INVITATIONVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEYPRO_INVITATIONVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEYPRO_CUSTOMDEFAULT:
                        $recurrencearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEYPRO_TIMENOWDEFAULT:
                        $recurrencearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $recurrencearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEYPRO_LIKELASTDEFAULT:
                        // look for the most recent submission I made
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('surveypro_answer', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $recurrencearray = $this->item_split_unix_time($time, false);
                        } else { // as in standard default
                            $recurrencearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->defaultoption = '.$this->defaultoption, DEBUG_DEVELOPER);
                }
                $mform->setDefault($this->itemname.'_day', $recurrencearray['mday']);
                $mform->setDefault($this->itemname.'_month', $recurrencearray['mon']);
            }
        } else {
            $mform->setDefault($this->itemname.'_day', SURVEYPRO_IGNOREME);
            $mform->setDefault($this->itemname.'_month', SURVEYPRO_IGNOREME);
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

        $errorkey = $this->itemname.'_group';

        // verify the content of each drop down menu
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_day'] != SURVEYPRO_INVITATIONVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_INVITATIONVALUE);
        } else {
            // both drop down menues are allowed to be == SURVEYPRO_IGNOREME
            // but not only 1
            $testpassed = true;
            if ($data[$this->itemname.'_day'] == SURVEYPRO_IGNOREME) {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] == SURVEYPRO_IGNOREME);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_IGNOREME);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_recurrencenotsetrequired', 'surveyprofield_recurrence');
            } else {
                $a = get_string('noanswer', 'surveypro');
                $errors[$errorkey] = get_string('uerr_recurrencenotset', 'surveyprofield_recurrence', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // stop here your investigation. I don't further validations.
            return;
        }

        if (!$this->item_check_monthday($data[$this->itemname.'_day'], $data[$this->itemname.'_month'])) {
            $errors[$errorkey] = get_string('incorrectrecurrence', 'surveyprofield_recurrence');
        }

        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $userinput = $this->item_recurrence_to_unix_time($data[$this->itemname.'_month'], $data[$this->itemname.'_day']);

        $format = get_string('strftimedateshort', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            // internal range
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_recurrence');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_recurrence');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_recurrence');
            }
        }
    }

    /**
     * userform_get_filling_instructions
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {
        global $surveypro;

        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $format = get_string('strftimedateshort', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);

            // internal range
            $fillinginstruction = get_string('restriction_lowerupper', 'surveyprofield_recurrence', $a);
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format, 0);
                $fillinginstruction = get_string('restriction_lower', 'surveyprofield_recurrence', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format, 0);
                $fillinginstruction = get_string('restriction_upper', 'surveyprofield_recurrence', $a);
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
                $olduserdata->content = $this->item_recurrence_to_unix_time($answer['month'], $answer['day']);
            } else {
                if ($answer['month'] == SURVEYPRO_IGNOREME) {
                    $olduserdata->content = null;
                } else {
                    $olduserdata->content = $this->item_recurrence_to_unix_time($answer['month'], $answer['day']);
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
                $recurrencearray = $this->item_split_unix_time($fromdb->content);
                $prefill[$this->itemname.'_day'] = $recurrencearray['mday'];
                $prefill[$this->itemname.'_month'] = $recurrencearray['mon'];
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
        // content
        $content = $answer->content;
        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'surveypro');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'surveypro');
        }

        // format
        if ($format == SURVEYPRO_FIRENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // output
        if ($format == 'unixtime') {
            return $content;
        } else {
            return userdate($content, get_string($format, 'surveyprofield_recurrence'), 0);
        }
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

    /**
     * get_canbeparent
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }
}
