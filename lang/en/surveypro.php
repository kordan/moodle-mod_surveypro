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
 * English strings for surveypro
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Surveypro';
$string['modulename_help'] = 'Surveypro allows the creation of custom surveys as far as built in surveys like ATTLS, COLLES and CRITICAL INCIDENTS. You can also save and reuse parts or whole of your own custom survey.';
$string['modulename_link'] = 'mod/surveypro/view';
$string['modulenameplural'] = 'surveys';
$string['surveyproname'] = 'Surveypro name';
$string['surveyproname_help'] = 'Choose the name of this surveypro.';
$string['surveypro'] = 'survey';
$string['pluginadministration'] = 'Surveypro administration';
$string['pluginname'] = 'Surveypro';

$string['tabitemsname'] = 'Layout';
    $string['tabitemspage1'] = 'Preview';
    $string['tabitemspage2'] = 'Elements';
    $string['tabitemspage3'] = 'Element setup';
    $string['tabitemspage4'] = 'Branching validation';
$string['tabsubmissionsname'] = 'Survey';
    $string['tabsubmissionspage1'] = 'Dashboard'; // 'Overview';
    $string['tabsubmissionspage2'] = 'Responses';
    $string['tabsubmissionspage3'] = 'Insert';
    $string['tabsubmissionspage4'] = 'Edit';
    $string['tabsubmissionspage5'] = 'Read only';
    $string['tabsubmissionspage6'] = 'Search';
    $string['tabsubmissionspage7'] = 'Reports';
    $string['tabsubmissionspage8'] = 'Import';
    $string['tabsubmissionspage9'] = 'Export';
$string['tabutemplatename'] = 'User templates';
    $string['tabutemplatepage1'] = 'Manage';
    $string['tabutemplatepage2'] = 'Save';
    $string['tabutemplatepage3'] = 'Import';
    $string['tabutemplatepage4'] = 'Apply';
$string['tabmtemplatename'] = 'Master templates';
    $string['tabmtemplatepage1'] = 'Save';
    $string['tabmtemplatepage2'] = 'Apply';

