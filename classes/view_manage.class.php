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
class mod_surveypro_submissionmanager {
    /*
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /*
     * $submissionid: the ID of the current submission
     */
    public $submissionid = 0;

    /*
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /*
     * $canmanageitems
     */
    public $canmanageitems = false;

    /*
     * $action
     */
    public $action = SURVEYPRO_NOACTION;

    /*
     * $view
     */
    public $view = 0;

    /*
     * $confirm
     */
    public $confirm = false;

    /*
     * $canseeownsubmissions
     */
    // public $canseeownsubmissions = true;

    /*
     * $canseeotherssubmissions
     */
    public $canseeotherssubmissions = false;

    /*
     * $caneditownsubmissions
     */
    public $caneditownsubmissions = false;

    /*
     * $caneditotherssubmissions
     */
    public $caneditotherssubmissions = false;

    /*
     * $candeleteownsubmissions
     */
    public $candeleteownsubmissions = false;

    /*
     * $candeleteotherssubmissions
     */
    public $candeleteotherssubmissions = false;

    /*
     * $cansavesubmissiontopdf
     */
    public $cansavesubmissiontopdf = false;

    /*
     * $searchfieldsget
     */
    public $searchfieldsget = '';

    /*
     * $userfeedback
     */
    public $userfeedback = '';

    /*
     * Class constructor
     */
    public function __construct($cm, $surveypro, $submissionid, $action, $view, $confirm, $searchfieldsget) {
        $this->cm = $cm;
        $this->context = context_module::instance($cm->id);
        $this->surveypro = $surveypro;
        $this->submissionid = $submissionid;
        $this->action = $action;
        $this->confirm = $confirm;
        $this->view = $view;
        $this->searchfields_get = $searchfieldsget;
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);

