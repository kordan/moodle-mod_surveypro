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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * The main schedule task for the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_pauseresume extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('reminderouncompleted_task', 'mod_surveypro');
    }

    /**
     * Send an email to users forgetting their paused surveypro.
     *
     * @return void
     */
    public function execute() {
        global $DB, $USER, $SITE, $CFG;

        $sql = 'SELECT id, name, keepinprogress
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
            AND s.pauseresume = ?';
        $timenow = time();
        $whereparams = array_fill(0, 7, $timenow);
        $whereparams[] = SURVEYPRO_PAUSERESUMEEMAIL;

        $surveypros = $DB->get_recordset_sql($sql, $whereparams);
        if ($surveypros->valid()) {
            $maxinputdelay = get_config('mod_surveypro', 'maxinputdelay');
            // pauseresume == SURVEYPRO_PAUSERESUMEEMAIL means: You are allowed to pause.
            // BUT you are still supposed to submit your survey in $maxinputdelay hours.
            // I remind your survey if you wait more than 25% of the allowed delay.
            // Issue: if $maxinputdelay is short enough...
            // I may never send you an email because at cron time your record has already been deleted.
            $sofar = 3600 * $maxinputdelay / 4;
            $sofar = time() - $sofar;
            $from = $USER;
            $subject = get_string('reminder_subject', 'surveypro', $SITE->fullname);

            foreach ($surveypros as $surveypro) {
                // Search for users with in progress surveypro.
                $whereparams = array();
                $submissiontable = 'SELECT userid, surveyproid
                                    FROM {surveypro_submission} ss
                                    WHERE ss.surveyproid = :surveyproid
                                    AND ss.status = :status
                                    AND (
                                        ((ss.timemodified IS NOT NULL) AND (ss.timemodified  < :sofar))
                                        OR
                                        ((ss.timemodified IS NULL) AND (ss.timecreated  < :stillsofar))
                                        )
                                    GROUP BY ss.userid, ss.surveyproid';

                $whereparams = [];
                $whereparams['surveyproid'] = $surveypro->id;
                $whereparams['status'] = SURVEYPRO_STATUSINPROGRESS;
                $whereparams['sofar'] = $sofar;
                $whereparams['stillsofar'] = $sofar;

                $allnames = \core_user\fields::get_name_fields();
                $sql = 'SELECT s.surveyproid, u.id, u.email, u.deleted, u.auth, u.suspended, u.';
                $sql .= implode(', u.', $allnames);
                $sql .= ' FROM {user} u
                        RIGHT JOIN ('.$submissiontable.') s ON s.userid = u.id';

                $warningstr = get_string('reminderpaused_content2', 'surveypro');
                $a = new \stdClass();
                $a->surveyproname = $surveypro->name;
                $a->surveyprourl = $CFG->wwwroot.'/mod/surveypro/view.php?id='.$surveypro->id;
                $rs = $DB->get_recordset_sql($sql, $whereparams);
                foreach ($rs as $user) {
                    $a->fullname = fullname($user);
                    // Your survey named is paused since a long time.
                    $message = get_string('reminderpaused_content1', 'surveypro', $a);
                    if (empty($surveypro->keepinprogress)) {
                        // There is a concrete risk to have it dropped
                        $message .= $warningstr;
                    }
                    // Please consider to login again and submit it.
                    $message .= get_string('reminderpaused_content3', 'surveypro', $a->surveyprourl);
                    // Direct email.
                    email_to_user($user, $from, $subject, $message);
                }
            }
        }
    }
}
