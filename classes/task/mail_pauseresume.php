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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\task;

use mod_surveypro\task\crontaskbase;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * The main schedule task for the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mail_pauseresume extends crontaskbase {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('reminder_pauseresume_task', 'mod_surveypro');
    }

    /**
     * Send an email to users forgetting their paused surveypro.
     *
     * @return void
     */
    public function execute() {
        global $DB, $USER, $SITE, $CFG;

        $surveyprofields = ['s.id', 's.name', 's.course'];
        list($sql, $whereparams) = $this->get_sqltimewindow($surveyprofields);
        $sql .= ' AND s.pauseresume = ?';
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
                $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
                $context = \context_module::instance($cm->id);

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

                $userfields = ['id', 'firstname', 'lastname', 'username', 'email'];
                $userfieldsapi = \core_user\fields::for_name()->including(...$userfields);
                $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
                $sql = 'SELECT s.surveyproid, '.$userfieldssql;
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

                    // Event: mail_pauseresume_sent.
                    $eventdata = ['context' => $context, 'objectid' => $surveypro->id, 'relateduserid' => $user->id];
                    $event = \mod_surveypro\event\mail_pauseresume_sent::create($eventdata);
                    $event->trigger();
                }
            }
        }
    }
}
