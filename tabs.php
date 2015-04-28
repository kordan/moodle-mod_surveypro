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

// do not prevent direct user input
// prevention is done in each working page according to actions
$riskyediting = ($surveypro->riskyeditdeadline > time());

$hassubmissions = surveypro_count_submissions($surveypro->id);
$context = context_module::instance($cm->id);

$cansubmit = has_capability('mod/surveypro:submit', $context, null, true);
$canmanageitems = has_capability('mod/surveypro:manageitems', $context, null, true);
$canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $context, null, true);

$cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $context, null, true);
$canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $context, null, true);

$whereparams = array('surveyproid' => $surveypro->id);
$countparents = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);

$inactive = null;
$activetwo = null;

// **********************************************
// TABS
// **********************************************
$paramurl = array('id' => $cm->id);

// ==> tab row definition
$row = array();

// ----------------------------------------
// TAB ITEMS
// ----------------------------------------
$tabitemsname = get_string('tabitemsname', 'surveypro');
if ($canmanageitems) {
    $elementurl = new moodle_url('/mod/surveypro/items_manage.php', $paramurl);
    $row[] = new tabobject($tabitemsname, $elementurl->out(), $tabitemsname);
}

// ----------------------------------------
// TAB SUBMISSIONS
// ----------------------------------------
$tabsubmissionsname = get_string('tabsubmissionsname', 'surveypro');
$localparamurl = array('id' => $cm->id, 'cover' => 0);
$elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
$row[] = new tabobject($tabsubmissionsname, $elementurl->out(), $tabsubmissionsname);

// ----------------------------------------
// TAB USER TEMPLATES
// ----------------------------------------
$tabutemplatename = get_string('tabutemplatename', 'surveypro');
if (empty($surveypro->template)) {
    if ($moduletab == SURVEYPRO_TABUTEMPLATES) {
        if ($canmanageusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurl);
            $row[] = new tabobject($tabutemplatename, $elementurl->out(), $tabutemplatename);
        }
    }
}

// ----------------------------------------
// TAB MASTER TEMPLATES
// ----------------------------------------
$tabmtemplatename = get_string('tabmtemplatename', 'surveypro');
if ($moduletab == SURVEYPRO_TABMTEMPLATES) {
    if ($cansavemastertemplates || ((!$hassubmissions || $riskyediting) && $canapplymastertemplates)) {
        $elementurl = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurl);
        $row[] = new tabobject($tabmtemplatename, $elementurl->out(), $tabmtemplatename);
    }
}

// ----------------------------------------
// ==> tab row definition
// ----------------------------------------
$tabs = array();
$tabs[] = $row; // Array of tabs. Closes the tab row element definition
                // next tabs element is going to define the pages row

// echo '$modulepage = '.$modulepage.'<br />';
$pageid = 'idpage'.$modulepage;
// $pageid is here because I leave the door open to override it during next switch

