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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class representing a field
 */
class mod_surveypro_reportbase {
    /**
     * cm
     */
    public $cm = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $coursecontext: the record of this surveypro
     */
    public $coursecontext = null;

    /**
     * $hassubmissions: the record of this surveypro
     */
    public $hassubmissions = false;

    /**
     * $canaccessownreports
     */
    public $canaccessownreports = false;

    /**
     * Class constructor
     */
    public function __construct($cm, $surveypro) {
        global $COURSE;

        $this->cm = $cm;
        $this->coursecontext = context_course::instance($COURSE->id);
        $this->surveypro = $surveypro;
        $this->canaccessreports = has_capability('mod/surveypro:accessreports', $this->coursecontext, null, true);
        $this->canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->coursecontext, null, true);
    }

    /**
     * restrict_templates
     */
    public function restrict_templates() {
        return array();
    }

    /**
     * has_student_report
     */
    public function has_student_report() {
        return false;
    }

    /**
     * does_report_apply
     */
    public function does_report_apply() {
        return true;
    }


    /**
     * get_childreports
     */
    public function get_childreports($canaccessreports) {
        return false;
    }

    /**
     * check_submissions
     */
    public function check_submissions() {
        global $OUTPUT;

        $hassubmissions = surveypro_count_submissions($this->surveypro->id);
        if (!$hassubmissions) {
            $message = get_string('nosubmissionfound', 'surveypro');
            echo $OUTPUT->box($message, 'notice centerpara');

            // Finish the page
            echo $OUTPUT->footer();

            die();
        }
    }
}