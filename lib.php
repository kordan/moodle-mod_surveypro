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
 * Library of interface functions and constants for module surveypro
 *
 * All the core Moodle functions, needed to allow the module to work
 * integrated in Moodle should be placed here.
 * All the surveypro specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Some constants
 */
define('SURVEYPRO_VALUELABELSEPARATOR', '::');
define('SURVEYPRO_OTHERSEPARATOR'     , '->');

/**
 * TABS
 */
define('SURVEYPRO_TABLAYOUT'     , 1);
define('SURVEYPRO_TABSUBMISSIONS', 2);
define('SURVEYPRO_TABUTEMPLATES' , 3);
define('SURVEYPRO_TABMTEMPLATES' , 4);

/**
 * PAGES
 */
// PAGES in tab LAYOUT.
define('SURVEYPRO_LAYOUT_PREVIEW'  , 1);
define('SURVEYPRO_LAYOUT_ITEMS'    , 2);
define('SURVEYPRO_LAYOUT_ITEMSETUP', 3);
define('SURVEYPRO_LAYOUT_VALIDATE' , 4);

// PAGES in tab SUBMISSION.
define('SURVEYPRO_SUBMISSION_CPANEL'  , 1);
define('SURVEYPRO_SUBMISSION_MANAGE'  , 2);
define('SURVEYPRO_SUBMISSION_INSERT'  , 3);
define('SURVEYPRO_SUBMISSION_EDIT'    , 4);
define('SURVEYPRO_SUBMISSION_READONLY', 5);
define('SURVEYPRO_SUBMISSION_SEARCH'  , 6);
define('SURVEYPRO_SUBMISSION_REPORT'  , 7);
define('SURVEYPRO_SUBMISSION_IMPORT'  , 8);
define('SURVEYPRO_SUBMISSION_EXPORT'  , 9);

// PAGES in tab USER TEMPLATES.
define('SURVEYPRO_UTEMPLATES_MANAGE', 1);
define('SURVEYPRO_UTEMPLATES_BUILD' , 2);
define('SURVEYPRO_UTEMPLATES_IMPORT', 3);
define('SURVEYPRO_UTEMPLATES_APPLY' , 4);

// PAGES in tab MASTER TEMPLATES.
define('SURVEYPRO_MTEMPLATES_BUILD', 1);
define('SURVEYPRO_MTEMPLATES_APPLY', 2);

/**
 * ITEM TYPES
 */
define('SURVEYPRO_TYPEFIELD' , 'field');
define('SURVEYPRO_TYPEFORMAT', 'format');

/**
 * ACTIONS
 */
define('SURVEYPRO_NOACTION'          , '0');

/**
 * ACTIONS in LAYOUT MANAGEMENT page
 */
define('SURVEYPRO_CHANGEORDER'       , '1');
define('SURVEYPRO_DELETEITEM'        , '2');
define('SURVEYPRO_DROPMULTILANG'     , '3');
define('SURVEYPRO_CHANGEINDENT'      , '4');
define('SURVEYPRO_HIDEITEM'          , '5');
define('SURVEYPRO_SHOWITEM'          , '6');
define('SURVEYPRO_MAKERESERVED'      , '7');
define('SURVEYPRO_MAKEAVAILABLE'     , '8');

/**
 * BULK ACTIONS in LAYOUT MANAGEMENT and in APPLY UTEMPLATE page
 */
define('SURVEYPRO_IGNOREITEMS'       , '0');
define('SURVEYPRO_HIDEALLITEMS'      , '13');
define('SURVEYPRO_SHOWALLITEMS'      , '14');
define('SURVEYPRO_DELETEALLITEMS'    , '15');
define('SURVEYPRO_DELETEVISIBLEITEMS', '16');
define('SURVEYPRO_DELETEHIDDENITEMS' , '17');

// ACTIONS in RESPONSES section.
define('SURVEYPRO_DELETERESPONSE'    , '18');
define('SURVEYPRO_DELETEALLRESPONSES', '19');
define('SURVEYPRO_DUPLICATERESPONSE' , '20');

// ACTIONS in UTEMPLATE section.
define('SURVEYPRO_DELETEUTEMPLATE'   , '21');
define('SURVEYPRO_EXPORTUTEMPLATE'   , '22');

/**
 * VIEW
 */
// VIEW in USER FORM section.
define('SURVEYPRO_NOVIEW'          , '0');
define('SURVEYPRO_NEWRESPONSE'     , '1');
define('SURVEYPRO_EDITRESPONSE'    , '2');
define('SURVEYPRO_READONLYRESPONSE', '3');

// VIEW in ITEM section.
define('SURVEYPRO_EDITITEM'        , '4');
define('SURVEYPRO_CHANGEORDERASK'  , '5');

