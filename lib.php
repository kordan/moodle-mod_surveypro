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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Some constants
define('SURVEYPRO_MAX_ENTRIES'        , 50);
define('SURVEYPRO_VALUELABELSEPARATOR', '::');
define('SURVEYPRO_OTHERSEPARATOR'     , '->');

// to change tabs order, just exchange numbers if the following lines
define('SURVEYPRO_TABITEMS'      , 1);
define('SURVEYPRO_TABSUBMISSIONS', 2);
define('SURVEYPRO_TABUTEMPLATES' , 3);
define('SURVEYPRO_TABMTEMPLATES' , 4);

// PAGES
    // ITEMS PAGES
    define('SURVEYPRO_ITEMS_PREVIEW' , 1);
    define('SURVEYPRO_ITEMS_MANAGE'  , 2);
    define('SURVEYPRO_ITEMS_SETUP'   , 3);
    define('SURVEYPRO_ITEMS_VALIDATE', 4);

    // SUBMISSIONS PAGES
    define('SURVEYPRO_SUBMISSION_CPANEL'  , 1);
    define('SURVEYPRO_SUBMISSION_MANAGE'  , 2);
    define('SURVEYPRO_SUBMISSION_INSERT'  , 3);
    define('SURVEYPRO_SUBMISSION_EDIT'    , 4);
    define('SURVEYPRO_SUBMISSION_READONLY', 5);
    define('SURVEYPRO_SUBMISSION_SEARCH'  , 6);
    define('SURVEYPRO_SUBMISSION_REPORT'  , 7);
    define('SURVEYPRO_SUBMISSION_IMPORT'  , 8);
    define('SURVEYPRO_SUBMISSION_EXPORT'  , 9);

    // USER TEMPLATES PAGES
    define('SURVEYPRO_UTEMPLATES_MANAGE', 1);
    define('SURVEYPRO_UTEMPLATES_BUILD' , 2);
    define('SURVEYPRO_UTEMPLATES_IMPORT', 3);
    define('SURVEYPRO_UTEMPLATES_APPLY' , 4);

    // MASTER TEMPLATES PAGES
    define('SURVEYPRO_MTEMPLATES_BUILD', 1);
    define('SURVEYPRO_MTEMPLATES_APPLY', 2);

// ITEM TYPES
define('SURVEYPRO_TYPEFIELD' , 'field');
define('SURVEYPRO_TYPEFORMAT', 'format');

// ACTIONS
    define('SURVEYPRO_NOACTION'          , '0');

    // ITEM MANAGEMENT section
    define('SURVEYPRO_CHANGEORDER'       , '1');
    define('SURVEYPRO_HIDEITEM'          , '2');
    define('SURVEYPRO_SHOWITEM'          , '3');
    define('SURVEYPRO_DELETEITEM'        , '4');
    define('SURVEYPRO_DROPMULTILANG'     , '5');
    define('SURVEYPRO_REQUIREDOFF'       , '6');
    define('SURVEYPRO_REQUIREDON'        , '7');
    define('SURVEYPRO_CHANGEINDENT'      , '8');
    define('SURVEYPRO_ADDTOSEARCH'       , '9');
    define('SURVEYPRO_OUTOFSEARCH'       , '10');
    define('SURVEYPRO_MAKEFORALL'        , '11');
    define('SURVEYPRO_MAKELIMITED'       , '12');

    // RESPONSES section
    define('SURVEYPRO_DELETERESPONSE'    , '13');
    define('SURVEYPRO_DELETEALLRESPONSES', '14');

    // UTEMPLATE section
    define('SURVEYPRO_DELETEUTEMPLATE'   , '16');

// VIEW
    // EMPTY FORM section (User page)
    define('SURVEYPRO_NOVIEW'           , '0');
    define('SURVEYPRO_NEWRESPONSE'      , '1');
    define('SURVEYPRO_PREVIEWSURVEYFORM', '2');
    define('SURVEYPRO_EDITRESPONSE'     , '3');
    define('SURVEYPRO_READONLYRESPONSE' , '4');

    // ITEM section
    define('SURVEYPRO_EDITITEM'        , '5');
    define('SURVEYPRO_CHANGEORDERASK'  , '6');

    // RESPONSES section
    define('SURVEYPRO_RESPONSETOPDF'   , '7');

    // UTEMPLATE section
    define('SURVEYPRO_EXPORTUTEMPLATE' , '8');

