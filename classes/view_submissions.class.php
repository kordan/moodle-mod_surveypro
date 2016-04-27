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
 * The submissionmanager class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

/**
 * The class managing users submissions
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_submissionmanager {

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
     * @var int ID of the current submission
     */
    protected $submissionid;

    /**
     * @var int Action to execute
     */
    protected $action;

    /**
     * @var int View
     */
    protected $view;

    /**
     * @var int User confirmation to actions
     */
    protected $confirm;

    /**
     * @var int Has this surveypro items
     */
    protected $hasitems;

    /**
     * @var string $searchquery
     */
    protected $searchquery;

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

        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, null, true);

        $utilityman = new mod_surveypro_utility($cm, $surveypro);
        $this->hasitems = $utilityman->has_input_items(0, false, $canmanageitems, $canaccessreserveditems);
    }

    /**
     * Object setup.
     *
     * @param int $submissionid
     * @param int $action
     * @param int $view
     * @param bool $confirm
     * @param string $searchquery
     * @return void
     */
    public function setup($submissionid, $action, $view, $confirm, $searchquery) {
        $this->set_submissionid($submissionid);
        $this->set_action($action);
        $this->set_view($view);
        $this->set_confirm($confirm);
        $this->set_searchquery($searchquery);

        $this->prevent_direct_user_input($confirm);
        $this->submission_to_pdf();

    }

    // MARK set.

    /**
     * Set submission id.
     *
     * @param int $submissionid
     * @return void
     */
    public function set_submissionid($submissionid) {
        $this->submissionid = $submissionid;
    }

    /**
     * Set action.
     *
     * @param int $action
     * @return void
     */
    public function set_action($action) {
        $this->action = $action;
    }

    /**
     * Set view.
     *
     * @param int $view
     * @return void
     */
    public function set_view($view) {
        $this->view = $view;
    }

    /**
     * Set confirm.
     *
     * @param int $confirm
     * @return void
     */
    public function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    /**
     * Set search query.
     *
     * @param string $searchquery
     * @return void
     */
    public function set_searchquery($searchquery) {
        $this->searchquery = $searchquery;
    }

    // MARK get.

    /**
     * Get submissions sql.
     *
     * @param flexible_table $table
     * @return void
     */
    public function get_submissions_sql($table) {
        global $COURSE, $USER;

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);

        $emptysql = 'SELECT DISTINCT s.*, s.id as submissionid, '.user_picture::fields('u').'
                     FROM {surveypro_submission} s
                       JOIN {user} u ON s.userid = u.id
                     WHERE u.id = :userid';

        $coursecontext = context_course::instance($COURSE->id);
        $roles = get_roles_used_in_context($coursecontext);
        if (!$role = array_keys($roles)) {
            // Return nothing.
            return array($emptysql, array('userid' => -1));
        }

        if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
            if ($groupmode == SEPARATEGROUPS) {
                $mygroupmates = surveypro_groupmates($this->cm);
                if (!count($mygroupmates)) { // User is not in any group.
                    if (has_capability('mod/surveypro:manageitems', $this->context)) {
                        // This is a teacher.
                        // Has to see each submission.
                        $manageallsubmissions = true;
                    } else {
                        // This is a student that has not been added to any group.
                        // The sql needs to return an empty set.
                        return array($emptysql, array('userid' => -1));
                    }
                } else {
                    // I don'care if the user is a teacher or a student.
                    // He/she was assigned to a group.
                    // I allow him/her only to submissions of his/her groupmates.
                    $manageallsubmissions = false;
                    $mygroups = groups_get_all_groups($COURSE->id, $USER->id, $this->cm->groupingid);
                    $mygroups = array_keys($mygroups);
                }
            }
        }

        // DISTINCT is needed when a user belongs to more than a single group.
        $sql = 'SELECT DISTINCT s.id as submissionid, s.surveyproid, s.status, s.userid, s.timecreated, s.timemodified, ';
        if ($this->searchquery) {
            $sql .= 'COUNT(a.submissionid) as matchcount, ';
        }
        $sql .= user_picture::fields('u');
        $sql .= ' FROM {surveypro_submission} s';
        $sql .= '   JOIN {user} u ON s.userid = u.id';
        $sql .= '   JOIN {role_assignments} ra ON u.id = ra.userid ';
        if ($this->searchquery) {
            $sql .= '   JOIN {surveypro_answer} a ON s.id = a.submissionid ';
        }

        if (($groupmode == SEPARATEGROUPS) && (!$manageallsubmissions)) {
            $sql .= '   JOIN {groups_members} gm ON gm.userid = s.userid ';
        }

        // Now finalise $sql.
        $sql .= 'WHERE ra.contextid = :contextid ';
        $whereparams['contextid'] = $coursecontext->id;
        $sql .= '  AND roleid IN ('.implode(',', $role).')';
        $sql .= '  AND s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        // Manage table alphabetical filter.
        list($wherefilter, $wherefilterparams) = $table->get_sql_where();
        if ($wherefilter) {
            $sql .= '  AND '.$wherefilter;
            $whereparams = $whereparams + $wherefilterparams;
        }

        if (($groupmode == SEPARATEGROUPS) && (!$manageallsubmissions)) {
            if (count($mygroups)) {
                // Restrict to your groups only.
                $sql .= '  AND gm.groupid IN ('.implode(',', $mygroups).')';
            } else {
                $sql .= '  AND s.userid = :userid';
                $whereparams['userid'] = $USER->id;

                $message = 'Activity is divided into SEPARATE groups BUT you do not belong to anyone of them';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);

                return array($sql, $whereparams);
            }
        }

        if (!$canseeotherssubmissions) {
            // Restrict to your submissions only.
            $sql .= '  AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        // Manage user selection.
        if ($this->searchquery) {
            // This will be re-send to URL for next page reload, whether requested with a sort, for instance.
            $whereparams['searchquery'] = $this->searchquery;

            $searchrestrictions = unserialize($this->searchquery);

            // ((a.itemid = 7720 AND a.content = 0) OR (a.itemid = 7722 AND a.content = 1))
            $userquery = array();
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $userquery[] = '(a.itemid = '.$itemid.' AND a.content = \''.$searchrestriction.'\')';
            }
            $sql .= '  AND ('.implode(' OR ', $userquery).') ';
            $sql .= 'GROUP BY s.id ';
            $sql .= 'HAVING matchcount = :matchcount ';
            $whereparams['matchcount'] = count($userquery);
        }

        if ($table->get_sql_sort()) {
            // Sort coming from $table->get_sql_sort().
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.timecreated';
        }

        return array($sql, $whereparams);
    }

    /**
     * Display the submissions table.
     *
     * @return void
     */
    public function display_submissions_table() {
        global $CFG, $OUTPUT, $DB, $COURSE, $USER;

        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context, null, true);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);
        $canduplicateownsubmissions = has_capability('mod/surveypro:duplicateownsubmissions', $this->context, null, true);
        $canduplicateotherssubmissions = has_capability('mod/surveypro:duplicateotherssubmissions', $this->context, null, true);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context, null, true);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context, null, true);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context, null, true);

        require_once($CFG->libdir.'/tablelib.php');

        $table = new flexible_table('submissionslist');

        if ($canseeotherssubmissions) {
            $table->initialbars(true);
        }

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        if ($this->searchquery) {
            $paramurl['searchquery'] = $this->searchquery;
        }
        $baseurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = array();
        if ($canalwaysseeowner || empty($this->surveypro->anonymous)) {
            $tablecolumns[] = 'picture';
            $tablecolumns[] = 'fullname';
        }
        $tablecolumns[] = 'status';
        $tablecolumns[] = 'timecreated';
        if (!$this->surveypro->history) {
            $tablecolumns[] = 'timemodified';
        }
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        if ($canalwaysseeowner || empty($this->surveypro->anonymous)) {
            $tableheaders[] = '';
            $tableheaders[] = get_string('fullname');
        }
        $tableheaders[] = get_string('status');
        $tableheaders[] = get_string('timecreated', 'mod_surveypro');
        if (!$this->surveypro->history) {
            $tableheaders[] = get_string('timemodified', 'mod_surveypro');
        }
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        $table->sortable(true, 'sortindex', 'ASC'); // Sorted by sortindex by default.
        $table->no_sorting('actions');

        $table->column_class('picture', 'picture');
        $table->column_class('fullname', 'fullname');
        $table->column_class('status', 'status');
        $table->column_class('timecreated', 'timecreated');
        if (!$this->surveypro->history) {
            $table->column_class('timemodified', 'timemodified');
        }
        $table->column_class('actions', 'actions');

        // Hide the same info whether in two consecutive rows.
        if ($canalwaysseeowner || empty($this->surveypro->anonymous)) {
            $table->column_suppress('picture');
            $table->column_suppress('fullname');
        }

        // General properties for the whole table.
        $table->set_attribute('cellpadding', 5);
        $table->set_attribute('id', 'submissions');
        $table->set_attribute('class', 'generaltable');
        $table->set_attribute('align', 'center');
        $table->setup();

        $status = array();
        $status[SURVEYPRO_STATUSINPROGRESS] = get_string('statusinprogress', 'mod_surveypro');
        $status[SURVEYPRO_STATUSCLOSED] = get_string('statusclosed', 'mod_surveypro');

        $downloadpdfstr = get_string('downloadpdf', 'mod_surveypro');
        $deletestr = get_string('delete');
        $neverstr = get_string('never');
        $readonlyaccessstr = get_string('readonlyaccess', 'mod_surveypro');

        $nonhistoryeditstr = get_string('edit');
        $duplicatestr = get_string('duplicate');
        if ($this->surveypro->history) {
            $attributestr = get_string('editcopy', 'mod_surveypro');
            $linkidprefix = 'editcopy_submission_';
        } else {
            $attributestr = $nonhistoryeditstr;
            $linkidprefix = 'edit_submission_';
        }

        // initialize variables to gather information for the "Submission overview".
        $countclosed = 0;
        $closeduserarray = array();
        $countinprogress = 0;
        $inprogressuserarray = array();

        list($sql, $whereparams) = $this->get_submissions_sql($table);
        $submissions = $DB->get_recordset_sql($sql, $whereparams);
        if ($submissions->valid()) {
            if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
                if ($groupmode == SEPARATEGROUPS) {
                    $mygroupmates = surveypro_groupmates($this->cm);
                }
            }

            $tablerowcounter = 0;
            $paramurlbase = array('id' => $this->cm->id);
            foreach ($submissions as $submission) {
                // Count submissions per each user.
                $tablerowcounter++;
                $submissionsuffix = 'row_'.$tablerowcounter;

                // Before starting, just set some information.
                if (!$ismine = ($submission->userid == $USER->id)) {
                    if (!$canseeotherssubmissions) {
                        continue;
                    }
                    if ($groupmode == SEPARATEGROUPS) {
                        // If I am a teacher, $mygroupmates is empty but I still have the right to see all my students.
                        if (!$mygroupmates) { // I have no $mygroupmates. I am a teacher. I am active part of each group.
                            $groupuser = true;
                        } else {
                            $groupuser = in_array($submission->userid, $mygroupmates);
                        }
                    } else {
                        $groupuser = true;
                    }
                }

                $tablerow = array();

                // Icon.
                if ($canalwaysseeowner || empty($this->surveypro->anonymous)) {
                    $tablerow[] = $OUTPUT->user_picture($submission, array('courseid' => $COURSE->id));

                    // User fullname.
                    $paramurl = array('id' => $submission->userid, 'course' => $COURSE->id);
                    $url = new moodle_url('/user/view.php', $paramurl);
                    $tablerow[] = '<a href="'.$url->out().'">'.fullname($submission).'</a>';
                }

                // Surveypro status.
                $tablerow[] = $status[$submission->status];

                // Creation time.
                $tablerow[] = userdate($submission->timecreated);

                // Timemodified.
                if (!$this->surveypro->history) {
                    // Modification time.
                    if ($submission->timemodified) {
                        $tablerow[] = userdate($submission->timemodified);
                    } else {
                        $tablerow[] = $neverstr;
                    }
                }

                // Actions.
                $paramurl = $paramurlbase;
                $paramurl['submissionid'] = $submission->submissionid;

                // Edit.
                if ($ismine) { // I am the owner.
                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        $displayediticon = true;
                    } else {
                        $displayediticon = $caneditownsubmissions;
                    }
                } else { // I am not the owner.
                    if ($groupmode == SEPARATEGROUPS) {
                        $displayediticon = $groupuser && $caneditotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS.
                        $displayediticon = $caneditotherssubmissions;
                    }
                }
                if ($displayediticon) {
                    $paramurl['view'] = SURVEYPRO_EDITRESPONSE;
                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        // Here title and alt are ALWAYS $nonhistoryeditstr.
                        $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view_form.php', $paramurl),
                            new pix_icon('t/edit', $nonhistoryeditstr, 'moodle', array('title' => $nonhistoryeditstr)),
                            null, array('id' => 'edit_submission_'.$submissionsuffix, 'title' => $nonhistoryeditstr));
                    } else {
                        // Here title and alt depend from $this->surveypro->history.
                        $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view_form.php', $paramurl),
                            new pix_icon('t/edit', $attributestr, 'moodle', array('title' => $attributestr)),
                            null, array('id' => $linkidprefix.$submissionsuffix, 'title' => $attributestr));
                    }
                } else {
                    $paramurl['view'] = SURVEYPRO_READONLYRESPONSE;
                    $icons = $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view_form.php', $paramurl),
                        new pix_icon('readonly', $readonlyaccessstr, 'surveypro', array('title' => $readonlyaccessstr)),
                        null, array('id' => 'view_submission_'.$submissionsuffix, 'title' => $readonlyaccessstr));
                }

                // Duplicate.
                if ($ismine) { // I am the owner.
                    $displayduplicateicon = $canduplicateownsubmissions;
                } else { // I am not the owner.
                    if ($groupmode == SEPARATEGROUPS) {
                        $displayduplicateicon = $groupuser && $canduplicateotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS.
                        $displayduplicateicon = $canduplicateotherssubmissions;
                    }
                }
                if ($displayduplicateicon) { // I am the owner or a groupmate.
                    $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                    $cansubmitmore = $utilityman->can_submit_more($submission->userid); // The copy will be assigned to the same owner.
                    if ($cansubmitmore) {
                        $paramurl = $paramurlbase;
                        $paramurl['submissionid'] = $submission->submissionid;
                        $paramurl['sesskey'] = sesskey();
                        $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;
                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view.php', $paramurl),
                            new pix_icon('t/copy', $duplicatestr, 'moodle', array('title' => $duplicatestr)),
                            null, array('id' => 'duplicate_submission_'.$submissionsuffix, 'title' => $duplicatestr));
                    }
                }

                // Delete.
                $paramurl = $paramurlbase;
                $paramurl['submissionid'] = $submission->submissionid;
                if ($ismine) { // I am the owner.
                    $displaydeleteicon = $candeleteownsubmissions;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $displaydeleteicon = $groupuser && $candeleteotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS.
                        $displaydeleteicon = $candeleteotherssubmissions;
                    }
                }
                if ($displaydeleteicon) {
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view.php', $paramurl),
                        new pix_icon('t/delete', $deletestr, 'moodle', array('title' => $deletestr)),
                        null, array('id' => 'delete_submission_'.$submissionsuffix, 'title' => $deletestr));
                }

                // Download to pdf.
                if ($cansavesubmissiontopdf) {
                    $paramurl = $paramurlbase;
                    $paramurl['submissionid'] = $submission->submissionid;
                    $paramurl['view'] = SURVEYPRO_RESPONSETOPDF;
                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/view.php', $paramurl),
                        new pix_icon('i/export', $downloadpdfstr, 'moodle', array('title' => $downloadpdfstr)),
                        null, array('id' => 'pdfdownload_submission_'.$submissionsuffix, 'title' => $downloadpdfstr));
                }

                $tablerow[] = $icons;

                // Add row to the table.
                $table->add_data($tablerow);

                // Before looping, gather information for the "Submission overview".
                if ($submission->status == SURVEYPRO_STATUSCLOSED) {
                    $countclosed++;
                    $closeduserarray[(int)$submission->userid] = 1;
                }
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    $countinprogress++;
                    $inprogressuserarray[(int)$submission->userid] = 1;
                }
            }
        }
        $submissions->close();

        $distinctusers = count($closeduserarray + $inprogressuserarray);
        $closeduser = count($closeduserarray);
        $inprogressuser = count($inprogressuserarray);
        $this->display_submissions_overview($distinctusers, $countclosed, $closeduser, $countinprogress, $inprogressuser);

        $table->summary = get_string('submissionslist', 'mod_surveypro');
        $table->print_html();

        // If this is the output of a search and nothing has been found add a way to show all submissions.
        if (!isset($tablerow) && ($this->searchquery)) {
            $url = new moodle_url('/mod/surveypro/view.php', array('id' => $this->cm->id));
            echo $OUTPUT->box($OUTPUT->single_button($url, get_string('showallsubmissions', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
        }
    }

    /**
     * Display buttons in the "view submissions" page according to capabilities and already sent submissions.
     *
     * @return void
     */
    public function show_action_buttons() {
        global $OUTPUT, $USER;

        $cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context, null, true);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context, null, true);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);

        // Begin of: is the button to add one more response going to be the page?
        $timenow = time();
        $userid = ($canseeotherssubmissions) ? null : $USER->id;
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $countclosed = $utilityman->has_submissions(true, SURVEYPRO_STATUSCLOSED, $userid);
        $inprogress = $utilityman->has_submissions(true, SURVEYPRO_STATUSINPROGRESS, $userid);
        $next = $countclosed + $inprogress + 1;

        $roles = get_roles_used_in_context($this->context);
        $addnew = count(array_keys($roles));
        $addnew = $addnew && $cansubmit;
        $addnew = $addnew && $this->hasitems;
        if ($this->surveypro->timeopen) {
            $addnew = $addnew && ($this->surveypro->timeopen < $timenow);
        }
        if ($this->surveypro->timeclose) {
            $addnew = $addnew && ($this->surveypro->timeclose > $timenow);
        }
        if (!$canignoremaxentries) {
            $addnew = $addnew && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
        }
        // End of: is the button to add one more response going to be the page?

        // Begin of: is the button to delete all responses going to be the page?
        $deleteall = $candeleteownsubmissions;
        $deleteall = $deleteall && $candeleteotherssubmissions;
        $deleteall = $deleteall && empty($this->searchquery);
        $deleteall = $deleteall && empty($_GET['tifirst']); // Hide the deleteall button if only partial responses are shown.
        $deleteall = $deleteall && empty($_GET['tilast']);  // Hide the deleteall button if only partial responses are shown.
        $deleteall = $deleteall && ($next > 2);
        // End of: is the button to delete all responses going to be the page?

        $buttoncount = 0;
        if ($addnew) {
            $addurl = new moodle_url('/mod/surveypro/view_form.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            $buttoncount = 1;
        }
        if ($deleteall) {
            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
            $paramurl['sesskey'] = sesskey();

            $deleteurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $buttoncount++;
        }

        if ($buttoncount == 0) {
            return;
        }

        if ($buttoncount == 1) {
            if ($addnew) {
                echo $OUTPUT->box($OUTPUT->single_button($addurl, get_string('addnewsubmission', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
            }

            if ($deleteall) {
                echo $OUTPUT->box($OUTPUT->single_button($deleteurl, get_string('deleteallsubmissions', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
            }
        } else {
            $addbutton = new single_button($addurl, get_string('addnewsubmission', 'mod_surveypro'), 'get', array('class' => 'buttons'));
            $deleteallbutton = new single_button($deleteurl, get_string('deleteallsubmissions', 'mod_surveypro'), 'get', array('class' => 'buttons'));

            // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
            // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo html_writer::tag('div', $OUTPUT->render($addbutton).$OUTPUT->render($deleteallbutton), array('class' => 'buttons'));
            echo $OUTPUT->box_end();
        }
    }

    /**
     * Redirect to layout_manage.php?s=xxx the user asking to go to /view.php?id=yyy if the survey has no items.
     *
     * I HATE software thinking for me
     * Because of this I ALWAYS want to go where I ask, even if the place I ask is not supposed to be accessed by me
     * In this particular case, I want a message explaining WHY the place I asked is not supposed to be accessed by me
     * I NEVER want to be silently redirected.
     *
     * By default accessing a surveypro from a course (/view.php?id=yyy), the "predefined" landing page should be:
     *     -> for admin/editing teacher:
     *         -> if no items were created: layout_manage.php
     *         -> if items were already created: view.php with the submission list
     *     -> for students: ALWAYS view.php with the submission list
     *
     * So the software HAS TO decide where to send the admin/editing teacher when he arrives from a course
     * So in the view.php I MUST add a code snippet TAKING THE DECISION for the user
     *
     * The problem rises up when the admin/editing teacher decides to go where he should not go, alias in:
     *     -> layout_manage.php even if items were already created
     *     -> view.php with the submission list even if no items were created
     *
     * The first request is a false problem, because the admin/editing teacher is always allowed to go there
     * The second request is allowed by the introduction of the parameter &force=1 in the URL of the TAB
     *     When the admin/editing teacher asks for view.php by clicking the corresponding TAB he asks for view.php?id=yyy&force=1
     *         and the software decision is omitted
     *     As opposite:
     *     When the admin/editing teacher arrives from a course (so he doesn't ask for a specific page), he is sent to land in view.php?id=yyy
     *         and the decision is taken here
     *
     * @return void
     */
    public function noitem_redirect() {
        if (!$this->hasitems) {
            $paramurl = array('s' => $this->surveypro->id);
            $redirecturl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
            redirect($redirecturl);
        }
    }

    /**
     * Trigger the all_submissions_viewed event.
     *
     * @return void
     */
    public function trigger_event() {
        // Event: all_submissions_viewed.
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\all_submissions_viewed::create($eventdata);
        $event->trigger();
    }

    /**
     * Execute actions requested using icons in the submission table.
     *
     * @return void
     */
    public function actions_execution() {
        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                break;
            case SURVEYPRO_DUPLICATERESPONSE:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                    $utilityman->duplicate_submissions(array('id' => $this->submissionid));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETERESPONSE:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                    $utilityman->delete_submissions(array('id' => $this->submissionid));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                    $utilityman->delete_submissions(array('surveyproid' => $this->surveypro->id));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            default:
                $message = 'Unexpected $this->action = '.$this->action;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Fork the user interaction on the basis of the action required.
     *
     * @return void
     */
    public function actions_feedback() {
        switch ($this->action) {
            case SURVEYPRO_NOACTION:
                break;
            case SURVEYPRO_DUPLICATERESPONSE:
                $this->one_submission_duplication_feedback();
                break;
            case SURVEYPRO_DELETERESPONSE:
                $this->one_submission_deletion_feedback();
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                $this->all_submission_deletion_feedback();
                break;
            default:
                $message = 'Unexpected $this->action = '.$this->action;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * User interaction for single submission duplication.
     *
     * If the action is missing confirmation: ask.
     * If the action was not confirmed: notify it.
     * If the action was executed: notify it.
     *
     * @return void
     */
    public function one_submission_duplication_feedback() {
        global $USER, $DB, $OUTPUT;

        switch ($this->confirm) {
            case SURVEYPRO_UNCONFIRMED:
                // Ask for confirmation.
                $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));

                $a = new stdClass();
                $a->timecreated = userdate($submission->timecreated);
                $a->timemodified = userdate($submission->timemodified);
                if ($submission->userid != $USER->id) {
                    $user = $DB->get_record('user', array('id' => $submission->userid), user_picture::fields());
                    $a->fullname = fullname($user);
                    if ($a->timemodified == 0) {
                        $message = get_string('askduplicateresponsenevermodified', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('askduplicateresponse', 'mod_surveypro', $a);
                    }
                } else {
                    if ($a->timemodified == 0) {
                        $message = get_string('askduplicatemyresponsenevermodified', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('askduplicatemyresponse', 'mod_surveypro', $a);
                    }
                }

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DUPLICATERESPONSE);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['submissionid'] = $this->submissionid;
                $urlyes = new moodle_url('/mod/surveypro/view.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/view.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            case SURVEYPRO_ACTION_EXECUTED:
                echo $OUTPUT->notification(get_string('responseduplicated', 'mod_surveypro'), 'notifysuccess');
                break;
            case SURVEYPRO_CONFIRMED_NO:
                echo $OUTPUT->notification(get_string('usercanceled', 'mod_surveypro'), 'notifymessage');
                break;
            default:
                $message = 'Unexpected $this->confirm = '.$this->confirm;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * User interaction for single submission deletion.
     *
     * If the action is missing confirmation: ask.
     * If the action was not confirmed: notify it.
     * If the action was executed: notify it.
     *
     * @return void
     */
    public function one_submission_deletion_feedback() {
        global $USER, $DB, $OUTPUT;

        switch ($this->confirm) {
            case SURVEYPRO_UNCONFIRMED:
                // Ask for confirmation.
                $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));

                $a = new stdClass();
                $a->timecreated = userdate($submission->timecreated);
                $a->timemodified = userdate($submission->timemodified);
                if ($submission->userid != $USER->id) {
                    $user = $DB->get_record('user', array('id' => $submission->userid), user_picture::fields());
                    $a->fullname = fullname($user);
                    if ($a->timemodified == 0) {
                        $message = get_string('askdeleteresponsenevermodified', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('askdeleteresponse', 'mod_surveypro', $a);
                    }
                } else {
                    if ($a->timemodified == 0) {
                        $message = get_string('askdeletemyresponsenevermodified', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('askdeletemyresponse', 'mod_surveypro', $a);
                    }
                }

                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETERESPONSE);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $optionsyes['submissionid'] = $this->submissionid;
                $urlyes = new moodle_url('/mod/surveypro/view.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/view.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            case SURVEYPRO_ACTION_EXECUTED:
                echo $OUTPUT->notification(get_string('responsedeleted', 'mod_surveypro'), 'notifysuccess');
                break;
            case SURVEYPRO_CONFIRMED_NO:
                echo $OUTPUT->notification(get_string('usercanceled', 'mod_surveypro'), 'notifymessage');
                break;
            default:
                $message = 'Unexpected $this->confirm = '.$this->confirm;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * User interaction for all submissions deletion.
     *
     * If the action is missing confirmation: ask.
     * If the action was not confirmed: notify it.
     * If the action was executed: notify it.
     *
     * @return void
     */
    public function all_submission_deletion_feedback() {
        global $OUTPUT;

        switch ($this->confirm) {
            case SURVEYPRO_UNCONFIRMED:
                // Ask for confirmation.
                $message = get_string('askdeleteallresponses', 'mod_surveypro');
                $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETEALLRESPONSES);

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $urlyes = new moodle_url('/mod/surveypro/view.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('continue'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/view.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));
                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            case SURVEYPRO_ACTION_EXECUTED:
                echo $OUTPUT->notification(get_string('allsubmissionsdeleted', 'mod_surveypro'), 'notifymessage');
                break;
            case SURVEYPRO_CONFIRMED_NO:
                echo $OUTPUT->notification(get_string('usercanceled', 'mod_surveypro'), 'notifymessage');
                break;
            default:
                $message = 'Unexpected $this->confirm = '.$this->confirm;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Display submissions overview.
     *
     * The output is supposed to look like:
     *     17 responses submitted by 2 user
     *     3 'in progress' responses submitted by 1 user
     *     14 'closed' responses submitted by 2 user
     *
     * and finally, if a query is filtering the output, a button to get all the submissions.
     *
     * @param int $distinctusers
     * @param int $countclosed
     * @param int $closedusers
     * @param int $countinprogress
     * @param int $inprogressusers
     * @return void
     */
    public function display_submissions_overview($distinctusers, $countclosed, $closedusers, $countinprogress, $inprogressusers) {
        global $OUTPUT;

        $strstatusinprogress = get_string('statusinprogress', 'mod_surveypro');
        $strstatusclosed = get_string('statusclosed', 'mod_surveypro');
        $struser = get_string('loweruser', 'mod_surveypro');
        $strusers = get_string('lowerusers', 'mod_surveypro');
        $strresponse = get_string('response', 'mod_surveypro');
        $strresponses = get_string('responses', 'mod_surveypro');

        echo html_writer::start_tag('fieldset', array('class' => 'generalbox', 'style' => 'padding-bottom: 15px;'));
        echo html_writer::start_tag('legend', array('class' => 'coverinfolegend'));
        echo get_string('submissions_welcome', 'mod_surveypro');
        echo html_writer::end_tag('legend');

        $allsubmissions = $countinprogress + $countclosed;
        if ($allsubmissions) {
            if (!empty($countinprogress) && !empty($countclosed)) {
                $a = new stdClass();
                $a->submissions = $allsubmissions;
                $a->usercount = $distinctusers;
                $a->responses = ($a->submissions == 1) ? $strresponse : $strresponses;
                $a->users = ($a->usercount == 1) ? $struser : $strusers;
                $message = get_string('submissions_all', 'mod_surveypro', $a);
                echo $OUTPUT->container($message, 'mdl-left');
            }

            if (!empty($countinprogress)) {
                $a = new stdClass();
                $a->submissions = $countinprogress;
                $a->usercount = $inprogressusers;
                $a->status = $strstatusinprogress;
                $a->responses = ($a->submissions == 1) ? $strresponse : $strresponses;
                $a->users = ($a->usercount == 1) ? $struser : $strusers;
                $message = get_string('submissions_detail', 'mod_surveypro', $a);
                echo $OUTPUT->container($message, 'mdl-left');
            }

            if (!empty($countclosed)) {
                $a = new stdClass();
                $a->submissions = $countclosed;
                $a->usercount = $closedusers;
                $a->status = $strstatusclosed;
                $a->responses = ($a->submissions == 1) ? $strresponse : $strresponses;
                $a->users = ($a->usercount == 1) ? $struser : $strusers;
                $message = get_string('submissions_detail', 'mod_surveypro', $a);
                echo $OUTPUT->container($message, 'mdl-left');
            }
        }

        if ($this->searchquery) {
            $findallurl = new moodle_url('/mod/surveypro/view.php', array('id' => $this->cm->id));
            $label = get_string('showallsubmissions', 'mod_surveypro');

            echo $OUTPUT->single_button($findallurl, $label, 'get', array('class' => 'box clearfix mdl-align'));
        }
        echo html_writer::end_tag('fieldset');
    }

    /**
     * Prevent direct user input.
     *
     * @param bool $confirm
     * @return void
     */
    private function prevent_direct_user_input($confirm) {
        global $COURSE, $USER, $DB;

        if ($this->action == SURVEYPRO_NOACTION) {
            return true;
        }
        if ($confirm == SURVEYPRO_ACTION_EXECUTED) {
            return true;
        }
        if ($confirm == SURVEYPRO_CONFIRMED_NO) {
            return true;
        }

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context, null, true);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context, null, true);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context, null, true);

        if ($this->action == SURVEYPRO_NOACTION) {
            return true;
        }
        if ($confirm == SURVEYPRO_ACTION_EXECUTED) {
            return true;
        }
        if ($confirm == SURVEYPRO_CONFIRMED_NO) {
            return true;
        }

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context, null, true);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context, null, true);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context, null, true);

        if ($this->action != SURVEYPRO_DELETEALLRESPONSES) { // If a specific submission is involved.
            $ownerid = $DB->get_field('surveypro_submission', 'userid', array('id' => $this->submissionid), IGNORE_MISSING);
            if (!$ownerid) {
                print_error('incorrectaccessdetected', 'mod_surveypro');
            }

            $ismine = ($ownerid == $USER->id);
            if (!$ismine) {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode == SEPARATEGROUPS) {
                    $mygroupmates = surveypro_groupmates($this->cm);
                    $groupuser = in_array($ownerid, $mygroupmates);
                }
            }
        }

        switch ($this->action) {
            case SURVEYPRO_DELETERESPONSE:
                if ($ismine) {
                    $allowed = $candeleteownsubmissions;
                } else {
                    if (!$groupmode) {
                        $allowed = $candeleteotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $candeleteotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS.
                            $allowed = $candeleteotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                $allowed = $candeleteotherssubmissions;
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
                        $allowed = $canseeotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $canseeotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS.
                            $allowed = $canseeotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_EDITRESPONSE:
                if ($ismine) {
                    $allowed = $caneditownsubmissions;
                } else {
                    if (!$groupmode) {
                        $allowed = $caneditotherssubmissions;
                    } else {
                        if ($groupmode == SEPARATEGROUPS) {
                            $allowed = $groupuser && $caneditotherssubmissions;
                        } else { // NOGROUPS || VISIBLEGROUPS.
                            $allowed = $caneditotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_RESPONSETOPDF:
                $allowed = $cansavesubmissiontopdf;
                break;
            default:
                $allowed = false;
        }

        if (!$allowed) {
            print_error('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Make one submission available in PDF.
     *
     * @return void
     */
    private function submission_to_pdf() {
        global $CFG, $DB;

        if ($this->view != SURVEYPRO_RESPONSETOPDF) {
            return;
        }

        // Event: submissioninpdf_downloaded.
        $eventdata = array('context' => $this->context, 'objectid' => $this->submissionid);
        $eventdata['other'] = array('view' => SURVEYPRO_RESPONSETOPDF);
        $event = \mod_surveypro\event\submissioninpdf_downloaded::create($eventdata);
        $event->trigger();

        require_once($CFG->libdir.'/tcpdf/tcpdf.php');
        require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

        $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $userdatarecord = $DB->get_records('surveypro_answer', array('submissionid' => $this->submissionid), '', 'itemid, id, content');

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, $user->id, true);
        list($where, $params) = surveypro_fetch_items_seeds($this->surveypro->id, $canaccessreserveditems);

        // I am not allowed to get ONLY answers from surveypro_answer
        // because I also need to gather info about fieldsets and labels.
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin');

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information.
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('moodle-mod_surveypro');
        $pdf->SetTitle('User response');
        $pdf->SetSubject('Single response in PDF');

        // Set default header data.
        $textheader = get_string('responseauthor', 'mod_surveypro');
        $textheader .= fullname($user);
        $textheader .= "\n";
        $textheader .= get_string('responsetimecreated', 'mod_surveypro');
        $textheader .= userdate($submission->timecreated);
        if ($submission->timemodified) {
            $textheader .= get_string('responsetimemodified', 'mod_surveypro');
            $textheader .= userdate($submission->timemodified);
        }

        $pdf->SetHeaderData('', 0, $this->surveypro->name, $textheader, array(0, 64, 255), array(0, 64, 128));
        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // Set header and footer fonts.
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set margins.
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetDrawColorArray(array(0, 64, 128));
        // Set auto page breaks.
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();

        $col1nunit = 1;
        $col2nunit = 6;
        $col3nunit = 3;
        $firstcolwidth = $pdf->getPageWidth();
        $firstcolwidth -= PDF_MARGIN_LEFT;
        $firstcolwidth -= PDF_MARGIN_RIGHT;
        $unitsum = $col1nunit + $col2nunit + $col3nunit;

        $firstcolwidth = number_format($col1nunit * 100 / $unitsum, 2);
        $secondcolwidth = number_format($col2nunit * 100 / $unitsum, 2);
        $thirdcolwidth = number_format($col3nunit * 100 / $unitsum, 2);
        $lasttwocolumns = $secondcolwidth + $thirdcolwidth;

        $htmllabeltemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmllabeltemplate .= '<td style="width:'.$lasttwocolumns.'%;text-align:left;">@@col2@@</td></tr></table>';

        $htmlstandardtemplate = '<table style="width:100%;"><tr><td style="width:'.$firstcolwidth.'%;text-align:left;">@@col1@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$secondcolwidth.'%;text-align:left;">@@col2@@</td>';
        $htmlstandardtemplate .= '<td style="width:'.$thirdcolwidth.'%;text-align:left;">@@col3@@</td></tr></table>';

        $border = array('T' => array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 1, 'color' => array(179, 219, 181)));
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);
            // Pagebreaks are not selected by surveypro_fetch_items_seeds.
            $template = $item::item_get_pdf_template();
            if ($template == SURVEYPRO_2COLUMNSTEMPLATE) {
                // First column.
                $html = $htmllabeltemplate;
                $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col1@@', $content, $html);

                // Second column: colspan 2.
                // I can't use $content = trim(strip_tags($item->get_content()), " \t\n\r"); because I want images in the PDF.
                $content = $item->get_content();
                // Why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
                // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col2@@', $content, $html);
                $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true); // This is like span 2.
            }

            if ($template == SURVEYPRO_3COLUMNSTEMPLATE) {
                // First column.
                $html = $htmlstandardtemplate;
                $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
                $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col1@@', $content, $html);

                // Second column.
                // I can't use $content = trim(strip_tags($item->get_content()), " \t\n\r"); because I want images in the PDF.
                $content = $item->get_content();
                // Why does $content here is already html encoded so that I do not have to apply htmlspecialchars?
                // Because it comes from an editor?
                // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                $html = str_replace('@@col2@@', $content, $html);

                // Third column.
                if (isset($userdatarecord[$item->get_itemid()])) {
                    $content = $item->userform_db_to_export($userdatarecord[$item->get_itemid()], SURVEYPRO_FIRENDLYFORMAT);
                    if ($item->get_plugin() != 'textarea') { // Content does not come from an html editor.
                        $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                        $content = str_replace(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, '<br />', $content);
                    } else { // Content comes from a textarea item.
                        if (!$item->get_useeditor()) { // Content does not come from an html editor.
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
        }

        $filename = $this->surveypro->name.'_'.$this->submissionid.'.pdf';
        $pdf->Output($filename, 'D');
        die();
    }
}
