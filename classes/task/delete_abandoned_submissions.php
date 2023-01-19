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

use mod_surveypro\utility_layout;
use mod_surveypro\task\crontaskbase;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * The main schedule task for the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_abandoned_submissions extends crontaskbase {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('deleteabandoned_task', 'mod_surveypro');
    }

    /**
     * Delete too old submissions from surveypro_answer and surveypro_submission.
     *
     * @return void
     */
    public function execute() {
        global $DB;

        $surveyprofields = ['s.id', 's.keepinprogress', 's.pauseresume', 's.course'];
        list($sql, $whereparams) = $this->get_sqltimewindow($surveyprofields);
        $sql .= 'AND s.keepinprogress = ?';
        $whereparams[] = 0;

        $surveypros = $DB->get_recordset_sql($sql, $whereparams);
        if ($surveypros->valid()) {
            $maxinputdelay = get_config('mod_surveypro', 'maxinputdelay');

            foreach ($surveypros as $surveypro) {
                $pasuseresumesurvey = ($surveypro->pauseresume == SURVEYPRO_PAUSERESUMENOEMAIL);
                $pasuseresumesurvey = $pasuseresumesurvey || ($surveypro->pauseresume == SURVEYPRO_PAUSERESUMEEMAIL);
                if ($pasuseresumesurvey && ($maxinputdelay == 0)) { // Maxinputdelay == 0 means, please don't delete.
                    continue;
                }

                // If !$pasuseresumesurvey then pauseresume is not allowed.
                // Users leaved responses in progress more than four hours ago.
                // I can not believe they are still working on them so I delete thier responses now.

                // If $pasuseresumesurvey then pauseresume is allowed.
                // Users leaved responses in progress more than maximum allowed time delay.
                // I delete thier responses now.
                $sofar = $pasuseresumesurvey ? ($maxinputdelay * 3600) : (4 * 3600);
                $sofar = time() - $sofar;

                // Second step: if you are here, for each surveypro
                // filter only submissions having 'status' = SURVEYPRO_STATUSINPROGRESS and timecreated < :sofar.
                $where = 'surveyproid = :surveyproid AND status = :status AND timecreated < :sofar';
                $whereparams = array('surveyproid' => $surveypro->id, 'status' => SURVEYPRO_STATUSINPROGRESS, 'sofar' => $sofar);
                if ($submissions = $DB->get_recordset_select('surveypro_submission', $where, $whereparams, 'surveyproid', 'id, userid')) {
                    // Those submissions all belong to THE SAME surveypro because $where = 'surveyproid = :surveyproid.
                    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);
                    $context = \context_module::instance($cm->id);

                    $utilitylayoutman = new utility_layout($cm, $surveypro);

                    foreach ($submissions as $submission) {
                        // Third step: delete each selected submission.
                        $utilitylayoutman->delete_submissions(array('id' => $submission->id));

                        // Event: mail_oneshotmp_sent.
                        $eventdata = ['context' => $context, 'objectid' => $surveypro->id, 'relateduserid' => $submission->userid];
                        $event = \mod_surveypro\event\abandoned_submission_deleted::create($eventdata);
                        $event->trigger();
                    }
                }
            }
        }
    }
}
