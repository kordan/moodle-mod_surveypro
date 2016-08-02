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

defined('MOODLE_INTERNAL') || die();

/**
 * The base class representing a report
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_reportbase {

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
    public function allowed_templates() {
        return array();
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
     * Return if this report applies.
     *
     * true means: the report apply
     * (!$this->surveypro->anonymous) means that reports applies ONLY IF user is not anonymous
     *
     * @return boolean
     */
    public function report_apply() {
        return true;
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
        global $OUTPUT;

        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $hassubmissions = $utilityman->has_submissions();
        if (!$hassubmissions) {
            $message = get_string('nosubmissionfound', 'mod_surveypro');
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
     * Get the list of groups the user is allowed to browse
     *
     * @return array of expected groups
     */
    public function get_groupjumper_content() {
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

        $sql = 'SELECT COUNT(\'x\')
                FROM {user} u
                    JOIN {surveypro_submission} s ON u.id = s.userid
                    LEFT JOIN {role_assignments} ra ON u.id = ra.userid
                WHERE surveyproid = :surveyproid
                    AND contextid IS NULL';

        $whereparams = array();
        $whereparams['surveyproid'] = $this->surveypro->id;

        return $DB->count_records_sql($sql, $whereparams);
    }

    /**
     * Get_submissions_sql
     *
     * @var int $groupid
     * @var int $canviewhiddenactivities
     * @return array($sql, $whereparams);
     */
    public function get_submissions_sql($groupid, $canviewhiddenactivities) {
        global $COURSE, $DB;

        $coursecontext = context_course::instance($COURSE->id);

        $whereparams = array();
        $sql = 'SELECT '.user_picture::fields('u').', s.id as submissionid
                FROM {user} u
                JOIN {surveypro_submission} s ON u.id = s.userid';
        $whereparams['surveyproid'] = $this->surveypro->id;
        if ($canviewhiddenactivities) { // You are an admin.
            switch ($groupid) {
                case -1: // You want to see people with no role in this course.
                    $sql .= ' LEFT JOIN {role_assignments} ra ON u.id = ra.userid';
                    $whereparams['contextid'] = null;
                    break;
                case 0: // Each user with submissions.
                    break;
                default:
                    $sql .= ' JOIN {groups_members} gm ON u.id = gm.userid';
                    $whereparams['groupid'] = $groupid;
            }
        } else { // You are a teacher.
            switch ($groupid) {
                case -1: // IMPOSSIBLE. If !$canviewhiddenactivities, $groupid can't be -1.
                    break;
                case 0: // Each user with a role in the course and submissions.
                    $sql .= ' JOIN {role_assignments} ra ON u.id = ra.userid';
                    $whereparams['contextid'] = $coursecontext->id;
                    break;
                default:
                    $sql .= ' JOIN {role_assignments} ra ON u.id = ra.userid';
                    $whereparams['contextid'] = $coursecontext->id;

                    $sql .= ' JOIN {groups_members} gm ON u.id = gm.userid';
                    $whereparams['groupid'] = $groupid;
            }
        }

        $conditions = array();
        foreach ($whereparams as $k => $v) {
            if ($v === null) {
                $conditions[] .= $k.' IS NULL';
                // unset($whereparams[$k]);
            } else {
                $conditions[] .= $k.' = :'.$k;
            }
        }
        $sql .= ' WHERE '.implode(' AND ', $conditions);

        list($where, $filterparams) = $this->outputtable->get_sql_where();
        if ($where) {
            $sql .= ' AND '.$where;
            $whereparams = array_merge($whereparams,  $filterparams);
        }

        if ($this->outputtable->get_sql_sort()) {
            $sql .= ' ORDER BY '.$this->outputtable->get_sql_sort().', submissionid ASC';
        } else {
            $sql .= ' ORDER BY u.lastname ASC, submissionid ASC';
        }

        return array($sql, $whereparams);
    }
}
