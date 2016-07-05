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
 * This file contains the surveyprofield_recurrence
 *
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/recurrence/lib.php');

/**
 * Class to manage each aspect of the recurrence item
 *
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_recurrence_field extends mod_surveypro_itembase {

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
     * @var bool 0 => optional item; 1 => mandatory item;
     */
    protected $required;

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
     * @var string Value of the default setting (invite, custom...)
     */
    protected $defaultoption;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var int Defaultvalue for the recurrence in unixtime
     */
    protected $defaultvalue;

    /**
     * @var int Month of the defaultvalue for the recurrence
     */
    protected $defaultvaluemonth;

    /**
     * @var int Day of the defaultvalue for the recurrence
     */
    protected $defaultvalueday;

    /**
     * @var int Lowerbound for the recurrence in unixtime
     */
    protected $lowerbound;

    /**
     * @var int Month of the lowerbound for the recurrence
     */
    protected $lowerboundmonth;

    /**
     * @var int Day of the lowerbound for the recurrence
     */
    protected $lowerboundday;

    /**
     * @var int Upperbound for the recurrence in unixtime
     */
    protected $upperbound;

    /**
     * @var int Month of the upperbound for the recurrence
     */
    protected $upperboundmonth;

    /**
     * @var int Day of the upperbound for the recurrence
     */
    protected $upperboundday;

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
        $this->plugin = 'recurrence';
        $this->savepositiontodb = false;

        // Other element specific properties.
        $this->lowerbound = $this->item_recurrence_to_unix_time(1, 1);
        $this->upperbound = $this->item_recurrence_to_unix_time(12, 31);
        $this->defaultvalue = $this->lowerbound;

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields I do not want to have in the item definition form.
        $this->insetupform['trimonsave'] = false;

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
     * Is this item available as a parent?
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
        $record->content = 'Recurrence [dd/mm]';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'recurrence_001';
        $record->indent = 0;
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->defaultvalue = 43200;
        $record->downloadformat = 'strftime3';
        $record->lowerbound = 43200;
        $record->upperbound = 31492800;
    }

    /**
     * Change $month, $day to unixtime.
     *
     * @param int $month
     * @param int $day
     * @return int unixtime
     */
    public function item_recurrence_to_unix_time($month, $day) {
        return (gmmktime(12, 0, 0, $month, $day, SURVEYPROFIELD_RECURRENCE_YEAROFFSET)); // This is GMT.
    }

    /**
     * Prepare values for the mform of this item.
     *
     * translates the recurrence class property $fieldlist in $field.'month' and $field.'day'
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_get_composite_fields();
        foreach ($fieldlist as $field) {
            if (!isset($this->{$field})) {
                switch ($field) {
                    case 'defaultvalue':
                        continue 2; // It may be; continues switch and foreach too.
                    case 'lowerbound':
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                    case 'upperbound':
                        $this->{$field} = $this->item_recurrence_to_unix_time(1, 1);
                        break;
                }
            }
            if (!empty($this->{$field})) {
                $recurrencearray = self::item_split_unix_time($this->{$field});
                $this->{$field.'_month'} = $recurrencearray['mon'];
                $this->{$field.'_day'} = $recurrencearray['mday'];
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
        $fieldlist = $this->item_get_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'_month'}) && isset($record->{$field.'_day'})) {
                $record->{$field} = $this->item_recurrence_to_unix_time($record->{$field.'_month'}, $record->{$field.'_day'});
                unset($record->{$field.'_month'});
                unset($record->{$field.'_day'});
            } else {
                $record->{$field} = null;
            }
        }

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'trimonsave', 'hideinstructions' were already considered in item_get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * Get the list of composite fields.
     *
     * @return void
     */
    public function item_get_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /**
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function item_get_downloadformats() {
        $options = array();

        for ($i = 1; $i < 4; $i++) {
            $strname = 'strftime'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $options[$strname] = get_string($strname, 'surveyprofield_recurrence');
        }
        $options['unixtime'] = get_string('unixtime', 'mod_surveypro');

        return $options;
    }

    /**
     * Item_check_monthday.
     *
     * @param int $day
     * @param int $month
     * @return void
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
     * Get the format recognized (without any really good reason) as friendly.
     *
     * @return the friendly format
     */
    public function item_get_friendlyformat() {
        return 'strftime3';
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
        global $DB, $USER;

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $noanswerstr = get_string('noanswer', 'mod_surveypro');
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        $idprefix = 'id_surveypro_field_recurrence_'.$this->sortindex;

        // Begin of: element values.
        $days = array();
        $months = array();
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $days[SURVEYPRO_INVITEVALUE] = get_string('inviteday', 'surveyprofield_recurrence');
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_recurrence');
            }
        } else {
            $days[SURVEYPRO_IGNOREMEVALUE] = '';
            $months[SURVEYPRO_IGNOREMEVALUE] = '';
        }
        $daysrange = range(1, 31);
        $days += array_combine($daysrange, $daysrange);
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = userdate(gmmktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
        }
        // End of: element values.

        // Begin of: mform element.
        $attributes = array();
        $elementgroup = array();

        $itemname = $this->itemname.'_day';
        $attributes['id'] = $idprefix.'_day';
        $attributes['class'] = 'indent-'.$this->indent.' recurrence_select';
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $itemname, '', $days, $attributes);

        $itemname = $this->itemname.'_month';
        $attributes['id'] = $idprefix.'_month';
        $attributes['class'] = 'recurrence_select';
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $itemname, '', $months, $attributes);

        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

            if (!$searchform) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $itemname = $this->itemname.'_noanswer';
            $attributes['id'] = $idprefix.'_noanswer';
            $attributes['class'] = 'recurrence_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $noanswerstr, $attributes);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
        }
        // End of: mform element.

        // Begin of: default section.
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $mform->setDefault($this->itemname.'_day', SURVEYPRO_INVITEVALUE);
                $mform->setDefault($this->itemname.'_month', SURVEYPRO_INVITEVALUE);
            } else {
                switch ($this->defaultoption) {
                    case SURVEYPRO_CUSTOMDEFAULT:
                        $recurrencearray = self::item_split_unix_time($this->defaultvalue, true);
                        break;
                    case SURVEYPRO_TIMENOWDEFAULT:
                        $recurrencearray = self::item_split_unix_time(time(), true);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $recurrencearray = self::item_split_unix_time($this->lowerbound, true);
                        $mform->setDefault($this->itemname.'_noanswer', '1');
                        break;
                    case SURVEYPRO_LIKELASTDEFAULT:
                        // Look for the most recent submission I made.
                        $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                        $where = array('userid' => $USER->id);
                        $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, $where, IGNORE_MISSING);
                        $where = array('itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid);
                        if ($time = $DB->get_field('surveypro_answer', 'content', $where, IGNORE_MISSING)) {
                            $recurrencearray = self::item_split_unix_time($time, false);
                        } else { // As in standard default.
                            $recurrencearray = self::item_split_unix_time(time(), true);
                        }
                        break;
                    default:
                        $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                        debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                }
                $mform->setDefault($this->itemname.'_day', $recurrencearray['mday']);
                $mform->setDefault($this->itemname.'_month', $recurrencearray['mon']);
            }
        } else {
            $mform->setDefault($this->itemname.'_day', SURVEYPRO_IGNOREMEVALUE);
            $mform->setDefault($this->itemname.'_month', SURVEYPRO_IGNOREMEVALUE);
            if (!$this->required) {
                $mform->setDefault($this->itemname.'_noanswer', '0');
            }
        }
        // End of: default section.
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
        // This plugin displays as dropdown menu. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // Nothing to validate.
        }

        $errorkey = $this->itemname.'_group';

        // Begin of: verify the content of each drop down menu.
        if (!$searchform) {
            $testpassed = true;
            $testpassed = $testpassed && ($data[$this->itemname.'_day'] != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_INVITEVALUE);
        } else {
            // Both drop down menues are allowed to be == SURVEYPRO_IGNOREMEVALUE.
            // But not only 1.
            $testpassed = true;
            if ($data[$this->itemname.'_day'] == SURVEYPRO_IGNOREMEVALUE) {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] == SURVEYPRO_IGNOREMEVALUE);
            } else {
                $testpassed = $testpassed && ($data[$this->itemname.'_month'] != SURVEYPRO_IGNOREMEVALUE);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_recurrencenotsetrequired', 'surveyprofield_recurrence');
            } else {
                $a = get_string('noanswer', 'mod_surveypro');
                $errors[$errorkey] = get_string('uerr_recurrencenotset', 'surveyprofield_recurrence', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu.

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return;
        }

        if (!$this->item_check_monthday($data[$this->itemname.'_day'], $data[$this->itemname.'_month'])) {
            $errors[$errorkey] = get_string('uerr_incorrectrecurrence', 'surveyprofield_recurrence');
        }

        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $userinput = $this->item_recurrence_to_unix_time($data[$this->itemname.'_month'], $data[$this->itemname.'_day']);

        if ($haslowerbound && $hasupperbound) {
            // Internal range.
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
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {
        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $format = get_string('strftimedateshort', 'langconfig');
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);

            // Internal range.
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
        if (isset($answer['noanswer'])) { // This is correct for input and search form both.
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                if (($answer['month'] == SURVEYPRO_INVITEVALUE) || ($answer['day'] == SURVEYPRO_INVITEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_recurrence_to_unix_time($answer['month'], $answer['day']);
                }
            } else {
                if (($answer['month'] == SURVEYPRO_IGNOREMEVALUE) || ($answer['day'] == SURVEYPRO_IGNOREMEVALUE)) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_recurrence_to_unix_time($answer['month'], $answer['day']);
                }
            }
        }
    }

    /**
     * This method is called from get_prefill_data (in formbase.class.php) to set $prefill at user form display time.
     *
     * @param object $fromdb
     * @return associative array with disaggregate element values
     */
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
            } else {
                $recurrencearray = self::item_split_unix_time($fromdb->content);
                $prefill[$this->itemname.'_day'] = $recurrencearray['mday'];
                $prefill[$this->itemname.'_month'] = $recurrencearray['mon'];
            }
        }

        return $prefill;
    }

    /**
     * Starting from the info stored into $answer, this function returns the corresponding content for the export file.
     *
     * @param object $answer
     * @param string $format
     * @return string - the string for the export file
     */
    public function userform_db_to_export($answer, $format='') {
        $quickresponse = parent::userform_db_to_export($answer, $format);
        if ($quickresponse !== null) { // Parent method provided the response.
            return $quickresponse;
        }

        // The content of the provided answer.
        $content = $answer->content;

        // Format.
        if ($format == SURVEYPRO_FRIENDLYFORMAT) {
            $format = $this->item_get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // Output.
        if ($format == 'unixtime') {
            $return = $content;
        } else {
            $return = userdate($content, get_string($format, 'surveyprofield_recurrence'), 0);
        }

        return $return;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array();
        $elementnames[] = $this->itemname.'_group';

        return $elementnames;
    }
}
