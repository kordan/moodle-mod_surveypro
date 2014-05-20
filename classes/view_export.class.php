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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * The base class representing a field
 */
class mod_surveypro_exportmanager {
    /*
     * $cm
     */
    public $cm = null;

    /*
     * $context
     */
    public $context = null;

    /*
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /*
     * $canseeownsubmissions
     */
    // public $canseeownsubmissions = true;

    /*
     * $canseeotherssubmissions
     */
    public $canseeotherssubmissions = false;

    /*
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $surveypro) {
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->surveypro = $surveypro;

        // $this->canseeownsubmissions = true;
        $this->canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
    }

    /*
     * trigger_event
     *
     * @return void
     */
    public function trigger_event() {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_exported::create($eventdata);
        $event->trigger();
    }

    /*
     * get_manage_sql
     *
     * @return
     */
    public function get_export_sql() {
        global $USER, $COURSE;

        if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
            $mygroups = surveypro_get_my_groups_simple($userid);
        }

        $sql = 'SELECT s.id as submissionid, s.status, s.timecreated, s.timemodified, ';
        if (empty($this->surveypro->anonymous)) {
            $sql .= 'u.id as userid, u.firstname,  u.lastname, ';
        }
        $sql .= 'ud.id as id, ud.itemid, ud.content,
                                si.sortindex, si.plugin
                            FROM {surveypro_submission} s
                                     JOIN {user} u ON u.id = s.userid
                                LEFT JOIN {surveypro_answer} ud ON ud.submissionid = s.id
                                LEFT JOIN {surveypro_item} si ON si.id = ud.itemid';

        if ($groupmode == SEPARATEGROUPS) {
            if (!$this->canseeotherssubmissions) {
                $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid ';
            }
        }

        // now finalise $sql
        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        // for IN PROGRESS submission where no fields were filled
        // I need the LEFT JOIN {surveypro_item}
        // In this case,
        // if I add a clause for fields of UNEXISTING {surveypro_item} (because no fields was filled)
        // I will miss the record if I do not further add OR ISNULL(si.xxxx)
        if (!isset($this->formdata->includehidden)) {
            $sql .= ' AND (si.hidden = 0 OR ISNULL(si.hidden))';
        }
        if (!isset($this->formdata->advanced)) {
            $sql .= ' AND (si.advanced = 0 OR ISNULL(si.advanced))';
        }
        if ($this->formdata->status != SURVEYPRO_STATUSALL) {
            $sql .= ' AND s.status = :status';
            $whereparams['status'] = $this->formdata->status;
        }

        if ($groupmode == SEPARATEGROUPS) {
            // restrict to your groups only
            $sql .= ' AND gm.groupid IN ('.implode(',', $mygroups).')';
        }
        if (!$this->canseeotherssubmissions) {
            // restrict to your submissions only
            $sql .= ' AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        // echo '$sql = '.$sql.'<br />';
        // echo '$whereparams:';
        // var_dump($whereparams);

        return array($sql, $whereparams);
    }

