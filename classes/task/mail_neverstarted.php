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
 * A scheduled task for surveypro cron.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\task;

/**
 * The main schedule task for the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_neverstarted extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('reminderneverstarted_task', 'mod_surveypro');
    }

    /**
     * Send an email to enrolled users that NEVER started the required surveypro.
     *
     * @return void
     */
    public function execute() {
        // Get the list of enrolled users that NEVER started the required surveypro
        global $DB, $USER, $SITE, $CFG;

        $sql = 'SELECT id, name, course
            FROM {surveypro} s
	        JOIN {course} c ON c.id = s.course
        WHERE (
                (c.startdate = 0 AND c.enddate = 0)
                    OR
                (c.startdate < ? AND c.enddate = 0)
                    OR
                (c.startdate = 0 AND c.enddate > ?)
                    OR
                (c.startdate < ? AND c.enddate > ?)
              )
            AND
              (
                ( (s.timeopen IS NULL OR s.timeopen = 0) AND (s.timeclose IS NULL OR s.timeclose = 0) )
                    OR
                ( (s.timeopen IS NOT NULL AND s.timeopen < ?) AND (s.timeclose IS NULL OR s.timeclose = 0) )
                    OR
                ( (s.timeopen IS NULL OR s.timeopen = 0) AND (s.timeclose IS NOT NULL AND s.timeclose > ?) )
                    OR
                ( (s.timeopen IS NOT NULL AND s.timeopen < ?) AND (s.timeclose IS NOT NULL AND s.timeclose > ?) )
              )
            AND s.neverstartedemail = ?';
        $timenow = time();
        $whereparams = array_fill(0, 7, $timenow);
        $whereparams[] = 1;

        $surveypros = $DB->get_recordset_sql($sql, $whereparams);
        if ($surveypros->valid()) {
            $from = $USER;
            $subject = get_string('reminder_subject', 'surveypro', $SITE->fullname);

            foreach ($surveypros as $surveypro) {

                $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
                $context = \context_module::instance($cm->id);

                // Search for users who never started their surveypro.
                list($enrolsql, $whereparams) = get_enrolled_sql($context);
                $allnames = \core_user\fields::get_name_fields();
                $sql = 'SELECT u.id, u.email, u.deleted, u.auth, u.suspended, u.';
                $sql .= implode(', u.', $allnames);
                $sql .= ' FROM {user} u
                            JOIN ('.$enrolsql.') eu ON eu.id = u.id
                            LEFT JOIN (SELECT id, userid
                                FROM {surveypro_submission}
                                WHERE surveyproid = :surveyproid) s ON s.userid = u.id
                        WHERE s.id IS NULL';
                $whereparams['surveyproid'] = $surveypro->id;

                $a = new \stdClass();
                $a->surveyproname = $surveypro->name;
                $a->surveyprourl = $CFG->wwwroot.'/mod/surveypro/view.php?id='.$surveypro->id;
                $rs = $DB->get_recordset_sql($sql, $whereparams);
                foreach ($rs as $user) {
                    $a->fullname = fullname($user);
                    $message = get_string('remindneverstarted_content', 'surveypro', $a);
                    // Direct email.
                    email_to_user($user, $from, $subject, $message);
                }
            }
        }
    }
}
