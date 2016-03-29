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

require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

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
     * @var int Current tab requested by the user
     */
    protected $modulettab;

    /**
     * @var int Current page requested by the user
     */
    protected $modulepage;

    /**
     * @var bool True if risky editing is on, false otherwise
     */
    protected $riskyediting;

    /**
     * @var bool True if this surveypro has submissions, false otherwise
     */
    protected $hassubmissions;

    /**
     * @var array Whole structure for tabs and corresponding pages
     */
    protected $tabs = array();

    /**
     * Class constructor
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     * @param int $moduletab
     * @param int $modulepage
     */
    public function __construct($cm, $context, $surveypro, $moduletab, $modulepage) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
        $this->moduletab = $moduletab;
        $this->modulepage = $modulepage;

        $this->riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $utilityman = new mod_surveypro_utility($cm, $surveypro);
        $this->hassubmissions = $utilityman->has_submissions();

        $this->get_tabs_structure();

        if (count($this->tabs[0]) == 1) {
            // Tabs row has only 1 tab. It is useless.
            // Forget what you did on $this->tabs and restart from scratch!
            $this->tabs = array();
        }

        $this->get_pages_structure();
    }

    /**
     * Get tabs structure
     */
    private function get_tabs_structure() {
        $paramurl = array('id' => $this->cm->id);
        $row = array();

        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);

        // TAB LAYOUT.
        $tablayoutname = get_string('tablayoutname', 'mod_surveypro');
        if ($canmanageitems) {
            $elementurl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
            $row[] = new tabobject($tablayoutname, $elementurl->out(), $tablayoutname);
        }

        // TAB SUBMISSIONS.
        $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');
        $localparamurl = array('id' => $this->cm->id, 'force' => 1);
        $elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
        $row[] = new tabobject($tabsubmissionsname, $elementurl->out(), $tabsubmissionsname);

        // TAB USER TEMPLATES.
        $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');
        if ($this->moduletab == SURVEYPRO_TABUTEMPLATES) {
            if (empty($this->surveypro->template)) {
                $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context, null, true);
                if ($canmanageusertemplates) {
                    $elementurl = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurl);
                    $row[] = new tabobject($tabutemplatename, $elementurl->out(), $tabutemplatename);
                }
            }
        }

        // TAB MASTER TEMPLATES.
        $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');
        if ($this->moduletab == SURVEYPRO_TABMTEMPLATES) {
            $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context, null, true);
            $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context, null, true);
            if ($cansavemastertemplates || ((!$this->hassubmissions || $this->riskyediting) && $canapplymastertemplates)) {
                $elementurl = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurl);
                $row[] = new tabobject($tabmtemplatename, $elementurl->out(), $tabmtemplatename);
            }
        }

        $this->tabs[] = $row; // Array of tabs. Closes the tab row element definition
                              // next tabs element is going to define the pages row
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

        // echo '$modulepage = '.$modulepage.'<br />';
        $pageid = 'idpage'.$this->modulepage;
        // $pageid is here because I leave the door open to override it during next switch

        // **********************************************
        // PAGES
        // **********************************************
        // echo '$this->moduletab = '.$this->moduletab.'<br />';
        // echo '$modulepage = '.$modulepage.'<br />';
        switch ($this->moduletab) {
            case SURVEYPRO_TABLAYOUT:
                $tablayoutname = get_string('tablayoutname', 'mod_surveypro');

                // Permissions used only locally.
                $canpreview = has_capability('mod/surveypro:preview', $this->context, null, true);
                $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);

                $inactive = array($tablayoutname);
                $activetwo = array($tablayoutname);

                if ($canpreview) {
                    // Preview.
                    $localparamurl = array('id' => $this->cm->id);
                    $elementurl = new moodle_url('/mod/surveypro/layout_preview.php', $localparamurl);
                    $strlabel = get_string('tabitemspage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                if ($canmanageitems) {
                    // Manage.
                    $elementurl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
                    $strlabel = get_string('tabitemspage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);

                    if (empty($this->surveypro->template)) {
                        // Setup.
                        if ($this->modulepage == SURVEYPRO_LAYOUT_SETUP) {
                            $elementurl = new moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
                            $strlabel = get_string('tabitemspage3', 'mod_surveypro');
                            $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                        }

                        // Verify parent child relations.
                        $whereparams = array('surveyproid' => $this->surveypro->id);
                        $parentscount = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);
                        if ($parentscount) {
                            $elementurl = new moodle_url('/mod/surveypro/layout_validation.php', $paramurl);
                            $strlabel = get_string('tabitemspage4', 'mod_surveypro');
                            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                        }
                    }
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABSUBMISSIONS:
                $tabsubmissionsname = get_string('tabsubmissionsname', 'mod_surveypro');

                $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);

                $canview = has_capability('mod/surveypro:view', $this->context, null, true);
                $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context, null, true);
                $cansearch = $cansearch && $utilityman->has_search_items();
                $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context, null, true);
                $canimportdata = has_capability('mod/surveypro:importdata', $this->context, null, true);
                $canexportdata = has_capability('mod/surveypro:exportdata', $this->context, null, true);

                $inactive = array($tabsubmissionsname);
                $activetwo = array($tabsubmissionsname);

                if ($canview) {
                    // Cover page.
                    $elementurl = new moodle_url('/mod/surveypro/view_cover.php', $paramurl);
                    $strlabel = get_string('tabsubmissionspage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                if (!is_guest($this->context)) {
                    // Responses.
                    $localparamurl = array('id' => $this->cm->id, 'force' => 1);
                    $elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_INSERT) {
                    // Insert.
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage3', 'mod_surveypro');
                    $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_EDIT) {
                    // Edit.
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_EDITRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage4', 'mod_surveypro'); // edit
                    $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_READONLY) {
                    // Read only.
                    $localparamurl = array('id' => $this->cm->id, 'view' => SURVEYPRO_READONLYRESPONSE);
                    $elementurl = new moodle_url('/mod/surveypro/view_form.php', $localparamurl);
                    $strlabel = get_string('tabsubmissionspage5', 'mod_surveypro'); // read only
                    $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
                }

                if ($cansearch) {
                    // Search.
                    $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurl);
                    $strlabel = get_string('tabsubmissionspage6', 'mod_surveypro');
                    $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_REPORT) {
                    // Report.
                    if ($canaccessreports) {
                        $elementurl = new moodle_url('/mod/surveypro/view_report.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage7', 'mod_surveypro');
                        $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
                    }
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_IMPORT) {
                    // Import.
                    if ($canimportdata) {
                        $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage8', 'mod_surveypro');
                        $row[] = new tabobject('idpage8', $elementurl->out(), $strlabel);
                    }
                }

                if ($this->modulepage == SURVEYPRO_SUBMISSION_EXPORT) {
                    // Export.
                    if ($canexportdata) {
                        $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurl);
                        $strlabel = get_string('tabsubmissionspage9', 'mod_surveypro');
                        $row[] = new tabobject('idpage9', $elementurl->out(), $strlabel);
                    }
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABUTEMPLATES:
                $tabutemplatename = get_string('tabutemplatename', 'mod_surveypro');

                // Permissions used only locally.
                $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $this->context, null, true);
                $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context, null, true);
                $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context, null, true);
                $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context, null, true);

                if (!empty($this->surveypro->template)) {
                    break;
                }

                $inactive = array($tabutemplatename);
                $activetwo = array($tabutemplatename);

                if ($canmanageusertemplates) {
                    // Manage.
                    $elementurl = new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl);
                    $strlabel = get_string('tabutemplatepage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

                    // Create.
                    if ($cansaveusertemplates) {
                        $elementurl = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurl);
                        $strlabel = get_string('tabutemplatepage2', 'mod_surveypro');
                        $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                    }

                    // Import.
                    if ($canimportusertemplates) {
                        $elementurl = new moodle_url('/mod/surveypro/utemplates_import.php', $paramurl);
                        $strlabel = get_string('tabutemplatepage3', 'mod_surveypro');
                        $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                    }

                    // Apply.
                    if ( (!$this->hassubmissions || $this->riskyediting) && $canapplyusertemplates ) {
                        $elementurl = new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurl);
                        $strlabel = get_string('tabutemplatepage4', 'mod_surveypro');
                        $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                    }
                }

                $this->tabs[] = $row;

                break;
            case SURVEYPRO_TABMTEMPLATES:
                $tabmtemplatename = get_string('tabmtemplatename', 'mod_surveypro');

                $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context, null, true);
                $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context, null, true);

                $inactive = array($tabmtemplatename);
                $activetwo = array($tabmtemplatename);

                // Create.
                if ($cansavemastertemplates) {
                    $elementurl = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurl);
                    $strlabel = get_string('tabmtemplatepage1', 'mod_surveypro');
                    $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
                }

                // Apply.
                // If there are submissions, do not allow the modification of the surveypro.
                if ( (!$this->hassubmissions || $this->riskyediting) && $canapplymastertemplates ) {
                    $elementurl = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurl);
                    $strlabel = get_string('tabmtemplatepage2', 'mod_surveypro');
                    $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
                }

                $this->tabs[] = $row;

                break;
            default:
                print_error('incorrectaccessdetected', 'mod_surveypro');
        }

        // echo '$tabs:';
        // var_dump($tabs);
        //
        // echo '$pageid:';
        // var_dump($pageid);
        //
        // echo '$inactive:';
        // var_dump($inactive);
        //
        // echo '$activetwo:';
        // var_dump($activetwo);

        print_tabs($this->tabs, $pageid, $inactive, $activetwo);
    }
}
