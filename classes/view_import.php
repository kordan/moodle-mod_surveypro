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
 * The importmanager class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class importing data from CSV
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_view_import {

    /**
     * @var object Course module object
     */
    protected $cm;

    /**
     * @var object Context object
     */
    protected $context;

    /**
     * @var object Surveypro object
     */
    protected $surveypro;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    /**
     * Trigger the all_submissions_exported event.
     *
     * @return void
     */
    public function trigger_event() {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_exported::create($eventdata);
        $event->trigger();
    }

    /**
     * Display the welcome message of the import page.
     *
     * @return void
     */
    public function welcome_message() {
        global $OUTPUT;

        $semanticitem = array();
        $plugins = surveypro_get_plugin_list(SURVEYPRO_TYPEFIELD);
        foreach ($plugins as $k => $plugin) {
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, SURVEYPRO_TYPEFIELD, $plugin, false);
            if ($item->get_savepositiontodb()) {
                $semanticitem[] = $plugins[$k];
            }
        }

        $a = new stdClass();
        $a->customsemantic = get_string('itemdrivensemantic', 'mod_surveypro', get_string('downloadformat', 'mod_surveypro'));
        $a->items = '<li>'.implode(';</li><li>', $semanticitem).'.</li>';
        $message = get_string('welcome_dataimport', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Get uniqueness columns.
     *
     * @param array $foundheaders
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
     * Get survey infos.
     *
     * @return $surveyheaders and $requireditems
     */
    public function get_survey_infos() {
        global $CFG, $DB;

        // Get the list of used plugin.
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $pluginlist = $utilityman->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        $requireditems = array();
        $surveyheaders = array();
        $surveyheaders[SURVEYPRO_OWNERIDLABEL] = SURVEYPRO_OWNERIDLABEL;
        $surveyheaders[SURVEYPRO_TIMECREATEDLABEL] = SURVEYPRO_TIMECREATEDLABEL;
        $surveyheaders[SURVEYPRO_TIMEMODIFIEDLABEL] = SURVEYPRO_TIMEMODIFIEDLABEL;

        $whereparams = array('surveyproid' => $this->surveypro->id);
        foreach ($pluginlist as $plugin) {
            $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'_'.SURVEYPRO_TYPEFIELD;
            $canbemandatory = $classname::item_uses_mandatory_dbfield();

            $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $fieldname = ($canbemandatory) ? ', p.required' : '';
            $sql = 'SELECT p.itemid, p.variable'.$fieldname.'
                    FROM {surveypro_item} i
                      JOIN {'.$tablename.'} p ON p.itemid = i.id
                    WHERE i.surveyproid = :surveyproid
                    ORDER BY p.itemid';
            $itemvariables = $DB->get_records_sql($sql, $whereparams);

            foreach ($itemvariables as $itemvariable) {
                $surveyheaders[$itemvariable->itemid] = $itemvariable->variable;
                if ($canbemandatory) {
                    if ($itemvariable->required > 0) {
                        $requireditems[] = $itemvariable->itemid;
                    }
                } else {
                    // It is autofill, so it is always mandatory.
                    $requireditems[] = $itemvariable->itemid;
                }
            }
        }

        return array($surveyheaders, $requireditems);
    }

    /**
     * Verify each required item is included among survey headers.
     *
     * @param array $requireditems
     * @param int $columntoitemid
     * @param array $surveyheaders
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
     * Verify whether attachments were included in the import file.
     *
     * @param array $foundheaders
     * @return mixed array extraheadres or bool false
     */
    public function verify_attachments_import($foundheaders) {
        global $DB;

        // First step: make the list of each fileupload items of this surveypro.
        $where = array('surveyproid' => $this->surveypro->id);
        $sql = 'SELECT p.itemid, p.variable
                FROM {surveypro_item} i
                  JOIN {surveyprofield_fileupload} p ON p.itemid = i.id
                WHERE i.surveyproid = :surveyproid
                ORDER BY p.itemid';
        $variablenames = $DB->get_records_sql_menu($sql, $where);
        if (!count($variablenames)) {
            return false;
        }

        $extraheadres = array();
        foreach ($foundheaders as $foundheader) {
            $key = in_array($foundheader, $variablenames);
            if ($key) {
                $extraheadres[] = $foundheader;
            }
        }

        if (count($extraheadres)) {
            return $extraheadres;
        } else {
            return false;
        }
    }

    /**
     * Get csv content.
     *
     * @return csv content
     */
    public function get_csv_content() {
        $importform = new mod_surveypro_importform();

        return $importform->get_file_content('csvfile_filepicker');
    }

    /**
     * Get column to item id.
     *
     * This method returns the correspondence between the column where the datum is found
     * and the id of the surveypro item where the datum has to go
     *
     * $foundheaders
     * $surveyheaders
     *
     * @param array $foundheaders
     * @param array $surveyheaders
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
                $columntoitemid[$k] = $key;
                if ($key == SURVEYPRO_OWNERIDLABEL) {
                    $environmentheaders[SURVEYPRO_OWNERIDLABEL] = $k;
                    continue;
                }
                if ($key == SURVEYPRO_TIMECREATEDLABEL) {
                    $environmentheaders[SURVEYPRO_TIMECREATEDLABEL] = $k;
                    continue;
                }
                if ($key == SURVEYPRO_TIMEMODIFIEDLABEL) {
                    $environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL] = $k;
                    continue;
                }
            } else {
                $nonmatchingheaders[] = $foundheader;
            }
        }

        return array($columntoitemid, $nonmatchingheaders, $environmentheaders);
    }

    /**
     * Get items helper info.
     *
     * @param array $columntoitemid
     * @param array $environmentheaders
     * @return $itemhelperinfo (one $itemhelperinfo per each item)
     * @return $itemoptions (one $itemoptions only each items with $info->savepositiontodb = 1)
     */
    public function get_items_helperinfo($columntoitemid, $environmentheaders) {
        $itemhelperinfo = array(); // One element per each item.
        $itemoptions = array(); // One element only for items saving position to db.
        foreach ($columntoitemid as $col => $itemid) {
            if (isset($environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                if ($col == $environmentheaders[SURVEYPRO_OWNERIDLABEL]) {
                    // The column for userid.
                    continue;
                }
            }
            if (isset($environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                if ($col == $environmentheaders[SURVEYPRO_TIMECREATEDLABEL]) {
                    // The column for timecreated.
                    continue;
                }
            }
            if (isset($environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                if ($col == $environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]) {
                    // The column for timemodified.
                    continue;
                }
            }
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemid);

            // Itemhelperinfo.
            $info = new stdClass();
            $info->required = $item->get_required();
            $info->savepositiontodb = $item->get_savepositiontodb();
            if (($this->formdata->csvsemantic == SURVEYPRO_ITEMDRIVEN) && ($info->savepositiontodb)) {
                $info->contentformat = $item->get_downloadformat();
            } else {
                $info->contentformat = $item->get_contentformat();
            }
            $itemhelperinfo[$col] = $info;

            // Itemoption.
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
                            // Do not waste your memory calculating $itemoptions.
                            // $itemoptions is useless as the position is already available in the csv.
                            // The count of the options is enough.
                            $itemoptions[$col] = count($item->item_get_content_array(SURVEYPRO_LABELS, 'options'));
                            break;
                        default:
                            $message = 'Unexpected $itemdownloadformat = '.$itemdownloadformat;
                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                    }
                    continue;
                }
            }
        }

        return array($itemhelperinfo, $itemoptions);
    }

    /**
     * Import csv.
     *
     * Make a long list of test and, if all goes fine, import.
     *
     * @return void
     */
    public function import_csv() {
        global $USER, $DB, $COURSE;

        $debug = false;

        $iid = csv_import_reader::get_new_iid('surveyprouserdata');
        $cir = new csv_import_reader($iid, 'surveyprouserdata');
        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$iid = '.$iid.'<br />';
            echo '$cir:';
            var_dump($cir);
        }

        $csvcontent = $this->get_csv_content();
        if ($debug) {
            echo '$csvcontent = '.$csvcontent.'<br />';
        }

        // Does data come from OLD surveypro?
        if ( (strpos($csvcontent, '__invItat10n__') === false) &&
             (strpos($csvcontent, '__n0__Answer__') === false) &&
             (strpos($csvcontent, '__1gn0rE__me__') === false) ) {
            $csvusesolddata = false;
        } else {
            $csvusesolddata = true;
        }

        // Method load_csv_content is needed to define properties in the class.
        $recordcount = $cir->load_csv_content($csvcontent, $this->formdata->encoding, $this->formdata->csvdelimiter);

        unset($csvcontent);

        // Start here 3 tests against general file configuration.
        // First) is the number of field of each line homogeneous with all the others?
        $csvfileerror = $cir->get_error();
        if (!is_null($csvfileerror)) {
            $cir->close();
            $cir->cleanup();

            $error = new stdClass();
            $error->key = 'import_columnscountchanges';
            return $error;
        }

        // Second) is each column unique?
        $foundheaders = $cir->get_columns();
        if ($debug) {
            echo '$foundheaders:';
            var_dump($foundheaders);
        }
        if ($duplicateheader = $this->verify_header_duplication($foundheaders)) { // Error.
            $cir->close();
            $cir->cleanup();

            $error = new stdClass();
            $error->key = 'import_duplicateheader';
            $error->a = $duplicateheader;
            return $error;
        }

        // Third) is the user trying to import an attachment?
        if ($attachments = $this->verify_attachments_import($foundheaders)) { // Error.
            $cir->close();
            $cir->cleanup();

            $error = new stdClass();
            $error->key = 'import_attachmentsnotallowed';
            $error->a = '<li>'.implode(';</li><li>', $attachments).'.</li>';
            return $error;
        }

        // Make a list of the header of each item in the survey.
        // And the list of the id of the required items.
        list($surveyheaders, $requireditems) = $this->get_survey_infos();
        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$surveyheaders:';
            var_dump($surveyheaders);
            echo '$requireditems:';
            var_dump($requireditems);
        }

        // Rationale: teacher is importing.
        // Each datum is gold. Because of this, I DECIDED that:
        // Even if a required field is not present...
        // -> I will anyway import the record;
        // -> I will even import the content for the items placed in pages greater than the page of a missing required item;
        //
        // TO MAKE THIS CLEAR ONCE AND FOR EVER:
        // -> Teacher IS NOT allowed to enter a invalid content
        // -> But IS allowed to partially import records even jumping mandatory values.

        // Make a relation between each column header and the corresponding itemid.
        list($columntoitemid, $nonmatchingheaders, $environmentheaders) = $this->get_columntoitemid($foundheaders, $surveyheaders);
        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$columntoitemid:';
            var_dump($columntoitemid);

            echo '$environmentheaders:';
            var_dump($environmentheaders);

            echo '$nonmatchingheaders:';
            var_dump($nonmatchingheaders);
            die;
        }

        if (count($nonmatchingheaders)) {
            $cir->close();
            $cir->cleanup();

            $error = new stdClass();
            $error->key = 'import_extraheaderfound';
            $error->a = '<li>'.implode(';</li><li>', $nonmatchingheaders).'.</li>';
            return $error;
        }

        // Define the stautus of imported records.
        // Is each required item included into the csv?
        if ($this->verify_required($requireditems, $columntoitemid, $surveyheaders)) {
            $defaultstatus = SURVEYPRO_STATUSINPROGRESS;
        } else {
            // This status is still subject to further changes during csv, line by line, scan.
            $defaultstatus = SURVEYPRO_STATUSCLOSED;
        }

        // To save time during all validations to carry out, save to $itemhelperinfo some information.
        // In this way I no longer will need to load item hundreds times.
        // Begin of: get now, once and for ever, each item helperinfo.
        list($itemhelperinfo, $itemoptions) = $this->get_items_helperinfo($columntoitemid, $environmentheaders);
        // End of: get now, once and for ever, each item option (where applicable).

        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$itemhelperinfo:';
            var_dump($itemhelperinfo);
            echo '$itemoptions:';
            var_dump($itemoptions);
        }

        // Begin of: DOES EACH RECORD provide a valid value?
        // Start here a looooooooong list of verifications against founded values record per record.
        $reservedwords = array();
        if ($csvusesolddata) {
            $reservedwords[] = '__invItat10n__';
            $reservedwords[] = '__n0__Answer__';
            $reservedwords[] = '__1gn0rE__me__';
        } else {
            $reservedwords[] = SURVEYPRO_INVITEVALUE;
            $reservedwords[] = SURVEYPRO_NOANSWERVALUE;
            $reservedwords[] = SURVEYPRO_IGNOREMEVALUE;
        }
        $reservedwords[] = SURVEYPRO_ANSWERNOTINDBVALUE;

        $csvusers = array();
        $cir->init();
        while ($csvrow = $cir->next()) {
            foreach ($foundheaders as $col => $unused) {
                $value = $csvrow[$col]; // The value reported in the csv file.

                if (isset($environmentheaders[SURVEYPRO_OWNERIDLABEL]) && ($col == $environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                    // The column for userid.
                    if (empty($value)) {
                        $cir->close();
                        $cir->cleanup();

                        $error = new stdClass();
                        $error->key = 'import_missinguserid';
                        return $error;
                    } else {
                        if (!is_number($value)) {
                            $cir->close();
                            $cir->cleanup();

                            $error = new stdClass();
                            $error->key = 'import_invaliduserid';
                            $error->a = $value;
                            return $error;
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

                if (isset($environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                    if ($col == $environmentheaders[SURVEYPRO_TIMECREATEDLABEL]) {
                        // The column for timecreated.
                        if (empty($value)) {
                            $cir->close();
                            $cir->cleanup();

                            $error = new stdClass();
                            $error->key = 'import_missingtimecreated';
                            return $error;
                        }
                        if (!is_number($value)) {
                            $cir->close();
                            $cir->cleanup();

                            $error = new stdClass();
                            $error->key = 'import_invalidtimecreated';
                            $error->a = $value;
                            return $error;
                        }
                        continue;
                    }
                }

                if (isset($environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                    if ($col == $environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]) {
                        if (!empty($value) && !is_number($value)) {
                            $cir->close();
                            $cir->cleanup();

                            $error = new stdClass();
                            $error->key = 'import_invalidtimemodified';
                            $error->a = $value;
                            return $error;
                        }
                    }
                    continue;
                }

                $info = $itemhelperinfo[$col]; // The itemhelperinfo of the item in column = $col.
                if ($debug) {
                    echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                    echo '$info:';
                    var_dump($info);
                }

                // I import files with missing mandatory columns (fields) too.
                // But, if the column of the mandatory element is provided in the csv file then it has to be not empty.
                if ($info->required) {
                    // Verify it is not empty.
                    if (!strlen($value)) { // Error.
                        $cir->close();
                        $cir->cleanup();

                        $error = new stdClass();
                        $error->key = 'import_emptyrequiredvalue';
                        $error->a = new stdClass();
                        $error->a->row = implode(', ', $csvrow);
                        $error->a->col = $col;
                        return $error;
                    }
                }

                if ($info->savepositiontodb) {
                    // Verify it is valid.
                    $options = $itemoptions[$col];

                    if ($debug) {
                        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                        echo '----> Validation of the content reported for the column number: '.$col.'<br />';
                        echo '----> Surveypro_item $options:';
                        var_dump($options);
                        echo 'I have $value = '.$value.'<br />';
                    }

                    if (!is_array($options)) {
                        // $option is not an array. It is a number. (see "public function get_items_helperinfo" for the reason).
                        // This means that the content reported in the csv for this column is supposed to be ALREADY a position.
                        // Verify the content is REALLY a VALID position.
                        $positions = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $value);

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
                                // If position is out of range...
                                if (($position >= $options) || ($position < 0)) { // For radio buttons.
                                    $cir->close();
                                    $cir->cleanup();
                                    $a->position = $position;
                                    $a->bounds = '0..'.$options;

                                    $error = new stdClass();
                                    $error->key = 'import_positionoutofbound';
                                    $error->a = new stdClass();
                                    $error->a->position = $position;
                                    $error->a->bounds = '0..'.$options;
                                    return $error;
                                }
                            } else {
                                // If $position must be numeric if $k is not is at its last value.
                                if ($k < $contentscount - 1) {
                                    $cir->close();
                                    $cir->cleanup();
                                    $a->position = $position;

                                    $error = new stdClass();
                                    $error->key = 'import_positionnotinteger';
                                    $error->a = $position;
                                    return $error;
                                }
                            }
                        }
                    } else {
                        $contents = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $value);
                        $contentscount = count($contents);

                        if ($debug) {
                            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                            echo '$contents:';
                            var_dump($contents);
                        }

                        foreach ($contents as $k => $content) {
                            $key = array_search($content, $options);
                            if ($key !== false) { // ...$content was found, carry on!
                                continue;
                            }
                            if (in_array($content, $reservedwords)) { // ...$content is a reserved word. Good. Carry on!
                                continue;
                            }
                            if ($k == $contentscount - 1) { // It is not an error, accept it.
                                continue;
                            }

                            if ($debug) {
                                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                                echo '** Error **<br />';
                                echo 'I can\'t find "'.$value.'" among $options items<br />';
                            }

                            $cir->close();
                            $cir->cleanup();

                            $error = new stdClass();
                            $error->key = 'import_missingsemantic';
                            $error->a = new stdClass();
                            $error->a->csvcol = $col;
                            $error->a->csvvalue = $value;
                            $error->a->csvrow = implode(', ', $csvrow);
                            $error->a->header = $foundheaders[$col];

                            switch ($this->formdata->csvsemantic) {
                                case SURVEYPRO_LABELS:
                                    $error->a->semantic = get_string('answerlabel', 'mod_surveypro');
                                    break;
                                case SURVEYPRO_VALUES:
                                    $error->a->semantic = get_string('answervalue', 'mod_surveypro');
                                    break;
                                case SURVEYPRO_POSITIONS:
                                    $error->a->semantic = get_string('answerposition', 'mod_surveypro');
                                    break;
                                case SURVEYPRO_ITEMDRIVEN:
                                    $itemdownloadformat = $itemhelperinfo[$col]->contentformat;
                                    switch ($itemdownloadformat) {
                                        case SURVEYPRO_ITEMRETURNSLABELS:
                                            $error->a->semantic = get_string('answerlabel', 'mod_surveypro');
                                            break;
                                        case SURVEYPRO_ITEMSRETURNSVALUES:
                                            $error->a->semantic = get_string('answervalue', 'mod_surveypro');
                                            break;
                                        case SURVEYPRO_ITEMRETURNSPOSITION:
                                            $error->a->semantic = get_string('answerposition', 'mod_surveypro');
                                            break;
                                        default:
                                            $message = 'Unexpected $itemdownloadformat = '.$itemdownloadformat;
                                            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                                    }
                                    break;
                                default:
                                    $message = 'Unexpected $this->formdata->csvsemantic = '.$this->formdata->csvsemantic;
                                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                            }
                            return $error;
                        }
                    }
                }
            }
        }
        // End of: DOES EACH RECORD provide a valid value?
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
                    if ($totalsubmissions > $this->surveypro->maxentries) { // Error.
                        $a = new stdClass();
                        $a->userid = $csvuserid;
                        $a->maxentries = $this->surveypro->maxentries;
                        $a->totalentries = $totalsubmissions;

                        $error = new stdClass();
                        $error->key = 'import_breakingmaxentries';
                        $error->a = new stdClass();
                        $error->a->userid = $csvuserid;
                        $error->a->maxentries = $this->surveypro->maxentries;
                        $error->a->totalentries = $totalsubmissions;
                        return $error;
                    }
                }
            }
        }

        // If you did not die, each validation passed.
        // Continue with the import.
        // I am now safe to import each value without arguing about its validity.
        $timenow = time();

        if ($debug) {
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I start the import<br />';
        }

        // F I N A L L Y   I M P O R T .
        // Init csv import helper.
        $debug = false;

        $gooduserids = array();
        $baduserids = array();

        $cir->init();
        while ($csvrow = $cir->next()) {
            if ($debug) {
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo '$csvrow = '.implode(', ', $csvrow).'<br />';
            }

            // Add one record to surveypro_submission.
            $record = new stdClass();
            $record->surveyproid = $this->surveypro->id;
            if (isset($environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                $userid = $csvrow[$environmentheaders[SURVEYPRO_OWNERIDLABEL]];
                // Try to save querys.
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

            if (isset($environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                $record->timecreated = $csvrow[$environmentheaders[SURVEYPRO_TIMECREATEDLABEL]];
            } else {
                $record->timecreated = $timenow;
            }

            if (isset($environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                if (!empty($csvrow[$environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]])) {
                    $record->timemodified = $csvrow[$environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]];
                }
            }

            $record->status = $defaultstatus;

            if ($debug) {
                echo 'I am going to save to surveypro_submission:<br />';
                echo '$record:';
                var_dump($record);
            }
            $submissionid = $DB->insert_record('surveypro_submission', $record);

            // Add many records to surveypro_answer.
            $status = $defaultstatus;
            foreach ($csvrow as $col => $value) {
                if (isset($environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                    if ($col == $environmentheaders[SURVEYPRO_OWNERIDLABEL]) {
                        // The column for userid.
                        continue;
                    }
                }
                if (isset($environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                    if ($col == $environmentheaders[SURVEYPRO_TIMECREATEDLABEL]) {
                        // The column for userid.
                        continue;
                    }
                }
                if (isset($environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                    if ($col == $environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]) {
                        // The column for userid.
                        continue;
                    }
                }
                if ($debug) {
                    echo '$col = '.$col.'<br />';
                    echo '$value = '.$value.'<br />';
                }
                // Even if each required header is present I need to check that the current content for each item is not empty!
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

                // Finally, save.
                $record = new stdClass();
                $record->submissionid = $submissionid;
                $record->itemid = $columntoitemid[$col];
                $record->content = $value;
                $record->verified = 1;
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
                $record = new StdClass();
                $record->id = $submissionid;
                $record->status = $status;
                $DB->update_record('surveypro_submission', $record);
            }
            if ($debug) {
                die();
            }
        }

        // Update completion state.
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_INCOMPLETE);
        }

        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\submissions_imported::create($eventdata);
        $event->trigger();
    }
}