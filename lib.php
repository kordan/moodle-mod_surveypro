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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_surveypro\utility_layout;

defined('MOODLE_INTERNAL') || die();

/**
 * Some constants
 */
define('SURVEYPRO_VALUELABELSEPARATOR', '::');
define('SURVEYPRO_OTHERSEPARATOR'     , '->');

/**
 * ITEM TYPES
 */
define('SURVEYPRO_TYPEFIELD' , 'field');
define('SURVEYPRO_TYPEFORMAT', 'format');

/**
 * KIND OF SUBMISSION
 */
define('SURVEYPRO_ONESHOTNOEMAIL',     0);
define('SURVEYPRO_ONESHOTEMAIL',       1);
define('SURVEYPRO_PAUSERESUMENOEMAIL', 2);
define('SURVEYPRO_PAUSERESUMEEMAIL',   3);

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
define('SURVEYPRO_RESPONSETOPDF'     , '9');

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
// MODE in which the USER FORM is going to be used.
define('SURVEYPRO_NOMODE'         , '0');
define('SURVEYPRO_NEWRESPONSEMODE', '1');
define('SURVEYPRO_EDITMODE'       , '2');
define('SURVEYPRO_READONLYMODE'   , '3');
define('SURVEYPRO_PREVIEWMODE'    , '4');

// VIEW in ITEM section.
define('SURVEYPRO_NEWITEM'        , '5');
define('SURVEYPRO_EDITITEM'       , '6');
define('SURVEYPRO_CHANGEORDERASK' , '7');

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
define('SURVEYPRO_ITEMPREFIX'       , 'surveypro');
define('SURVEYPRO_PLACEHOLDERPREFIX', 'placeholder');
define('SURVEYPRO_DONTSAVEMEPREFIX' , 'placeholder');

/**
 * INVITE, NO-ANSWER AND IGNOREME VALUE
 */
// Since the very first beginning of the development.
// define('SURVEYPRO_INVITATIONVALUE', '__invItat10n__'); // User should never guess it.
// define('SURVEYPRO_NOANSWERVALUE',   '__n0__Answer__'); // User should never guess it.
// define('SURVEYPRO_IGNOREMEVALUE',   '__1gn0rE__me__'); // User should never guess it.

// Starting from version 2015090901.
define('SURVEYPRO_INVITEVALUE'    , '@@_INVITE_@@'); // User should never guess it.
define('SURVEYPRO_NOANSWERVALUE'  , '@@_NOANSW_@@'); // User should never guess it.
define('SURVEYPRO_IGNOREMEVALUE'  , '@@_IGNORE_@@'); // User should never guess it.
define('SURVEYPRO_EXPNULLVALUE'   , '@@_NULVAL_@@'); // User should never guess it.
define('SURVEYPRO_IMPFORMATSUFFIX', '@@_FORMAT_@@'); // User should never guess it.

/**
 * ITEM ADJUSTMENTS
 */
define('SURVEYPRO_VERTICAL'  , 0);
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
define('SURVEYPRO_THANKSPAGEFILEAREA' , 'thankshtml');
define('SURVEYPRO_ITEMCONTENTFILEAREA', 'itemcontent');

/**
 * FIRENDLY FORMAT
 */
define('SURVEYPRO_FRIENDLYFORMAT', -1);

/**
 * POSITION OF THE QUESTION CONTENT IN THE ITEM
 */
define('SURVEYPRO_POSITIONLEFT'     , 0);
define('SURVEYPRO_POSITIONTOP'      , 1);
define('SURVEYPRO_POSITIONFULLWIDTH', 2);

/**
 * STATUS OF CONDITIONS OF RELATIONS
 */
define('SURVEYPRO_CONDITIONOK'        , 0);
define('SURVEYPRO_CONDITIONNEVERMATCH', 1);
define('SURVEYPRO_CONDITIONMALFORMED' , 2);

/**
 * SEMANTIC OF CONTENT RETURNED BY ITEMS
 */
define('SURVEYPRO_ITEMSRETURNSVALUES' , 0);
define('SURVEYPRO_ITEMRETURNSLABELS'  , 1);
define('SURVEYPRO_ITEMRETURNSPOSITION', 2);

/**
 * OUTPUT CONTENT
 */
define('SURVEYPRO_LABELS'    , 'labels');
define('SURVEYPRO_VALUES'    , 'values');
define('SURVEYPRO_POSITIONS' , 'positions');
define('SURVEYPRO_ITEMDRIVEN', 'itemdriven');

/**
 * DUMMY CONTENT USED AT ANSWER SAVE TIME
 */
