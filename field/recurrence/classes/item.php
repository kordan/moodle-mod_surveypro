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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_recurrence;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;
use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/mod/surveypro/field/recurrence/lib.php');

/**
 * Class to manage each aspect of the recurrence item
 *
 * @package   surveyprofield_recurrence
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item extends itembase {

    // Itembase properties.

    /**
     * @var string Value of the default setting (invite, custom...)
     */
    protected $defaultoption;

    /**
     * @var int Defaultvalue for the item answer
     */
    protected $defaultvalue;

    /**
     * @var string Format of the content once downloaded
     */
    protected $downloadformat;

    /**
     * @var int Lowerbound for the recurrence in unixtime
     */
    protected $lowerbound;

    /**
     * @var int Upperbound for the recurrence in unixtime
     */
    protected $upperbound;

    // Service variables.

    /**
     * @var int Month of the defaultvalue for the recurrence
     */
    protected $defaultvaluemonth;

    /**
     * @var int Day of the defaultvalue for the recurrence
     */
    protected $defaultvalueday;

    /**
     * @var int Month of the lowerbound for the recurrence
     */
    protected $lowerboundmonth;

    /**
     * @var int Day of the lowerbound for the recurrence
     */
    protected $lowerboundday;

    /**
     * @var int Month of the upperbound for the recurrence
     */
    protected $upperboundmonth;

    /**
     * @var int Day of the upperbound for the recurrence
     */
    protected $upperboundday;

    // Service variables.

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
        $this->plugin = 'recurrence';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        $this->lowerbound = $this->item_recurrence_to_unix_time(1, 1);
        $this->upperbound = $this->item_recurrence_to_unix_time(12, 31);
        $this->defaultvalue = $this->lowerbound;

        // Override properties depending from $surveypro settings.
        // No properties here.

        // List of fields of the base form I do not want to have in the item definition.
        // Each (field|format) plugin receive a list of fields (quite) common to each (field|format) plugin.
        // This is the list of the elements of the itembase form fields that this (field|format) plugin does not use.
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
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        // $record->defaultvalue
        $record->downloadformat = 'strftime03';
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
        return (gmmktime(12, 0, 0, $month, $day, SURVEYPROFIELD_RECURRENCE_YEAROFFSET));
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
        $fieldlist = $this->get_composite_fields();
        foreach ($fieldlist as $field) {
            if (!$this->{$field}) {
                continue;
            }
            $recurrencearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'month'} = $recurrencearray['mon'];
            $this->{$field.'day'} = $recurrencearray['mday'];
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
        $fieldlist = $this->get_composite_fields();
        foreach ($fieldlist as $field) {
            if (isset($record->{$field.'month'}) && isset($record->{$field.'day'})) {
                $record->{$field} = $this->item_recurrence_to_unix_time($record->{$field.'month'}, $record->{$field.'day'});
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

    // MARK set.

    /**
     * Set defaultoption.
     *
     * @param string $defaultoption
     * @return void
     */
    public function set_defaultoption($defaultoption) {
        $this->defaultoption = $defaultoption;
    }

    /**
     * Set defaultvalue.
     *
     * @param string $defaultvalue
     * @return void
     */
    public function set_defaultvalue($defaultvalue) {
        $this->defaultvalue = $defaultvalue;
    }

    /**
     * Set downloadformat.
     *
     * @param string $downloadformat
     * @return void
     */
    public function set_downloadformat($downloadformat) {
        $this->downloadformat = $downloadformat;
    }

    /**
     * Set lowerbound.
     *
     * @param string $lowerbound
     * @return void
     */
    public function set_lowerbound($lowerbound) {
        $this->lowerbound = $lowerbound;
    }

    /**
     * Set upperbound.
     *
     * @param string $upperbound
     * @return void
     */
    public function set_upperbound($upperbound) {
        $this->upperbound = $upperbound;
    }

    /**
     * Set defaultvaluemonth.
     *
     * @param string $defaultvaluemonth
     * @return void
     */
    public function set_defaultvaluemonth($defaultvaluemonth) {
        $this->defaultvaluemonth = $defaultvaluemonth;
    }

    /**
     * Set defaultvalueday.
     *
     * @param string $defaultvalueday
     * @return void
     */
    public function set_defaultvalueday($defaultvalueday) {
        $this->defaultvalueday = $defaultvalueday;
    }

    /**
     * Set lowerboundmonth.
     *
     * @param string $lowerboundmonth
     * @return void
     */
    public function set_lowerboundmonth($lowerboundmonth) {
        $this->lowerboundmonth = $lowerboundmonth;
    }

    /**
     * Set lowerboundday.
     *
     * @param string $lowerboundday
     * @return void
     */
    public function set_lowerboundday($lowerboundday) {
        $this->lowerboundday = $lowerboundday;
    }

    /**
     * Set upperboundmonth.
     *
     * @param string $upperboundmonth
     * @return void
     */
    public function set_upperboundmonth($upperboundmonth) {
        $this->upperboundmonth = $upperboundmonth;
    }

    /**
     * Set upperboundday.
     *
     * @param string $upperboundday
     * @return void
     */
    public function set_upperboundday($upperboundday) {
        $this->upperboundday = $upperboundday;
    }

    // MARK get.

    /**
     * Get defaultoption.
     *
     * @return $this->defaultoption
     */
    public function get_defaultoption() {
        return $this->defaultoption;
    }

    /**
     * Get defaultvalue.
     *
     * @return $this->defaultvalue
     */
    public function get_defaultvalue() {
        return $this->defaultvalue;
    }

    /**
     * Get downloadformat.
     *
     * @return $this->downloadformat
     */
    public function get_downloadformat() {
        return $this->downloadformat;
    }

    /**
     * Get lowerbound.
     *
     * @return $this->lowerbound
     */
    public function get_lowerbound() {
        return $this->lowerbound;
    }

    /**
     * Get upperbound.
     *
     * @return $this->upperbound
     */
    public function get_upperbound() {
        return $this->upperbound;
    }

    /**
     * Get defaultvaluemonth.
     *
     * @return $this->defaultvaluemonth
     */
    public function get_defaultvaluemonth() {
        return $this->defaultvaluemonth;
    }

    /**
     * Get defaultvalueday.
     *
     * @return $this->defaultvalueday
     */
    public function get_defaultvalueday() {
        return $this->defaultvalueday;
    }

    /**
     * Get lowerboundmonth.
     *
     * @return $this->lowerboundmonth
     */
    public function get_lowerboundmonth() {
        return $this->lowerboundmonth;
    }

    /**
     * Get lowerboundday.
     *
     * @return $this->lowerboundday
     */
    public function get_lowerboundday() {
        return $this->lowerboundday;
    }

    /**
     * Get upperboundmonth.
     *
     * @return $this->upperboundmonth
     */
    public function get_upperboundmonth() {
        return $this->upperboundmonth;
    }

    /**
     * Get upperboundday.
     *
     * @return $this->upperboundday
     */
    public function get_upperboundday() {
        return $this->upperboundday;
    }

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

        for ($i = 1; $i < 4; $i++) {
            $strname = 'strftime'.str_pad($i, 2, '0', STR_PAD_LEFT);
            $options[$strname] = userdate($timenow, get_string($strname, 'surveyprofield_recurrence'));
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
        return 'strftime01';
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = [
            'defaultoption', 'defaultvalue', 'downloadformat', 'lowerbound', 'upperbound',
            'defaultvaluemonth', 'defaultvalueday', 'lowerboundmonth', 'lowerboundday', 'upperboundmonth', 'upperboundday',
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
        $fieldlist['surveyprofield_recurrence'] = [];

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
    <xs:element name="surveyprofield_recurrence">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="unixtime" minOccurs="0"/>
                <xs:element name="downloadformat" type="xs:string" minOccurs="0"/>
                <xs:element name="lowerbound" type="unixtime" minOccurs="0"/>
                <xs:element name="upperbound" type="unixtime" minOccurs="0"/>
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

        // Begin of: element values.
        $days = [];
        $months = [];
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $days[SURVEYPRO_INVITEVALUE] = get_string('inviteday', 'surveyprofield_recurrence');
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_recurrence');
            }
        }

        if ($this->lowerboundmonth == $this->upperboundmonth) {
            $daysrange = range($this->lowerboundday, $this->upperboundday);
        } else {
            $daysrange = range(1, 31);
        }
        $days += array_combine($daysrange, $daysrange);
        if ($this->lowerboundmonth <= $this->upperboundmonth) {
            for ($i = $this->lowerboundmonth; $i <= $this->upperboundmonth; $i++) {
                $months[$i] = userdate(mktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
            }
        } else {
            for ($i = $this->lowerboundmonth; $i <= 12; $i++) {
                $months[$i] = userdate(mktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
            }
            for ($i = 1; $i <= $this->upperboundmonth; $i++) {
                $months[$i] = userdate(mktime(12, 0, 0, $i, 1, 2000), "%B", 0); // January, February, March...
            }
        }
        // End of: element values.

        // Begin of: mform element.
        $attributes = [];
        $elementgroup = [];
        $baseid = 'id_field_recurrence_'.$this->sortindex;
        $class = ['class' => 'indent-'.$this->indent];
        $basename = $this->itemname;

        $attributes['id'] = $baseid.'_day';
        $elementgroup[] = $mform->createElement('select', $basename.'_day', '', $days, $attributes);
        $attributes['id'] = $baseid.'_month';
        $elementgroup[] = $mform->createElement('select', $basename.'_month', '', $months, $attributes);

        if ($this->required) {
            if (!$searchform) {
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);

                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $basename.'_extrarow_group' : $basename.'_group';
                $mform->_required[] = $starplace;
            } else {
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $baseid.'_ignoreme';
                $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
                $mform->disabledIf($basename.'_group', $basename.'_ignoreme', 'checked');
                $mform->setDefault($basename.'_ignoreme', '1');
            }
        } else {
            $attributes['id'] = $baseid.'_noanswer';
            $noanswerstr = get_string('noanswer', 'mod_surveypro');
            $elementgroup[] = $mform->createElement('checkbox', $basename.'_noanswer', '', $noanswerstr, $attributes);

            if (!$searchform) {
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
                $mform->disabledIf($basename.'_group', $basename.'_noanswer', 'checked');
            } else {
                $starstr = get_string('star', 'mod_surveypro');
                $attributes['id'] = $baseid.'_ignoreme';
                $elementgroup[] = $mform->createElement('checkbox', $basename.'_ignoreme', '', $starstr, $attributes);

                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);
                $mform->disabledIf($basename.'_group', $basename.'_ignoreme', 'checked');
                $mform->setDefault($basename.'_ignoreme', '1');
            }
        }
        // End of: mform element.

        // Begin of: default section.
        if (!$searchform) {
            switch ($this->defaultoption) {
                case SURVEYPRO_INVITEDEFAULT:
                    $recurrencearray['mday'] = SURVEYPRO_INVITEVALUE;
                    $recurrencearray['mon'] = SURVEYPRO_INVITEVALUE;
                    break;
                case SURVEYPRO_NOANSWERDEFAULT:
                    $mform->setDefault($basename.'_noanswer', '1');
                    // No break here. SURVEYPRO_CUSTOMDEFAULT case is a subset of the SURVEYPRO_NOANSWERDEFAULT case.
                case SURVEYPRO_CUSTOMDEFAULT:
                    // I need to set a value for the default field even if it disabled.
                    // When opening this form for the first time, I have:
                    // $this->defaultoption = SURVEYPRO_INVITEDEFAULT
                    // so $this->defaultvalue may be empty.
                    // Generally $this->lowerbound is set but... to avoid nasty surprises... I also provide a parachute else.
                    if ($this->defaultvalue) {
                        $recurrencearray = $this->item_split_unix_time($this->defaultvalue);
                    } else if ($this->lowerbound) {
                        $recurrencearray = $this->item_split_unix_time($this->lowerbound);
                    } else {
                        $recurrencearray['mday'] = $days[1];
                        $recurrencearray['mon'] = $months[1];
                    }
                    break;
                case SURVEYPRO_TIMENOWDEFAULT:
                    $recurrencearray = $this->item_split_unix_time(time());
                    break;
                case SURVEYPRO_LIKELASTDEFAULT:
                    // Look for the most recent submission I made.
                    $sql = 'userid = :userid ORDER BY timecreated DESC LIMIT 1';
                    $where = ['userid' => $USER->id];
                    $mylastsubmissionid = $DB->get_field_select('surveypro_submission', 'id', $sql, $where, IGNORE_MISSING);
                    $where = ['itemid' => $this->itemid, 'submissionid' => $mylastsubmissionid];
                    if ($time = $DB->get_field('surveypro_answer', 'content', $where, IGNORE_MISSING)) {
                        $recurrencearray = $this->item_split_unix_time($time);
                    } else { // As in standard default.
                        $recurrencearray = $this->item_split_unix_time(time());
                    }
                    break;
                default:
                    $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
            $mform->setDefault($basename.'_day', $recurrencearray['mday']);
            $mform->setDefault($basename.'_month', $recurrencearray['mon']);
        }
        if ($searchform) {
            if (!$this->required) {
                $mform->setDefault($basename.'_noanswer', '0');
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
            return $errors; // Nothing to validate.
        }

        // Make validation in the search form too.
        // I can not use if ($searchform) { return; because I still need to validate the correcteness of the date.
        if (isset($data[$this->itemname.'_ignoreme'])) {
            return $errors; // Nothing to validate.
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
            return $errors;
        }
        // End of: verify the content of each drop down menu.

        if (!utility_item::date_is_valid($data[$this->itemname.'_day'], $data[$this->itemname.'_month'])) {
            $errors[$errorkey] = get_string('ierr_invalidinput', 'mod_surveypro');
            return $errors;
        }

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return $errors;
        }

        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $userinput = $this->item_recurrence_to_unix_time($data[$this->itemname.'_month'], $data[$this->itemname.'_day']);

        if ($haslowerbound && $hasupperbound) {
            if ($this->lowerbound < $this->upperbound) {
                // Internal range.
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_recurrence');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                if ( ($userinput > $this->lowerbound) && ($userinput < $this->upperbound) ) {
                    $format = $this->get_friendlyformat();
                    $a = new \stdClass();
                    $a->lowerbound = userdate($this->lowerbound, get_string($format, 'surveyprofield_recurrence'), 0);
                    $a->upperbound = userdate($this->upperbound, get_string($format, 'surveyprofield_recurrence'), 0);
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyprofield_recurrence', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_recurrence');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_recurrence');
            }
        }

        return $errors;
    }

    /**
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {
        $haslowerbound = ($this->lowerbound != $this->item_recurrence_to_unix_time(1, 1));
        $hasupperbound = ($this->upperbound != $this->item_recurrence_to_unix_time(12, 31));

        $fillinginstruction = ''; // Even if nothing happen, I have something to return.
        $formatkey = $this->get_friendlyformat();
        $format = get_string($formatkey, 'surveyprofield_recurrence');

        if ($haslowerbound && $hasupperbound) {
            $a = new \stdClass();
            $a->lowerbound = userdate($this->lowerbound, $format, 0);
            $a->upperbound = userdate($this->upperbound, $format, 0);
            if ($this->lowerbound < $this->upperbound) {
                // Internal range.
                $fillinginstruction = get_string('restriction_lowerupper', 'surveyprofield_recurrence', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                // External range.
                $fillinginstruction = get_string('restriction_upperlower', 'surveyprofield_recurrence', $a);
            }
        } else {
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

            $recurrencearray = $this->item_split_unix_time($fromdb->content);
            $prefill[$this->itemname.'_day'] = $recurrencearray['mday'];
            $prefill[$this->itemname.'_month'] = $recurrencearray['mon'];
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
            // The worker's day is the same all around the world.
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
        $elementnames = [$this->itemname.'_group'];

        return $elementnames;
    }
}