// VIEW in RESPONSES section.
define('SURVEYPRO_RESPONSETOPDF'   , '6');

/**
 * OVERFLOW
 */
define('SURVEYPRO_LEFT_OVERFLOW' , -10);
define('SURVEYPRO_RIGHT_OVERFLOW', -20);

/**
 * SENDERS
 */
define('SURVEYPRO_TAB'  , 1);
define('SURVEYPRO_BLOCK', 2);

/**
 * FEEDBACKMASK
 */
define('SURVEYPRO_NOFEEDBACK', 0);

/**
 * ITEMPREFIX
 */
define('SURVEYPRO_ITEMPREFIX', 'surveypro');
define('SURVEYPRO_PLACEHOLDERPREFIX', 'placeholder');
define('SURVEYPRO_DONTSAVEMEPREFIX', 'placeholder');

/**
 * INVITE, NO-ANSWER AND IGNOREME VALUE
 */
// Since the very first beginning of the development.
// define('SURVEYPRO_INVITATIONVALUE', '__invItat10n__'); // User should never guess it.
// define('SURVEYPRO_NOANSWERVALUE',   '__n0__Answer__'); // User should never guess it.
// define('SURVEYPRO_IGNOREMEVALUE',   '__1gn0rE__me__'); // User should never guess it.

// Starting from version 2015090901.
define('SURVEYPRO_INVITEVALUE',        '@@_INVITE_@@'); // User should never guess it.
define('SURVEYPRO_NOANSWERVALUE',      '@@_NOANSW_@@'); // User should never guess it.
define('SURVEYPRO_IGNOREMEVALUE',      '@@_IGNORE_@@'); // User should never guess it.
define('SURVEYPRO_EXPNULLVALUE',       '@@_NULVAL_@@'); // User should never guess it.
define('SURVEYPRO_IMPFORMATSUFFIX',    '@@_FORMAT_@@'); // User should never guess it.

/**
 * ITEM ADJUSTMENTS
 */
define('SURVEYPRO_VERTICAL',   0);
define('SURVEYPRO_HORIZONTAL', 1);

/**
 * SURVEYPRO STATUS
 */
define('SURVEYPRO_STATUSCLOSED'    , 0);
define('SURVEYPRO_STATUSINPROGRESS', 1);
define('SURVEYPRO_STATUSALL'       , 2);

/**
 * DOWNLOAD
 */
define('SURVEYPRO_DOWNLOADCSV', 1);
define('SURVEYPRO_DOWNLOADTSV', 2);
define('SURVEYPRO_DOWNLOADXLS', 3);
define('SURVEYPRO_FILESBYUSER', 4);
define('SURVEYPRO_FILESBYITEM', 5);
define('SURVEYPRO_NOFIELDSSELECTED' , 1);
define('SURVEYPRO_NORECORDSFOUND'   , 2);
define('SURVEYPRO_NOATTACHMENTFOUND', 3);
define('SURVEYPRO_OWNERIDLABEL', 'ownerid');
define('SURVEYPRO_TIMECREATEDLABEL', 'timecreated');
define('SURVEYPRO_TIMEMODIFIEDLABEL', 'timemodified');

/**
 * SEPARATORS
 */
define('SURVEYPRO_DBMULTICONTENTSEPARATOR',     ';');
define('SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR', '; ');

/**
 * CONFIRMATION
 */
define('SURVEYPRO_UNCONFIRMED'    , 0);
define('SURVEYPRO_CONFIRMED_YES'  , 1);
define('SURVEYPRO_CONFIRMED_NO'   , 2);
define('SURVEYPRO_ACTION_EXECUTED', 3);

/**
 * DEFAULTVALUE OPTION
 */
define('SURVEYPRO_CUSTOMDEFAULT'  , 1);
define('SURVEYPRO_INVITEDEFAULT'  , 2);
define('SURVEYPRO_NOANSWERDEFAULT', 3);
define('SURVEYPRO_LIKELASTDEFAULT', 4);
define('SURVEYPRO_TIMENOWDEFAULT' , 5);

/**
 * FILEAREAS
 */
define('SURVEYPRO_STYLEFILEAREA'      , 'userstyle');
define('SURVEYPRO_TEMPLATEFILEAREA'   , 'templatefilearea');
define('SURVEYPRO_THANKSHTMLFILEAREA' , 'thankshtml');
define('SURVEYPRO_ITEMCONTENTFILEAREA', 'itemcontent');

/**
 * FIRENDLY FORMAT
 */
define('SURVEYPRO_FRIENDLYFORMAT', -1);

/**
 * POSITION OF THE QUESTION CONTENT IN THE ITEM
 */
define('SURVEYPRO_POSITIONLEFT',      0);
define('SURVEYPRO_POSITIONTOP',       1);
define('SURVEYPRO_POSITIONFULLWIDTH', 2);

