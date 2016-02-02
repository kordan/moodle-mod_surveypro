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
require_once($CFG->dirroot.'/mod/surveypro/field/shortdate/lib.php');

class mod_surveypro_field_shortdate extends mod_surveypro_itembase {

    /**
     * $surveypro
     */
    public $surveypro = null;

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

    /**
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    public $defaultoption = SURVEYPRO_INVITEDEFAULT;

    /**
     * $downloadformat = the format of the content once downloaded
     */
    public $downloadformat = null;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = 0;
    public $defaultvalue_month = null;
    public $defaultvalue_year = null;

    /**
     * $lowerbound = the minimum allowed short date
     */
    public $lowerbound = 0;
    public $lowerbound_month = null;
    public $lowerbound_year = null;

    /**
     * $upperbound = the maximum allowed short date
     */
    public $upperbound = 0;
    public $upperbound_month = null;
    public $upperbound_year = null;

    /**
     * static canbeparent
     */
    public static $canbeparent = false;

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
        global $DB;

        parent::__construct($cm, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'shortdate';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // It is already true from parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        // Override properties depending from $surveypro settings.
        $this->surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
        $this->lowerbound = $this->item_shortdate_to_unix_time(1, $this->surveypro->startyear);
        $this->upperbound = $this->item_shortdate_to_unix_time(12, $this->surveypro->stopyear);
        $this->defaultvalue = $this->lowerbound;

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
     * @param bool $evaluateparentcontent: include among item elements the 'parentcontent' too
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
     * item_shortdate_to_unix_time
     *
     * @param $month
     * @param $year
     * @return
     */
    public function item_shortdate_to_unix_time($month, $year) {
        return (gmmktime(12, 0, 0, $month, 1, $year)); // This is GMT
    }

    /**
     * item_custom_fields_to_form
     * translates the shortdate class property $fieldlist in $field.'_year' and $field.'_month'
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                switch ($field) {
                    case 'defaultvalue':
                        continue 2; // It may be; continues switch and foreach too.
                    case 'lowerbound':
                        $this->{$field} = $this->item_shortdate_to_unix_time(1, $this->surveypro->startyear);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_shortdate_to_unix_time(1, $this->surveypro->stopyear);
                        break;
                }
            }
            if (!empty($this->{$field})) {
                $shortdatearray = $this->item_split_unix_time($this->{$field});
                $this->{$field.'_month'} = $shortdatearray['mon'];
                $this->{$field.'_year'} = $shortdatearray['year'];
            }
        }
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the shortdate custom item
     *
     * @param $record
     * @return
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_month'}) && isset($record->{$field.'_year'})) {
                $record->{$field} = $this->item_shortdate_to_unix_time($record->{$field.'_month'}, $record->{$field.'_year'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_year'});
            } else {
                $record->{$field} = null;
            }
        }

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Nothing to do: no checkboxes in this plugin item form.

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
     * item_get_downloadformats
     *
     * @param none
     * @return
     */
    public function item_get_downloadformats() {
        $option = array();
        $timenow = time();

        for ($i = 1; $i < 7; $i++) {
            $strname = 'strftime'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $option[$strname] = userdate($timenow, get_string($strname, 'surveyprofield_shortdate'));
        }
        $option['unixtime'] = get_string('unixtime', 'mod_surveypro');
        // June 2013
        // June '13
        // Jun 2013
        // Jun '13
        // 06/2013
        // 06/13
        // Unix time.

        return $option;
    }

    /**
     * item_get_friendlyformat
     *
     * @param none
     * @return
     */
    public function item_get_friendlyformat() {
        return 'strftime01';
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
    <xs:element name="surveyprofield_shortdate">
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

        $idprefix = 'id_surveypro_field_shortdate_'.$this->sortindex;

        // Begin of: element values.
        $months = array();
        $years = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_shortdate');
                $years[SURVEYPRO_INVITEVALUE] = get_string('inviteyear', 'surveyprofield_shortdate');
            }
        } else {
            $months[SURVEYPRO_IGNOREMEVALUE] = '';
            $years[SURVEYPRO_IGNOREMEVALUE] = '';
        }
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
        }
        $years += array_combine(range($this->lowerbound_year, $this->upperbound_year), range($this->lowerbound_year, $this->upperbound_year));
        // End of: element values

        // Begin of: mform element.
        $elementgroup = array();
        $attributes = array('id' => $idprefix.'_month', 'class' => 'indent-'.$this->indent);
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_month', '', $months, $attributes);
        $attributes = array('id' => $idprefix.'_year');
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_year', '', $years, $attributes);

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

        // Default section.
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $mform->setDefault($this->itemname.'_month', SURVEYPRO_INVITEVALUE);
                $mform->setDefault($this->itemname.'_year', SURVEYPRO_INVITEVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEYPRO_CUSTOMDEFAULT:
                        $shortdatearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEYPRO_TIMENOWDEFAULT:
                        $shortdatearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $shortdatearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEYPRO_LIKELASTDEFAULT:
                        // Look for the last submission I made.
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('surveypro_answer', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $shortdatearray = $this->item_split_unix_time($time, false);
                        } else { // As in standard default.
                            $shortdatearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                }
                $mform->setDefault($this->itemname.'_month', $shortdatearray['mon']);
                $mform->setDefault($this->itemname.'_year', $shortdatearray['year']);
            }
        } else {
            $mform->setDefault($this->itemname.'_month', SURVEYPRO_IGNOREMEVALUE);
            $mform->setDefault($this->itemname.'_year', SURVEYPRO_IGNOREMEVALUE);
            if (!$this->required) {
                $mform->setDefault($this->itemname.'_noanswer', '0');
            }
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
        // This plugin displays as dropdown menu. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // Nothing to validate.
        }

        $errorkey = $this->itemname.'_group';

        // Begin of: verify the content of each drop down menu.
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_year'] != SURVEYPRO_INVITEVALUE);
        } else {
            // Both drop down menues are allowed to be == SURVEYPRO_IGNOREMEVALUE.
            // But not only 1.
            $testpassed = true;
            if ($data[$this->itemname.'_month'] == SURVEYPRO_IGNOREMEVALUE) {
                $testpassed = $testpassed && ($data[$this->itemname.'_year'] == SURVEYPRO_IGNOREMEVALUE);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_year'] != SURVEYPRO_IGNOREMEVALUE);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_shortdatenotsetrequired', 'surveyprofield_shortdate');
            } else {
                $a = get_string('noanswer', 'mod_surveypro');
                $errors[$errorkey] = get_string('uerr_shortdatenotset', 'surveyprofield_shortdate', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_shortdate_to_unix_time(1, $surveypro->startyear));
        $hasupperbound = ($this->upperbound != $this->item_shortdate_to_unix_time(12, $surveypro->stopyear));

        $userinput = $this->item_shortdate_to_unix_time($data[$this->itemname.'_month'], $data[$this->itemname.'_year']);

        if ($haslowerbound && $hasupperbound) {
            // Internal range.
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_shortdate');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_shortdate');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_shortdate');
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
        $haslowerbound = ($this->lowerbound != $this->item_shortdate_to_unix_time(1, $this->surveypro->startyear));
        $hasupperbound = ($this->upperbound != $this->item_shortdate_to_unix_time(12, $this->surveypro->stopyear));

        $format = get_string('strftimemonthyear', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);

            // Internal range.
            $fillinginstruction = get_string('restriction_lowerupper', 'surveyprofield_shortdate', $a);
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format, 0);
                $fillinginstruction = get_string('restriction_lower', 'surveyprofield_shortdate', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format, 0);
                $fillinginstruction = get_string('restriction_upper', 'surveyprofield_shortdate', $a);
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
                $olduseranswer->content = $this->item_shortdate_to_unix_time($answer['month'], $answer['year']);
            } else {
                if ($answer['month'] == SURVEYPRO_IGNOREMEVALUE) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_shortdate_to_unix_time($answer['month'], $answer['year']);
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
                $shortdatearray = $this->item_split_unix_time($fromdb->content);
                $prefill[$this->itemname.'_month'] = $shortdatearray['mon'];
                $prefill[$this->itemname.'_year'] = $shortdatearray['year'];
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
        // Content.
        $content = $answer->content;
        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        // Format.
        if ($format == SURVEYPRO_FIRENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // Output.
        if ($format == 'unixtime') {
            return $content;
        } else {
            return userdate($content, get_string($format, 'surveyprofield_shortdate'), 0);
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
}