define('SURVEYPRO_DUMMYCONTENT', '__my_dummy_content@@');

/**
 * OUTPUT OF FINAL SUBMISSION EVALUATION
 */
define('SURVEYPRO_VALIDRESPONSE'    , 0);
define('SURVEYPRO_MISSINGMANDATORY' , 1);
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

/**
 * SURVEYPRO EVENT TYPE
 */
define('SURVEYPRO_EVENT_TYPE_OPEN', 'open');

// Moodle core API.

require_once($CFG->dirroot.'/lib/formslib.php'); // Needed by unittest.
require_once($CFG->dirroot.'/mod/surveypro/deprecatedlib.php');
/* Do not include any libraries here! */

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
    $context = \context_module::instance($cmid);

    surveypro_pre_process_checkboxes($surveypro);
    $surveypro->timecreated = time();
    $surveypro->timemodified = time();

    $surveypro->id = $DB->insert_record('surveypro', $surveypro);

    // Manage userstyle filemanager.
    $draftitemid = $surveypro->userstyle_filemanager;
    file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);

    // Manage thankspage editor.
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankspageeditor['itemid']) {
        $surveypro->thankspage = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro',
                SURVEYPRO_THANKSPAGEFILEAREA, 0, $editoroptions, $surveypro->thankspageeditor['text']);
        $surveypro->thankspageformat = $surveypro->thankspageeditor['format'];
    }

    // Manage mailcontent editor. No embedded pictures to handle.
    $surveypro->mailcontent = $surveypro->mailcontenteditor['text'];
    $surveypro->mailcontentformat = $surveypro->mailcontenteditor['format'];

    $DB->update_record('surveypro', $surveypro);

    create_event_on_calendar($surveypro, 'open');
    create_event_on_calendar($surveypro, 'close');

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
    $context = \context_module::instance($cmid);

    $surveypro->timemodified = time();
    $surveypro->id = $surveypro->instance;

    surveypro_pre_process_checkboxes($surveypro);

    // Classes are not available here!
    // So, I can't use $utilitylayoutman->reset_items_pages();
    $whereparams = ['surveyproid' => $surveypro->id];
    $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);

    // Manage userstyle filemanager.
    if ($draftitemid = file_get_submitted_draft_itemid('userstyle_filemanager')) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);
    }

    // Manage thankspage editor.
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankspageeditor['itemid']) {
        $surveypro->thankspage = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro',
                SURVEYPRO_THANKSPAGEFILEAREA, 0, $editoroptions, $surveypro->thankspageeditor['text']);
        $surveypro->thankspageformat = $surveypro->thankspageeditor['format'];
    }

    // Manage mailcontent editor. No embedded pictures to handle.
    $surveypro->mailcontentformat = $surveypro->mailcontenteditor['format'];
    $surveypro->mailcontent = $surveypro->mailcontenteditor['text'];

    $DB->update_record('surveypro', $surveypro);

    create_event_on_calendar($surveypro, 'open');
    create_event_on_calendar($surveypro, 'close');

    return true;
}

/**
 * Create a calendar event from the newly
 * created surveypro module
 *
 * create_event_on_calendar
 *
 * @param object $surveypro instance
 * @param string $type
 * @return void
 */
