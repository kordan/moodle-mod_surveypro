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
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$plugin.'/classes/plugin.class.php');

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
        echo $OUTPUT->notification($message, 'notifymessage');
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

        $sql = 'SELECT MIN(id), plugin
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
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$plugin.'/classes/plugin.class.php');

            // $tablename === $itemclass :)
            // $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $itemclass = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            // $item = new $itemclass($this->cm, null, false);
            $requiredfieldname = $itemclass::get_requiredfieldname();

            $sql = 'SELECT p.itemid, p.variable, p.'.$requiredfieldname.'
                FROM {surveypro_item} i
                    JOIN {surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'} p ON i.id = p.itemid
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
     * @return array $environmentheaders
     */
    public function get_columntoitemid($foundheaders, $surveyheaders) {
        $columntoitemid = array();

        $nonmatchingheaders = array();
        $environmentheaders = array();

        foreach ($foundheaders as $k => $foundheader) {
            $key = array_search($foundheader, $surveyheaders);
            if ($key !== false) {
                if ($key == 'userid') {
                    $environmentheaders['userid'] = $k;
                }
                if ($key == 'timecreated') {
                    $environmentheaders['timecreated'] = $k;
                }
                if ($key == 'timemodified') {
                    $environmentheaders['timemodified'] = $k;
                }
                $columntoitemid[$k] = $key;
            } else {
                $nonmatchingheaders[] = $foundheader;
            }
        }

        return array($columntoitemid, $nonmatchingheaders, $environmentheaders);
    }

    /**
     * get_items_helperinfo
     *
     * @param $columntoitemid
     * @param $environmentheaders
     * @return $itemhelperinfo (one $itemhelperinfo per each item)
     * @return $itemoptions (one $itemoptions only each items with $info->savepositiontodb = 1)
     */
    public function get_items_helperinfo($columntoitemid, $environmentheaders) {
        $itemhelperinfo = array(); // one element per each item
        $itemoptions = array(); // one element only for items saving position to db
        foreach ($columntoitemid as $col => $itemid) {
            if (isset($environmentheaders['userid']) && ($col == $environmentheaders['userid'])) {
                // the column for userid
                continue;
            }
            if (isset($environmentheaders['timecreated']) && ($col == $environmentheaders['timecreated'])) {
                // the column for timecreated
                continue;
            }
            if (isset($environmentheaders['timemodified']) && ($col == $environmentheaders['timemodified'])) {
                // the column for timemodified
                continue;
            }
            $item = surveypro_get_item($itemid);

            // itemhelperinfo
            $info = new stdClass();
            $info->required = $item->get_required();
            $info->savepositiontodb = $item->get_savepositiontodb();
            if (($this->formdata->csvsemantic == SURVEYPRO_ITEMDRIVEN) && ($info->savepositiontodb)) {
                $info->contentformat = $item->get_downloadformat();
            } else {
                $info->contentformat = $item->get_contentformat();
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
                if ($this->formdata->csvsemantic == SURVEYPRO_ITEMDRIVEN) {
                    $itemdownloadformat = $item->get_downloadformat();
                    switch ($itemdownloadformat) {
                        case SURVEYPRO_ITEMRETURNSLABELS:
                            $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_LABELS, 'options');
                            break;
                        case SURVEYPRO_ITEMSRETURNSVALUES:
                            $itemoptions[$col] = $item->item_get_content_array(SURVEYPRO_VALUES, 'options');
                            break;
                        case SURVEYPRO_ITEMRETURNSPOSITION:
                            // do not waste your memory calculating $itemoptions.
                            // $itemoptions is useless as the position is already available in the csv
                            // the count of the options is enough
                            $itemoptions[$col] = count($item->item_get_content_array(SURVEYPRO_LABELS, 'options'));
                            break;
                        default:
                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->formdata->csvsemantic = '.$this->formdata->csvsemantic, DEBUG_DEVELOPER);
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
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
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
        $surveyheaders = array('userid' => 'userid', 'timecreated' => 'timecreated', 'timemodified' => 'timemodified') + $surveyheaders;
        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
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
        //     I will even import the content for the items placed in pages greater than the page of a missing required item
        //
        // TO MAKE THIS CLEAR ONCE AND FOR EVER
        //     teacher IS NOT allowed to enter a invalid content
        //     but IS allowed to partially import records even jumping mandatory values
        // *******************************************************

        // make a relation between each column header and the corresponding itemid
        list($columntoitemid, $nonmatchingheaders, $environmentheaders) = $this->get_columntoitemid($foundheaders, $surveyheaders);
        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$columntoitemid:';
            var_dump($columntoitemid);
            // array (size=5)
            //   0 => int 672
            //   1 => int 674
            //   2 => int 676
            //   3 => int 673
            //   4 => int 675
            echo '$environmentheaders: ';
            var_dump($environmentheaders);
        }
        // userid column in the csv is not mandatory
        // if it is not present, imported surveys vill be given to $USER
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
        // get now, once and for ever, each item helperinfo.
        list($itemhelperinfo, $itemoptions) = $this->get_items_helperinfo($columntoitemid, $environmentheaders);
        // end of: get now, once and for ever, each item option (where applicable)

        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$itemhelperinfo:';
            var_dump($itemhelperinfo);
            echo '$itemoptions:';
            var_dump($itemoptions);
        }

        // DOES EACH RECORD provide a valid value?
        $reservedwords = array();
        $reservedwords[] = SURVEYPRO_INVITATIONVALUE;
        $reservedwords[] = SURVEYPRO_NOANSWERVALUE;
        $reservedwords[] = SURVEYPRO_IGNOREMEVALUE;
        $reservedwords[] = SURVEYPRO_ANSWERNOTINDBVALUE;

        $csvusers = array();
        $cir->init();
        while ($csvrow = $cir->next()) {
            foreach ($foundheaders as $col => $unused) {
                $value = $csvrow[$col]; // the value reported in the csv file

                if (isset($environmentheaders['userid']) && ($col == $environmentheaders['userid'])) {
                    // the column for userid
                    if (empty($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_missinguserid', 'surveypro', $returnurl);
                    } else {
                        if (!is_number($value)) {
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

                if (isset($environmentheaders['timecreated']) && ($col == $environmentheaders['timecreated'])) {
                    // the column for timecreated
                    if (empty($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_missingtimecreated', 'surveypro', $returnurl);
                    }
                    if (!is_number($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_invalidtimecreated', 'surveypro', $returnurl, $value);
                    }
                    continue;
                }

                if (isset($environmentheaders['timemodified']) && ($col == $environmentheaders['timemodified'])) {
                    // the column for timemodified
                    if (empty($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_missingtimemodified', 'surveypro', $returnurl);
                    }
                    if (!is_number($value)) {
                        $cir->close();
                        $cir->cleanup();
                        print_error('import_invalidtimemodified', 'surveypro', $returnurl, $value);
                    }
                    continue;
                }

                $info = $itemhelperinfo[$col]; // the itemhelperinfo of the item in col = $col
                // I import records with missing mandatory content too
                // but if the content is provided, then it has to be present
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
                        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                        echo '----> Validation of the content reported for the column number: '.$col.'<br />';
                        echo '----> Surveypro_item $options:';
                        var_dump($options);
                        echo 'I have $value = '.$value.'<br />';
                    }

                    if (!is_array($options)) {
                        // $option is not an array. It is a number. (see "public function get_items_helperinfo" for the reason.)
                        // This means that the content reported in the csv for this column is supposed to be ALREADY a position
                        // verify the content is REALLY a VALID position
                        $positions = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $value);
                        $positionscount = count($positions);

                        if ($debug) {
                            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                            echo '$positions:';
                            var_dump($positions);
                        }
                        $a = new stdClass();
                        $a->csvcol = $col;
                        $a->csvvalue = $value;

                        foreach ($positions as $k => $position) {
                            if (is_number($position)) {
                                // if position is out of range...
                                if (($position >= $options) || ($position < 0)) { // for radio buttons
                                    $cir->close();
                                    $cir->cleanup();
                                    $a->position = $position;
                                    $a->bounds = '0..'.$options;
                                    print_error('import_positionoutofbound', 'surveypro', $returnurl, $a);
                                }
                            } else {
                                // if $position must be numeric if $k is not is at its last value
                                if ($k < $contentscount-1) {
                                    $cir->close();
                                    $cir->cleanup();
                                    $a->position = $position;
                                    print_error('import_positionnotinteger', 'surveypro', $returnurl, $a);
                                }
                            }
                        }
                    } else {
                        $contents = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $value);
                        $contentscount = count($contents);

                        if ($debug) {
                            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                            echo '$positions:';
                            var_dump($positions);
                        }

                        foreach ($contents as $k => $content) {
                            $key = array_search($content, $options);
                            if ($key !== false) { // it is not an error, accept it
                                continue;
                            }
                            if (in_array($content, $reservedwords)) { // it is not an error, accept it
                                continue;
                            }
                            if ($k == $contentscount-1) { // it is not an error, accept it
                                continue;
                            }

                            if ($debug) {
                                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                                echo '**Errore**<br />';
                                echo 'Non trovo "'.$value.'" fra gli elementi di $options<br />';
                            }

                            $cir->close();
                            $cir->cleanup();

                            $a = new stdClass();
                            $a->csvcol = $col;
                            $a->csvvalue = $content;
                            $a->csvrow = implode(', ', $csvrow);
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
                                case SURVEYPRO_ITEMDRIVEN:
                                    $itemdownloadformat = $itemhelperinfo[$col]->contentformat;
                                    switch ($itemdownloadformat) {
                                        case SURVEYPRO_ITEMRETURNSLABELS:
                                            $a->semantic = get_string('answerlabel', 'surveypro');
                                            break;
                                        case SURVEYPRO_ITEMSRETURNSVALUES:
                                            $a->semantic = get_string('answervalue', 'surveypro');
                                            break;
                                        case SURVEYPRO_ITEMRETURNSPOSITION:
                                            $a->semantic = get_string('answerposition', 'surveypro');
                                            break;
                                        default:
                                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $itemhelperinfo[$col]->contentformat = '.$itemhelperinfo[$col]->contentformat, DEBUG_DEVELOPER);
                                    }
                                    break;
                                default:
                                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->formdata->csvsemantic = '.$this->formdata->csvsemantic, DEBUG_DEVELOPER);
                            }
                            print_error('import_missingsemantic', 'surveypro', $returnurl, $a);
                        }
                    }
                }
            }
        }
        // end of: DOES EACH RECORD provide a valid value?
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
        // I am now safe to import each value without arguing about its validity
        $timenow = time();

        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I start the import<br />';
        }

        // ********************************
        //   F I N A L L Y   I M P O R T
        // ********************************
        // init csv import helper
        $debug = false;

        $gooduserids = array();
        $baduserids = array();

        $cir->init();
        while ($csvrow = $cir->next()) {
            if ($debug) {
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo '$csvrow = '.implode(', ', $csvrow).'<br />';
            }

            // add one record to surveypro_submission
            $record = new stdClass();
            $record->surveyproid = $this->surveypro->id;
            if (isset($environmentheaders['userid'])) {
                $userid = $csvrow[$environmentheaders['userid']];
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

            if (isset($environmentheaders['timecreated'])) {
                $record->timecreated = $csvrow[$environmentheaders['timecreated']];
            } else {
                $record->timecreated = $timenow;
            }

            if (isset($environmentheaders['timemodified'])) {
                $record->timemodified = $csvrow[$environmentheaders['timemodified']];
            }

            $record->status = $defaultstatus;

            if ($debug) {
                echo 'I am going to save to surveypro_submission:<br />';
                echo '$record:';
                var_dump($record);
            }
            $submissionid = $DB->insert_record('surveypro_submission', $record);

            // add many records to surveypro_answer
            $status = $defaultstatus;
            foreach ($csvrow as $col => $value) {
                if (isset($environmentheaders['userid']) && ($col == $environmentheaders['userid'])) {
                    // the column for userid
                    continue;
                }
                if (isset($environmentheaders['timecreated']) && ($col == $environmentheaders['timecreated'])) {
                    // the column for userid
                    continue;
                }
                if (isset($environmentheaders['timemodified']) && ($col == $environmentheaders['timemodified'])) {
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

                // finally, save
                $record = new stdClass();
                $record->submissionid = $submissionid;
                $record->itemid = $columntoitemid[$col];
                $record->content = $value;
                $record->contentformat = $itemhelperinfo[$col]->contentformat;
                if ($debug) {
                    echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                    echo 'I am going to save to surveypro_answer:<br />';
                    echo '$record:';
                    var_dump($record);
                }
                $DB->insert_record('surveypro_answer', $record);
            }

            if ($status != $defaultstatus) {
                // $record = $DB->get_record('surveypro_submission', array('id' => $submissionid));
                $record = new StdClass();
                $record->id = $submissionid;
                $record->status = $status;
                $DB->update_record('surveypro_submission', $record);
            }
            if ($debug) {
                die();
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