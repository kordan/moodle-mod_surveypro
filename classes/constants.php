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
 * Surveypro user item utility class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The constants class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_constants {
    /**
     * Some constants
     */
    const VALUELABELSEPARATOR = '::';
    const OTHERSEPARATOR = '->';

    /**
     * TABS
     */
    const TABLAYOUT      = 1;
    const TABSUBMISSIONS = 2;
    const TABUTEMPLATES  = 3;
    const TABMTEMPLATES  = 4;

    /**
     * PAGES
     */
    // PAGES in tab LAYOUT.
    const LAYOUT_PREVIEW   = 1;
    const LAYOUT_ITEMS     = 2;
    const LAYOUT_ITEMSETUP = 3;
    const LAYOUT_VALIDATE  = 4;

    // PAGES in tab SUBMISSION.
    const SUBMISSION_CPANEL   = 1;
    const SUBMISSION_MANAGE   = 2;
    const SUBMISSION_INSERT   = 3;
    const SUBMISSION_EDIT     = 4;
    const SUBMISSION_READONLY = 5;
    const SUBMISSION_SEARCH   = 6;
    const SUBMISSION_REPORT   = 7;
    const SUBMISSION_IMPORT   = 8;
    const SUBMISSION_EXPORT   = 9;

    // PAGES in tab USER TEMPLATES.
    const UTEMPLATES_MANAGE = 1;
    const UTEMPLATES_BUILD  = 2;
    const UTEMPLATES_IMPORT = 3;
    const UTEMPLATES_APPLY  = 4;

    // PAGES in tab MASTER TEMPLATES.
    const MTEMPLATES_BUILD = 1;
    const MTEMPLATES_APPLY = 2;

    /**
     * ITEM TYPES
     */
    const TYPEFIELD = 'field';
    const TYPEFORMAT = 'format';

    /**
     * ACTIONS
     */
    const NOACTION           = '0';

    /**
     * ACTIONS in LAYOUT MANAGEMENT page
     */
    const CHANGEORDER        = '1';
    const DELETEITEM         = '2';
    const DROPMULTILANG      = '3';
    const CHANGEINDENT       = '4';
    const HIDEITEM           = '5';
    const SHOWITEM           = '6';
    const MAKERESERVED       = '7';
    const MAKEAVAILABLE      = '8';

    /**
     * BULK ACTIONS in LAYOUT MANAGEMENT and in APPLY UTEMPLATE page
     */
    const IGNOREITEMS        = '0';
    const HIDEALLITEMS       = '13';
    const SHOWALLITEMS       = '14';
    const DELETEALLITEMS     = '15';
    const DELETEVISIBLEITEMS = '16';
    const DELETEHIDDENITEMS  = '17';

    // ACTIONS in RESPONSES section.
    const DELETERESPONSE     = '18';
    const DELETEALLRESPONSES = '19';
    const DUPLICATERESPONSE  = '20';

    // ACTIONS in UTEMPLATE section.
    const DELETEUTEMPLATE    = '21';
    const EXPORTUTEMPLATE    = '22';

    /**
     * VIEW
     */
    // VIEW in USER FORM section.
    const NOVIEW           = '0';
    const NEWRESPONSE      = '1';
    const EDITRESPONSE     = '2';
    const READONLYRESPONSE = '3';

    // VIEW in ITEM section.
    const EDITITEM         = '4';
    const CHANGEORDERASK   = '5';

    // VIEW in RESPONSES section.
    const RESPONSETOPDF    = '6';

    /**
     * OVERFLOW
     */
    const LEFT_OVERFLOW  = -10;
    const RIGHT_OVERFLOW = -20;

    /**
     * SENDERS
     */
    const TAB   = 1;
    const BLOCK = 2;

    /**
     * FEEDBACKMASK
     */
    const NOFEEDBACK = 0;

    /**
     * ITEMPREFIX
     */
    const ITEMPREFIX        = 'surveypro';
    const PLACEHOLDERPREFIX = 'placeholder';
    const DONTSAVEMEPREFIX  = 'placeholder';

    /**
     * INVITE, NO-ANSWER AND IGNOREME VALUE
     */
    const INVITEVALUE     = '@@_INVITE_@@'; // User should never guess it.
    const NOANSWERVALUE   = '@@_NOANSW_@@'; // User should never guess it.
    const IGNOREMEVALUE   = '@@_IGNORE_@@'; // User should never guess it.
    const EXPNULLVALUE    = '@@_NULVAL_@@'; // User should never guess it.
    const IMPFORMATSUFFIX = '@@_FORMAT_@@'; // User should never guess it.

    /**
     * ITEM ADJUSTMENTS
     */
    const VERTICAL   = 0;
    const HORIZONTAL = 1;

    /**
     * SURVEYPRO STATUS
     */
    const STATUSCLOSED     = 0;
    const STATUSINPROGRESS = 1;
    const STATUSALL        = 2;

    /**
     * DOWNLOAD
     */
    const DOWNLOADCSV = 1;
    const DOWNLOADTSV = 2;
    const DOWNLOADXLS = 3;
    const FILESBYUSER = 4;
    const FILESBYITEM = 5;

    const NOFIELDSSELECTED  = 1;
    const NORECORDSFOUND    = 2;
    const NOATTACHMENTFOUND = 3;

    const OWNERIDLABEL      = 'ownerid';
    const TIMECREATEDLABEL  = 'timecreated';
    const TIMEMODIFIEDLABEL = 'timemodified';

    /**
     * SEPARATORS
     */
    const DBMULTICONTENTSEPARATOR     = ';';
    const OUTPUTMULTICONTENTSEPARATOR = '; ';

    /**
     * CONFIRMATION
     */
    const UNCONFIRMED     = 0;
    const CONFIRMED_YES   = 1;
    const CONFIRMED_NO    = 2;
    const ACTION_EXECUTED = 3;

    /**
     * DEFAULTVALUE OPTION
     */
    const CUSTOMDEFAULT   = 1;
    const INVITEDEFAULT   = 2;
    const NOANSWERDEFAULT = 3;
    const LIKELASTDEFAULT = 4;
    const TIMENOWDEFAULT  = 5;

    /**
     * FILEAREAS
     */
    const STYLEFILEAREA       = 'userstyle';
    const TEMPLATEFILEAREA    = 'templatefilearea';
    const THANKSPAGEFILEAREA  = 'thankshtml';
    const ITEMCONTENTFILEAREA = 'itemcontent';

    /**
     * FIRENDLY FORMAT
     */
    const FRIENDLYFORMAT = -1;

    /**
     * POSITION OF THE QUESTION CONTENT IN THE ITEM
     */
    const POSITIONLEFT      = 0;
    const POSITIONTOP       = 1;
    const POSITIONFULLWIDTH = 2;

    /**
     * STATUS OF CONDITIONS OF RELATIONS
     */
    const CONDITIONOK         = 0;
    const CONDITIONNEVERMATCH = 1;
    const CONDITIONMALFORMED  = 2;

    /**
     * SEMANTIC OF CONTENT RETURNED BY ITEMS
     */
    const ITEMSRETURNSVALUES  = 0;
    const ITEMRETURNSLABELS   = 1;
    const ITEMRETURNSPOSITION = 2;

    /**
     * OUTPUT CONTENT
     */
    const LABELS     = 'labels';
    const VALUES     = 'values';
    const POSITIONS  = 'positions';
    const ITEMDRIVEN = 'itemdriven';

    /**
     * DUMMY CONTENT USED AT ANSWER SAVE TIME
     */
    const DUMMYCONTENT = '__my_dummy_content@@';

    /**
     * OUTPUT OF FINAL SUBMISSION EVALUATION
     */
    const VALIDRESPONSE     = 0;
    const MISSINGMANDATORY  = 1;
    const MISSINGVALIDATION = 2;

    /**
     * PDF TEMPLATES
     */
    const TWOCOLUMNSTEMPLATE = 2;
    const THREECOLUMNSTEMPLATE = 3;

    /**
     * EXPORT CSV FILE STYLE
     */
    const RAW     = 0;
    const VERBOSE = 1;
}