    /*
     * surveypro_export
     *
     * @return exporterror
     */
    public function surveypro_export() {
        global $CFG, $DB;

        // do I need to filter groups?
        $filtergroups = surveypro_need_group_filtering($this->cm, $this->context);

        // -----------------------------
        // get the field list
        //     no matter for the page
        $where = array();
        $where['surveyproid'] = $this->surveypro->id;
        $where['type'] = SURVEYPRO_TYPEFIELD;
        if (!isset($this->formdata->advanced)) {
            $where['advanced'] = 0;
        }
        if (!isset($this->formdata->includehide)) {
            $where['hidden'] = 0;
        }

        if (!$itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, plugin')) {
            return SURVEYPRO_NOFIELDSSELECTED;
            die();
        }
        // end of: get the field list
        // -----------------------------

        list($richsubmissionssql, $whereparams) = $this->get_export_sql();

        $richsubmissions = $DB->get_recordset_sql($richsubmissionssql, $whereparams);
        if ($richsubmissions->valid()) {
            if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
                require_once($CFG->libdir.'/excellib.class.php');
                $filename = str_replace(' ', '_', $this->surveypro->name).'.xls';
                $workbook = new MoodleExcelWorkbook('-');
                $workbook->send($filename);

                $worksheet = array();
                $worksheet[0] = $workbook->add_worksheet(get_string('surveypro', 'surveypro'));
            } else { // SURVEYPRO_DOWNLOADCSV or SURVEYPRO_DOWNLOADTSV
                header('Content-Transfer-Encoding: utf-8');
                header('Content-Disposition: attachment; filename='.str_replace(' ', '_', $this->surveypro->name).'.csv');
                header('Content-Type: text/comma-separated-values');

                $worksheet = null;
            }

            $this->export_print_header($itemseeds, $worksheet);

            // reduce the weight of $itemseeds disposing no longer relevant infos
            $notsetstring = get_string('notanswereditem', 'surveypro');
            $itemseedskeys = array_keys($itemseeds);
            $placeholders = array_fill_keys($itemseedskeys, $notsetstring);
            unset($itemseeds);

            // echo '$placeholders:';
            // var_dump($placeholders);

            // get user group (to filter surveypro to download) ???? TODO: NEVER USED ????
            // $mygroups = surveypro_get_my_groups_simple();

            $oldrichsubmissionid = 0;
            $strnever = get_string('never');

            foreach ($richsubmissions as $richsubmission) {
                if ($oldrichsubmissionid != $richsubmission->submissionid) {
                    if (!empty($oldrichsubmissionid)) { // new richsubmissionid, stop managing old record
                        // write old record
                        $this->export_close_record($recordtoexport, $worksheet);
                    }

                    // update the reference
                    $oldrichsubmissionid = $richsubmission->submissionid;

                    // begin a new record
                    $recordtoexport = array();
                    if (empty($this->surveypro->anonymous)) {
                        $recordtoexport['firstname'] = $richsubmission->firstname;
                        $recordtoexport['lastname'] = $richsubmission->lastname;
                    }
                    // I add to my almost empy associative array a dummy array of empty values.
                    // I do this only to fix the order of elements in the array.
                    $recordtoexport += $placeholders;

                    $recordtoexport['timecreated'] = userdate($richsubmission->timecreated);
                    if ($richsubmission->timemodified) {
                        $recordtoexport['timemodified'] = userdate($richsubmission->timemodified);
                    } else {
                        $recordtoexport['timemodified'] = $strnever;
                    }
                }
                $recordtoexport[$richsubmission->itemid] = $this->decode_content($richsubmission);
            }
            $richsubmissions->close();
            $this->export_close_record($recordtoexport, $worksheet);

            if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
                $workbook->close();
            }
        } else {
            return SURVEYPRO_NORECORDSFOUND;
        }
    }

    /*
     * export_print_header
     *
     * I am forced to query, once more, the database to get the header of the fiel to export because:
     * -> richsubmission is not reliable as it may omit some item
     * -> the load of the item object is more resource expensive than a single simple query

     * @param $itemseeds
     * @param $worksheet
     * @return
     */
    public function export_print_header($itemseeds, $worksheet) {
        global $DB;

        // write the names of the fields in the header of the file to export
        $recordtoexport = array();
        if (empty($this->surveypro->anonymous)) {
            $recordtoexport[] = get_string('firstname');
            $recordtoexport[] = get_string('lastname');
        }
        // variables
        foreach ($itemseeds as $itemseed) {
            $recordtoexport[] = $DB->get_field('surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemseed->plugin, 'variable', array('itemid' => $itemseed->id));
        }
        $recordtoexport[] = get_string('timecreated', 'surveypro');
        $recordtoexport[] = get_string('timemodified', 'surveypro');

        if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
            $col = 0;
            foreach ($recordtoexport as $header) {
                $worksheet[0]->write(0, $col, $header, '');
                $col++;
            }
        } else { // SURVEYPRO_DOWNLOADCSV or SURVEYPRO_DOWNLOADTSV
            $separator = ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADCSV) ? ',' : "\t";
            echo implode($separator, $recordtoexport)."\n";
        }
    }

    /*
     * export_close_record
     *
     * @param $recordtoexport
     * @param $worksheet
     * @return
     */
    public function export_close_record($recordtoexport, $worksheet) {
        static $row = 0;

        if ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADXLS) {
            $row++;
            $col = 0;
            foreach ($recordtoexport as $value) {
                $worksheet[0]->write($row, $col, $value, '');
                $col++;
            }
        } else { // SURVEYPRO_DOWNLOADCSV or SURVEYPRO_DOWNLOADTSV
            $separator = ($this->formdata->downloadtype == SURVEYPRO_DOWNLOADCSV) ? ',' : "\t";
            echo implode($separator, $recordtoexport)."\n";
        }
    }

    /*
     * decode_content
     *
     * @param $richsubmission
     * @return
     */
    public function decode_content($richsubmission) {
        $content = $richsubmission->content;
        if (isset($content)) {
            $plugin = $richsubmission->plugin;
            $itemid = $richsubmission->itemid;
            $item = surveypro_get_item($itemid, SURVEYPRO_TYPEFIELD, $plugin);

            $return = $item->userform_db_to_export($richsubmission);
        } else {
            $return = '';
        }

        return $return;
    }
}