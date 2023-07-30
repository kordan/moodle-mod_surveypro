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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_layout;

/**
 * The class managing the page "cover" of the module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_cover {

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

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '..

        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);
        $cansubmit = has_capability('mod/surveypro:submit', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context);
        $cansaveusertemplate = has_capability('mod/surveypro:saveusertemplates', \context_course::instance($COURSE->id));
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context);
        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        $hassubmissions = $utilitylayoutman->has_submissions();
        $itemcount = $utilitylayoutman->has_items(0, SURVEYPRO_TYPEFIELD, $canmanageitems, $canaccessreserveditems, true);

        $messages = array();
        $timenow = time();

        // User submitted responses.
        $countclosed = $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSCLOSED, $USER->id);
        $inprogress = $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSINPROGRESS, $USER->id);
        $next = $countclosed + $inprogress + 1;

        // Begin of: is the button to add one more response going to be displayed?
        $addnew = $utilitylayoutman->is_newresponse_allowed($next);
        // End of: is the button to add one more response going to be displayed?

        if ($this->surveypro->intro) {
            echo $OUTPUT->box(format_module_intro('surveypro', $this->surveypro, $this->cm->id), 'generalbox description', 'intro');
        }

        // Number of elements.
        // If you can not manage items, you do not want to know their number.
        if ($itemcount && $canmanageitems) {
            $a = $itemcount;
            $message = get_string('count_allitems', 'mod_surveypro', $a);
            if ($canmanageitems) {
                // If I $canmanageitems in $itemcount items counted were: visible + hidden.
                $message .= ' ';
                $visibleonly = $utilitylayoutman->has_items(0, SURVEYPRO_TYPEFIELD, false, $canaccessreserveditems, true);
                $a = $itemcount - $visibleonly;
                $message .= get_string('count_hiddenitems', 'mod_surveypro', $a);
            }
            $messages[] = $message;
        }

        // Number of pages.
        $pagecount = $utilitylayoutman->assign_pages();
        if ($pagecount > 1) {
            $messages[] = get_string('count_pages', 'mod_surveypro', $pagecount);
        }

        if ($cansubmit) {
            if (empty($this->surveypro->maxentries) || $canignoremaxentries) {
                $maxentries = get_string('unlimited', 'mod_surveypro');
            } else {
                $maxentries = $this->surveypro->maxentries;
            }
            $messages[] = get_string('maxentries', 'mod_surveypro').$labelsep.$maxentries;

            // Your 'closed' responses.
            $a = new \stdClass();
            $a->status = get_string('statusclosed', 'mod_surveypro');
            $a->responsescount = $countclosed;
            $messages[] = get_string('yoursubmissions', 'mod_surveypro', $a);

            $pasuseresumesurvey = ($this->surveypro->pauseresume == SURVEYPRO_PAUSERESUMENOEMAIL);
            $pasuseresumesurvey = $pasuseresumesurvey || ($this->surveypro->pauseresume == SURVEYPRO_PAUSERESUMEEMAIL);
            if ($pasuseresumesurvey) {
                // Your 'in progress' responses.
                $a = new \stdClass();
                $a->status = get_string('statusinprogress', 'mod_surveypro');
                $a->responsescount = $inprogress;
                $messages[] = get_string('yoursubmissions', 'mod_surveypro', $a);
            }
        }

        $this->display_messages($messages, get_string('attemptinfo', 'mod_surveypro'));
        $messages = array();
        // End of: general info.

        if ($addnew) {
            $paramurl = ['id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE, 'begin' => 1];
            $url = new \moodle_url('/mod/surveypro/view_form.php', $paramurl);
            $message = get_string('addnewsubmission', 'mod_surveypro');
            echo $OUTPUT->box($OUTPUT->single_button($url, $message, 'get'), 'clearfix mdl-align');
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
                if ($inprogress) {
                    $a = new \stdClass();
                    $a->inprogress = get_string('statusinprogress', 'mod_surveypro');
                    $a->tabsubmissionspage2 = get_string('tabsubmissionspage2', 'mod_surveypro');
                    $message .= get_string('onlyfinalizationallowed', 'mod_surveypro', $a);
                } else {
                    $message .= '.';
                }
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
        $paramurlbase = ['id' => $this->cm->id];

        foreach ($surveyproreportlist as $reportname => $pluginpath) {
            $classname = 'surveyproreport_'.$reportname.'\report';
            $reportman = new $classname($this->cm, $this->context, $this->surveypro);

            if ($reportman->is_report_allowed($reportname)) {
                if ($childrenreports = $reportman->get_haschildrenreports()) {
                    $linklabel = get_string('pluginname', 'surveyproreport_'.$reportname);
                    $this->add_report_link($childrenreports, $reportname, $messages, $linklabel);
                } else {
                    $url = new \moodle_url('/mod/surveypro/report/'.$reportname.'/view.php', $paramurlbase);
                    $a = new \stdClass();
                    $a->href = $url->out();
                    $a->reportname = get_string('pluginname', 'surveyproreport_'.$reportname);
                    $messages[] = get_string('runreport', 'mod_surveypro', $a);
                }
            }
        }

        $this->display_messages($messages, get_string('reportsection', 'mod_surveypro'));
        $messages = array();
        // End of: report section.

        // Begin of: user templates section.
        if ($canmanageusertemplates) {
            $url = new \moodle_url('/mod/surveypro/utemplate_manage.php', $paramurlbase);
            $messages[] = get_string('manageusertemplates', 'mod_surveypro', $url->out());
        }

        if ($cansaveusertemplate) {
            $url = new \moodle_url('/mod/surveypro/utemplate_save.php', $paramurlbase);
            $messages[] = get_string('saveusertemplates', 'mod_surveypro', $url->out());
        }

        if ($canimportusertemplates) {
            $url = new \moodle_url('/mod/surveypro/utemplate_import.php', $paramurlbase);
            $messages[] = get_string('importusertemplates', 'mod_surveypro', $url->out());
        }

        if ($canapplyusertemplates && (!$hassubmissions || $riskyediting)) {
            $url = new \moodle_url('/mod/surveypro/utemplate_apply.php', $paramurlbase);
            $messages[] = get_string('applyusertemplates', 'mod_surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('utemplatessection', 'mod_surveypro'));
        $messages = array();
        // End of: user templates section.

        // Begin of: master templates section.
        if ($cansavemastertemplates) {
            $url = new \moodle_url('/mod/surveypro/mtemplate_save.php', $paramurlbase);
            $messages[] = get_string('savemastertemplates', 'mod_surveypro', $url->out());
        }

        if ($canapplymastertemplates) {
            $url = new \moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurlbase);
            $messages[] = get_string('applymastertemplates', 'mod_surveypro', $url->out());
        }

        $this->display_messages($messages, get_string('mtemplatessection', 'mod_surveypro'));
        $messages = array();
        // End of: master templates section.
    }

    /**
     * Recursive function to populate the $messages array for reports nested as much times as wanted
     *
     * Uncomment lines of get_haschildrenreports method in surveyproreport_colles\report class of report/colles/classes/report.php file
     * to see this function in action.
     *
     * @param string $childrenreports
     * @param string $pluginname
     * @param array $messages
     * @param string $categoryname
     * @return void
     */
    public function add_report_link($childrenreports, $pluginname, &$messages, $categoryname) {
        global $PAGE;

        foreach ($childrenreports as $reportkey => $childparams) {
            $subreport = get_string($reportkey, 'surveyprotemplate_'.$this->surveypro->template);
            if (is_array(reset($childparams))) { // If the first element of $childparams is an array.
                $categoryname .= ' > '.$subreport;
                $this->add_report_link($childparams, $pluginname, $messages, $categoryname);
            } else {
                $childparams = ['s' => $this->cm->instance] + $childparams;
                $url = new \moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $childparams);
                $a = new \stdClass();
                $a->href = $url->out();
                $a->reportname = $categoryname.' > '.$subreport;
                $messages[] = get_string('runreport', 'mod_surveypro', $a);
            }
        }
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
            echo \html_writer::start_tag('fieldset', ['class' => 'generalbox']);
            echo \html_writer::start_tag('legend', ['class' => 'coverinfolegend']);
            echo $strlegend;
            echo \html_writer::end_tag('legend');
            foreach ($messages as $message) {
                echo $OUTPUT->container($message, 'mdl-left');
            }
            echo \html_writer::end_tag('fieldset');
        }
    }
}
