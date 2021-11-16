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

/**
 * The class managing users submissions
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_view_submissions {

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

        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);
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
     * Teachers is the role of users usually accessing reports.
     * They are "teachers" so they care about "students" and nothing more.
     * If, at import time, some records go under the admin ownership
     * the teacher is not supposed to see them because admin is not a student.
     * In this case, if the teacher wants to see submissions owned by admin, HE HAS TO ENROLL ADMIN with some role.
     *
     * Different is the story for the admin.
     * If an admin wants to make a report, he will see EACH RESPONSE SUBMITTED
     * without care to the role of the owner of the submission.
     *
     * @param flexible_table $table
     * @return array($sql, $whereparams);
     */
    public function get_submissions_sql($table) {
        global $DB, $COURSE, $USER;

        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');

        $emptysql = 'SELECT DISTINCT s.*, s.id as submissionid'.$userfieldsapi->selects.'
                     FROM {surveypro_submission} s
                         JOIN {user} u ON u.id = s.userid
                     WHERE u.id = :userid';

        $coursecontext = context_course::instance($COURSE->id);
        list($enrolsql, $eparams) = get_enrolled_sql($coursecontext);

        $sql = 'SELECT COUNT(eu.id)
                FROM ('.$enrolsql.') eu';
        // If there are no enrolled people, give up!
        if (!$DB->count_records_sql($sql, $eparams)) {
            if (!$canviewhiddenactivities) {
                return array($emptysql, array('userid' => -1));
            }
        }

        $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            $mygroups = groups_get_all_groups($COURSE->id, $USER->id, $this->cm->groupingid);
            $mygroups = array_keys($mygroups);
            if (!count($mygroups)) { // User is not in any group.
                // This is a student that has not been added to any group.
                // The sql needs to return an empty set.
                return array($emptysql, array('userid' => -1));
            }
        }

        $whereparams = array();

        $sql = 'SELECT s.id as submissionid, s.surveyproid, s.status, s.userid, s.timecreated, s.timemodified';
        $sql .= $userfieldsapi->selects;
        $sql .= ' FROM {surveypro_submission} s
                  JOIN {user} u ON u.id = s.userid';

        if (!$canviewhiddenactivities) {
            $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';
        }

        if ($this->searchquery) {
            // This will be re-send to URL for next page reload, whether requested with a sort, for instance.
            $whereparams['searchquery'] = $this->searchquery;

            $searchrestrictions = unserialize($this->searchquery);

            $sqlanswer = 'SELECT a.submissionid, COUNT(a.submissionid) as matchcount
              FROM {surveypro_answer} a';

            // (a.itemid = 7720 AND a.content = 0) OR (a.itemid = 7722 AND a.content = 1))
            // (a.itemid = 1219 AND $DB->sql_like('a.content', ':content_1219', false)).
            $userquery = array();
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
                $classname = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin.'_'.$itemseed->type;
                // Ask to the item class how to write the query.
                list($whereclause, $whereparam) = $classname::response_get_whereclause($itemid, $searchrestriction);
                $userquery[] = '(a.itemid = '.$itemid.' AND '.$whereclause.')';
                $whereparams['content_'.$itemid] = $whereparam;
            }
            $sqlanswer .= ' WHERE ('.implode(' OR ', $userquery).')';

            $sqlanswer .= ' GROUP BY a.submissionid';
            $sqlanswer .= ' HAVING matchcount = :matchcount';
            $whereparams['matchcount'] = count($userquery);

            // Finally, continue writing $sql.
            $sql .= ' JOIN ('.$sqlanswer.') a ON a.submissionid = s.id';
        }

        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid';
        }

        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        if (!$canseeotherssubmissions) {
            // Restrict to your submissions only.
            $sql .= ' AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        // Manage table alphabetical filter.
        list($wherefilter, $wherefilterparams) = $table->get_sql_where();
        if ($wherefilter) {
            $sql .= ' AND '.$wherefilter;
            $whereparams = $whereparams + $wherefilterparams;
        }

        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            // Restrict to your groups only.
            list($insql, $subparams) = $DB->get_in_or_equal($mygroups, SQL_PARAMS_NAMED, 'groupid');
            $whereparams = array_merge($whereparams, $subparams);
            $sql .= ' AND gm.groupid '.$insql;
        }

        if ($table->get_sql_sort()) {
            // Sort coming from $table->get_sql_sort().
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY s.timecreated';
        }

        if (!$canviewhiddenactivities) {
            $whereparams = array_merge($whereparams, $eparams);
        }

        return array($sql, $whereparams);
    }

    /**
     * Get counters.
     *
     * @param flexible_table $table
     * @return array of cunters
     */
    public function get_counter($table) {
        global $DB, $COURSE, $USER;

        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');

        $coursecontext = context_course::instance($COURSE->id);
        list($enrolsql, $eparams) = get_enrolled_sql($coursecontext);

        $sql = 'SELECT COUNT(eu.id)
                FROM ('.$enrolsql.') eu';
        // If there are no enrolled people, give up!
        if (!$enrolledusers = $DB->count_records_sql($sql, $eparams)) {
            if (!$canviewhiddenactivities) {
                return 0;
            }
        }

        $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            $mygroups = groups_get_all_groups($COURSE->id, $USER->id, $this->cm->groupingid);
            $mygroups = array_keys($mygroups);
            if (!count($mygroups)) { // User is not in any group.
                // This is a student that has not been added to any group.
                // The sql needs to return an empty set.
                return 0;
            }
        }

        $whereparams = array();

        $sql = 'SELECT s.status, COUNT(s.id) submissions, COUNT(DISTINCT(u.id)) users';
        $sql .= ' FROM {surveypro_submission} s
                  JOIN {user} u ON u.id = s.userid';

        if (!$canviewhiddenactivities) {
            $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';
        }

        if ($this->searchquery) {
            // This will be re-send to URL for next page reload, whether requested with a sort, for instance.
            $whereparams['searchquery'] = $this->searchquery;

            $searchrestrictions = unserialize($this->searchquery);

            $sqlanswer = 'SELECT a.submissionid, COUNT(a.submissionid) as matchcount
              FROM {surveypro_answer} a';

            // (a.itemid = 7720 AND a.content = 0) OR (a.itemid = 7722 AND a.content = 1))
            // (a.itemid = 1219 AND $DB->sql_like('a.content', ':content_1219', false));
            $userquery = array();
            foreach ($searchrestrictions as $itemid => $searchrestriction) {
                $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'type, plugin', MUST_EXIST);
                $classname = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin.'_'.$itemseed->type;
                // Ask to the item class how to write the query.
                list($whereclause, $whereparam) = $classname::response_get_whereclause($itemid, $searchrestriction);
                $userquery[] = '(a.itemid = '.$itemid.' AND '.$whereclause.')';
                $whereparams['content_'.$itemid] = $whereparam;
            }
            $sqlanswer .= ' WHERE ('.implode(' OR ', $userquery).')';

            $sqlanswer .= ' GROUP BY a.submissionid';
            $sqlanswer .= ' HAVING matchcount = :matchcount';
            $whereparams['matchcount'] = count($userquery);

            // Finally, continue writing $sql.
            $sql .= ' JOIN ('.$sqlanswer.') a ON a.submissionid = s.id';
        }

        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid';
        }

        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        if (!$canseeotherssubmissions) {
            // Restrict to your submissions only.
            $sql .= ' AND s.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        // Manage table alphabetical filter.
        list($wherefilter, $wherefilterparams) = $table->get_sql_where();
        if ($wherefilter) {
            $sql .= ' AND '.$wherefilter;
            $whereparams = $whereparams + $wherefilterparams;
        }

        if (($groupmode == SEPARATEGROUPS) && (!$canaccessallgroups)) {
            // Restrict to your groups only.
            list($insql, $subparams) = $DB->get_in_or_equal($mygroups, SQL_PARAMS_NAMED, 'groupid');
            $whereparams = array_merge($whereparams, $subparams);
            $sql .= ' AND gm.groupid '.$insql;
        }

        $sql .= ' GROUP BY s.status';

        if (!$canviewhiddenactivities) {
            $whereparams = array_merge($whereparams, $eparams);
        }

        $counters = $DB->get_records_sql($sql, $whereparams);

        $counter = array();
        $counter['enrolled'] = $enrolledusers;
        if (isset($counters[SURVEYPRO_STATUSINPROGRESS])) {
            $counter['inprogresssubmissions'] = $counters[SURVEYPRO_STATUSINPROGRESS]->submissions;
            $counter['inprogressusers'] = $counters[SURVEYPRO_STATUSINPROGRESS]->users;
        } else {
            $counter['inprogresssubmissions'] = 0;
            $counter['inprogressusers'] = 0;
        }
        if (isset($counters[SURVEYPRO_STATUSCLOSED])) {
            $counter['closedsubmissions'] = $counters[SURVEYPRO_STATUSCLOSED]->submissions;
            $counter['closedusers'] = $counters[SURVEYPRO_STATUSCLOSED]->users;
        } else {
            $counter['closedsubmissions'] = 0;
            $counter['closedusers'] = 0;
        }

        $sql = str_replace('s.status, COUNT(s.id) submissions, ', '', $sql);
        $sql = str_replace(' GROUP BY s.status', '', $sql);
        $counters = $DB->get_record_sql($sql, $whereparams);
        $counter['allusers'] = (int) $counters->users;

        return $counter;
    }

    /**
     * Display the submissions table.
     *
     * @return void
     */
    public function display_submissions_table() {
        global $CFG, $OUTPUT, $DB, $COURSE, $USER;

        require_once($CFG->libdir.'/tablelib.php');

        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $canduplicateownsubmissions = has_capability('mod/surveypro:duplicateownsubmissions', $this->context);
        $canduplicateotherssubmissions = has_capability('mod/surveypro:duplicateotherssubmissions', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        $table = new flexible_table('submissionslist');

        if ($canseeotherssubmissions) {
            $table->initialbars(true);
        }

        $paramurl = array();
        $paramurl['id'] = $this->cm->id;
        if ($this->searchquery) {
            $paramurl['searchquery'] = $this->searchquery;
        }
        $baseurl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
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

        $neverstr = get_string('never');

        $counter = $this->get_counter($table);
        $table->pagesize(20, $counter['closedsubmissions'] + $counter['inprogresssubmissions']);

        $this->display_submissions_overview($counter['enrolled'], $counter['allusers'],
                                            $counter['closedsubmissions'], $counter['closedusers'],
                                            $counter['inprogresssubmissions'], $counter['inprogressusers']);

        list($sql, $whereparams) = $this->get_submissions_sql($table);

        $submissions = $DB->get_recordset_sql($sql, $whereparams, $table->get_page_start(), $table->get_page_size());
        if ($submissions->valid()) {

            $iconparams = array();

            $nonhistoryeditstr = get_string('edit');
            $iconparams['title'] = $nonhistoryeditstr;
            $nonhistoryediticn = new pix_icon('t/edit', $nonhistoryeditstr, 'moodle', $iconparams);

            $readonlyaccessstr = get_string('readonlyaccess', 'mod_surveypro');
            $iconparams['title'] = $readonlyaccessstr;
            $readonlyicn = new pix_icon('readonly', $readonlyaccessstr, 'surveypro', $iconparams);

            $duplicatestr = get_string('duplicate');
            $iconparams['title'] = $duplicatestr;
            $duplicateicn = new pix_icon('t/copy', $duplicatestr, 'moodle', $iconparams);

            if ($this->surveypro->history) {
                $attributestr = get_string('editcopy', 'mod_surveypro');
                $linkidprefix = 'editcopy_submission_';
            } else {
                $attributestr = $nonhistoryeditstr;
                $linkidprefix = 'edit_submission_';
            }
            $iconparams['title'] = $attributestr;
            $attributeicn = new pix_icon('t/edit', $attributestr, 'moodle', $iconparams);

            $deletestr = get_string('delete');
            $iconparams['title'] = $deletestr;
            $deleteicn = new pix_icon('t/delete', $deletestr, 'moodle', $iconparams);

            $downloadpdfstr = get_string('downloadpdf', 'mod_surveypro');
            $iconparams['title'] = $downloadpdfstr;
            $downloadpdficn = new pix_icon('t/download', $downloadpdfstr, 'moodle', $iconparams);

            if ($groupmode = groups_get_activity_groupmode($this->cm, $COURSE)) {
                if ($groupmode == SEPARATEGROUPS) {
                    $utilitysubmissionman = new mod_surveypro_utility_submission($this->cm, $this->surveypro);
                    $mygroupmates = $utilitysubmissionman->get_groupmates($this->cm);
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
                        if ($canaccessallgroups) {
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
                        $link = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
                        $paramlink = array('id' => 'edit_submission_'.$submissionsuffix, 'title' => $nonhistoryeditstr);
                        $icons = $OUTPUT->action_icon($link, $nonhistoryediticn, null, $paramlink);
                    } else {
                        // Here title and alt depend from $this->surveypro->history.
                        $link = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
                        $paramlink = array('id' => $linkidprefix.$submissionsuffix, 'title' => $attributestr);
                        $icons = $OUTPUT->action_icon($link, $attributeicn, null, $paramlink);
                    }
                } else {
                    $paramurl['view'] = SURVEYPRO_READONLYRESPONSE;

                    $link = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
                    $paramlink = array('id' => 'view_submission_'.$submissionsuffix, 'title' => $readonlyaccessstr);
                    $icons = $OUTPUT->action_icon($link, $readonlyicn, null, $paramlink);
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
                    $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
                    $cansubmitmore = $utilitylayoutman->can_submit_more($submission->userid);
                    if ($cansubmitmore) { // The copy will be assigned to the same owner.
                        $paramurl = $paramurlbase;
                        $paramurl['submissionid'] = $submission->submissionid;
                        $paramurl['sesskey'] = sesskey();
                        $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;

                        $link = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
                        $paramlink = array('id' => 'duplicate_submission_'.$submissionsuffix, 'title' => $duplicatestr);
                        $icons .= $OUTPUT->action_icon($link, $duplicateicn, null, $paramlink);
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

                    $link = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
                    $paramlink = array('id' => 'delete_submission_'.$submissionsuffix, 'title' => $deletestr);
                    $icons .= $OUTPUT->action_icon($link, $deleteicn, null, $paramlink);
                }

                // Download to pdf.
                if ($cansavesubmissiontopdf) {
                    $paramurl = $paramurlbase;
                    $paramurl['submissionid'] = $submission->submissionid;
                    $paramurl['view'] = SURVEYPRO_RESPONSETOPDF;

                    $link = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
                    $paramlink = array('id' => 'pdfdownload_submission_'.$submissionsuffix, 'title' => $downloadpdfstr);
                    $icons .= $OUTPUT->action_icon($link, $downloadpdficn, null, $paramlink);
                }

                $tablerow[] = $icons;

                // Add row to the table.
                $table->add_data($tablerow);
            }
        }
        $submissions->close();

        $table->summary = get_string('submissionslist', 'mod_surveypro');
        $table->print_html();

        // If this is the output of a search and nothing has been found add a way to show all submissions.
        if (!isset($tablerow) && ($this->searchquery)) {
            $url = new moodle_url('/mod/surveypro/view_submissions.php', array('id' => $this->cm->id));
            $label = get_string('showallsubmissions', 'mod_surveypro');
            echo $OUTPUT->box($OUTPUT->single_button($url, $label, 'get'), 'clearfix mdl-align');
        }
    }

    /**
     * Display buttons in the "view submissions" page according to capabilities and already sent submissions.
     *
     * @param string $tifirst
     * @param string $tilast
     * @return void
     */
    public function show_action_buttons($tifirst, $tilast) {
        global $OUTPUT, $USER;

        $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);

        $cansubmit = has_capability('mod/surveypro:submit', $this->context);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);

        $timenow = time();
        $userid = ($canseeotherssubmissions) ? null : $USER->id;

        $countclosed = $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSCLOSED, $userid);
        $inprogress = $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSINPROGRESS, $userid);
        $next = $countclosed + $inprogress + 1;

        // Begin of: is the button to add one more response going to be in the page?
        $addnew = $utilitylayoutman->is_newresponse_allowed($next);
        // End of: is the button to add one more response going to be the page?

        // Begin of: is the button to delete all responses going to be the page?
        $deleteall = $candeleteownsubmissions;
        $deleteall = $deleteall && $candeleteotherssubmissions;
        $deleteall = $deleteall && empty($this->searchquery);
        $deleteall = $deleteall && empty($tifirst); // Hide the deleteall button if only partial responses are shown.
        $deleteall = $deleteall && empty($tilast);  // Hide the deleteall button if only partial responses are shown.
        $deleteall = $deleteall && ($next > 1);
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

            $deleteurl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
            $buttoncount++;
        }

        if ($buttoncount == 0) {
            return;
        }

        if ($buttoncount == 1) {
            if ($addnew) {
                $label = get_string('addnewsubmission', 'mod_surveypro');
                echo $OUTPUT->box($OUTPUT->single_button($addurl, $label, 'get'), 'clearfix mdl-align');
            }

            if ($deleteall) {
                $label = get_string('deleteallsubmissions', 'mod_surveypro');
                echo $OUTPUT->box($OUTPUT->single_button($deleteurl, $label, 'get'), 'clearfix mdl-align');
            }
        } else {
            $class = array('class' => 'buttons');
            $addbutton = new single_button($addurl, get_string('addnewsubmission', 'mod_surveypro'), 'get', $class);
            $deleteallbutton = new single_button($deleteurl, get_string('deleteallsubmissions', 'mod_surveypro'), 'get', $class);

            // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
            // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo html_writer::tag('div', $OUTPUT->render($addbutton).$OUTPUT->render($deleteallbutton), $class);
            echo $OUTPUT->box_end();
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
                    $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->duplicate_submissions(array('id' => $this->submissionid));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETERESPONSE:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->delete_submissions(array('id' => $this->submissionid));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->delete_submissions(array('surveyproid' => $this->surveypro->id));

                    // Redirect.
                    $paramurl = array();
                    $paramurl['id'] = $this->cm->id;
                    $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $redirecturl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurl);
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
     * Actually display the thanks page.
     *
     * @param int $responsestatus
     * @param int $formview
     * @param int $justsubmitted
     * @return void
     */
    public function show_thanks_page($responsestatus, $formview, $justsubmitted) {
        global $OUTPUT;

        if ($responsestatus == SURVEYPRO_MISSINGMANDATORY) {
            $a = get_string('statusinprogress', 'mod_surveypro');
            $message = get_string('missingmandatory', 'mod_surveypro', $a);
            echo $OUTPUT->notification($message, 'notifyproblem');
        }

        if ($responsestatus == SURVEYPRO_MISSINGVALIDATION) {
            $a = get_string('statusinprogress', 'mod_surveypro');
            $message = get_string('missingvalidation', 'mod_surveypro', $a);
            echo $OUTPUT->notification($message, 'notifyproblem');
        }

        if ($justsubmitted == 1) {
            $message = get_string('basic_editthanks', 'mod_surveypro');
        } else {
            // $justsubmitted == 2. User deserves thanks if available.
            if (!empty($this->surveypro->thankspage)) {
                $htmlbody = $this->surveypro->thankspage;
                $contextid = $this->context->id;
                $component = 'mod_surveypro';
                $filearea = SURVEYPRO_THANKSPAGEFILEAREA;
                $message = file_rewrite_pluginfile_urls($htmlbody, 'pluginfile.php', $contextid, $component, $filearea, null);
            } else {
                $message = get_string('basic_submitthanks', 'mod_surveypro');
            }
        }

        $utilitylayoutman = new mod_surveypro_utility_layout($this->cm, $this->surveypro);
        $cansubmitmore = $utilitylayoutman->can_submit_more();

        $paramurlbase = array('id' => $this->cm->id);
        if ($cansubmitmore) { // If the user is allowed to submit one more response.
            $paramurl = $paramurlbase + array('view' => SURVEYPRO_NEWRESPONSE);
            $buttonurl = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
            $onemore = new single_button($buttonurl, get_string('addnewsubmission', 'mod_surveypro'));

            $buttonurl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurlbase);
            $gotolist = new single_button($buttonurl, get_string('gotolist', 'mod_surveypro'));

            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo html_writer::tag('p', $message);
            echo html_writer::tag('div', $OUTPUT->render($onemore).$OUTPUT->render($gotolist), array('class' => 'buttons'));
            echo $OUTPUT->box_end();
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $buttonurl = new moodle_url('/mod/surveypro/view_submissions.php', $paramurlbase);
            $buttonlabel = get_string('gotolist', 'mod_surveypro');
            echo $OUTPUT->box($OUTPUT->single_button($buttonurl, $buttonlabel, 'get'), 'generalbox centerpara');
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

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));

            $a = new stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid != $USER->id) {
                $user = $DB->get_record('user', array('id' => $submission->userid), user_picture::fields());
                $a->fullname = fullname($user);
                if ($a->timemodified == 0) {
                    $message = get_string('confirm_duplicate1foreignresponse_nevmod', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_duplicate1foreignresponse', 'mod_surveypro', $a);
                }
            } else {
                if ($a->timemodified == 0) {
                    $message = get_string('confirm_duplicate1ownresponse_nevmod', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_duplicate1ownresponse', 'mod_surveypro', $a);
                }
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DUPLICATERESPONSE);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['submissionid'] = $this->submissionid;
            $urlyes = new moodle_url('/mod/surveypro/view_submissions.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('continue'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/view_submissions.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_duplicateresponse', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
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

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));

            $a = new stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid != $USER->id) {
                $user = $DB->get_record('user', array('id' => $submission->userid), user_picture::fields());
                $a->fullname = fullname($user);
                if ($a->timemodified == 0) {
                    $message = get_string('confirm_delete1response_nevmod', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_delete1response', 'mod_surveypro', $a);
                }
            } else {
                if ($a->timemodified == 0) {
                    $message = get_string('confirm_delete1ownresponse_nevmod', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_delete1ownresponse', 'mod_surveypro', $a);
                }
            }

            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETERESPONSE);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['submissionid'] = $this->submissionid;
            $urlyes = new moodle_url('/mod/surveypro/view_submissions.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('continue'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/view_submissions.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_delete1response', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifysuccess');
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
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

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $message = get_string('confirm_deleteallresponses', 'mod_surveypro');
            $optionbase = array('id' => $this->cm->id, 'act' => SURVEYPRO_DELETEALLRESPONSES);

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new moodle_url('/mod/surveypro/view_submissions.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('continue'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/view_submissions.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));
            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_ACTION_EXECUTED) {
            $message = get_string('feedback_deleteallresponses', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
        }
    }

    /**
     * Display submissions overview.
     *
     * The output is supposed to look like:
     *     Enrolled users: 3
     *     17 responses submitted by 2 user
     *     3 'in progress' responses submitted by 1 user
     *     14 'closed' responses submitted by 2 user
     *
     * and finally, if a query is filtering the output, a button to get all the submissions.
     *
     * @param int $enrolledusers
     * @param int $distinctusers
     * @param int $countclosed
     * @param int $closedusers
     * @param int $countinprogress
     * @param int $inprogressusers
     * @return void
     */
    public function display_submissions_overview($enrolledusers, $distinctusers,
                                                 $countclosed, $closedusers,
                                                 $countinprogress, $inprogressusers) {
        global $OUTPUT;

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);

        $strstatusinprogress = get_string('statusinprogress', 'mod_surveypro');
        $strstatusclosed = get_string('statusclosed', 'mod_surveypro');

        echo html_writer::start_tag('fieldset', array('class' => 'generalbox', 'style' => 'padding-bottom: 15px;'));
        echo html_writer::start_tag('legend', array('class' => 'coverinfolegend'));
        echo get_string('submissions_welcome', 'mod_surveypro');
        echo html_writer::end_tag('legend');

        $allsubmissions = $countinprogress + $countclosed;
        if (!empty($allsubmissions)) {
            if ($canseeotherssubmissions) {
                // Enrolled users: 3.
                if ($enrolledusers == 1) {
                    echo $OUTPUT->container(get_string('userenrolled', 'mod_surveypro'), 'mdl-left');
                } else {
                    echo $OUTPUT->container(get_string('usersenrolled', 'mod_surveypro', $enrolledusers), 'mdl-left');
                }
            }

            // 17 responses submitted by 2 user.
            $a = new stdClass();
            $a->submissions = $allsubmissions;
            $a->usercount = $distinctusers;
            if ($allsubmissions == 1) {
                if ($distinctusers == 1) {
                    $message = get_string('submissions_all_1_1', 'mod_surveypro', $a);
                } else {
                    $message = get_string('submissions_all_1_many', 'mod_surveypro', $a);
                }
            } else {
                if ($distinctusers == 1) {
                    $message = get_string('submissions_all_many_1', 'mod_surveypro', $a);
                } else {
                    $message = get_string('submissions_all_many_many', 'mod_surveypro', $a);
                }
            }
            echo $OUTPUT->container($message, 'mdl-left');

            if (!empty($countinprogress)) {
                // 3 'in progress' response submitted by 1 user.
                $a = new stdClass();
                $a->submissions = $countinprogress;
                $a->usercount = $inprogressusers;
                $a->status = $strstatusinprogress;
                if ($countinprogress == 1) {
                    if ($inprogressusers == 1) {
                        $message = get_string('submissions_detail_1_1', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('submissions_detail_1_many', 'mod_surveypro', $a);
                    }
                } else {
                    if ($inprogressusers == 1) {
                        $message = get_string('submissions_detail_many_1', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('submissions_detail_many_many', 'mod_surveypro', $a);
                    }
                }
                echo $OUTPUT->container($message, 'mdl-left');
            }

            if (!empty($countclosed)) {
                // 14 'closed' response submitted by 1 user.
                $a = new stdClass();
                $a->submissions = $countclosed;
                $a->usercount = $closedusers;
                $a->status = $strstatusclosed;
                if ($countclosed == 1) {
                    if ($closedusers == 1) {
                        $message = get_string('submissions_detail_1_1', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('submissions_detail_1_many', 'mod_surveypro', $a);
                    }
                } else {
                    if ($closedusers == 1) {
                        $message = get_string('submissions_detail_many_1', 'mod_surveypro', $a);
                    } else {
                        $message = get_string('submissions_detail_many_many', 'mod_surveypro', $a);
                    }
                }
                echo $OUTPUT->container($message, 'mdl-left');
            }
        }

        if ($this->searchquery) {
            $findallurl = new moodle_url('/mod/surveypro/view_submissions.php', array('id' => $this->cm->id));
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

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context);

        if ($this->action == SURVEYPRO_NOACTION) {
            return true;
        }
        if ($confirm == SURVEYPRO_ACTION_EXECUTED) {
            return true;
        }
        if ($confirm == SURVEYPRO_CONFIRMED_NO) {
            return true;
        }

        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $cansavesubmissiontopdf = has_capability('mod/surveypro:savesubmissiontopdf', $this->context);

        if ($this->action != SURVEYPRO_DELETEALLRESPONSES) { // If a specific submission is involved.
            $ownerid = $DB->get_field('surveypro_submission', 'userid', array('id' => $this->submissionid), IGNORE_MISSING);
            if (!$ownerid) {
                print_error('incorrectaccessdetected', 'mod_surveypro');
            }

            $ismine = ($ownerid == $USER->id);
            if (!$ismine) {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode == SEPARATEGROUPS) {
                    $utilitysubmissionman = new mod_surveypro_utility_submission($cm, $surveypro);
                    $mygroupmates = $utilitysubmissionman->get_groupmates($this->cm);
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
    public function submission_to_pdf() {
        global $CFG, $DB;

        // Event: submissioninpdf_downloaded.
        $eventdata = array('context' => $this->context, 'objectid' => $this->submissionid);
        $eventdata['other'] = array('view' => SURVEYPRO_RESPONSETOPDF);
        $event = \mod_surveypro\event\submissioninpdf_downloaded::create($eventdata);
        $event->trigger();

        require_once($CFG->libdir.'/tcpdf/tcpdf.php');
        require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

        $submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $where = array('submissionid' => $this->submissionid, 'verified' => 1);
        $userdatarecord = $DB->get_records('surveypro_answer', $where, '', 'itemid, id, content');

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, $user->id, true);
        list($where, $params) = surveypro_fetch_items_seeds($this->surveypro->id, true, $canaccessreserveditems);

        // I am not allowed to get ONLY answers from surveypro_answer
        // because I also need to gather info about fieldsets and labels.
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin');

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->add_pdf_details($pdf, $user, $submission->timecreated, $submission->timemodified);
        list($twocolstemplate, $threecolstemplate) = $this->get_columns_html();
        $border = $this->get_border_style();

        $answernotprovided = get_string('answernotsubmitted', 'mod_surveypro');
        foreach ($itemseeds as $itemseed) {
            // Pagebreaks are not selected by surveypro_fetch_items_seeds.
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $template = $item::get_pdf_template();
            $html = ($template == SURVEYPRO_2COLUMNSTEMPLATE) ? $twocolstemplate : $threecolstemplate;

            // First column.
            $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
            // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col1@@', $content, $html);

            // Second column.
            $content = $item->get_content();
            $content = $this->replace_http_url($content);
            // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
            $html = str_replace('@@col2@@', $content, $html);

            if ($template == SURVEYPRO_3COLUMNSTEMPLATE) {
                // Third column.
                if (isset($userdatarecord[$itemseed->id])) {
                    $content = $item->userform_db_to_export($userdatarecord[$itemseed->id], SURVEYPRO_FRIENDLYFORMAT);
                    // $content = htmlspecialchars($content, ENT_NOQUOTES, 'UTF-8');
                    $content = str_replace(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, '<br />', $content);
                } else {
                    $content = $answernotprovided;
                }
                $content = $this->replace_http_url($content);
                $html = str_replace('@@col3@@', $content, $html);
            }

            $pdf->writeHTMLCell(0, 0, '', '', $html, $border, 1, 0, true, '', true);
        }

        $filename = $this->surveypro->name.'_'.$this->submissionid.'.pdf';
        $pdf->Output($filename, 'D');

        // Remember to empty $CFG->tempdir.'/mod_surveypro/PDF_temp/ folder.
        $tempsubdir = $CFG->tempdir.'/mod_surveypro/PDF_temp';
        $files = scandir($tempsubdir);
        foreach ($files as $file) {
            if (($file != '.') && ($file != '..')) {
                unlink($tempsubdir.'/'.$file);
            }
        }
        die();
    }

    /**
     * Is there any image into the content?
     * If a file (usually picture) is found in $item->content replace its http address with its phisical address.
     * The reason is due to surveypro_pluginfile that deny the file (returning null).
     * The user calling surveypro_pluginfile is the PDF agent (and not the logged user) and surveypro_pluginfile stops it.
     * To avoid the call to surveypro_pluginfile I need to use the phisical file address.
     * This behaviour of surveypro_pluginfile is correct and due to file security: not logged users are not allowed to get my files.
     *
     * stored in DB:
     * '<p><img src="@@PLUGINFILE@@/apple.jpg" alt="apple" class="..." width="212" height="160" /></p>';
     * Returned by $item->get_content():
     * '<p><img src="http://localhost/master/pluginfile.php/150/mod_surveypro/itemcontent/1458/apple.jpg" alt="apple" class="..." width="212" height="160" /></p>';
     * What I am supposed to rewrite:
     * '<p><img src="$CFG->tempdir.'/mod_surveypro/PDF_temp/apple.jpg" alt="apple" class="..." width="212" height="160" /></p>';
     *
     * :: Example ::
     * If I find:
     *     <img src="http://localhost/master/pluginfile.php/150/mod_surveypro/itemcontent/1458/apple.jpg" alt="apple"...
     * I have to replace it with:
     *     <img src="$CFG->tempdir.'/mod_surveypro/PDF_temp/apple.jpg" alt="apple"...
     *
     * @param string $content
     * @return $content with each http url replaced by a direct link
     */
    private function replace_http_url($content) {
        global $CFG;

        $regex = '~"('.$CFG->wwwroot.'/pluginfile.php/[^"]*)"~';
        $regex = addslashes($regex);
        preg_match_all($regex, $content, $httpurls, PREG_SET_ORDER);

        $tempsubdir = $CFG->tempdir.'/mod_surveypro/PDF_temp/';
        make_temp_directory('mod_surveypro/PDF_temp');
        foreach ($httpurls as $httpurl) {
            $fileurl = $httpurl[0];
            if ($file = $this->get_image_file($fileurl)) {
                // make a copy of the file in the moodledata temp folder
                // and replace the web address with the phisical address to the moodledata temp file

                $filename = $file->get_filename();
                $directlink = $tempsubdir.$filename;
                $filehandler = fopen($directlink, 'w');
                fwrite($filehandler, $file->get_content());
                fclose($filehandler);
            }
            $content = str_replace($httpurl[1], $directlink, $content);
        }

        return $content;
    }

    /**
     * Get the file content starting from its URL
     *
     * @param string $fileurl
     * @return $content with each http url replaced by a direct link
     */
    private function get_image_file($fileurl) {
        global $CFG;

        if (strpos($fileurl, $CFG->wwwroot.'/pluginfile.php') === false) {
            return null;
        }

        $fs = get_file_storage();

        $params = substr($fileurl, strlen($CFG->wwwroot.'/pluginfile.php'));
        if (substr($params, 0, 1) == '?') { // Slasharguments off.
            $pos = strpos($params, 'file=');
            $params = substr($params, $pos + 5);
        } else { // Slasharguments on.
            if (($pos = strpos($params, '?')) !== false) {
                $params = substr($params, 0, $pos);
            }
        }
        $params = urldecode($params);
        $params = explode('/', $params);
        array_shift($params); // Remove empty first param.
        $contextid = (int)array_shift($params);
        $component = clean_param(array_shift($params), PARAM_COMPONENT);
        $filearea  = clean_param(array_shift($params), PARAM_AREA);
        $itemid = array_shift($params);

        if (empty($params)) {
            $filename = $itemid;
            $itemid = 0;
        } else {
            $filename = array_pop($params);
        }

        if (empty($params)) {
            $filepath = '/';
        } else {
            $filepath = '/'.implode('/', $params).'/';
        }

        if ($component != 'mod_surveypro') {
            return null; // Only allowed to include files directly from this surveypro.
        }

        if (!$file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename)) {
            if ($itemid) {
                $filepath = '/'.$itemid.$filepath; // See if there was no itemid in the originalPath URL.
                $itemid = 0;
                $file = $fs->get_file($contextid, $component, $filename, $itemid, $filepath, $filename);
            }
        }

        if (!$file) {
            return null;
        }

        return $file;
    }

    /**
     * Add details to PDF
     *
     * @param object $pdf
     * @param object $user
     * @param int $timecreated
     * @param int $timemodified
     * @return void
     */
    private function add_pdf_details($pdf, $user, $timecreated, $timemodified) {
        $pdf->SetFont('helvetica', '', 12);

        // Set document information.
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('moodle-mod_surveypro');
        $pdf->SetTitle('User response');
        $pdf->SetSubject('Single response in PDF');

        $textheader = $this->get_header_text($user, $timecreated, $timemodified);
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
    }

    /**
     * Define default header text
     *
     * @param string $user
     * @param int $timecreated
     * @param int $timemodified
     * @return string $headertext
     */
    private function get_header_text($user, $timecreated, $timemodified) {
        $textheader = get_string('responseauthor', 'mod_surveypro');
        $textheader .= fullname($user);
        $textheader .= "\n";
        $textheader .= get_string('responsetimecreated', 'mod_surveypro');
        $textheader .= userdate($timecreated);
        if ($timemodified) {
            $textheader .= get_string('responsetimemodified', 'mod_surveypro');
            $textheader .= userdate($timemodified);
        }

        return $textheader;
    }

    /**
     * Define the widths of each column into PDF
     *
     * @return list() of html for each template
     */
    private function get_columns_html() {
        list($col1width, $col2width, $col3width) = $this->get_columns_width();
        $col23width = $col2width + $col3width;

        $twocolstemplate = '<table style="width:100%;"><tr>';
        $twocolstemplate .= '<td style="width:'.$col1width.'%;text-align:left;">@@col1@@</td>';
        $twocolstemplate .= '<td style="width:'.$col23width.'%;text-align:left;">@@col2@@</td>';
        $twocolstemplate .= '</tr></table>';

        $threecolstemplate = '<table style="width:100%;"><tr>';
        $threecolstemplate .= '<td style="width:'.$col1width.'%;text-align:left;">@@col1@@</td>';
        $threecolstemplate .= '<td style="width:'.$col2width.'%;text-align:left;">@@col2@@</td>';
        $threecolstemplate .= '<td style="width:'.$col3width.'%;text-align:left;">@@col3@@</td>';
        $threecolstemplate .= '</tr></table>';

        return array($twocolstemplate, $threecolstemplate);
    }

    /**
     * Define the widths of each column into PDF
     *
     * @return list() of width of each column
     */
    private function get_columns_width() {
        $col1unit = 1;
        $col2unit = 6;
        $col3unit = 3;
        $unitsum = $col1unit + $col2unit + $col3unit;

        $col1width = number_format($col1unit * 100 / $unitsum, 2);
        $col2width = number_format($col2unit * 100 / $unitsum, 2);
        $col3width = number_format($col3unit * 100 / $unitsum, 2);

        return array($col1width, $col2width, $col3width);
    }

    /**
     * Define the style of the border used sometimes in the PDF
     *
     * @return list() of width of each column
     */
    private function get_border_style() {
        $border = array();
        $border['T'] = array();
        $border['T']['width'] = 0.2;
        $border['T']['cap'] = 'butt';
        $border['T']['join'] = 'miter';
        $border['T']['dash'] = 1;
        $border['T']['color'] = array(179, 219, 181);

        return $border;
    }
}
