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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

/**
 * The base class representing a field
 */
class mod_surveypro_reportbase {
    /**
     * @var object, the course module object
     */
    protected $cm;

    /**
     * @var object, the context object
     */
    protected $context;

    /**
     * @var object, the surveypro object
     */
    protected $surveypro;

    /**
     * Class constructor
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
     * report_apply
     */
    public function report_apply() {
        return true;
    }

    /**
     * get_childreports
     *
     * @param bool $canaccessreports
     * @return void
     */
    public function get_childreports($canaccessreports) {
        return false;
    }

    /**
     * check_submissions
     */
    public function check_submissions() {
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
}
