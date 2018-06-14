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
 * The exportmanager class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class exporting gathered data
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_view_export {

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
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_exported::create($eventdata);
        $event->trigger();
    }

    /**
     * Get the query to export submissions.
     *
     * @param bool $forceuserid
     * @return void
     */
    public function get_export_sql($forceuserid=false) {
        global $USER, $COURSE;

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);

        $sql = 'SELECT s.id as submissionid, s.status, s.timecreated, s.timemodified, ';
        if (empty($this->surveypro->anonymous) || ($forceuserid)) {
            $sql .= 'u.id as userid, '.user_picture::fields('u').', ';
        }
        $sql .= 'a.id as id, a.itemid, a.content, a.contentformat,
                 si.sortindex, si.plugin
                 FROM {surveypro_submission} s
                   JOIN {user} u ON u.id = s.userid
                   LEFT JOIN {surveypro_answer} a ON a.submissionid = s.id
                   LEFT JOIN {surveypro_item} si ON si.id = a.itemid';

        // If !$canseeotherssubmissions do not overload the query with useless conditions.
        if ($canseeotherssubmissions) {
            if ($groupmode) { // Activity is divided into groups.
                if (!empty($this->formdata->groupid)) {
                    $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid ';
                }
            }
        }

        // Now finalise $sql.
        $sql .= ' WHERE s.surveyproid = :surveyproid
                      AND a.verified = :verified';
        $whereparams['surveyproid'] = $this->surveypro->id;
        $whereparams['verified'] = 1;

        // For IN PROGRESS submissions where no fields were filled I need the LEFT JOIN {surveypro_item}.
        // In this case,
        // If I add a clause for fields of UNEXISTING {surveypro_item} (because no fields was filled)
        // I will miss the record if I do not further add OR si.xxxx IS NULL.
        if (!isset($this->formdata->includehidden)) {
            $sql .= ' AND (si.hidden = :hidden OR si.hidden IS NULL)';
            $whereparams['hidden'] = 0;
        }
        if (!isset($this->formdata->includereserved)) {
            $sql .= ' AND (si.reserved = :reserved OR si.reserved IS NULL)';
            $whereparams['reserved'] = 0;
        }
        if ($this->formdata->status != SURVEYPRO_STATUSALL) {
            $sql .= ' AND s.status = :status';
            $whereparams['status'] = $this->formdata->status;
        }
        if (($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) ||
            ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM)) {
            $sql .= ' AND si.plugin = :plugin';
        } else {
            $sql .= ' AND si.plugin <> :plugin';
        }
        $whereparams['plugin'] = 'fileupload';

        // If !$canseeotherssubmissions do not overload the query with useless conditions.
        if ($canseeotherssubmissions) {
            if ($groupmode) { // Activity is divided into groups.
                if (!empty($this->formdata->groupid)) {
                    $sql .= ' AND gm.groupid = :groupid';
                    $whereparams['groupid'] = $this->formdata->groupid;
                }
            }
        } else {
            // Restrict to your submissions only.
            $sql .= ' AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        if ($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) {
            $sql .= ' ORDER BY s.userid, submissionid, a.itemid';
        } else if ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM) {
            $sql .= ' ORDER BY a.itemid, s.userid, submissionid';
        } else {
            $sql .= ' ORDER BY submissionid';
        }

        return array($sql, $whereparams);
    }

    /**
     * Fetch submissions and send them to output in xls or csv.
     *
     * @return $exporterror
     */
    public function submissions_export() {
        global $DB;

        // Do I need to filter groups?
        // $filtergroups = surveypro_need_group_filtering($this->cm, $this->context);

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

        list($richsubmissionssql, $whereparams) = $this->get_export_sql(false);
        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);

        if ($richsubmissions->valid()) {
            if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
                $this->output_to_xls($richsubmissions);
            } else { // SURVEYPRO_DOWNLOADCSV or SURVEYPRO_DOWNLOADTSV.
                $this->output_to_csv($richsubmissions);
            }
        } else {
            return SURVEYPRO_NORECORDSFOUND;
        }
    }

    /**
     * Provide the base name for the file to export
     *
     * @param string $extension
     * @return void
     */
    public function get_export_filename($extension = '') {
        $filename = $this->surveypro->name;
        if ($this->formdata->outputstyle == SURVEYPRO_VERBOSE) {
            $filename .= '_verbose';
        } else {
            $filename .= '_raw';
        }
        $filename .= '.'.$extension;
        $filename = clean_filename($filename);

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
        $headerlabels = array();
        if (empty($this->surveypro->anonymous)) {
            $headerlabels[] = SURVEYPRO_OWNERIDLABEL;
            if (isset($this->formdata->includenames)) {
                $headerlabels[] = get_string('firstname');
                $headerlabels[] = get_string('lastname');
            }
        }

        $itemseedskeys = array();
        foreach ($itemseeds as $itemseed) {
            $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemseed->plugin;
            $where = array('itemid' => $itemseed->id);
            $currentheader = $DB->get_field($tablename, 'variable', $where);
            $headerlabels[] = $currentheader;
            $itemseedskeys[] = $itemseed->id;
            if ($this->formdata->outputstyle == SURVEYPRO_RAW) {
                $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemseed->plugin.'_'.SURVEYPRO_TYPEFIELD;
                if ($classname::item_needs_contentformat()) {
                    $headerlabels[] = $currentheader.SURVEYPRO_IMPFORMATSUFFIX;
                    $itemseedskeys[] = $itemseed->id.SURVEYPRO_IMPFORMATSUFFIX;
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

        return array($headerlabels, $placeholders);
    }

    /**
     * Print given submissions to csv file and make it available.
     *
     * @param array $richsubmissions
     * @return void
     */
    public function output_to_csv($richsubmissions) {
        global $CFG, $DB;

        require_once($CFG->libdir.'/csvlib.class.php');

        if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADCSV) {
            $csvexport = new csv_export_writer('comma');
        } else {
            $csvexport = new csv_export_writer('tab');
        }

        $csvexport->filename = $this->get_export_filename('csv');

        // Get headers and placeholders.
        list($headerlabels, $placeholders) = $this->export_get_output_headers();
        $csvexport->add_data($headerlabels);

        $currentsubmissionid = 0;
        foreach ($richsubmissions as $richsubmission) {
            if ($currentsubmissionid != $richsubmission->submissionid) {
                if (!empty($currentsubmissionid)) { // New richsubmissionid, stop managing old record.
                    // Write old record.
                    $csvexport->add_data($recordtoexport);
                }

                // Update the reference.
                $currentsubmissionid = $richsubmission->submissionid;

                // Begin a new record.
                $recordtoexport = $this->export_begin_newrecord($richsubmission, $placeholders);
            }

            $this->export_populate_currentrecord($richsubmission, $recordtoexport);
        }
        $richsubmissions->close();

        $csvexport->add_data($recordtoexport);
        $csvexport->download_file();
        die();
    }

    /**
     * Print given submissions to xls file and make it available.
     *
     * @param array $richsubmissions
     * @return void
     */
    public function output_to_xls($richsubmissions) {
        global $CFG, $DB;

        require_once($CFG->libdir.'/excellib.class.php');

        $filename = $this->get_export_filename('xls');

        $workbook = new MoodleExcelWorkbook('-');
        $workbook->send($filename);

        $worksheet = array();
        $worksheet[0] = $workbook->add_worksheet(get_string('surveypro', 'mod_surveypro'));

        $itemseeds = $this->export_get_field_list();

        // Get headers and placeholders.
        list($headerlabels, $placeholders) = $this->export_get_output_headers();
        $rowcounter = 0;
        $this->export_write_xlsrecord($rowcounter, $headerlabels, $worksheet);

        $currentsubmissionid = 0;
        foreach ($richsubmissions as $richsubmission) {
            if ($currentsubmissionid != $richsubmission->submissionid) {
                if (!empty($currentsubmissionid)) { // New richsubmissionid, stop managing old record.
                    // Write current record.
                    $rowcounter++;
                    $this->export_write_xlsrecord($rowcounter, $recordtoexport, $worksheet);
                }

                // Update the reference.
                $currentsubmissionid = $richsubmission->submissionid;

                // Begin a new record.
                $recordtoexport = $this->export_begin_newrecord($richsubmission, $placeholders);
            }

            $this->export_populate_currentrecord($richsubmission, $recordtoexport);
        }
        $richsubmissions->close();

        $rowcounter++;
        $this->export_write_xlsrecord($rowcounter, $recordtoexport, $worksheet);

        $workbook->close();
        return true;
    }

    /**
     * Get seeds of fields (items) of data going to be exporeted.
     *
     * @return void
     */
    public function export_get_field_list() {
        global $DB;

        // No matter for the page.
        $where = array();
        $where['surveyproid'] = $this->surveypro->id;
        $where['type'] = SURVEYPRO_TYPEFIELD;
        if (!isset($this->formdata->includereserved)) {
            $where['reserved'] = 0;
        }
        if (!isset($this->formdata->includehidden)) {
            $where['hidden'] = 0;
        }
        if (($this->formdata->downloadtype == SURVEYPRO_FILESBYUSER) ||
            ($this->formdata->downloadtype == SURVEYPRO_FILESBYITEM)) {
            $where['plugin'] = 'fileupload';
            if (!$itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, plugin')) {
                return SURVEYPRO_NOFIELDSSELECTED;
                die(); // Never reached.
            }
        } else {
            $conditions = array();
            foreach ($where as $field => $value) {
                $conditions[] = $field.' = :'.$field;
            }
            $select = implode(' AND ', $conditions);

            $where['plugin'] = 'fileupload';
            $select .= ' AND plugin <> :plugin';

            if (!$itemseeds = $DB->get_records_select('surveypro_item', $select, $where, 'sortindex', 'id, plugin')) {
                return SURVEYPRO_NOFIELDSSELECTED;
                die(); // Never reached.
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
        $owner = array();
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
        $names = array();
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
        $dates = array();
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
        $recordtoexport = array();
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

            $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$richsubmission->plugin.'_'.SURVEYPRO_TYPEFIELD;
            if ($classname::item_needs_contentformat()) {
                $recordtoexport[$richsubmission->itemid.SURVEYPRO_IMPFORMATSUFFIX] = $richsubmission->contentformat;
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
            if ($value == SURVEYPRO_EXPNULLVALUE) {
                $worksheet[0]->write_string($row, $col, $value);
            } else {
                $worksheet[0]->write($row, $col, $value, '');
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
        if (!strlen($content)) {
            $return = '';
        } else {
            $itemid = $richsubmission->itemid;
            $plugin = $richsubmission->plugin;
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemid, SURVEYPRO_TYPEFIELD, $plugin);
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

        $whereparams = array('surveyproid' => $this->surveypro->id, 'plugin' => 'fileupload');
        $counter = $DB->count_records('surveypro_item', $whereparams);

        return ($counter > 0);
    }

    /**
     * Define the name of the file to download starting from the name of this surveypro instance.
     *
     * @param string $type, either 'user' or 'item'
     * @return string $packagename
     */
    public function attachments_define_packagename($type) {
        if (($type != 'user') && ($type != 'item')) {
            $message = 'Wrong param passed to attachments_define_packagename';
            debugging($message, DEBUG_DEVELOPER);
        }

        $packagename = clean_filename($this->surveypro->name);
        $packagename = clean_param($packagename, PARAM_ALPHAEXT);
        $packagename .= '_attachments_by_'.$type;
        // In MS Azure files with a name longer than 80 characters give problems.
        $packagename = substr($packagename, 0, 80);

        return $packagename;
    }

    /**
     * Craft each uploaded attachment by user and compress the package.
     *
     * @return void
     */
    public function attachments_downloadbyuser() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

        $anonymousstr = get_string('anonymous', 'mod_surveypro');
        $itemstr = get_string('item', 'mod_surveypro');
        $submissionstr = get_string('submission', 'mod_surveypro');
        $dummyuserid = 0;
        $dirnames = array();
        $filelist = array();

        $fs = get_file_storage();
        list($richsubmissionssql, $whereparams) = $this->get_export_sql(true);
        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);

        if ($richsubmissions->valid()) {
            $packagename = $this->attachments_define_packagename('user');

            $tempsubdir = '/mod_surveypro/attachmentsexport/'.$packagename;
            $tempbasedir = $CFG->tempdir.$tempsubdir;

            $currentsubmissionid = 0;
            $olduserid = 0;
            foreach ($richsubmissions as $richsubmission) {
                // Itemid always changes so, I look at submissionid.
                if ($currentsubmissionid != $richsubmission->submissionid) {
                    // New submissionid.
                    if ($olduserid != $richsubmission->userid) {
                        // New user.
                        // Add a new folder named fullname($richsubmission).'_'.$richsubmission->userid.
                        if (!empty($this->surveypro->anonymous)) {
                            $dummyuserid++;
                            $tempuserdir = $anonymousstr.'_'.$dummyuserid;
                        } else {
                            $tempuserdir = fullname($richsubmission).'_'.$richsubmission->userid;
                        }
                        $tempuserdir = str_replace(' ', '_', $tempuserdir);
                        $temppath = $tempsubdir.'/'.$tempuserdir;
                        make_temp_directory($temppath);
                        $dirnames[] = $temppath;

                        $olduserid = $richsubmission->userid;
                    }

                    // Add a new folder named $richsubmission->submissionid.
                    $tempsubmissiondir = $submissionstr.'_'.$richsubmission->submissionid;
                    $tempsubmissiondir = str_replace(' ', '_', $tempsubmissiondir);
                    $temppath = $tempsubdir.'/'.$tempuserdir.'/'.$tempsubmissiondir;
                    make_temp_directory($temppath);
                    $dirnames[] = $temppath;

                    $currentsubmissionid = $richsubmission->submissionid;
                }

                // Add a new folder named $itemid.
                $tempitemdir = $itemstr.'_'.$richsubmission->itemid;
                $tempitemdir = str_replace(' ', '_', $tempitemdir);
                $currentfilepath = $tempuserdir.'/'.$tempsubmissiondir.'/'.$tempitemdir;
                $temppath = $tempsubdir.'/'.$currentfilepath;
                make_temp_directory($temppath);
                $dirnames[] = $temppath;

                $tempfullpath = $CFG->tempdir.'/'.$temppath;
                // Finally add the attachment.
                $component = 'surveyprofield_fileupload';
                $filearea = SURVEYPROFIELD_FILEUPLOAD_FILEAREA;
                $itemid = $richsubmission->id;
                if ($files = $fs->get_area_files($this->context->id, $component, $filearea, $itemid, 'timemodified', false)) {
                    foreach ($files as $file) {
                        $filename = $file->get_filename();
                        if ($filename == '.') {
                            continue;
                        }

                        $file->copy_content_to($tempfullpath.'/'.$filename);
                        $filelist[$packagename.'/'.$currentfilepath.'/'.$filename] = $tempfullpath.'/'.$filename;
                    }
                }
            }
            $richsubmissions->close();

            // Continue making zip file available ONLY IF selection was valid.
            $exportfile = $tempbasedir.'.zip';
            file_exists($exportfile) && unlink($exportfile);

            $fp = get_file_packer('application/zip');
            $fp->archive_to_pathname($filelist, $exportfile);

            foreach ($filelist as $file) {
                unlink($file);
            }
            $dirnames = array_reverse($dirnames);
            foreach ($dirnames as $dir) {
                rmdir($CFG->tempdir.$dir);
            }
            rmdir($tempbasedir);

            $this->makezip_available($exportfile);
        } else {
            return SURVEYPRO_NOATTACHMENTFOUND;
        }
    }

    /**
     * Craft each uploaded attachment by item and compress the package.
     *
     * @return void
     */
    public function attachments_downloadbyitem() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

        $anonymousstr = get_string('anonymous', 'mod_surveypro');
        $itemstr = get_string('item', 'mod_surveypro');
        $submissionstr = get_string('submission', 'mod_surveypro');
        $dummyuserid = 0;
        $dirnames = array();
        $filelist = array();

        $fs = get_file_storage();
        list($richsubmissionssql, $whereparams) = $this->get_export_sql(true);

        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);

        if ($richsubmissions->valid()) {
            $packagename = $this->attachments_define_packagename('item');

            $tempsubdir = '/mod_surveypro/attachmentsexport/'.$packagename;
            $tempbasedir = $CFG->tempdir.$tempsubdir;

            $olduserid = 0;
            $olditemid = 0;
            $forcenewuserfolder = false;
            foreach ($richsubmissions as $richsubmission) {
                if ($olditemid != $richsubmission->itemid) {
                    // New item.
                    // Add a new folder named 'element_'.$richsubmission->itemid.
                    $tempitemdir = $itemstr.'_'.$richsubmission->itemid;
                    $tempitemdir = str_replace(' ', '_', $tempitemdir);
                    $temppath = $tempsubdir.'/'.$tempitemdir;
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
                        $tempuserdir = $anonymousstr.'_'.$dummyuserid;
                    } else {
                        $tempuserdir = fullname($richsubmission).'_'.$richsubmission->userid;
                    }
                    $tempuserdir = str_replace(' ', '_', $tempuserdir);
                    $temppath = $tempsubdir.'/'.$tempitemdir.'/'.$tempuserdir;
                    make_temp_directory($temppath);
                    $dirnames[] = $temppath;

                    $olduserid = $richsubmission->userid;
                }

                // Add a new folder named $richsubmission->submissionid.
                $tempsubmissiondir = $submissionstr.'_'.$richsubmission->submissionid;
                $tempsubmissiondir = str_replace(' ', '_', $tempsubmissiondir);
                $currentfilepath = $tempitemdir.'/'.$tempuserdir.'/'.$tempsubmissiondir;
                $temppath = $tempsubdir.'/'.$currentfilepath;
                make_temp_directory($temppath);
                $dirnames[] = $temppath;

                $tempfullpath = $CFG->tempdir.'/'.$temppath;
                // Finally add the attachment.
                $component = 'surveyprofield_fileupload';
                $filearea = SURVEYPROFIELD_FILEUPLOAD_FILEAREA;
                $itemid = $richsubmission->id;
                if ($files = $fs->get_area_files($this->context->id, $component, $filearea, $itemid, 'timemodified', false)) {
                    foreach ($files as $file) {
                        $filename = $file->get_filename();
                        if ($filename == '.') {
                            continue;
                        }

                        $file->copy_content_to($tempfullpath.'/'.$filename);
                        $filelist[$packagename.'/'.$currentfilepath.'/'.$filename] = $tempfullpath.'/'.$filename;
                    }
                }
            }
            $richsubmissions->close();

            // Continue making zip file available ONLY IF selection was valid.
            $exportfile = $tempbasedir.'.zip';
            file_exists($exportfile) && unlink($exportfile);

            $fp = get_file_packer('application/zip');
            $fp->archive_to_pathname($filelist, $exportfile);

            foreach ($filelist as $file) {
                unlink($file);
            }
            $dirnames = array_reverse($dirnames, true);
            foreach ($dirnames as $dir) {
                rmdir($CFG->tempdir.$dir);
            }
            rmdir($tempbasedir);

            $this->makezip_available($exportfile);
        } else {
            return SURVEYPRO_NOATTACHMENTFOUND;
        }
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