// OVERFLOW
define('SURVEYPRO_LEFT_OVERFLOW' , -10);
define('SURVEYPRO_RIGHT_OVERFLOW', -20);

// SAVESTATUS
define('SURVEYPRO_NOFEEDBACK', 0);

// ITEMPREFIX
define('SURVEYPRO_ITEMPREFIX', 'surveypro');
define('SURVEYPRO_PLACEHOLDERPREFIX', 'placeholder');

// INVITATION AND NO ANSWER VALUE
define('SURVEYPRO_INVITATIONVALUE', '__invItat10n__'); // user should never guess it
define('SURVEYPRO_NOANSWERVALUE', '__n0__Answer__');   // user should never guess it
define('SURVEYPRO_IGNOREME', '__1gn0rE__me__');        // user should never guess it

// ADJUSTMENTS
define('SURVEYPRO_VERTICAL',   0);
define('SURVEYPRO_HORIZONTAL', 1);

// SURVEYPRO STATUS
define('SURVEYPRO_STATUSINPROGRESS', 1);
define('SURVEYPRO_STATUSCLOSED'    , 0);
define('SURVEYPRO_STATUSALL'       , 2);

// DOWNLOAD
define('SURVEYPRO_DOWNLOADCSV', 1);
define('SURVEYPRO_DOWNLOADTSV', 2);
define('SURVEYPRO_DOWNLOADXLS', 3);
define('SURVEYPRO_FILESBYUSER', 4);
define('SURVEYPRO_FILESBYITEM', 5);
define('SURVEYPRO_NOFIELDSSELECTED', 1);
define('SURVEYPRO_NORECORDSFOUND'  , 2);

define('SURVEYPRO_DBMULTICONTENTSEPARATOR',     ';');
define('SURVEYPRO_OUTPUTMULTICONTENTSEPARATOR', '; ');

// CONFIRMATION
define('SURVEYPRO_UNCONFIRMED',   0);
define('SURVEYPRO_CONFIRMED_YES', 1);
define('SURVEYPRO_CONFIRMED_NO' , 2);

// values for defaultvalue_option
define('SURVEYPRO_CUSTOMDEFAULT'    , 1);
define('SURVEYPRO_INVITATIONDEFAULT', 2);
define('SURVEYPRO_NOANSWERDEFAULT'  , 3);
define('SURVEYPRO_LIKELASTDEFAULT'  , 4);
define('SURVEYPRO_TIMENOWDEFAULT'   , 5);

define('SURVEYPRO_INVITATIONDBVALUE', -1);

// mandatory field
define('SURVEYPRO_REQUIREDITEM', 1);
define('SURVEYPRO_OPTIONALITEM', 0);

// fileareas
define('SURVEYPRO_STYLEFILEAREA'      , 'userstyle');
define('SURVEYPRO_TEMPLATEFILEAREA'   , 'templatefilearea');
define('SURVEYPRO_THANKSHTMLFILEAREA' , 'thankshtml');
define('SURVEYPRO_ITEMCONTENTFILEAREA', 'itemcontent');

// otheritems
define('SURVEYPRO_IGNOREITEMS'       , '1');
define('SURVEYPRO_HIDEITEMS'         , '2');
define('SURVEYPRO_DELETEALLITEMS'    , '3');
define('SURVEYPRO_DELETEVISIBLEITEMS', '4');
define('SURVEYPRO_DELETEHIDDENITEMS' , '5');

// friendly format
define('SURVEYPRO_FIRENDLYFORMAT', -1);

// position of the content
define('SURVEYPRO_POSITIONLEFT',      0);
define('SURVEYPRO_POSITIONTOP',       1);
define('SURVEYPRO_POSITIONFULLWIDTH', 2);

// relation condition format
define('SURVEYPRO_CONDITIONOK',         0);
define('SURVEYPRO_CONDITIONNEVERMATCH', 1);
define('SURVEYPRO_CONDITIONMALFORMED',  2);

// formats
define('SURVEYPRO_ITEMSRETURNSVALUES',  0);
define('SURVEYPRO_ITEMRETURNSLABELS',   1);
define('SURVEYPRO_ITEMRETURNSPOSITION', 2);

// output content
define('SURVEYPRO_LABELS', 'labels');
define('SURVEYPRO_VALUES', 'values');
define('SURVEYPRO_POSITIONS', 'positions');

