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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class representing the tab-page structure on top of every page of the module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_tabs {

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
     * @var array of boolean permissions to show in the module tree
     */
    protected $isallowed;

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

        $utilityman = new mod_surveypro_utility($cm, $surveypro);
        $this->hassubmissions = $utilityman->has_submissions();
        $this->isallowed = $utilityman->get_admin_elements_visibility(SURVEYPRO_TAB);

        // Get the count of TABS.
        $tabcount = 0;
        foreach ($this->isallowed as $area) {
            if ($area['root']) {
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
        $paramurl = array('id' => $this->cm->id);
        $row = array();

        // TAB LAYOUT.
        if ($this->isallowed['tab_layout']['root']) {
            $tablayoutname = get_string('tablayoutname', 'mod_surveypro');
            $elementurl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
            $row[] = new tabobject($tablayoutname, $elementurl->out(), $tablayoutname);
        }

        // TAB SUBMISSIONS.
        if ($this->isallowed['tab_submissions']['root']) {
            $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');
            $localparamurl = array('id' => $this->cm->id, 'force' => 1);
            $elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
            $row[] = new tabobject($tabsubmissionsname, $elementurl->out(), $tabsubmissionsname);
        }

        // TAB USER TEMPLATES.
        if ($this->tabtab == SURVEYPRO_TABUTEMPLATES) {
            if ($this->isallowed['tab_utemplate']['root']) {
                $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');
                $elementurl = new moodle_url('/mod/surveypro/utemplate_save.php', $paramurl);
                $row[] = new tabobject($tabutemplatename, $elementurl->out(), $tabutemplatename);
            }
        }

        // TAB MASTER TEMPLATES.
        if ($this->tabtab == SURVEYPRO_TABMTEMPLATES) {
            if ($this->isallowed['tab_mtemplate']['root']) {
                $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');
                $elementurl = new moodle_url('/mod/surveypro/mtemplate_save.php', $paramurl);
                $row[] = new tabobject($tabmtemplatename, $elementurl->out(), $tabmtemplatename);
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

        $paramurl = array('id' => $this->cm->id);
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

                $inactive = array($tablayoutname);
                $activetwo = array($tablayoutname);

                // Preview.
                if ($this->isallowed['tab_layout']['preview']) {
                    $localparamurl = array('id' => $this->cm->id);
                    $elementurl = new moodle_url('/mod/surveypro/layout_preview.php', $localparamurl);
                    $strlabel = get_string('tabitemspage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Manage.
                if ($this->isallowed['tab_layout']['manage']) {
                    $elementurl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
                    $strlabel = get_string('tabitemspage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Setup.
                if ($this->tabpage == SURVEYPRO_LAYOUT_SETUP) {
                    if ($this->isallowed['tab_layout']['itemsetup']) {
                        $elementurl = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
                        $strlabel = get_string('tabitemspage3', 'mod_surveypro');
                        $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                    }
                }

                // Verify parent child relations.
                if ($this->isallowed['tab_layout']['validate']) {
                    $elementurl = new moodle_url('/mod/surveypro/layout_validation.php', $paramurl);
                    $strlabel = get_string('tabitemspage4', 'mod_surveypro');
                    $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABSUBMISSIONS:
                $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');

                $inactive = array($tabsubmissionsname);
                $activetwo = array($tabsubmissionsname);

                // Cover page.
                if ($this->isallowed['tab_submissions']['cover']) {
                    $elementurl = new moodle_url('/mod/surveypro/view_cover.php', $paramurl);
                    $strlabel = get_string('tabsubmissionspage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Responses.
                if ($this->isallowed['tab_submissions']['responses']) {
                    $localparamurl = array('id' => $this->cm->id, 'force' => 1);
                    $elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Insert.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_INSERT) {
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage3', 'mod_surveypro');
                    $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                }

                // Edit.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_EDIT) {
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_EDITRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage4', 'mod_surveypro');
                    $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                // Read only.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_READONLY) {
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_READONLYRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage5', 'mod_surveypro');
                    $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
                }

                // Search.
                if ($this->isallowed['tab_submissions']['search']) {
                    $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurl);
                    $strlabel = get_string('tabsubmissionspage6', 'mod_surveypro');
                    $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);
                }

                // Report.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_REPORT) {
                    if ($this->isallowed['tab_submissions']['report']) {
                        $elementurl = new moodle_url('/mod/surveypro/view_report.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage7', 'mod_surveypro');
                        $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
                    }
                }

                // Import.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_IMPORT) {
                    if ($this->isallowed['tab_submissions']['import']) {
                        $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage8', 'mod_surveypro');
                        $row[] = new tabobject('idpage8', $elementurl->out(), $strlabel);
                    }
                }

                // Export.
                if ($this->tabpage == SURVEYPRO_SUBMISSION_EXPORT) {
                    if ($this->isallowed['tab_submissions']['export']) {
                        $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage9', 'mod_surveypro');
                        $row[] = new tabobject('idpage9', $elementurl->out(), $strlabel);
                    }
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABUTEMPLATES:
                $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');

                $inactive = array($tabutemplatename);
                $activetwo = array($tabutemplatename);

                // Manage.
                if ($this->isallowed['tab_utemplate']['manage']) {
                    $elementurl = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurl);
                    $strlabel = get_string('tabutemplatepage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Create.
                if ($this->isallowed['tab_utemplate']['save']) {
                    $elementurl = new moodle_url('/mod/surveypro/utemplate_save.php', $paramurl);
                    $strlabel = get_string('tabutemplatepage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                // Import.
                if ($this->isallowed['tab_utemplate']['import']) {
                    $elementurl = new moodle_url('/mod/surveypro/utemplate_import.php', $paramurl);
                    $strlabel = get_string('tabutemplatepage3', 'mod_surveypro');
                    $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                }

                // Apply.
                if ($this->isallowed['tab_utemplate']['apply']) {
                    $elementurl = new moodle_url('/mod/surveypro/utemplate_apply.php', $paramurl);
                    $strlabel = get_string('tabutemplatepage4', 'mod_surveypro');
                    $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABMTEMPLATES:
                $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');

                $inactive = array($tabmtemplatename);
                $activetwo = array($tabmtemplatename);

                // Create.
                if ($this->isallowed['tab_mtemplate']['save']) {
                    $elementurl = new moodle_url('/mod/surveypro/mtemplate_save.php', $paramurl);
                    $strlabel = get_string('tabmtemplatepage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Apply.
                if ($this->isallowed['tab_mtemplate']['apply']) {
                    $elementurl = new moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurl);
                    $strlabel = get_string('tabmtemplatepage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            default:
                print_error('incorrectaccessdetected', 'mod_surveypro');
        }

        print_tabs($this->tabs, $pageid, $inactive, $activetwo);
    }
}
