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
     * $hasitems
     */
    public $hasitems = 0;

    /**
     * $cansubmit
     */
    public $cansubmit = false;

    /**
     * $canignoremaxentries
     */
    public $canignoremaxentries = false;

    /**
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        // $this->canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $this->hasitems = $this->has_input_items();
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);
        $this->cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $this->canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
    }

    /**
     * has_input_items
     *
     * @param none
     * @return
     */
    public function has_input_items() {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'hidden' => 0);
        if (!$this->canaccessadvanceditems) {
            $whereparams['advanced'] = 0;
        }

        return ($DB->count_records('surveypro_item', $whereparams) > 0);
    }

    /**
     * display_cover
     *
     * @param none
     * @return
     */
    public function display_cover() {
        global $CFG, $OUTPUT, $COURSE;

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

        // User submitted responses.
        $countclosed = $this->user_sent_submissions(SURVEYPRO_STATUSCLOSED);
        $inprogress = $this->user_sent_submissions(SURVEYPRO_STATUSINPROGRESS);
        $next = $countclosed + $inprogress + 1;

        // Begin of: the button to add one more surveypro.
        // Begin of: is the button to add one more surveypro going to be displayed?
        $displaybutton = $this->cansubmit;
        $displaybutton = $displaybutton && $this->hasitems;
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

        echo $OUTPUT->heading(get_string('coverpage_welcome', 'mod_surveypro', $this->surveypro->name));
        if ($this->surveypro->intro) {
            echo $OUTPUT->box(format_module_intro('surveypro', $this->surveypro, $this->cm->id), 'generalbox description', 'intro');
        }

        // Begin of: general info.
        if ($this->surveypro->timeopen) { // Opening time.
            $langkey = ($this->surveypro->timeopen > $timenow) ? 'willopen' : 'opened';
            $messages[] = get_string($langkey, 'mod_surveypro').$labelsep.userdate($this->surveypro->timeopen);
        }

        if ($this->surveypro->timeclose) { // Closing time.
            $langkey = ($this->surveypro->timeclose > $timenow) ? 'willclose' : 'closed';
            $messages[] = get_string($langkey, 'mod_surveypro').$labelsep.userdate($this->surveypro->timeclose);
        }

        if ($this->cansubmit) {
            if (!$this->canignoremaxentries) {
                $maxentries = ($this->surveypro->maxentries) ? $this->surveypro->maxentries : get_string('unlimited', 'mod_surveypro');
            } else {
                $maxentries = get_string('unlimited', 'mod_surveypro');
            }
            $messages[] = get_string('maxentries', 'mod_surveypro').$labelsep.$maxentries;

            // Your 'closed' responses.
            $a = new stdClass();
            $a->status = get_string('statusclosed', 'mod_surveypro');
            $a->responsescount = $countclosed;
            $messages[] = get_string('yoursubmissions', 'mod_surveypro', $a);

            // Your 'in progress' responses.
            $a = new stdClass();
            $a->status = get_string('statusinprogress', 'mod_surveypro');
            $a->responsescount = $inprogress;
            $messages[] = get_string('yoursubmissions', 'mod_surveypro', $a);

            if ($displaybutton) {
                $messages[] = get_string('yournextattempt', 'mod_surveypro', $next);
            }
        }

        $this->display_messages($messages, get_string('attemptinfo', 'mod_surveypro'));
        $messages = array();
        // End of: general info.

        if ($displaybutton) {
            $url = new moodle_url('/mod/surveypro/view_userform.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            echo $OUTPUT->box($OUTPUT->single_button($url, get_string('addnewsubmission', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
        } else {
            if (!$this->cansubmit) {
                $message = get_string('canneversubmit', 'mod_surveypro');
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->timeopen) && ($this->surveypro->timeopen >= $timenow)) {
                $message = get_string('cannotsubmittooearly', 'mod_surveypro', userdate($this->surveypro->timeopen));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->timeclose) && ($this->surveypro->timeclose <= $timenow)) {
                $message = get_string('cannotsubmittoolate', 'mod_surveypro', userdate($this->surveypro->timeclose));
                echo $OUTPUT->container($message, 'centerpara');
            } else if (($this->surveypro->maxentries > 0) && ($next >= $this->surveypro->maxentries)) {
                $message = get_string('nomoresubmissionsallowed', 'mod_surveypro', $this->surveypro->maxentries);
                echo $OUTPUT->container($message, 'centerpara');
            } else if (!$this->hasitems) {
                $message = get_string('noitemsfound', 'mod_surveypro');
                echo $OUTPUT->container($message, 'centerpara');
            }
        }
        // End of: the button to add one more surveypro.

        if (!$this->hasitems) {
            return;
        }

        // Begin of: report section.
        $surveyproreportlist = get_plugin_list('surveyproreport');
        $paramurlbase = array('id' => $this->cm->id);
        foreach ($surveyproreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/surveypro/report/'.$pluginname.'/classes/report.class.php');
            $classname = 'mod_surveypro_report_'.$pluginname;
            $reportman = new $classname($this->cm, $this->context, $this->surveypro);

            $restricttemplates = $reportman->restrict_templates();

            if ((!$restricttemplates) || in_array($this->surveypro->template, $restricttemplates)) {
                if ($canaccessreports || ($reportman->has_student_report() && $canaccessownreports)) {
                    if ($reportman->report_apply()) {
                        if ($childreports = $reportman->get_childreports($canaccessreports)) {
                            foreach ($childreports as $childname => $childparams) {
                                $childparams['s'] = $this->cm->instance;
                                $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $childparams);
                                $a = new stdClass();
                                $a->href = $url->out();
                                $a->reportname = get_string('pluginname', 'surveyproreport_'.$pluginname).$labelsep.$childname;
                                $messages[] = get_string('runreport', 'mod_surveypro', $a);
                            }
                        } else {
                            $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $paramurlbase);
                            $a = new stdClass();
                            $a->href = $url->out();
                            $a->reportname = get_string('pluginname', 'surveyproreport_'.$pluginname);
                            $messages[] = get_string('runreport', 'mod_surveypro', $a);
                        }
                    }
                }
            }
        }

        $this->display_messages($messages, get_string('reportsection', 'mod_surveypro'));
        $messages = array();
        // End of: report section.

        // Begin of: user templates section.
        if ($canmanageusertemplates) {
            $url = new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurlbase);
            $messages[] = get_string('manageusertemplates', 'mod_surveypro', $url->out());
        }

        if ($cansaveusertemplate) {
            $url = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurlbase);
            $messages[] = get_string('saveusertemplates', 'mod_surveypro', $url->out());
        }

        if ($canimportusertemplates) {
            $url = new moodle_url('/mod/surveypro/utemplates_import.php', $paramurlbase);
            $messages[] = get_string('importusertemplates', 'mod_surveypro', $url->out());
        }

        if ($canapplyusertemplates && (!$hassubmissions || $riskyediting)) {
            $url = new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applyusertemplates', 'mod_surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('utemplatessection', 'mod_surveypro'));
        $messages = array();
        // End of: user templates section.

        // Begin of: master templates section.
        if ($cansavemastertemplates) {
            $url = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurlbase);
            $messages[] = get_string('savemastertemplates', 'mod_surveypro', $url->out());
        }

        if ($canapplymastertemplates) {
            $url = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurlbase);
            $messages[] = get_string('applymastertemplates', 'mod_surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('mtemplatessection', 'mod_surveypro'));
        $messages = array();
        // End of: master templates section.
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
                print_error('invalid_status', 'mod_surveypro', null, $a);
            }
            $whereparams['status'] = $status;
        }

        return $DB->count_records('surveypro_submission', $whereparams);
    }
}
