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

namespace mod_surveypro\output;

use moodle_url;
use url_select;
use mod_surveypro\utility_layout;
use action_link;

/**
 * Class responsible for generating the action bar elements in the surveypro module pages.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_bar {

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
     * @var moodle_url $currenturl The URL of the current page
     */
    private $currenturl;

    /**
     * @var moodle_url $hostingpage The page going to host the menu.
     */
    private $hostingpage;

    /**
     * The class constructor.
     *
     * @param object $cm
     * @param \context_module $context the context of the surveypro.
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        global $PAGE;

        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        $this->currenturl = $PAGE->url;
    }

    /**
     * Generate the output for the action selector in the view page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_view_action_bar(): string {
        global $PAGE;

        $pageparams = $PAGE->url->params();

        $canview = has_capability('mod/surveypro:view', $this->context);
        $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);

        $paramurl = ['s' => $this->surveypro->id, 'area' => 'surveypro'];

        // View -> cover.
        if ($canview) {
            $paramurl['section'] = 'cover';
            $linktocover = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $menu[$linktocover->out(false)] = get_string('surveypro_dashboard', 'mod_surveypro');
        }

        // View -> submissionslist.
        if (!is_guest($this->context)) {
            $paramurl['section'] = 'submissionslist';
            $linktosubmissions = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $menu[$linktosubmissions->out(false)] = get_string('surveypro_responses', 'mod_surveypro');
        }

        // View -> searchsubmissions.
        if ($cansearch && $utilitylayoutman->has_search_items()) {
            $paramurl['section'] = 'searchsubmissions';
            $linktosearch = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $menu[$linktosearch->out(false)] = get_string('surveypro_view_search', 'mod_surveypro');
        }

        // Gosth links.
        if (isset($pageparams['mode'])) {
            // View -> insert.
            if ($pageparams['mode'] == SURVEYPRO_NEWRESPONSEMODE) {
                // Optional gosth item.
                $gosthlink = new moodle_url('/mod/surveypro/view.php'); // URL is useless. It can't be used.
                $menu[$gosthlink->out(false)] = get_string('surveypro_insert', 'mod_surveypro');
            }
            // View -> edit submission.
            if ($pageparams['mode'] == SURVEYPRO_EDITMODE) {
                // Optional gosth item.
                $gosthlink = new moodle_url('/mod/surveypro/view.php'); // URL is useless. It can't be used.
                $menu[$gosthlink->out(false)] = get_string('surveypro_edit', 'mod_surveypro');
            }
            // View -> read only.
            if ($pageparams['mode'] == SURVEYPRO_READONLYMODE) {
                // Optional gosth item.
                $gosthlink = new moodle_url('/mod/surveypro/view.php'); // URL is useless. It can't be used.
                $menu[$gosthlink->out(false)] = get_string('surveypro_readonly', 'mod_surveypro');
            }
        }

        // If section = 'submissionform', set $activeurl according to the way the form is going to be used.
        if (strpos($this->currenturl->out(false), 'submissionform')) {
            if (isset($gosthlink)) {
                $activeurl = $gosthlink;
            } else {
                $activeurl = $linktosubmissions;
            }
        } else {
            $activeurl = $this->currenturl;
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the layout page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_layout_action_bar(): string {
        global $PAGE, $DB;

        $pageparams = $PAGE->url->params();

        $canpreview = has_capability('mod/surveypro:preview', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);

        $whereparams = ['surveyproid' => $this->surveypro->id, 'parentid' => 0];
        $wheresql = 'surveyproid = :surveyproid AND parentid <> :parentid';
        $countparents = $DB->count_records_select('surveypro_item', $wheresql, $whereparams);

        $paramurl = ['s' => $this->surveypro->id, 'area' => 'layout'];

        // Layout -> itemslist.
        if ($canmanageitems) {
            $paramurl['section'] = 'itemslist';
            $linktoitemslist = new moodle_url('/mod/surveypro/layout.php', $paramurl);
            $menu[$linktoitemslist->out(false)] = get_string('layout_items', 'mod_surveypro');
        }

        // Gosth links.
        if ($canmanageitems) {
            if (isset($pageparams['mode'])) {
                // Layout -> itemsetup.
                if ($pageparams['mode'] == SURVEYPRO_NEWITEM) {
                    $gosthlink = new moodle_url('/mod/surveypro/layout.php'); // URL is useless. It can't be used.
                    $menu[$gosthlink->out(false)] = get_string('layout_itemsetup', 'mod_surveypro');
                }
                // Layout -> edit item.
                if ($pageparams['mode'] == SURVEYPRO_EDITITEM) {
                    // Optional gosth item.
                    $gosthlink = new moodle_url('/mod/surveypro/layout.php'); // URL is useless. It can't be used.
                    $menu[$gosthlink->out(false)] = get_string('layout_edititem', 'mod_surveypro');
                }
            }
        }

        // Layout -> branchingvalidation.
        if ($canmanageitems && empty($this->surveypro->template) && $countparents) {
            $paramurl['section'] = 'branchingvalidation';
            $linktobranchingvalidation = new moodle_url('/mod/surveypro/layout.php', $paramurl);
            $menu[$linktobranchingvalidation->out(false)] = get_string('layout_branchingvalidation', 'mod_surveypro');
        }

        // Layout -> preview.
        if ($canpreview) {
            $paramurl['section'] = 'preview';
            $linktopreview = new moodle_url('/mod/surveypro/layout.php', $paramurl);
            $menu[$linktopreview->out(false)] = get_string('layout_preview', 'mod_surveypro');
        }

        $activeurl = $this->currenturl;

        if (isset($gosthlink)) {
            $activeurl = $gosthlink;
        } else {
            // Select the menu item according to section.
            if (strpos($this->currenturl->out(false), 'itemslist')) {
                $activeurl = $linktoitemslist;
            }
            if (strpos($this->currenturl->out(false), 'branchingvalidation') && isset($linktobranchingvalidation)) {
                $activeurl = $linktobranchingvalidation;
            }
            if (strpos($this->currenturl->out(false), 'preview')) {
                $activeurl = $linktopreview;
            }
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the tools page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_tools_action_bar(): string {
        global $PAGE, $DB;

        $canimportresponses = has_capability('mod/surveypro:importresponses', $this->context);
        $canexportresponses = has_capability('mod/surveypro:exportresponses', $this->context);

        $paramurl = ['s' => $this->surveypro->id, 'area' => 'tools'];

        // Begin of definition for urlselect.
        // Tools -> import.
        if ($canimportresponses) {
            $paramurl['section'] = 'import';
            $linktoimport = new moodle_url('/mod/surveypro/tools.php', $paramurl);
            $menu[$linktoimport->out(false)] = get_string('tools_import', 'mod_surveypro');
        }

        // Tools -> export.
        if ($canexportresponses) {
            $paramurl['section'] = 'export';
            $linktoexport = new moodle_url('/mod/surveypro/tools.php', $paramurl);
            $menu[$linktoexport->out(false)] = get_string('tools_export', 'mod_surveypro');
        }

        $activeurl = $this->currenturl;

        // Select the menu item according to section.
        if (strpos($this->currenturl->out(false), 'import')) {
            $activeurl = $linktoimport;
        }
        if (strpos($this->currenturl->out(false), 'export')) {
            $activeurl = $linktoexport;
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        // End of definition for urlselect.

        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the user templates page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_utemplates_action_bar(): string {
        global $PAGE, $DB;

        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context);
        $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $this->context);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $paramurl = ['s' => $this->surveypro->id, 'area' => 'utemplates'];

        // Begin of definition for urlselect.
        // Utemplates -> manage.
        if ($canmanageusertemplates) {
            $paramurl['section'] = 'manage';
            $linktomanage = new moodle_url('/mod/surveypro/utemplates.php', $paramurl);
            $menu[$linktomanage->out(false)] = get_string('utemplate_manage', 'mod_surveypro');
        }

        // Utemplates -> save.
        if ($cansaveusertemplates) {
            $paramurl['section'] = 'save';
            $linktosave = new moodle_url('/mod/surveypro/utemplates.php', $paramurl);
            $menu[$linktosave->out(false)] = get_string('utemplate_save', 'mod_surveypro');
        }

        // Utemplates -> import.
        if ($canimportusertemplates) {
            $paramurl['section'] = 'import';
            $linktoimport = new moodle_url('/mod/surveypro/utemplates.php', $paramurl);
            $menu[$linktoimport->out(false)] = get_string('utemplate_import', 'mod_surveypro');
        }

        // Utemplates -> apply.
        if ($canapplyusertemplates && (!$hassubmissions || $riskyediting)) {
            $paramurl['section'] = 'apply';
            $linktoapply = new moodle_url('/mod/surveypro/utemplates.php', $paramurl);
            $menu[$linktoapply->out(false)] = get_string('utemplate_apply', 'mod_surveypro');
        }

        $activeurl = $this->currenturl;

        // Select the menu item according to section.
        if (strpos($this->currenturl->out(false), 'manage')) {
            $activeurl = $linktomanage;
        }
        if (strpos($this->currenturl->out(false), 'save')) {
            $activeurl = $linktosave;
        }
        if (strpos($this->currenturl->out(false), 'import')) {
            $activeurl = $linktoimport;
        }
        if (strpos($this->currenturl->out(false), 'apply') && isset($linktoapply)) {
            $activeurl = $linktoapply;
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        // End of definition for urlselect.

        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the master templates page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_mtemplates_action_bar(): string {
        global $PAGE;

        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $paramurl = ['s' => $this->surveypro->id, 'area' => 'mtemplates'];

        // Begin of definition for urlselect.
        // Mtemplates -> save.
        if ($cansavemastertemplates && empty($this->surveypro->template)) {
            $paramurl['section'] = 'save';
            $linktosave = new moodle_url('/mod/surveypro/mtemplates.php', $paramurl);
            $menu[$linktosave->out(false)] = get_string('mtemplate_save', 'mod_surveypro');
        }

        // Mtemplates -> apply.
        if ($canapplymastertemplates && (!$hassubmissions || $riskyediting)) {
            $paramurl['section'] = 'apply';
            $linktoapply = new moodle_url('/mod/surveypro/mtemplates.php', $paramurl);
            $menu[$linktoapply->out(false)] = get_string('mtemplate_apply', 'mod_surveypro');
        }

        $activeurl = $this->currenturl;

        // Select the menu item according to section.
        if (strpos($this->currenturl->out(false), 'save') && isset($linktosave)) {
            $activeurl = $linktosave;
        }
        if (strpos($this->currenturl->out(false), 'apply') && isset($linktoapply)) {
            $activeurl = $linktoapply;
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        // End of definition for urlselect.

        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }

    /**
     * Generate the output for the action selector in the master templates page.
     *
     * @return string The HTML code for the action selector.
     */
    public function draw_reports_action_bar(): string {
        global $PAGE;

        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);
        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);

        if ($surveyproreportlist = get_plugin_list('surveyproreport')) {
            foreach ($surveyproreportlist as $reportname => $reportpath) {
                $classname = 'surveyproreport_'.$reportname.'\report';
                $reportman = new $classname($this->cm, $this->context, $this->surveypro);
                $reportman->setup();
                if ($reportman->is_report_allowed()) {
                    $paramurl = ['s' => $this->cm->instance, 'area' => 'reports', 'section' => 'view', 'report' => $reportname];
                    $linktoreport = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
                    $menu[$linktoreport->out(false)] = get_string('pluginname', 'surveyproreport_'.$reportname);
                }
            }
        }

        $pageparams = $PAGE->url->params();

        // Select the menu item according to the currenturl.
        $paramurl = ['s' => $this->surveypro->id, 'area' => 'reports', 'section' => 'view'];
        if (isset($pageparams['report'])) {
            $paramurl['report'] = $pageparams['report'];
            $activeurl = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
        } else {
            $report = reset($surveyproreportlist);
            $paramurl['report'] = $report;
            $activeurl = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
        }

        $urlselect = new url_select($menu, $activeurl->out(false), null, 'viewactionselect');
        // End of definition for urlselect.

        $viewactionbar = new view_action_bar($this->surveypro->id, $urlselect);

        $renderer = $PAGE->get_renderer('mod_surveypro');

        return $renderer->render_view_action_bar($viewactionbar);
    }
}
