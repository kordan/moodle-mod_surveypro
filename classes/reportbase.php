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
 * Surveypro reportbase class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

/**
 * The base class representing a report
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reportbase {

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
     * @var int $groupid
     */
    public $groupid = 0;

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
     * Get the list of mastertemplates to which this report is applicable.
     *
     * If ruturns an empty array, each report is added to admin menu
     * If returns a non empty array, only reports listed will be added to admin menu
     *
     * @return array
     */
    public function report_applies_to() {
        return array('each');
    }

    /**
     * Return if this report applies.
     *
     * true means: the report applies
     * empty($this->surveypro->anonymous) means that reports applies ONLY IF the survey is not anonymous
     *
     * @return boolean
     */
    public function report_apply() {
        return true;
    }

    /**
     * Returns if this report was created for student too.
     *
     * @return boolean false
     */
    public function has_student_report() {
        return false;
    }

    /**
     * Get child reports.
     *
     * @param bool $canaccessreports
     * @return boolean false
     */
    public function has_childreports($canaccessreports) {
        return false;
    }

    /**
     * Display a message if no submissions were provided
     */
    public function nosubmissions_stop() {
        global $DB, $OUTPUT;

        // You need to count submissions OF ENROLLED PEOPLE, otherwise colles report may crash,
        // in $m = $aggregate->sumofanswers / $aggregate->answerscount dividing by 0.
        // Because of this, $utilitylayoutman->has_submissions(); can't be used
        // because it simply counts ALL the submissions found
        // without care to the actual enrolment of the submitters
        // while colles report actually selects submissions of enrolled users ONLY.

        // Other reports handle no submissions locally because they display an empty table or alphabet.
        $sql = 'SELECT COUNT(s.id) as answerscount
                FROM {user} u
                    JOIN {surveypro_submission} s ON s.userid = u.id
                    JOIN {surveypro_answer} a ON a.submissionid = s.id';

        list($middlesql, $whereparams) = $this->get_middle_sql();
        $sql .= $middlesql;

        $aggregate = $DB->get_record_sql($sql, $whereparams);
        if (!$aggregate->answerscount) {
            $message = get_string('nosubmissionsforenrolled', 'mod_surveypro');
            echo $OUTPUT->box($message, 'notice centerpara');
            echo $OUTPUT->footer();

            die();
        }
    }

    /**
     * Is groupjumper drop down menu needed?
     *
     * @return boolean
     */
    public function is_groupjumper_needed() {
        global $COURSE, $USER;

        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        if (!\groups_get_activity_groupmode($this->cm, $COURSE)) {
            return false;
        }

        if ($canaccessallgroups) { // You can see only your groups.
            $allgroups = \groups_get_all_groups($COURSE->id);
        } else {
            $allgroups = \groups_get_all_groups($COURSE->id, $USER->id);
        }

        return (count($allgroups) > 1);
    }

    /**
     * Does the item "Not in any group" must be added?
     *
     * @return boolean
     */
    public function add_notinanygroup() {
        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $this->context);

        if ($canviewhiddenactivities) {
            $noroleusers = $this->count_unenrolled_users();

            return ($noroleusers > 0);
        } else {
            return false;
        }
    }

    /**
     * Count the number of users...
     * WITH submission in the current surveypro
     * AND WITHOUT ANY ROLE in the course.
     *
     * @return int;
     */
    private function count_unenrolled_users() {
        global $DB;

        list($enrolsql, $whereparams) = get_enrolled_sql($this->context);

        $sql = 'SELECT COUNT(\'x\')
                FROM {user} u
                    JOIN {surveypro_submission} s ON s.userid = u.id
                    LEFT JOIN ('.$enrolsql.') eu ON eu.id = u.id
                WHERE s.surveyproid = :surveyproid
                    AND eu.id IS NULL';

        $whereparams['surveyproid'] = $this->surveypro->id;

        return $DB->count_records_sql($sql, $whereparams);
    }

    // MARK set.

    /**
     * Set the groupid.
     *
     * @param int $groupid
     */
    public function set_groupid($groupid) {
        $this->groupid = $groupid;
    }

    // MARK get.

    /**
     * Get the list of groups the user is allowed to browse
     *
     * @return array of expected groups
     */
    public function get_groupjumper_items() {
        global $COURSE, $USER;

        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        if ($canaccessallgroups) { // You can see only your groups.
            $allgroups = \groups_get_all_groups($COURSE->id);
        } else {
            $allgroups = \groups_get_all_groups($COURSE->id, $USER->id);
        }

        return $allgroups;
    }

    /**
     * get_middle_sql
     *
     * @param bool $actualrelation the kind of relation I need in the query
     * @return array($sql, $whereparams);
     */
    public function get_middle_sql($actualrelation=true) {
        global $COURSE;

        $coursecontext = \context_course::instance($COURSE->id);
        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $coursecontext);

        $whereparams = array();
        if ($actualrelation) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        } else {
            $whereparams['surveyproid'] = null;
        }

        list($enrolsql, $eparams) = get_enrolled_sql($coursecontext);

        $sql = '';
        if ($canviewhiddenactivities) { // You are an admin.
            switch ($this->groupid) {
                case -1: // Users not enrolled in this course.
                    $sql .= ' LEFT JOIN ('.$enrolsql.') eu ON eu.id = u.id';
                    break;
                case 0: // Each user with submissions.
                    // JOIN $enrolsql is needed to take guest out!
                    $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';
                    break;
                default: // Each user of group xx with submissions.
                    $sql .= ' JOIN {groups_members} gm ON gm.userid = u.id';
                    $whereparams['groupid'] = $this->groupid;
            }
        } else { // You are a teacher.
            $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';

            // $this->groupid == -1 is IMPOSSIBLE. If !$canviewhiddenactivities, $groupid can't be -1.
            if ($this->groupid > 0) {
                $sql .= ' JOIN {groups_members} gm ON gm.userid = u.id';
                $whereparams['groupid'] = $this->groupid;
            }
        }

        $conditions = array();
        foreach ($whereparams as $k => $v) {
            if ($v === null) {
                $conditions[] = $k.' IS NULL';
            } else {
                $conditions[] = $k.' = :'.$k;
            }
        }
        $sql .= ' WHERE '.implode(' AND ', $conditions);

        // The query for graphs don't make use of $this->outputtable.
        if (isset($this->outputtable)) {
            list($where, $filterparams) = $this->outputtable->get_sql_where();
            if ($where) {
                $sql .= ' AND '.$where;
                $whereparams = array_merge($whereparams,  $filterparams);
            }
        }

        $whereparams = array_merge($whereparams, $eparams);
        return array($sql, $whereparams);
    }
}
