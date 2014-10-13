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
require_once($CFG->dirroot.'/mod/surveypro/field/numeric/lib.php');

class surveyprofield_numeric extends mod_surveypro_itembase {

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
     * $defaultvalue = the value of the field when the form is initially displayed.
     */
    public $defaultvalue = '';

    /**
     * $decimalseparator
     */
    public $decimalseparator = '.';

    /**
     * $signed = will be, the expected number, signed
     */
    public $signed = 0;

    /**
     * $lowerbound = the minimun allowed value
     */
    public $lowerbound = '';

    /**
     * $upperbound = the maximum allowed value
     */
    public $upperbound = '';

    /**
     * $decimals = number of decimals allowed for this number
     */
    public $decimals = 0;

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
    public function __construct($itemid=0, $evaluateparentcontent) {
        global $PAGE;

        $cm = $PAGE->cm;

        if (isset($cm)) { // it is not set during upgrade whether this item is loaded
            $this->context = context_module::instance($cm->id);
        }

        $this->type = SURVEYPRO_TYPEFIELD;
        $this->plugin = 'numeric';
        $this->decimalseparator = get_string('decsep', 'langconfig');

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
        // Do parent item loading stuff here (mod_surveypro_itembase::item_load($itemid)))
        parent::item_load($itemid, $evaluateparentcontent);

        // float numbers need more attention because I can write them using , or .
        if (strlen($this->defaultvalue)) {
            $this->defaultvalue = format_float($this->defaultvalue, $this->decimals);
        }
        if (strlen($this->lowerbound)) {
            $this->lowerbound = format_float($this->lowerbound, $this->decimals);
        }
        if (strlen($this->upperbound)) {
            $this->upperbound = format_float($this->upperbound, $this->decimals);
        }

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

        $record->signed = isset($record->signed) ? 1 : 0;

        // float numbers need more attention because I can write them using , or .
        if (strlen($record->defaultvalue)) {
            $record->defaultvalue = unformat_float($record->defaultvalue, true);
        } else {
            unset($record->defaultvalue);
        }
        if (strlen($record->lowerbound)) {
            $record->lowerbound = unformat_float($record->lowerbound, true);
        } else {
            unset($record->lowerbound);
        }
        if (strlen($record->upperbound)) {
            $record->upperbound = unformat_float($record->upperbound, true);
        } else {
            unset($record->upperbound);
        }
        // end of: plugin specific settings (eventally overriding general ones)

        // Do parent item saving stuff here (mod_surveypro_itembase::item_save($record)))
        return parent::item_save($record);
    }

    /**
     * item_custom_fields_to_form
     * add checkboxes selection for empty fields
     *
     * @param none
     * @return
     */
    public function item_custom_fields_to_form() {
        // 1. special management for fields equipped with "free" checkbox
        // nothing to do: they don't exist in this plugin

        // 2. special management for composite fields
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
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
        // nothing to do: they don't exist in this plugin

        // 3. special management for defaultvalue
        // nothing to do: defaultvalue doesn't need any further care
        if ($record->defaultvalue === '') {
            $record->defaultvalue = null;
        }
    }

    /**
     * item_atomize_number
     * starting from justanumber, this function returns it splitted into an array
     *
     * @param $justanumber
     * @return
     */
    public function item_atomize_number($justanumber) {
        $pattern = '~^\s*(-?)([0-9]+)'.get_string('decsep', 'langconfig').'?([0-9]*)\s*$~';
        preg_match($pattern, $justanumber, $matches);

        return $matches;
    }