// templates types
define('SURVEYPRO_MASTERTEMPLATE', 'mastertemplate');
define('SURVEYPRO_USERTEMPLATE',   'usertemplate');

// event to use for logging 2
if ($CFG->branch == '26') {
    define ('SURVEYPRO_EVENTLEVEL', 'level');
} else {
    define ('SURVEYPRO_EVENTLEVEL', 'edulevel');
}

// -----------------------------
// Moodle core API
// -----------------------------

require_once($CFG->dirroot . '/lib/formslib.php'); // <-- needed by unittest

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
    // stop working with the surveypro table (unless $surveypro->thankshtml_editor['itemid'] != 0)

    // manage userstyle filemanager
    $draftitemid = $surveypro->userstyle_filemanager;
    file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);

    // manage thankshtml editor
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankshtml_editor['itemid']) {
        $surveypro->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA,
                $surveypro->id, $editoroptions, $surveypro->thankshtml_editor['text']);
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

    surveypro_reset_items_pages($surveypro->id);

    $DB->update_record('surveypro', $surveypro);
    // stop working with the surveypro table (unless $surveypro->thankshtml_editor['itemid'] != 0)

    // manage userstyle filemanager
    if ($draftitemid = file_get_submitted_draft_itemid('userstyle_filemanager')) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0);
    }

    // manage thankshtml editor
    $editoroptions = surveypro_get_editor_options();
    if ($draftitemid = $surveypro->thankshtml_editor['itemid']) {
        $surveypro->thankshtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA,
                $surveypro->id, $editoroptions, $surveypro->thankshtml_editor['text']);
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
 * @return none
 */
