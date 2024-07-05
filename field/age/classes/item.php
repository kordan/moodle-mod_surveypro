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
 * This file contains the surveyprofield_age
 *
 * @package   surveyprofield_age
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_age;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itembase;

require_once($CFG->dirroot.'/mod/surveypro/field/age/lib.php');

/**
 * Class to manage each aspect of the age item
 *
 * @package   surveyprofield_age
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
     * @var int Lowerbound for the age in unixtime
     */
    protected $lowerbound;

    /**
     * @var int Upperbound for the age in unixtime
     */
    protected $upperbound;

    // Service variables.

    /**
     * @var int Year of the defaultvalue for the age
     */
    protected $defaultvalueyear;

    /**
     * @var int Month of the defaultvalue for the age
     */
    protected $defaultvaluemonth;

    /**
     * @var int Year of the lowerbound for the age in unixtime
     */
    protected $lowerboundyear;

    /**
     * @var int Month of the lowerbound for the age in unixtime
     */
    protected $lowerboundmonth;

    /**
     * @var int Year of the upperbound for the age in unixtime
     */
    protected $upperboundyear;

    /**
     * @var int Month of the upperbound for the age in unixtime
     */
    protected $upperboundmonth;

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

        // List of properties set to static values..
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'age';

        // Override the list of fields using format, whether needed.
        // Nothing to override, here.

        // Other element specific properties.
        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

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

        // Do parent item saving stuff here (itembase::item_save($record))).
        return parent::item_save($record);
    }

    /**
     * Set defaults to fields of items
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_add_fields_default_to_child_table(&$record) {
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        // $record->defaultvalue;
        $record->lowerbound = -2148552000;
        $record->upperbound = 1193918400;
    }

    /**
     * Verify the validity of contents of the record
     * for instance: age not greater than maximum age
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_force_coherence($record) {
        if (isset($record->defaultvalue)) {
            $maxyear = get_config('surveyprofield_age', 'maximumage');
            $maximumage = $this->item_age_to_unix_time($maxyear, 11);
            if ($record->defaultvalue > $maximumage) {
                $record->defaultvalue = $maximumage;
            }
        }
    }

    /**
     * Convert unix time to an age.
     *
     * @param int $unixtime
     * @return void
     */
    public function item_split_unix_time($unixtime) {
        $getdate = parent::item_split_unix_time($unixtime);

        $getdate['year'] -= SURVEYPROFIELD_AGE_YEAROFFSET;
        if ($getdate['mon'] == 12) {
            $getdate['year']++;
            $getdate['mon'] = 0;
        }

        return $getdate;
    }

    /**
     * Starting from an age array returns the corresponding age in text format.
     *
     * @param array $agearray
     * @return void
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
     * Convert an age to unixtime.
     *
     * @param int $year
     * @param int $month
     * @return int unixtime
     */
    public function item_age_to_unix_time($year, $month) {
        $year += SURVEYPROFIELD_AGE_YEAROFFSET;

        return (gmmktime(12, 0, 0, $month, 1, $year));
    }

    /**
     * Prepare values for the mform of this item.
     *
     * translates the age class property $fieldlist in $field.'year' and $field.'month'
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
            $agearray = $this->item_split_unix_time($this->{$field});
            $this->{$field.'year'} = $agearray['year'];
            $this->{$field.'month'} = $agearray['mon'];
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
            if (isset($record->{$field.'year'}) && isset($record->{$field.'month'})) {
                $record->{$field} = $this->item_age_to_unix_time($record->{$field.'year'}, $record->{$field.'month'});
                unset($record->{$field.'year'});
                unset($record->{$field.'month'});
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
     * Set defaultvalueyear.
     *
     * @param string $defaultvalueyear
     * @return void
     */
    public function set_defaultvalueyear($defaultvalueyear) {
        $this->defaultvalueyear = $defaultvalueyear;
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
     * Set lowerboundyear.
     *
     * @param string $lowerboundyear
     * @return void
     */
    public function set_lowerboundyear($lowerboundyear) {
        $this->lowerboundyear = $lowerboundyear;
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
     * Set upperboundyear.
     *
     * @param string $upperboundyear
     * @return void
     */
    public function set_upperboundyear($upperboundyear) {
        $this->upperboundyear = $upperboundyear;
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
     * Get defaultvalueyear.
     *
     * @return $this->defaultvalueyear
     */
    public function get_defaultvalueyear() {
        return $this->defaultvalueyear;
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
     * Get lowerboundyear.
     *
     * @return $this->lowerboundyear
     */
    public function get_lowerboundyear() {
        return $this->lowerboundyear;
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
     * Get upperboundyear.
     *
     * @return $this->upperboundyear
     */
    public function get_upperboundyear() {
        return $this->upperboundyear;
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
     * Get the list of composite fields.
     *
     * @return void
     */
    public function get_composite_fields() {
        return ['defaultvalue', 'lowerbound', 'upperbound'];
    }

    /**
     * Prepare presets for itemsetuprform with the help of the parent class too.
     *
     * @return array $data
     */
    public function get_plugin_presets() {
        $pluginproperties = [
            'defaultoption', 'defaultvalue', 'lowerbound', 'upperbound', 'defaultvalueyear', 'defaultvaluemonth',
            'lowerboundyear', 'lowerboundmonth', 'upperboundyear', 'upperboundmonth',
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
        $fieldlist['surveyprofield_age'] = [];

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
    <xs:element name="surveyprofield_age">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="unixtime" minOccurs="0"/>
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
        $stryears = get_string('years');
        $strmonths = get_string('months', 'surveyprofield_age');

        if ($this->position == SURVEYPRO_POSITIONLEFT) {
            $elementlabel = $this->get_contentwithnumber();
        } else {
            $elementlabel = '&nbsp;';
        }

        // Begin of: element values.
        $years = [];
        $months = [];
        if (!$searchform) {
            if ($this->defaultoption == SURVEYPRO_INVITEDEFAULT) {
                $years[SURVEYPRO_INVITEVALUE] = get_string('inviteyear', 'surveyprofield_age');
                $months[SURVEYPRO_INVITEVALUE] = get_string('invitemonth', 'surveyprofield_age');
            }
        } else {
            $years[SURVEYPRO_IGNOREMEVALUE] = '';
            $months[SURVEYPRO_IGNOREMEVALUE] = '';
        }
        $yearsrange = range($this->lowerboundyear, $this->upperboundyear);
        $years += array_combine($yearsrange, $yearsrange);
        if ($this->lowerboundyear == $this->upperboundyear) {
            $monthsrange = range($this->lowerboundmonth, $this->upperboundmonth);
        } else {
            $monthsrange = range(0, 11);
        }
        $months += array_combine($monthsrange, $monthsrange);
        // End of: element values.

        // Begin of: mform element.
        $attributes = [];
        $elementgroup = [];
        $class = ['class' => 'indent-'.$this->indent];
        $baseid = 'id_field_age_'.$this->sortindex;
        $basename = $this->itemname;

        $attributes['id'] = $baseid.'_year';
        $elementgroup[] = $mform->createElement('select', $basename.'_year', '', $years, $attributes);

        if ($readonly) {
            $itemname = 'yearlabel_'.$this->itemid;
            $attributes['id'] = $baseid.'_yearseparator';
            $elementgroup[] = $mform->createElement('static', $itemname, '', $stryears, $attributes);
        }

        $attributes['id'] = $baseid.'_month';
        $elementgroup[] = $mform->createElement('select', $basename.'_month', '', $months, $attributes);

        if ($readonly) {
            $itemname = 'monthlabel_'.$this->itemid;
            $attributes['id'] = $baseid.'_monthseparator';
            $elementgroup[] = $mform->createElement('static', $itemname, '', $strmonths, $attributes);
        }

        if ($this->required) {
            if (!$searchform) {
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, ' ', false, $class);

                if (!$searchform) {
                    // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                    // I do not want JS form validation if the page is submitted through the "previous" button.
                    // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                    // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                    $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $basename.'_extrarow_group' : $basename.'_group';
                    $mform->_required[] = $starplace;
                }
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
            $strnoanswer = get_string('noanswer', 'mod_surveypro');
            $elementgroup[] = $mform->createElement('checkbox', $basename.'_noanswer', '', $strnoanswer, $attributes);
            $separator[] = ' ';

            if (!$searchform) {
                $mform->addGroup($elementgroup, $basename.'_group', $elementlabel, $separator, false, $class);
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
                    $agearray['year'] = SURVEYPRO_INVITEVALUE;
                    $agearray['mon'] = SURVEYPRO_INVITEVALUE;
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
                        $agearray = $this->item_split_unix_time($this->defaultvalue);
                    } else if ($this->lowerbound) {
                        $agearray = $this->item_split_unix_time($this->lowerbound);
                    } else {
                        $agearray['year'] = $years[1];
                        $agearray['mon'] = $months[1];
                    }
                    break;
                default:
                    $message = 'Unexpected $this->defaultoption = '.$this->defaultoption;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
            $mform->setDefault($basename.'_year', $agearray['year']);
            $mform->setDefault($basename.'_month', $agearray['mon']);
        } else {
            $mform->setDefault($basename.'_year', SURVEYPRO_IGNOREMEVALUE);
            $mform->setDefault($basename.'_month', SURVEYPRO_IGNOREMEVALUE);
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
        // Because of this, if ($this->required) { if (empty($data[$this->itemname])) { is useless.

        if (isset($data[$this->itemname.'_noanswer'])) {
            return $errors; // Nothing to validate.
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
            return $errors;
        }
        // End of: verify the content of each drop down menu.

        if ($searchform) {
            // Stop here your investigation. I don't need further validations.
            return $errors;
        }

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $userinput = $this->item_age_to_unix_time($data[$this->itemname.'_year'], $data[$this->itemname.'_month']);

        if ($haslowerbound && $hasupperbound) {
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

        return $errors;
    }

    /**
     * Prepare the string with the filling instruction.
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
            $a = new \stdClass();
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
     * Starting from the info set by the user in the form
     * this method calculates what to save in the db
     * or what to return for the search form.
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

        // Output.
        $agearray = $this->item_split_unix_time($content);
        $return = $this->item_age_to_text($agearray);

        return $return;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        return [$this->itemname.'_group'];
    }
}
