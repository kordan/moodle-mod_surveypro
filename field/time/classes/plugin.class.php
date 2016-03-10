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
require_once($CFG->dirroot.'/mod/surveypro/field/time/lib.php');

class mod_surveypro_field_time extends mod_surveypro_itembase {

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
     * $hideinstructions = boolean. Exceptionally hide filling instructions
     */
    protected $hideinstructions;

    /**
     * $variable = the name of the field storing data in the db table
     */
    protected $variable;

    /**
     * $indent = the indent of the item in the form page
     */
    protected $indent;

    /**
     * $step = the step for minutes drop down menu
     */
    protected $step;

    /**
     * $defaultoption = the value of the field when the form is initially displayed.
     */
    protected $defaultoption;

    /**
     * $downloadformat = the format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    protected $defaultvalue;
    protected $defaultvalue_hour;
    protected $defaultvalue_minute;

    /**
     * $lowerbound = the minimum allowed time
     */
    protected $lowerbound;
    protected $lowerbound_hour;
    protected $lowerbound_minute;

    /**
     * $upperbound = the maximum allowed time
     */
    protected $upperbound;
    protected $upperbound_hour;
    protected $upperbound_minute;

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
    public function __construct($cm, $surveypro, $itemid=0, $evaluateparentcontent) {
        parent::__construct($cm, $surveypro, $itemid, $evaluateparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'time';
        // $this->editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA); // Already set in parent class.
        $this->savepositiontodb = false;

        // Other element specific properties.
        // No properties here.

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
     * @return void
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
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Time';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'time_001';
        $record->indent = 0;
        $record->step = 1;
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->defaultvalue = 0;
        $record->downloadformat = 'strftime1';
        $record->lowerbound = 0;
        $record->upperbound = 86340;
    }

    /**
     * item_time_to_unix_time
     *
     * @param $hour
     * @param $minute
     * @return void
     */
    public function item_time_to_unix_time($hour, $minute) {
        return (gmmktime($hour, $minute, 0, SURVEYPROFIELD_TIME_MONTHOFFSET, SURVEYPROFIELD_TIME_DAYOFFSET, SURVEYPROFIELD_TIME_YEAROFFSET)); // This is GMT
    }

    /**
     * item_custom_fields_to_form
     * sets record field to store the correct value to the form for customfields of the time item
     *
     * @param none
     * @return void
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
                        $this->{$field} = 0;
                        break;
                    case 'upperbound':
                        $this->{$field} = 86340;
                        break;
                }
            }
            if (!empty($this->{$field})) {
                $timearray = $this->item_split_unix_time($this->{$field});
                $this->{$field.'_hour'} = $timearray['hours'];
                $this->{$field.'_minute'} = $timearray['minutes'];
            }
        }
    }

    /**
     * item_custom_fields_to_db
     * sets record field to store the correct value to db for the time custom item
     *
     * @param $record
     * @return void
     */
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_hour'}) && isset($record->{$field.'_minute'})) {
                $record->{$field} = $this->item_time_to_unix_time($record->{$field.'_hour'}, $record->{$field.'_minute'});
                unset($record->{$field.'_hour'});
                unset($record->{$field.'_minute'});
            } else {
                $record->{$field} = null;
            }
        }

        // 2. Override few values.
        // Begin of: round defaultvalue according to step.
        $timearray = $this->item_split_unix_time($record->defaultvalue);
        $defaultvaluehour = $timearray['hours'];
        $defaultvalueminute = $timearray['minutes'];

        $stepscount = intval($defaultvalueminute / $record->step);
        $exceed = $defaultvalueminute % $record->step;
        if ($exceed < ($record->step / 2)) {
            $defaultvalueminute = $stepscount * $record->step;
        } else {
            $defaultvalueminute = (1 + $stepscount) * $record->step;
        }
        $record->defaultvalue = $this->item_time_to_unix_time($defaultvaluehour, $defaultvalueminute);
        // End of: round defaultvalue according to step.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in item_get_common_settings
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * item_composite_fields
     * get the list of composite fields
     *
     * @param none
     * @return void
     */
    public function item_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /**
     * item_get_downloadformats
     *
     * @param none
     * @return void
     */
    public function item_get_downloadformats() {
        $option = array();
        $timenow = time();

        $option['strftime1'] = userdate($timenow, get_string('strftime1', 'surveyprofield_time')); // 05:15
        $option['strftime2'] = userdate($timenow, get_string('strftime2', 'surveyprofield_time')); // 5:15 am
        $option['unixtime'] = get_string('unixtime', 'mod_surveypro');

        return $option;
    }

    /**
     * item_get_friendlyformat
     *
     * @param none
     * @return void
     */
    public function item_get_friendlyformat() {
        return 'strftime1';
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
    <xs:element name="surveyprofield_time">
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

                <xs:element type="xs:int" name="step"/>
                <xs:element type="xs:int" name="defaultoption"/>
                <xs:element type="xs:int" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:string" name="downloadformat"/>
                <xs:element type="xs:int" name="lowerbound"/>
                <xs:element type="xs:int" name="upperbound"/>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
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
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        global $DB, $USER;

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_time_'.$this->sortindex;

        // Begin of: element values.
        $hours = array();
        $minutes = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $hours[SURVEYPRO_INVITEVALUE] = get_string('invitehour', 'surveyprofield_time');
                $minutes[SURVEYPRO_INVITEVALUE] = get_string('inviteminute', 'surveyprofield_time');
            }
        } else {
            $hours[SURVEYPRO_IGNOREMEVALUE] = '';
            $minutes[SURVEYPRO_IGNOREMEVALUE] = '';
        }

        if ($this->lowerbound_hour <= $this->upperbound_hour) {
            for ($i = (int)$this->lowerbound_hour; $i <= $this->upperbound_hour; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
        } else {
            for ($i = (int)$this->lowerbound_hour; $i <= 24; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
            for ($i = (int)1; $i <= $this->upperbound_hour; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
        }
        for ($i = 0; $i <= 59; $i += $this->step) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        // End of: element values

        // Begin of: mform element.
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_hour', '', $hours, array('class' => 'indent-'.$this->indent, 'id' => $idprefix.'_hour'));
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $this->itemname.'_minute', '', $minutes, array('id' => $idprefix.'_minute'));

        $separator = array(':');
        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);

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
            $separator[] = ' ';
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, $separator, false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }
        // End of: mform element.

        // Default section.
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $mform->setDefault($this->itemname.'_hour', SURVEYPRO_INVITEVALUE);
                $mform->setDefault($this->itemname.'_minute', SURVEYPRO_INVITEVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEYPRO_CUSTOMDEFAULT:
                        $timearray = $this->item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEYPRO_TIMENOWDEFAULT:
                        $timearray = $this->item_split_unix_time(time(), true);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $timearray = $this->item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEYPRO_LIKELASTDEFAULT:
                        // Look for the last submission I made.
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, array('userid' => $USER->id), IGNORE_MISSING);
                        if ($time = $DB->get_field('surveypro_answer', 'content', array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid), IGNORE_MISSING)) {
                            $timearray = $this->item_split_unix_time($time, false);
                        } else { // As in standard default.
                            $timearray = $this->item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                }
                $mform->setDefault($this->itemname.'_hour', $timearray['hours']);
                $mform->setDefault($this->itemname.'_minute', $timearray['minutes']);
            }
        } else {
            $mform->setDefault($this->itemname.'_hour', SURVEYPRO_IGNOREMEVALUE);
            $mform->setDefault($this->itemname.'_minute', SURVEYPRO_IGNOREMEVALUE);
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
     * @return void
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
            $testpassed = $testpassed && ($data[$this->itemname.'_hour'] != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_minute'] != SURVEYPRO_INVITEVALUE);
        } else {
            // Both drop down menues are allowed to be == SURVEYPRO_IGNOREMEVALUE.
            // But not only 1.
            $testpassed = true;
            if ($data[$this->itemname.'_hour'] == SURVEYPRO_IGNOREMEVALUE) {
                $testpassed = $testpassed && ($data[$this->itemname.'_minute'] == SURVEYPRO_IGNOREMEVALUE);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_minute'] != SURVEYPRO_IGNOREMEVALUE);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_timenotsetrequired', 'surveyprofield_time');
            } else {
                $a = get_string('noanswer', 'mod_surveypro');
                $errors[$errorkey] = get_string('uerr_timenotset', 'surveyprofield_time', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $userinput = $this->item_time_to_unix_time($data[$this->itemname.'_hour'], $data[$this->itemname.'_minute']);

        if ($haslowerbound && $hasupperbound) {
            $format = get_string('strftimetime', 'langconfig');
            if ($this->lowerbound < $this->upperbound) {
                // Internal range.
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_time');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                if ( ($userinput > $this->lowerbound) && ($userinput < $this->upperbound) ) {
                    $format = $this->item_get_friendlyformat();
                    $a = new stdClass();
                    $a->lowerbound = userdate($this->lowerbound, get_string($format, 'surveyprofield_time'), 0);
                    $a->upperbound = userdate($this->upperbound, get_string($format, 'surveyprofield_time'), 0);
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyprofield_time', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_time');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_time');
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

        $haslowerbound = ($this->lowerbound != $this->item_time_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_time_to_unix_time(23, 59));

        $format = get_string('strftimetime', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);

            if ($this->lowerbound < $this->upperbound) {
                // Internal range.
                $fillinginstruction = get_string('restriction_lowerupper', 'surveyprofield_time', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                $fillinginstruction = get_string('restriction_upperlower', 'surveyprofield_time', $a);
            }
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format, 0);
                $fillinginstruction = get_string('restriction_lower', 'surveyprofield_time', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format, 0);
                $fillinginstruction = get_string('restriction_upper', 'surveyprofield_time', $a);
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
     * @return void
     */
    public function userform_save_preprocessing($answer, $olduseranswer, $searchform) {
        if (isset($answer['noanswer'])) { // This is correct for input and search form both.
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                if (($answer['hour'] == SURVEYPRO_INVITEVALUE) || ($answer['minute'] == SURVEYPRO_INVITEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_time_to_unix_time($answer['hour'], $answer['minute']);
                }
            } else {
                if (($answer['hour'] == SURVEYPRO_IGNOREMEVALUE) || ($answer['minute'] == SURVEYPRO_IGNOREMEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_time_to_unix_time($answer['hour'], $answer['minute']);
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
     * @return void
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
                $prefill[$this->itemname.'_hour'] = $datearray['hours'];
                $prefill[$this->itemname.'_minute'] = $datearray['minutes'];
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
     * @return void
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
            return userdate($content, get_string($format, 'surveyprofield_time'), 0);
        }
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return void
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname.'_group');

        return $elementnames;
    }
}