        // $this->canseeownsubmissions = true;
        $this->canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);

        $this->caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);
        $this->caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);

        $this->candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context, null, true);
        $this->candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context, null, true);

        $this->cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context, null, true);
    }

    /*
     * trigger_event
     *
     * @return void
     */
    public function trigger_event() {
        // event: all_submissions_viewed
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_viewed::create($eventdata);
        $event->trigger();
    }

    /*
     * manage_actions
     *
     * @return
     */
    public function manage_actions() {
        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                break;
            case SURVEYPRO_DELETERESPONSE:
                $this->manage_submission_deletion();
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                $this->manage_all_submission_deletion();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->action = '.$this->action, DEBUG_DEVELOPER);
        }
    }

    /*
     * manage_submission_deletion
     *
     * @return
     */
    public function manage_submission_deletion() {
        global $USER, $DB, $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // ask for confirmation
            $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));

            $a = new stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid != $USER->id) {
                $user = $DB->get_record('user', array('id' => $submission->userid), user_picture::fields());
                $a->fullname = fullname($user);
                if ($a->timemodified == 0) {
                    $message = get_string('askdeleteonesurveypronevermodified', 'surveypro', $a);
                } else {
                    $message = get_string('askdeleteonesurveypro', 'surveypro', $a);
                }
            } else {
                if ($a->timemodified == 0) {
                    $message = get_string('askdeletemysubmissionsnevermodified', 'surveypro', $a);
                } else {
                    $message = get_string('askdeletemysubmissions', 'surveypro', $a);
                }
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETERESPONSE);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['submissionid'] = $this->submissionid;
            $urlyes = new moodle_url('view_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmsurveyprodeletion', 'surveypro'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('view_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    try {
                        $transaction = $DB->start_delegated_transaction();

                        $DB->delete_records('surveypro_userdata', array('submissionid' => $this->submissionid));
                        $DB->delete_records('surveypro_submission', array('id' => $this->submissionid));

                        $transaction->allow_commit();

                        echo $OUTPUT->notification(get_string('responsedeleted', 'surveypro'), 'notifyproblem');
                    } catch (Exception $e) {
                        //extra cleanup steps
                        $transaction->rollback($e); // rethrows exception
                    }

                    // Update completion state
                    $course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
                    $completion = new completion_info($course);
                    if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                        $completion->update_state($this->cm, COMPLETION_INCOMPLETE);
                    }

                    // event: submission_deleted
                    $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
                    $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                    $event->trigger();
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }
    }

    /*
     * manage_all_submission_deletion
     *
     * @return
     */
    public function manage_all_submission_deletion() {
        global $DB, $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // ask for confirmation
            $message = get_string('askdeleteallsubmissions', 'surveypro');

            $optionbase = array('s' => $this->surveypro->id, 'surveyproid' => $this->surveypro->id, 'act' => SURVEYPRO_DELETEALLRESPONSES);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new moodle_url('view_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('confirmallsubmissionsdeletion', 'surveypro'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('view_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    try {
                        $transaction = $DB->start_delegated_transaction();
                        $sql = 'SELECT s.id
                                    FROM {surveypro_submission} s
                                    WHERE s.surveyproid = :surveyproid';
                        $idlist = $DB->get_records_sql($sql, array('surveyproid' => $this->surveypro->id));

                        foreach ($idlist as $submissionid) {
                            $DB->delete_records('surveypro_userdata', array('submissionid' => $submissionid->id));
                        }

                        $DB->delete_records('surveypro_submission', array('surveyproid' => $this->surveypro->id));
                        echo $OUTPUT->notification(get_string('allsubmissionsdeleted', 'surveypro'), 'notifyproblem');

                        $transaction->allow_commit();
                    } catch (Exception $e) {
                        //extra cleanup steps
                        $transaction->rollback($e); // rethrows exception
                    }

                    // Update completion state
                    $course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
                    $completion = new completion_info($course);
                    if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                        $completion->update_state($this->cm, COMPLETION_INCOMPLETE);
                    }

                    // event: all_submissions_deleted
                    $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
                    $event = \mod_surveypro\event\all_submissions_deleted::create($eventdata);
                    $event->trigger();
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'surveypro');
                    echo $OUTPUT->notification($message, 'notifyproblem');
                    break;
                default:
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->confirm = '.$this->confirm, DEBUG_DEVELOPER);
            }
        }
    }

    /*
     * get_manage_sql
     *
     * @param $table
     * @return
     */
    public function get_manage_sql($table) {
        global $COURSE, $USER;

        if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
            $mygroupmates = surveypro_groupmates();
            // if (!count($mygroupmates)) then the user is a teacher
            if (!count($mygroupmates)) { // user is not in any group
                if (has_capability('mod/surveypro:manageitems', $this->context)) {
                    // This is a teacher
                    // Has to see each submission
                    $manageallsubmissions = true;
                } else {
                    // This is a student that has not been added to any group.
                    // The sql needs to return an empty set
                    $sql = 'SELECT DISTINCT s.*, s.id as submissionid, '.user_picture::fields('u').'
                            FROM {surveypro_submission} s
                                JOIN {user} u ON s.userid = u.id
                            WHERE u.id = :userid';
                    return array($sql, array('userid' => -1));
                }
            } else {
                // I don'care if the user is a teacher or a student.
                // He/she was assigned to a group.
                // I allow him/her only to submissions of his/her groupmates.
                $manageallsubmissions = false;
                $courseisgrouped = groups_get_all_groups($COURSE->id);
                $mygroups = surveypro_get_my_groups_simple();
            }
        }

        // DISTINCT is needed when a user belongs to more than a single group
        $sql = 'SELECT DISTINCT s.*, s.id as submissionid, '.user_picture::fields('u').'
                FROM {surveypro_submission} s
                    JOIN {user} u ON s.userid = u.id';

        list($where, $whereparams) = $table->get_sql_where();

        // write $transposeduserdata whether necessary
        if ($this->searchfields_get) {
            // this will be re-send to URL for next page reload, whether requested with a sort, for instance
            $paramurl['searchquery'] = $this->searchfields_get;

            $searchrestrictions = unserialize($this->searchfields_get);

            // written following http://buysql.com/mysql/14-how-to-automate-pivot-tables.html
            $transposeduserdata = 'SELECT submissionid, ';
            $sqlrow = array();
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $sqlrow[] = 'MAX(IF(itemid = \''.$itemid.'\', content, NULL)) AS \'c_'.$itemid.'\'';
            }
            $transposeduserdata .= implode(', ', $sqlrow);
            $transposeduserdata .= ' FROM {surveypro_userdata}';
            $transposeduserdata .= ' GROUP BY submissionid';

            $sql .= ' JOIN ('.$transposeduserdata.') tud ON tud.submissionid = s.id '; // tud == transposed user data
        }

        if (($groupmode == SEPARATEGROUPS) && (!$manageallsubmissions)) {
            $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid ';
        }

        // now finalise $sql
        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        if (($groupmode == SEPARATEGROUPS) && (!$manageallsubmissions)) {
            if (count($mygroups)) {
                // restrict to your groups only
                $sql .= ' AND gm.groupid IN ('.implode(',', $mygroups).')';
            } else {
                $sql .= ' AND s.userid = :userid';
                $whereparams['userid'] = $USER->id;
                debugging('Activity is divided into SEPARATE groups BUT you do not belong to anyone of them', DEBUG_DEVELOPER);

                return array($sql, $whereparams);
            }
        }
        if (!$this->canseeotherssubmissions) {
            // restrict to your submissions only
            $sql .= ' AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        if ($this->searchfields_get) {
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $sql .= ' AND tud.c_'.$itemid.' = :c_'.$itemid;
                $whereparams['c_'.$itemid] = $searchrestriction;
            }
        }

        // sort coming from $table->get_sql_sort()
        if ($table->get_sql_sort()) {
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.timecreated';
        }

        // echo '$sql = '.$sql.'<br />';
        // echo '$whereparams:';
        // var_dump($whereparams);

        return array($sql, $whereparams);
    }

    /*
     * manage_submissions
     *
     * @return
     */
    public function manage_submissions() {
        global $OUTPUT, $CFG, $DB, $COURSE, $USER;

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('submissionslist');

        if ($this->canseeotherssubmissions) {
            $table->initialbars(true);
        }

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        if ($this->searchfields_get) {
            $paramurl['searchquery'] = $this->searchfields_get;
        }
        $table->define_baseurl(new moodle_url('view_manage.php', $paramurl));

        $tablecolumns = array();
        $tablecolumns[] = 'picture';
        $tablecolumns[] = 'fullname';
        $tablecolumns[] = 'status';
        $tablecolumns[] = 'timecreated';
        if (!$this->surveypro->history) {
            $tablecolumns[] = 'timemodified';
        }
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = '';
        $tableheaders[] = get_string('fullname');
        $tableheaders[] = get_string('status');
        $tableheaders[] = get_string('timecreated', 'surveypro');
        if (!$this->surveypro->history) {
            $tableheaders[] = get_string('timemodified', 'surveypro');
        }
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        // $table->collapsible(true);
        $table->sortable(true, 'sortindex', 'ASC'); // sorted by sortindex by default
        $table->no_sorting('actions');

        // $table->column_style('actions', 'width', '60px');
        // $table->column_style('actions', 'align', 'center');
        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        $table->column_class('status', 'status');
        $table->column_class('timecreated', 'timecreated');
        if (!$this->surveypro->history) {
            $table->column_class('timemodified', 'timemodified');
        }
        $table->column_class('actions', 'actions');

        // hide the same info whether in two consecutive rows
        $table->column_suppress('picture');
        $table->column_suppress('fullname');

        // general properties for the whole table
        // $table->set_attribute('name', 'submissions');
        $table->set_attribute('cellpadding', 5);
        $table->set_attribute('id', 'submissions');
        $table->set_attribute('class', 'generaltable');
        $table->set_attribute('align', 'center');
        // $table->set_attribute('width', '90%');
        $table->setup();

        $status = array(SURVEYPRO_STATUSINPROGRESS => get_string('statusinprogress', 'surveypro'),
                        SURVEYPRO_STATUSCLOSED => get_string('statusclosed', 'surveypro'));
        $downloadpdftitle = get_string('downloadpdf', 'surveypro');
        $deletetitle = get_string('delete');
        $neverstring = get_string('never');
        $readonlyaccess = get_string('readonlyaccess', 'surveypro');

        $nonhistoryedittitle = get_string('edit');
        $historyedittitle = get_string('duplicate');
        if ($this->surveypro->history) {
            $edittitle = $historyedittitle;
            $linkidprefix = 'duplicate';
            $editiconpath = 't/copy';
        } else {
            $edittitle = $nonhistoryedittitle;
            $linkidprefix = 'edit';
            $editiconpath = 't/edit';
        }

        $paramurlbase = array('id' => $this->cm->id);
        $basepath = new moodle_url('view.php', $paramurlbase);

        list($sql, $whereparams) = $this->get_manage_sql($table);
        $submissions = $DB->get_recordset_sql($sql, $whereparams);

        if ($submissions->valid()) {
            if ($this->candeleteownsubmissions && $this->candeleteotherssubmissions) {
                $paramurl = $paramurlbase;
                $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
                $paramurl['sesskey'] = sesskey();
                $url = new moodle_url('/mod/surveypro/view_manage.php', $paramurl);
                echo $OUTPUT->single_button($url, get_string('deleteallsubmissions', 'surveypro'), 'get');
            }

            if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
                $mygroupmates = surveypro_groupmates();
            }

            foreach ($submissions as $submission) {
                // before starting, just set some information
                if (!$ismine = ($submission->userid == $USER->id)) {
                    if (!$this->canseeotherssubmissions) {
                        continue;
                    }
                    if ($groupmode == SEPARATEGROUPS) {
                        // if I am a teacher, $mygroupmates is empty but I still have the right to see all my students
                        if (!$mygroupmates) { // I have no $mygroupmates. I am a teacher. I am active part of each group.
                            $groupuser = true;
                        } else {
                            $groupuser = in_array($submission->userid, $mygroupmates);
                        }
                    }
                }

                $tablerow = array();

                // icon
                $tablerow[] = $OUTPUT->user_picture($submission, array('courseid' => $COURSE->id));

                // user fullname
                $tablerow[] = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$submission->userid.'&amp;course='.$COURSE->id.'">'.fullname($submission).'</a>';

                // surveypro status
                $tablerow[] = $status[$submission->status];

                // creation time
                $tablerow[] = userdate($submission->timecreated);

                // timemodified
                if (!$this->surveypro->history) {
                    // modification time
                    if ($submission->timemodified) {
                        $tablerow[] = userdate($submission->timemodified);
                    } else {
                        $tablerow[] = $neverstring;
                    }
                }

                // actions
                $paramurl = $paramurlbase;
                $paramurl['submissionid'] = $submission->submissionid;
                $paramurl['cvp'] = 0;

                // edit
                if ($ismine) { // I am the owner
                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        $displayediticon = true;
                    } else {
                        $displayediticon = $this->caneditownsubmissions;
                    }
                } else { // I am not the owner
                    if ($groupmode == SEPARATEGROUPS) {
                        $displayediticon = $groupuser && $this->caneditotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS
                        $displayediticon = $this->caneditotherssubmissions;
                    }
                }
                if ($displayediticon) {
                    $paramurl['view'] = SURVEYPRO_EDITRESPONSE;
                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        $icons = $OUTPUT->action_icon(new moodle_url('view.php', $paramurl),
                            new pix_icon('t/edit', $nonhistoryedittitle, 'moodle', array('title' => $nonhistoryedittitle)),
                            null, array('id' => 'edit_submission_'.$submission->submissionid, 'title' => $nonhistoryedittitle));
                    } else {
                        $icons = $OUTPUT->action_icon(new moodle_url('view.php', $paramurl),
                            new pix_icon($editiconpath, $edittitle, 'moodle', array('title' => $edittitle)),
                            null, array('id' => $linkidprefix.'_submission_'.$submission->submissionid, 'title' => $edittitle));
                    }
                } else {
                    $paramurl['view'] = SURVEYPRO_READONLYRESPONSE;
                    $icons = $OUTPUT->action_icon(new moodle_url('view.php', $paramurl),
                        new pix_icon('readonly', $readonlyaccess, 'surveypro', array('title' => $readonlyaccess)),
                        null, array('id' => 'view_submission_'.$submission->submissionid, 'title' => $readonlyaccess));
                }

                // delete
                $paramurl = $paramurlbase;
                $paramurl['submissionid'] = $submission->submissionid;
                $paramurl['cvp'] = 0;
                if ($ismine) { // I am the owner
                    $displaydeleteicon = $this->candeleteownsubmissions;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $displaydeleteicon = $groupuser && $this->candeleteotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS
                        $displaydeleteicon = $this->candeleteotherssubmissions;
                    }
                }
                if ($displaydeleteicon) {
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $paramurl['sesskey'] = sesskey();
                    $icons .= $OUTPUT->action_icon(new moodle_url('view_manage.php', $paramurl),
                        new pix_icon('t/delete', $deletetitle, 'moodle', array('title' => $deletetitle)),
                        null, array('id' => 'delete_submission_'.$submission->submissionid, 'title' => $deletetitle));
                }

                // download to pdf
                if ($this->cansavesubmissiontopdf) {
                    $paramurl = $paramurlbase;
                    $paramurl['submissionid'] = $submission->submissionid;
                    $paramurl['view'] = SURVEYPRO_RESPONSETOPDF;
                    $icons .= $OUTPUT->action_icon(new moodle_url('view_manage.php', $paramurl),
                        new pix_icon('i/export', $downloadpdftitle, 'moodle', array('title' => $downloadpdftitle)),
                        null, array('id' => 'pdf_submission_'.$submission->submissionid, 'title' => $downloadpdftitle));
                }

                $tablerow[] = $icons;

                // add row to the table
                $table->add_data($tablerow);
            }
        }
        $submissions->close();

        $table->summary = get_string('submissionslist', 'surveypro');
        $table->print_html();
    }

    /*
     * prevent_direct_user_input
     *
     * @param $confirm
     * @return
     */
    public function prevent_direct_user_input($confirm) {
        global $COURSE, $USER, $DB;

        if ($this->action == SURVEYPRO_NOACTION) {
            return true;
        }
        if ($confirm == SURVEYPRO_CONFIRMED_NO) {
            return true;
        }
        if ($this->action != SURVEYPRO_DELETEALLRESPONSES) { // if a specific submission is involved
            if (!$ownerid = $DB->get_field('surveypro_submission', 'userid', array('id' => $this->submissionid), IGNORE_MISSING)) {
                print_error('incorrectaccessdetected', 'surveypro');
            }

            if (!$ismine = ($ownerid == $USER->id)) {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode == SEPARATEGROUPS) {
                    $mygroupmates = surveypro_groupmates();
                    $groupuser = in_array($submission->userid, $mygroupmates);
                }
            }
        }

        switch ($this->action) {
            case SURVEYPRO_DELETERESPONSE:
                if ($ismine) {
                    $allowed = $this->candeleteownsubmissions;
                } else {
                    if (!$groupmode) {
                        $allowed = $this->candeleteotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $this->candeleteotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS
                            $allowed = $this->candeleteotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                $allowed = $this->candeleteotherssubmissions;
                break;
            default:
                $allowed = false;
        }

        switch ($this->view) {
            case SURVEYPRO_NOVIEW:
                $allowed = true;
                break;
            case SURVEYPRO_READONLYRESPONSE:
                if ($ismine) {
                    $allowed = $this->canseeownsubmissions;
                } else {
                    if (!$groupmode) {
                        $allowed = $this->canseeotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $this->canseeotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS
                            $allowed = $this->canseeotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_EDITRESPONSE:
                if ($ismine) {
                    $allowed = $this->caneditownsubmissions;
                } else {
                    if (!$groupmode) {
                        $allowed = $this->caneditotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $this->caneditotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS
                            $allowed = $this->caneditotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_RESPONSETOPDF:
                $allowed = $this->cansavesubmissiontopdf;
                break;
            default:
                $allowed = false;
        }

        if (!$allowed) {
            print_error('incorrectaccessdetected', 'surveypro');
        }
    }

    /*
     * submission_to_pdf
     *
     * @return
     */
    public function submission_to_pdf() {
        global $CFG, $DB;

        if ($this->view != SURVEYPRO_RESPONSETOPDF) {
            return;
        }

        // event: submissioninpdf_downloaded
        $eventdata = array('context' => $this->context, 'objectid' => $this->submissionid);
        $eventdata['other'] = array('view' => SURVEYPRO_RESPONSETOPDF);
        $event = \mod_surveypro\event\submissioninpdf_downloaded::create($eventdata);
        $event->trigger();

        require_once($CFG->libdir.'/tcpdf/tcpdf.php');
        require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

        $emptyanswer = get_string('notanswereditem', 'surveypro');

        $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $userdatarecord = $DB->get_records('surveypro_userdata', array('submissionid' => $this->submissionid), '', 'itemid, id, content');

        $accessedadvancedform = has_capability('mod/surveypro:accessadvanceditems', $this->context, $user->id, true);
        // $canaccessadvanceditems, $searchform = false; $type = false; $formpage = false;
        list($sql, $whereparams) = surveypro_fetch_items_seeds($this->surveypro->id, $accessedadvancedform, false);

        // I am not allowed to get ONLY answers from surveypro_userdata
        // because I also need to gather info about fieldset and label
        // $sql = 'SELECT *, s.id as submissionid, ud.id as userdataid, ud.itemid as id
        //         FROM {surveypro_submission} s
        //             JOIN {surveypro_userdata} ud ON ud.submissionid = s.id
        //         WHERE s.id = :submissionid';
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('moodle-mod_surveypro');
        $pdf->SetTitle('User response');
        $pdf->SetSubject('Single response in PDF');

        // set default header data
        $textheader = get_string('responseauthor', 'surveypro');
        $textheader .= fullname($user);
        $textheader .= "\n";
        $textheader .= get_string('responsetimecreated', 'surveypro');
        $textheader .= userdate($submission->timecreated);
        if ($submission->timemodified) {
            $textheader .= get_string('responsetimemodified', 'surveypro');
            $textheader .= userdate($submission->timemodified);
        }

        $pdf->SetHeaderData('', 0, $this->surveypro->name, $textheader, array(0, 64, 255), array(0, 64, 128));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetDrawColorArray(array(0, 64, 128));
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();

        $col1nunit = 1;
        $col2nunit = 6;
        $col3nunit = 3;
        $firstcolwidth = $pdf->getPageWidth();
        $firstcolwidth -= PDF_MARGIN_LEFT;
        $firstcolwidth -= PDF_MARGIN_RIGHT;
        $unitsum = $col1nunit + $col2nunit + $col3nunit;

        $firstcolwidth = number_format($col1nunit*100/$unitsum, 2);
        $secondcolwidth = number_format($col2nunit*100/$unitsum, 2);
        $thirdcolwidth = number_format($col3nunit*100/$unitsum, 2);
        $lasttwocolumns = $secondcolwidth + $thirdcolwidth;

        // 0: to the right (or left for RTL language)
        // 1: to the beginning of the next line
        // 2: below

        $htmllabeltemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmllabeltemplate .= '<td style="width:'.$lasttwocolumns.'%;text-align:left;">@@col2@@</td></tr></table>';

        $htmlstandardtemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$secondcolwidth.'%;text-align:left;">@@col2@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$thirdcolwidth.'%;text-align:left;">@@col3@@</td></tr></table>';

        $border = array('T' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(179, 219, 181)));
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);
            // ($itemseed->plugin == 'pagebreak') is not selected by surveypro_fetch_items_seeds
            if (($itemseed->plugin == 'fieldset') || ($itemseed->plugin == 'fieldsetend')) {
                continue;
            }
            if ($itemseed->plugin == 'label') {
                // first column
                $html = $htmllabeltemplate;
                $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col1@@', $content, $html);

                // second column: colspan 2
                // $content = trim(strip_tags($item->get_content()), " \t\n\r"); <-- I want images in the PDF
                $content = $item->get_content();
                // why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
                // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col2@@', $content, $html);
                $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true); // this is like span 2
                continue;
            }

            // first column
            $html = $htmlstandardtemplate;
            $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
            $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col1@@', $content, $html);

            // second column
            // $content = trim(strip_tags($item->get_content()), " \t\n\r"); <-- I want images in the PDF
            $content = $item->get_content();
            // why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
            // because it comes from an editor?
            // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col2@@', $content, $html);

            // third column
            if (isset($userdatarecord[$item->get_itemid()])) {
                $content = $item->userform_db_to_export($userdatarecord[$item->get_itemid()], SURVEYPRO_FIRENDLYFORMAT);
                if ($item->get_plugin() != 'textarea') { // content does not come from an html editor
                    $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                } else {
                    if (!$item->get_useeditor()) { // content does not come from an html editor
                        $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                    }
                }
            } else {
                // $content = $emptyanswer;
                $content = '';
            }
            $html = str_replace('@@col3@@', $content, $html);
            $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true);
        }

        $filename = $this->surveypro->name.'_'.$this->submissionid.'.pdf';
        $pdf->Output($filename, 'D');
        die();
    }
}