function create_event_on_calendar($surveypro, $type) {
    global $CFG, $DB, $USER;

    require_once($CFG->dirroot.'/calendar/lib.php');

    $types = array('open', 'close');
    if (!in_array($type, $types)) {
        $message = 'Wrong type passed to create_event_on_calendar';
        debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message, DEBUG_DEVELOPER);
    }

    $condition = (!is_null($surveypro->timeopen) && ($type == 'open'));
    $condition = $condition || (!is_null($surveypro->timeclose) && ($type == 'close'));
    if ($condition) {
        // Create an event to show on calendar.
        $event = new stdClass(); // Event class created.

        // If the same old event has already been added to calendar, reuse it.
        $conditions = array('instance' => $surveypro->id, 'eventtype' => $type);
        if ($oldevent = $DB->get_record('event', $conditions, 'id', 'id', IGNORE_MISSING)) {
            $event->id = $oldevent->id;
            $editing = true;
        } else {
            $editing = false;
        }
        $event->courseid = $surveypro->course; // Course ID.
        $event->userid = $USER->id; // User ID creating the event.
        $event->modulename = 'surveypro'; // Module name.
        $event->instance = $surveypro->id; // Module surveypro's ID.
        $event->visible = instance_is_visible('surveypro', $surveypro); // Set visibility for users.
        $event->eventtype = $type; // Calendar's event type.
        if ($editing) {
            $event->timemodified = time(); // Event modification date.
        }

        if ($type == 'open') {
            $event->timestart = $surveypro->timeopen; // Event's start date.
            $event->name = get_string('calendar_open_time', 'mod_surveypro', $surveypro->name);
            $event->description = get_string('calendar_open_description', 'mod_surveypro', $surveypro->name);
        } else {
            $event->timestart = $surveypro->timeclose; // Event's close date.
            $event->name = get_string('calendar_close_time', 'mod_surveypro', $surveypro->name);
            $event->description = get_string('calendar_close_description', 'mod_surveypro', $surveypro->name);
        }

        // Add the event to calendar.
        calendar_event::create($event);
    }
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
    $checkboxes = ['newpageforchild', 'neverstartedemail', 'keepinprogress', 'history', 'anonymous', 'captcha'];

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

    if (!$surveypro = $DB->get_record('surveypro', ['id' => $id])) {
        return false;
    }

    $status = true;

    // Now get rid of all files.
    $fs = get_file_storage();
    if ($cm = get_coursemodule_from_instance('surveypro', $surveypro->id)) {
        $context = \context_module::instance($cm->id);
        $fs->delete_area_files($context->id);
    }

    $whereparams = ['surveyproid' => $surveypro->id];

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
    $types = [SURVEYPRO_TYPEFIELD, SURVEYPRO_TYPEFORMAT];
    foreach ($types as $type) {
        $pluginlist = surveypro_get_plugin_list($type);

        // Delete all associated item<<$type>>_<<plugin>>.
        foreach ($pluginlist as $plugin) {
            $tablename = 'surveypro'.$type.'_'.$plugin;
            if ($DB->get_manager()->table_exists($tablename)) {
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
    }

    // Delete all associated surveypro_items.
    if (!$DB->delete_records('surveypro_item', ['surveyproid' => $surveypro->id])) {
        $status = false;
    }

    // Finally, delete the surveypro record.
    if (!$DB->delete_records('surveypro', ['id' => $surveypro->id])) {
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
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
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
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_COMMUNICATION;
        default:
            return null;
    }
}

/**
 * Print the grade information for the surveypro for this user.
 *
 * @param \stdClass $course
 * @param \stdClass $user
 * @param \stdClass $coursemodule
 * @param \stdClass $surveypro
 */
function surveypro_user_outline($course, $user, $coursemodule, $surveypro) {
    $return = new \stdClass();
    $return->time = 0;
    $return->info = '';
    return $return;
}

/**
 * Prints a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param \stdClass $course the current course record
 * @param \stdClass $user the record of the user we are generating report for
 * @param cm_info $mod course module info
 * @param \stdClass $surveypro the module instance record
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
 * @param \stdClass $activity
 * @param int $courseid
 * @param bool $detail
 * @param array $modnames
 */
function surveypro_print_recent_mod_activity($activity, $courseid, $detail, $modnames) {
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

    return $DB->get_records_sql($sql, ['surveyproid' => $surveyproid]);
}

/**
 * Returns all other caps used in the module
 *
 * @return array
 */
function surveypro_get_extra_capabilities() {
    return ['moodle/site:config', 'moodle/site:accessallgroups'];
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
 * @param \stdClass $surveypro instance object with extra cmidnumber and modname property
 * @return void
 */
function surveypro_grade_item_update(\stdClass $surveypro) {
}

/**
 * Update surveypro grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param \stdClass $surveypro instance object with extra cmidnumber and modname property
 * @param int $userid Update grade of specific user only, 0 means all participants
 * @return void
 */
function surveypro_update_grades(\stdClass $surveypro, $userid = 0) {
}

// File API.

/**
 * Lists all browsable file areas
 *
 * @param \stdClass $course course object
 * @param \stdClass $cm course module object
 * @param \stdClass $context context object
 * @return array
 */
function surveypro_get_file_areas($course, $cm, $context) {
    return array();
}

/**
 * Serves the files from the surveypro file areas
 *
 * @param \stdClass $course the course object
 * @param \stdClass $cm the course module object
 * @param \stdClass $context context object
 * @param string $filearea the name of the file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file is not found, just send the file otherwise returning nothing
 */
function surveypro_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB;

    require_login($course, true, $cm);
    if (!$surveypro = $DB->get_record('surveypro', ['id' => $cm->instance])) {
        send_file_not_found();
    }

    $fs = get_file_storage();

    // For toplevelfileareas $args come without itemid, just the path.
    // Other fileareas come with both itemid and path.
    $toplevelfilearea = ($filearea == SURVEYPRO_THANKSPAGEFILEAREA);
    $toplevelfilearea = $toplevelfilearea || ($filearea == SURVEYPRO_STYLEFILEAREA);
    $toplevelfilearea = $toplevelfilearea || ($filearea == SURVEYPRO_TEMPLATEFILEAREA);
    $itemid = ($toplevelfilearea) ? 0 : (int)array_shift($args);
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/mod_surveypro/$filearea/$itemid/$relativepath";

    $file = $fs->get_file_by_hash(sha1($fullpath));
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true); // Download MUST be forced - security!
}