    /**
     * item_get_generic_property
     *
     * @param $field
     * @return the content of the field
     */
    public function item_get_generic_property($field) {
        $doublefields = array('lowerbound', 'upperbound', 'defaultvalue');
        if (in_array($field, $doublefields)) {
            $value = parent::item_get_generic_property($field);
            return unformat_float($value, true);
        } else {
            return parent::item_get_generic_property($field);
        }
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
    <xs:element name="surveyprofield_numeric">
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

                <xs:element type="xs:decimal" name="defaultvalue" minOccurs="0"/>
                <xs:element type="xs:int" name="signed"/>
                <xs:element type="xs:decimal" name="lowerbound" minOccurs="0"/>
                <xs:element type="xs:decimal" name="upperbound" minOccurs="0"/>
                <xs:element type="xs:int" name="decimals"/>
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
     * @return
     */
    public function userform_mform_element($mform, $searchform, $readonly=false, $submissionid=0) {
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $elementnumber = $this->customnumber ? $this->customnumber.$labelsep : '';
        $elementlabel = ($this->position == SURVEYPRO_POSITIONLEFT) ? $elementnumber.strip_tags($this->get_content()) : '&nbsp;';

        if (!$searchform) {
            $mform->addElement('text', $this->itemname, $elementlabel, array('class' => 'indent-'.$this->indent, 'itemid' => $this->itemid));
            $mform->setType($this->itemname, PARAM_RAW); // see: moodlelib.php lines 133+
            if (strlen($this->defaultvalue)) {
                $mform->setDefault($this->itemname, "$this->defaultvalue");
            }

            if ($this->required) {
                // even if the item is required I CAN NOT ADD ANY RULE HERE because:
                // -> I do not want JS form validation if the page is submitted through the "previous" button
                // -> I do not want JS field validation even if this item is required BUT disabled. See: MDL-34815
                // simply add a dummy star to the item and the footer note about mandatory fields
                $starplace = ($this->position != SURVEYPRO_POSITIONLEFT) ? $this->itemname.'_extrarow' : $this->itemname;
                $mform->_required[] = $starplace;
            }
        } else {
            $elementgroup = array();
            $elementgroup[] = $mform->createElement('text', $this->itemname, '', array('class' => 'indent-'.$this->indent));
            $elementgroup[] = $mform->createElement('checkbox', $this->itemname.'_ignoreme', '', get_string('star', 'surveypro'));
            $mform->setType($this->itemname, PARAM_RAW);
            $mform->addGroup($elementgroup, $this->itemname.'_group', $elementlabel, ' ', false);
            $mform->disabledIf($this->itemname.'_group', $this->itemname.'_ignoreme', 'checked');
            $mform->setDefault($this->itemname.'_ignoreme', '1');
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
        if ($searchform) {
            return;
        }

        $errorkey = $this->itemname;

        $draftuserinput = $data[$this->itemname];
        if ($this->required) {
            if (strlen($draftuserinput) == 0) {
                $errors[$errorkey] = get_string('required');
                return;
            }
        }

        if (empty($draftuserinput)) {
            return;
        }

        // if it is not a number, shouts
        if (strlen($draftuserinput)) {
            $matches = $this->item_atomize_number($draftuserinput);
            if (empty($matches)) {
                $errors[$errorkey] = get_string('uerr_notanumber', 'surveyprofield_numeric');
                return;
            } else {
                $userinput = unformat_float($draftuserinput, true);
                // if it is < 0 but has been defined as unsigned, shouts
                if (!$this->signed && ($userinput < 0)) {
                    $errors[$errorkey] = get_string('uerr_negative', 'surveyprofield_numeric');
                }
                // if it has decimal but has been defined as integer, shouts
                $isinteger = (bool)(strval(intval($userinput)) == strval($userinput));
                if (($this->decimals == 0) && (!$isinteger)) {
                    $errors[$errorkey] = get_string('uerr_notinteger', 'surveyprofield_numeric');
                }
            }
        }

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));

        if ($haslowerbound && $hasupperbound) {
            if ($this->lowerbound < $this->upperbound) {
                // internal range
                if ( ($userinput < $this->lowerbound) || ($userinput > $this->upperbound) ) {
                    $errors[$errorkey] = get_string('uerr_outofinternalrange', 'surveyprofield_numeric');
                }
            }

            if ($this->lowerbound > $this->upperbound) {
                // external range
                if (($userinput > $this->lowerbound) && ($userinput < $this->upperbound)) {
                    $format = get_string($this->item_get_friendlyformat(), 'surveyprofield_numeric');
                    $a = new stdClass();
                    $a->lowerbound = $this->lowerbound;
                    $a->upperbound = $this->upperbound;
                    $errors[$errorkey] = get_string('uerr_outofexternalrange', 'surveyprofield_numeric', $a);
                }
            }
        } else {
            if ($haslowerbound && ($userinput < $this->lowerbound)) {
                $errors[$errorkey] = get_string('uerr_lowerthanminimum', 'surveyprofield_numeric');
            }
            if ($hasupperbound && ($userinput > $this->upperbound)) {
                $errors[$errorkey] = get_string('uerr_greaterthanmaximum', 'surveyprofield_numeric');
            }
        }

    }

