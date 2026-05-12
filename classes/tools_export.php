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
 * The tools_export class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;

/**
 * The class exporting gathered data
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tools_export
{
    /**
     * @var \stdClass Course module object
     */
    protected $cm;

    /**
     * @var \stdClass Context object
     */
    protected $context;

    /**
     * @var \stdClass Surveypro object
     */
    protected $surveypro;

    /**
     * @var \stdClass Form content as submitted by the user
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
     * Display the welcome message of the export page.
     *
     * @return void
     */
    public function welcome_message() {
        global $OUTPUT;

        $a = get_string('downloadformat', 'mod_surveypro');
        $message = get_string('welcome_dataexport', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Trigger the all_submissions_exported event.
     *
     * @return void
     */
    public function trigger_event() {
        $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
        $event = \mod_surveypro\event\all_submissions_exported::create($eventdata);
        $event->trigger();
    }

    /**
     * Build the SQL query to fetch submissions for export.
     *
     * Returns one row per submission. User fields are included unless the
     * survey is anonymous and $forceuserid is false.
     *
     * @param bool $forceuserid If true, user id is always included even on anonymous surveys.
     * @return array [$sql, $params]
     */
    private function build_submissions_sql(bool $forceuserid = false): array {
        global $USER, $COURSE;

        $canseeothers = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $groupmode    = groups_get_activity_groupmode($this->cm, $COURSE);

        $sql = 'SELECT s.id AS submissionid, s.status, s.timecreated, s.timemodified, u.id AS userid';
        if (empty($this->surveypro->anonymous) || $forceuserid) {
            $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');
            $sql .= $userfieldsapi->selects;
        }
        $sql .= ' FROM {surveypro_submission} s
                  JOIN {user} u ON u.id = s.userid';

        if ($canseeothers && $groupmode && !empty($this->formdata->groupid)) {
            $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid';
        }

        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $params = ['surveyproid' => $this->surveypro->id];

        if ($this->formdata->status != SURVEYPRO_STATUSALL) {
            $sql .= ' AND s.status = :status';
            $params['status'] = $this->formdata->status;
        }

        if ($canseeothers) {
            if ($groupmode && !empty($this->formdata->groupid)) {
                $sql .= ' AND gm.groupid = :groupid';
                $params['groupid'] = $this->formdata->groupid;
            }
        } else {
            $sql .= ' AND s.userid = :userid';
            $params['userid'] = $USER->id;
        }

        $sql .= ' ORDER BY s.id';

        return [$sql, $params];
    }

    /**
     * Build the SQL query to fetch answers for the given submission ids.
     *
     * Hidden and reserved items are excluded unless the corresponding
     * formdata properties are set. File upload items are excluded unless
     * the download type is SURVEYPRO_FILESBYUSER or SURVEYPRO_FILESBYITEM.
     *
     * @param int[] $submissionids List of surveypro_submission.id values to fetch answers for.
     * @return array [$sql, $params]
     */
    private function build_answers_sql(array $submissionids): array {
        global $DB;

        [$insql, $inparams] = $DB->get_in_or_equal($submissionids, SQL_PARAMS_NAMED, 'sid');

        $sql = 'SELECT a.id, a.submissionid, a.itemid, a.content, a.contentformat,
                       si.sortindex, si.plugin
                FROM {surveypro_answer} a
                JOIN {surveypro_item} si ON si.id = a.itemid
                WHERE a.submissionid ' . $insql;

        $params = $inparams;

        if (!isset($this->formdata->includehidden)) {
            $sql .= ' AND si.hidden = :hidden';
            $params['hidden'] = 0;
        }
        if (!isset($this->formdata->includereserved)) {
            $sql .= ' AND si.reserved = :reserved';
            $params['reserved'] = 0;
        }

        $isfiledownload = in_array($this->formdata->downloadtype, [SURVEYPRO_FILESBYUSER, SURVEYPRO_FILESBYITEM], true);
        $sql .= $isfiledownload
            ? ' AND si.plugin = :plugin'
            : ' AND si.plugin <> :plugin';
        $params['plugin'] = 'fileupload';

        $sql .= ' ORDER BY a.submissionid, a.itemid';

        return [$sql, $params];
    }

    /**
     * Get the query to export submissions with their answers.
     * Used by attachments_downloadbyuser() and attachments_downloadbyitem().
     *
     * @param bool $forceuserid Force inclusion of userid even on anonymous surveys.
     * @return array [$sql, $params]
     */
    private function get_export_sql(bool $forceuserid = false): array {
        global $USER, $COURSE;

        $canseeothers = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $groupmode    = groups_get_activity_groupmode($this->cm, $COURSE);

        $sql = 'SELECT s.id AS submissionid, s.status, s.timecreated, s.timemodified, ';
        if (empty($this->surveypro->anonymous) || $forceuserid) {
            $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');
            $sql .= 'u.id AS userid' . $userfieldsapi->selects . ', ';
        }
        $sql .= 'a.id AS id, a.itemid, a.content, a.contentformat,
                 si.sortindex, si.plugin
                 FROM {surveypro_submission} s
                   JOIN {user} u ON u.id = s.userid
                   LEFT JOIN {surveypro_answer} a ON a.submissionid = s.id
                   LEFT JOIN {surveypro_item} si ON si.id = a.itemid';

        if ($canseeothers && $groupmode && !empty($this->formdata->groupid)) {
            $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid';
        }

        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $params = ['surveyproid' => $this->surveypro->id];

        if (!isset($this->formdata->includehidden)) {
            $sql .= ' AND (si.hidden = :hidden OR si.hidden IS NULL)';
            $params['hidden'] = 0;
        }
        if (!isset($this->formdata->includereserved)) {
            $sql .= ' AND (si.reserved = :reserved OR si.reserved IS NULL)';
            $params['reserved'] = 0;
        }
        if ($this->formdata->status != SURVEYPRO_STATUSALL) {
            $sql .= ' AND s.status = :status';
            $params['status'] = $this->formdata->status;
        }

        $isfiledownload = in_array(
            $this->formdata->downloadtype,
            [SURVEYPRO_FILESBYUSER, SURVEYPRO_FILESBYITEM],
            true
        );
        $sql .= $isfiledownload ? ' AND si.plugin = :plugin' : ' AND si.plugin <> :plugin';
        $params['plugin'] = 'fileupload';

        if ($canseeothers) {
            if ($groupmode && !empty($this->formdata->groupid)) {
                $sql .= ' AND gm.groupid = :groupid';
                $params['groupid'] = $this->formdata->groupid;
            }
        } else {
            $sql .= ' AND s.userid = :userid';
            $params['userid'] = $USER->id;
        }

        if ($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) {
            $sql .= ' ORDER BY s.userid, submissionid, a.itemid';
        } else if ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM) {
            $sql .= ' ORDER BY a.itemid, s.userid, submissionid';
        } else {
            $sql .= ' ORDER BY submissionid';
        }

        return [$sql, $params];
    }

    /**
     * Fetch submissions and send them to output in xls or csv.
     *
     * @return $exporterror
     */
    public function submissions_export() {

        // Do I need to filter groups?

        if ($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) {
            if ($errorreturned = $this->attachments_downloadbyuser()) {
                return $errorreturned;
            }
            die();
        }
        if ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM) {
            if ($errorreturned = $this->attachments_downloadbyitem()) {
                return $errorreturned;
            }
            die();
        }

        // CSV, TSV, XLS: i metodi gestiscono tutto internamente.
        if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
            $this->output_to_xls();
        } else {
            $this->output_to_csv();
        }
    }

    /**
     * Provide the base name for the file to export
     *
     * @param string $extension
     * @return void
     */
    public function get_export_filename($extension = '') {
        $filename = format_text($this->surveypro->name, FORMAT_PLAIN);
        $filename = str_replace(' ', '_', $filename);

        if ($this->formdata->status == SURVEYPRO_STATUSCLOSED) {
            $filename .= ' ' . str_replace(' ', '', get_string('statusclosed', 'surveypro'));
        }
        if ($this->formdata->status == SURVEYPRO_STATUSINPROGRESS) {
            $filename .= ' ' . str_replace(' ', '', get_string('statusinprogress', 'surveypro'));
        }
        if ($this->formdata->outputstyle == SURVEYPRO_VERBOSE) {
            $filename .= ' verbose';
        }
        $filename .= userdate(time(), ' %Y%m%d%H%M', 99, false, false);
        if ($extension) {
            $filename .= '.' . $extension;
        }

        return $filename;
    }

    /**
     * Get headers and placeholders for the output.
     *
     * @return void
     */
    public function export_get_output_headers() {
        global $DB;

        $itemseeds = $this->export_get_field_list();
        $headerlabels = [];
        if (empty($this->surveypro->anonymous)) {
            $headerlabels[] = SURVEYPRO_OWNERIDLABEL;
            if (isset($this->formdata->includenames)) {
                $headerlabels[] = get_string('firstname');
                $headerlabels[] = get_string('lastname');
            }
        }

        $itemseedskeys = [];
        foreach ($itemseeds as $itemseed) {
            $where = ['id' => $itemseed->id];
            $currentheader = $DB->get_field('surveypro_item', 'variable', $where);
            $headerlabels[] = $currentheader;
            $itemseedskeys[] = $itemseed->id;
            if ($this->formdata->outputstyle == SURVEYPRO_RAW) {
                $classname = 'surveypro' . SURVEYPRO_TYPEFIELD . '_' . $itemseed->plugin . '\item';
                if ($classname::response_uses_format()) {
                    $headerlabels[] = $currentheader . SURVEYPRO_IMPFORMATSUFFIX;
                    $itemseedskeys[] = $itemseed->id . SURVEYPRO_IMPFORMATSUFFIX;
                }
            }
        }

        if (isset($this->formdata->includedates)) {
            $headerlabels[] = SURVEYPRO_TIMECREATEDLABEL;
            $headerlabels[] = SURVEYPRO_TIMEMODIFIEDLABEL;
        }

        // Define once and forever $placeholders.
        if ($this->formdata->outputstyle == SURVEYPRO_VERBOSE) {
            $answernotprovided = get_string('answernotsubmitted', 'mod_surveypro');
        } else {
            $answernotprovided = SURVEYPRO_EXPNULLVALUE;
        }
        $placeholders = array_fill_keys($itemseedskeys, $answernotprovided);
        // End of: Define once and forever $placeholders.

        return [$headerlabels, $placeholders];
    }

    /**
     * Print given submissions to csv file and make it available.
     *
     * @return void
     */
    public function output_to_csv(): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/csvlib.class.php');

        $sep = ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADCSV) ? 'comma' : 'tab';
        $csvexport = new \csv_export_writer($sep);
        $csvexport->filename = $this->get_export_filename('csv');

        [$headerlabels, $placeholders] = $this->export_get_output_headers();
        $csvexport->add_data($headerlabels);

        // Query 1: submissions (N rows, one per each submission).
        [$submissionssql, $submissionsparams] = $this->build_submissions_sql();
        $submissions = $DB->get_records_sql($submissionssql, $submissionsparams);
        // $submissions è keyed by submissionid (the returned 'id' field).

        if (empty($submissions)) {
            $csvexport->download_file();
            die();
        }

        // Query 2: answers, only for the submissions found.
        $submissionids = array_keys($submissions);

        [$answerssql, $answersparams] = $this->build_answers_sql($submissionids);
        $answersrs = $DB->get_recordset_sql($answerssql, $answersparams);

        // Group answers by submission id.
        // Let's use a recordset to avoid overloading the RAM when working with huge datasets.
        $answersbysubmission = []; // [submissionid => [itemid => richsubmission_row]]
        foreach ($answersrs as $answer) {
            $answersbysubmission[$answer->submissionid][$answer->itemid] = $answer;
        }
        $answersrs->close();

        // Assembly and writing.
        foreach ($submissions as $submissionid => $submission) {
            $recordtoexport = $this->export_begin_newrecord($submission, $placeholders);

            if (isset($answersbysubmission[$submissionid])) {
                foreach ($answersbysubmission[$submissionid] as $answer) {
                    $this->export_populate_currentrecord($answer, $recordtoexport);
                }
            }

            $csvexport->add_data($recordtoexport);
        }

        // Release immediately.
        unset($answersbysubmission);

        $csvexport->download_file();
        die();
    }

    /**
     * Print given submissions to xls file and make it available.
     *
     * @return void
     */
    public function output_to_xls(): void {
        global $CFG, $DB;

        require_once($CFG->libdir . '/excellib.class.php');

        $filename = $this->get_export_filename('xls');
        $workbook = new \MoodleExcelWorkbook('-');
        $workbook->send($filename);
        $worksheet = [];
        $worksheet[0] = $workbook->add_worksheet(get_string('surveypro', 'mod_surveypro'));

        [$headerlabels, $placeholders] = $this->export_get_output_headers();
        $rowcounter = 0;
        $this->export_write_xlsrecord($rowcounter, $headerlabels, $worksheet);

        // Query 1: submissions.
        [$submissionssql, $submissionsparams] = $this->build_submissions_sql();
        $submissions = $DB->get_records_sql($submissionssql, $submissionsparams);

        if (empty($submissions)) {
            $workbook->close();
            die();
        }

        // Query 2: answers.
        [$answerssql, $answersparams] = $this->build_answers_sql(array_keys($submissions));
        $answersrs = $DB->get_recordset_sql($answerssql, $answersparams);

        $answersbysubmission = [];
        foreach ($answersrs as $answer) {
            $answersbysubmission[$answer->submissionid][$answer->itemid] = $answer;
        }
        $answersrs->close();

        foreach ($submissions as $submissionid => $submission) {
            $recordtoexport = $this->export_begin_newrecord($submission, $placeholders);

            if (isset($answersbysubmission[$submissionid])) {
                foreach ($answersbysubmission[$submissionid] as $answer) {
                    $this->export_populate_currentrecord($answer, $recordtoexport);
                }
            }

            $rowcounter++;
            $this->export_write_xlsrecord($rowcounter, $recordtoexport, $worksheet);
        }

        unset($answersbysubmission);

        $workbook->close();
        die();
    }

    /**
     * Fetch all fileupload files for the given answer ids in a single query.
     *
     * @param int[] $answerids List of surveypro_answer.id values used as filearea itemid.
     * @return array [answerid => stored_file[]] indexed by answer id.
     */
    private function fetch_all_attachment_files(array $answerids): array {
        global $DB;

        if (empty($answerids)) {
            return [];
        }

        $fs = get_file_storage();

        [$insql, $inparams] = $DB->get_in_or_equal($answerids, SQL_PARAMS_NAMED, 'aid');
        $inparams['contextid']  = $this->context->id;
        $inparams['component']  = 'surveyprofield_fileupload';
        $inparams['filearea']   = 'fileuploadfiles';

        $sql = 'SELECT * FROM {files}
                WHERE contextid = :contextid
                  AND component = :component
                  AND filearea  = :filearea
                  AND itemid ' . $insql . "
                  AND filename <> '.'
                ORDER BY itemid, timemodified";

        $filerecords = $DB->get_records_sql($sql, $inparams);

        // Group by itemid (= answerid).
        $result = [];
        foreach ($filerecords as $filerecord) {
            $result[$filerecord->itemid][] = $fs->get_file_instance($filerecord);
        }

        return $result;
    }

    /**
     * Get seeds of fields (items) of data going to be exporeted.
     *
     * @return void
     */
    public function export_get_field_list() {
        global $DB;

        // No matter for the page.
        $where = [];
        $where['surveyproid'] = $this->surveypro->id;
        $where['type'] = SURVEYPRO_TYPEFIELD;
        if (!isset($this->formdata->includereserved)) {
            $where['reserved'] = 0;
        }
        if (!isset($this->formdata->includehidden)) {
            $where['hidden'] = 0;
        }
        if (
            ($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) ||
            ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM)
        ) {
            $where['plugin'] = 'fileupload';
            if (!$itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, plugin')) {
                return SURVEYPRO_NOFIELDSSELECTED;
            }
        } else {
            $conditions = [];
            foreach ($where as $field => $unused) {
                $conditions[] = $field . ' = :' . $field;
            }
            $select = implode(' AND ', $conditions);

            $where['plugin'] = 'fileupload';
            $select .= ' AND plugin <> :plugin';

            if (!$itemseeds = $DB->get_records_select('surveypro_item', $select, $where, 'sortindex', 'id, plugin')) {
                return SURVEYPRO_NOFIELDSSELECTED;
            }
        }

        return $itemseeds;
    }

    /**
     * Add the ownerid to the structure of the table to export.
     *
     * @param array $richsubmission
     * @return $owner
     */
    public function export_add_ownerid($richsubmission) {
        $owner = [];
        if (empty($this->surveypro->anonymous)) {
            // If NOT anonymous.
            $owner[SURVEYPRO_OWNERIDLABEL] = $richsubmission->userid;
        }

        return $owner;
    }

    /**
     * Add first and last name of the owner to the structure of the table to export.
     *
     * @param array $richsubmission
     * @return array $names
     */
    public function export_add_names($richsubmission) {
        $names = [];
        if (empty($this->surveypro->anonymous) && isset($this->formdata->includenames)) {
            $names['firstname'] = $richsubmission->firstname;
            $names['lastname'] = $richsubmission->lastname;
        }

        return $names;
    }

    /**
     * Add timecreated and/or timemodified to the structure of the table to export.
     *
     * @param array $richsubmission
     * @return array $dates
     */
    public function export_add_dates($richsubmission) {
        $dates = [];
        if (isset($this->formdata->includedates)) {
            if ($this->formdata->outputstyle == SURVEYPRO_VERBOSE) {
                $dates['timecreated'] = userdate($richsubmission->timecreated);
                if ($richsubmission->timemodified) {
                    $dates['timemodified'] = userdate($richsubmission->timemodified);
                } else {
                    $dates['timemodified'] = get_string('never');
                }
            } else {
                $dates['timecreated'] = $richsubmission->timecreated;
                if ($richsubmission->timemodified) {
                    $dates['timemodified'] = $richsubmission->timemodified;
                } else {
                    $dates['timemodified'] = null;
                }
            }
        }

        return $dates;
    }

    /**
     * Create a new record to export
     *
     * @param array $richsubmission
     * @param object $placeholders
     * @return void
     */
    public function export_begin_newrecord($richsubmission, $placeholders) {
        $recordtoexport = [];
        $recordtoexport += $this->export_add_ownerid($richsubmission);
        $recordtoexport += $this->export_add_names($richsubmission);
        $recordtoexport += $placeholders;
        $recordtoexport += $this->export_add_dates($richsubmission);

        return $recordtoexport;
    }

    /**
     * Populate the record to export
     *
     * @param array $richsubmission
     * @param object $recordtoexport
     * @return void
     */
    public function export_populate_currentrecord($richsubmission, &$recordtoexport) {
        if ($this->formdata->outputstyle == SURVEYPRO_VERBOSE) {
            $recordtoexport[$richsubmission->itemid] = $this->decode_content($richsubmission);
        } else {
            $recordtoexport[$richsubmission->itemid] = $richsubmission->content;

            $classname = 'surveypro' . SURVEYPRO_TYPEFIELD . '_' . $richsubmission->plugin . '\item';
            if ($classname::response_uses_format()) {
                $recordtoexport[$richsubmission->itemid . SURVEYPRO_IMPFORMATSUFFIX] = $richsubmission->contentformat;
            }
        }
    }

    /**
     * Write to xls file the passed record.
     *
     * @param int $row
     * @param array $recordtoexport
     * @param object $worksheet
     * @return void
     */
    public function export_write_xlsrecord($row, $recordtoexport, &$worksheet) {
        $col = 0;
        foreach ($recordtoexport as $value) {
            if (is_numeric($value)) {
                $worksheet[0]->write_number($row, $col, $value);
            } else {
                $worksheet[0]->write_string($row, $col, $value);
            }
            $col++;
        }
    }

    /**
     * If it was required SURVEYPRO_VERBOSE output, change numbers to verbose explanations.
     *
     * @param array $richsubmission
     * @return void
     */
    public function decode_content($richsubmission) {
        $content = $richsubmission->content;
        if (!core_text::strlen($content)) {
            $return = '';
        } else {
            $itemid = $richsubmission->itemid;
            $plugin = $richsubmission->plugin;
            $item = surveypro_get_itemclass($this->cm, $this->surveypro, $itemid, SURVEYPRO_TYPEFIELD, $plugin);
            $return = $item->userform_db_to_export($richsubmission);
        }

        return $return;
    }

    /**
     * Check if attachments were added to the current surveypro
     *
     * @return boolean
     */
    public function are_attachments_onboard() {
        global $DB;

        $whereparams = ['surveyproid' => $this->surveypro->id, 'plugin' => 'fileupload'];
        $counter = $DB->count_records('surveypro_item', $whereparams);

        return ($counter > 0);
    }

    /**
     * Define the name of the file to download starting from the name of this surveypro instance.
     *
     * @param string $type either 'user' or 'item'
     * @return string $packagename
     */
    public function attachments_define_packagename($type) {
        if (($type != 'user') && ($type != 'item')) {
            $message = 'Wrong param passed to attachments_define_packagename';
            debugging($message, DEBUG_DEVELOPER);
        }

        $packagename = clean_filename($this->surveypro->name);
        $packagename = clean_param($packagename, PARAM_ALPHAEXT);
        $packagename .= '_attachments_by_' . $type;
        // In MS Azure files with a name longer than 80 characters give problems.
        $packagename = \core_text::substr($packagename, 0, 80);

        return $packagename;
    }

    /**
     * Craft each uploaded attachment by user and compress the package.
     *
     * @return void
     */
    public function attachments_downloadbyuser() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/surveypro/field/fileupload/lib.php');

        $anonymousstr = get_string('anonymous', 'mod_surveypro');
        $itemstr = get_string('item', 'mod_surveypro');
        $submissionstr = get_string('submission', 'mod_surveypro');
        $dummyuserid = 0;
        $dirnames = [];
        $filelist = [];

        $fs = get_file_storage();
        [$richsubmissionssql, $whereparams] = $this->get_export_sql(true);
        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);

        if (!$richsubmissions->valid()) {
            $richsubmissions->close();
            return SURVEYPRO_NOATTACHMENTFOUND;
        }

        // Materialise the recordset: collect all answer ids for the batch file query,
        // then iterate $rows to build the directory tree.
        // File-upload answers are typically few, so holding them in memory is fine.
        $rows = [];
        $answerids = [];
        foreach ($richsubmissions as $row) {
            $rows[] = $row;
            $answerids[] = $row->id; // surveypro_answer.id == filearea itemid
        }
        $richsubmissions->close();

        // Load all attachment files in one query instead of one query per answer.
        $allfiles = $this->fetch_all_attachment_files($answerids);

        $packagename = $this->attachments_define_packagename('user');

        $tempsubdir = '/mod_surveypro/attachmentsexport/' . $packagename;
        $tempbasedir = $CFG->tempdir . $tempsubdir;

        $currentsubmissionid = 0;
        $olduserid = 0;
        foreach ($rows as $richsubmission) {
            // Itemid always changes so, I look at submissionid.
            if ($currentsubmissionid != $richsubmission->submissionid) {
                // New submissionid.
                if ($olduserid != $richsubmission->userid) {
                    // New user.
                    // Add a new folder named fullname($richsubmission).'_'.$richsubmission->userid.
                    if (!empty($this->surveypro->anonymous)) {
                        $dummyuserid++;
                        $tempuserdir = $anonymousstr . '_' . $dummyuserid;
                    } else {
                        $tempuserdir = fullname($richsubmission) . '_' . $richsubmission->userid;
                    }
                    $tempuserdir = str_replace(' ', '_', $tempuserdir);
                    $temppath = $tempsubdir . '/' . $tempuserdir;
                    make_temp_directory($temppath);
                    $dirnames[] = $temppath;

                    $olduserid = $richsubmission->userid;
                }

                // Add a new folder named $richsubmission->submissionid.
                $tempsubmissiondir = $submissionstr . '_' . $richsubmission->submissionid;
                $tempsubmissiondir = str_replace(' ', '_', $tempsubmissiondir);
                $temppath = $tempsubdir . '/' . $tempuserdir . '/' . $tempsubmissiondir;
                make_temp_directory($temppath);
                $dirnames[] = $temppath;

                $currentsubmissionid = $richsubmission->submissionid;
            }

            // Add a new folder named $itemid.
            $tempitemdir = $itemstr . '_' . $richsubmission->itemid;
            $tempitemdir = str_replace(' ', '_', $tempitemdir);
            $currentfilepath = $tempuserdir . '/' . $tempsubmissiondir . '/' . $tempitemdir;
            $temppath = $tempsubdir . '/' . $currentfilepath;
            make_temp_directory($temppath);
            $dirnames[] = $temppath;

            $tempfullpath = $CFG->tempdir . '/' . $temppath;
            // Finally add the attachment.
            if (!empty($allfiles[$richsubmission->id])) {
                foreach ($allfiles[$richsubmission->id] as $file) {
                    $filename = $file->get_filename();
                    $file->copy_content_to($tempfullpath . '/' . $filename);
                    $filelist[$packagename . '/' . $currentfilepath . '/' . $filename] = $tempfullpath . '/' . $filename;
                }
            }
        }

        // Continue making zip file available ONLY IF selection was valid.
        $exportfile = $tempbasedir . '.zip';
        file_exists($exportfile) && unlink($exportfile);

        $fp = get_file_packer('application/zip');
        $fp->archive_to_pathname($filelist, $exportfile);

        foreach ($filelist as $file) {
            unlink($file);
        }
        $dirnames = array_reverse($dirnames);
        foreach ($dirnames as $dir) {
            rmdir($CFG->tempdir . $dir);
        }
        rmdir($tempbasedir);

        $this->makezip_available($exportfile);
    }

    /**
     * Craft each uploaded attachment by item and compress the package.
     *
     * @return void
     */
    public function attachments_downloadbyitem() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/surveypro/field/fileupload/lib.php');

        $anonymousstr = get_string('anonymous', 'mod_surveypro');
        $itemstr = get_string('item', 'mod_surveypro');
        $submissionstr = get_string('submission', 'mod_surveypro');
        $dummyuserid = 0;
        $dirnames = [];
        $filelist = [];

        $fs = get_file_storage();
        [$richsubmissionssql, $whereparams] = $this->get_export_sql(true);
        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);

        if (!$richsubmissions->valid()) {
            $richsubmissions->close();
            return SURVEYPRO_NOATTACHMENTFOUND;
        }

        // Materialise the recordset: collect all answer ids for the batch file query,
        // then iterate $rows to build the directory tree.
        // File-upload answers are typically few, so holding them in memory is fine.
        $rows = [];
        $answerids = [];
        foreach ($richsubmissions as $row) {
            $rows[] = $row;
            $answerids[] = $row->id; // surveypro_answer.id == filearea itemid
        }
        $richsubmissions->close();

        // Load all attachment files in one query instead of one query per answer.
        $allfiles = $this->fetch_all_attachment_files($answerids);

        $packagename = $this->attachments_define_packagename('item');

        $tempsubdir = '/mod_surveypro/attachmentsexport/' . $packagename;
        $tempbasedir = $CFG->tempdir . $tempsubdir;

        $olduserid = 0;
        $olditemid = 0;
        $forcenewuserfolder = false;
        foreach ($rows as $richsubmission) {
            if ($olditemid != $richsubmission->itemid) {
                // New item.
                // Add a new folder named 'element_'.$richsubmission->itemid.
                $tempitemdir = $itemstr . '_' . $richsubmission->itemid;
                $tempitemdir = str_replace(' ', '_', $tempitemdir);
                $temppath = $tempsubdir . '/' . $tempitemdir;
                make_temp_directory($temppath);
                $dirnames[] = $temppath;

                $olditemid = $richsubmission->itemid;
                $forcenewuserfolder = true;
            }

            if (($olduserid != $richsubmission->userid) || ($forcenewuserfolder)) {
                $forcenewuserfolder = false;

                // New user or forced by new item.
                // Add a new folder named $richsubmission->userid.
                if (!empty($this->surveypro->anonymous)) {
                    $dummyuserid++;
                    $tempuserdir = $anonymousstr . '_' . $dummyuserid;
                } else {
                    $tempuserdir = fullname($richsubmission) . '_' . $richsubmission->userid;
                }
                $tempuserdir = str_replace(' ', '_', $tempuserdir);
                $temppath = $tempsubdir . '/' . $tempitemdir . '/' . $tempuserdir;
                make_temp_directory($temppath);
                $dirnames[] = $temppath;

                $olduserid = $richsubmission->userid;
            }

            // Add a new folder named $richsubmission->submissionid.
            $tempsubmissiondir = $submissionstr . '_' . $richsubmission->submissionid;
            $tempsubmissiondir = str_replace(' ', '_', $tempsubmissiondir);
            $currentfilepath = $tempitemdir . '/' . $tempuserdir . '/' . $tempsubmissiondir;
            $temppath = $tempsubdir . '/' . $currentfilepath;
            make_temp_directory($temppath);
            $dirnames[] = $temppath;

            $tempfullpath = $CFG->tempdir . '/' . $temppath;
            // Finally add the attachment.
            if (!empty($allfiles[$richsubmission->id])) {
                foreach ($allfiles[$richsubmission->id] as $file) {
                    $filename = $file->get_filename();
                    $file->copy_content_to($tempfullpath . '/' . $filename);
                    $filelist[$packagename . '/' . $currentfilepath . '/' . $filename] = $tempfullpath . '/' . $filename;
                }
            }
        }

        // Continue making zip file available ONLY IF selection was valid.
        $exportfile = $tempbasedir . '.zip';
        file_exists($exportfile) && unlink($exportfile);

        $fp = get_file_packer('application/zip');
        $fp->archive_to_pathname($filelist, $exportfile);

        foreach ($filelist as $file) {
            unlink($file);
        }
        $dirnames = array_reverse($dirnames, true);
        foreach ($dirnames as $dir) {
            rmdir($CFG->tempdir . $dir);
        }
        rmdir($tempbasedir);

        $this->makezip_available($exportfile);
    }

    /**
     * Make the zip file available.
     *
     * @param string $exportfile File to make available
     * @return void
     */
    public function makezip_available($exportfile) {
        $exportfilename = basename($exportfile);
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$exportfilename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        header('Pragma: public');
        $exportfilehandler = fopen($exportfile, 'rb');
        print fread($exportfilehandler, filesize($exportfile));
        fclose($exportfilehandler);
        unlink($exportfile);
    }
}