/**
 * STATUS OF CONDITIONS OF RELATIONS
 */
define('SURVEYPRO_CONDITIONOK',         0);
define('SURVEYPRO_CONDITIONNEVERMATCH', 1);
define('SURVEYPRO_CONDITIONMALFORMED',  2);

/**
 * SEMANTIC OF CONTENT RETURNED BY ITEMS
 */
define('SURVEYPRO_ITEMSRETURNSVALUES',  0);
define('SURVEYPRO_ITEMRETURNSLABELS',   1);
define('SURVEYPRO_ITEMRETURNSPOSITION', 2);

/**
 * OUTPUT CONTENT
 */
define('SURVEYPRO_LABELS', 'labels');
define('SURVEYPRO_VALUES', 'values');
define('SURVEYPRO_POSITIONS', 'positions');
define('SURVEYPRO_ITEMDRIVEN', 'itemdriven');

/**
 * DUMMY CONTENT USED AT ANSWER SAVE TIME
 */
define('SURVEYPRO_DUMMYCONTENT', '__my_dummy_content@@');

/**
 * OUTPUT OF FINAL SUBMISSION EVALUATION
 */
define('SURVEYPRO_VALIDRESPONSE',     0);
define('SURVEYPRO_MISSINGMANDATORY',  1);
define('SURVEYPRO_MISSINGVALIDATION', 2);

/**
 * PDF TEMPLATES
 */
define('SURVEYPRO_2COLUMNSTEMPLATE', 2);
define('SURVEYPRO_3COLUMNSTEMPLATE', 3);

/**
 * EXPORT CSV FILE STYLE
 */
define('SURVEYPRO_RAW',     0);
define('SURVEYPRO_VERBOSE', 1);

// Moodle core API.

require_once($CFG->dirroot . '/lib/formslib.php'); // Needed by unittest.

/**
 * Saves a new instance of the surveypro into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $surveypro An object from the form in mod_form.php
 * @param mod_surveypro_mod_form $mform
 * @return int The id of the newly inserted surveypro record
 */
function surveypro_add_instance($surveypro, $mform) {
    global $DB;

    $cmid = $surveypro->coursemodule;
    $context = context_module::instance($cmid);

    surveypro_pre_process_checkboxes($surveypro);
    $surveypro->timecreated = time();
    $surveypro->timemodified = time();

    $surveypro->id = $DB->insert_record('surveypro', $surveypro);
    // Stop working with the surveypro table (unless $surveypro->thankshtml_editor['itemid'] != 0).

    // Manage userstyle filemanager.
    $draftitemid = $surveypro->userstyle_filemanager;
    file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);

    // Manage thankshtml editor.
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankshtml_editor['itemid']) {
        $surveypro->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro',
                SURVEYPRO_THANKSHTMLFILEAREA, 0, $editoroptions, $surveypro->thankshtml_editor['text']);
        $surveypro->thankshtmlformat = $surveypro->thankshtml_editor['format'];
        $DB->update_record('surveypro', $surveypro);
    }

    return $surveypro->id;
}

/**
 * Updates an instance of the surveypro in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $surveypro An object from the form in mod_form.php
 * @param mod_surveypro_mod_form $mform
 * @return boolean Success/Fail
 */
function surveypro_update_instance($surveypro, $mform) {
    global $DB;

    $cmid = $surveypro->coursemodule;
    $draftitemid = $surveypro->userstyle_filemanager;
    $context = context_module::instance($cmid);

    $surveypro->timemodified = time();
    $surveypro->id = $surveypro->instance;

    surveypro_pre_process_checkboxes($surveypro);

    // I don't think classes are available here!
    // So, I can't use $utilityman->reset_items_pages();
    $whereparams = array('surveyproid' => $surveypro->id);
    $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);

    $DB->update_record('surveypro', $surveypro);
    // Stop working with the surveypro table (unless $surveypro->thankshtml_editor['itemid'] != 0).

    // Manage userstyle filemanager.
    if ($draftitemid = file_get_submitted_draft_itemid('userstyle_filemanager')) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);
    }

    // Manage thankshtml editor.
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankshtml_editor['itemid']) {
        $surveypro->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro',
                SURVEYPRO_THANKSHTMLFILEAREA, 0, $editoroptions, $surveypro->thankshtml_editor['text']);
        $surveypro->thankshtmlformat = $surveypro->thankshtml_editor['format'];
        $DB->update_record('surveypro', $surveypro);
    }

    return true;
}

/**
 * Runs any processes that must run before
 * a lesson insert/update
 *
 * surveypro_pre_process_checkboxes
 *
 * @param object $surveypro Surveypro record
 * @return void
 */
