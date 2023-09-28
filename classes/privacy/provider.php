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
 * Privacy Subsystem implementation for mod_surveypro.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Implementation of the privacy subsystem plugin provider for the surveypro activity module.
 *
 * @copyright  2018 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $collection a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $collection) : collection {
        // Table: surveypro_submission.
        $data = [
            'userid' => 'privacy:metadata:submission:userid',
            'status' => 'privacy:metadata:submission:status',
            'timecreated' => 'privacy:metadata:submission:timecreated',
            'timemodified' => 'privacy:metadata:submission:timemodified',
        ];
        $collection->add_database_table('surveypro_submission', $data, 'privacy:metadata:submission');

        // Table: surveypro_answer.
        $data = [
            'content' => 'privacy:metadata:answer:content',
            'contentformat' => 'privacy:metadata:answer:contentformat',
        ];
        $collection->add_database_table('surveypro_answer', $data, 'privacy:metadata:answer');

        // Link to subplugins.
        $collection->add_subsystem_link('core_files', [], 'privacy:metadata:uploadedfiles');

        // Link to subplugins.
        $collection->add_plugintype_link('surveyprofield', [], 'privacy:metadata:surveyprofieldpluginsummary');
        $collection->add_plugintype_link('surveyproformat', [], 'privacy:metadata:surveyproformatpluginsummary');
        $collection->add_plugintype_link('surveyprotemplate', [], 'privacy:metadata:surveyprotemplatepluginsummary');
        $collection->add_plugintype_link('surveyproreport', [], 'privacy:metadata:surveyproreportpluginsummary');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     * This is for individual delete requests and is supposed to be used by delete_data_for_user().
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        // Fetch all surveypro answers.
        $sql = "SELECT c.id
                FROM {context} c
                    INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                    INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                    INNER JOIN {surveypro} sp ON sp.id = cm.instance
                    INNER JOIN {surveypro_submission} ss ON ss.surveyproid = sp.id
                    INNER JOIN {surveypro_answer} sa ON sa.submissionid = ss.id
                WHERE ss.userid = :userid";

        $params = ['modname' => 'surveypro', 'contextlevel' => CONTEXT_MODULE, 'userid' => $userid];

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export personal data for the given approved_contextlist. User and context information is contained within the contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = 'SELECT cm.id AS cmid,
                       ss.id AS submissionid, sa.id AS answerid, sa.content AS answer,
                       ss.timecreated, ss.timemodified,
                       si.id AS itemid, si.plugin
                FROM {context} c
                    INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                    INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                    INNER JOIN {surveypro} s ON s.id = cm.instance
                    INNER JOIN {surveypro_item} si ON si.surveyproid = s.id
                    INNER JOIN {surveypro_submission} ss ON ss.surveyproid = s.id
                    INNER JOIN {surveypro_answer} sa ON sa.submissionid = ss.id AND sa.itemid = si.id
                WHERE c.id '.$contextsql.'
                     AND ss.userid = :userid
                     AND si.type = :type
                ORDER BY cmid, submissionid, answerid';

        $params = ['modname' => 'surveypro', 'contextlevel' => CONTEXT_MODULE, 'userid' => $user->id, 'type' => 'field'];
        $params = $params + $contextparams;

        $surveyproanswers = $DB->get_recordset_sql($sql, $params);

        $lastcmid = null;
        $lastsubmissionid = null;
        foreach ($surveyproanswers as $surveyproanswer) {
            $itemcontent = self::export_get_itemcontent($surveyproanswer);

            if ($surveyproanswer->cmid != $lastcmid) {
                // Send current $surveyprodata.
                if (!empty($lastcmid)) {
                    $context = \context_module::instance($lastcmid);
                    self::export_surveypro_data_for_user($surveyprodata, $context, $user);
                    if (!empty($attachments)) {
                        self::export_surveypro_attachments($attachments, $context);
                    }
                }
                // Reset surveyprodata to start again.
                $surveyprodata = [
                    'submissions' => [],
                ];
                $attachments = [];

                // Store the current answer.
                self::store_current_data($surveyprodata, $surveyproanswer, $itemcontent, $attachments);

                // Update the indicators.
                $lastcmid = $surveyproanswer->cmid;
                $lastsubmissionid = $surveyproanswer->submissionid;
            } else {
                // In the frame of the same surveypro, submission has changed.
                if ($surveyproanswer->submissionid != $lastsubmissionid) {
                    // Store the current answer.
                    self::store_current_data($surveyprodata, $surveyproanswer, $itemcontent, $attachments);

                    // Update the indicator.
                    $lastsubmissionid = $surveyproanswer->submissionid;
                } else {
                    // Only the itemid changed. So: same surveypro and same submission.
                    // Store the current answer.
                    self::store_current_data($surveyprodata, $surveyproanswer, $itemcontent, $attachments);
                }
            }
        }

        if ($surveyproanswer->cmid == $lastcmid) {
            $context = \context_module::instance($lastcmid);
            // Send current $surveyprodata.
            self::export_surveypro_data_for_user($surveyprodata, $context, $user);
            if (!empty($attachments)) {
                self::export_surveypro_attachments($attachments, $context);
            }
        }
    }

    /**
     * Add the current answer (and, maybe, the corresponding uploaded file) to $surveyprodata
     *
     * @param array $surveyprodata
     * @param \stdClass $surveyproanswer
     * @param string $itemcontent
     * @param array $attachments
     */
    protected static function store_current_data(&$surveyprodata, $surveyproanswer, $itemcontent, &$attachments) {
        // Store the current answer in $surveyprodata.
        $submissionid = $surveyproanswer->submissionid;

        if (!isset($surveyprodata['submissions']['submission_'.$submissionid])) {
            $timecreated = \core_privacy\local\request\transform::datetime($surveyproanswer->timecreated);
            if ($surveyproanswer->timemodified) {
                $timemodified = \core_privacy\local\request\transform::datetime($surveyproanswer->timemodified);
            } else {
                $timemodified = 'never';
            }
            $surveyprodata['submissions']['submission_'.$submissionid] = [
                'items' => [],
                'timecreated' => $timecreated,
                'timemodified' => $timemodified,
            ];
        }
        $itemanswer = ['content' => $itemcontent, 'answer' => $surveyproanswer->answer];
        $surveyprodata['submissions']['submission_'.$submissionid]['items']['item_'.$surveyproanswer->itemid] = $itemanswer;

        // Store the current attachment.
        if ($surveyproanswer->plugin == 'fileupload') {
            $attachments[$submissionid][] = $surveyproanswer->answerid;
        }
    }

    /**
     * Get the content of the current item.
     * I didn't add this field to the main sql because the table name is $surveyproanswer->plugin dependent
     * and $surveyproanswer->plugin comes from the same query.
     *
     * @param \stdClass $surveyproanswer the personal data to export for the surveypro.
     */
    protected static function export_get_itemcontent($surveyproanswer) {
        global $DB;

        $tablename = 'surveyprofield_'.$surveyproanswer->plugin;
        $itemid = $surveyproanswer->itemid;
        $params = ['itemid' => $itemid];
        $plugin = $DB->get_record($tablename, $params);

        return $plugin->content;
    }

    /**
     * Export all files that the user uploaded.
     *
     * @param array $attachments the personal data to export for the surveypro.
     * @param \context_module $context the context of the surveypro.
     */
    protected static function export_surveypro_attachments($attachments, $context) {
        $lastsubmissionid = null;
        $responsenumber = 0;
        $writer = \core_privacy\local\request\writer::with_context($context);
        foreach ($attachments as $submissionid) {
            foreach ($submissionid as $answerid) {
                $contextpath = [];
                if ($submissionid != $lastsubmissionid) {
                    $responsenumber += 1;
                    $lastsubmissionid = $submissionid;
                }
                $contextpath[] = get_string('privacy:path:fileupload', 'surveypro', $responsenumber);
                $writer->export_area_files($contextpath, 'surveyprofield_fileupload', 'fileuploadfiles', $answerid);
            }
        }
    }

    /**
     * Export the supplied personal data for a single surveypro activity, along with any generic data or area files.
     *
     * @param array $surveyprodata the personal data to export for the surveypro.
     * @param \context_module $context the context of the surveypro.
     * @param \stdClass $user the user record
     */
    protected static function export_surveypro_data_for_user(array $surveyprodata, \context_module $context, \stdClass $user) {
        // Fetch the generic module data for the surveypro.
        $contextdata = helper::get_context_data($context, $user);

        // Merge with surveypro data and write it.
        $contextdata = (object)array_merge((array)$contextdata, $surveyprodata);
        writer::with_context($context)->export_data([], $contextdata);

        // Write generic module intro files.
        helper::export_context_files($context, $user);
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if (!$cm = get_coursemodule_from_id('surveypro', $context->instanceid)) {
            return;
        }

        $surveyproid = $cm->instance;

        $fs = get_file_storage();

        $whereparams = ['surveyproid' => $surveyproid];
        $submissions = $DB->get_recordset('surveypro_submission', $whereparams, '', 'id');
        foreach ($submissions as $submission) {
            $DB->delete_records('surveypro_answer', ['submissionid' => $submission->id]);

            // Delete related files (if any).
            $fs->delete_area_files($context->id, 'surveyprofield_fileupload', 'fileuploadfiles', $submission->id);
        }
        $submissions->close();
        $DB->delete_records('surveypro_submission', $whereparams);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $fs = get_file_storage();

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                return;
            }
            $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);

            $where = ['surveyproid' => $instanceid, 'userid' => $userid];
            $rs = $DB->get_recordset('surveypro_submission', $where, '', 'id');
            $submissions = [];
            foreach ($rs as $submission) {
                $submissions[] = $submission->id;
            }
            $rs->close();

            // $submissions is the list of the submissions ID of the users found.
            if (!$submissions) {
                return;
            }

            // Delete attachments uploaded within the answers to the submissions listed in $submissions.
            // Get the list of ID of answers related to $submissions.
            list($insql, $inparams) = $DB->get_in_or_equal($submissions, SQL_PARAMS_NAMED);

            $rs = $DB->get_recordset_select('surveypro_answer', "submissionid {$insql}", $inparams, 'id', 'id');
            $answers = [];
            foreach ($rs as $answer) {
                $answers[] = $answer->id;
            }
            $rs->close();

            list($insql, $inparams) = $DB->get_in_or_equal($answers, SQL_PARAMS_NAMED);
            $fs = get_file_storage();
            $fs->delete_area_files_select($context->id, 'surveyprofield_fileupload', 'fileuploadfiles', $insql, $inparams);

            // Delete answers for the submissions listed in $submissions.
            $DB->delete_records_list('surveypro_answer', 'submissionid', $submissions);

            // Delete submissions listed in $submissions.
            $DB->delete_records_list('surveypro_submission', 'id', $submissions);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();
        $userids = $userlist->get_userids();

        $instanceid = $DB->get_field('course_modules', 'instance', ['id' => $context->instanceid], MUST_EXIST);
        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $where = "surveyproid = :instanceid AND userid {$insql}";
        $sqlparams = $inparams + ['instanceid' => (int)$instanceid];

        $rs = $DB->get_recordset_select('surveypro_submission', $where, $sqlparams, 'id', 'id');
        $submissions = [];
        foreach ($rs as $submission) {
            $submissions[] = $submission->id;
        }
        $rs->close();

        // $submissions is the list of the submissions ID of the users found.
        if (!$submissions) {
            return;
        }

        // Delete attachments uploaded within the answers to the submissions listed in $submissions.
        // Get the list of ID of answers related to $submissions.
        list($insql, $inparams) = $DB->get_in_or_equal($submissions, SQL_PARAMS_NAMED);

        $rs = $DB->get_recordset_select('surveypro_answer', "submissionid {$insql}", $inparams, 'id', 'id');
        $answers = [];
        foreach ($rs as $answer) {
            $answers[] = $answer->id;
        }
        $rs->close();

        list($insql, $inparams) = $DB->get_in_or_equal($answers, SQL_PARAMS_NAMED);
        $fs = get_file_storage();
        $fs->delete_area_files_select($context->id, 'surveyprofield_fileupload', 'fileuploadfiles', $insql, $inparams);

        // Delete answers for the submissions listed in $submissions.
        $DB->delete_records_list('surveypro_answer', 'submissionid', $submissions);

        // Delete submissions listed in $submissions.
        $DB->delete_records_list('surveypro_submission', 'id', $submissions);

    }

    /**
     * Get the list of users who have data within a context.
     * This is for expiring contexts and is supposed to be used by delete_data_for_users()
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        // Find users with surveypro submissions.
        $sql = 'SELECT ss.userid
                FROM {context} c
                    INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                    INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                    INNER JOIN {surveypro} s ON s.id = cm.instance
                    INNER JOIN {surveypro_submission} ss ON ss.surveyproid = s.id
                WHERE c.id = :contextid';

        $params = [
            'modname' => 'surveypro',
            'contextid' => $context->id,
            'contextlevel' => CONTEXT_MODULE,
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }
}
