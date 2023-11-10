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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

/**
 * The base class representing a report
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class reportbase {

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
     * @var array $additionalparams
     */
    public $additionalparams;

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
     * Does the current report apply to the passed mastertemplates?
     *
     * @param string $mastertemplate
     * @return boolean
     */
    abstract public function report_applies_to($mastertemplate);

    /**
     * Returns if this report was created for student too.
     *
     * @return boolean
     */
    abstract public static function get_hasstudentreport();

    /**
     * Does this report display user names.
     *
     * @return boolean
     */
    abstract public static function get_displayusernames();

    /**
     * Get child reports.
     *
     * @return boolean
     */
    public function get_haschildrenreports() {
        return false;
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        if (!$this->is_report_allowed()) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Is the use of the current report allowed?
     *
     * @return bool $condition
     */
    public function is_report_allowed() {
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);

        $condition = $canaccessreports;
        $condition = $condition || ($canaccessownreports && $this->get_hasstudentreport());
        $condition = $condition && $this->report_applies_to($this->surveypro->template);

        // GDPR condition.
        $othercondition = !$this->get_displayusernames() || (empty($this->surveypro->anonymous) || $canalwaysseeowner);
        $condition = $condition && $othercondition;

        return $condition;
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

        if (!groups_get_activity_groupmode($this->cm, $COURSE)) {
            return false;
        }

        if ($canaccessallgroups) { // You can see only your groups.
            $allgroups = groups_get_all_groups($COURSE->id);
        } else {
            $allgroups = groups_get_all_groups($COURSE->id, $USER->id);
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
            $allgroups = groups_get_all_groups($COURSE->id);
        } else {
            $allgroups = groups_get_all_groups($COURSE->id, $USER->id);
        }

        return $allgroups;
    }

    /**
     * get_middle_sql
     *
     * @param bool $actualrelation the kind of relation I need in the query
     * @return [$sql, $whereparams];
     */
    public function get_middle_sql($actualrelation=true) {
        global $COURSE;

        $coursecontext = \context_course::instance($COURSE->id);
        $canviewhiddenactivities = has_capability('moodle/course:viewhiddenactivities', $coursecontext);

        $whereparams = [];
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

        $conditions = [];
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
        return [$sql, $whereparams];
    }

    /**
     * set_additionalparams.
     *
     * Sets the parameters to be supplied to the url to call the specific report page
     */
    public function set_additionalparams() {
        $this->additionalparams = ['optional' => [], 'required' => []];
    }

    /**
     * get_paramurl.
     *
     * @return $paramurl to be supplied to the url to call the specific report page
     */
    public function get_paramurl(): array {
        $paramurl = ['s' => $cm->instance];

        foreach ($params['optional'] as $variable => $type) {
            $value = optional_param($variable, '', $type);
            $paramurl[$variable] = $value;
        }
        foreach ($params['required'] as $variable => $type) {
            $value = required_param($variable, $type);
            $paramurl[$variable] = $value;
        }

        return $paramurl;
    }
}
