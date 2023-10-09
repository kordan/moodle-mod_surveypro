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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_submission;

/**
 * The class managing users submissions
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissions_list {

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
     * Supporting rationale:
     * if a user has the 'mod/surveypro:seeotherssubmissions'...
     *     he can ENLARGES his/her view/panorama from his/hew own submissions to
     *     all the submissions coming from his/her "world".
     *     What is his/her "world"?
     *     if sPro is NOT divided into groups,
     *         his/her "world" is the set of ALL THE submissions coming from users enrolled in the course.
     *     if sPro is divided into groups AND a user is in a group,
     *         his/her "world" is the set of submissions coming from users part of his/her same groups.
     *     Subcase:
     *     if sPro is divided into groups but the user is NOT in any group,
     *         his/her "world" is the set of ALL THE submissions coming from users enrolled in the course.
     *
     *     if a user has the 'moodle/site:accessallgroups' even if he/she is member of a group,
     *         his/her "world" is the set of ALL THE submissions coming from users enrolled in the course.
     *
     * if a user has NOT the 'mod/surveypro:seeotherssubmissions'...
     *     he can see ONLY HIS/HER OWN submissions without worrying about any eventual groups.
     *
     * The supporting idea of this rationale is:
     *     'mod/surveypro:seeotherssubmissions' ENLARGES view/panorama of users
     *     groups REDUCES the width of the world
     *
     * Note:
     * Teachers is the role of users usually accessing reports.
     * They are "teachers" so they care about "students" and nothing more.
     * If some records go under the admin ownership
     * the teacher is not supposed to see them because admin is not a student.
     * In this case, if the teacher wants to see submissions from the admin, HE HAS TO ENROLL ADMIN with some role.
     *
     * Different is the story for the admin.
     * If an admin wants to make a report, he will see EACH RESPONSE SUBMITTED
     * without care to the role of the owner of the submission.
     *
     * @param flexible_table $table
     * @return [$sql, $whereparams];
     */
    public function get_submissions_sql($table) {
        global $DB, $COURSE, $USER;

        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');

        // Are there enrolled users in this course?
        $coursecontext = \context_course::instance($COURSE->id);
        list($enrolsql, $eparams) = get_enrolled_sql($coursecontext);

        $sql = 'SELECT COUNT(eu.id)
                FROM ('.$enrolsql.') eu';
        // If there are not enrolled users, give up!
        if (!$DB->count_records_sql($sql, $eparams)) {
            if (!$canviewhiddenactivities) { // If you are not an admin like.
                // Prepare an empty SQL to return if conditions force it.
                $emptysql = 'SELECT DISTINCT s.*, s.id as submissionid'.$userfieldsapi->selects.'
                             FROM {surveypro_submission} s
                                 JOIN {user} u ON u.id = s.userid
                             WHERE u.id = :userid';

                return [$emptysql, ['userid' => -1]];
            }
        }

        // Make a list of the groups the users is part of.
        if (!$canaccessallgroups) {
            // Is this instance of surveypro divided into groups?
            // Take care: even if the course is divided into groups, this surveypro may not inherit that division.
            $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
            if ($groupmode) { // Activity is divided into groups.
                // Does the user belong to any group?
                $mygroups = groups_get_all_groups($COURSE->id, $USER->id);
                if (count($mygroups)) {
                    $mygroups = array_keys($mygroups);

                    list($ingroupsql, $groupsparams) = $DB->get_in_or_equal($mygroups, SQL_PARAMS_NAMED, 'groupid');
                    // $groupsparams is ready to array_merge $whereparams if ((!$canaccessallgroups) && count($mygroups)).

                    $sqlgroups = ' AND gm.groupid '.$ingroupsql;
                }
            } else {
                $mygroups = [];
            }
        } else {
            $mygroups = [];
        }

        $sql = 'SELECT ss.id as submissionid, ss.surveyproid, ss.status, ss.userid, ss.timecreated, ss.timemodified';
        $sql .= $userfieldsapi->selects;
        $sql .= ' FROM {surveypro_submission} ss
                  JOIN {user} u ON u.id = ss.userid';

        // Initialize $whereparams.
        if ($canviewhiddenactivities) { // You are a student so make the selection among enrolled users only.
            $whereparams = array();
        } else {
            $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';
            $whereparams = $eparams;
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
                $itemseed = $DB->get_record('surveypro_item', ['id' => $itemid], 'type, plugin', MUST_EXIST);
                $classname = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin.'\item';
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
            $sql .= ' JOIN ('.$sqlanswer.') a ON a.submissionid = ss.id';
        }

        $debug = false;
        if ($debug) {
            if ($canviewhiddenactivities) {
                echo '$canviewhiddenactivities = true<br>';
            } else {
                echo '$canviewhiddenactivities = false<br>';
            }
            if ($canseeotherssubmissions) {
                echo '$canseeotherssubmissions = true<br>';
            } else {
                echo '$canseeotherssubmissions = false<br>';
            }
            if ($canaccessallgroups) {
                echo '$canaccessallgroups = true<br>';
            } else {
                echo '$canaccessallgroups = false<br>';
            }
            echo '$mygroups =';
            // print_object($mygroups); // <-- This is better than var_dump but codechecker doesn't like it.
            echo '<br>count($mygroups) = '.count($mygroups).'<br>';
        }

        if (count($mygroups)) { // User is, at least, in a group.
            if ($canseeotherssubmissions && (!$canaccessallgroups)) {
                $sql .= ' JOIN (SELECT DISTINCT gm.userid
                                FROM {groups_members} gm
                                WHERE gm.groupid '.$ingroupsql.') gr ON gr.userid = u.id';
                $whereparams = array_merge($whereparams, $groupsparams);
            }
        }

        $sql .= ' WHERE ss.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        if (!$canseeotherssubmissions) {
            // Restrict to your submissions only.
            $sql .= ' AND ss.userid = :userid';
            $whereparams['userid'] = $USER->id;
        }

        // Manage table alphabetical filter.
        list($wherefilter, $wherefilterparams) = $table->get_sql_where();
        if ($wherefilter) {
            $sql .= ' AND '.$wherefilter;
            $whereparams = $whereparams + $wherefilterparams;
        }

        if ($table->get_sql_sort()) {
            // Sort coming from $table->get_sql_sort().
            $sql .= ' ORDER BY '.$table->get_sql_sort();
        } else {
            $sql .= ' ORDER BY ss.timecreated';
        }

        $debug = false;
        if ($debug) {
            global $CFG;

            // echo '$sql = '.$sql.'<br><br>';
            $sql2display = preg_replace('~{([a-z_]*)}~', $CFG->prefix.'\1', $sql);
            $sql2display = str_replace('JOIN', '<br>&nbsp;&nbsp;&nbsp;&nbsp;JOIN', $sql2display);
            echo '$sql2display = '.$sql2display.'<br>';
            echo '$whereparams =';
            // print_object($whereparams); // <-- This is better than var_dump but codechecker doesn't like it.
            var_dump($whereparams);
        }

        return [$sql, $whereparams];
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

        $coursecontext = \context_course::instance($COURSE->id);
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
        if ($groupmode && (!$canaccessallgroups)) {
            $mygroups = groups_get_all_groups($COURSE->id, $USER->id);
            $mygroups = array_keys($mygroups);
            // if count($mygroups) == 0
            // then the course is divided into groups
            // but this user was not added to any group.
        }

        $whereparams = array();

        $sql = 'SELECT s.status, COUNT(s.id) submissions, COUNT(DISTINCT(u.id)) users';
        $sql .= ' FROM {surveypro_submission} s
                  JOIN {user} u ON u.id = s.userid';

        if (!$canviewhiddenactivities) { // You are a student so make the selection among enrolled users only.
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
                $itemseed = $DB->get_record('surveypro_item', ['id' => $itemid], 'type, plugin', MUST_EXIST);
                $classname = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin.'\item';
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

        if ($groupmode && (!$canaccessallgroups)) {
            if (count($mygroups)) { // User is, at least, in a group.
                $sql .= ' JOIN {groups_members} gm ON gm.userid = s.userid';
            }
        }

        $sql .= ' WHERE s.surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;

        if (!$canaccessallgroups) {
            if (!$groupmode || !count($mygroups)) { // User is not in any group.
                // Restrict to your submissions only.
                $sql .= ' AND s.userid = :userid';
                $whereparams['userid'] = $USER->id;
            }
        }

        // Manage table alphabetical filter.
        list($wherefilter, $wherefilterparams) = $table->get_sql_where();
        if ($wherefilter) {
            $sql .= ' AND '.$wherefilter;
            $whereparams = $whereparams + $wherefilterparams;
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

        $params = \core_text::substr($fileurl, \core_text::strlen($CFG->wwwroot.'/pluginfile.php'));
        if (\core_text::substr($params, 0, 1) == '?') { // Slasharguments off.
            $pos = strpos($params, 'file=');
            $params = \core_text::substr($params, $pos + 5);
        } else { // Slasharguments on.
            if (($pos = strpos($params, '?')) !== false) {
                $params = \core_text::substr($params, 0, $pos);
            }
        }
        $params = urldecode($params);
        $params = explode('/', $params);
        array_shift($params); // Remove empty first param.
        $contextid = (int)array_shift($params);
        $component = clean_param(array_shift($params), PARAM_COMPONENT);
        $filearea = clean_param(array_shift($params), PARAM_AREA);
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
     * Define default header text
     *
     * @param string $user
     * @param int $timecreated
     * @param int $timemodified
     * @return string $headertext
     */
    private function get_header_text($user, $timecreated, $timemodified) {
        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);

        $textheader = '';
        if (empty($this->surveypro->anonymous) || $canalwaysseeowner) {
            $textheader .= get_string('responseauthor', 'mod_surveypro');
            $textheader .= fullname($user);
            $textheader .= "\n";
        }
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
        $twocolstemplate .= '<td style="width:'.$col1width.'%; text-align:left;">@@col1@@</td>';
        $twocolstemplate .= '<td style="width:'.$col23width.'%; text-align:left;">@@col2@@</td>';
        $twocolstemplate .= '</tr></table>';

        $threecolstemplate = '<table style="width:100%;"><tr>';
        $threecolstemplate .= '<td style="width:'.$col1width.'%; text-align:left;">@@col1@@</td>';
        $threecolstemplate .= '<td style="width:'.$col2width.'%; text-align:left;">@@col2@@</td>';
        $threecolstemplate .= '<td style="width:'.$col3width.'%; text-align:left;">@@col3@@</td>';
        $threecolstemplate .= '</tr></table>';

        return [$twocolstemplate, $threecolstemplate];
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

        return [$col1width, $col2width, $col3width];
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
        $border['T']['color'] = [179, 219, 181];

        return $border;
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
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $canduplicateownsubmissions = has_capability('mod/surveypro:duplicateownsubmissions', $this->context);
        $canduplicateotherssubmissions = has_capability('mod/surveypro:duplicateotherssubmissions', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $cansavetopdfownsubmissions = has_capability('mod/surveypro:savetopdfownsubmissions', $this->context);
        $cansavetopdfotherssubmissions = has_capability('mod/surveypro:savetopdfotherssubmissions', $this->context);

        $table = new \flexible_table('submissionslist');

        if ($canseeotherssubmissions) {
            $table->initialbars(true);
        }

        $paramurl = ['s' => $this->cm->instance, 'section' => 'submissionslist'];
        if ($this->searchquery) {
            $paramurl['searchquery'] = $this->searchquery;
        }
        $baseurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
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
            $nonhistoryediticn = new \pix_icon('t/edit', $nonhistoryeditstr, 'moodle', $iconparams);

            $readonlyaccessstr = get_string('readonlyaccess', 'mod_surveypro');
            $iconparams['title'] = $readonlyaccessstr;
            $readonlyicn = new \pix_icon('readonly', $readonlyaccessstr, 'surveypro', $iconparams);

            $duplicatestr = get_string('duplicate');
            $iconparams['title'] = $duplicatestr;
            $duplicateicn = new \pix_icon('t/copy', $duplicatestr, 'moodle', $iconparams);

            if ($this->surveypro->history) {
                $attributestr = get_string('editcopy', 'mod_surveypro');
                $linkidprefix = 'editcopy_submission_';
            } else {
                $attributestr = $nonhistoryeditstr;
                $linkidprefix = 'edit_submission_';
            }
            $iconparams['title'] = $attributestr;
            $attributeicn = new \pix_icon('t/edit', $attributestr, 'moodle', $iconparams);

            $deletestr = get_string('delete');
            $iconparams['title'] = $deletestr;
            $deleteicn = new \pix_icon('t/delete', $deletestr, 'moodle', $iconparams);

            $downloadpdfstr = get_string('downloadpdf', 'mod_surveypro');
            $iconparams['title'] = $downloadpdfstr;
            $downloadpdficn = new \pix_icon('t/download', $downloadpdfstr, 'moodle', $iconparams);

            $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
            if ($groupmode) { // Activity is divided into groups.
                $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
                $mygroupmates = $utilitysubmissionman->get_groupmates($this->cm);
            } else {
                $mygroupmates = [];
            }

            $tablerowcounter = 0;
            $paramurlbase = ['s' => $this->cm->instance];

            foreach ($submissions as $submission) {
                // Count each submission.
                $tablerowcounter++;
                $submissionsuffix = 'row_'.$tablerowcounter;

                // Before starting, just set some information.
                $ismine = ($submission->userid == $USER->id);
                if ($canaccessallgroups) {
                    $mysamegroup = true;
                } else {
                    if ($groupmode) { // Activity is divided into groups.
                        $mysamegroup = in_array($submission->userid, $mygroupmates);
                    } else {
                        $mysamegroup = false;
                    }
                }

                $tablerow = array();

                // Icon.
                if ($canalwaysseeowner || empty($this->surveypro->anonymous)) {
                    $tablerow[] = $OUTPUT->user_picture($submission, ['courseid' => $COURSE->id]);

                    // User fullname.
                    $paramurl = ['id' => $submission->userid, 'course' => $COURSE->id];
                    $url = new \moodle_url('/user/view.php', $paramurl);
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
                $displayediticon = false;
                if ($ismine) { // Owner is me.
                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        // You always MUST have the possibility to close an inprogress submission.
                        $displayediticon = true;
                    } else {
                        $displayediticon = $caneditownsubmissions;
                    }
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        // I should be here only if $canseeotherssubmissions = true.
                        if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                            // You always MUST have the possibility to close an inprogress submission of someone from your same group.
                            $displayediticon = $canseeotherssubmissions;
                        } else {
                            $displayediticon = $caneditotherssubmissions;
                        }
                    }
                }
                if ($displayediticon) {
                    $paramurl['mode'] = SURVEYPRO_EDITMODE;
                    $paramurl['begin'] = 1;
                    $paramurl['section'] = 'submissionform';

                    if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                        // Here title and alt are ALWAYS $nonhistoryeditstr.
                        $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                        $paramlink = ['id' => 'edit_submission_'.$submissionsuffix, 'title' => $nonhistoryeditstr];
                        $icons = $OUTPUT->action_icon($link, $nonhistoryediticn, null, $paramlink);
                    } else {
                        // Here title and alt depend from $this->surveypro->history.
                        $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                        $paramlink = ['id' => $linkidprefix.$submissionsuffix, 'title' => $attributestr];
                        $icons = $OUTPUT->action_icon($link, $attributeicn, null, $paramlink);
                    }
                } else {
                    $paramurl['mode'] = SURVEYPRO_READONLYMODE;
                    $paramurl['begin'] = 1;
                    $paramurl['section'] = 'submissionform';

                    $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                    $paramlink = ['id' => 'view_submission_'.$submissionsuffix, 'title' => $readonlyaccessstr];
                    $icons = $OUTPUT->action_icon($link, $readonlyicn, null, $paramlink);
                }

                // Duplicate.
                $displayduplicateicon = false;
                if ($ismine) { // Owner is me.
                    $displayduplicateicon = $canduplicateownsubmissions;
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        // I should be here only if $canseeotherssubmissions = true.
                        $displayduplicateicon = $caneditotherssubmissions;
                    }
                }
                if ($displayduplicateicon) { // I am the owner or a groupmate.
                    $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
                    $cansubmitmore = $utilitylayoutman->can_submit_more($submission->userid);
                    if ($cansubmitmore) { // The copy will be assigned to the same owner.
                        $paramurl = $paramurlbase;
                        $paramurl['submissionid'] = $submission->submissionid;
                        $paramurl['sesskey'] = sesskey();
                        $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;
                        $paramurl['section'] = 'submissionslist';

                        $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                        $paramlink = ['id' => 'duplicate_submission_'.$submissionsuffix, 'title' => $duplicatestr];
                        $icons .= $OUTPUT->action_icon($link, $duplicateicn, null, $paramlink);
                    }
                }

                // Delete.
                $displaydeleteicon = false;
                $paramurl = $paramurlbase;
                $paramurl['submissionid'] = $submission->submissionid;
                if ($ismine) { // Owner is me.
                    $displaydeleteicon = $candeleteownsubmissions;
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        // I should be here only if $canseeotherssubmissions = true.
                        $displaydeleteicon = $candeleteotherssubmissions;
                    }
                }
                if ($displaydeleteicon) {
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $paramurl['section'] = 'submissionslist';

                    $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                    $paramlink = ['id' => 'delete_submission_'.$submissionsuffix, 'title' => $deletestr];
                    $icons .= $OUTPUT->action_icon($link, $deleteicn, null, $paramlink);
                }

                // Download to pdf.
                $displaydownloadtopdficon = false;
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    $displaydownloadtopdficon = false;
                } else {
                    if ($ismine) { // Owner is me.
                        $displaydownloadtopdficon = $cansavetopdfownsubmissions;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $displaydownloadtopdficon = $cansavetopdfotherssubmissions;
                        }
                    }
                }
                if ($displaydownloadtopdficon) {
                    $paramurl = $paramurlbase;
                    $paramurl['submissionid'] = $submission->submissionid;
                    $paramurl['act'] = SURVEYPRO_RESPONSETOPDF;
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['section'] = 'submissionslist';

                    $link = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                    $paramlink = ['id' => 'pdfdownload_submission_'.$submissionsuffix, 'title' => $downloadpdfstr];
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
            $paramurl = ['s' => $this->cm->instance, 'section' => 'submissionslist'];
            $url = new \moodle_url('/mod/surveypro/view.php', $paramurl);
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

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

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
        $deleteall = $deleteall && empty($tifirst); // Hide the deleteall button if not all the responses are shown.
        $deleteall = $deleteall && empty($tilast);  // Hide the deleteall button if not all the responses are shown.
        $deleteall = $deleteall && ($next > 1); // Show the deleteall button only if there are responses to delete.
        // End of: is the button to delete all responses going to be the page?

        $buttoncount = 0;
        $paramurl = ['s' => $this->cm->instance];
        if ($addnew) {
            $paramurl['mode'] = SURVEYPRO_NEWRESPONSEMODE;
            $paramurl['begin'] = 1;
            $paramurl['section'] = 'submissionform';

            $addurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            $buttoncount = 1;
        }
        if ($deleteall) {
            $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
            $paramurl['sesskey'] = sesskey();
            $paramurl['section'] = 'submissionslist';

            $deleteurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
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
                echo $OUTPUT->box($OUTPUT->single_button($deleteurl, $label, 'post', ['type' => 'secondary']), 'clearfix mdl-align');
            }
        } else {
            $class = ['class' => 'buttons'];
            $addbutton = new \single_button($addurl, get_string('addnewsubmission', 'mod_surveypro'), 'post', 'primary');
            $class = ['class' => 'buttons btn-secondary'];
            $deleteallbutton = new \single_button($deleteurl, get_string('deleteallsubmissions', 'mod_surveypro'));

            // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
            // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo \html_writer::tag('div', $OUTPUT->render($addbutton).$OUTPUT->render($deleteallbutton), ['class' => 'buttons']);
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
        $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
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
                    $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->duplicate_submissions(['id' => $this->submissionid]);

                    // Redirect.
                    $paramurl = array();
                    $paramurl['s'] = $this->cm->instance;
                    $paramurl['act'] = SURVEYPRO_DUPLICATERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['section'] = 'submissionslist';
                    $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETERESPONSE:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->delete_submissions(['id' => $this->submissionid]);

                    // Redirect.
                    $paramurl = array();
                    $paramurl['s'] = $this->cm->instance;
                    $paramurl['act'] = SURVEYPRO_DELETERESPONSE;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['section'] = 'submissionslist';
                    $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
                    redirect($redirecturl);
                }
                break;
            case SURVEYPRO_DELETEALLRESPONSES:
                if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                    $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
                    $utilitylayoutman->delete_submissions(['surveyproid' => $this->surveypro->id]);

                    // Redirect.
                    $paramurl = array();
                    $paramurl['s'] = $this->cm->instance;
                    $paramurl['act'] = SURVEYPRO_DELETEALLRESPONSES;
                    $paramurl['cnf'] = SURVEYPRO_ACTION_EXECUTED;
                    $paramurl['sesskey'] = sesskey();
                    $paramurl['section'] = 'submissionslist';
                    $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
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
     * @param int $justsubmitted
     * @return void
     */
    public function show_thanks_page($responsestatus, $justsubmitted) {
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
                $message = format_text($message, $this->surveypro->thankspageformat);
            } else {
                $message = get_string('basic_submitthanks', 'mod_surveypro');
            }
        }

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $cansubmitmore = $utilitylayoutman->can_submit_more();

        $paramurlbase = ['s' => $this->cm->instance];
        if ($cansubmitmore) { // If the user is allowed to submit one more response.
            $paramurl = $paramurlbase + ['mode' => SURVEYPRO_NEWRESPONSEMODE, 'begin' => 1, 'section' => 'submissionform'];
            $buttonurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            $onemore = new \single_button($buttonurl, get_string('addnewsubmission', 'mod_surveypro'), 'post', 'primary');

            $paramurl = $paramurlbase + ['section' => 'submissionslist'];
            $buttonurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            $gotolist = new \single_button($buttonurl, get_string('gotolist', 'mod_surveypro'));

            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo \html_writer::tag('p', $message);
            echo \html_writer::tag('div', $OUTPUT->render($onemore).$OUTPUT->render($gotolist), ['class' => 'buttons']);
            echo $OUTPUT->box_end();
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $paramurl = $paramurlbase + ['section' => 'submissionslist'];
            $buttonurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            $buttonlabel = get_string('gotolist', 'mod_surveypro');
            echo $OUTPUT->box($OUTPUT->single_button($buttonurl, $buttonlabel), 'generalbox centerpara');
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
            $submission = $DB->get_record('surveypro_submission', ['id' => $this->submissionid]);

            $a = new \stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid == $USER->id) {
                if ($submission->timemodified == 0) {
                    $message = get_string('confirm_duplicatemyresponse_original', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_duplicatemyresponse_modified', 'mod_surveypro', $a);
                }
            } else {
                $userfieldsapi = \core_user\fields::for_userpic();
                $namefields = $userfieldsapi->get_sql('', false, '', 'id', false)->selects;
                $user = $DB->get_record('user', ['id' => $submission->userid], $namefields);
                $a->fullname = fullname($user);
                if ($submission->timemodified == 0) {
                    $message = get_string('confirm_duplicateotherresponse_original', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_duplicateotherresponse_modified', 'mod_surveypro', $a);
                }
            }

            $optionbase = ['s' => $this->cm->instance, 'act' => SURVEYPRO_DUPLICATERESPONSE];

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['submissionid'] = $this->submissionid;
            $optionsyes['section'] = 'submissionslist';
            $urlyes = new \moodle_url('/mod/surveypro/view.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, get_string('continue'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $optionsno['section'] = 'submissionslist';
            $urlno = new \moodle_url('/mod/surveypro/view.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));

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
            $submission = $DB->get_record('surveypro_submission', ['id' => $this->submissionid]);

            $a = new \stdClass();
            $a->timecreated = userdate($submission->timecreated);
            $a->timemodified = userdate($submission->timemodified);
            if ($submission->userid != $USER->id) {
                $userfieldsapi = \core_user\fields::for_userpic();
                $namefields = $userfieldsapi->get_sql('', false, '', 'id', false)->selects;
                $user = $DB->get_record('user', ['id' => $submission->userid], $namefields);
                $a->fullname = fullname($user);
                if ($submission->timemodified == 0) {
                    $message = get_string('confirm_deleteotherresponse_original', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_deleteotherresponse_modified', 'mod_surveypro', $a);
                }
            } else {
                if ($submission->timemodified == 0) {
                    $message = get_string('confirm_deletemyresponse_original', 'mod_surveypro', $a);
                } else {
                    $message = get_string('confirm_deletemyresponse_modified', 'mod_surveypro', $a);
                }
            }

            $optionbase = ['s' => $this->cm->instance, 'act' => SURVEYPRO_DELETERESPONSE];

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['submissionid'] = $this->submissionid;
            $optionsyes['section'] = 'submissionslist';
            $urlyes = new \moodle_url('/mod/surveypro/view.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, get_string('continue'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $optionsno['section'] = 'submissionslist';
            $urlno = new \moodle_url('/mod/surveypro/view.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));

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
            $optionbase = ['s' => $this->cm->instance, 'act' => SURVEYPRO_DELETEALLRESPONSES, 'section' => 'submissionslist'];

            $optionsyes = $optionbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $urlyes = new \moodle_url('/mod/surveypro/view.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, get_string('deleteallsubmissions', 'mod_surveypro'));

            $optionsno = $optionbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new \moodle_url('/mod/surveypro/view.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));
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

        echo \html_writer::start_tag('fieldset', ['class' => 'generalbox', 'style' => 'padding-bottom: 15px;']);
        echo \html_writer::start_tag('legend', ['class' => 'coverinfolegend']);
        echo get_string('submissions_welcome', 'mod_surveypro');
        echo \html_writer::end_tag('legend');

        $allsubmissions = $countinprogress + $countclosed;
        if ($allsubmissions) {
            if ($canseeotherssubmissions) {
                // Enrolled users: 3.
                if ($enrolledusers == 1) {
                    echo $OUTPUT->container(get_string('userenrolled', 'mod_surveypro'), 'mdl-left');
                } else {
                    echo $OUTPUT->container(get_string('usersenrolled', 'mod_surveypro', $enrolledusers), 'mdl-left');
                }
            }

            // 17 responses submitted by 2 user.
            $a = new \stdClass();
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
                $a = new \stdClass();
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
                $a = new \stdClass();
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
            $paramurl = ['s' => $this->cm->instance, 'section' => 'submissionslist'];
            $findallurl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            $label = get_string('showallsubmissions', 'mod_surveypro');

            echo $OUTPUT->single_button($findallurl, $label, 'get', ['type' => 'secondary', 'class' => 'box clearfix mdl-align']);
        }
        echo \html_writer::end_tag('fieldset');
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

        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $candeleteownsubmissions = has_capability('mod/surveypro:deleteownsubmissions', $this->context);
        $candeleteotherssubmissions = has_capability('mod/surveypro:deleteotherssubmissions', $this->context);
        $canduplicateownsubmissions = has_capability('mod/surveypro:duplicateownsubmissions', $this->context);
        $canduplicateotherssubmissions = has_capability('mod/surveypro:duplicateotherssubmissions', $this->context);
        $cansavetopdfownsubmissions = has_capability('mod/surveypro:savetopdfownsubmissions', $this->context);
        $cansavetopdfotherssubmissions = has_capability('mod/surveypro:savetopdfotherssubmissions', $this->context);

        // Start with the unique case not needing $ismine and $mysamegroup.
        if ($this->action == SURVEYPRO_DELETEALLRESPONSES) {
            if (!$candeleteotherssubmissions) {
                throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
            } else {
                return true;
            }
        }

        // From now on each action has a specific submission as target so the submission HAS TO EXIST.
        $fields = 'userid, status';
        // In the next line I could use MUST_EXIST but I prefer to always have homogeneous errors messages.
        $submission = $DB->get_record('surveypro_submission', ['id' => $this->submissionid], $fields, IGNORE_MISSING);
        $ownerid = $submission->userid;
        $status = $submission->status;
        if (!$ownerid) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }

        $ismine = ($ownerid == $USER->id);
        if (!$canaccessallgroups) {
            $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
            if ($groupmode) { // Activity is divided into groups.
                $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
                $mygroupmates = $utilitysubmissionman->get_groupmates($this->cm);
                $mysamegroup = in_array($ownerid, $mygroupmates);
            } else {
                $mysamegroup = false;
            }
        } else {
            $mysamegroup = true;
        }

        switch ($this->action) {
            case SURVEYPRO_DELETERESPONSE:
                if ($ismine) { // Owner is me.
                    $allowed = $candeleteownsubmissions;
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        $allowed = $candeleteotherssubmissions;
                    }
                }
                break;
            case SURVEYPRO_DUPLICATERESPONSE:
                if ($ismine) { // Owner is me.
                    $allowed = $canduplicateownsubmissions;
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        $allowed = $canduplicateotherssubmissions;
                    }
                }
                break;
            case SURVEYPRO_RESPONSETOPDF:
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    $allowed = false;
                } else {
                    if ($ismine) { // Owner is me.
                        $allowed = $cansavetopdfownsubmissions;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $allowed = $cansavetopdfotherssubmissions;
                        }
                    }
                }
                break;
            default:
                $allowed = false;
        }

        if (!$allowed) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }

        switch ($this->view) {
            case SURVEYPRO_NOMODE:
                $allowed = true;
                break;
            case SURVEYPRO_READONLYMODE:
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    $allowed = false;
                } else {
                    if ($ismine) { // Owner is me.
                        $allowed = true;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $allowed = $canseeotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_EDITMODE:
                if ($ismine) { // Owner is me.
                    $allowed = $caneditownsubmissions;
                } else {
                    if ($mysamegroup) { // Owner is from a group of mine.
                        $allowed = $caneditotherssubmissions;
                    }
                }
                break;
            default:
                $allowed = false;
        }

        if (!$allowed) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Make one submission available in PDF.
     *
     * @return void
     */
    public function submission_to_pdf() {
        global $CFG, $DB;

        $this->prevent_direct_user_input(SURVEYPRO_CONFIRMED_YES);

        // Event: submissiontopdf_downloaded.
        $eventdata = ['context' => $this->context, 'objectid' => $this->submissionid];
        $eventdata['other'] = ['act' => SURVEYPRO_RESPONSETOPDF];
        $event = \mod_surveypro\event\submissiontopdf_downloaded::create($eventdata);
        $event->trigger();

        require_once($CFG->libdir.'/tcpdf/tcpdf.php');
        require_once($CFG->libdir.'/tcpdf/config/tcpdf_config.php');

        $submission = $DB->get_record('surveypro_submission', ['id' => $this->submissionid]);
        $user = $DB->get_record('user', ['id' => $submission->userid]);
        $where = ['submissionid' => $this->submissionid, 'verified' => 1];
        $userdatarecord = $DB->get_records('surveypro_answer', $where, '', 'itemid, id, content');

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, $user->id, true);
        list($where, $params) = surveypro_fetch_items_seeds($this->surveypro->id, true, $canaccessreserveditems);

        // I am not allowed to get ONLY answers from surveypro_answer
        // because I also need to gather info about fieldsets and labels.
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin');

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->add_pdf_details($pdf, $user, $submission->timecreated, $submission->timemodified);
        list($twocolstemplate, $threecolstemplate) = $this->get_columns_html();
        $border = $this->get_border_style();

        $answernotprovided = get_string('answernotsubmitted', 'mod_surveypro');
        foreach ($itemseeds as $itemseed) {
            // Pagebreaks are not selected by surveypro_fetch_items_seeds.
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $template = $item::get_pdf_template();
            if (!$template) {
                continue;
            }
            $html = ($template == SURVEYPRO_2COLUMNSTEMPLATE) ? $twocolstemplate : $threecolstemplate;

            // First column.
            $content = ($item->get_customnumber()) ? $item->get_customnumber().':' : '';
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
                    $content = str_replace(SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR, '<br>', $content);
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
        die();
    }

    /**
     * Is there any image into the content?
     * If a file (usually picture) is found in $item->content replace its http address with its base64_encode.
     * The reason is due to surveypro_pluginfile that deny the file (returning null).
     * The user calling surveypro_pluginfile is the PDF agent (and not the logged user) and surveypro_pluginfile stops it.
     * To avoid the call to surveypro_pluginfile I need to use the phisical file address.
     * This behaviour of surveypro_pluginfile is correct and due to file security: not logged users are not allowed to get my files.
     *
     * Ideally I would copy the file to a temp location and return it's path but...
     * Stored in DB:
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
     *     <img src="$CFG->tempdir.'/mod_surveypro/PDF_temp/apple.jpg'" alt="apple"...
     * ...but a bug in TCPDF currently prevents that.
     *
     * Because of this issue I have to replace the path with the file base64_encode.
     * Stored in DB:
     * '<p><img src="@@PLUGINFILE@@/apple.jpg" alt="apple" class="..." width="212" height="160" /></p>';
     * Returned by $item->get_content():
     * '<p><img src="http://localhost/master/pluginfile.php/150/mod_surveypro/itemcontent/1458/apple.jpg" alt="apple" class="..." width="212" height="160" /></p>';
     * What I am supposed to rewrite:
     * '<p><img src="@base64_encode" alt="apple" class="..." width="212" height="160" /></p>';
     *
     * :: Example ::
     * If I find:
     *     <img src="http://localhost/master/pluginfile.php/150/mod_surveypro/itemcontent/1458/apple.jpg" alt="apple"...
     * I have to replace it with:
     *     <img src="@base64_encode" alt="apple"...
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
                $filecontent = $file->get_content();
                $encodedfilecontent = '@'.base64_encode($filecontent);
                $content = str_replace($httpurl[1], $encodedfilecontent, $content);
            }
        }

        return $content;
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
        $pdf->SetHeaderData('', 0, $this->surveypro->name, $textheader, [0, 64, 255], [0, 64, 128]);
        $pdf->setFooterData([0, 64, 0], [0, 64, 128]);

        // Set header and footer fonts.
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // Set margins.
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf->SetDrawColorArray(array(0, 64, 128));
        // Set auto page breaks.
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->AddPage();
    }
}
