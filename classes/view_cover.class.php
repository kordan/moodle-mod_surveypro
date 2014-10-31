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
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class representing a field
 */
class mod_surveypro_covermanager {
    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $cansubmit
     */
    public $cansubmit = false;

    /**
     * $canignoremaxentries
     */
    public $canignoremaxentries = false;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        global $DB;

        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        // $this->canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $this->cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $this->canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
    }

    /**
     * display_cover
     *
     * @param none
     * @return
     */
    public function display_cover() {
        global $OUTPUT, $CFG, $COURSE;

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context, null, true);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context, null, true);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context, null, true);
        $cansaveusertemplate = has_capability('mod/surveypro:saveusertemplates', context_course::instance($COURSE->id), null, true);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context, null, true);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context, null, true);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context, null, true);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context, null, true);
        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        $hassubmissions = surveypro_count_submissions($this->surveypro->id);

        $messages = array();
        $timenow = time();

        // user submitted responses:
        $countclosed = $this->user_sent_submissions(SURVEYPRO_STATUSCLOSED);
        $inprogress = $this->user_sent_submissions(SURVEYPRO_STATUSINPROGRESS);
        $next = $countclosed + $inprogress + 1;

        // is the button to add one more surveypro going to be displayed?
        $displaybutton = $this->cansubmit;
        if ($this->surveypro->timeopen) {
            $displaybutton = $displaybutton && ($this->surveypro->timeopen < $timenow);
        }
        if ($this->surveypro->timeclose) {
            $displaybutton = $displaybutton && ($this->surveypro->timeclose > $timenow);
        }
        if (!$this->canignoremaxentries) {
            $displaybutton = $displaybutton && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
        }
        // End of: is the button to add one more surveypro going to be displayed?

        echo $OUTPUT->heading(get_string('coverpage_welcome', 'surveypro', $this->surveypro->name));
        if ($this->surveypro->intro) {
            echo $OUTPUT->box(format_module_intro('surveypro', $this->surveypro, $this->cm->id), 'generalbox description', 'intro');
            // old code
            // $intro = file_rewrite_pluginfile_urls($this->surveypro->intro, 'pluginfile.php', $this->context->id, 'mod_surveypro', 'intro', null);
            // echo $OUTPUT->box($intro, 'generalbox description', 'intro');
        }

        // general info
        if ($this->surveypro->timeopen) { // opening time:
            $key = ($this->surveypro->timeopen > $timenow) ? 'willopen' : 'opened';
            $messages[] = get_string($key, 'surveypro').$labelsep.userdate($this->surveypro->timeopen);
        }

        if ($this->surveypro->timeclose) { // closing time:
            $key = ($this->surveypro->timeclose > $timenow) ? 'willclose' : 'closed';
            $messages[] = get_string($key, 'surveypro').$labelsep.userdate($this->surveypro->timeclose);
        }

        if ($this->cansubmit) {
            if (!$this->canignoremaxentries) {
                $maxentries = ($this->surveypro->maxentries) ? $this->surveypro->maxentries : get_string('unlimited', 'surveypro');
            } else {
                $maxentries = get_string('unlimited', 'surveypro');
            }
            $messages[] = get_string('maxentries', 'surveypro').$labelsep.$maxentries;

            // user closed attempt number:
            $messages[] = get_string('closedsubmissions', 'surveypro', $countclosed);

            // your in progress attempt number:
            $messages[] = get_string('inprogresssubmissions', 'surveypro', $inprogress);

            if ($displaybutton) {
                $messages[] = get_string('yournextattempt', 'surveypro', $next);
            }
        }

        $this->display_messages($messages, get_string('attemptinfo', 'surveypro'));
        $messages = array();
        // end of: general info

        if ($displaybutton) {
            $url = new moodle_url('/mod/surveypro/view_userform.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            echo $OUTPUT->box($OUTPUT->single_button($url, get_string('addnewsubmission', 'surveypro'), 'get'), 'clearfix mdl-align');
        } else {
            if (!$this->cansubmit) {
                $message = get_string('canneversubmit', 'surveypro');
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->timeopen) && ($this->surveypro->timeopen >= $timenow)) {
                $message = get_string('cannotsubmittooearly', 'surveypro', userdate($this->surveypro->timeopen));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->timeclose) && ($this->surveypro->timeclose <= $timenow)) {
                $message = get_string('cannotsubmittoolate', 'surveypro', userdate($this->surveypro->timeclose));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->maxentries > 0) && ($next >= $this->surveypro->maxentries)) {
                $message = get_string('nomoresubmissionsallowed', 'surveypro', $this->surveypro->maxentries);
                echo $OUTPUT->container($message, 'centerpara');
            }
        }
        // end of: the button to add one more surveypro

        // report
        $surveyproreportlist = get_plugin_list('surveyproreport');
        $paramurlbase = array('id' => $this->cm->id);
        foreach ($surveyproreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/surveypro/report/'.$pluginname.'/classes/report.class.php');
            $classname = 'mod_surveypro_report_'.$pluginname;
            $reportman = new $classname($this->cm, $this->surveypro);

            $restricttemplates = $reportman->restrict_templates();

            if ((!$restricttemplates) || in_array($this->surveypro->template, $restricttemplates)) {
                if ($canaccessreports || ($reportman->has_student_report() && $canaccessownreports)) {
                    if ($reportman->does_report_apply()) {
                        if ($childreports = $reportman->get_childreports($canaccessreports)) {
                            foreach ($childreports as $childname => $childparams) {
                                $childparams['s'] = $this->cm->instance;
                                $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $childparams);
                                $a = new stdClass();
                                $a->href = $url->out();
                                $a->reportname = get_string('pluginname', 'surveyproreport_'.$pluginname).$labelsep.$childname;
                                $messages[] = get_string('runreport', 'surveypro', $a);
                            }
                        } else {
                            $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $paramurlbase);
                            $a = new stdClass();
                            $a->href = $url->out();
                            $a->reportname = get_string('pluginname', 'surveyproreport_'.$pluginname);
                            $messages[] = get_string('runreport', 'surveypro', $a);
                        }
                    }
                }
            }
        }

        $this->display_messages($messages, get_string('reportsection', 'surveypro'));
        $messages = array();
        // end of: report

        // user templates
        if ($canmanageusertemplates) {
            $url = new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurlbase);
            $messages[] = get_string('manageusertemplates', 'surveypro', $url->out());
        }

        if ($cansaveusertemplate) {
            $url = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurlbase);
            $messages[] = get_string('saveusertemplates', 'surveypro', $url->out());
        }

        if ($canimportusertemplates) {
            $url = new moodle_url('/mod/surveypro/utemplates_import.php', $paramurlbase);
            $messages[] = get_string('importusertemplates', 'surveypro', $url->out());
        }

        if ($canapplyusertemplates && (!$hassubmissions || $riskyediting)) {
            $url = new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applyusertemplates', 'surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('utemplatessection', 'surveypro'));
        $messages = array();
        // end of: user templates

        // master templates
        if ($cansavemastertemplates) {
            $url = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurlbase);
            $messages[] = get_string('savemastertemplates', 'surveypro', $url->out());
        }

        if ($canapplymastertemplates) {
            $url = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applymastertemplates', 'surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('mtemplatessection', 'surveypro'));
        $messages = array();
        // end of: master templates
    }

    /**
     * display_messages
     *
     * @param $messages
     * @param $strlegend
     * @return
     */
    public function display_messages($messages, $strlegend) {
        global $OUTPUT;

        if (count($messages)) {
            // echo $OUTPUT->box_start('box generalbox description', 'intro');
            echo html_writer::start_tag('fieldset', array('class' => 'generalbox'));
            echo html_writer::start_tag('legend', array('class' => 'coverinfolegend'));
            echo $strlegend;
            echo html_writer::end_tag('legend');
            foreach ($messages as $message) {
                echo $OUTPUT->container($message, 'mdl-left');
            }
            echo html_writer::end_tag('fieldset');
            // echo $OUTPUT->box_end();
        }
    }

    /**
     * user_sent_submissions
     *
     * @param $status
     * @return
     */
    public function user_sent_submissions($status=SURVEYPRO_STATUSALL) {
        global $USER, $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'userid' => $USER->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $statuslist = array(SURVEYPRO_STATUSCLOSED, SURVEYPRO_STATUSINPROGRESS);
            if (!in_array($status, $statuslist)) {
                $a = 'user_sent_submissions';
                print_error('invalid_status', 'surveypro', null, $a);
            }
            $whereparams['status'] = $status;
        }

        return $DB->count_records('surveypro_submission', $whereparams);
    }
}
