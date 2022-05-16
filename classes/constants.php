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

namespace mod_surveypro;

/**
 * The class managing the page "cover" of the module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class constants {

    /**
     * TABS
     */
    const SURVEYPRO_TABLAYOUT      = 1;
    const SURVEYPRO_TABSUBMISSIONS = 2;
    const SURVEYPRO_TABUTEMPLATES  = 3;
    const SURVEYPRO_TABMTEMPLATES  = 4;
    const SURVEYPRO_TABREPORTS     = 5;

    /**
     * PAGES
     */
    // PAGES in tab LAYOUT.
    const SURVEYPRO_LAYOUT_PREVIEW   = 1;
    const SURVEYPRO_LAYOUT_ITEMS     = 2;
    const SURVEYPRO_LAYOUT_ITEMSETUP = 3;
    const SURVEYPRO_LAYOUT_VALIDATE  = 4;

    // PAGES in tab SUBMISSION.
    const SURVEYPRO_SUBMISSION_CPANEL   = 1;
    const SURVEYPRO_SUBMISSION_MANAGE   = 2;
    const SURVEYPRO_SUBMISSION_INSERT   = 3;
    const SURVEYPRO_SUBMISSION_EDIT     = 4;
    const SURVEYPRO_SUBMISSION_READONLY = 5;
    const SURVEYPRO_SUBMISSION_SEARCH   = 6;
    const SURVEYPRO_SUBMISSION_REPORT   = 7;
    const SURVEYPRO_SUBMISSION_IMPORT   = 8;
    const SURVEYPRO_SUBMISSION_EXPORT   = 9;

    // PAGES in tab USER TEMPLATES.
    const SURVEYPRO_UTEMPLATES_MANAGE = 1;
    const SURVEYPRO_UTEMPLATES_BUILD  = 2;
    const SURVEYPRO_UTEMPLATES_IMPORT = 3;
    const SURVEYPRO_UTEMPLATES_APPLY  = 4;

    // PAGES in tab MASTER TEMPLATES.
    const SURVEYPRO_MTEMPLATES_BUILD = 1;
    const SURVEYPRO_MTEMPLATES_APPLY = 2;

    // PAGES in tab REPORTS.
    // I can not define constants for report pages because they are a subplugin and can be integrated with additional reports.

    /**
     * ITEM TYPES
     */
    const SURVEYPRO_TYPEFIELD  = 'field';
    const SURVEYPRO_TYPEFORMAT = 'format';

    /**
     * KIND OF SUBMISSION
     */
    const SURVEYPRO_ONESHOTNOEMAIL     = 0;
    const SURVEYPRO_ONESHOTEMAIL       = 1;
    const SURVEYPRO_PAUSERESUMENOEMAIL = 2;
    const SURVEYPRO_PAUSERESUMEEMAIL   = 3;

    /**
     * ACTIONS
     */
    const SURVEYPRO_NOACTION = '0';

    /**
     * ACTIONS in LAYOUT MANAGEMENT page
     */
    const SURVEYPRO_CHANGEORDER   = '1';
    const SURVEYPRO_DELETEITEM    = '2';
    const SURVEYPRO_DROPMULTILANG = '3';
    const SURVEYPRO_CHANGEINDENT  = '4';
    const SURVEYPRO_HIDEITEM      = '5';
    const SURVEYPRO_SHOWITEM      = '6';
    const SURVEYPRO_MAKERESERVED  = '7';
    const SURVEYPRO_MAKEAVAILABLE = '8';

    /**
     * BULK ACTIONS in LAYOUT MANAGEMENT and in APPLY UTEMPLATE page
     */
    const SURVEYPRO_IGNOREITEMS        = '0';
    const SURVEYPRO_HIDEALLITEMS       = '13';
    const SURVEYPRO_SHOWALLITEMS       = '14';
    const SURVEYPRO_DELETEALLITEMS     = '15';
    const SURVEYPRO_DELETEVISIBLEITEMS = '16';
    const SURVEYPRO_DELETEHIDDENITEMS  = '17';

    // ACTIONS in RESPONSES section.
    const SURVEYPRO_DELETERESPONSE     = '18';
    const SURVEYPRO_DELETEALLRESPONSES = '19';
    const SURVEYPRO_DUPLICATERESPONSE  = '20';

    // ACTIONS in UTEMPLATE section.
    const SURVEYPRO_DELETEUTEMPLATE    = '21';
    const SURVEYPRO_EXPORTUTEMPLATE    = '22';

    /**
     * VIEW
     */
    // VIEW in USER FORM section.
    const SURVEYPRO_NOVIEW           = '0';
    const SURVEYPRO_NEWRESPONSE      = '1';
    const SURVEYPRO_EDITRESPONSE     = '2';
    const SURVEYPRO_READONLYRESPONSE = '3';

    // VIEW in ITEM section.
    const SURVEYPRO_EDITITEM         = '4';
    const SURVEYPRO_CHANGEORDERASK   = '5';

    // VIEW in RESPONSES section.
    const SURVEYPRO_RESPONSETOPDF    = '6';

    /**
     * Separators
     */
    const SURVEYPRO_VALUELABELSEPARATOR = '::';
    const SURVEYPRO_OTHERSEPARATOR      = '->';

    /**
     * OVERFLOW
     */
    const SURVEYPRO_LEFT_OVERFLOW  = -10;
    const SURVEYPRO_RIGHT_OVERFLOW = -20;

    /**
     * SENDERS
     */
    const SURVEYPRO_TAB   = 1;
    const SURVEYPRO_BLOCK = 2;

    /**
     * FEEDBACKMASK
     */
    const SURVEYPRO_NOFEEDBACK = 0;

    /**
     * ITEMPREFIX
     */
    const SURVEYPRO_ITEMPREFIX        = 'surveypro';
    const SURVEYPRO_PLACEHOLDERPREFIX = 'placeholder';
    const SURVEYPRO_DONTSAVEMEPREFIX  = 'placeholder';

    /**
     * INVITE, NO-ANSWER AND IGNOREME VALUE
     */
    const SURVEYPRO_INVITEVALUE     = '@@_INVITE_@@';
    const SURVEYPRO_NOANSWERVALUE   = '@@_NOANSW_@@';
    const SURVEYPRO_IGNOREMEVALUE   = '@@_IGNORE_@@';
    const SURVEYPRO_EXPNULLVALUE    = '@@_NULVAL_@@';
    const SURVEYPRO_IMPFORMATSUFFIX = '@@_FORMAT_@@';

    /**
     * ITEM ADJUSTMENTS
     */
    const SURVEYPRO_VERTICAL   = 0;
    const SURVEYPRO_HORIZONTAL = 1;

    /**
     * SURVEYPRO STATUS
     */
    const SURVEYPRO_STATUSCLOSED     = 0;
    const SURVEYPRO_STATUSINPROGRESS = 1;
    const SURVEYPRO_STATUSALL        = 2;

    /**
     * DOWNLOAD
     */
    const SURVEYPRO_DOWNLOADCSV = 1;
    const SURVEYPRO_DOWNLOADTSV = 2;
    const SURVEYPRO_DOWNLOADXLS = 3;
    const SURVEYPRO_FILESBYUSER = 4;
    const SURVEYPRO_FILESBYITEM = 5;
    const SURVEYPRO_NOFIELDSSELECTED  = 1;
    const SURVEYPRO_NORECORDSFOUND    = 2;
    const SURVEYPRO_NOATTACHMENTFOUND = 3;
    const SURVEYPRO_OWNERIDLABEL      = 'ownerid';
    const SURVEYPRO_TIMECREATEDLABEL  = 'timecreated';
    const SURVEYPRO_TIMEMODIFIEDLABEL = 'timemodified';

    /**
     * SEPARATORS
     */
    const SURVEYPRO_DBMULTICONTENTSEPARATOR     = ';';
    const SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR = '; ';

    /**
     * CONFIRMATION
     */
    const SURVEYPRO_UNCONFIRMED     = 0;
    const SURVEYPRO_CONFIRMED_YES   = 1;
    const SURVEYPRO_CONFIRMED_NO    = 2;
    const SURVEYPRO_ACTION_EXECUTED = 3;

    /**
     * DEFAULTVALUE OPTION
     */
    const SURVEYPRO_CUSTOMDEFAULT   = 1;
    const SURVEYPRO_INVITEDEFAULT   = 2;
    const SURVEYPRO_NOANSWERDEFAULT = 3;
    const SURVEYPRO_LIKELASTDEFAULT = 4;
    const SURVEYPRO_TIMENOWDEFAULT  = 5;

    /**
     * FILEAREAS
     */
    const SURVEYPRO_STYLEFILEAREA       = 'userstyle';
    const SURVEYPRO_TEMPLATEFILEAREA    = 'templatefilearea';
    const SURVEYPRO_THANKSPAGEFILEAREA  = 'thankshtml';
    const SURVEYPRO_ITEMCONTENTFILEAREA = 'itemcontent';

    /**
     * FIRENDLY FORMAT
     */
    const SURVEYPRO_FRIENDLYFORMAT = -1;

    /**
     * POSITION OF THE QUESTION CONTENT IN THE ITEM
     */
    const SURVEYPRO_POSITIONLEFT      = 0;
    const SURVEYPRO_POSITIONTOP       = 1;
    const SURVEYPRO_POSITIONFULLWIDTH = 2;

    /**
     * STATUS OF CONDITIONS OF RELATIONS
     */
    const SURVEYPRO_CONDITIONOK         = 0;
    const SURVEYPRO_CONDITIONNEVERMATCH = 1;
    const SURVEYPRO_CONDITIONMALFORMED  = 2;

    /**
     * SEMANTIC OF CONTENT RETURNED BY ITEMS
     */
    const SURVEYPRO_ITEMSRETURNSVALUES  = 0;
    const SURVEYPRO_ITEMRETURNSLABELS   = 1;
    const SURVEYPRO_ITEMRETURNSPOSITION = 2;

    /**
     * OUTPUT CONTENT
     */
    const SURVEYPRO_LABELS     = 'labels';
    const SURVEYPRO_VALUES     = 'values';
    const SURVEYPRO_POSITIONS  = 'positions';
    const SURVEYPRO_ITEMDRIVEN = 'itemdriven';

    /**
     * DUMMY CONTENT USED AT ANSWER SAVE TIME
     */
    const SURVEYPRO_DUMMYCONTENT = '__my_dummy_content@@';

    /**
     * OUTPUT OF FINAL SUBMISSION EVALUATION
     */
    const SURVEYPRO_VALIDRESPONSE     = 0;
    const SURVEYPRO_MISSINGMANDATORY  = 1;
    const SURVEYPRO_MISSINGVALIDATION = 2;

    /**
     * PDF TEMPLATES
     */
    const SURVEYPRO_2COLUMNSTEMPLATE = 2;
    const SURVEYPRO_3COLUMNSTEMPLATE = 3;

    /**
     * EXPORT CSV FILE STYLE
     */
    const SURVEYPRO_RAW     = 0;
    const SURVEYPRO_VERBOSE = 1;
}
