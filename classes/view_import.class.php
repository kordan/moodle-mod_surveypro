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

/**
 * The base class representing a field
 */
class mod_surveypro_importmanager {
    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $canseeownsubmissions
     */
    // public $canseeownsubmissions = true;

    /**
     * $canseeotherssubmissions
     */
    public $canseeotherssubmissions = false;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        $this->canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
    }

    /**
     * trigger_event
     *
     * @param none
     * @return none
     */
    public function trigger_event() {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_exported::create($eventdata);
        $event->trigger();
    }

    /**
     * welcome_message
     *
     * @param none
     * @return null
     */
    public function welcome_message() {
        global $OUTPUT, $CFG;

        $semanticitem = array();
        $plugins = surveypro_get_plugin_list(SURVEYPRO_TYPEFIELD);
        foreach ($plugins as $k => $plugin) {
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$plugin.'/plugin.class.php');

            $itemclass = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $item = new $itemclass($this->cm, null, false);
            if ($item->get_savepositiontodb()) {
                $semanticitem[] = $plugins[$k];
            }
        }

        $a = new stdClass();
        $a->customsemantic = get_string('itemdrivensemantic', 'surveypro', get_string('downloadformat', 'surveypro'));
        $a->items = '<li>'.implode(';</li><li>', $semanticitem).'.</li>';
        $message = get_string('welcomeimport', 'surveypro', $a);
        echo $OUTPUT->notification($message, 'notice');
    }

    /**
     * get_csv_content
     *
     * @param none
     * @return csv content
     */
    public function get_csv_content() {
        $importform = new mod_surveypro_importform();

        return $importform->get_file_content('csvfile_filepicker');
    }

    /**
     * get_uniqueness_columns
     *
     * @param $foundheaders
     * @return false or the duplicate header
     */
    public function verify_header_duplication($foundheaders) {
        $processed = array();
        foreach ($foundheaders as $foundheader) {
            if (in_array($foundheader, $processed)) {
                return $foundheader;
            }
            $processed[] = $foundheader;
        }
        unset($processed);

        return false;
    }

    /**
     * get_survey_infos
     *
     * @param none
     * @return $surveyheaders and $requireditems
     */
    public function get_survey_infos() {
        global $DB, $CFG;

        $sql = 'SELECT id, plugin
            FROM {surveypro_item}
            WHERE surveyproid = :surveyproid
                AND type = :type
            GROUP BY plugin';
        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
        $pluginlist = $DB->get_records_sql_menu($sql, $whereparams);

        $requireditems = array();
        $surveyheaders = array();
        $where = array('surveyproid' => $this->surveypro->id);
        foreach ($pluginlist as $plugin) {
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$plugin.'/plugin.class.php');

            // $tablename === $itemclass :)
            // $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $itemclass = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $item = new $itemclass($this->cm, null, false);
            $requiredfieldname = $itemclass::get_requiredfieldname();

            $sql = 'SELECT p.itemid, p.variable, p.'.$requiredfieldname.'
                FROM {surveypro_item} i
                    JOIN {'.$itemclass.'} p ON i.id = p.itemid
                WHERE i.surveyproid = :surveyproid
                ORDER BY p.itemid';
            $itemvariables = $DB->get_records_sql($sql, $where);

            foreach ($itemvariables as $itemvariable) {
                $surveyheaders[$itemvariable->itemid] = $itemvariable->variable;
                if ($itemvariable->{$requiredfieldname} > 0) {
                    $requireditems[] = $itemvariable->itemid;
                }
            }
        }

        return array($surveyheaders, $requireditems);
    }

    /**
     * verify_required
     *
     * @param $requireditems
     * @param $columntoitemid
     * @param $surveyheaders
     * @return false or the missing required header
     */
    public function verify_required($requireditems, $columntoitemid, $surveyheaders) {
        foreach ($requireditems as $requireditemid) {
            $col = in_array($requireditemid, $columntoitemid);
            if ($col === false) {
                return $surveyheaders[$requireditemid];
            }
        }

        return false;
    }

    /**
     * verify_attachments_import
     *
     * @param $foundheaders
     * @return [array extraheadres|bool false]
     */
    public function verify_attachments_import($foundheaders) {
        global $DB;

        // first step: make the list of each variablename of fileupload items of this surveypro
        $where = array('surveyproid' => $this->surveypro->id);
        $sql = 'SELECT p.itemid, p.variable
            FROM {surveypro_item} i
                JOIN {surveyprofield_fileupload} p ON i.id = p.itemid
            WHERE i.surveyproid = :surveyproid
            ORDER BY p.itemid';
        $variablenames = $DB->get_records_sql_menu($sql, $where);
        if (!count($variablenames)) {
            return false;
        }

        $foundheaders = array();
        foreach ($foundheaders as $foundheader) {
            $key = in_array($foundheader, $variablenames);
            if ($key !== false) {
                $foundheaders[] = $foundheader;
            }
        }

        if (count($foundheaders)) {
            return $foundheaders;
        } else {
            return false;
        }
    }

    /**
     * get_columntoitemid
     *
     * This method returns the correspondence between the column where the datum is found
     * and the id of the surveypro item where the datum has to go
     *
     * $foundheaders
     * $surveyheaders
     *
     * @param $foundheaders
     * @param $surveyheaders
     * @return array $columntoitemid
     * @return array $nonmatchingheaders
     * @return int   $useridcolumnkey
     */
    public function get_columntoitemid($foundheaders, $surveyheaders) {
        $columntoitemid = array();

        $useridcolumnkey = -1;
        $nonmatchingheaders = array();
        foreach ($foundheaders as $k => $foundheader) {
            $key = array_search($foundheader, $surveyheaders);
            if ($key !== false) {
                if ($key == 'userid') {
                    $useridcolumnkey = $k;
                } else {
                    $columntoitemid[$k] = $key;
                }
            } else {
                $nonmatchingheaders[] = $foundheader;
            }
        }

        return array($columntoitemid, $nonmatchingheaders, $useridcolumnkey);
    }

    /**
     * get_items_helperinfo
     *
     * @param $columntoitemid
     * @param $useridcolumnkey
     * @return $itemhelperinfo (one $itemhelperinfo per each item)
     * @return $itemoptions (one $itemoptions only each items with $info->savepositiontodb = 1)
     */
    public function get_items_helperinfo($columntoitemid, $useridcolumnkey) {
        $itemhelperinfo = array(); // one element per each item
        $itemoptions = array(); // one element only for items saving position to db
        foreach ($columntoitemid as $col => $itemid) {
            if ($col == $useridcolumnkey) {
                // the column for userid
                continue;
            }
            $item = surveypro_get_item($itemid);

            // itemhelperinfo
            $info = new stdClass();
            $info->required = $item->get_required();
            $info->savepositiontodb = $item->get_savepositiontodb();
            $info->contentformat = $item->get_contentformat();
            if (($this->formdata->csvsemantic == 'itemdriven') && ($info->savepositiontodb)) {
                $info->contentformat = $item->get_downloadformat();
            }
            $itemhelperinfo[$col] = $info;

            // itemoption
            if ($info->savepositiontodb) {
                if ($this->formdata->csvsemantic == SURVEYPRO_LABELS) {
                    $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
                    continue;
                }
                if ($this->formdata->csvsemantic == SURVEYPRO_VALUES) {
                    $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_VALUES, 'options');
                    continue;
                }
                if ($this->formdata->csvsemantic == 'itemdriven') {
                    if ($this->get_downloadformat() == SURVEYPRO_ITEMRETURNSLABELS) {
                        $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
                    } else { // SURVEYPRO_ITEMSRETURNSVALUES
                        $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_VALUES, 'options');
                    }
                    continue;
                }
            }
        }

        return array($itemhelperinfo, $itemoptions);
    }

    /**
     * validate_csv
     *
     * @param none
     * @return
     */
    public function import_csv() {
        global $USER, $DB;

        $debug = false;

        // start with the validation
        // define the url to redirect in case of validation failure
        $returnurl = new moodle_url('/mod/surveypro/view_import.php', array('s' => $this->surveypro->id));

        $iid = csv_import_reader::get_new_iid('surveyprouserdata');
        $cir = new csv_import_reader($iid, 'surveyprouserdata');

        $csvcontent = $this->get_csv_content();
        $recordcount = $cir->load_csv_content($csvcontent, $this->formdata->encoding, $this->formdata->csvdelimiter);
        unset($csvcontent);

        // is the number of field of each row homogeneous with all the others?
        $csvfileerror = $cir->get_error();
        if (!is_null($csvfileerror)) { // error
            $cir->close();
            $cir->cleanup();
            print_error('import_columnscountchanges', 'surveypro', $returnurl, $csvfileerror);
        }

        // is each column unique?
        $foundheaders = $cir->get_columns();
        if ($debug) {
            echo '$foundheaders:';
            var_dump($foundheaders);
        }
        if ($duplicateheader = $this->verify_header_duplication($foundheaders)) { // error
            $cir->close();
            $cir->cleanup();
            print_error('import_duplicateheader', 'surveypro', $returnurl, $duplicateheader);
        }
        // is the user trying to import an attachment?
        if ($attachments = $this->verify_attachments_import($foundheaders)) { // error
            $cir->close();
            $cir->cleanup();
            $a = '<li>'.implode(';</li><li>', $attachments).'.</li>';
            print_error('import_attachmentsnotallowed', 'surveypro', $returnurl, $a);
        }

        // make a list of the header of each item in the survey
        // and the list of the id of the required items
        list($surveyheaders, $requireditems) = $this->get_survey_infos();
        $surveyheaders = array('userid' => 'userid') + $surveyheaders;
        if ($debug) {
            echo '$surveyheaders:';
            var_dump($surveyheaders);
            echo '$requireditems:';
            var_dump($requireditems);
        }

        // *******************************************************
        // Rationale: teacher is importing.
        // Each datum is gold. Because of this, I DECIDED that:
        // even if a required field is not present
        //     I will anyway import the record
        //     I will even import the data for items placed in pages greater than the page of the required missing item
        //
        // TO MAKE THIS CLEAR ONCE AND FOR EVER
        //     teacher IS NOT allowed to enter a invalid content
        //     but IS allowed to partially import records even jumping mandatory values
        // *******************************************************

        // make a relation between each column header and the corresponding itemid
        // was userid added? if userid was added ? $useridcolumnkey holds its column key : $useridcolumnkey = null
        list($columntoitemid, $nonmatchingheaders, $useridcolumnkey) = $this->get_columntoitemid($foundheaders, $surveyheaders);
        if ($debug) {
            echo '$columntoitemid:';
            var_dump($columntoitemid);
            // array (size=5)
            //   0 => int 672
            //   1 => int 674
            //   2 => int 676
            //   3 => int 673
            //   4 => int 675
            echo '$useridcolumnkey: ';
            var_dump($useridcolumnkey);
        }
        // if ($useridcolumnkey == -1) {
        //     // userid column in the csv is not mandatory
        //     // if it is not present, imported surveys vill be given to admin
        // }
        if (count($nonmatchingheaders)) {
            $cir->close();
            $cir->cleanup();
            $a = '<li>'.implode(';</li><li>', $nonmatchingheaders).'.</li>';
            print_error('import_extraheaderfound', 'surveypro', $returnurl, $a);
        }

        // is each required item included into the csv?
        if ($this->verify_required($requireditems, $columntoitemid, $surveyheaders)) {
            $defaultstatus = SURVEYPRO_STATUSINPROGRESS;
        } else {
            // this status is still subject to further changes
            // during csv, line by line, scan
            $defaultstatus = SURVEYPRO_STATUSCLOSED;
        }

        // to save time during all validations to carry out, save to $itemhelperinfo some information
        // in this way I no longer will need to load item hundreds times
        // get now, once and for ever, each item helperinfo. For items saving position,
        list($itemhelperinfo, $itemoptions) = $this->get_items_helperinfo($columntoitemid, $useridcolumnkey);
        // end of: get now, once and for ever, each item option (where applicable)

        if ($debug) {
            echo '$itemhelperinfo:';
            var_dump($itemhelperinfo);
            echo '$itemoptions:';
            var_dump($itemoptions);
        }

        // Does EACH RECORD provide a valid value (per each item)?
        $csvusers = array();
        $cir->init();
        while ($csvrow = $cir->next()) {
            foreach ($foundheaders as $col => $unused) {
                $value = $csvrow[$col]; // the value reported in the csv file

                if ($col == $useridcolumnkey) {
                    // the column for userid
                    if (empty($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_missinguserid', 'surveypro', $returnurl);
                    } else {
                        if (!(int)$value) {
                            $cir->close();
                            $cir->cleanup();
                            print_error('import_invaliduserid', 'surveypro', $returnurl, $value);
                        }
                        if ($value != $USER->id) {
                            if (!isset($csvusers[$value])) {
                                $csvusers[$value] = 1;
                            } else {
                                $csvusers[$value]++;
                            }
                        }
                    }
                    continue;
                }

                $info = $itemhelperinfo[$col]; // the itemhelperinfo of the item in col = $col
                if ($info->required) {
                    // verify it is not empty
                    if (empty($value)) { // error
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_emptyrequiredvalue', 'surveypro', $returnurl, $requireditem);
                    }
                }

                if ($info->savepositiontodb) {
                    // verify it is valid
                    $options = $itemoptions[$col];
                    if ($debug) {
                        echo '$options:';
                        var_dump($options);
                    }
                    $key = array_search($value, $options);
                    if ($key === false) { // error
                        $cir->close();
                        $cir->cleanup();

                        $a = new stdClass();
                        $a->csvrow = implode(', ', $csvrow);
                        $a->csvcol = $col;
                        $a->csvvalue = $value;
                        $a->header = $foundheaders[$col];
                        switch ($this->formdata->csvsemantic) {
                            case SURVEYPRO_LABELS:
                                $a->semantic = get_string('answerlabel', 'surveypro');
                                break;
                            case SURVEYPRO_VALUES:
                                $a->semantic = get_string('answervalue', 'surveypro');
                                break;
                            case SURVEYPRO_POSITIONS:
                                $a->semantic = get_string('answerposition', 'surveypro');
                                break;
                            case 'itemdriven':
                                $a->semantic = $itemhelperinfo[$col]->contentformat;
                                break;
                            default:
                                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->formdata->csvsemantic = '.$this->formdata->csvsemantic, DEBUG_DEVELOPER);
                        }
                        print_error('import_missingsemantic', 'surveypro', $returnurl, $a);
                    }
                }
            }
        }
        // end of: Do they ALL and IN EACH RECORD provide a valid value?
        unset($foundheaders);

        // Am I going to break maxentries limit (for each user)?
        if ($this->surveypro->maxentries) {
            if (count($csvusers)) {
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $sql = 'SELECT userid, COUNT(\'x\')
                    FROM {surveypro_submission}
                    WHERE surveyproid = :surveyproid
                        AND userid IN ('.implode(',', array_keys($csvusers)).')
                    GROUP BY userid';
                $oldsubmissionsperuser = $DB->get_records_sql_menu($sql, $whereparams);

                foreach ($oldsubmissionsperuser as $csvuserid => $oldsubmissions) {
                    $totalsubmissions = $oldsubmissions + $csvusers[$csvuserid];
                    if ($totalsubmissions > $this->surveypro->maxentries) { // error
                        $a = new stdClass();
                        $a->userid = $csvuserid;
                        $a->maxentries = $this->surveypro->maxentries;
                        $a->totalentries = $totalsubmissions;
                        print_error('import_breakingmaxentries', 'surveypro', $returnurl, $a);
                    }
                }
            }
        }

        // if you did not die, each validation passed.
        // Continue with the import
        // I am safe to import each value without arguing about its validity
        $timenow = time();

        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I start the import<br />';
        }

        // ********************************
        //   F I N A L L Y   I M P O R T
        // ********************************
        // init csv import helper
        $gooduserids = array();
        $baduserids = array();

        $cir->init();
        while ($csvrow = $cir->next()) {
            if ($debug) {
                echo '$csvrow = '.implode(', ', $csvrow).'<br />';
            }
            // add one record to surveypro_submission
            $record = new stdClass();
            $record->surveyproid = $this->surveypro->id;
            if (($useridcolumnkey !== null) && !empty($csvrow[$useridcolumnkey])) {
                $userid = $csvrow[$useridcolumnkey];
                // try to save querys
                if (in_array($userid, $gooduserids)) {
                    $record->userid = $userid;
                }
                if (in_array($userid, $baduserids)) {
                    $record->userid = $USER->id;
                }
                if (!isset($record->userid)) {
                    if ($DB->record_exists('user', array('id' => $userid))) {
                        $gooduserids[] = $userid;
                        $record->userid = $userid;
                    } else {
                        $baduserids[] = $userid;
                        $record->userid = $USER->id;
                    }
                }
            } else {
                $record->userid = $USER->id;
            }
            $record->status = $defaultstatus;
            $record->timecreated = $timenow;
            // $record->timemodified = never
            if ($debug) {
                echo 'I am going to save to surveypro_submission:<br />';
                echo '$record:';
                var_dump($record);
            }
            $submissionid = $DB->insert_record('surveypro_submission', $record);

            $status = $defaultstatus;
            foreach ($csvrow as $col => $value) {
                if ($col == $useridcolumnkey) {
                    // the column for userid
                    continue;
                }
                if ($debug) {
                    echo '$col = '.$col.'<br />';
                    echo '$value = '.$value.'<br />';
                }
                // Take care. Even if each required header is present I need to check that the current content for each item is not empty!
                if (!strlen($value)) {
                    if ($debug) {
                        echo 'value returned by csv file is empty<br />';
                    }
                    if (in_array($col, $requireditems)) {
                        // I found a raw where the value for a required item IS EMPTY.
                        $status = SURVEYPRO_STATUSINPROGRESS;
                    }
                    continue;
                }

                // is the current item saving position to db?
                if ($itemhelperinfo[$col]->savepositiontodb) {
                    if ($debug) {
                        echo 'Item saves position<br />';
                    }
                    $options = $itemoptions[$col];
                    if ($debug) {
                        echo '$options:';
                        var_dump($options);
                    }
                    $content = array_search($value, $options);
                } else {
                    if ($debug) {
                        echo 'Item saves raw data<br />';
                    }
                    // merely save the content
                    $content = $value;
                }

                // finally, save
                $record = new stdClass();
                $record->submissionid = $submissionid;
                $record->itemid = $columntoitemid[$col];
                $record->content = $content;
                $record->contentformat = $itemhelperinfo[$col]->contentformat;
                if ($debug) {
                    echo 'I am going to save to surveypro_answer:<br />';
                    echo '$record:';
                    var_dump($record);
                }
                $DB->insert_record('surveypro_answer', $record);
            }

            if ($status != $defaultstatus) {
                $record = $DB->get_record('surveypro_submission', array('id' => $submissionid));
                $record->status = $status;
                $DB->insert_record('surveypro_submission', $record);
            }
        }

        $course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
        $completion = new completion_info($course);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_INCOMPLETE);
        }

        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\submissions_imported::create($eventdata);
        $event->trigger();
    }
}