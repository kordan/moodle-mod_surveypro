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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The utility class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_utility {

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
        $this->context = context_module::instance($cm->id);
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
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

        $maxassignedpage = 0;
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
                            $parentpage = $DB->get_field('surveypro_item', 'formpage', array('id' => $item->parentid), MUST_EXIST);
                            if ($parentpage == $pagenumber) {
                                $pagenumber++;
                            }
                        }
                    }
                    $DB->set_field('surveypro_item', 'formpage', $pagenumber, array('id' => $item->id));
                }
            }
            $items->close();
            $maxassignedpage = $pagenumber;
        }

        return $maxassignedpage;
    }

    /**
     * Return if the survey has input items.
     *
     * @param int $formpage
     * @param int $returncount
     * @param bool $includehidden
     * @param bool $includereserved
     * @return bool|int as required by $returncount
     */
    public function has_input_items($formpage=0, $returncount=false, $includehidden=false, $includereserved=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
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
        $whereparams['type'] = SURVEYPRO_TYPEFIELD;
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

        $whereparams = array('surveyproid' => $this->surveypro->id);
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
    public function delete_items($whereparams=null) {
        global $DB, $COURSE;

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $items = $DB->get_records('surveypro_item', $whereparams, '', 'id, type, plugin');
        if (!count($items)) {
            return;
        }

        // Update completion state: step 1 of 2.
        // Before deleting items, get the list of involved students as they may get course completion.
        // After items deletion you will no longer be able to select them with this query.
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $sql = 'SELECT DISTINCT s.userid as id
                    FROM {surveypro_submission} s';
            if (count($whereparams) == 1) {
                $sql .= ' WHERE surveyproid = :surveyproid';
            }

            if (count($whereparams) > 1) {
                list($insql, $inparams) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED, 'itemid');
                $sql .= ' JOIN {surveypro_answer} a ON a.submissionid = s.id
                        WHERE s.surveyproid = :surveyproid
                            AND a.itemid '.$insql;

                $whereparams = array_merge($whereparams, $inparams);
            }
            $possibleusers = $DB->get_records_sql($sql, $whereparams);
        }
        // End of: Update completion state: step 1 of 2.

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            if (count($whereparams) == 1) { // Delete all the items of this surveypro.
                foreach ($items as $item) {
                    $DB->delete_records('surveypro'.$item->type.'_'.$item->plugin, array('itemid' => $item->id));

                    // Event: item_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $item->id);
                    $eventdata['other'] = array('plugin' => $item->plugin);
                    $event = \mod_surveypro\event\item_deleted::create($eventdata);
                    $event->trigger();
                }
                $DB->delete_records('surveypro_item', array('surveyproid' => $this->surveypro->id));
            }

            if (count($whereparams) > 1) { // Some more detail about items were provided in $whereparams.
                foreach ($items as $item) {
                    $DB->delete_records('surveypro'.$item->type.'_'.$item->plugin, array('itemid' => $item->id));

                    $DB->delete_records('surveypro_item', array('id' => $item->id));

                    // Event: item_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $item->id);
                    $eventdata['other'] = array('plugin' => $item->plugin);
                    $event = \mod_surveypro\event\item_deleted::create($eventdata);
                    $event->trigger();
                }
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        $this->reset_items_pages();

        // Take care: in this method $whereparams is a constrain for the item table
        // so, you can not pass it as is to the delete_submissions method because there
        // $whereparams is supposed to hold constrains for the submissions table.

        // Delete corresponding submissions.
        if (count($whereparams) == 1) { // Delete all the items of this surveypro.
            $this->delete_submissions($whereparams, false);
        }

        if (count($whereparams) > 1) { // Some more detail about items were provided in $whereparams.
            foreach ($items as $item) {
                // In the frame of delete_answer I also delete the corresponding submission when needed.
                $this->delete_answer(array('itemid' => $item->id));
            }
        }

        // Update completion state: step 2 of 2.
        // Item deletion lead to COMPLETION_COMPLETE.
        // All the students with an "in progress" submission that was missing ONLY the just deleted item,
        // maybe now reach the activity completion.
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            foreach ($possibleusers as $user) {
                $completion->update_state($this->cm, COMPLETION_COMPLETE, $user->id);
            }
        }
        // End of: Update completion state: step 2 of 2.
    }

    /**
     * Delete submissions.
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
     * @param bool $updatecompletion
     * @return void
     */
    public function delete_submissions($whereparams=null, $updatecompletion=true) {
        global $DB, $COURSE;

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $submissions = $DB->get_recordset('surveypro_submission', $whereparams, '', 'id');
        if (!$submissions->valid()) {
            return;
        }

        // Update completion state: step 1 of 2.
        // Before deleting submissions, get the list of involved students as they may get course completion.
        // After submissions deletion you will no longer be able to select them with this query.
        if ($updatecompletion) {
            if (count($whereparams) == 1) { // Delete all submission of this surveypro.
                // Update completion state.
                $possibleusers = surveypro_get_participants($this->surveypro->id);
            }

            if (count($whereparams) > 1) { // Some more detail about submissions were provided in $whereparams.
                $conditions = array();
                foreach ($whereparams as $field => $unused) {
                    $conditions[] = $field.' = :'.$field;
                }
                $sql = 'SELECT DISTINCT userid as id
                        FROM {surveypro_submission}
                        WHERE '.implode(' AND ', $conditions);
                $possibleusers = $DB->get_records_sql($sql, $whereparams);
            }
        }
        // End of: Update completion state: step 1 of 2.

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            if (count($whereparams) == 1) { // Delete all the submissions of this surveypro.
                foreach ($submissions as $submission) {
                    $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));

                    // Event: submission_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $submission->id);
                    $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                    $event->trigger();
                }
                $submissions->close();
                $DB->delete_records('surveypro_submission', $whereparams);
            }

            if (count($whereparams) > 1) { // Some more detail about submissions were provided in $whereparams.
                foreach ($submissions as $submission) {
                    $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));

                    // Event: submission_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $submission->id);
                    $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                    $event->trigger();
                }
                $submissions->close();
                $DB->delete_records('surveypro_submission', $whereparams);
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        // Update completion state: step 2 of 2.
        // Item deletion lead to COMPLETION_COMPLETE.
        // All the students with an "in progress" submission that was missing ONLY the just deleted item,
        // maybe now reach the activity completion.
        if ($updatecompletion) {
            $completion = new completion_info($COURSE);
            if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                foreach ($possibleusers as $user) {
                    $completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->id);
                }
            }
        }
        // End of: Update completion state: step 2 of 2.
    }

    /**
     * Delete answer.
     *
     * This is the rationale: an item was deleted
     * This method drops from EACH submission of the surveypro that had the deleted item
     * the answer to that item
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
    public function delete_answer($whereparams=null) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }

        // This is the list of the id of the submissions involved by the deletion of the answer.
        if (array_key_exists('content', $whereparams)) {
            // Take note about the submissionid of the answers you are going to delete.
            $conditions = array();
            foreach ($whereparams as $field => $unused) {
                $conditions[$field] = $field.' = :'.$field;
            }
            unset($conditions['content']);

            $sql = 'SELECT submissionid
                    FROM {surveypro_answer}
                    WHERE content = '.$DB->sql_compare_text(':content');
            if (count($conditions)) {
                $sql .= ' AND '.implode(' AND ', $conditions);
            }
            $answers = $DB->get_records_sql($sql, $whereparams);

            // Delete answers.
            $sql = 'DELETE FROM {surveypro_answer}
                    WHERE content = '.$DB->sql_compare_text($whereparams['content']);
            unset($whereparams['content']);
            foreach ($whereparams as $field => $value) {
                $sql .= ' AND '.$field.' = '.$value;
            }
            $DB->execute($sql);
        } else {
            // Take note about the submissionid of the answers you are going to delete.
            $answers = $DB->get_records('surveypro_answer', $whereparams, '', 'submissionid');

            // Delete answers.
            $DB->delete_records('surveypro_answer', $whereparams);
        }

        foreach ($answers as $answer) {
            // Once some answers were deleted, are there any more answers, for the same submission, still present?
            $count = $DB->count_records('surveypro_answer', array('submissionid' => $answer->submissionid));
            if (empty($count)) {
                // No more answers for the same submission are still present. Delete the parent submission too.
                $this->delete_submissions(array('id' => $answer->submissionid), true);
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
     * @param bool $updatecompletion
     * @return void
     */
    public function duplicate_submissions($whereparams=null, $updatecompletion=true) {
        global $DB, $COURSE;

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            $submissions = $DB->get_recordset('surveypro_submission', $whereparams, '');

            if (count($whereparams) == 1) { // Duplicate all the submissions of this surveypro.
                foreach ($submissions as $submission) {
                    $submissionid = $submission->id;

                    unset($submission->id);
                    // $submission->userid = $USER->id; // Assign the duplicate to the user performing the action.
                    $submission->timecreated = time();
                    unset($submission->timemodified);
                    $newsubmissionid = $DB->insert_record('surveypro_submission', $submission);

                    $useranswers = $DB->get_recordset('surveypro_answer', array('submissionid' => $submissionid));
                    foreach ($useranswers as $useranswer) {
                        unset($useranswer->id);
                        $useranswer->submissionid = $newsubmissionid;
                        $DB->insert_record('surveypro_answer', $useranswer);
                    }
                    $useranswers->close();

                    // Event: submission_duplicated.
                    $eventdata = array('context' => $context, 'objectid' => $submissionid);
                    $event = \mod_surveypro\event\submission_duplicated::create($eventdata);
                    $event->trigger();
                }
                $submissions->close();
            }

            if (count($whereparams) > 1) { // Some more detail about submissions were provided in $whereparams.
                foreach ($submissions as $submission) {
                    $submissionid = $submission->id;

                    unset($submission->id);
                    // $submission->userid = $USER->id; // Assign the duplicate to the user performing the action.
                    $submission->timecreated = time();
                    unset($submission->timemodified);
                    $newsubmissionid = $DB->insert_record('surveypro_submission', $submission);

                    $useranswers = $DB->get_recordset('surveypro_answer', array('submissionid' => $submissionid));
                    foreach ($useranswers as $useranswer) {
                        unset($useranswer->id);
                        $useranswer->submissionid = $newsubmissionid;
                        $DB->insert_record('surveypro_answer', $useranswer);
                    }
                    $useranswers->close();

                    // Event: submission_duplicated.
                    $eventdata = array('context' => $context, 'objectid' => $submissionid);
                    $event = \mod_surveypro\event\submission_duplicated::create($eventdata);
                    $event->trigger();
                }
                $submissions->close();
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        if ($updatecompletion) {
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

            $completion = new completion_info($COURSE);
            if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                foreach ($possibleusers as $user) {
                    $completion->update_state($this->cm, COMPLETION_COMPLETE, $user->id);
                }
            }
        }
    }

    /**
     * Set the visibility to items.
     *
     * @param array $whereparams
     * @param bool $visibility
     * @return void
     */
    public function items_set_visibility($whereparams=null, $visibility) {
        global $DB;

        if ( ($visibility != 0) && ($visibility != 1) ) {
            debugging('Bad parameters passed to items_set_visibility', DEBUG_DEVELOPER);
        }

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $whereparams['hidden'] = $visibility;

        $context = context_module::instance($this->cm->id);
        $items = $DB->get_records('surveypro_item', $whereparams, '', 'id, plugin');
        if ($visibility == 0) {
            // I was asked to hide.
            foreach ($items as $item) {
                // Event: item_hidden.
                $eventdata = array('context' => $context, 'objectid' => $item->id);
                $eventdata['other'] = array('plugin' => $item->plugin);
                $event = \mod_surveypro\event\item_hidden::create($eventdata);
                $event->trigger();
            }
        } else {
            // I was asked to show.
            foreach ($items as $item) {
                // Event: item_shown.
                $eventdata = array('context' => $context, 'objectid' => $item->id);
                $eventdata['other'] = array('plugin' => $item->plugin);
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

        $whereparams = array('surveyproid' => $this->surveypro->id);

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
                $DB->set_field('surveypro_item', 'sortindex', $currentsortindex, array('id' => $item->id));
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

        $whereparams = array('surveyproid' => $this->surveypro->id);
        $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);
    }

    /**
     * Set the status to submissions.
     *
     * @param array $whereparams
     * @param bool $status
     * @return void
     */
    public function submissions_set_status($whereparams=null, $status) {
        global $DB;

        if ( ($status != SURVEYPRO_STATUSCLOSED) && ($status != SURVEYPRO_STATUSINPROGRESS) ) {
            debugging('Bad parameters passed to submissions_set_status', DEBUG_DEVELOPER);
        }

        if (empty($whereparams)) {
            $whereparams = array();
        }
        // Just in case the call is missing the surveypro id, I add it.
        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        $whereparams['status'] = 1 - $status;
        $DB->set_field('surveypro_submission', 'status', $status, $whereparams);
    }

    /**
     * Get submissions id from answers.
     *
     * @param array $whereparams
     * @return recordset
     */
    public function get_submissionsid_from_answers($whereparams) {
        global $DB;

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
     * Perform necessary followup to the change of obligatoriness.
     *
     * @param int $itemid
     * @return void
     */
    public function optional_to_required_followup($itemid) {
        $whereparams = array('itemid' => $itemid, 'content' => SURVEYPRO_NOANSWERVALUE);
        $submissions = $this->get_submissionsid_from_answers($whereparams);
        foreach ($submissions as $submission) {
            // Change to SURVEYPRO_STATUSINPROGRESS the status of submissions where was answered SURVEYPRO_NOANSWERVALUE.
            $whereparams = array();
            $whereparams['surveyproid'] = $this->surveypro->id;
            $whereparams['id'] = $submission->id;
            $this->submissions_set_status($whereparams, SURVEYPRO_STATUSINPROGRESS);

            // Delete answers where content == SURVEYPRO_NOANSWERVALUE.
            $whereparams = array();
            $whereparams['submissionid'] = $submission->id;
            $whereparams['content'] = SURVEYPRO_NOANSWERVALUE;
            $this->delete_answer($whereparams);
        }
        $submissions->close();
    }

    /**
     * Display an alarming message whether there are submissions.
     *
     * @return void
     */
    public function has_submissions_warning() {
        global $COURSE;

        $message = get_string('hassubmissions_alert', 'mod_surveypro');
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $message .= get_string('hassubmissions_alert_activitycompletion', 'mod_surveypro');
        }

        return $message;
    }

    /**
     * Get used plugin list.
     *
     * This method provide the list af the plugin used in the current surveypro
     * getting them from the items already added
     *
     * @param string $type Optional plugin type
     * @return array $pluginlist;
     */
    public function get_used_plugin_list($type='') {
        global $DB;

        $whereparams = array();
        $sql = 'SELECT plugin
                FROM {surveypro_item}
                WHERE surveyproid = :surveyproid';
        $whereparams['surveyproid'] = $this->surveypro->id;
        if (!empty($type)) {
            $sql .= ' AND type = :type';
            $whereparams['type'] = $type;
        }
        $sql .= ' GROUP BY plugin';

        $pluginlist = $DB->get_fieldset_sql($sql, $whereparams);

        return $pluginlist;
    }

    /**
     * Assign to the user outform the custom css provided for the instance.
     *
     * @return void
     */
    public function add_custom_css() {
        global $PAGE;

        $fs = get_file_storage();
        if ($fs->get_area_files($this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false)) {
            $PAGE->requires->css('/mod/surveypro/userstyle.php?id='.$this->surveypro->id.'&amp;cmid='.$this->cm->id);
        }
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
     * Get the list of available URLs for admin menu and for module pages tree both
     *
     * @param int $caller of this routine. It can be: SURVEYPRO_TAB, SURVEYPRO_BLOCK.
     * @return array of boolean permissions to show link in the admin blook or pages in the module tree
     */
    public function get_common_links_url($caller) {
        global $DB;

        $callers = array(SURVEYPRO_TAB, SURVEYPRO_BLOCK);
        if (!in_array($caller, $callers)) {
            $message = 'Wrong caller passed to get_common_links_url';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());

        $canview = has_capability('mod/surveypro:view', $this->context);
        $canpreview = has_capability('mod/surveypro:preview', $this->context);
        $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);
        $cansearch = has_capability('mod/surveypro:searchsubmissions', $this->context);
        $canimportdata = has_capability('mod/surveypro:importdata', $this->context);
        $canexportdata = has_capability('mod/surveypro:exportdata', $this->context);
        $canmanageusertemplates = has_capability('mod/surveypro:manageusertemplates', $this->context);
        $cansaveusertemplates = has_capability('mod/surveypro:saveusertemplates', $this->context);
        $canimportusertemplates = has_capability('mod/surveypro:importusertemplates', $this->context);
        $canapplyusertemplates = has_capability('mod/surveypro:applyusertemplates', $this->context);
        $cansavemastertemplates = has_capability('mod/surveypro:savemastertemplates', $this->context);
        $canapplymastertemplates = has_capability('mod/surveypro:applymastertemplates', $this->context);
        $canaccessreports = has_capability('mod/surveypro:accessreports', $this->context);

        $hassubmissions = $this->has_submissions();

        $whereparams = array('surveyproid' => $this->surveypro->id);
        $countparents = $DB->count_records_select('surveypro_item', 'surveyproid = :surveyproid AND parentid <> 0', $whereparams);

        $availableurllist = array();

        $paramurlbase = array('id' => $this->cm->id);

        // Tab/Container layout.
        $elements = array();

        // Layout -> preview.
        $elements['preview'] = false;
        if ($canpreview) {
            $elementurl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurlbase);
            $elements['preview'] = $elementurl;
        }

        // Layout -> elements.
        $elements['manage'] = false;
        if ($canmanageitems) {
            $elementurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // Layout -> validate.
        $elements['validate'] = false;
        if ($canmanageitems && empty($this->surveypro->template) && $countparents) {
            $elementurl = new moodle_url('/mod/surveypro/layout_validation.php', $paramurlbase);
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
            $elementurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurlbase);
            $elements['container'] = $elementurl;
        }

        $availableurllist['tab_layout'] = $elements;
        // End of: Tab/Container layout.

        // Tab/Container submissions.
        $elements = array();

        // Submissions -> cover.
        $elements['cover'] = false;
        if ($canview) {
            $elementurl = new moodle_url('/mod/surveypro/view_cover.php', $paramurlbase);
            $elements['cover'] = $elementurl;
        }

        // Submissions -> responses.
        $elements['responses'] = false;
        if (!is_guest($this->context)) {
            $elementurl = new moodle_url('/mod/surveypro/view.php', array('id' => $this->cm->id, 'force' => 1));
            $elements['responses'] = $elementurl;
        }

        // Submissions -> search.
        $elements['search'] = false;
        if ($cansearch && $this->has_search_items()) {
            $elementurl = new moodle_url('/mod/surveypro/view_search.php', $paramurlbase);
            $elements['search'] = $elementurl;
        }

        // Submissions -> import.
        $elements['import'] = false;
        if ($canimportdata) {
            $elementurl = new moodle_url('/mod/surveypro/view_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // Submissions -> export.
        $elements['export'] = false;
        if ($canexportdata) {
            $elementurl = new moodle_url('/mod/surveypro/view_export.php', $paramurlbase);
            $elements['export'] = $elementurl;
        }

        // Submissions -> report.
        $elements['report'] = $canaccessreports;

        // Submissions -> container.
        $elements['container'] = false;
        if ($caller == SURVEYPRO_TAB) {
            if ($elements['cover'] || $elements['responses'] || $elements['search'] || $elements['report']) {
                $elementurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
                $elements['container'] = $elementurl;
            }
        }
        if ($caller == SURVEYPRO_BLOCK) {
            if ($elements['import'] || $elements['export']) {
                $elementurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
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
            $elementurl = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurlbase);
            $elements['manage'] = $elementurl;
        }

        // User template -> save.
        $elements['save'] = false;
        if ($elements['container'] && $cansaveusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // User template -> import.
        $elements['import'] = false;
        if ($elements['container'] && $canimportusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_import.php', $paramurlbase);
            $elements['import'] = $elementurl;
        }

        // User template -> apply.
        $elements['apply'] = false;
        if ($elements['container'] && (!$hassubmissions || $riskyediting) && $canapplyusertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/utemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        $availableurllist['tab_utemplate'] = $elements;
        // End of: Tab/Container user template.

        // Tab/Container master template.
        $elements = array();

        // Master template -> save.
        $elements['save'] = false;
        if ($cansavemastertemplates && empty($this->surveypro->template)) {
            $elementurl = new moodle_url('/mod/surveypro/mtemplate_save.php', $paramurlbase);
            $elements['save'] = $elementurl;
        }

        // Master template -> apply.
        $elements['apply'] = false;
        if ((!$hassubmissions || $riskyediting) && $canapplymastertemplates) {
            $elementurl = new moodle_url('/mod/surveypro/mtemplate_apply.php', $paramurlbase);
            $elements['apply'] = $elementurl;
        }

        // Master template -> container.
        $elements['container'] = $elements['save'] || $elements['apply'];

        $availableurllist['tab_mtemplate'] = $elements;
        // End of: Tab/Container master template.

        return $availableurllist;
    }

    /**
     * Convert an mform element name to type, plugin, item id and optional info
     *
     * @param string $elementname The string to parse
     * @return array $match
     */
    public static function get_item_parts($elementname) {
        preg_match(self::get_regexp(), $elementname, $match);

        return $match;
    }

    /**
     * Provide the regex to convert an mform element name to type, plugin, item id and optional info
     *
     * @return string $regex
     */
    public static function get_regexp() {
        $regex = '~';
        $regex .= '(?P<prefix>'.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_PLACEHOLDERPREFIX.'|'.SURVEYPRO_DONTSAVEMEPREFIX.')';
        $regex .= '_';
        $regex .= '(?P<type>'.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')';
        $regex .= '_';
        $regex .= '(?P<plugin>[^_]+)';
        $regex .= '_';
        $regex .= '(?P<itemid>\d+)';
        $regex .= '_?';
        $regex .= '(?P<option>[\d\w]+)?';
        $regex .= '~';

        return $regex;
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

        $itemcount = $this->has_input_items(0, true, $canmanageitems, $canaccessreserveditems);

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
}
