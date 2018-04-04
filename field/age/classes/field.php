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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/age/lib.php');

/**
 * Class to manage each aspect of the age item
 *
 * @package   surveyprofield_age
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyprofield_age_field extends mod_surveypro_itembase {

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
     * @var bool O => optional item; 1 => mandatory item;
     */
    protected $required;

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
     * @var int Defaultvalue for the age in unixtime
     */
    protected $defaultvalue;

    /**
     * @var int Year of the defaultvalue for the age
     */
    protected $defaultvalueyear;

    /**
     * @var int Month of the defaultvalue for the age
     */
    protected $defaultvaluemonth;

    /**
     * @var int Lowerbound for the age in unixtime
     */
    protected $lowerbound;

    /**
     * @var int Year of the lowerbound for the age in unixtime
     */
    protected $lowerboundyear;

    /**
     * @var int Month of the lowerbound for the age in unixtime
     */
    protected $lowerboundmonth;

    /**
     * @var int Upperbound for the age in unixtime
     */
    protected $upperbound;

    /**
     * @var int Year of the upperbound for the age in unixtime
     */
    protected $upperboundyear;

    /**
     * @var int Month of the upperbound for the age in unixtime
     */
    protected $upperboundmonth;

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

        // List of properties set to static values..
        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'age';
        $this->savepositiontodb = false;

        // Other element specific properties.
        $maximumage = get_config('surveyprofield_age', 'maximumage');
        $this->upperbound = $this->item_age_to_unix_time($maximumage, 11);

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
        $record->content = 'Age [yy/mm]';
        $record->contentformat = 1;
        $record->position = 0;
        $record->required = 0;
        $record->hideinstructions = 0;
        $record->variable = 'age_001';
        $record->indent = 0;
        $record->defaultoption = SURVEYPRO_INVITEDEFAULT;
        $record->lowerbound = -2148552000;
        $record->upperbound = 1193918400;
    }

    /**
     * Verify the validity of contents of the record
     * for instance: age not greater than maximum age
     *
     * @param stdClass $record
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
     * @param int $time
     * @param bool $applyusersettings
     * @return void
     */
    public static function item_split_unix_time($time, $applyusersettings=false) {
        $getdate = parent::item_split_unix_time($time, $applyusersettings);

        $getdate['year'] -= SURVEYPROFIELD_AGE_YEAROFFSET;
        if ($getdate['mon'] == 12) {
            $getdate['year']++;
            $getdate['mon'] = 0;
        }

        return $getdate;
    }

    /**
     * Convert an age to unix time.
     *
     * @param int $year
     * @param int $month
     * @return int unixtime
     */
    public function item_age_to_unix_time($year, $month) {
        $year += SURVEYPROFIELD_AGE_YEAROFFSET;
        return (gmmktime(12, 0, 0, $month, 1, $year)); // This is GMT.
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
        $fieldlist = $this->item_get_composite_fields();
        foreach ($fieldlist as $field) {
            if (is_null($this->{$field})) {
                continue;
            }
            $agearray = self::item_split_unix_time($this->{$field});
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
    public function item_custom_fields_to_db($record) {
        // 1. Special management for composite fields.
        $fieldlist = $this->item_get_composite_fields();
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
        // Take care: 'required', 'trimonsave', 'hideinstructions' were already considered in item_get_common_settings.
        // Nothing to do: no checkboxes in this plugin item form.

        // 4. Other.
    }

    /**
     * Starting from an age array returns the corresponding age in text format.
     *
     * @param array $agearray
     * @return void
     */
    public static function item_age_to_text($agearray) {
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
     * Get the list of composite fields.
     *
     * @return void
     */
    public function item_get_composite_fields() {
        return array('defaultvalue', 'lowerbound', 'upperbound');
    }

    /**
     * Make the list of the fields using multilang
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist[$this->plugin] = array('content', 'extranote');

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
    <xs:element name="surveyprofield_age">
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
                <!-- <xs:element name="trimonsave" type="xs:int"/> -->

                <xs:element name="defaultoption" type="xs:int"/>
                <xs:element name="defaultvalue" type="unixtime" minOccurs="0"/>
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
     * Define the mform element for the outform and the searchform.
     *
     * @param moodleform $mform
     * @param bool $searchform
     * @param bool $readonly
     * @return void
     */
    public function userform_mform_element($mform, $searchform, $readonly) {
        $stryears = get_string('years');
        $strmonths = get_string('months', 'surveyprofield_age');
        $strnoanswer = get_string('noanswer', 'mod_surveypro');

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
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
        $elementgroup = array();
        $attributes = array();

        $itemname = $this->itemname.'_year';
        $attributes['id'] = $idprefix.'_year';
        $attributes['class'] = 'indent-'.$this->indent.' age_select';
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $itemname, '', $years, $attributes);

        if ($readonly) {
            $itemname = 'yearlabel_'.$this->itemid;
            $attributes['id'] = $idprefix.'_yearseparator';
            $attributes['class'] = 'inline age_static';
            $elementgroup[] = $mform->createElement('mod_surveypro_label', $itemname, '', $stryears, $attributes);
        }

        $itemname = $this->itemname.'_month';
        $attributes['id'] = $idprefix.'_month';
        $attributes['class'] = 'age_select';
        $elementgroup[] = $mform->createElement('mod_surveypro_select', $itemname, '', $months, $attributes);

        if ($readonly) {
            $itemname = 'monthlabel_'.$this->itemid;
            $attributes['id'] = $idprefix.'_monthseparator';
            $attributes['class'] = 'inline age_static';
            $elementgroup[] = $mform->createElement('mod_surveypro_label', $itemname, '', $strmonths, $attributes);
        }

        if ($this->required) {
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);

            if (!$searchform) {
                // Even if the item is required I CAN NOT ADD ANY RULE HERE because...
                // I do not want JS form validation if the page is submitted through the "previous" button.
                // I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815.
                // Because of this, I simply add a dummy star to the item and the footer note about mandatory fields.
                $starplace = ($this->position == SURVEYPRO_POSITIONTOP) ? $this->itemname.'_extrarow' : $this->itemname.'_group';
                $mform->_required[] = $starplace;
            }
        } else {
            $itemname = $this->itemname.'_noanswer';
            $attributes['id'] = $idprefix.'_noanswer';
            $attributes['class'] = 'age_check';
            $elementgroup[] = $mform->createElement('mod_surveypro_checkbox', $itemname, '', $strnoanswer, $attributes);
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
                        $agearray = self::item_split_unix_time($this->defaultvalue);
                        break;
                    case SURVEYPRO_NOANSWERDEFAULT:
                        $agearray = self::item_split_unix_time($this->lowerbound);
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
        // End of: verify the content of each drop down menu.

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
     * Prepare the string with the filling instruction.
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {
        $maximumage = get_config('surveyprofield_age', 'maximumage');

        $haslowerbound = ($this->lowerbound != $this->item_age_to_unix_time(0, 0));
        $hasupperbound = ($this->upperbound != $this->item_age_to_unix_time($maximumage, 11));

        $a = '';
        $lowerbound = self::item_split_unix_time($this->lowerbound);
        $upperbound = self::item_split_unix_time($this->upperbound);

        $fillinginstruction = '';
        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = self::item_age_to_text($lowerbound);
            $a->upperbound = self::item_age_to_text($upperbound);

            $fillinginstruction .= get_string('restriction_lowerupper', 'surveyprofield_age', $a);
        } else {
            if ($haslowerbound) {
                $a = self::item_age_to_text($lowerbound);
                $fillinginstruction .= get_string('restriction_lower', 'surveyprofield_age', $a);
            }

            if ($hasupperbound) {
                $a = self::item_age_to_text($upperbound);
                $fillinginstruction .= get_string('restriction_upper', 'surveyprofield_age', $a);
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
    public function userform_save_preprocessing($answer, &$olduseranswer, $searchform) {
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
    public function userform_set_prefill($fromdb) {
        $prefill = array();

        if (!$fromdb) { // Param $fromdb may be boolean false for not existing data.
            return $prefill;
        }

        if (isset($fromdb->content)) {
            if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
                $prefill[$this->itemname.'_noanswer'] = 1;
                return $prefill;
            }

            $datearray = self::item_split_unix_time($fromdb->content);
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
        $agearray = self::item_split_unix_time($content);
        $return = self::item_age_to_text($agearray);

        return $return;
    }

    /**
     * Returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup.
     *
     * @return array
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname.'_group');

        return $elementnames;
    }
}
