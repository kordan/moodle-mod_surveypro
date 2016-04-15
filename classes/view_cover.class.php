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
 * The covermanager class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

/**
 * The class managing the page "cover" of the module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_covermanager {

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
     * Display the overview page.
     *
     * @return void
     */
    public function display_cover() {
        global $CFG, $OUTPUT, $COURSE, $USER;

        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context, null, true);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context, null, true);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context, null, true);
        $cansaveusertemplate = has_capability('mod/surveypro:saveusertemplates', context_course::instance($COURSE->id), null, true);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context, null, true);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context, null, true);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context, null, true);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context, null, true);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, null, true);

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        $hassubmissions = $utilityman->has_submissions();
        $itemcount = $utilityman->has_input_items(0, true, $canmanageitems, $canaccessreserveditems);

        $messages = array();
        $timenow = time();

        // User submitted responses.
        $countclosed = $utilityman->has_submissions(true, SURVEYPRO_STATUSCLOSED, $USER->id);
        $inprogress = $utilityman->has_submissions(true, SURVEYPRO_STATUSINPROGRESS, $USER->id);
        $next = $countclosed + $inprogress + 1;

        // Begin of: the button to add one more surveypro.
        // Begin of: is the button to add one more surveypro going to be displayed?
        $roles = get_roles_used_in_context($this->context);
        $displaybutton = count(array_keys($roles));
        $displaybutton = $displaybutton && $cansubmit;
        $displaybutton = $displaybutton && $itemcount;
        if ($this->surveypro->timeopen) {
            $displaybutton = $displaybutton && ($this->surveypro->timeopen < $timenow);
        }
        if ($this->surveypro->timeclose) {
            $displaybutton = $displaybutton && ($this->surveypro->timeclose > $timenow);
        }
        if (!$canignoremaxentries) {
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

        // Number of elements.
        if ($itemcount) {
            $a = $itemcount;
            $message = get_string('count_allitems', 'mod_surveypro', $a);
            if ($canmanageitems) {
                // If I $canmanageitems in $itemcount items counted were: visible + hidden.
                $message .= ' ';
                $visibleonly = $utilityman->has_input_items(0, true, false, $canaccessreserveditems);
                $a = $itemcount - $visibleonly;
                $message .= get_string('count_hiddenitems', 'mod_surveypro', $a);
            }
            $messages[] = $message;
        }

        // Number of pages.
        $pagecount = $utilityman->assign_pages();
        if ($pagecount > 1) {
            $messages[] = get_string('count_pages', 'mod_surveypro', $pagecount);
        }

        if ($cansubmit) {
            if (!$canignoremaxentries) {
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
        }

        $this->display_messages($messages, get_string('attemptinfo', 'mod_surveypro'));
        $messages = array();
        // End of: general info.

        if ($displaybutton) {
            $url = new moodle_url('/mod/surveypro/view_form.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            echo $OUTPUT->box($OUTPUT->single_button($url, get_string('addnewsubmission', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
        } else {
            if (!$cansubmit) {
                $message = get_string('canneversubmit', 'mod_surveypro');
                echo $OUTPUT->notification($message, 'notifyproblem');
            } else if (($this->surveypro->timeopen) && ($this->surveypro->timeopen >= $timenow)) {
                $message = get_string('cannotsubmittooearly', 'mod_surveypro', userdate($this->surveypro->timeopen));
                echo $OUTPUT->notification($message, 'notifyproblem');
            } else if (($this->surveypro->timeclose) && ($this->surveypro->timeclose <= $timenow)) {
                $message = get_string('cannotsubmittoolate', 'mod_surveypro', userdate($this->surveypro->timeclose));
                echo $OUTPUT->notification($message, 'notifyproblem');
            } else if (($this->surveypro->maxentries > 0) && ($next >= $this->surveypro->maxentries)) {
                $message = get_string('nomoresubmissionsallowed', 'mod_surveypro', $this->surveypro->maxentries);
                echo $OUTPUT->notification($message, 'notifyproblem');
            } else if (!$itemcount) {
                $message = get_string('noitemsfound', 'mod_surveypro');
                echo $OUTPUT->notification($message, 'notifyproblem');
            }
        }
        // End of: the button to add one more surveypro.

        if (!$itemcount) {
            return;
        }

        // Begin of: report section.
        $surveyproreportlist = get_plugin_list('surveyproreport');
        $paramurlbase = array('id' => $this->cm->id);
        foreach ($surveyproreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/surveypro/report/'.$pluginname.'/classes/report.class.php');
            $classname = 'mod_surveypro_report_'.$pluginname;
            $reportman = new $classname($this->cm, $this->context, $this->surveypro);

            $allowedtemplates = $reportman->allowed_templates();

            if ((!$allowedtemplates) || in_array($this->surveypro->template, $allowedtemplates)) {
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
     * Display the generic message of the overview page.
     *
     * @param string $messages
     * @param string $strlegend
     * @return void
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
}
