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
 * Surveypro tabs class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_layout;

/**
 * The class representing the tab-page structure on top of every page of the module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs {

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
     * @var array of boolean permissions to show in the module tab/pages tree
     */
    protected $tabpagesurl;

    /**
     * @var int Current tab requested by the user
     */
    protected $tabtab;

    /**
     * @var int Current page requested by the user
     */
    protected $tabpage;

    /**
     * @var bool True if this surveypro has submissions, false otherwise
     */
    protected $hassubmissions;

    /**
     * @var array Whole structure for tabs and corresponding pages
     */
    protected $tabs = array();

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     * @param int $tabtab
     * @param int $tabpage
     */
    public function __construct($cm, $context, $surveypro, $tabtab, $tabpage) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
        $this->tabtab = $tabtab;
        $this->tabpage = $tabpage;

        $utilitylayoutman = new utility_layout($cm, $surveypro);
        $this->hassubmissions = $utilitylayoutman->has_submissions();
        $this->tabpagesurl = $utilitylayoutman->get_common_links_url(SURVEYPRO_TAB);

        // Get the count of TABS.
        $tabcount = 0;
        foreach ($this->tabpagesurl as $area) {
            if ($area['container']) {
                $tabcount++;
            }
        }

        // Tabs definition.
        if ($tabcount == 1) {
            $this->tabs = array();
        } else {
            $this->get_tabs_structure();
        }

        // Pages definition.
        $this->get_pages_structure();
    }

    /**
     * Get tabs structure
     */
    private function get_tabs_structure() {
        $paramurl = ['id' => $this->cm->id];
        $row = array();

        // TAB LAYOUT.
        if ($elementurl = $this->tabpagesurl['tab_layout']['container']) {
            $tablayoutname = get_string('tablayoutname', 'mod_surveypro');
            $row[] = new \tabobject($tablayoutname, $elementurl->out(), $tablayoutname);
        }

        // TAB SUBMISSIONS.
        if ($elementurl = $this->tabpagesurl['tab_submissions']['container']) {
            $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');
            $row[] = new \tabobject($tabsubmissionsname, $elementurl->out(), $tabsubmissionsname);
        }

        // TAB USER TEMPLATES.
        if ($this->tabtab == SURVEYPRO_TABUTEMPLATES) {
            if ($this->tabpagesurl['tab_utemplate']['container']) {
                $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');
                $row[] = new \tabobject($tabutemplatename, null, $tabutemplatename);
            }
        }

        // TAB MASTER TEMPLATES.
        if ($this->tabtab == SURVEYPRO_TABMTEMPLATES) {
            if ($this->tabpagesurl['tab_mtemplate']['container']) {
                $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');
                $row[] = new \tabobject($tabmtemplatename, null, $tabmtemplatename);
            }
        }

        // TAB REPORTS.
        if ($this->tabtab == SURVEYPRO_TABREPORTS) {
            if ($this->tabpagesurl['tab_reports']['container']) {
                $tabreportsname = get_string('tabreportsname', 'mod_surveypro');
                $row[] = new \tabobject($tabreportsname, null, $tabreportsname);
            }
        }

        // Array of tabs. Closes the tab row element definition.
        // Next tabs element is going to define the pages row.
        $this->tabs[] = $row;
    }

    /**
     * Get pages structure
     */
    private function get_pages_structure() {
        global $DB;

        $row = array();
        $inactive = null;
        $activetwo = null;

        // echo '$tabpage = '.$tabpage.'<br />';
        $pageid = 'idpage'.$this->tabpage;
        // $pageid is here because I leave the door open to override it during next switch.

        // PAGES.
        switch ($this->tabtab) {
            case SURVEYPRO_TABLAYOUT:
                $tablayoutname = get_string('tablayoutname', 'mod_surveypro');

                $inactive = [$tablayoutname];
                $activetwo = [$tablayoutname];

                // Preview.
                if ($elementurl = $this->tabpagesurl['tab_layout']['preview']) {
                    $strlabel = get_string('tabitemspage1', 'mod_surveypro');
                    $row[] = new \tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Element management.
                if ($elementurl = $this->tabpagesurl['tab_layout']['manage']) {
                    $strlabel = get_string('tabitemspage2', 'mod_surveypro');
                    $row[] = new \tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Setup.
                if ($this->tabpage == SURVEYPRO_LAYOUT_ITEMSETUP) {
                    $strlabel = get_string('tabitemspage3', 'mod_surveypro');
                    $row[] = new \tabobject('idpage3', null, $strlabel);
                }

                // Validate.
                if ($elementurl = $this->tabpagesurl['tab_layout']['validate']) {
                    $strlabel = get_string('tabitemspage4', 'mod_surveypro');
                    $row[] = new \tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABSUBMISSIONS:
                $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');

                $inactive = [$tabsubmissionsname];
                $activetwo = [$tabsubmissionsname];

                // Dashboard.
                if ($elementurl = $this->tabpagesurl['tab_submissions']['cover']) {
                    $strlabel = get_string('tabsubmissionspage1', 'mod_surveypro');
                    $row[] = new \tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Responses.
                if ($elementurl = $this->tabpagesurl['tab_submissions']['responses']) {
                    $strlabel = get_string('tabsubmissionspage2', 'mod_surveypro');
                    $row[] = new \tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Insert.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_INSERT) {
                    $strlabel = get_string('tabsubmissionspage3', 'mod_surveypro');
                    $row[] = new \tabobject('idpage3', null, $strlabel);
                }

                // Edit.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_EDIT) {
                    $strlabel = get_string('tabsubmissionspage4', 'mod_surveypro');
                    $row[] = new \tabobject('idpage4', null, $strlabel);
                }

                // Read only.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_READONLY) {
                    $strlabel = get_string('tabsubmissionspage5', 'mod_surveypro');
                    $row[] = new \tabobject('idpage5', null, $strlabel);
                }

                // Search.
                if ($elementurl = $this->tabpagesurl['tab_submissions']['search']) {
                    $strlabel = get_string('tabsubmissionspage6', 'mod_surveypro');
                    $row[] = new \tabobject('idpage6', $elementurl->out(), $strlabel);
                }

                // Report.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_REPORT) {
                    if ($this->tabpagesurl['tab_submissions']['report']) {
                        $strlabel = get_string('tabsubmissionspage7', 'mod_surveypro');
                        $row[] = new \tabobject('idpage7', null, $strlabel);
                    }
                }

                // Import.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_IMPORT) {
                    if ($this->tabpagesurl['tab_submissions']['import']) {
                        $strlabel = get_string('tabsubmissionspage8', 'mod_surveypro');
                        $row[] = new \tabobject('idpage8', null, $strlabel);
                    }
                }

                // Export.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_EXPORT) {
                    if ($this->tabpagesurl['tab_submissions']['export']) {
                        $strlabel = get_string('tabsubmissionspage9', 'mod_surveypro');
                        $row[] = new \tabobject('idpage9', null, $strlabel);
                    }
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABUTEMPLATES:
                $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');

                $inactive = [$tabutemplatename];
                $activetwo = [$tabutemplatename];

                // Manage.
                if ($elementurl = $this->tabpagesurl['tab_utemplate']['manage']) {
                    $strlabel = get_string('tabutemplatepage1', 'mod_surveypro');
                    $row[] = new \tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Save.
                if ($elementurl = $this->tabpagesurl['tab_utemplate']['save']) {
                    $strlabel = get_string('tabutemplatepage2', 'mod_surveypro');
                    $row[] = new \tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Import.
                if ($elementurl = $this->tabpagesurl['tab_utemplate']['import']) {
                    $strlabel = get_string('tabutemplatepage3', 'mod_surveypro');
                    $row[] = new \tabobject('idpage3', $elementurl->out(), $strlabel);
                }

                // Apply.
                if ($elementurl = $this->tabpagesurl['tab_utemplate']['apply']) {
                    $strlabel = get_string('tabutemplatepage4', 'mod_surveypro');
                    $row[] = new \tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABMTEMPLATES:
                $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');

                $inactive = [$tabmtemplatename];
                $activetwo = [$tabmtemplatename];

                // Create.
                if ($elementurl = $this->tabpagesurl['tab_mtemplate']['save']) {
                    $strlabel = get_string('tabmtemplatepage1', 'mod_surveypro');
                    $row[] = new \tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Apply.
                if ($elementurl = $this->tabpagesurl['tab_mtemplate']['apply']) {
                    $strlabel = get_string('tabmtemplatepage2', 'mod_surveypro');
                    $row[] = new \tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABREPORTS:
                $surveyproreportlist = \core_component::get_plugin_list('surveyproreport');

                $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);
                $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
                $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);

                $tabreportsname = get_string('tabreportsname', 'mod_surveypro');

                $inactive = [$tabreportsname];
                $activetwo = [$tabreportsname];

                $counter = 0;
                foreach ($surveyproreportlist as $reportname => $reportpath) {
                    $classname = 'surveyproreport_'.$reportname.'\report';
                    $reportman = new $classname($this->cm, $this->context, $this->surveypro);

                    if (isset($this->tabpagesurl['tab_reports'][$reportname])) {
                        $elementurl = $this->tabpagesurl['tab_reports'][$reportname];
                        $strlabel = get_string('pluginname', 'surveyproreport_'.$reportname);
                        $row[] = new \tabobject('idpage'.$counter, $elementurl->out(), $strlabel);
                    }
                    $counter++;
                }

                $this->tabs[] = $row;

                break;
            default:
                throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }

        print_tabs($this->tabs, $pageid, $inactive, $activetwo);
    }
}