function surveypro_pre_process_checkboxes($surveypro) {
    $checkboxes = array('newpageforchild', 'history', 'saveresume', 'keepinprogress', 'anonymous', 'notifyteachers');
    foreach ($checkboxes as $checkbox) {
        if (!isset($surveypro->{$checkbox})) {
            $surveypro->{$checkbox} = 0;
        }
    }
}

/**
 * Removes an instance of the surveypro from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function surveypro_delete_instance($id) {
    global $DB;

    if (!$surveypro = $DB->get_record('surveypro', array('id' => $id))) {
        return false;
    }

    $status = true;

    // Now get rid of all files.
    $fs = get_file_storage();
    if ($cm = get_coursemodule_from_instance('surveypro', $surveypro->id)) {
        $context = context_module::instance($cm->id);
        $fs->delete_area_files($context->id);
    }

    $whereparams = array('surveyproid' => $surveypro->id);

    // Delete any dependent records here.
    if ($submissions = $DB->get_records('surveypro_submission', $whereparams, '', 'id')) {
        $submissions = array_keys($submissions);

        // Delete all associated surveypro_answer.
        if (!$DB->delete_records_list('surveypro_answer', 'submissionid', $submissions)) {
            $status = false;
        }

        // Delete all associated surveypro_submission.
        if (!$DB->delete_records('surveypro_submission', $whereparams)) {
            $status = false;
        }
    }

    // Get all item_<<plugin>> and format_<<plugin>>.
    $types = array(SURVEYPRO_TYPEFIELD, SURVEYPRO_TYPEFORMAT);
    foreach ($types as $type) {
        $pluginlist = surveypro_get_plugin_list($type);

        // Delete all associated item<<$type>>_<<plugin>>.
        foreach ($pluginlist as $plugin) {
            $tablename = 'surveypro'.$type.'_'.$plugin;
            $whereparams['plugin'] = $plugin;

            if ($deletelist = $DB->get_records('surveypro_item', $whereparams, 'id', 'id')) {
                $deletelist = array_keys($deletelist);
                list($insql, $inparams) = $DB->get_in_or_equal($deletelist, SQL_PARAMS_NAMED, 'delete');
                $select = 'itemid '.$insql;

                if (!$DB->delete_records_select($tablename, $select, $inparams)) {
                    $status = false;
                }
            }
        }
    }

    // Delete all associated surveypro_items.
    if (!$DB->delete_records('surveypro_item', array('surveyproid' => $surveypro->id))) {
        $status = false;
    }

    // Finally, delete the surveypro record.
    if (!$DB->delete_records('surveypro', array('id' => $surveypro->id))) {
        $status = false;
    }

    return $status;
}

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function surveypro_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
             return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Print the grade information for the surveypro for this user.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $coursemodule
 * @param stdClass $surveypro
 */
function surveypro_user_outline($course, $user, $coursemodule, $surveypro) {
    $return = new stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param stdClass $course the current course record
 * @param stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param stdClass $surveypro the module instance record
 * @return void, is supposed to echp directly
 */
function surveypro_user_complete($course, $user, $mod, $surveypro) {
    return true;
}

/**
 * Print recent activity from all surveypro in a given course
 *
 * This is used by the recent activity block
 * @param mixed $course the course to print activity for
 * @param bool $viewfullnames boolean to determine whether to show full names or not
 * @param int $timestart the time the rendering started
 * @return bool true if activity was printed, false otherwise.
 */
function surveypro_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  // True if anything was printed, otherwise false.
}

/**
 * Returns all surveypro since a given time.
 *
 * @param array $activities The activity information is returned in this array
 * @param int $index The current index in the activities array
 * @param int $timestart The earliest activity to show
 * @param int $courseid Limit the search to this course
 * @param int $cmid The course module id
 * @param int $userid Optional user id
 * @param int $groupid Optional group id
 * @return void
 */
function surveypro_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Print recent activity from all assignments in a given course
 *
 * This is used by course/recent.php
 * @param stdClass $activity
 * @param int $courseid
 * @param bool $detail
 * @param array $modnames
 */
