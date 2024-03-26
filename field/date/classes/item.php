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
 * This file contains the surveyprofield_date
 *
 * @package   surveyprofield_date
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_date;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;
use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/mod/surveypro/field/date/lib.php');

/**
 * Class to manage each aspect of the date item
 *
 * @package   surveyprofield_date
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
     * @var int Defaultvalue for the date in unixtime
     */
    protected $defaultvalue;

    /**
     * @var int Year of the defaultvalue for the date in unixtime
     */
    protected $defaultvalueyear;

    /**
     * @var int Month of the defaultvalue for the date in unixtime
     */
    protected $defaultvaluemonth;

    /**
     * @var int Day of the defaultvalue for the date in unixtime
     */
    protected $defaultvalueday;

    /**
     * @var int Lowerbound for the date in unixtime
     */
    protected $lowerbound;

    /**
     * @var int Year of the lowerbound for the date in unixtime
     */
    protected $lowerboundyear;

    /**
     * @var int Month of the lowerbound for the date in unixtime
     */
    protected $lowerboundmonth;

    /**
     * @var int Day of the lowerbound for the date in unixtime
     */
    protected $lowerboundday;

    /**
     * @var int Upperbound for the date in unixtime
     */
    protected $upperbound;

    /**
     * @var int Year of the upperbound for the date in unixtime
     */
    protected $upperboundyear;

    /**
     * @var int Month of the upperbound for the date in unixtime
     */
    protected $upperboundmonth;

    /**
     * @var int Day of the upperbound for the date in unixtime
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
     * @param \stdClass $cm
     * @param object $surveypro
     * @param int $itemid Optional item ID
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        global $DB;

        parent::__construct($cm, $surveypro, $itemid, $getparentcontent);

        // List of properties set to static values.
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'date';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        // No properties here.

        // Override properties depending from $surveypro settings.
        $this->lowerbound = $this->item_date_to_unix_time($this->surveypro->startyear, 1, 1);
        $this->upperbound = $this->item_date_to_unix_time($this->surveypro->stopyear, 12, 31);
        $this->defaultvalue = $this->lowerbound;

        // List of fields I do not want to have in the item definition form.
        // Empty list.

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
     * Is this item available as a parent?
     *
     * @return the content of the static property "canbeparent"
     */
    public static function get_canbeparent() {
        return self::$canbeparent;
    }

    /**
     * Item add mandatory plugin fields
     * Copy mandatory fields to $record
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_add_mandatory_plugin_fields(&$record) {
        $record->content = 'Date [dd/mm/yyyy]';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'date_001';
        $record->indent = 0;
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->defaultvalue = 43200;
        $record->downloadformat = 'strftime05';
        $record->lowerbound = 43200;
        $record->upperbound = 1609416000;
    }

    /**
     * Change $year, $month, $day to unixtime.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int unixtime
     */
    public function item_date_to_unix_time($year, $month, $day) {
        return (mktime(12, 0, 0, $month, $day, $year));
    }

    /**
     * Verify the validity of contents of the record
     * for instance: date not greater than maximum date
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_force_coherence($record) {
        if (isset($record->defaultvalue)) {
            $mindate = $this->item_date_to_unix_time($this->surveypro->startyear, 1, 1);
            if ($record->defaultvalue < $mindate) {
                $record->defaultvalue = $mindate;
            }
            $maxdate = $this->item_date_to_unix_time($this->surveypro->startyear, 12, 31);
            if ($record->defaultvalue > $maxdate) {
                $record->defaultvalue = $maxdate;
            }
        }
    }

    /**
     * Prepare values for the mform of this item.
     *
     * translates the date class property $fieldlist in $field.'year', $field.'month' and $field.'day'
     *
     * @return void
     */
    public function item_custom_fields_to_form() {
        // 1. Special management for composite fields.
        $fieldlist = $this->get_composite_fields();
        foreach ($fieldlist as $field) {
            if (!$this->{$field}) {
                continue;
            }
            $datearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'year'} = $datearray['year'];
            $this->{$field.'month'} = $datearray['mon'];
            $this->{$field.'day'} = $datearray['mday'];
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
        $fieldlist = $this->get_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'year'}) && isset($record->{$field.'month'}) && isset($record->{$field.'day'})) {
                $year = $record->{$field.'year'};
                $month = $record->{$field.'month'};
                $day = $record->{$field.'day'};
                $record->{$field} = $this->item_date_to_unix_time($year, $month, $day);
                unset($record->{$field.'year'});
                unset($record->{$field.'month'});
                unset($record->{$field.'day'});
            } else {
                $record->{$field} = null;
            }
        }

        // 2. Override few values.
        // Nothing to do: no need to overwrite variables.

        // 3. Set values corresponding to checkboxes.
        // Take care: 'required', 'hideinstructions' were already considered in get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    // MARK get.

    /**
     * Get the list of composite fields.
     *
     * @return void
     */
    public function get_composite_fields() {
        return ['defaultvalue', 'lowerbound', 'upperbound'];
    }

    /**
     * Get the content of the downloadformats menu of the item setup form.
     *
     * @return array of downloadformats
     */
    public function get_downloadformats() {
        $options = [];
        $timenow = time();

        for ($i = 1; $i < 11; $i++) {
            $strname = 'strftime'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $options[$strname] = userdate($timenow, get_string($strname, 'surveyprofield_date'));
        }
        $options['unixtime'] = get_string('unixtime', 'mod_surveypro');

        return $options;
    }

    /**
     * Get the format recognized (without any really good reason) as friendly.
     *
     * @return the friendly format
     */
    public function get_friendlyformat() {
        return 'strftime05';
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
    <xs:element name="surveyprofield_date">
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

                <xs:element name="required" type="xs:int"/>
                <xs:element name="indent" type="xs:int"/>
                <xs:element name="position" type="xs:int"/>
                <xs:element name="customnumber" type="xs:string" minOccurs="0"/>
                <xs:element name="hideinstructions" type="xs:int"/>
                <xs:element name="variable" type="xs:string"/>
                <xs:element name="extranote" type="xs:string" minOccurs="0"/>

                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="unixtime" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:string"/>
                <xs:element name="lowerbound" type="unixtime"/>
                <xs:element name="upperbound" type="unixtime"/>
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
     * Define the mform element for the userform and the searchform.
     *
     * @param \moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        global $DB, $USER;

        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        $idprefix = 'id_surveypro_field_date_'.$this->sortindex;

        // Begin of: element values.
        $days = [];
        $months = [];
        $years = [];
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $days[SURVEYPRO_INVITEVALUE] = get_string('inviteday', 'surveyprofield_date');
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_date');
                $years[SURVEYPRO_INVITEVALUE] = get_string('inviteyear', 'surveyprofield_date');
            }
        }
        // Condition limiting days.
        $condition = true;
        $condition = $condition && ($this->lowerboundyear == $this->upperboundyear);
        $condition = $condition && ($this->lowerboundmonth == $this->upperboundmonth);
        if ($condition) {
            $daysrange = range($this->lowerboundday, $this->upperboundday);
        } else {
            $daysrange = range(1, 31);
        }
        $days += array_combine($daysrange, $daysrange);
        // Condition limiting months.
        if ($this->lowerboundyear == $this->upperboundyear) {
            for ($i = $this->lowerboundmonth; $i <= $this->upperboundmonth; $i++) {
                $months[$i] = userdate(mktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
            }
        } else {
            for ($i = 1; $i <= 12; $i++) {
                $months[$i] = userdate(mktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
            }
        }
        // No condition limiting years.
        $yearsrange = range($this->lowerboundyear, $this->upperboundyear);
        $years += array_combine($yearsrange, $yearsrange);
        // End of: element values.

        // Begin of: mform element.
        $attributes = [];
        $elementgroup = [];

        $itemname = $this->itemname.'_day';
        $attributes['id'] = $idprefix.'_day';
        $attributes['class'] = 'indent-'.$this->indent.' date_select';
        $elementgroup[] = $mform->createElement('select', $itemname, '', $days, $attributes);

        $itemname = $this->itemname.'_month';
        $attributes['id'] = $idprefix.'_month';
        $attributes['class'] = 'date_select';
        $elementgroup[] = $mform->createElement('select', $itemname, '', $months, $attributes);

        $itemname = $this->itemname.'_year';
        $attributes['id'] = $idprefix.'_year';
        $elementgroup[] = $mform->createElement('select', $itemname, '', $years, $attributes);

        if ($this->required) {
            if (!$searchform) {
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            } else {
                $itemname = $this->itemname.'_ignoreme';
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $idprefix.'_ignoreme';
                $attributes['class'] = 'character_check';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);
                $mform->setType($this->itemname, PARAM_RAW);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
                $mform->setDefault($this->itemname.'_ignoreme', '1');
            }
        } else {
            $itemname = $this->itemname.'_noanswer';
            $attributes['id'] = $idprefix.'_noanswer';
            $attributes['class'] = 'date_check';
            $noanswerstr = get_string('noanswer', 'mod_surveypro');
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $noanswerstr, $attributes);

            if (!$searchform) {
                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_noanswer', 'checked');
            } else {
                $itemname = $this->itemname.'_ignoreme';
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $idprefix.'_ignoreme';
                $attributes['class'] = 'character_check';
                $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $starstr, $attributes);
                $mform->setType($this->itemname, PARAM_RAW);

                $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
                $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
                $mform->setDefault($this->itemname.'_ignoreme', '1');
            }
        }
        // End of: mform element.

        // Default section.
        if (!$searchform) {
            switch ($this->defaultoption) {
                case SURVEYPRO_INVITEDEFAULT:
                    $datearray['mday'] = SURVEYPRO_INVITEVALUE;
                    $datearray['mon'] = SURVEYPRO_INVITEVALUE;
                    $datearray['year'] = SURVEYPRO_INVITEVALUE;
                    break;
                case SURVEYPRO_NOANSWERDEFAULT:
                    $mform->setDefault($this->itemname.'_noanswer', '1');
                    // No break here. SURVEYPRO_CUSTOMDEFAULT case is a subset of the SURVEYPRO_NOANSWERDEFAULT case.
                case SURVEYPRO_CUSTOMDEFAULT:
                    // I need to set a value for the default field even if it disabled.
                    // When opening this form for the first time, I have:
                    // $this->defaultoption = SURVEYPRO_INVITEDEFAULT
                    // so $this->defaultvalue may be empty.
                    // Generally $this->lowerbound is set but... to avoid nasty surprises... I also provide a parachute else.
                    if ($this->defaultvalue) {
                        $datearray = $this->item_split_unix_time($this->defaultvalue);
                    } else if ($this->lowerbound) {
                        $datearray = $this->item_split_unix_time($this->lowerbound);
                    } else {
                        $datearray['mday'] = $days[1];
                        $datearray['mon'] = $months[1];
                        $datearray['year'] = $years[1];
                    }
                    break;
                case SURVEYPRO_TIMENOWDEFAULT:
                    $datearray = $this->item_split_unix_time(time());
                    break;
                case SURVEYPRO_LIKELASTDEFAULT:
                    // Look for the most recent submission I made.
                    $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                    $where = ['userid' => $USER->id];
                    $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, $where, IGNORE_MISSING);
                    $where = ['itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid];
                    if ($time = $DB->get_field('surveypro_answer', 'content', $where, IGNORE_MISSING)) {
                        $datearray = $this->item_split_unix_time($time);
                    } else { // As in standard default.
                        $datearray = $this->item_split_unix_time(time());
                    }
                    break;
                default:
                    $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
            $mform->setDefault($this->itemname.'_day', $datearray['mday']);
            $mform->setDefault($this->itemname.'_month', $datearray['mon']);
            $mform->setDefault($this->itemname.'_year', $datearray['year']);
        }
        if ($searchform) {
            if (!$this->required) {
                $mform->setDefault($this->itemname.'_noanswer', '0');
            }
        }
        // End of: default section.
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
        // This plugin displays as dropdown menu. It will never return empty values.
        // If ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if (isset($data[$this->itemname.'_noanswer'])) {
            return; // Nothing to validate.
        }

        // Make validation in the search form too.
        // I can not use if ($searchform) { return; because I still need to validate the correcteness of the date.
        if (isset($data[$this->itemname.'_ignoreme'])) {
            return; // Nothing to validate.
        }

        $errorkey = $this->itemname.'_group';

        $userday = $data[$this->itemname.'_day'];
        $usermonth = $data[$this->itemname.'_month'];
        $useryear = $data[$this->itemname.'_year'];

        // Begin of: verify the content of each drop down menu.
        $testpassed = true;
        if (!$searchform) {
            $testpassed = $testpassed && ($userday != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($usermonth != SURVEYPRO_INVITEVALUE);
            $testpassed = $testpassed && ($useryear != SURVEYPRO_INVITEVALUE);
        } else {
            // All three drop down menues are allowed to be == SURVEYPRO_IGNOREMEVALUE.
            // But not only 2 or 1.
            if ($userday == SURVEYPRO_IGNOREMEVALUE) {
                $testpassed = $testpassed && ($usermonth == SURVEYPRO_IGNOREMEVALUE);
                $testpassed = $testpassed && ($useryear == SURVEYPRO_IGNOREMEVALUE);
            } else {
                $testpassed = $testpassed && ($usermonth != SURVEYPRO_IGNOREMEVALUE);
                $testpassed = $testpassed && ($useryear != SURVEYPRO_IGNOREMEVALUE);
            }
        }
        if (!$testpassed) {
            if ($this->required) {
                $errors[$errorkey] = get_string('uerr_datenotsetrequired', 'surveyprofield_date');
            } else {
                $a = get_string('noanswer', 'mod_surveypro');
                $errors[$errorkey] = get_string('uerr_datenotset', 'surveyprofield_date', $a);
            }
            return;
        }
        // End of: verify the content of each drop down menu.

        if (!utility_item::date_is_valid($userday, $usermonth, $useryear)) {
            $errors[$errorkey] = get_string('ierr_invalidinput', 'mod_surveypro');
            return;
        }

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return;
        }

        $haslowerbound = ($this->lowerbound != $this->item_date_to_unix_time($this->surveypro->startyear, 1, 1));
        $hasupperbound = ($this->upperbound != $this->item_date_to_unix_time($this->surveypro->stopyear, 12, 31));

        $userinput = $this->item_date_to_unix_time($useryear, $usermonth, $userday);

        if ($haslowerbound && $hasupperbound) {
            // Internal range.
            if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_date');
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_date');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_date');
            }
        }
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {
        $haslowerbound = ($this->lowerbound != $this->item_date_to_unix_time($this->surveypro->startyear, 1, 1));
        $hasupperbound = ($this->upperbound != $this->item_date_to_unix_time($this->surveypro->stopyear, 12, 31));

        $formatkey = $this->get_friendlyformat();
        $format = get_string($formatkey, 'surveyprofield_date');

        if ($haslowerbound && $hasupperbound) {
            $a = new \stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);
            $fillinginstruction = get_string('restriction_lowerupper', 'surveyprofield_date', $a);
        } else {
            $fillinginstruction = '';
            if ($haslowerbound) {
                $a = userdate($this->lowerbound, $format, 0);
                $fillinginstruction = get_string('restriction_lower', 'surveyprofield_date', $a);
            }
            if ($hasupperbound) {
                $a = userdate($this->upperbound, $format, 0);
                $fillinginstruction = get_string('restriction_upper', 'surveyprofield_date', $a);
            }
        }

        return $fillinginstruction;
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
        if (isset($answer['noanswer'])) { // This is correct for input and search form both.
            $olduseranswer->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (!$searchform) {
                $condition = ($answer['year'] == SURVEYPRO_INVITEVALUE);
                $condition = $condition || ($answer['month'] == SURVEYPRO_INVITEVALUE);
                $condition = $condition || ($answer['day'] == SURVEYPRO_INVITEVALUE);
                if ($condition) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_date_to_unix_time($answer['year'], $answer['month'], $answer['day']);
                }
            } else {
                $condition = ($answer['year'] == SURVEYPRO_IGNOREMEVALUE);
                $condition = $condition || ($answer['month'] == SURVEYPRO_IGNOREMEVALUE);
                $condition = $condition || ($answer['day'] == SURVEYPRO_IGNOREMEVALUE);
                if ($condition) {
                    $olduseranswer->content = null;
                } else {
                    $olduseranswer->content = $this->item_date_to_unix_time($answer['year'], $answer['month'], $answer['day']);
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
    public function userform_get_prefill($fromdb) {
        $prefill = [];

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
                return $prefill;
            }

            $datearray = $this->item_split_unix_time($fromdb->content);
            $prefill[$this->itemname.'_day'] = $datearray['mday'];
            $prefill[$this->itemname.'_month'] = $datearray['mon'];
            $prefill[$this->itemname.'_year'] = $datearray['year'];
        }

        // If the "No answer" checkbox is part of the element GUI...
        if ($this->defaultoption = SURVEYPRO_NOANSWERDEFAULT) {
            $prefill[$this->itemname.'_noanswer'] = 0;
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
        // The content of the provided answer.
        $content = $answer->content;

        // Trigger 'answernotsubmitted' and 'answerisnoanswer'.
        $quickresponse = self::userform_standardcontent_to_string($content);
        if (isset($quickresponse)) { // Parent method provided the response.
            return $quickresponse;
        }

        // Format.
        if ($format == SURVEYPRO_FRIENDLYFORMAT) {
            $format = $this->get_friendlyformat();
        }
        if (empty($format)) {
            $format = $this->downloadformat;
        }

        // Output.
        if ($format == 'unixtime') {
            $return = $content;
        } else {
            // Last param "0" means: don't care of time zone.
            // My birthday is the same all around the world.
            $return = userdate($content, get_string($format, 'surveyprofield_date'), 0);
        }

        return $return;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = [$this->itemname.'_group'];

        return $elementnames;
    }
}
