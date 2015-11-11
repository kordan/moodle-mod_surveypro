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
 * Capability definitions for the surveypro module
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 *
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 *   [mod/block]/<plugin_name>:<capabilityname>
 *
 * component_name should be the same as the directory name of the mod or block.
 *
 * Core moodle capabilities are defined thus:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Examples: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *  Let's start with a summary:.
 *  It follows the list of TABS detailed with corresponding sub-tabs and php file name.
 *  For each sub-tab, I would define a capability at first but, I will find, sometimes it is useless.
 *
 *  -------------------------------------------
 *  TWO MODULE GENERAL CAPABILITIES
 *  -------------------------------------------
 *  mod/surveypro:addinstance
 *  mod/surveypro:view
 *
 *  -------------------------------------------
 *  TAB ELEMENTS
 *  -------------------------------------------
 *  SUB-TAB == SURVEYPRO_ITEMS_PREVIEW
 *      $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
 *      mod/surveypro:preview
 *
 *  SUB-TAB == SURVEYPRO_ITEMS_MANAGE
 *      $elementurl = new moodle_url('/mod/surveypro/items_manage.php', $localparamurl);
 *      mod/surveypro:manageitems
 *      mod/surveypro:additems
 *
 *  SUB-TAB == SURVEYPRO_ITEMS_SETUP
 *      $elementurl = new moodle_url('/mod/surveypro/items_setup.php', $localparamurl);
 *
 *  SUB-TAB == SURVEYPRO_ITEMS_VALIDATE
 *      $elementurl = new moodle_url('/mod/surveypro/items_validate.php', $localparamurl);
 *
 *  -------------------------------------------
 *  TAB SURVEYPRO
 *  -------------------------------------------
 *  SUB-TAB == SURVEYPRO_SUBMISSION_CPANEL
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_INSERT
 *      $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
 *      mod/surveypro:view
 *      mod/surveypro:accessadvanceditems
 *      mod/surveypro:submit
 *      mod/surveypro:ignoremaxentries
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_MANAGE
 *      $elementurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
 *
 *      mod/surveypro:alwaysseeowner
 *
 *      mod/surveypro:seeownsubmissions <-- It does not actually exist. It is always allowed.
 *      mod/surveypro:seeotherssubmissions
 *
 *      mod/surveypro:editownsubmissions
 *      mod/surveypro:editotherssubmissions
 *
 *      mod/surveypro:deleteownsubmissions
 *      mod/surveypro:deleteotherssubmissions
 *
 *      mod/surveypro:savesubmissiontopdf
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_EDIT
 *  SUB-TAB == SURVEYPRO_SUBMISSION_READONLY
 *      $elementurl = new moodle_url('/mod/surveypro/view_userform.php', $localparamurl);
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_SEARCH
 *      $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurl);
 *      mod/surveypro:searchsubmissions
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_REPORT
 *      $elementurl = new moodle_url('/mod/surveypro/view_report.php', $paramurl);
 *      mod/surveypro:accessreports
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_IMPORT
 *      $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurl);
 *      mod/surveypro:importdata
 *
 *  SUB-TAB == SURVEYPRO_SUBMISSION_EXPORT
 *      $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurl);
 *      mod/surveypro:exportdata
 *
 *  -------------------------------------------
 *  TAB USER TEMPLATES
 *  -------------------------------------------
 *  SUB-TAB == SURVEYPRO_UTEMPLATES_MANAGE
 *      $elementurl = new moodle_url('/mod/surveypro/utemplates_manage.php', $localparamurl);
 *      mod/surveypro:manageusertemplates
 *      mod/surveypro:deleteusertemplates
 *      mod/surveypro:downloadusertemplates
 *
 *  SUB-TAB == SURVEYPRO_UTEMPLATES_BUILD
 *      $elementurl = new moodle_url('/mod/surveypro/utemplates_create.php', $localparamurl);
 *      mod/surveypro:saveusertemplates @ CONTEXT_COURSE
 *
 *  SUB-TAB == SURVEYPRO_UTEMPLATES_IMPORT
 *      $elementurl = new moodle_url('/mod/surveypro/utemplates_import.php', $localparamurl);
 *      mod/surveypro:importusertemplates
 *
 *  SUB-TAB == SURVEYPRO_UTEMPLATES_APPLY
 *      $elementurl = new moodle_url('/mod/surveypro/utemplates_apply.php', $localparamurl);
 *      mod/surveypro:applyusertemplates
 *
 *  -------------------------------------------
 *  TAB MASTER TEMPLATES
 *  -------------------------------------------
 *  SUB-TAB == SURVEYPRO_MTEMPLATES_BUILD
 *      $elementurl = new moodle_url('/mod/surveypro/mtemplates_create.php', $localparamurl);
 *      mod/surveypro:savemastertemplates
 *
 *  SUB-TAB == SURVEYPRO_MTEMPLATES_APPLY
 *      $elementurl = new moodle_url('/mod/surveypro/mtemplates_apply.php', $localparamurl);
 *      mod/surveypro:applymastertemplates
 *
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'mod/surveypro:addinstance' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),

    'mod/surveypro:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'guest' => CAP_ALLOW,
            'frontpage' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:preview' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:accessadvanceditems' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:submit' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'frontpage' => CAP_ALLOW,
            'student' => CAP_ALLOW
        )
    ),

    'mod/surveypro:ignoremaxentries' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW
        )
    ),

    'mod/surveypro:alwaysseeowner' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:seeotherssubmissions' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:editownsubmissions' => array(
        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:editotherssubmissions' => array(
        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:deleteownsubmissions' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:deleteotherssubmissions' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:savesubmissiontopdf' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:searchsubmissions' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/surveypro:accessreports' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:accessownreports' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:importdata' => array(
        'riskbitmask' => RISK_CONFIG | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:exportdata' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:manageitems' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:additems' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:manageusertemplates' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:deleteusertemplates' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:downloadusertemplates' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:saveusertemplates' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:importusertemplates' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:applyusertemplates' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:savemastertemplates' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'mod/surveypro:applymastertemplates' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

);