    /**
     * userform_get_filling_instructions
     *
     * @return string $fillinginstruction
     */
    public function userform_get_filling_instructions() {

        $haslowerbound = (strlen($this->lowerbound));
        $hasupperbound = (strlen($this->upperbound));
        $fillinginstruction = array();

        if (!empty($this->signed)) {
            $fillinginstruction[] = get_string('restriction_hassign', 'surveyprofield_numeric');
        }

        if ($haslowerbound && $hasupperbound) {
            $a = new stdClass();
            $a->lowerbound = $this->lowerbound;
            $a->upperbound = $this->upperbound;

            if ($this->lowerbound < $this->upperbound) {
                $fillinginstruction[] = get_string('restriction_lowerupper', 'surveyprofield_numeric', $a);
            }

            if ($this->lowerbound > $this->upperbound) {
                $fillinginstruction[] = get_string('restriction_upperlower', 'surveyprofield_numeric', $a);
            }
        } else {
            if ($haslowerbound) {
                $a = $this->lowerbound;
                $fillinginstruction[] = get_string('restriction_lower', 'surveyprofield_numeric', $a);
            }

            if ($hasupperbound) {
                $a = $this->upperbound;
                $fillinginstruction[] = get_string('restriction_upper', 'surveyprofield_numeric', $a);
            }
        }

        if (!empty($this->decimals)) {
            $a = $this->decimals;
            $fillinginstruction[] = get_string('restriction_hasdecimals', 'surveyprofield_numeric', $a);
            $fillinginstruction[] = get_string('decimalautofix', 'surveyprofield_numeric');
            // this sentence dials about decimal separator not about the expected value
            // so I leave it as last sentence
            $fillinginstruction[] = get_string('declaredecimalseparator', 'surveyprofield_numeric', $this->decimalseparator);
        } else {
            $fillinginstruction[] = get_string('restriction_isinteger', 'surveyprofield_numeric');
        }

        if (count($fillinginstruction)) {
            $fillinginstruction = get_string('number', 'surveyprofield_numeric').implode('; ', $fillinginstruction);
        } else {
            $fillinginstruction = '';
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
        if (isset($answer['ignoreme'])) {
            $olduserdata->content = null;
            return;
        }

        if (strlen($answer['mainelement']) == 0) {
            $olduserdata->content = SURVEYPRO_NOANSWERVALUE;
        } else {
            if (empty($this->decimals)) {
                $olduserdata->content = $answer['mainelement'];
            } else {
                $matches = $this->item_atomize_number($answer['mainelement']);
                $decimals = isset($matches[3]) ? $matches[3] : '';
                if (strlen($decimals) > $this->decimals) {
                    // round it
                    $decimals = round((float)$decimals, $this->decimals);
                }
                if (strlen($decimals) < $this->decimals) {
                    // padright
                    $decimals = str_pad($decimals, $this->decimals, '0', STR_PAD_RIGHT);
                }
                if (isset($matches[2])) {
                    // I DO ALWATYS save using english decimal separator
                    // At load time, the number will be formatted according to user settings
                    $olduserdata->content = $matches[2].'.'.$decimals;
                    if ($matches[1] == '-') {
                        $olduserdata->content *= -1;
                    }
                } else {
                    // in the SEARCH form the remote user entered something very wrong
                    // remember: the for search form NO VALIDATION IS PERFORMED
                    // user is free to waste his/her time as he/she like
                    $olduserdata->content = $answer['mainelement'];
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

        if ($fromdb->content == SURVEYPRO_NOANSWERVALUE) {
            $prefill[$this->itemname] = '';
        } else {
            $prefill[$this->itemname] = number_format((double)$fromdb->content, $this->decimals, $this->decimalseparator, '');
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
        $content = trim($answer->content);

        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'surveypro');
        }
        if (strlen($content) == 0) { // item was disabled
            return get_string('notanswereditem', 'surveypro');
        }

        return $content;
    }

    /**
     * userform_get_root_elements_name
     * returns an array with the names of the mform element added using $mform->addElement or $mform->addGroup
     *
     * @param none
     * @return
     */
    public function userform_get_root_elements_name() {
        $elementnames = array($this->itemname);

        return $elementnames;
    }
}