// Navigation API.

/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings
 * @param navigation_node $surveypronode
 * @return void
 */
function surveypro_extend_settings_navigation(settings_navigation $settings, navigation_node $surveypronode) {
    global $PAGE, $DB;

    if (!$cm = $PAGE->cm) {
        return;
    }

    $paramurl = ['s' => $cm->instance];

    // First tab; "Surveypro".
    $condition = surveypro_get_link_visibility_condition('surveypro');
    if ($condition) {
        $label = get_string('modulename', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/view.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
        // Do not add it. It is added by moodle core with the modulename label.
        $navnode->set_show_in_secondary_navigation(false);
    }

    // Layout.
    $condition = surveypro_get_link_visibility_condition('layout');
    if ($condition) {
        $label = get_string('layout', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Reports.
    $condition = surveypro_get_link_visibility_condition('reports');
    if ($condition) {
        $label = get_string('reports', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/reports.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Tools.
    $condition = surveypro_get_link_visibility_condition('tools');
    if ($condition) {
        $label = get_string('tools', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/tools.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // User templates. (Maybe "User presets" is better?).
    $condition = surveypro_get_link_visibility_condition('utemplates');
    if ($condition) {
        $label = get_string('utemplate', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Master templates. (Maybe "Master presets" is better?).
    $condition = surveypro_get_link_visibility_condition('mtemplates');
    if ($condition) {
        $label = get_string('mtemplate', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/mtemplates.php', $paramurl);
        $navnode = $surveypronode->add($label, $url, navigation_node::TYPE_SETTING);
    }
}

/**
 * Extends the global navigation tree in the Navigation block
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navigation An object representing the navigation tree node of the surveypro module instance
 * @param \stdClass $course
 * @param \stdClass $surveypro
 * @param cm_info $cm
 * @return void
 */
function surveypro_extend_navigation(navigation_node $navigation, \stdClass $course, \stdClass $surveypro, cm_info $cm) {
    // Surveypro.
    $condition = surveypro_get_link_visibility_condition('surveypro');
    if ($condition) {
        $label = get_string('modulename', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $surveypro->id, 'section' => 'cover']);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Layout.
    $condition = surveypro_get_link_visibility_condition('layout');
    if ($condition) {
        $label = get_string('layout', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/layout.php', ['s' => $cm->instance, 'section' => 'itemslist']);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Report.
    $condition = surveypro_get_link_visibility_condition('reports');
    if ($condition) {
        $label = get_string('reports', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/layout_itemlist.php', ['s' => $cm->instance, 'section' => 'itemlist']);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Tools.
    $condition = surveypro_get_link_visibility_condition('tools');
    if ($condition) {
        $label = get_string('tools', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/tools.php', ['s' => $cm->instance, 'section' => 'export']);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // User templates. (Maybe "User presets" is better?).
    $condition = surveypro_get_link_visibility_condition('utemplates');
    if ($condition) {
        $label = get_string('utemplate', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/upreset_manage.php', ['s' => $cm->instance]);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }

    // Master templates. (Maybe "Master presets" is better?).
    $condition = surveypro_get_link_visibility_condition('mtemplates');
    if ($condition) {
        $label = get_string('mtemplate', 'mod_surveypro');
        $url = new \moodle_url('/mod/surveypro/upreset_manage.php', ['s' => $cm->instance]);
        $navigation->add($label, $url, navigation_node::TYPE_SETTING);
    }
}

// CUSTOM SURVEYPRO API.

/**
 * Is re-captcha enabled at site level
 *
 * @param string $linkid
 * @return boolean true if the link can be added to blocks. False otherwise.
 */
function surveypro_get_link_visibility_condition($linkid) {
    global $PAGE, $DB;

    if (!$cm = $PAGE->cm) {
        return;
    }

    $context = \context_module::instance($cm->id);

    $paramurl = ['s' => $cm->instance];

    switch ($linkid) {
        case 'surveypro':
            $condition = has_capability('mod/surveypro:submit', $context);
            break;
        case 'layout':
            $condition = has_capability('mod/surveypro:manageitems', $context);
            break;
        case 'reports':
            $condition = has_capability('mod/surveypro:accessreports', $context);
            break;
        case 'tools':
            $canimportresponses = has_capability('mod/surveypro:importresponses', $context);
            $canexportresponses = has_capability('mod/surveypro:exportresponses', $context);

            $condition = ($canimportresponses || $canexportresponses);
            break;
        case 'utemplates':
            $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $context);
            $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);

            $condition = ($canmanageusertemplates && empty($surveypro->template));
            break;
        case 'mtemplates':
            $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $context);
            $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $context);
            $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);

            $utilitylayoutman = new utility_layout($cm, $surveypro);
            $hassubmissions = $utilitylayoutman->has_submissions();

            $riskyediting = ($surveypro->riskyeditdeadline > time());

            $condition = false;
            $condition = $condition || ($cansavemastertemplates && empty($surveypro->template));
            $condition = $condition || ($canapplymastertemplates && (!$hassubmissions || $riskyediting));

            break;
        default:
            $message = 'Unexpected $linkid = '.$linkid;
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
    }

    return $condition;
}

/**
 * Can the "Surveypro" link be added to Navigation block and to Administration block?
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
 * @return [$where, $params]
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

    return [$where, $params];
}

/**
 * surveypro_get_view_actions
 *
 * @return ['view', 'view all']
 */
function surveypro_get_view_actions() {
    return ['view', 'view all'];
}

/**
 * surveypro_get_post_actions
 *
 * @return ['add', 'update']
 */
function surveypro_get_post_actions() {
    return ['add', 'update'];
}

/**
 * This gets an array with default options for the editor
 *
 * @return array the options
 */
function surveypro_get_editor_options() {
    return ['trusttext' => true, 'subdirs' => false, 'maxfiles' => EDITOR_UNLIMITED_FILES];
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
 * cut down a string and close it with ellipsis
 *
 * @param string $plainstring
 * @param int $maxlength
 *
 * @return void
 */
function surveypro_cutdownstring($plainstring, $maxlength=60) {
    if (\core_text::strlen($plainstring) > $maxlength) {
        $ellipsis = '...';
        $cutlength = $maxlength - \core_text::strlen($ellipsis);
        $plainstring = \core_text::substr($plainstring, 0, $cutlength).$ellipsis;
    }

    return $plainstring;
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
    $classname = 'mod_surveypro\local\ipe\\'.$itemtype;

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
        $itemseed = $DB->get_record('surveypro_item', ['id' => $itemid], 'surveyproid, type, plugin', MUST_EXIST);
        if ($cm->instance != $itemseed->surveyproid) {
            $message = 'Mismatch between passed itemid ('.$itemid.') and corresponding cm->instance ('.$cm->instance.')';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message, DEBUG_DEVELOPER);
        }
    }

    if (empty($type) || empty($plugin)) {
        if (empty($itemid)) {
            $message = 'Unexpected empty($itemid)';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message, DEBUG_DEVELOPER);
        }

        $type = $itemseed->type;
        $plugin = $itemseed->plugin;
    } else {
        if (isset($itemseed)) {
            if ($type != $itemseed->type) {
                $message = 'Mismatch between passed type ('.$type.') and found type ('.$itemseed->type.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message, DEBUG_DEVELOPER);
            }
            if ($plugin != $itemseed->plugin) {
                $message = 'Mismatch between passed plugin ('.$plugin.') and found plugin ('.$itemseed->plugin.')';
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message, DEBUG_DEVELOPER);
            }
        }
    }

    $classname = 'surveypro'.$type.'_'.$plugin.'\item';
    $item = new $classname($cm, $surveypro, $itemid, $getparentcontent);

    return $item;
}

/**
 * Add a get_coursemodule_info function in case any database type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param \stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function surveypro_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionsubmit, timeopen, timeclose';
    if (!$surveyprodetails = $DB->get_record('surveypro', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $surveyprodetails->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('surveypro', $surveyprodetails, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionentries'] = $surveyprodetails->completionsubmit;
    }
    // Other properties that may be used in calendar or on dashboard.
    if ($surveyprodetails->timeopen) {
        $result->customdata['timeavailablefrom'] = $surveyprodetails->timeopen;
    }
    if ($surveyprodetails->timeclose) {
        $result->customdata['timeavailableto'] = $surveyprodetails->timeclose;
    }

    return $result;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|\stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_surveypro_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules']) || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionentries':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionsubmitdesc', 'surveypro', $val);
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}
