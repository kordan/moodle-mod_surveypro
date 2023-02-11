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
 * Surveypro utility class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_submission;

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_submission {

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
     * @param object $surveypro
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        $this->context = \context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * Set the status to submissions.
     *
     * @param array $whereparams
     * @param bool $status
     * @return void
     */
    public function submissions_set_status($whereparams, $status) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        if ( ($status != SURVEYPRO_STATUSCLOSED) && ($status != SURVEYPRO_STATUSINPROGRESS) ) {
            debugging('Bad parameters passed to submissions_set_status', DEBUG_DEVELOPER);
        }

        $whereparams['status'] = 1 - $status;
        $DB->set_field('surveypro_submission', 'status', $status, $whereparams);
    }

    /**
     * Display an alarming message whether there are submissions.
     *
     * @return void
     */
    public function get_submissions_warning() {
        global $COURSE;

        $message = get_string('hassubmissions_alert', 'mod_surveypro');

        $keepinprogress = $this->surveypro->keepinprogress;
        if (empty($keepinprogress)) {
            $message .= get_string('hassubmissions_danger', 'mod_surveypro');
        }

        $completion = new \completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $message .= get_string('hassubmissions_alert_activitycompletion', 'mod_surveypro');
        }

        return $message;
    }

    /**
     * Get used plugin list.
     *
     * This method provide the list af the plugin used in the current surveypro
     * getting them from the items already added
     *
     * @param string $type Optional plugin type
     * @return array $pluginlist;
     */
    public function get_used_plugin_list($type='') {
        global $DB;

        $whereparams = array();
        $sql = 'SELECT plugin
                FROM {surveypro_item}
                WHERE surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;
        if (!empty($type)) {
            $sql .= ' AND type = :type';
            $whereparams['type'] = $type;
        }
        $sql .= ' GROUP BY plugin';

        $pluginlist = $DB->get_fieldset_sql($sql, $whereparams);

        return $pluginlist;
    }

    /**
     * surveypro_groupmates
     *
     * @param object $cm
     * @param int $userid Optional $userid: the user you want to know his/her groupmates
     * @return Array with the list of groupmates of the user
     */
    public function get_groupmates($cm, $userid=0) {
        global $COURSE, $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $groupusers = array();
        if ($currentgroups = groups_get_all_groups($COURSE->id, $USER->id)) {
            foreach ($currentgroups as $currentgroup) {
                $groupusers += groups_get_members($currentgroup->id, 'u.id');
            }
        }

        return array_keys($groupusers);
    }
}