function surveypro_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function surveypro_cron_scheduled_task() {
    global $DB;

    // Delete too old submissions from surveypro_answer and surveypro_submission.

    $saveresumestatus = array(0, 1);
    // If $saveresumestatus == 0 then saveresume is not allowed.
    // Users leaved responses in progress more than four hours ago...
    // I can not believe they are still working on them so I delete thier responses now.

    // If $saveresumestatus == 1 then saveresume is allowed
    // Users leaved responses in progress more than maximum allowed time delay so I delete thier responses now.
    $maxinputdelay = get_config('mod_surveypro', 'maxinputdelay');
    foreach ($saveresumestatus as $saveresume) {
        if (($saveresume == 1) && ($maxinputdelay == 0)) { // Maxinputdelay == 0 means, please don't delete.
            continue;
        }

        // First step: filter surveypro to the subset having 'saveresume' = $saveresume.
        if ($surveypros = $DB->get_records('surveypro', array('saveresume' => $saveresume), null, 'id, keepinprogress')) {
            $sofar = ($saveresume == 0) ? (4 * 3600) : ($maxinputdelay * 3600);
            $sofar = time() - $sofar;
            foreach ($surveypros as $surveypro) {
                $keepinprogress = $surveypro->keepinprogress;
                if (!empty($keepinprogress)) {
                    continue;
                }

                // Second step: if you are here, for each surveypro
                // filter only submissions having 'status' = SURVEYPRO_STATUSINPROGRESS and timecreated < :sofar.
                $where = 'surveyproid = :surveyproid AND status = :status AND timecreated < :sofar';
                $whereparams = array('surveyproid' => $surveypro->id, 'status' => SURVEYPRO_STATUSINPROGRESS, 'sofar' => $sofar);
                if ($submissions = $DB->get_recordset_select('surveypro_submission', $where, $whereparams, 'surveyproid', 'id')) {

                    $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, 0, false, MUST_EXIST);
                    $utilityman = new mod_surveypro_utility($cm, $surveypro);

                    foreach ($submissions as $submission) {
                        // Third step: delete each selected submission.
                        $utilityman->delete_submissions(array('id' => $submission->id));
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Returns an array of users who are participanting in this surveypro
 *
 * Must return an array of users who are participants for a given instance
 * of surveypro. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $surveyproid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function surveypro_get_participants($surveyproid) {
    global $DB;

    $sql = 'SELECT DISTINCT s.userid as id
            FROM {surveypro_submission} s
            WHERE s.surveyproid = :surveyproid';
    return $DB->get_records_sql($sql, array('surveyproid' => $surveyproid));
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function surveypro_get_extra_capabilities() {
    return array('moodle/site:config', 'moodle/site:accessallgroups');
}

// Gradebook API.

/**
 * Checks if a scale is being used by an surveypro.
 *
 * This is used by the backup code to decide whether to back up a scale
 * @param int $surveyproid
 * @param int $scaleid
 * @return boolean True if the scale is used by the surveypro
 */
function surveypro_scale_used($surveyproid, $scaleid) {
}

/**
 * Checks if scale is being used by any instance of surveypro
 *
 * This is used to find out if scale used anywhere
 * @param int $scaleid
 * @return boolean True if the scale is used by any surveypro
 */
function surveypro_scale_used_anywhere($scaleid) {
}

/**
 * Creates or updates grade item for the give surveypro instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $surveypro instance object with extra cmidnumber and modname property
 * @return void
 */
function surveypro_grade_item_update(stdClass $surveypro) {
}

/**
 * Update surveypro grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $surveypro instance object with extra cmidnumber and modname property
 * @param int $userid Update grade of specific user only, 0 means all participants
 * @return void
 */
function surveypro_update_grades(stdClass $surveypro, $userid = 0) {
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function surveypro_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the surveypro file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function surveypro_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $DB;

    require_login($course, true, $cm);
    if (!$surveypro = $DB->get_record('surveypro', array('id' => $cm->instance))) {
        send_file_not_found();
    }

    $fs = get_file_storage();

    // For toplevelfileareas $args come without itemid, just the path.
    // Other fileareas come with both itemid and path.
    $toplevelfilearea = ($filearea == SURVEYPRO_THANKSHTMLFILEAREA);
    $toplevelfilearea = $toplevelfilearea || ($filearea == SURVEYPRO_STYLEFILEAREA);
    $toplevelfilearea = $toplevelfilearea || ($filearea == SURVEYPRO_TEMPLATEFILEAREA);
    $itemid = ($toplevelfilearea) ? 0 : (int)array_shift($args);
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_surveypro/$filearea/$itemid/$relativepath";

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true); // Download MUST be forced - security!

    return false;
}

// Navigation API.

/**
 * extend a surveypro navigation settings
 *
 * @param settings_navigation $settings
 * @param navigation_node $navref
 * @return void
 */
function surveypro_extend_settings_navigation(settings_navigation $settings, navigation_node $navref) {
    global $PAGE, $DB;

    if (!$cm = $PAGE->cm) {
        return;
    }

    $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

    $utilityman = new mod_surveypro_utility($cm, $surveypro);
    $nodeurl = $utilityman->get_common_links_url(SURVEYPRO_BLOCK);

    $paramurlbase = array('s' => $cm->instance);

    // SURVEYPRO_TABLAYOUT.
    if ($nodeurl['tab_layout']['container']) {
        // Parent.
        $nodelabel = get_string('tablayoutname', 'mod_surveypro');
        $navnode = $navref->add($nodelabel,  null, navigation_node::TYPE_CONTAINER);

        // Children.
        if ($elementurl = $nodeurl['tab_layout']['preview']) {
            $nodelabel = get_string('tabitemspage1', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_layout']['manage']) {
            $nodelabel = get_string('tabitemspage2', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_layout']['validate']) {
            $nodelabel = get_string('tabitemspage4', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO_TABSUBMISSIONS.
    if ($nodeurl['tab_submissions']['container']) {
        // Parent.
        $nodelabel = get_string('tabsubmissionsname', 'mod_surveypro');
        $navnode = $navref->add($nodelabel,  null, navigation_node::TYPE_CONTAINER);

        // Children.
        if ($elementurl = $nodeurl['tab_submissions']['import']) { // Import.
            $nodelabel = get_string('tabsubmissionspage8', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_submissions']['export']) { // Export.
            $nodelabel = get_string('tabsubmissionspage9', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO_TABUTEMPLATES.
    if ($nodeurl['tab_utemplate']['container']) {
        // Parent.
        $nodelabel = get_string('tabutemplatename', 'mod_surveypro');
        $navnode = $navref->add($nodelabel, null, navigation_node::TYPE_CONTAINER);

        // Children.
        if ($elementurl = $nodeurl['tab_utemplate']['manage']) {
            $nodelabel = get_string('tabutemplatepage1', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_utemplate']['save']) {
            $nodelabel = get_string('tabutemplatepage2', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_utemplate']['import']) {
            $nodelabel = get_string('tabutemplatepage3', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_utemplate']['apply']) {
            $nodelabel = get_string('tabutemplatepage4', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO_TABMTEMPLATES.
    if ($nodeurl['tab_mtemplate']['container']) {
        // Parent.
        $nodelabel = get_string('tabmtemplatename', 'mod_surveypro');
        $navnode = $navref->add($nodelabel, null, navigation_node::TYPE_CONTAINER);

        // Children.
        if ($elementurl = $nodeurl['tab_mtemplate']['save']) {
            $nodelabel = get_string('tabmtemplatepage1', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
        if ($elementurl = $nodeurl['tab_mtemplate']['apply']) {
            $nodelabel = get_string('tabmtemplatepage2', 'mod_surveypro');
            $navnode->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO REPORTS.
    $context = context_module::instance($cm->id);
    if ($surveyproreportlist = get_plugin_list('surveyproreport')) {
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $context);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $context);

        $icon = new pix_icon('i/report', '', 'moodle');
        foreach ($surveyproreportlist as $reportname => $reportpath) {
            $classname = 'surveyproreport_'.$reportname.'_report';
            $reportman = new $classname($cm, $context, $surveypro);

            $allowedtemplates = $reportman->allowed_templates();

            if ((!$allowedtemplates) || in_array($surveypro->template, $allowedtemplates)) {
                if ($canaccessreports || ($reportman->has_student_report() && $canaccessownreports)) {
                    if ($reportman->report_apply()) {
                        if (!isset($reportnode)) {
                            $nodelabel = get_string('report');
                            $reportnode = $navref->add($nodelabel, null, navigation_node::TYPE_CONTAINER);
                        }
                        if ($childreports = $reportman->has_childreports($canaccessreports)) {
                            $nodelabel = get_string('pluginname', 'surveyproreport_'.$reportname);
                            $childnode = $reportnode->add($nodelabel, null, navigation_node::TYPE_CONTAINER);
                            surveypro_add_report_link($surveypro->template, $childreports, $childnode, $reportname, $icon);
                        } else {
                            $url = new moodle_url('/mod/surveypro/report/'.$reportname.'/view.php', $paramurlbase);
                            $nodelabel = get_string('pluginname', 'surveyproreport_'.$reportname);
                            $reportnode->add($nodelabel, $url, navigation_node::TYPE_SETTING, null, null, $icon);
                        }
                    }
                }
            }
        }
    }
}

/**
 * Recursive function to add links for reports nested as much times as wanted
 *
 * Uncomment lines of has_childreports method in the surveyproreport_colles_report class of report/colles/classes/report.php file
 * to see this function in action.
 *
 * @param string $templatename
 * @param array $childreports
 * @param navigation_node $childnode
 * @param string $reportname
 * @param pix_icon $icon
 * @return void
 */
function surveypro_add_report_link($templatename, $childreports, $childnode, $reportname, $icon) {
    global $PAGE;

    foreach ($childreports as $reportkey => $reportparams) {
        $label = get_string($reportkey, 'surveyprotemplate_'.$templatename);
        if (is_array(reset($reportparams))) { // If the first element of $reportparams is an array.
            $childnode = $childnode->add($label, null, navigation_node::TYPE_CONTAINER);
            surveypro_add_report_link($templatename, $reportparams, $childnode, $reportname, $icon);
        } else {
            $reportparams['s'] = $PAGE->cm->instance;
            $url = new moodle_url('/mod/surveypro/report/'.$reportname.'/view.php', $reportparams);
            $childnode->add($label, $url, navigation_node::TYPE_SETTING, null, null, $icon);
        }
    }
}

/**
 * Extends the global navigation tree by adding surveypro nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the surveypro module instance
 * @param stdClass $course
 * @param stdClass $surveypro
 * @param cm_info $cm
 * @return void
 */
function surveypro_extend_navigation(navigation_node $navref, stdClass $course, stdClass $surveypro, cm_info $cm) {
    $utilityman = new mod_surveypro_utility($cm, $surveypro);
    $nodeurl = $utilityman->get_common_links_url(SURVEYPRO_BLOCK);

    // $currentgroup = groups_get_activity_group($cm);
    // $groupmode = groups_get_activity_groupmode($cm, $COURSE);

    // SURVEYPRO_TABSUBMISSIONS.
    // Children only.
    if ($elementurl = $nodeurl['tab_submissions']['cover']) {
        $nodelabel = get_string('tabsubmissionspage1', 'mod_surveypro');
        $navref->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
    }
    if ($elementurl = $nodeurl['tab_submissions']['responses']) {
        $nodelabel = get_string('tabsubmissionspage2', 'mod_surveypro');
        $navref->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
    }
    if ($elementurl = $nodeurl['tab_submissions']['search']) {
        $nodelabel = get_string('tabsubmissionspage6', 'mod_surveypro');
        $navref->add($nodelabel, $elementurl, navigation_node::TYPE_SETTING);
    }
}

// CUSTOM SURVEYPRO API.

/**
 * Is re-captcha enabled at site level
 *
 * @return boolean true if true
 */
function surveypro_site_recaptcha_enabled() {
    global $CFG;

    return !empty($CFG->recaptchapublickey) && !empty($CFG->recaptchaprivatekey);
}

/**
 * surveypro_get_plugin_list
 *
 * @param string $plugintype
 * @param bool $includetype
 * @param bool $count
 * @return void
 */
function surveypro_get_plugin_list($plugintype=null, $includetype=false, $count=false) {
    $plugincount = 0;
    $fieldplugins = array();
    $formatplugins = array();

    if ($plugintype == SURVEYPRO_TYPEFIELD || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(get_plugin_list('surveypro'.SURVEYPRO_TYPEFIELD));
        } else {
            $fieldplugins = core_component::get_plugin_list('surveypro'.SURVEYPRO_TYPEFIELD);
            if (!empty($includetype)) {
                foreach ($fieldplugins as $k => $v) {
                    if (!get_config('surveyprofield_'.$k, 'disabled')) {
                        $fieldplugins[$k] = SURVEYPRO_TYPEFIELD.'_'.$k;
                    } else {
                        unset($fieldplugins[$k]);
                    }
                }
                $fieldplugins = array_flip($fieldplugins);
            } else {
                foreach ($fieldplugins as $k => $v) {
                    if (!get_config('surveyprofield_'.$k, 'disabled')) {
                        $fieldplugins[$k] = $k;
                    } else {
                        unset($fieldplugins[$k]);
                    }
                }
            }
        }
    }
    if ($plugintype == SURVEYPRO_TYPEFORMAT || is_null($plugintype)) {
        if ($count) {
            $plugincount += count(core_component::get_plugin_list('surveypro'.SURVEYPRO_TYPEFORMAT));
        } else {
            $formatplugins = core_component::get_plugin_list('surveypro'.SURVEYPRO_TYPEFORMAT);
            if (!empty($includetype)) {
                foreach ($formatplugins as $k => $v) {
                    if (!get_config('surveyproformat_'.$k, 'disabled')) {
                        $formatplugins[$k] = SURVEYPRO_TYPEFORMAT.'_'.$k;
                    } else {
                        unset($formatplugins[$k]);
                    }
                }
                $formatplugins = array_flip($formatplugins);
            } else {
                foreach ($formatplugins as $k => $v) {
                    if (!get_config('surveyproformat_'.$k, 'disabled')) {
                        $formatplugins[$k] = $k;
                    } else {
                        unset($formatplugins[$k]);
                    }
                }
            }
        }
    }

    if ($count) {
        return $plugincount;
    } else {
        $pluginlist = $fieldplugins + $formatplugins;
        asort($pluginlist);
        return $pluginlist;
    }
}

/**
 * surveypro_fetch_items_seeds
 *
 * @param int $surveyproid
 * @param bool $visibleonly
 * @param bool $canaccessreserveditems
 * @param bool $searchform
 * @param string $type
 * @param int $formpage
 * @param bool $pagebreak
 * @return array($where, $params)
 */
function surveypro_fetch_items_seeds($surveyproid, $visibleonly=true, $canaccessreserveditems=false,
                                     $searchform=false, $type=false, $formpage=false, $pagebreak=false) {
    $params = array();
    $conditions = array();

    $conditions[] = 'surveyproid = :surveyproid';
    $params['surveyproid'] = (int)$surveyproid;

    if ($visibleonly) {
        $conditions[] = 'hidden = :hidden';
        $params['hidden'] = 0;
    }

    if (!$canaccessreserveditems) {
        $conditions[] = 'reserved = :reserved';
        $params['reserved'] = 0;
    }

    if ($searchform) {
        $conditions[] = 'insearchform = :insearchform';
        $params['insearchform'] = 1;
    }

    if ($type) {
        $conditions[] = 'type = :type';
        $params['type'] = $type;
    }

    if ($formpage) {
        $conditions[] = 'formpage = :formpage';
        $params['formpage'] = $formpage;
    }

    if (!$pagebreak) {
        $conditions[] = 'plugin <> :plugin';
        $params['plugin'] = 'pagebreak';
    }

    $where = '( ('.implode(') AND (', $conditions).') )';

    return array($where, $params);
}

/**
 * surveypro_get_view_actions
 *
 * @return array('view', 'view all')
 */
function surveypro_get_view_actions() {
    return array('view', 'view all');
}

/**
 * surveypro_get_post_actions
 *
 * @return array('add', 'update')
 */
function surveypro_get_post_actions() {
    return array('add', 'update');
}

/**
 * This gets an array with default options for the editor
 *
 * @return array the options
 */
function surveypro_get_editor_options() {
    return array('trusttext' => true, 'subdirs' => false, 'maxfiles' => EDITOR_UNLIMITED_FILES);
}

/**
 * surveypro_get_user_style_options
 *
 * @return $filemanageroptions
 */
function surveypro_get_user_style_options() {
    $filemanageroptions = array();
    $filemanageroptions['accepted_types'] = '.css';
    $filemanageroptions['maxbytes'] = 0;
    $filemanageroptions['maxfiles'] = -1;
    $filemanageroptions['mainfile'] = true;
    $filemanageroptions['subdirs'] = false;

    return $filemanageroptions;
}

/**
 * Obtains the automatic completion state for this module based on any conditions
 * in surveypro settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function surveypro_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    // Get surveypro details.
    $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

    // If completion option is enabled, evaluate it and return true/false.
    if ($surveypro->completionsubmit) {
        $params = array('surveyproid' => $cm->instance, 'userid' => $userid, 'status' => SURVEYPRO_STATUSCLOSED);
        $submissioncount = $DB->count_records('surveypro_submission', $params);
        return ($submissioncount >= $surveypro->completionsubmit);
    } else {
        // Completion option is not enabled so just return $type.
        return $type;
    }
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype The type of item
 * @param int $id The ID of the file/item to modify
 * @param mixed $newvalue The new value to set
 * @return \core\output\inplace_editable
 */
function surveypro_inplace_editable($itemtype, $id, $newvalue) {
    $classname = 'mod_surveypro_'.$itemtype;

    return $classname::update($id, $newvalue);
}

/**
 * Load the class of the specified item
 *
 * @param object $cm
 * @param object $surveypro
 * @param int $itemid
 * @param string $type
 * @param string $plugin
 * @param bool $getparentcontent
 * @return $item object
 */
function surveypro_get_item($cm, $surveypro, $itemid=0, $type='', $plugin='', $getparentcontent=false) {
    global $CFG, $DB;

    if (!empty($itemid)) {
        $itemseed = $DB->get_record('surveypro_item', array('id' => $itemid), 'surveyproid, type, plugin', MUST_EXIST);
        if ($cm->instance != $itemseed->surveyproid) {
            $message = 'Mismatch between passed itemid ('.$itemid.') and corresponding cm->instance ('.$cm->instance.')';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    if (empty($type) || empty($plugin)) {
        if (empty($itemid)) {
            $message = 'Unexpected empty($itemid)';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    } else {
        if (isset($itemseed)) {
            if ($type != $itemseed->type) {
                $message = 'Mismatch between passed type ('.$type.') and found type ('.$itemseed->type.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
            if ($plugin != $itemseed->plugin) {
                $message = 'Mismatch between passed plugin ('.$plugin.') and found plugin ('.$itemseed->plugin.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
    }

    $classname = 'surveypro'.$type.'_'.$plugin.'_'.$type;
    $item = new $classname($cm, $surveypro, $itemid, $getparentcontent);

    return $item;
}

