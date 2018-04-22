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
     * @var array assigning to each column of the csv the corresponding itemid of this surveypro
     */
    public $columntoitemid;

    /**
     * @var array with at least three elements
     * - SURVEYPRO_OWNERIDLABEL
     * - SURVEYPRO_TIMECREATEDLABEL
     * - SURVEYPRO_TIMEMODIFIEDLABEL
     * describing the additional headers found in the csv file
     * respect to the headers of this surveypro.
     */
    public $environmentheaders;

    /**
     * @var object csv import reader
     */
    public $cir;

    /**
     * @var array itemhelperinfo
     */
    private $itemhelperinfo = array();

    /**
     * @var int The dafault status of the submission to import
     */
    public $defaultstatus;

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

        $message = get_string('welcome_dataimport', 'mod_surveypro');
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    // MARK validations

    /**
     * Verify uniqueness columns.
     *
     * @param array $foundheaders
     * @return mixed error object or bool false
     */
    public function are_headers_unique($foundheaders) {
        $uniqueheaders = array_unique($foundheaders);
        if ($duplicateheader = array_diff_key($foundheaders, $uniqueheaders)) {
            $error = new stdClass();
            $error->key = 'import_duplicateheader';
            $error->a = $duplicateheader;

            return $err;
        }

        return false;
    }

    /**
     * Verify whether attachments were included in the import file.
     *
     * @param array $foundheaders
     * @return mixed error object or bool false
     */
    public function are_attachments_included($foundheaders) {
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

        if ($intersection = array_intersect($foundheaders, $variablenames)) {
            $error = new stdClass();
            $error->key = 'import_attachmentsnotallowed';
            $error->a = '<ul><li>'.implode(';</li><li>', $intersection).'.</li></ul>';

            return $error;
        } else {
            return false;
        }
    }

    /**
     * Verify headers are all matched.
     *
     * @param array $nonmatchingheaders
     * @return mixed error object or bool false
     */
    public function are_headers_matching($nonmatchingheaders) {
        if (count($nonmatchingheaders)) {
            $error = new stdClass();
            $error->key = 'import_extraheaderfound';
            $error->a = '<ul><li>'.implode(';</li><li>', $nonmatchingheaders).'.</li></ul>';

            return $error;
        } else {
            return false;
        }
    }

    /**
     * Validate the content of the userid column found in csv.
     *
     * @param array $surveyheaders
     * @return mixed error object or bool false
     */
    public function are_children_orphans($surveyheaders) {
        $orphansheader = array();
        $missingheader = array();
        foreach ($this->itemhelperinfo as $k => $itemhelper) {
            // Is this item a child?
            if (!empty($itemhelper->parentid)) {
                // Verify the parent is imported too.
                // Get the column where is stored the answer given to the parent item.
                $parentcolumn = array_search($itemhelper->parentid, $this->columntoitemid);
                if ($parentcolumn === false) {
                    $a = new stdClass();
                    $missingparentid = $this->columntoitemid[$k];
                    $a->childheader = $surveyheaders[$missingparentid];
                    $a->missingparentheader = $surveyheaders[$itemhelper->parentid];
                    $orphansheader[] = get_string('import_missingheaders', 'surveypro', $a);
                }
            }
        }

        if (!empty($orphansheader)) {
            $error = new stdClass();
            $error->key = 'import_orphanchild';
            $error->a  = '<ul><li>'.implode(';</li><li>', $orphansheader).'</li></ul>';

            return $error;
        }

        return false;
    }

    /**
     * Validate the content of the userid column found in csv.
     *
     * @param string $value
     * @return mixed error object or bool false
     */
    public function is_valid_userid($value) {
        if (empty($value)) {
            $error = new stdClass();
            $error->key = 'import_missinguserid';

            return $error;
        }

        if (!is_number($value)) {
            $error = new stdClass();
            $error->key = 'import_invaliduserid';
            $error->a = $value;

            return $error;
        }

        return false;
    }

    /**
     * Validate the content of the timecreated column found in csv.
     *
     * @param string $value
     * @return mixed error object or bool false
     */
    public function is_valid_creationtime($value) {
        if (empty($value)) {
            $error = new stdClass();
            $error->key = 'import_missingtimecreated';

            return $error;
        }

        if (!is_number($value)) {
            $error = new stdClass();
            $error->key = 'import_invalidtimecreated';
            $error->a = $value;

            return $error;
        }

        return false;
    }

    /**
     * Validate the content of the timemodified column found in csv.
     *
     * @param string $value
     * @return mixed error object or bool false
     */
    public function is_valid_modificationtime($value) {
        if (empty($value)) {
            return false;
        }

        if (!is_number($value)) {
            $error = new stdClass();
            $error->key = 'import_invalidtimemodified';
            $error->a = $value;

            return $error;
        }

        return false;
    }

    /**
     * Verify the null value is allowe and, eventually, return an error message.
     *
     * @param array $csvrow
     * @param int $col
     * @param object $itemhelper
     * @return mixed error object or bool false
     */
    public function is_nullvalue_allowed($csvrow, $col, $itemhelper) {
        // SURVEYPRO_EXPNULLVALUE is allowed if the parent item was feeded with a wrong answer.

        // Has, this element, a parent item?
        if (empty($itemhelper->parentid)) {
            $error = new stdClass();
            $error->key = 'import_nullwithoutparent';
            $error->a = new stdClass();
            $error->a->row = implode(', ', $csvrow);
            $error->a->value = SURVEYPRO_EXPNULLVALUE;
            $error->a->col = $col;
            $error->a->content = $itemhelper->content;
            $error->a->plugin = $itemhelper->plugin;

            return $error;
        } else {
            // Get the column where is stored the answer given to the parent item.
            $parentcolumn = array_search($itemhelper->parentid, $this->columntoitemid);
            $parentanswer = $csvrow[$parentcolumn];
            // Did the parent item receive an answer forbidding this child?
            if ($itemhelper->parentvalue == $parentanswer) {
                $error = new stdClass();
                $error->key = 'import_nullnotallowed';
                $error->a = new stdClass();
                $error->a->row = implode(', ', $csvrow);
                $error->a->value = SURVEYPRO_EXPNULLVALUE;
                $error->a->col = $col;
                $error->a->content = $itemhelper->content;
                $error->a->plugin = $itemhelper->plugin;

                return $error;
            }
        }

        return false;
    }

    /**
     * Verify the passed value is not empty and, eventually, return the error message.
     *
     * @param array $csvrow
     * @param int $col
     * @param object $itemhelper
     * @return mixed error object or bool false
     */
    public function is_string_notempty($csvrow, $col, $itemhelper) {
        $value = $csvrow[$col];
        if (!strlen($value)) {
            $error = new stdClass();
            $error->key = 'import_emptyrequiredvalue';
            $error->a = new stdClass();
            $error->a->col = $col;
            $error->a->plugin = $itemhelper->plugin;
            $error->a->content = $itemhelper->content;
            $error->a->row = implode(', ', $csvrow);

            return $error;
        }

        if ($value == SURVEYPRO_NOANSWERVALUE) {
            $error = new stdClass();
            $error->key = 'import_noanswertorequired';
            $error->a = new stdClass();
            $error->a->value = SURVEYPRO_NOANSWERVALUE;
            $error->a->col = $col;
            $error->a->plugin = $itemhelper->plugin;
            $error->a->content = $itemhelper->content;
            $error->a->row = implode(', ', $csvrow);

            return $error;
        }

        return false;
    }

    /**
     * Verify the passed position is a possible position and, eventually, return the error message.
     *
     * @param string $value
     * @param int $col
     * @param int $itemoptionscount
     * @param object $itemhelper
     * @return mixed error object or bool false
     */
    public function are_positions_valid($value, $col, $itemoptionscount, $itemhelper) {
        $foundpositions = explode(SURVEYPRO_DBMULTICONTENTSEPARATOR, $value);

        $countfoundpositions = count($foundpositions);
        $countfoundpositions--;

        foreach ($foundpositions as $k => $position) {
            if (is_number($position)) {
                // If position is out of range...
                if (($position >= $itemoptionscount) || ($position < 0)) { // For radio buttons.
                    $error = new stdClass();
                    $error->key = 'import_positionoutofbound';
                    $error->a = new stdClass();
                    $error->a->position = $position;
                    $error->a->plugin = $itemhelper->plugin;
                    $error->a->content = $itemhelper->content;
                    $error->a->csvcol = $col;
                    $error->a->bounds = '0..'.$itemoptionscount;
                    $error->a->prettywarning = get_string('import_prettywarning', 'mod_surveypro');

                    return $error;
                }
            } else {
                if ($itemhelper->usesoptionother) {
                    // If $position must be numeric if $k is not is at its last value.
                    if ($k < $countfoundpositions) {
                        $error = new stdClass();
                        $error->key = 'import_positionnotinteger';
                        $error->a = new stdClass();
                        $error->a->position = $position;
                        $error->a->plugin = $itemhelper->plugin;
                        $error->a->content = $itemhelper->content;
                        $error->a->csvcol = $col;
                        $error->a->bounds = '0..'.$itemoptionscount;
                        $error->a->prettywarning = get_string('import_prettywarning', 'mod_surveypro');

                        return $error;
                    }
                } else {
                    $error = new stdClass();
                    $error->key = 'import_positionnotinteger';
                    $error->a = new stdClass();
                    $error->a->position = $position;
                    $error->a->plugin = $itemhelper->plugin;
                    $error->a->content = $itemhelper->content;
                    $error->a->csvcol = $col;
                    $error->a->bounds = '0..'.$itemoptionscount;
                    $error->a->prettywarning = get_string('import_prettywarning', 'mod_surveypro');

                    return $error;
                }
            }
        }

        return false;
    }

    /**
     * Verify if the import of new answers breaks maxentries limit (for each user).
     *
     * @param int $submissionsperuser
     * @return mixed error object or bool false
     */
    public function is_maxentries_respected($submissionsperuser) {
        global $DB;

        if ($this->surveypro->maxentries) {
            if (!empty($submissionsperuser)) {
                list($insql, $inparams) = $DB->get_in_or_equal(array_keys($submissionsperuser), SQL_PARAMS_NAMED);
                $inparams['surveyproid'] = $this->surveypro->id;
                $sql = 'SELECT userid, COUNT(\'x\')
                        FROM {surveypro_submission}
                        WHERE surveyproid = :surveyproid
                          AND userid '.$insql.'
                        GROUP BY userid';
                $oldsubmissionsperuser = $DB->get_records_sql_menu($sql, $inparams);

                foreach ($oldsubmissionsperuser as $csvuserid => $oldsubmissions) {
                    $totalsubmissions = $oldsubmissions + $submissionsperuser[$csvuserid];
                    if ($totalsubmissions > $this->surveypro->maxentries) { // Error.
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

        return false;
    }

    // MARK get

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
                if ($classname::item_needs_contentformat()) {
                    $surveyheaders[$itemvariable->itemid.SURVEYPRO_IMPFORMATSUFFIX] = $itemvariable->variable.SURVEYPRO_IMPFORMATSUFFIX;
                }
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
     * @return the default status for records that are going to get imported.
     */
    public function get_default_status($requireditems) {
        $intersection = array_intersect($requireditems, $this->columntoitemid);

        $countrequired = count($requireditems);
        $countprovided = count($intersection);

        if ($countrequired == $countprovided) {
            return SURVEYPRO_STATUSCLOSED;
        } else {
            return SURVEYPRO_STATUSINPROGRESS;
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
     * @param array $foundheaders
     * @param array $surveyheaders
     *
     * @return array $nonmatchingheaders
     */
    public function get_columntoitemid($foundheaders, $surveyheaders) {
        $this->columntoitemid = array();

        $nonmatchingheaders = array();
        $this->environmentheaders = array();

        foreach ($foundheaders as $k => $foundheader) {
            $key = array_search($foundheader, $surveyheaders);
            if ($key !== false) {
                $this->columntoitemid[$k] = (string)$key;
                if ($key == SURVEYPRO_OWNERIDLABEL) {
                    $this->environmentheaders[SURVEYPRO_OWNERIDLABEL] = $k;
                    continue;
                }
                if ($key == SURVEYPRO_TIMECREATEDLABEL) {
                    $this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL] = $k;
                    continue;
                }
                if ($key == SURVEYPRO_TIMEMODIFIEDLABEL) {
                    $this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL] = $k;
                    continue;
                }
            } else {
                if (!preg_match('/'.SURVEYPRO_IMPFORMATSUFFIX.'$/', $foundheader)) {
                    $nonmatchingheaders[] = (string)$foundheader;
                }
            }
        }

        return $nonmatchingheaders;
    }

    /**
     * Get item helper.
     *
     * @return $optionscountpercol ($optionscountpercol is available only for items with $item->get_savepositiontodb() = 1)
     */
    public function buil_item_helpers() {
        $optionscountpercol = array(); // Elements only for items saving position to db.
        foreach ($this->columntoitemid as $col => $itemid) {
            if (preg_match('/'.SURVEYPRO_IMPFORMATSUFFIX.'$/', $itemid)) {
                continue;
            }

            if (isset($this->environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                if ($col == $this->environmentheaders[SURVEYPRO_OWNERIDLABEL]) {
                    // The column for userid.
                    continue;
                }
            }
            if (isset($this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                if ($col == $this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL]) {
                    // The column for timecreated.
                    continue;
                }
            }
            if (isset($this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                if ($col == $this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]) {
                    // The column for timemodified.
                    continue;
                }
            }
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemid);

            // Itemhelperinfo.
            $itemhelper = new stdClass();
            $itemhelper->plugin = $item->get_plugin();
            $itemhelper->content = $item->get_content();
            $itemhelper->required = $item->get_required();
            $itemhelper->savepositiontodb = $item->get_savepositiontodb();
            $itemhelper->usesoptionother = $item->get_usesoptionother();
            $itemhelper->parentid = $item->get_parentid();
            $itemhelper->parentvalue = $item->get_parentvalue();

            $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemhelper->plugin.'_'.SURVEYPRO_TYPEFIELD;
            $itemhelper->usescontentformat = $classname::item_needs_contentformat();
            $this->itemhelperinfo[$col] = $itemhelper;

            if ($itemhelper->savepositiontodb) {
                // The count of the options is enough.
                $optionscountpercol[$col] = count($item->item_get_content_array(SURVEYPRO_LABELS, 'options'));
            }
        }

        return $optionscountpercol;
    }

    /**
     * Import csv.
     *
     * Make a long list of test against the selected csv file
     *
     * @return mixed error object or bool false
     */
    public function validate_csvcontent() {
        global $USER;

        $debug = false;

        $iid = csv_import_reader::get_new_iid('surveyprouserdata');
        $this->cir = new csv_import_reader($iid, 'surveyprouserdata');
        $csvcontent = $this->get_csv_content();
        if ($debug) {
            echo html_writer::start_tag('pre');
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$iid = '.$iid;
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$this->cir:';
            var_dump($this->cir);
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$csvcontent = '.$csvcontent;
            echo html_writer::end_tag('pre');
        }

        // Method load_csv_content is needed to define properties in the class.
        $recordcount = $this->cir->load_csv_content($csvcontent, $this->formdata->encoding, $this->formdata->csvdelimiter);

        unset($csvcontent);

        // Start here 3 tests against general file configuration.
        // 1st) Verify each raw has the same column count.
        if ($this->cir->get_error()) {
            $error = new stdClass();
            $error->key = 'import_columnscountchanges';

            return $error;
        }

        $foundheaders = $this->cir->get_columns();

        // 2nd) is each column unique?
        if ($debug) {
            echo html_writer::start_tag('pre');
            echo '$foundheaders:';
            var_dump($foundheaders);
            echo html_writer::end_tag('pre');
        }
        if ($err = $this->are_headers_unique($foundheaders)) {
            return $err;
        }

        // 3rd) is the user trying to import attachments?
        if ($err = $this->are_attachments_included($foundheaders)) {
            return $err;
        }

        // Make a list of the header of each item in the survey.
        // And the list of the id of the required items.
        list($surveyheaders, $requireditems) = $this->get_survey_infos();
        if ($debug) {
            echo html_writer::start_tag('pre');
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$surveyheaders:';
            var_dump($surveyheaders);
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$requireditems:';
            var_dump($requireditems);
            echo html_writer::end_tag('pre');
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
        //
        // Records (submissions) missing required answers will be marked as SURVEYPRO_STATUSINPROGRESS.

        // Make a relation between each column header and the corresponding itemid.
        $nonmatchingheaders = $this->get_columntoitemid($foundheaders, $surveyheaders);
        if ($debug) {
            echo html_writer::start_tag('pre');
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$this->columntoitemid:';
            var_dump($this->columntoitemid);
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$this->environmentheaders:';
            var_dump($this->environmentheaders);
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$nonmatchingheaders:';
            var_dump($nonmatchingheaders);
            echo html_writer::end_tag('pre');
        }

        if ($err = $this->are_headers_matching($nonmatchingheaders)) {
            return $err;
        }

        // Define the stautus of imported records.
        // Is each required item included into the csv?
        // Even if status == SURVEYPRO_STATUSCLOSED, it may still change according to validation during line by line, scan.
        $this->defaultstatus = $this->get_default_status($requireditems);

        // To save time during all validations process, save to $itemhelperinfo some information.
        // In this way I no longer will need to load item hundreds times.
        // Begin of: get now, once and for ever, each item helperinfo.
        $optionscountpercol = $this->buil_item_helpers();
        // End of: get now, once and for ever, each item option (where applicable).

        if ($debug) {
            echo html_writer::start_tag('pre');
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo '$this->itemhelperinfo:';
            var_dump($this->itemhelperinfo);
            echo html_writer::end_tag('pre');

            echo html_writer::start_tag('pre');
            echo '$optionscountpercol:';
            var_dump($optionscountpercol);
            echo html_writer::end_tag('pre');
        }

        // Make one more test against general file configuration.
        // 4th) Has each child its parent imported too?
        if ($err = $this->are_children_orphans($surveyheaders)) {
            return $err;
        }

        // Begin of: DOES EACH RECORD provide a valid value?
        // Start here a looooooooong list of validations against founded values, record per record.
        $submissionsperuser = array();
        $this->cir->init();
        while ($csvrow = $this->cir->next()) {
            foreach ($foundheaders as $col => $unused) {
                $value = $csvrow[$col]; // The header reported in the csv file.

                if (!isset($this->itemhelperinfo[$col])) {
                    if (isset($this->environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                        if ($col == $this->environmentheaders[SURVEYPRO_OWNERIDLABEL]) {
                            // The column for userid.
                            if ($err = $this->is_valid_userid($value)) {
                                return $err;
                            } else {
                                if ($value != $USER->id) {
                                    if (!isset($submissionsperuser[$value])) {
                                        $submissionsperuser[$value] = 1;
                                    } else {
                                        $submissionsperuser[$value]++;
                                    }
                                }
                                continue;
                            }
                        }
                    }

                    if (isset($this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                        if ($col == $this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL]) {
                            // The column for timecreated.
                            if ($err = $this->is_valid_creationtime($value)) {
                                return $err;
                            }
                            continue;
                        }
                    }

                    if (isset($this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                        if ($col == $this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]) {
                            // The column for timemodified.
                            if ($err = $this->is_valid_modificationtime($value)) {
                                return $err;
                            }
                            continue;
                        }
                    }

                    $itemid = $this->columntoitemid[$col];
                    if (preg_match('/'.SURVEYPRO_IMPFORMATSUFFIX.'$/', $itemid)) {
                        // TODO: format should be verified too.
                        continue;
                    }

                    $message = 'Missing itemhelper for item ID = '.$this->columntoitemid[$col].' found in column: '.$col;
                    debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
                } else {
                    $itemhelper = $this->itemhelperinfo[$col]; // The itemhelperinfo of the item in column = $col.
                    if ($debug) {
                        echo html_writer::start_tag('pre');
                        echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                        echo '$itemhelper:';
                        var_dump($itemhelper);
                        echo html_writer::end_tag('pre');
                    }
                }

                if ($value == SURVEYPRO_EXPNULLVALUE) {
                    // Was the parent item receiving an answer not permitting the child?
                    // SURVEYPRO_EXPNULLVALUE is allowed ONLY IF the parent was not matched by the answer.
                    if ($err = $this->is_nullvalue_allowed($csvrow, $col, $itemhelper)) {
                        return $err;
                    }
                    continue;
                }

                // I import files even if mandatory columns (fields) are missing.
                // But, if the column of the mandatory element is provided in the csv file then it has to be not empty.
                if ($itemhelper->required) {
                    // Verify the found value is not empty.
                    if ($err = $this->is_string_notempty($csvrow, $col, $itemhelper)) {
                        return $err;
                    }
                } else {
                    if ($value == SURVEYPRO_NOANSWERVALUE) {
                        continue;
                    }
                }

                if ($itemhelper->savepositiontodb) {
                    // Verify positions are valid.
                    $itemoptionscount = $optionscountpercol[$col];

                    if ($err = $this->are_positions_valid($value, $col, $itemoptionscount, $itemhelper)) {
                        return $err;
                    }
                }
            }
        }
        // End of: DOES EACH RECORD provide a valid value?
        unset($foundheaders);

        // Am I going to break maxentries limit (for each user)?
        if ($err = $this->is_maxentries_respected($submissionsperuser)) {
            return $err;
        }
    }

    /**
     * Import csv.
     *
     * @return void
     */
    public function import_csv() {
        global $DB, $COURSE, $USER;

        // I am now safe to import each value without arguing about its validity.
        $timenow = time();

        $debug = false;
        if ($debug) {
            echo html_writer::start_tag('pre');
            echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
            echo 'I start the import';
            echo html_writer::end_tag('pre');
        }

        // Create helper $contentformattocol.
        $contentformattocol = array();
        foreach ($this->columntoitemid as $col => $itemid) {
            if (preg_match('/'.SURVEYPRO_IMPFORMATSUFFIX.'$/', $itemid)) {
                $contentformattocol[$itemid] = $col;
            }
        }

        // The import process is finally going to finalize.
        // I can safetly drop few elements of $this->columntoitemid to save a bit of time.
        // Drop out the element corresponding to ownerid.
        if (isset($this->environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
            $key = $this->environmentheaders[SURVEYPRO_OWNERIDLABEL];
            unset($this->columntoitemid[$key]);
        }
        // Drop out the element corresponding to timecreated.
        if (isset($this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
            $key = $this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL];
            unset($this->columntoitemid[$key]);
        }
        // Drop out the element corresponding to timemodified.
        if (isset($this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
            $key = $this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL];
            unset($this->columntoitemid[$key]);
        }
        // Drop out all the elements corresponding to format.
        foreach ($contentformattocol as $key) {
            unset($this->columntoitemid[$key]);
        }

        // F I N A L L Y   I M P O R T .
        // Init csv import helper.
        $gooduserids = array();
        $baduserids = array();

        $this->cir->init();
        while ($csvrow = $this->cir->next()) {
            if ($debug) {
                echo html_writer::start_tag('pre');
                echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                echo '$csvrow = '.implode(', ', $csvrow);
                echo html_writer::end_tag('pre');
            }

            // Add one record to surveypro_submission.
            $record = new stdClass();
            $record->surveyproid = $this->surveypro->id;
            $record->status = $this->defaultstatus;
            if (isset($this->environmentheaders[SURVEYPRO_OWNERIDLABEL])) {
                $userid = $csvrow[$this->environmentheaders[SURVEYPRO_OWNERIDLABEL]];
                // Try to save a query.
                if (in_array($userid, $gooduserids)) {
                    $record->userid = $userid;
                }
                if (in_array($userid, $baduserids)) {
                    $record->userid = $USER->id;
                }
                if (!isset($record->userid)) {
                    // Ok, make one more query! GRRRR.
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

            if (isset($this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL])) {
                $record->timecreated = $csvrow[$this->environmentheaders[SURVEYPRO_TIMECREATEDLABEL]];
            } else {
                $record->timecreated = $timenow;
            }

            if (isset($this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL])) {
                if (!empty($csvrow[$this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]])) {
                    $record->timemodified = $csvrow[$this->environmentheaders[SURVEYPRO_TIMEMODIFIEDLABEL]];
                }
            }

            if ($debug) {
                echo html_writer::start_tag('pre');
                echo 'I am going to save to surveypro_submission:<br />';
                echo '$record:';
                var_dump($record);
                echo html_writer::end_tag('pre');
            }
            $submissionid = $DB->insert_record('surveypro_submission', $record);
            // End of: Add one record to surveypro_submission.

            // Add as many records to surveypro_answer as the number of elements in the surveypro (if answered).
            foreach ($this->columntoitemid as $col => $itemid) {
                $content = $csvrow[$col];
                if ($content == SURVEYPRO_EXPNULLVALUE) {
                    continue;
                }

                // Finally, save.
                $record = new stdClass();
                $record->submissionid = $submissionid;
                $record->itemid = $itemid;
                $record->content = $content;
                $itemhelper = $this->itemhelperinfo[$col];
                if ($itemhelper->usescontentformat) {
                    if (isset($contentformattocol[$itemid.SURVEYPRO_IMPFORMATSUFFIX])) {
                        $formatcol = $contentformattocol[$itemid.SURVEYPRO_IMPFORMATSUFFIX];
                        $record->contentformat = $csvrow[$formatcol];
                    } else {
                        // Content format should always be provided.
                        // If the data file is old, it may miss the format column.
                        // In this so rare case, use the default content format as better approximation available.
                        $record->contentformat = FORMAT_MOODLE;
                    }
                }
                $record->verified = 1;
                if ($debug) {
                    echo html_writer::start_tag('pre');
                    echo 'I am at the line '.__LINE__.' of the file '.__FILE__.'<br />';
                    echo 'I am going to save to surveypro_answer:<br />';
                    echo '$record:';
                    var_dump($record);
                    echo html_writer::end_tag('pre');
                }
                $DB->insert_record('surveypro_answer', $record);
            }
            // End of: Add as many records to surveypro_answer as the number of elements in the surveypro (if answered).
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