$string['action_help'] = 'Operate on elements already present in the survey with the following action.';
$string['action'] = 'Preexisting elements';
$string['addnewsubmission'] = 'New response';
$string['advanced_help'] = 'Is this element going to be available only to users equipped with a special permission or generally available to each user?';
$string['advanced'] = 'Advanced element';
$string['allsubmissionsdeleted'] = 'All the responses of this survey have been successfully deleted';
$string['anonymous_help'] = 'The responses management table will not show the owner of the responses and reports and export will be anonymous.';
$string['anonymous'] = 'Anonymous responses';
$string['answerisnoanswer'] = 'Answer refused';
$string['answerlabel'] = 'label';
$string['answermissingindb'] = 'Answer is missing in the database';
$string['answerposition'] = 'position';
$string['answervalue'] = 'value';
$string['applymastertemplates'] = '<a href="{$a}">Apply master templates</a>';
$string['applymtemplateinfo'] = 'You can build your survey applying set of elements taken from a master template plugin<br />Take care: all other preexisting elements (if any) will be definitely deleted WITH ALL THE DATA ALREADY GATHERED.';
$string['applytemplate'] = 'Apply template';
$string['applyusertemplatedenied01'] = 'You are not allowed to apply a user template because the current survey has already been responded';
$string['applyusertemplatedenied02'] = 'You are not allowed to apply a user template over a master template';
$string['applyusertemplates'] = '<a href="{$a}">Apply user templates</a>';
$string['applyutemplateinfo'] = 'You can enrich your survey applying set of elements taken from an XML user template<br /><strong>Be warned: by setting "{$a->usertemplate}" to "{$a->none}" and "{$a->action}" to "{$a->deleteallitems}" you drop all the items currently included in this survey</strong>';
$string['arrayexpected'] = 'Array is expected in {$a}';
$string['askallitemserase'] = 'Are you sure you want to drop all the items of this survey without applying any user template?';
$string['askdeleteallsubmissions'] = 'Are you sure you want delete ALL the stored responses?';
$string['askdeletemysubmissions'] = 'Are you sure you want delete the response created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeletemysubmissionsnevermodified'] = 'Are you sure you want delete the response created on {$a->timecreated} and never modified?';
$string['askdeleteoneitem'] = 'Are you sure you want delete the \'{$a->pluginname}\' element: {$a->content}';
$string['askdeleteonesurveypro'] = 'Are you sure you want delete the selected response owned by {$a->fullname}, created on {$a->timecreated} and modified on {$a->timemodified}?';
$string['askdeleteonesurveypronevermodified'] = 'Are you sure you want delete the selected responses owned by {$a->fullname}, created on {$a->timecreated} and never modified?';
$string['askdeleteonetemplate'] = 'Are you sure you want delete the user template "{$a}"';
$string['askitemstoadvanced'] = 'Switching to "Advanced" the element {$a->parentid} all its dependencies will be switched to "Advanced" too.<br />Dependencies is (are) the element(s) in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['askitemstohide'] = 'Hiding the element {$a->parentid} all its dependencies will be hided too.<br />Dependencies is (are) the element(s) in position: {$a->dependencies}.<br />Do you confirm this action?';
$string['askitemstoshow'] = 'Showing the element {$a->lastitem} you are going to show all its ancestors.<br />Ancestors is (are) the element(s) in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['askitemstostandard'] = 'Switching to "Standard" the element {$a->lastitem} you are going to switch to "Standard" all its ancestors.<br />Ancestors is (are) the element(s) in position: {$a->ancestors}.<br />Do you confirm this action?';
$string['attemptinfo'] = 'Survey and responses information';
$string['availability_fs'] = 'Availability';
$string['availability'] = 'Availability';
$string['available'] = 'Element available to each user';
$string['badtablenamefound'] = 'Parse error reading xml. "{$a}" has been found as table name and, most probably, is invalid.';
$string['beginfromscratch'] = 'To create a new survey you can apply a master template to get the survey all at once<br />or add as much elements as you like to create the one that best suits your needs.';
$string['belongtosearchform'] = 'Element available in the search form';
$string['branching'] = 'Branching';
$string['builplugin'] = 'Save master template';
$string['canneversubmit'] = 'You are not allowed to submit a response';
$string['cannotsubmittooearly'] = 'The survey is still not open. You have to wait until {$a}';
$string['cannotsubmittoolate'] = 'The survey has been closed on {$a}';
$string['captcha_help'] = 'Add to this collectoin the captcha to increase the security.';
$string['captcha'] = 'Add captcha';
$string['category'] = 'Course category';
$string['chaindeleted'] = 'The \'{$a->pluginname}\' element: {$a->content} and descending element(s) have been successfully deleted';
$string['changeorder'] = 'Reorder';
$string['closed'] = 'This survey closed at';
$string['closedsubmissions'] = 'Closed responses';
$string['collesactual'] = 'COLLES (Actual)';
$string['collespreferred'] = 'COLLES (Preferred)';
$string['common_fs'] = 'General settings';
$string['completionsubmit_check'] = 'Student must submit the survey at least ';
$string['completionsubmit_group_help'] = 'This survey is considered completed when the student submit it at least as much as times how it is written here.';
$string['completionsubmit_group'] = 'Require submission';
$string['completionsubmit'] = 'this is the title of the \'help\'. Where does it appear?';
$string['confirmallitemserase'] = 'Yes, I do confirm';
$string['confirmallsubmissionsdeletion'] = 'Yes, delete them all';
$string['confirmitemsdeletion'] = 'Yes, delete them all';
$string['confirmitemstoadvanced'] = 'Yes, change to advanced them all';
$string['confirmitemstohide'] = 'Yes, hide them all';
$string['confirmitemstoshow'] = 'Yes, show them all';
$string['confirmitemstostandard'] = 'Yes, change to standard them all';
$string['confirmsurveyprodeletion'] = 'Yes, delete this response';
$string['content_editor_err'] = 'The content is mandatory';
$string['content_editor_help'] = 'The content of the element as it will be shown to remote user.';
$string['content_editor'] = 'Content';
$string['content_err'] = 'The content is mandatory';
$string['content_help'] = 'The content of the element as it will be shown to remote user.';
$string['content'] = 'Content';
$string['course'] = 'Course';
$string['coverpage_welcome'] = 'Welcome to {$a}';
$string['csvsemantic'] = 'Content semantic (whether applicable)';
$string['currenttotemplate'] = 'Save current survey as master template in zip format.<br />To install a master template, unzip it to mod/surveypro/template/ and visit the notification page.';
$string['customnumber_header'] = '#';
$string['customnumber_help'] = 'Use this field to give a custom number to the element. It may be a natural number like 1 or whatever you may need: 1a, A, 1.1.a, #1, A, A.1... Take in mind that you are responsible for the coherence of that numbers. Because of this take care if you plan to change the order of the elements.';
$string['customnumber'] = 'Element number';
$string['dataimport'] = 'Import data';
$string['defaultcreationthanksmessage'] = 'Thank you. Your response has been successfully submitted!';
$string['defaulteditingthanksmessage'] = 'Thank you. Your response has been successfully modified!';
$string['deleteallitems'] = 'Delete all elements';
$string['deleteallsubmissions'] = 'Delete all responses';
$string['deletehiddenitems'] = 'Delete hidden elements';
$string['deletepluginmessage'] = 'You are about to completely delete the survey plugin "{$a}". This will completely delete everything in the database associated with this plugin. Are you SURE you want to continue?';
$string['deletevisibleitems'] = 'Delete visible elements';
$string['deletingplugin'] = 'Deleting plugin {$a}.';
$string['deletionbreakslinks'] = 'The current element has child element(s) that are going to be deleted too. The child element(s) position is: {$a}';
$string['downloadformat'] = 'Download format';
$string['downloadpdf'] = 'download to pdf';
$string['downloadtocsv'] = 'comma separated values';
$string['downloadtotsv'] = 'TAB separated values';
$string['downloadtoxls'] = 'xls';
$string['downloadtozipbysubmission'] = 'download attachments by item to zip';
$string['downloadtozipbyuser'] = 'download attachments by user to zip';
$string['downloadtype'] = 'Download file type';
$string['emptyanswer'] = 'Empty answer';
$string['emptydownload'] = 'No responses to export were found';
$string['enteruniquename'] = 'Please choose a unique name or tick the option "{$a->overwrite}" since "{$a->filename}" already exists in the choosen context';
$string['event_all_items_viewed'] = 'All items have been viewed';
$string['event_all_submissions_deleted'] = 'All submissions have been deleted';
$string['event_all_submissions_exported'] = 'All submissions have been exported';
$string['event_all_submissions_viewed'] = 'All submissions have been viewed';
$string['event_all_usertemplates_viewed'] = 'All usertemplates have been viewed';
$string['event_form_previewed'] = 'The survey layout has been previewed';
$string['event_item_created'] = 'An item has been created';
$string['event_item_deleted'] = 'An item has been deleted';
$string['event_item_modified'] = 'An item has been modified';
$string['event_mastertemplate_applied'] = 'A master template has been applied';
$string['event_mastertemplate_saved'] = 'A master template has been saved';
$string['event_submission_created'] = 'A response has been created';
$string['event_submission_deleted'] = 'A response has been deleted';
$string['event_submissions_imported'] = 'Responses imported';
$string['event_submission_modified'] = 'A response has been modified';
$string['event_submission_viewed'] = 'A response has been viewed';
$string['event_submissioninpdf_downloaded'] = 'A response has been downloaded to pdf';
$string['event_unattended_submissions_deleted'] = 'Unattended submissions were deleted by periodic sanity check';
$string['event_usertemplate_applied'] = 'A user template has been applied';
$string['event_usertemplate_deleted'] = 'A user template has been deleted';
$string['event_usertemplate_exported'] = 'A user template has been exported';
$string['event_usertemplate_imported'] = 'A user template has been imported';
$string['event_usertemplate_saved'] = 'A user template has been saved';
$string['exporttemplate'] = 'export template';
$string['extranote_help'] = 'Write here a description/note about extra informations the user is supposed to know about this element.';
$string['extranote'] = 'Additional note';
$string['extranoteinsearch_descr'] = 'Are user notes needed in the search form?';
$string['extranoteinsearch'] = 'Extra note in search form';
$string['field'] = 'field element';
$string['fieldplugin'] = 'Element plugin';
$string['fieldplugins'] = 'Field plugin';
$string['fillinginstructioninsearch_descr'] = 'Are filling instructions needed in the search form?';
$string['fillinginstructioninsearch'] = 'Filling instruction in search form';
$string['findall'] = 'Find all';
$string['format'] = 'format element';
$string['formatplugin'] = 'Format plugin';
$string['formatplugins'] = 'Format plugin';
$string['free'] = 'free';
$string['frendlyversionmismatchuser'] = 'The choosen usertemplate has a version mismatch for the following plugins: <ul{$a->plugins}</ul>
Applying it may lead to an unexpected behaviour. Please:<ul>
<li>Go to {$a->tab}->{$a->page1};</li>
<li>Download {$a->templatename};</li>
<li>Delete it from the list in the page;</li>
<li>Go to {$a->tab}->{$a->page3} and try to import it again. You will get warnings</li>;
<li>Modify your usertemplate according to the suggestions displayed during upload.</li>';
$string['fullwidth'] = 'top left (full width)';
$string['gotolist'] = 'Continue to responses list';
$string['hassubmissions_alert'] = 'This survey has already been answered at least once.<br />Please proceed with extreme caution and make only neutral changes to not compromise the validity of the whole survey.';
$string['hidden_help'] = 'Use this option to hide the element. Hided elements will not be available to anyone. You can consider these elements as not part of the survey.';
$string['hidden'] = 'hidden';
$string['hidden'] = 'Hidden';
$string['hidefield'] = 'Hide the element';
$string['hideinstructions_help'] = 'Use this checkbox to show/hide filling instruction for this element.';
$string['hideinstructions'] = 'Hide filling instruction';
$string['hideitems'] = 'Hide';
$string['hideshow'] = 'Hide/Show';
$string['history_help'] = 'Preserving history, users will no longer be able to directly modify a closed response. Modification to closed responses will be saved as a new copy, leaving the original one untouched and the history preserved.';
$string['history'] = 'Preserve history';
$string['ierr_missingparentcontent'] = 'You need to specify a parent content otherwise clear the "{$a}" field';
$string['ierr_missingparentid'] = 'You need to select a element to branch the survey. Otherwise clear the "{$a}" field';
$string['ierr_notalloweddefault'] = '"{$a}" is not an allowed default for "required" elements';
$string['ignoreitems'] = 'Ignore';
$string['import_attachmentsnotallowed'] = 'It seems you are trying to import attachments for the following elements:<ul>{$a}</ul><br />This is not allowed at the moment.';
$string['import_breakingmaxentries'] = 'Import will assign {$a->totalentries} responses to user ID {$a->userid}. This exceeds the maximum allowed number as it has been set to {$a->maxentries}';
$string['import_columnscountchanges'] = 'The number of the columns changes in the file';
$string['import_duplicateheader'] = 'The header "{$a}" was found, at least, twice';
$string['import_emptyrequiredvalue'] = 'The surveypro item number {$a->col} is required but its value is missing in the row:<br />"{$a->row}"';
$string['import_extraheaderfound'] = 'Some fields in the selected file were not found among variables of this surveypro.<br />They are: <ul>{$a}</ul>';
$string['import_invalidtimecreated'] = 'The timecreated "{$a}" is invalid';
$string['import_invalidtimemodified'] = 'The timemodified "{$a}" is invalid';
$string['import_invaliduserid'] = 'The userid "{$a}" is invalid';
$string['import_missingsemantic'] = 'A semanticless value "{$a->csvvalue}" has been found in the column {$a->csvcol} ({$a->header}) of the row: "{$a->csvrow}". The semantic for this item is supposed to be: {$a->semantic}';
$string['import_missingtimecreated'] = 'Empty creation time is invalid';
$string['import_missinguserid'] = 'Empty userid is invalid';
$string['import_positionnotinteger'] = 'The position {$a->position} found in "{$a->csvvalue}" for the column {$a->csvcol} is not an integer number';
$string['import_positionoutofbound'] = 'The position {$a->position} found in "{$a->csvvalue}" for the column {$a->csvcol} is out of bound. Allowed bounds are: "{$a->bounds}"';
$string['importfile'] = 'Choose files to import';
$string['importusertemplates'] = '<a href="{$a}">Import user templates</a>';
$string['includeadvanced'] = 'Include advanced element';
$string['includedates'] = 'Include creation and modification dates';
$string['includehidden'] = 'Include hidden elements';
$string['includenames'] = 'Include owner name';
$string['incorrectaccessdetected'] = 'Incorrect access detected';
$string['indent_help'] = 'The indent of the element alias the left margin the element will respect once drawn.';
$string['indent'] = 'Indent';
$string['inprogresssubmissions'] = 'In progress responses';
$string['insearchform_help'] = 'Is this element going to be used in the search form?';
$string['insearchform'] = 'Search form';
$string['invalid_status'] = 'Invalid $status passed to {$a}';
$string['invalidcsvfile'] = 'File {$a} is an invalid csv file. Please verify it.';
$string['invalidtypeorplugin'] = 'Invalid type or plugin were provided as item properties in the template';
$string['invitedefault'] = 'Invite';
$string['item'] = 'Element';
$string['itemaddfail'] = 'The new element has not been added';
$string['itemaddok'] = 'Element has been successfully added';
$string['itemdeleted'] = 'The \'{$a->pluginname}\' element: {$a->content} has been successfully deleted';
$string['itemdrivensemantic'] = 'as in the "{$a}" of each item';
$string['itemeditfail'] = 'An error occurred saving the element';
$string['itemedithidehide'] = 'Hiding this element, some depending elements were hided too.';
$string['itemeditmakeadvanced'] = 'Marking this element as "Advanced", some depending elements were forced to "Advanced" too.';
$string['itemeditok'] = 'Element has been successfully modified';
$string['itemeditshow'] = 'Showing this element, some parent elements were showed too.';
$string['itemeditshowinbasicform'] = 'Removing the "Advanced" attribute from this element, some parent elements were forced to "Standard" element too.';
$string['itemlist'] = 'Elements list';
$string['left'] = 'left';
$string['likelast'] = 'Like last response';
$string['loweruser'] = 'user';
$string['lowerusers'] = 'users';
$string['malformedchildparentvalue'] = 'Malformed condition: "{$a}".<br />It might never be verified.';
$string['managesurveyprofieldplugins'] = 'Manage field plugins';
$string['managesurveyproformatplugins'] = 'Manage format plugins';
$string['managesurveyproreportplugins'] = 'Manage report plugins';
$string['managesurveyprotemplateplugins'] = 'Manage template plugins';
$string['manageusertemplates'] = '<a href="{$a}">Manage user templates</a>';
$string['mastertemplate_help'] = 'Choose the master template you want to apply to your survey.';
$string['mastertemplate_noedit'] = 'Current survey supports multilanguage as imported from a master template.<br />This means that the survey displays questions and labels according to the user preferred language (if available).<br />By editing this kind of survey you will lose the multilanguage support returning to the standard indifferenciated labels all along the survey.<br />Be warned that once you drop the multilanguage support even by generating again a master template, you still no longer get missed languages and, last but not least, the drop of the multilanguage support is not undoable.<br />Are you sure you want to edit this multilanguage survey?';
$string['mastertemplate'] = 'Master templates';
$string['mastertemplateaddendum'] = '<br />You can not apply this mastertemplate until you uninstall it, fix all the issues and reinstall it.';
$string['mastertemplatename_help'] = 'Choose the name of the master template name that is going to be downloaded in zip format.';
$string['mastertemplatename'] = 'Master template name';
$string['mastertemplateplugins'] = 'Master template plugin';
$string['mastertemplates'] = 'master templates';
$string['maxentries_help'] = 'The maximum number of responses a student is allowed to submit for this activity.';
$string['maxentries'] = 'Maximum allowed attempts';
$string['maxinputdelay_descr'] = 'The maximum allowed delay in hours for users to submit a survey. Even if the user is allowed to pause the data entry and restart it later, after the time defined here partial responses will be deleted. Default of 168 hours is equivalent to a week. Set this to 0 (zero) if you really want to allow partial responses (not recommended).';
$string['maxinputdelay'] = 'Max input delay';
$string['missingfile'] = 'It seems no file was selected';
$string['missingitemplugin'] = 'One or more items of the template are missing the plugin';
$string['missingitemtype'] = 'One or more items of the template are missing the type';
$string['missingitemversion'] = 'One or more items of the template is missing the version';
$string['missingmandatory'] = 'Some mandatory answer of this response has not been found. Because of this, the overall response has been marked as "{$a}".<br />To fix this issue, please edit the response and review item contents page per page.';
$string['missingplugin'] = 'Each plugin has undefined version in the template file';
$string['missingsortindex'] = 'Sortindex is missing in the template';
$string['missingvalidation'] = 'Some answers of this response have been found as unverified. Because of this, the overall response has been marked as "{$a}".<br />Your data is not necessarily incorrect but needs verification before definitive storage.<br />To fix this issue, please edit the response and review item contents page per page.';
$string['module'] = 'This instance of survey';
$string['modulesettinghdr'] = 'Surveypro settings';
$string['months'] = 'months';
$string['mtemplatessection'] = 'Master templates section';
$string['namenotset'] = 'not set';
$string['needrole'] = 'Advanced element: only users with specific capability will see it';
$string['newpageforchild_help'] = 'Use this option to force a new page after each branching element.';
$string['newpageforchild'] = 'Branches increase pages';
$string['newsubmissionbody'] = '{$a->username} submitted a new record in {$a->surveyproname}
You can review it <a title="{$a->title}" href="{$a->href}">here</a>';
$string['newsubmissionsubject'] = 'New response';
$string['nextformpage'] = 'Next page >>';
$string['noanswer'] = 'No answer';
$string['noattachmentfound'] = 'Not any attachment has been found';
$string['noitemsfound'] = 'This survey is still a work in progress.<br />Please try again later.';
$string['noitemsfoundadmin'] = 'This survey has no elements. Please add them from the page "{$a}".';
$string['nomoreitems'] = 'On the basis of the answers provided, no more elements remain to display.<br />Your survey is over. You only need to submit{$a}.';
$string['nomoresubmissionsallowed'] = 'The maximun number of {$a} responses was already reached.<br />No more attempts are allowed.';
$string['nomtemplates_help'] = 'Course creator probably denied the instantiation of each master tempalte. Contact your course creator for further details.';
$string['nomtemplates_message'] = 'Sorry. Not any master template seems available in this moodle site instance.';
$string['nomtemplates'] = 'Missing master templates';
$string['nosubmissionfound'] = 'No responses were found in this survey.';
$string['notanswereditem'] = 'Answer not submitted';
$string['notanyset'] = 'none';
$string['notdeleted_item'] = 'Unable to delete record id = {$a} from surveypro_item';
$string['notdeleted_plugin'] = 'Unable to delete record id = {$a->pluginid} from surveypro{$a->type}_{$a->plugin}';
$string['notdeleted_submission'] = 'Unable to delete record with id IN ({$a}) from surveypro_submission';
$string['notdeleted_userdata'] = 'Unable to delete record id = {$a} from surveypro_answer';
$string['note'] = 'Note:';
$string['nothingtodownload'] = 'Nothing to download';
$string['notifymore_help'] = 'Some additional email addresses to notify about new responses. Addresses are supposed to be one per row.';
$string['notifymore'] = 'More notifications';
$string['notifyrole_help'] = 'Send an email to each component of the selected roles at each response. The email will only advise about response from the user, not about its content and without sender details.';
$string['notifyrole'] = 'Notify role';
$string['notinsearchform'] = 'Element not available in the search form';
$string['numinstances'] = 'Instances';
$string['onlyadvanceditemhere'] = 'The current page holds only advanced elements you are not supposed to access';
$string['onlyoptional'] = 'Optional is forced by the value of default.';
$string['onlyreview'] = ' or review';
$string['opened'] = 'Opening time';
$string['outputstyle'] = 'Output style';
$string['overwrite_help'] = 'Selecting this checkbox you will overwrite an older template with the same name. If you leave this checkbox unselected, in case of conflicts, you will be asked for a new unique name.';
$string['overwrite'] = 'Replace older template';
$string['pagexofy'] = 'Page {$a->formpage} of {$a->maxassignedpage}';
$string['parentconstraints'] = 'Parent constraints';
$string['parentcontent_help'] = 'This is what the user is supposed to enter in the parent element in order to enable/display this element.';
$string['parentcontent'] = 'Parent content';
$string['parentformat'] = 'Define the content format of the answer as shown here: {$a}';
$string['parentid_alt'] = 'Parent element';
$string['parentid_header'] = 'Relation';
$string['parentid_help'] = 'Parent elements allow you to create conditional branching. Dimmed elements in the list identify hidden parent elments. Show them to have them available in this list.<br />Elements preceded by an asterisk are supposed to belong ONLY to advanced form.';
$string['parentid'] = 'Parent element';
$string['pause'] = 'Pause';
$string['plugin'] = 'Element';
$string['pluginname_help'] = 'Write here the name of the survey plugin you are going to save.';
$string['plugintype'] = 'Plugin type';
$string['position_help'] = 'Use this option to choose the position of the content of the element. It can be to the left of the user interface, in a dedicated row just upper the interface to enter the answer or in a dedicated row just upper the interface spanning all the row.
Note: The left position forces the element contents to plain text without images.
The two \'top\' positions are usually needed for contents longer than few words and are required for questions containing images!';
$string['position'] = 'Question position';
$string['previewmode'] = 'You are in \'{$a}\': buttons to save data are not supposed to display';
$string['previousformpage'] = '<< Previous page';
$string['raw'] = 'Raw (for further import into different instances of surveypro)';
$string['readonlyaccess'] = 'Read only access';
$string['relation_status'] = 'Status';
$string['reportederror'] = '{$a}';
$string['reportederrortemplate'] = '%s as required by the xsd of the "%s" plugin';
$string['reportplugin'] = 'Report plugin';
$string['reportplugins'] = 'Report plugin';
$string['reportsection'] = 'Reports section';
$string['required_help'] = 'Will the user be forced to answer this element?';
$string['required'] = 'Required';
$string['response'] = 'response';
$string['responseauthor'] = 'Author: ';
$string['responsedeleted'] = 'User response has been successfully deleted';
$string['responses'] = 'responses';
$string['responsetimecreated'] = 'Response sbmitted on: ';
$string['responsetimemodified'] = ', Last modified on: ';
$string['revieworpause'] = ', review or pause';
$string['reviewsubmissions'] = 'review surveypro submissions';
$string['riskyeditdeadline_help'] = 'Allow users permitted to manage survey elements to force modifications of this survey even once already answered.';
$string['riskyeditdeadline'] = 'Deadline of risky modification session';
$string['runreport'] = '<a href="{$a->href}">Run {$a->reportname} report</a>';
$string['saveasnew'] = 'Save as new';
$string['savemastertemplates'] = '<a href="{$a}">Save master templates</a>';
$string['saveresume_help'] = 'Allow to pause a survey in order to resume and submit it in a second data entry session.';
$string['saveresume'] = 'Allow Save/Resume';
$string['saveusertemplates'] = '<a href="{$a}">Save user templates</a>';
$string['schemavalidationfailed'] = 'The template uses an invalid xml file. Please verify it';
$string['settings'] = 'Surveypro';
$string['sharinglevel_help'] = 'Choose at which level your template will be shared with other courses. If you choose "course" this template will be available in this course ONLY, if you choose course category this template will be available ONLY to courses sharing the same course "category" with this course, if you choose "site" this template will be available to each other courses in this platform.';
$string['sharinglevel'] = 'Sharing level';
$string['showallsubmissions'] = 'Show all responses';
$string['showfield'] = 'Show the element';
$string['sortindex'] = 'Order';
$string['specializations'] = '{$a} specific settings';
$string['star'] = '*';
$string['startyear_help'] = 'Define the lower year that each question will require.';
$string['startyear'] = 'Minimum allowed year';
$string['status'] = 'Survey status';
$string['statusboth'] = 'closed and in progress both';
$string['statusclosed'] = 'closed';
$string['statusinprogress'] = 'in progress';
$string['stopyear_help'] = 'Define the upper year that each question will require.';
$string['stopyear'] = 'Maximum allowed year';
$string['submission'] = 'Attempt';
$string['submissions_all'] = '{$a->submissions} {$a->oneormanyresponses} submitted by {$a->distinctusers} {$a->oneormanyusers}';
$string['submissions_detail'] = '{$a->submissions} \'{$a->status}\' {$a->oneormanyresponses} submitted by {$a->distinctusers} {$a->oneormanyusers}';
$string['submissions_welcome'] = 'Responses overview';
$string['submissions'] = 'Attempts';
$string['submissionslist'] = 'Attempts list';
$string['surveypro:accessadvanceditems'] = 'Access advanced items';
$string['surveypro:accessownreports'] = 'Access own reports';
$string['surveypro:accessreports'] = 'Access reports';
$string['surveypro:addinstance'] = 'Add a new survey activity';
$string['surveypro:additems'] = 'Add survey elements';
$string['surveypro:alwaysseeowner'] = 'See responses owner even for anonymous surveys';
$string['surveypro:applymastertemplates'] = 'Apply master template';
$string['surveypro:applyusertemplates'] = 'Apply user templates';
$string['surveypro:deleteotherssubmissions'] = 'Delete responses from other users';
$string['surveypro:deleteownsubmissions'] = 'Delete own responses';
$string['surveypro:deleteusertemplates'] = 'Delete user templates';
$string['surveypro:downloadusertemplates'] = 'Download user templates';
$string['surveypro:editotherssubmissions'] = 'Edit responses from other users';
$string['surveypro:editownsubmissions'] = 'Edit own responses';
$string['surveypro:exportdata'] = 'Export collected responses';
$string['surveypro:ignoremaxentries'] = 'Submissions are not limited by max entries setting';
$string['surveypro:importdata'] = 'Import data';
$string['surveypro:importusertemplates'] = 'Upload user templates';
$string['surveypro:manageitems'] = 'Manage survey elements';
$string['surveypro:manageusertemplates'] = 'Manage user templates';
$string['surveypro:preview'] = 'Preview a survey';
$string['surveypro:savemastertemplates'] = 'Save master template';
$string['surveypro:savesubmissiontopdf'] = 'Download own submission in PDF';
$string['surveypro:saveusertemplates'] = 'Save user templates';
$string['surveypro:searchsubmissions'] = 'Search responses';
$string['surveypro:seeotherssubmissions'] = 'See responses from other users';
$string['surveypro:submit'] = 'Submit responses';
$string['surveypro:view'] = 'View surveys';
$string['surveyprofieldpluginname'] = 'Field element plugin';
$string['surveyproformatpluginname'] = 'Item element plugin';
$string['surveyproplugins'] = 'Survey plugins';
$string['surveyproreportpluginname'] = 'Report plugin';
$string['surveyprotemplatepluginname'] = 'Master template plugin';
$string['switchoptional'] = 'Set the question tye element as optional';
$string['switchrequired'] = 'Set the question tye element as required';
$string['system'] = 'Site';
$string['templatecreateinfo'] = 'Save a user template with the current survey. At any time you can download and share it with other moodle users or restore it to your own server. Be careful to "{$a}" if you want to reuse your templates without downloading and reloading them.';
$string['templatelist'] = 'list of available templates';
$string['templatename_help'] = 'Write here the name of the template you are going to save.';
$string['templatename'] = 'Template name';
$string['templateplugin'] = 'Master template plugin';
$string['thankshtml_help'] = 'The html code of the web page the user get at each response closing time.';
$string['thankshtml'] = 'Thanks web page';
$string['timeclose_help'] = 'The last date available for students to fill a survey.';
$string['timeclose'] = 'Available to';
$string['timecreated'] = 'Created';
$string['timemodified'] = 'Modified';
$string['timeopen_help'] = 'The first date available for students to fill a survey.';
$string['timeopen'] = 'Available from';
$string['top'] = 'top';
$string['translatedstring'] = '$string[\'{$a->stringkey}\'] = \'English translation of corresponding string from "{$a->userlang}" language file\';';
$string['type'] = 'Type';
$string['typefield'] = 'Fields';
$string['typeformat'] = 'Formats';
$string['typeplugin_help'] = 'This is the list of available elements. Survey elements are of two types: "field" type and "format" type. Choose the element that better suite your needs.';
$string['typeplugin'] = 'Element';
$string['unhandledvalue'] = 'Unhandled return value from {$a}';
$string['unixtime'] = 'unix time';
$string['unlimited'] = 'Unlimited';
$string['user'] = 'User';
$string['usercanceled'] = 'Action canceled by the user';
$string['userstyle_help'] = 'Add here one or more cascade style sheet (css) you want to apply to this survey.';
$string['userstyle'] = 'Custom style sheet';
$string['usertemplateinfo_help'] = 'Choose the user template you want to add to your survey.';
$string['usertemplateinfo'] = 'User templates';
// $string['usertemplates'] = 'user templates';
$string['utemplatessection'] = 'User templates section';
$string['validation'] = 'Validation options';
$string['validationinfo'] = 'This report let you verify the reliability of the current survey. This tool checks the validity of each relation identifying the bad ones that will never allow child element to be included in the survey.';
$string['variable_help'] = 'The name of the variable once downloaded.';
$string['variable'] = 'Variable';
$string['verbose'] = 'Verbose (for human reading)';
$string['versionmismatch'] = 'Version mismatch for {$a->plugin} {$a->type} plugin. Template uses version: {$a->currentversion} while your surveypro plugin uses version {$a->versiondisk}';
$string['visiblesonly_help'] = 'Include in this template only visibles elements.';
$string['visiblesonly'] = 'Visibles elements only';
$string['welcomeimport'] = 'Use this page to import responses into this survey. <br />
The headers of the csv file to import are supposed to match the "variable names" of the elements of the survey.<br />
Currently it is not allowed the import of attachment elements.<br />
Unknown headers will break the import process.<br />
"Ownerid" can be included among headers. It will assign the ownership of each imported response (even if the survey is anonymous).<br />
If the "Ownerid" column is missing from the csv file, the imported responses will be assigned to the user executing the import.<br />
It is allowed the importation of csv files missing required elements but, whether included, they must hold valid and non empty values.<br />
Import files missing required elements are allowed and the imported responses will be marked as "in progress".<br />
Semantic defines the meaning of the content of the csv file for some specific elements. For instance, for a "select" plugin element, the csv file can provide the label of the answer such as its value or its position in the drop down user interface<br />
Elements currently using semantic are: <ul>{$a->items}</ul>
It is possible to choose a single semantic that will apply to EACH element to import or to choose `{$a->customsemantic}` option to provide a custom semantic for each element.<br />
The import process breaks if it lead to exceed the maximum number of responses allowed to users (if set).';
$string['willclose'] = 'Closure time';
$string['willopen'] = 'This survey will open at';
$string['wrong_direction_found'] = 'Invalid $direction provided to {$a->methodname} in conjunction with $startingpage == {$a->methodname}';
$string['wrong_sharinglevel_found'] = 'Invalid $sharinglevel = "{$a->sharinglevel}" provided to {$a->methodname}';
$string['wrong_userdatarec_found'] = 'Invalid $userdatarec = \'{$a}\' has not been replaced';
$string['wrongrelation'] = '"{$a}" will never match';
$string['wrongsortindex'] = 'The sortindex of the items in the template does not grow uniformly';
$string['xmltemplate_help'] = 'Choose the template you want to download as zip file to share it with other moodle users.';
$string['xsdnotfound'] = 'xsd validation schema for your xml template was not found.<br />Your code must be fixed by a developer.';
$string['yournextattempt'] = 'You could fill out your survey number {$a}';
$string['yoursubmissions'] = 'Your \'{$a->status}\' responses: {$a->responsescount}';

$string['aaa'] = '';
$string['aaa'] = '';
$string['aaa'] = '';
$string['aaa'] = '';
$string['aaa'] = '';
$string['aaa'] = '';
