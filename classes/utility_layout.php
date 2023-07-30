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
 * Surveypro utility class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_submission;

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility_layout {

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
     * Class constructor.
     *
     * @param object $cm
     * @param object $surveypro
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        $this->context = \context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', ['id' => $cm->instance], '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * Assign pages to item writing them in the db.
     *
     * @return void
     */
    public function assign_pages() {
        global $DB;

        $where = array();
        $where['surveyproid'] = $this->surveypro->id;
        $where['hidden'] = 0;

        $userformpagecount = 0;
        $lastwaspagebreak = true; // Whether 2 page breaks in line, the second one is ignored.
        $pagenumber = 1;
        $items = $DB->get_recordset('surveypro_item', $where, 'sortindex', 'id, type, plugin, parentid, formpage, sortindex');
        if ($items) {
            foreach ($items as $item) {
                if ($item->plugin == 'pagebreak') { // It is a page break.
                    if (!$lastwaspagebreak) {
                        $pagenumber++;
                    }
                    $lastwaspagebreak = true;
                } else {
                    $lastwaspagebreak = false;
                    if ($this->surveypro->newpageforchild) {
                        if (!empty($item->parentid)) {
                            $parentpage = $DB->get_field('surveypro_item', 'formpage', ['id' => $item->parentid], MUST_EXIST);
                            if ($parentpage == $pagenumber) {
                                $pagenumber++;
                            }
                        }
                    }
                    $DB->set_field('surveypro_item', 'formpage', $pagenumber, ['id' => $item->id]);
                }
            }
            $items->close();
            $userformpagecount = $pagenumber;
        }

        return $userformpagecount;
    }

    /**
     * Redirect to layout_itemslist.php?s=xxx the user asking to go to /view.php?id=yyy if the survey has no items.
     *
     * I HATE software thinking for me
     * Because of this I ALWAYS want to go where I ask, even if the place I ask is not supposed to be accessed by me
     * In this particular case, I want a message explaining WHY the place I asked is not supposed to be accessed by me
     * I NEVER want to be silently redirected.
     *
     * By default accessing a surveypro from a course (/view.php?id=yyy), the "predefined" landing page should be:
     *     -> for admin/editing teacher:
     *         -> if no items were created: layout_itemslist.php
     *         -> if items were already created: view_submissions.php with the submission list
     *     -> for students: ALWAYS view.php with the welcome and the surveypro cover page
     *
     * So the software HAS TO decide where to send the admin/editing teacher when he arrives from a course
     * So in the view.php I MUST add a code snippet TAKING THE DECISION for the user
     *
     * The problem rises up when the admin/editing teacher decides to go where he should not go, alias in:
     *     -> layout_itemslist.php even if items were already created
     *     -> view_submissions.php with the submission list even if no items were created
     *
     * The first request is a false problem, because the admin/editing teacher is always allowed to go there
     * The second request is allowed by the introduction of the parameter &force=1 in the URL of the TAB
     *     When the admin/editing teacher asks for view.php by clicking the corresponding TAB
     *         he asks for view.php?id=yyy&force=1
     *         and the software decision is omitted
     *     As opposite:
     *     When the admin/editing teacher arrives from a course (so he doesn't ask for a specific page)
     *         he is sent to view.php?id=yyy
     *         and the decision is taken here
     *
     * @return void
     */
    public function noitem_redirect() {
        if (!$this->layout_has_items(0, 'field', true, true, true)) {
            $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);

            $paramurl = ['id' => $this->cm->id];
            if ($canmanageitems) {
                $redirecturl = new \moodle_url('/mod/surveypro/layout_itemslist.php', $paramurl);
            } else {
                $redirecturl = new \moodle_url('/mod/surveypro/view.php', $paramurl);
            }
            redirect($redirecturl);
        }
    }

    /**
     * Return if the survey has input items.
     *
     * @param int $formpage
     * @param string $type
     * @param bool $includehidden
     * @param bool $includereserved
     * @param int $returncount
     * @return bool|int as required by $returncount
     */
    public function layout_has_items($formpage=0, $type=null, $includehidden=false, $includereserved=false, $returncount=false) {
        global $DB;

        if (!empty($type)) {
            if (($type != 'field') && ($type != 'format')) {
                $message = 'Unexpected value for $type found.';
                $message .= 'Valid values are only: field or format.';
                debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }

        $whereparams = ['surveyproid' => $this->surveypro->id];
        if (!empty($type)) {
            $whereparams['type'] = $type;
        }
        if (!empty($formpage)) {
            $whereparams['formpage'] = $formpage;
        }
        if (!$includehidden) {
            $whereparams['hidden'] = 0;
        }
        if (!$includereserved) {
            $whereparams['reserved'] = 0;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * Return if the survey has search items.
     *
     * @param bool $returncount
     * @return bool|int as required by $returncount
     */
    public function has_search_items($returncount=false) {
        global $DB;

        $whereparams = array();
        $whereparams['surveyproid'] = $this->surveypro->id;
        $whereparams['type'] = 'field';
        $whereparams['hidden'] = 0;
        $whereparams['insearchform'] = 1;

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * Return the number (or the availability) of required submissions.
     *
     * @param bool $returncount
     * @param int $status
     * @param int $userid
     * @return int
     */
    public function has_submissions($returncount=false, $status=SURVEYPRO_STATUSALL, $userid=null) {
        global $DB;

        $whereparams = ['surveyproid' => $this->surveypro->id];
        if ($status != SURVEYPRO_STATUSALL) {
            $whereparams['status'] = $status;
        }
        if (!empty($userid)) {
            $whereparams['userid'] = $userid;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_submission', $whereparams);
        } else {
            return ($DB->count_records('surveypro_submission', $whereparams) > 0);
        }
    }

    /**
     * Delete items.
     *
     * I can ask to delete a single item or a set of items, for instance, with bulk actions
     * or, at usertemplate apply time, choosing the option: "Delete all elements" or "Delete hidden elements" or ...
     * In the first case here I receive:
     * $whereparams = ['surveyproid' => $this->surveypro->id, 'id' => $itemtodelete->id];
     * In case of "Delete all elements" here I receive:
     * $whereparams = ['surveyproid' => $this->surveypro->id];
     * In case of "Delete visible elements" here I receive:
     * $whereparams = ['surveyproid' => $this->surveypro->id, 'hidden' => 0];
     * In case of "Delete hidden elements" here I receive:
     * $whereparams = ['surveyproid' => $this->surveypro->id, 'hidden' => 1];
     *
     * surveypro_item                 surveypro(field|format)_<<plugin>>
     *   id  <-----------------|        id
     *   surveyproid           |------- itemid
     *   type                           ..
     *   status
     *   ..
     *   timecreated
     *   timemodified
     *
     * @param array $whereparams
     * @return void
     */
    public function delete_items($whereparams) {
        global $DB;

        // Verify input params integrity.
        $validanswerparams = ['id', 'surveyproid', 'type ', 'plugin', 'hidden', 'insearchform', 'reserved', 'parentid'];
        $startingparams = array_keys($whereparams);
        foreach ($startingparams as $startingparam) {
            if (!in_array($startingparam, $validanswerparams)) {
                $message = 'I can not delete answers using '.$startingparam.'. It is not an answer attribute.';
                debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
        // End of: Verify input params integrity.

        $items = $DB->get_records('surveypro_item', $whereparams, '', 'id, type, plugin');
        if (!count($items)) {
            return;
        }

        $context = \context_module::instance($this->cm->id);
        // Delete answers to this/these item/s.
        foreach ($items as $item) {
            $this->delete_answers(['itemid' => $item->id], $item);
        }

        $this->reset_items_pages();
    }

    /**
     * Delete submissions.
     *
     * I am free to choose to delete a submission (or set of submissions)
     * or, to delete, a single answer of a submission (or set of answers).
     * If I delete a submission this function (delete_submissions) will call the delete_answers function.
     * If I directly call delete_answers, this function (delete_submissions) will never be called.
     * Even if I ask to delete a single answer, it may be possible I need to delete the corresponding submission.
     * So, I can not delete submissions here (in delete_submissions) because I may need to delete a submission without passing here.
     * Because of this, without care to which function I call, once an answer is deleted
     * the deletion of the parent submission is actually executed into the function delete_answers and not here.
     * All of this to say that it may appear strange but the function "delete_submissions" does not delete submissions.
     *
     * surveypro_submission           surveypro_answer
     *   id  <-----------------|        id
     *   surveyproid           |------- submissionid
     *   userid                         itemid
     *   status                         verified
     *   timecreated                    content
     *   timemodified                   contentformat
     *
     * $whereparams could be...
     * ['id' => $submission->id];
     * ['surveyproid' => $surveypro->id]
     *
     * @param array $whereparams
     * @return void
     */
    public function delete_submissions($whereparams) {
        global $DB;

        // Verify input params integrity.
        $condition = array_key_exists('surveyproid', $whereparams);
        $condition = $condition || array_key_exists('id', $whereparams);
        if (!$condition) {
            $message = 'I can not delete submissions missing submissionid AND surveyproid both';
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        // End of: Verify input params integrity.

        // Just to avoid I go to delete single submission belonging to a sureypro different from the one where I am in.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $submissions = $DB->get_recordset('surveypro_submission', $whereparams, '', 'id');
        if (!$submissions->valid()) {
            return;
        }

        $whereparams = array();
        foreach ($submissions as $submission) {
            $whereparams['submissionid'] = $submission->id;
            $this->delete_answers($whereparams);
        }
        $submissions->close();
    }

    /**
     * Delete answer.
     *
     * Here I actually drop answers.
     *
     * I am free to choose to delete a submission (or set of submissions)
     * or to delete a single answer of a submission (or set of answers).
     * If I delete a submission the function delete_submissions will call this function (delete_answers).
     * Without care to which function I call, only this function (delete_answers) will delete answers.
     * This is the reason why the deletion of the parent submission is always executed here, too.
     *
     * $whereparams could be...
     * ['id' => $answer->id];
     * ['itemid' => $answer->itemid];
     * ['submissionid' => $submission->id];
     * ['submissionid' => $submission->id, 'content' => 'something'];
     *
     * surveypro_submission           surveypro_answer
     *   id  <-----------------|        id
     *   surveyproid           |------- submissionid
     *   userid                         itemid
     *   status                         verified
     *   timecreated                    content
     *   timemodified                   contentformat
     *
     * @param array $whereparams
     * @param \stdClass $item
     * @return void
     */
    public function delete_answers($whereparams, $item=null) {
        global $DB, $COURSE;

        // Verify input params integrity.
        $condition1 = array_key_exists('content', $whereparams);
        $condition2 = array_key_exists('submissionid', $whereparams);
        if ($condition1 && !$condition2) {
            $message = 'I refuse to delete answers by content witout submissionid. Too dangerous.';
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $validanswerparams = ['id', 'submissionid', 'itemid', 'content'];
        $startingparams = array_keys($whereparams);
        foreach ($startingparams as $startingparam) {
            if (!in_array($startingparam, $validanswerparams)) {
                $message = 'I can not delete answers using '.$startingparam.'. It is not an answer attribute.';
                debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
        // End of: Verify input params integrity.

        // Build the list of ids of the answers to delete.
        if (array_key_exists('id', $whereparams)) {
            $answersidlist = [$whereparams['id']];
        } else {
            $answersidlist = $this->get_answers_idlist_from_answers($whereparams);
        }

        // Before deleting answers, get the list of involved user to recalculate their completion state.
        $completionusers = $this->get_user_from_answersid($answersidlist);

        // Before deleting answers, get the list of corresponding submissions.
        $submissionsid = $this->get_submissions_idlist_from_answersid($answersidlist);

        try {
            $transaction = $DB->start_delegated_transaction();

            // Before deleting answers, delete their attachments, if they exist.
            $this->drop_uploadfile_attachments($answersidlist);

            // Now, finally, delete answers.
            if (array_key_exists('content', $whereparams)) {
                $sql = 'DELETE FROM {surveypro_answer}
                        WHERE content = '.$DB->sql_compare_text($whereparams['content']);
                unset($whereparams['content']);
                foreach ($whereparams as $field => $value) {
                    $sql .= ' AND '.$field.' = '.$value;
                }
                // Here I actually delete a set of answers.
                $DB->execute($sql);
            } else {
                // Here I actually delete a set of answers.
                $DB->delete_records('surveypro_answer', $whereparams);
            }
            // End of: Now, finally, delete answers.

            // Now that $answers were deleted, kill parent submissions if they have no more children.
            foreach ($submissionsid as $submissionid) {
                // Now that few answers were deleted, are there any more answers, for the same submission, still present?
                $count = $DB->count_records('surveypro_answer', ['submissionid' => $submissionid]);
                if (empty($count)) {
                    // No more answers for this submission are still present. Delete this submission too.
                    // Here I actually delete a submissions.
                    $DB->delete_records('surveypro_submission', ['id' => $submissionid]);
                }
            }
            // End of: Now that $answers were deleted, kill parent submissions if they have no more children.

            // If this method was called from delete_items, you are supposed to delete related item too.
            if ($item) {
                // Here I actually delete items.
                $tablename = 'surveypro'.$item->type.'_'.$item->plugin;
                if ($DB->get_manager()->table_exists($tablename)) {
                    $DB->delete_records('surveypro'.$item->type.'_'.$item->plugin, ['itemid' => $item->id]);
                }
                $DB->delete_records('surveypro_item', ['id' => $item->id]);
            }
            // End of: If this method was called from delete_items, you are supposed to delete related items too.

            // If no error rise up, execution continue and I can log events.
            $context = \context_module::instance($this->cm->id);
            foreach ($submissionsid as $submissionid) {
                // Event: submission_deleted.
                $eventdata = ['context' => $context, 'objectid' => $submissionid];
                $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                $event->trigger();
            }

            if ($item) {
                // Event: item_deleted.
                $eventdata = ['context' => $context, 'objectid' => $item->id];
                $eventdata['other'] = ['plugin' => $item->plugin];
                $event = \mod_surveypro\event\item_deleted::create($eventdata);
                $event->trigger();
            }
            // End of: If no error rise up, execution continue and I can log events.

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        // Now that $answers were deleted, update completion state
        // Item deletion lead to COMPLETION_COMPLETE.
        // All the students with an "in progress" submission that were missing ONLY the deleted items,
        // now may get the activity completion.
        $completion = new \completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            foreach ($completionusers as $user) {
                $completion->update_state($this->cm, COMPLETION_COMPLETE, $user->id);
            }
        }
    }

    /**
     * Duplicate submission.
     *
     * surveypro_submission           surveypro_answer
     *   id  <-----------------|        id
     *   surveyproid           |------- submissionid
     *   userid                         itemid
     *   status                         verified
     *   timecreated                    content
     *   timemodified                   contentformat
     *
     * @param array $whereparams
     * @return void
     */
    public function duplicate_submissions($whereparams) {
        global $DB, $COURSE;

        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $fs = get_file_storage();

        $context = \context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            $submissions = $DB->get_recordset('surveypro_submission', $whereparams, '');

            foreach ($submissions as $submission) {
                $submissionid = $submission->id;

                unset($submission->id);
                // $submission->userid = $USER->id; // Assign the duplicate to the user performing the action.
                $submission->timecreated = time();
                unset($submission->timemodified);
                $newsubmissionid = $DB->insert_record('surveypro_submission', $submission);

                $useranswers = $DB->get_recordset('surveypro_answer', ['submissionid' => $submissionid]);
                foreach ($useranswers as $useranswer) {
                    $originalanswerid = $useranswer->id;

                    unset($useranswer->id);
                    $useranswer->submissionid = $newsubmissionid;
                    $newanswerid = $DB->insert_record('surveypro_answer', $useranswer);

                    // Make a copy of the attachments if they esist.
                    $files = $fs->get_area_files($context->id, 'surveyprofield_fileupload', 'fileuploadfiles', $originalanswerid);
                    foreach ($files as $file) {
                        $filename = $file->get_filename();
                        if ($filename == '.') {
                            continue;
                        } else {
                            $filerecord = array();
                            $filerecord['contextid'] = $context->id;
                            $filerecord['component'] = 'surveyprofield_fileupload';
                            $filerecord['filearea'] = 'fileuploadfiles';
                            $filerecord['itemid'] = $newanswerid;
                            $fs->create_file_from_storedfile($filerecord, $file);
                        }
                    }
                }
                $useranswers->close();

                // Event: submission_duplicated.
                $eventdata = ['context' => $context, 'objectid' => $submissionid];
                $event = \mod_surveypro\event\submission_duplicated::create($eventdata);
                $event->trigger();
            }
            $submissions->close();

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        if (count($whereparams) == 1) { // Duplicate all the submissions of this surveypro.
            // Update completion state.
            $possibleusers = surveypro_get_participants($this->surveypro->id);
        }

        if (count($whereparams) > 1) { // Some more detail about submissions were provided in $whereparams.
            $conditions = array();
            foreach ($whereparams as $field => $unused) {
                $conditions[$field] = $field.' = :'.$field;
            }

            $sql = 'SELECT DISTINCT userid as id
                    FROM {surveypro_submission}
                    WHERE '.implode(' AND ', $conditions);
            $possibleusers = $DB->get_records_sql($sql, $whereparams);

            // Update completion state.
        }

        $completion = new \completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            foreach ($possibleusers as $user) {
                $completion->update_state($this->cm, COMPLETION_COMPLETE, $user->id);
            }
        }
    }

    /**
     * Get the list of users involved in the passed answersid
     *
     * @param array $answersid
     * @return recordset of users id
     */
    public function get_user_from_answersid($answersid) {
        global $DB;

        if (empty($answersid)) {
            return array();
        }

        list($insql, $inparams) = $DB->get_in_or_equal($answersid, SQL_PARAMS_NAMED);
        $sql = 'SELECT s.userid as id
                FROM {surveypro_submission} s
                    JOIN {surveypro_answer} a ON s.id = a.submissionid
                WHERE s.surveyproid = :surveyproid
                    AND a.itemid '.$insql.'
                GROUP BY s.id';
        $whereparams = $inparams;
        $whereparams['surveyproid'] = $this->surveypro->id;
        $users = $DB->get_recordset_sql($sql, $whereparams);

        return $users;
    }

    /**
     * Get the list of answer going to be deleted
     *
     * @param array $whereparams
     * @return recordset of answers id
     */
    public function get_answers_idlist_from_answers($whereparams) {
        global $DB;

        // Verify input params integrity.
        $validanswerparams = ['id', 'submissionid', 'itemid', 'content'];
        $startingparams = array_keys($whereparams);
        foreach ($startingparams as $startingparam) {
            if (!in_array($startingparam, $validanswerparams)) {
                $message = 'I can not delete answers using '.$startingparam.'. It is not an answer attribute.';
                debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }

        $condition1 = array_key_exists('content', $whereparams);
        $condition2 = array_key_exists('submissionid', $whereparams);
        if ($condition1 && !$condition2) {
            $message = 'I refuse to delete answers by content witout submissionid. Too dangerous.';
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        // End of: Verify input params integrity.

        if (array_key_exists('content', $whereparams)) {
            $conditions = array();
            foreach ($whereparams as $field => $unused) {
                $conditions[$field] = $field.' = :'.$field;
            }
            unset($conditions['content']);

            $sql = 'SELECT id
                    FROM {surveypro_answer}
                    WHERE content = '.$DB->sql_compare_text(':content');
            // $whereparams['content'] is never alone.
            $sql .= ' AND '.implode(' AND ', $conditions);
            $answers = $DB->get_records_sql($sql, $whereparams);
        } else {
            // Take note about the submissionid of the answers you are going to delete.
            $answers = $DB->get_records('surveypro_answer', $whereparams, '', 'id');
        }
        $answers = array_keys($answers);

        return $answers;
    }

    /**
     * Get the list of id of submissions parents of $answersid
     *
     * @param array $answersid
     * @return recordset of submissions id
     */
    public function get_submissions_idlist_from_answersid($answersid) {
        global $DB;

        if (!is_array($answersid)) {
            $message = 'Answer ids must be an array';
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        if (empty($answersid)) {
            return array();
        }

        list($insql, $inparams) = $DB->get_in_or_equal($answersid, SQL_PARAMS_NAMED, 'answerid');
        $sql = 'SELECT submissionid
                FROM {surveypro_answer}
                WHERE id '.$insql.'
                GROUP BY submissionid
                ORDER BY submissionid';
        // $submissionsid = $DB->get_records_select('surveypro_answer', "id {$insql}", $inparams, 'submissionid', 'id, submissionid');
        $submissionsid = $DB->get_records_sql_menu($sql, $inparams);
        $submissionsid = array_keys($submissionsid);

        return $submissionsid;
    }

    /**
     * Get submissions id from answers.
     *
     * @param array $whereparams
     * @return recordset of submissions id
     */
    public function get_submissionsid_from_answers($whereparams) {
        global $DB;

        // Verify input params integrity.
        $validanswerparams = ['id', 'submissionid', 'itemid', 'content'];
        $startingparams = array_keys($whereparams);
        foreach ($startingparams as $startingparam) {
            if (!in_array($startingparam, $validanswerparams)) {
                $message = 'I can not get answers using '.$startingparam.'. It is not an answer attribute.';
                debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
        // End of: Verify input params integrity.

        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        // Get submissions from constrains on surveypro_answer.
        $sql = 'SELECT s.id
                FROM {surveypro_submission} s
                  JOIN {surveypro_answer} a ON a.submissionid = s.id
                WHERE (s.surveyproid = :surveyproid)';
        $conditions = array();
        foreach ($whereparams as $field => $unused) {
            $conditions[$field] = 'a.'.$field.' = :'.$field;
        }
        unset($conditions['surveyproid']); // That has s. as prefix.
        if (isset($conditions['content'])) {
            unset($conditions['content']); // This is going to be set in next 5 lines.
        }

        if (count($conditions)) {
            $sql .= ' AND '.implode(' AND ', $conditions);
        }
        if (array_key_exists('content', $whereparams)) {
            $sql .= ' AND a.content = '.$DB->sql_compare_text(':content');
            unset($conditions['content']);
        }

        return $DB->get_recordset_sql($sql, $whereparams);
    }

    /**
     * Get the list of available URLs for admin menu and for module pages tree both
     *
     * @param int $caller of this routine. It can be: SURVEYPRO_TAB, SURVEYPRO_BLOCK.
     * @return array of boolean permissions to show link in the admin blook or pages in the module tree
     */
    public function get_common_links_url($caller) {
        global $DB;

        $callers = [SURVEYPRO_TAB, SURVEYPRO_BLOCK];
        if (!in_array($caller, $callers)) {
            $message = 'Wrong caller passed to get_common_links_url';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $canalwaysseeowner = has_capability('mod/surveypro:alwaysseeowner', $this->context);
        $canview = has_capability('mod/surveypro:view', $this->context);
        $canpreview = has_capability('mod/surveypro:preview', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context);
        $canimportresponses = has_capability('mod/surveypro:importresponses', $this->context);
        $canexportresponses = has_capability('mod/surveypro:exportresponses', $this->context);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context);
        $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $this->context);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);
        $canaccessownreports = has_capability('mod/surveypro:accessownreports', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        $whereparams = ['surveyproid' => $this->surveypro->id];
        $countparents = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);

        $availableurllist = array();

        $paramurlbase = ['id' => $this->cm->id];

        // Tab/Container layout.
        $elements = array();

        // Layout -> preview.
        $elements['preview'] = false;
        if ($canpreview) {
            $elementurl = new \moodle_url('/mod/surveypro/layout_preview.php', $paramurlbase);
            $elements['preview'] = $elementurl;
        }

        // Layout -> elements.
        $elements['manage'] = false;
        if ($canmanageitems) {
            $elementurl = new \moodle_url('/mod/surveypro/layout_itemslist.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // Layout -> validate.
        $elements['validate'] = false;
        if ($canmanageitems && empty($this->surveypro->template) && $countparents) {
            $elementurl = new \moodle_url('/mod/surveypro/layout_validation.php', $paramurlbase);
            $elements['validate'] = $elementurl;
        }

        // Layout -> itemsetup.
        $elements['itemsetup'] = false;
        if ($canmanageitems) {
            $elements['itemsetup'] = empty($this->surveypro->template);
        }

        // Layout -> container.
        $elements['container'] = false;
        if ($elements['preview'] || $elements['manage'] || $elements['validate'] || $elements['itemsetup']) {
            $elementurl = new \moodle_url('/mod/surveypro/layout_itemslist.php', $paramurlbase);
            $elements['container'] = $elementurl;
        }

        $availableurllist['tab_layout'] = $elements;
        // End of: Tab/Container layout.

        // Tab/Container submissions.
        $elements = array();

        // Submissions -> cover.
        $elements['cover'] = false;
        if ($canview) {
            $elementurl = new \moodle_url('/mod/surveypro/view.php', $paramurlbase);
            $elements['cover'] = $elementurl;
        }

        // Submissions -> responses.
        $elements['responses'] = false;
        if (!is_guest($this->context)) {
            $elementurl = new \moodle_url('/mod/surveypro/view_submissions.php', ['id' => $this->cm->id, 'force' => 1]);
            $elements['responses'] = $elementurl;
        }

        // Submissions -> search.
        $elements['search'] = false;
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        if ($cansearch && $utilitylayoutman->has_search_items()) {
            $elementurl = new \moodle_url('/mod/surveypro/view_search.php', $paramurlbase);
            $elements['search'] = $elementurl;
        }

        // Submissions -> import.
        $elements['import'] = false;
        if ($canimportresponses) {
            $elementurl = new \moodle_url('/mod/surveypro/tools_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // Submissions -> export.
        $elements['export'] = false;
        if ($canexportresponses) {
            $elementurl = new \moodle_url('/mod/surveypro/tools_export.php', $paramurlbase);
            $elements['export'] = $elementurl;
        }

        // Submissions -> report.
        $elements['report'] = $canaccessreports;

        // Submissions -> container.
        $elements['container'] = false;
        if ($caller == SURVEYPRO_TAB) {
            if ($elements['cover'] || $elements['responses'] || $elements['search'] || $elements['report']) {
                $elementurl = new \moodle_url('/mod/surveypro/view_submissions.php', $paramurlbase);
                $elements['container'] = $elementurl;
            }
        }
        if ($caller == SURVEYPRO_BLOCK) {
            if ($elements['import'] || $elements['export']) {
                $elementurl = new \moodle_url('/mod/surveypro/view_submissions.php', $paramurlbase);
                $elements['container'] = $elementurl;
            }
        }

        $availableurllist['tab_submissions'] = $elements;
        // End of: Tab/Container submissions.

        // Tab/Container user template.
        $elements = array();

        // User template -> container.
        $elements['container'] = $canmanageusertemplates && empty($this->surveypro->template);

        // User template -> manage.
        $elements['manage'] = false;
        if ($elements['container']) {
            $elementurl = new \moodle_url('/mod/surveypro/utemplate_manage.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // User template -> save.
        $elements['save'] = false;
        if ($elements['container'] && $cansaveusertemplates) {
            $elementurl = new \moodle_url('/mod/surveypro/utemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // User template -> import.
        $elements['import'] = false;
        if ($elements['container'] && $canimportusertemplates) {
            $elementurl = new \moodle_url('/mod/surveypro/utemplate_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // User template -> apply.
        $elements['apply'] = false;
        if ($elements['container'] && (!$hassubmissions || $riskyediting) && $canapplyusertemplates) {
            $elementurl = new \moodle_url('/mod/surveypro/utemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        $availableurllist['tab_utemplate'] = $elements;
        // End of: Tab/Container user template.

        // Tab/Container master template.
        $elements = array();

        // Master template -> save.
        $elements['save'] = false;
        if ($cansavemastertemplates && empty($this->surveypro->template)) {
            $elementurl = new \moodle_url('/mod/surveypro/mtemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // Master template -> apply.
        $elements['apply'] = false;
        if ((!$hassubmissions || $riskyediting) && $canapplymastertemplates) {
            $elementurl = new \moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        // Master template -> container.
        $elements['container'] = $elements['save'] || $elements['apply'];

        $availableurllist['tab_mtemplate'] = $elements;
        // End of: Tab/Container master template.

        // Tab/Container report.
        $elements = array();
        $counter = 0;

        // Reports -> container.
        $elements['container'] = false;

        if ($surveyproreportlist = get_plugin_list('surveyproreport')) {
            foreach ($surveyproreportlist as $reportname => $reportpath) {
                $classname = 'surveyproreport_'.$reportname.'\report';
                $reportman = new $classname($this->cm, $this->context, $this->surveypro);

                $condition = $canaccessreports;
                $condition = $condition || ($canaccessownreports && $reportman->get_hasstudentreport());
                $condition = $condition && $reportman->report_applies_to($this->surveypro->template);

                // GDPR condition.
                $othercondition = !$reportman->get_displayusernames() || (empty($this->surveypro->anonymous) || $canalwaysseeowner);
                $condition = $condition && $othercondition;
                if ($condition) {
                    $counter++;
                    $elements[$reportname] = false;
                    $elementurl = new \moodle_url('/mod/surveypro/report/'.$reportname.'/view.php', $paramurlbase);
                    $elements[$reportname] = $elementurl;

                    // Reports -> container.
                    $elements['container'] = $elements['container'] || $elements[$reportname];
                }
            }
        }

        $availableurllist['tab_reports'] = $elements;
        // End of: Tab/Container reports.

        return $availableurllist;
    }

    /**
     * Drop uploadfile attachments.
     *
     * Here I actually drop files related to answers.
     *
     * @param array $answersid List of the id's of the submissions
     * @return void
     */
    public function drop_uploadfile_attachments($answersid) {
        global $DB;

        if (!is_array($answersid)) {
            $message = 'Answer ids must be an array';
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        if (empty($answersid)) {
            return;
        }

        $context = \context_module::instance($this->cm->id);

        list($insql, $inparams) = $DB->get_in_or_equal($answersid, SQL_PARAMS_NAMED);

        $fs = get_file_storage();
        $fs->delete_area_files_select($context->id, 'surveyprofield_fileupload', 'fileuploadfiles', $insql, $inparams);
    }

    /**
     * Set the visibility to items.
     *
     * @param array $whereparams
     * @param bool $visibility
     * @return void
     */
    public function items_set_visibility($whereparams, $visibility) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        if ( ($visibility != 0) && ($visibility != 1) ) {
            debugging('Bad parameters passed to items_set_visibility', DEBUG_DEVELOPER);
        }

        $whereparams['hidden'] = $visibility;

        $context = \context_module::instance($this->cm->id);
        $items = $DB->get_records('surveypro_item', $whereparams, '', 'id, plugin');
        if ($visibility == 0) {
            // I was asked to hide.
            foreach ($items as $item) {
                // Event: item_hidden.
                $eventdata = ['context' => $context, 'objectid' => $item->id];
                $eventdata['other'] = ['plugin' => $item->plugin];
                $event = \mod_surveypro\event\item_hidden::create($eventdata);
                $event->trigger();
            }
        } else {
            // I was asked to show.
            foreach ($items as $item) {
                // Event: item_shown.
                $eventdata = ['context' => $context, 'objectid' => $item->id];
                $eventdata['other'] = ['plugin' => $item->plugin];
                $event = \mod_surveypro\event\item_shown::create($eventdata);
                $event->trigger();
            }
        }

        // If I ask for visibility == 0, I want hidden = 1.
        // If I ask for visibility == 1, I want hidden = 0.
        $DB->set_field('surveypro_item', 'hidden', 1 - $visibility, $whereparams);
    }

    /**
     * Reindex items.
     *
     * @param int $startingsortindex
     * @return void
     */
    public function items_reindex($startingsortindex=0) {
        global $DB;

        $whereparams = ['surveyproid' => $this->surveypro->id];

        // Renum sortindex.
        $sql = 'SELECT id, sortindex
                FROM {surveypro_item}
                WHERE surveyproid = :surveyproid';
        if (!empty($startingsortindex)) {
            $sql .= ' AND sortindex > :startingsortindex';
            $whereparams['startingsortindex'] = $startingsortindex;
        }
        $sql .= ' ORDER BY sortindex ASC';
        $itemlist = $DB->get_recordset_sql($sql, $whereparams);
        $currentsortindex = empty($startingsortindex) ? 1 : $startingsortindex;
        foreach ($itemlist as $item) {
            if ($item->sortindex != $currentsortindex) {
                $DB->set_field('surveypro_item', 'sortindex', $currentsortindex, ['id' => $item->id]);
            }
            $currentsortindex++;
        }
        $itemlist->close();
    }

    /**
     * Reset the pages assigned to items.
     *
     * @return void
     */
    public function reset_items_pages() {
        global $DB;

        $whereparams = ['surveyproid' => $this->surveypro->id];
        $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);
    }

    /**
     * Perform necessary followup to the change of obligatoriness.
     *
     * @param int $itemid
     * @return void
     */
    public function optional_to_required_followup($itemid) {
        $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
        $whereparams = ['itemid' => $itemid, 'content' => SURVEYPRO_NOANSWERVALUE];
        $submissions = $this->get_submissionsid_from_answers($whereparams);
        foreach ($submissions as $submission) {
            // Change to SURVEYPRO_STATUSINPROGRESS the status of submissions where was answered SURVEYPRO_NOANSWERVALUE.
            $whereparams = array();
            $whereparams['surveyproid'] = $this->surveypro->id;
            $whereparams['id'] = $submission->id;
            $utilitysubmissionman->submissions_set_status($whereparams, SURVEYPRO_STATUSINPROGRESS);

            // Delete answers where content == SURVEYPRO_NOANSWERVALUE.
            $whereparams = array();
            $whereparams['submissionid'] = $submission->id;
            $whereparams['content'] = SURVEYPRO_NOANSWERVALUE;
            $this->delete_answers($whereparams);
        }
        $submissions->close();
    }

    /**
     * Is a user allowed to fill one more response?
     *
     * @param int $userid Optional userid
     * @return bool
     */
    public function can_submit_more($userid=null) {
        global $USER;

        if (empty($userid)) {
            $userid = $USER->id;
        }

        $cansubmitmore = has_capability('mod/surveypro:submit', $this->context, null, true);
        if ($cansubmitmore) {
            if (!empty($this->surveypro->maxentries)) {
                if (!has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true)) {
                    $usersubmissions = $this->has_submissions(true, SURVEYPRO_STATUSALL, $userid);
                    $cansubmitmore = ($usersubmissions < $this->surveypro->maxentries);
                }
            }
        }

        return $cansubmitmore;
    }

    /**
     * Is the button to add one more response supposed to appear in the page?
     *
     * @param int $next
     * @return bool $addnew
     */
    public function is_newresponse_allowed($next) {
        $timenow = time();

        $cansubmit = has_capability('mod/surveypro:submit', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context);

        $itemcount = $this->layout_has_items(0, 'field', $canmanageitems, $canaccessreserveditems, true);

        $addnew = true;
        $addnew = $addnew && $cansubmit;
        $addnew = $addnew && $itemcount;
        if ($this->surveypro->timeopen) {
            $addnew = $addnew && ($this->surveypro->timeopen < $timenow);
        }
        if ($this->surveypro->timeclose) {
            $addnew = $addnew && ($this->surveypro->timeclose > $timenow);
        }
        if (!$canignoremaxentries) {
            $addnew = $addnew && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
        }

        return $addnew;
    }

    /**
     * Assign to the userform the custom css provided for the instance.
     *
     * @return void
     */
    public function add_custom_css() {
        global $PAGE;

        $fs = get_file_storage();
        if ($fs->get_area_files($this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false)) {
            $paramurl = ['id' => $this->surveypro->id, 'cmid' => $this->cm->id];
            $styleurl = new \moodle_url('/mod/surveypro/userstyle.php', $paramurl);
            $PAGE->requires->css($styleurl);
        }
    }
}