function surveypro_pre_process_checkboxes($surveypro) {
    $checkboxes = array('newpageforchild', 'history', 'saveresume', 'anonymous', 'notifyteachers');
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

    $dbman = $DB->get_manager();
    $whereparams = array('surveyproid' => $surveypro->id);

    // Delete any dependent records here
    $submissions = $DB->get_records('surveypro_submission', $whereparams, '', 'id');
    $submissions = array_keys($submissions);

    // delete all associated surveypro_answer
    $DB->delete_records_list('surveypro_answer', 'submissionid', $submissions);

    // delete all associated surveypro_submission
    $DB->delete_records('surveypro_submission', $whereparams);

    // get all item_<<plugin>> and format_<<plugin>>
    $surveyprotypes = array(SURVEYPRO_TYPEFIELD, SURVEYPRO_TYPEFORMAT);
    foreach ($surveyprotypes as $surveyprotype) {
        $pluginlist = surveypro_get_plugin_list($surveyprotype);

        // delete all associated item<<$surveyprotype>>_<<plugin>>
        foreach ($pluginlist as $plugin) {
            $tablename = 'surveypro'.$surveyprotype.'_'.$plugin;
            $whereparams['plugin'] = $plugin;

            if ($deletelist = $DB->get_records('surveypro_item', $whereparams, 'id', 'id')) {
                $deletelist = array_keys($deletelist);
                $select = 'itemid IN ('.implode(',', $deletelist).')';
                $DB->delete_records_select($tablename, $select);
            }
        }
    }

    // delete all associated surveypro_items
    $DB->delete_records('surveypro_item', array('surveyproid' => $surveypro->id));

    // finally, delete the surveypro record
    $DB->delete_records('surveypro', array('id' => $surveypro->id));

    // -----------------------------
    // TODO: Am I supposed to delete files too?
    // -----------------------------

    // AREAS:
    //     SURVEYPRO_STYLEFILEAREA
    //     SURVEYPRO_TEMPLATEFILEAREA
    //     SURVEYPRO_THANKSHTMLFILEAREA

    //     SURVEYPRO_ITEMCONTENTFILEAREA <-- is this supposed to go to its delete_instance plugin?
    //     SURVEYPROFIELD_FILEUPLOAD_FILEAREA <-- is this supposed to go to its delete_instance plugin?
    //     SURVEYPROFIELD_TEXTAREAFILEAREA <-- is this supposed to go to its delete_instance plugin?

    // never delete mod_surveypro files in each AREA in $context = context_user::instance($userid);

    // always delete mod_surveypro files in each AREA in $context = context_module::instance($contextid);

    // if this is the last surveypro of this course, delete also:
    // delete mod_surveypro files in each AREA in $context = context_course::instance($contextid);

    // if this is the last surveypro of the category, delete also:
    // delete mod_surveypro files in each AREA in $context = context_coursecat::instance($contextid);

    // if this is the very last surveypro, delete also:
    // delete mod_surveypro files in each AREA in $context = context_system::instance();

    return true;
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
 * Returns a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return stdClass|null
 */
function surveypro_user_outline($course, $user, $mod, $surveypro) {
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
 * @return none, is supposed to echp directly
 */
function surveypro_user_complete($course, $user, $mod, $surveypro) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in surveypro activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function surveypro_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link surveypro_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return none adds items into $activities and increases $index
 */
function surveypro_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
}

/**
 * Prints single activity item prepared by {@see surveypro_get_recent_mod_activity()}
 *
 * @return none
 */
function surveypro_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function surveypro_cron() {
    global $DB;

    // delete too old submissions from surveypro_answer and surveypro_submission

    $permission = array(0, 1);
    // permission == 0:  saveresume is not allowed
    //     users leaved records in progress more than four hours ago...
    //     I can not believe they are still working on them so
    //     I delete records now
    // permission == 1:  saveresume is allowed
    //     these records are older than maximum allowed time delay
    $maxinputdelay = get_config('surveypro', 'maxinputdelay');
    foreach ($permission as $saveresume) {
        if (($saveresume == 1) && ($maxinputdelay == 0)) { // maxinputdelay == 0 means, please don't delete
            continue;
        }
        if ($surveypros = $DB->get_records('surveypro', array('saveresume' => $saveresume), null, 'id')) {
            $where = 'surveyproid IN ('.implode(',', array_keys($surveypros)).') AND status = :status AND timecreated < :sofar';
            $sofar = ($saveresume == 0) ? (4 * 3600) : ($maxinputdelay * 3600);
            $sofar = time() - $sofar;
            $whereparams = array('status' => SURVEYPRO_STATUSINPROGRESS, 'sofar' => $sofar);
            if ($submissionidlist = $DB->get_fieldset_select('surveypro_submission', 'id', $where, $whereparams)) {
                $DB->delete_records_list('surveypro_answer', 'submissionid', $submissionidlist);
                $DB->delete_records_list('surveypro_submission', 'id', $submissionidlist);
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
    return false;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function surveypro_get_extra_capabilities() {
    return array('moodle/site:accessallgroups', 'moodle/site:viewfullnames', 'moodle/rating:view', 'moodle/rating:viewany', 'moodle/rating:viewall', 'moodle/rating:rate');
}

// -----------------------------
// Gradebook API
// -----------------------------

/**
 * Is a given scale used by the instance of surveypro?
 *
 * This function returns if a scale is being used by one surveypro
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $surveyproid ID of an instance of this module
 * @return bool true if the scale is used by the given surveypro instance
 */
function surveypro_scale_used($surveyproid, $scaleid) {
    // global $DB;

    /* @example */
    // if ($scaleid and $DB->record_exists('surveypro', array('id' => $surveyproid, 'grade' => -$scaleid))) {
    //     return true;
    // } else {
    //     return false;
    // }
    return false;
}

/**
 * Checks if scale is being used by any instance of surveypro.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any surveypro instance
 */
function surveypro_scale_used_anywhere($scaleid) {
    global $DB;

    /* @example */
    if ($scaleid and $DB->record_exists('surveypro', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates or updates grade item for the give surveypro instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $surveypro instance object with extra cmidnumber and modname property
 * @return none
 */
// I will take comments out when I will understand why this method is called
// function surveypro_grade_item_update(stdClass $surveypro) {
//     global $CFG;
//     require_once($CFG->libdir.'/gradelib.php');
//
//     /* @example */
//     $item = array();
//     $item['itemname'] = clean_param($surveypro->name, PARAM_NOTAGS);
//     $item['gradetype'] = GRADE_TYPE_VALUE;
//     $item['grademax'] = $surveypro->grade;
//     $item['grademin'] = 0;
//
//     grade_update('mod/surveypro', $surveypro->course, 'mod', 'surveypro', $surveypro->id, 0, null, $item);
// }

/**
 * Update surveypro grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $surveypro instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return none
 */
function surveypro_update_grades(stdClass $surveypro, $userid = 0) {
    global $CFG;

    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $grades = array(); // populate array of grade objects indexed by userid

    grade_update('mod/surveypro', $surveypro->course, 'mod', 'surveypro', $surveypro->id, 0, $grades);
}

// -----------------------------
// File API
// -----------------------------

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
 * @return none this should never return to the caller
 */
function surveypro_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG;

    $debug = false;
    if ($debug) {
        $debugfile = $CFG->dataroot.'/debug_'.date("m.d.y_H:i:s").'.txt';

        $debughandle = fopen($debugfile, 'w');
        fwrite($debughandle, 'Scrivo dalla riga '.__LINE__.' di '.__FILE__."\n");
        fwrite($debughandle, '$course'."\n");
        foreach ($course as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$cm'."\n");
        foreach ($cm as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$context'."\n");
        foreach ($context as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$filearea = '.$filearea."\n");

        fwrite($debughandle, "\n".'$args'."\n");
        foreach ($args as $k => $v) {
            fwrite($debughandle, '$args['.$k.'] = '.$v."\n");
        }

        fwrite($debughandle, "\n".'$forcedownload = '.$forcedownload."\n");
    }

    $itemid = (int)array_shift($args);
    if ($debug) {
        fwrite($debughandle, "\n".'$itemid = '.$itemid."\n");
    }

    $relativepath = implode('/', $args);
    if ($debug) {
        fwrite($debughandle, "\n".'$relativepath = '.$relativepath."\n");
    }

    $fs = get_file_storage();

    $fullpath = "/$context->id/mod_surveypro/$filearea/$itemid/$relativepath";
    if ($debug) {
        fwrite($debughandle, "\n".'$fullpath = '.$fullpath."\n");
    }

    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        if ($debug) {
            fwrite($debughandle, "\n".'$file da problemi: riporterei false'."\n");
        } else {
            return false;
        }
    }

    if ($debug) {
        fclose($debughandle);
    }

    // finally send the file
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!

    return false;
}

// -----------------------------
// Navigation API
// -----------------------------

/**
 * Extends the settings navigation with the surveypro settings
 *
 * This function is called when the context for the page is a surveypro module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $surveypronode {@link navigation_node}
 * @return
 */
function surveypro_extend_settings_navigation(settings_navigation $settings, navigation_node $surveypronode) {
    global $CFG, $PAGE, $DB;

    $cm = $PAGE->cm;
    if (!$cm = $PAGE->cm) {
        return;
    }

    $paramurlbase = array('s' => $cm->instance);
    $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);

    $context = context_module::instance($cm->id);

    $riskyediting = ($surveypro->riskyeditdeadline > time());

    $canpreview = has_capability('mod/surveypro:preview', $context, null, true);
    $canmanageitems = has_capability('mod/surveypro:manageitems', $context, null, true);

    $canimportdata = has_capability('mod/surveypro:importdata', $context, null, true);
    $canexportdata = has_capability('mod/surveypro:exportdata', $context, null, true);

    $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $context, null, true);
    $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $context, null, true);
    $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $context, null, true);
    $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $context, null, true);

    $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $context, null, true);
    $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $context, null, true);

    $canaccessreports = has_capability('mod/surveypro:accessreports', $context, null, true);
    $canaccessownreports = has_capability('mod/surveypro:accessownreports', $context, null, true);

    $hassubmissions = surveypro_count_submissions($cm->instance);

    $whereparams = array('surveyproid' => $cm->instance);
    $countparents = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);

    // SURVEYPRO_TABITEMS
    // -> parent
    if (($canpreview) || ($canmanageitems && empty($surveypro->template))) {
        $nodelabel = get_string('tabitemsname', 'surveypro');
        $navnode = $surveypronode->add($nodelabel,  null, navigation_node::TYPE_CONTAINER);
    }

    // -> children
    if ($canpreview) {
        $nodelabel = get_string('tabitemspage1', 'surveypro');
        $localparamurl = array('s' => $cm->instance, 'view' => SURVEYPRO_PREVIEWSURVEYFORM);
        $navnode->add($nodelabel, new moodle_url('/mod/surveypro/view_userform.php', $localparamurl), navigation_node::TYPE_SETTING);
    }
    if ($canmanageitems) {
        $nodelabel = get_string('tabitemspage2', 'surveypro');
        $navnode->add($nodelabel, new moodle_url('/mod/surveypro/items_manage.php', $paramurlbase), navigation_node::TYPE_SETTING);
        if (empty($surveypro->template)) {
            if ($countparents) {
                $nodelabel = get_string('tabitemspage4', 'surveypro');
                $navnode->add($nodelabel, new moodle_url('/mod/surveypro/items_validate.php', $paramurlbase), navigation_node::TYPE_SETTING);
            }
        }
    }

    // SURVEYPRO_TABSUBMISSIONS
    if ($canimportdata || $canexportdata) {
        // -> parent
        $nodelabel = get_string('tabsubmissionspage2', 'surveypro');
        $navnode = $surveypronode->add($nodelabel,  null, navigation_node::TYPE_CONTAINER);

        // -> children
        if ($canimportdata) { // import
            $nodelabel = get_string('tabsubmissionspage8', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/view_import.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
        if ($canexportdata) { // export
            $nodelabel = get_string('tabsubmissionspage9', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/view_export.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO_TABUTEMPLATES
    if ($canmanageusertemplates && empty($surveypro->template)) {
        // -> parent
        $nodelabel = get_string('tabutemplatename', 'surveypro');
        $navnode = $surveypronode->add($nodelabel,  null, navigation_node::TYPE_CONTAINER);

        // -> children
        $nodelabel = get_string('tabutemplatepage1', 'surveypro');
        $navnode->add($nodelabel, new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurlbase), navigation_node::TYPE_SETTING);
        if ($cansaveusertemplates) {
            $nodelabel = get_string('tabutemplatepage2', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/utemplates_create.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
        if ($canimportusertemplates) {
            $nodelabel = get_string('tabutemplatepage3', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/utemplates_import.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
        if ( (!$hassubmissions || $riskyediting) && $canapplyusertemplates ) {
            $nodelabel = get_string('tabutemplatepage4', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/utemplates_apply.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO_TABMTEMPLATES
    $condition1 = $cansavemastertemplates && empty($surveypro->template);
    $condition2 = (!$hassubmissions || $riskyediting) && $canapplymastertemplates;
    if ($condition1 || $condition2) {
        // -> parent
        $nodelabel = get_string('tabmtemplatename', 'surveypro');
        $navnode = $surveypronode->add($nodelabel, null, navigation_node::TYPE_CONTAINER);

        // -> children
        if ($condition1) {
            $nodelabel = get_string('tabmtemplatepage1', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/mtemplates_create.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
        if ($condition2) {
            $nodelabel = get_string('tabmtemplatepage2', 'surveypro');
            $navnode->add($nodelabel, new moodle_url('/mod/surveypro/mtemplates_apply.php', $paramurlbase), navigation_node::TYPE_SETTING);
        }
    }

    // SURVEYPRO REPORTS
    if ($surveyproreportlist = get_plugin_list('surveyproreport')) {
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $context, null, true);
        $icon = new pix_icon('i/report', '', 'moodle', array('class' => 'icon'));
        foreach ($surveyproreportlist as $pluginname => $pluginpath) {
            require_once($CFG->dirroot.'/mod/surveypro/report/'.$pluginname.'/classes/report.class.php');
            $classname = 'mod_surveypro_report_'.$pluginname;
            $reportman = new $classname($cm, $context, $surveypro);

            $restricttemplates = $reportman->restrict_templates();

            if ((!$restricttemplates) || in_array($surveypro->template, $restricttemplates)) {
                if ($canaccessreports || ($reportman->has_student_report() && $canaccessownreports)) {
                    if ($reportman->report_apply()) {
                        if (!isset($reportnode)) {
                            $nodelabel = get_string('report');
                            $reportnode = $surveypronode->add($nodelabel, null, navigation_node::TYPE_CONTAINER);
                        }
                        if ($childreports = $reportman->get_childreports($canaccessreports)) {
                            $nodelabel = get_string('pluginname', 'surveyproreport_'.$pluginname);
                            $childnode = $reportnode->add($nodelabel, null, navigation_node::TYPE_CONTAINER);
                            foreach ($childreports as $childname => $childparams) {
                                $childparams['s'] = $PAGE->cm->instance;
                                $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $childparams);
                                $childnode->add($childname, $url, navigation_node::TYPE_SETTING, null, null, $icon);
                            }
                        } else {
                            $url = new moodle_url('/mod/surveypro/report/'.$pluginname.'/view.php', $paramurlbase);
                            $nodelabel = get_string('pluginname', 'surveyproreport_'.$pluginname);
                            $reportnode->add($nodelabel, $url, navigation_node::TYPE_SETTING, null, null, $icon);
                        }
                    }
                }
            }
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
 * @return
 */
function surveypro_extend_navigation(navigation_node $navref, stdClass $course, stdClass $surveypro, cm_info $cm) {
    // global $COURSE;

    // $context = context_system::instance();
    $context = context_module::instance($cm->id);

    $cansearch = has_capability('mod/surveypro:searchsubmissions', $context, null, true);
    $canimportdata = has_capability('mod/surveypro:importdata', $context, null, true);
    $canexportdata = has_capability('mod/surveypro:exportdata', $context, null, true);

    // $currentgroup = groups_get_activity_group($cm);
    // $groupmode = groups_get_activity_groupmode($cm, $COURSE);

    // SURVEYPRO_TABSUBMISSIONS
    // children only
    $paramurl = array('s' => $cm->instance);
    $localparamurl = array('s' => $cm->instance, 'cover' => 0);
    $nodelabel = get_string('tabsubmissionspage1', 'surveypro');
    $navref->add($nodelabel, new moodle_url('/mod/surveypro/view_cover.php', $paramurl), navigation_node::TYPE_SETTING);
    $nodelabel = get_string('tabsubmissionspage2', 'surveypro');
    $navref->add($nodelabel, new moodle_url('/mod/surveypro/view.php', $localparamurl), navigation_node::TYPE_SETTING);
    if ($cansearch) {
        $nodelabel = get_string('tabsubmissionspage6', 'surveypro');
        $navref->add($nodelabel, new moodle_url('/mod/surveypro/view_search.php', $paramurl), navigation_node::TYPE_SETTING);
    }
}

// -----------------------------
// CUSTOM SURVEYPRO API
// -----------------------------

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
 * @param $plugintype
 * @param $includetype
 * @param $count
 * @return
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
            if (!empty($includetype)) {
                $formatplugins = core_component::get_plugin_list('surveypro'.SURVEYPRO_TYPEFORMAT);
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
 * @param $surveyproid
 * @param $canaccessadvanceditems
 * @param $searchform
 * @param $type
 * @param $formpage
 * @return
 */
function surveypro_fetch_items_seeds($surveyproid, $canaccessadvanceditems, $searchform, $type=false, $formpage=false) {
    $sql = 'SELECT si.*
               FROM {surveypro_item} si
               WHERE si.surveyproid = :surveyproid
                   AND si.hidden = 0';
    $params = array();
    $params['surveyproid'] = $surveyproid;

    if (!$canaccessadvanceditems) {
        $sql .= ' AND si.advanced = 0';
    }
    if ($searchform) { // advanced search
        $sql .= ' AND si.insearchform = 1';
        $sql .= ' AND si.plugin <> "pagebreak"';
    }
    if ($type) {
        $sql .= ' AND si.type = :type';
        $params['type'] = $type;
    }
    if ($formpage) { // if I am asking for a single page ONLY
        $sql .= ' AND si.formpage = :formpage';
        $params['formpage'] = $formpage;
    }
    $sql .= ' ORDER BY si.sortindex';

    return array($sql, $params);
}

/**
 * surveypro_get_view_actions
 *
 * @param
 * @return
 */
function surveypro_get_view_actions() {
    return array('view', 'view all');
}

/**
 * surveypro_get_post_actions
 *
 * @param
 * @return
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
 * surveypro_reset_items_pages
 *
 * @param $surveyproid
 * @return
 */
function surveypro_reset_items_pages($surveyproid) {
    global $DB;

    $whereparams = array('surveyproid' => $surveyproid);
    $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);
}

/**
 * surveypro_count_items
 *
 * @param $surveyproid
 * @return
 */
function surveypro_count_items($surveyproid) {
    global $DB;

    $whereparams = array('surveyproid' => $surveyproid);

    return $DB->count_records('surveypro_item', $whereparams);
}

/**
 * surveypro_count_submissions
 *
 * @param $surveyproid
 * @param $status
 * @return
 */
function surveypro_count_submissions($surveyproid, $status=SURVEYPRO_STATUSALL) {
    global $DB;

    $params = array('surveyproid' => $surveyproid);
    if ($status != SURVEYPRO_STATUSALL) {
        $params['status'] = $status;
    }

    return $DB->count_records('surveypro_submission', $params);
}

/**
 * surveypro_get_user_style_options
 *
 * @param none
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

    // Get surveypro details
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