// **********************************************
// PAGES
// **********************************************
// echo '$moduletab = '.$moduletab.'<br />';
// echo '$modulepage = '.$modulepage.'<br />';
switch ($moduletab) {
    case SURVEYPRO_TABITEMS:
        // permissions
        $canpreview = has_capability('mod/surveypro:preview', $context, null, true);

        $inactive = array($tabitemsname);
        $activetwo = array($tabitemsname);

        $row = array();
        if ($canpreview) { // preview
            $localparamurl = array('id' => $cm->id, 'view' => SURVEYPRO_PREVIEWSURVEYFORM);
            $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
            $strlabel = get_string('tabitemspage1', 'surveypro');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        if ($canmanageitems) { // manage
            $elementurl = new moodle_url('/mod/surveypro/items_manage.php', $paramurl);
            $strlabel = get_string('tabitemspage2', 'surveypro');
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);

            if (empty($surveypro->template)) {
                if ($modulepage == SURVEYPRO_ITEMS_SETUP) { // setup
                    $elementurl = new moodle_url('/mod/surveypro/items_setup.php', $paramurl);
                    $strlabel = get_string('tabitemspage3', 'surveypro');
                    $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
                }

                if ($countparents) { // verify parent child relations
                    $elementurl = new moodle_url('/mod/surveypro/items_validate.php', $paramurl);
                    $strlabel = get_string('tabitemspage4', 'surveypro');
                    $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
                }
            }
        }

        $tabs[] = $row;

        break;
    case SURVEYPRO_TABSUBMISSIONS:
        // permissions
        $canview = has_capability('mod/surveypro:view', $context, null, true);
        $cansearch = has_capability('mod/surveypro:searchsubmissions', $context, null, true);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $context, null, true);
        $canimportdata = has_capability('mod/surveypro:importdata', $context, null, true);
        $canexportdata = has_capability('mod/surveypro:exportdata', $context, null, true);

        $inactive = array($tabsubmissionsname);
        $activetwo = array($tabsubmissionsname);

        $row = array();

         // Cover page
        if ($canview) {
            $elementurl = new moodle_url('/mod/surveypro/view_cover.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage1', 'surveypro');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        // Responses
        if (!is_guest($context)) {
            $localparamurl = array('id' => $cm->id, 'cover' => 0);
            $elementurl = new moodle_url('/mod/surveypro/view.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage2', 'surveypro');
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }

        // Insert
        if ($modulepage == SURVEYPRO_SUBMISSION_INSERT) {
            $localparamurl = array('id' => $cm->id, 'view' => SURVEYPRO_NEWRESPONSE);
            $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage3', 'surveypro');
            $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
        }

        if ($modulepage == SURVEYPRO_SUBMISSION_EDIT) { // edit
            $localparamurl = array('id' => $cm->id, 'view' => SURVEYPRO_EDITRESPONSE);
            $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage4', 'surveypro'); // edit
            $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
        }

        if ($modulepage == SURVEYPRO_SUBMISSION_READONLY) { // read only
            $localparamurl = array('id' => $cm->id, 'view' => SURVEYPRO_READONLYRESPONSE);
            $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
            $strlabel = get_string('tabsubmissionspage5', 'surveypro'); // read only
            $row[] = new tabobject('idpage5', $elementurl->out(), $strlabel);
        }

        if ($cansearch) { // search
            $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurl);
            $strlabel = get_string('tabsubmissionspage6', 'surveypro');
            $row[] = new tabobject('idpage6', $elementurl->out(), $strlabel);
        }

        if ($modulepage == SURVEYPRO_SUBMISSION_REPORT) { // report
            if ($canaccessreports) {
                $elementurl = new moodle_url('/mod/surveypro/view_report.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage7', 'surveypro');
                $row[] = new tabobject('idpage7', $elementurl->out(), $strlabel);
            }
        }

        if ($modulepage == SURVEYPRO_SUBMISSION_IMPORT) { // import
            if ($canimportdata) { // import
                $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage8', 'surveypro');
                $row[] = new tabobject('idpage8', $elementurl->out(), $strlabel);
            }
        }

        if ($modulepage == SURVEYPRO_SUBMISSION_EXPORT) { // export
            if ($canexportdata) { // export
                $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurl);
                $strlabel = get_string('tabsubmissionspage9', 'surveypro');
                $row[] = new tabobject('idpage9', $elementurl->out(), $strlabel);
            }
        }

        $tabs[] = $row;

        break;
    case SURVEYPRO_TABUTEMPLATES:
        // permissions
        $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $context, null, true);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $context, null, true);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $context, null, true);

        if (!empty($surveypro->template)) {
            break;
        }

        $inactive = array($tabutemplatename);
        $activetwo = array($tabutemplatename);

        if ($canmanageusertemplates) {
            $row = array();
            $elementurl = new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl); // manage
            $strlabel = get_string('tabutemplatepage1', 'surveypro');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);

            if ($cansaveusertemplates) { // create
                $elementurl = new moodle_url('/mod/surveypro/utemplates_create.php', $paramurl);
                $strlabel = get_string('tabutemplatepage2', 'surveypro');
                $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
            }

            if ($canimportusertemplates) { // import
                $elementurl = new moodle_url('/mod/surveypro/utemplates_import.php', $paramurl);
                $strlabel = get_string('tabutemplatepage3', 'surveypro');
                $row[] = new tabobject('idpage3', $elementurl->out(), $strlabel);
            }

            if ( (!$hassubmissions || $riskyediting) && $canapplyusertemplates ) {
                $elementurl = new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurl); // apply
                $strlabel = get_string('tabutemplatepage4', 'surveypro');
                $row[] = new tabobject('idpage4', $elementurl->out(), $strlabel);
            }
        }

        $tabs[] = $row;

        break;
    case SURVEYPRO_TABMTEMPLATES:
        $inactive = array($tabmtemplatename);
        $activetwo = array($tabmtemplatename);

        $row = array();
        if ($cansavemastertemplates) { // create
            $elementurl = new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurl);
            $strlabel = get_string('tabmtemplatepage1', 'surveypro');
            $row[] = new tabobject('idpage1', $elementurl->out(), $strlabel);
        }

        if ( (!$hassubmissions || $riskyediting) && $canapplymastertemplates ) { // if submissions were done, do not change the list of fields
            $elementurl = new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurl); // apply
            $strlabel = get_string('tabmtemplatepage2', 'surveypro');
            $row[] = new tabobject('idpage2', $elementurl->out(), $strlabel);
        }
        $tabs[] = $row;

        break;
    default:
        print_error('incorrectaccessdetected', 'surveypro');
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

print_tabs($tabs, $pageid, $inactive, $activetwo);
