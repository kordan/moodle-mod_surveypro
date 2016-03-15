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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class representing a field
 */
class mod_surveypro_utility {
    /**
     * Basic necessary essential ingredients
     */
    protected $cm;
    protected $surveypro;

    /**
     * Class constructor
     */
    public function __construct($cm, $surveypro=null) {
        global $DB;

        $this->cm = $cm;
        if (empty($surveypro)) {
            $surveypro = $DB->get_record('surveypro', array('id' => $cm->instance), '*', MUST_EXIST);
        }
        $this->surveypro = $surveypro;
    }

    /**
     * assign_pages
     *
     * @param none
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
                    // echo 'Assigning pages: $DB->set_field(\'surveypro_item\', \'formpage\', '.$pagenumber.', array(\'id\' => '.$item->id.'));<br />';
                    $DB->set_field('surveypro_item', 'formpage', $pagenumber, array('id' => $item->id));
                }
            }
            $items->close();
            $maxassignedpage = $pagenumber;
        }

        return $maxassignedpage;
    }

    /**
     * has_input_items
     *
     * @param $surveyproid
     * @param $formpage
     * @param $includehidden
     * @param $includereserved
     * @return bool|int as required by $returncount
     */
    public function has_input_items($formpage=0, $returncount=false, $includehidden=false, $includereserved=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
        if (!empty($formpage)) {
            $whereparams['formpage'] = $formpage;
        }
        if (!$includehidden) {
            $whereclause['hidden'] = 0;
        }
        if (!$includereserved) {
            $whereclause['reserved'] = 0;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * has_search_items
     *
     * @param $surveyproid
     * @return bool|int as required by $returncount
     */
    public function has_search_items($returncount=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD, 'hidden' => 0, 'insearchform' => 1);

        if ($returncount) {
            return $DB->count_records('surveypro_item', $whereparams);
        } else {
            return ($DB->count_records('surveypro_item', $whereparams) > 0);
        }
    }

    /**
     * has_submissions
     *
     * @param $surveyproid
     * @param $status
     * @param $userid
     * @return int
     */
    public function has_submissions($returncount=false, $status=SURVEYPRO_STATUSALL, $userid=null) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $params['status'] = $status;
        }
        if (!empty($userid)) {
            $params['userid'] = $userid;
        }

        if ($returncount) {
            return $DB->count_records('surveypro_submission', $whereparams);
        } else {
            return ($DB->count_records('surveypro_submission', $whereparams) > 0);
        }
    }

    /**
     * delete_items
     *
     * surveypro_item                 surveypro(field|format)_<<plugin>>
     *   id  <-----------------|        id
     *   surveyproid           |------- itemid
     *   type                           ...
     *   status
     *   ...
     *   timecreated
     *   timemodified
     *
     * @param $whereparams
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

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            $items = $DB->get_records('surveypro_item', $whereparams, '', 'id, type, plugin');
            if (count($whereparams) == 1) { // Delete all items this surveypro.
                foreach ($items as $item) {
                    $DB->delete_records('surveypro'.$item->type.'_'.$item->plugin, array('itemid' => $item->id));
                }
                $DB->delete_records('surveypro_item', array('surveyproid' => $this->surveypro->id));

                // Event: all_items_deleted.
                $eventdata = array('context' => $context, 'objectid' => $this->surveypro->id);
                $event = \mod_surveypro\event\all_items_deleted::create($eventdata);
                $event->trigger();
            }

            if (count($whereparams) > 1) { // $whereparams has some more details about items.
                foreach ($items as $item) {
                    $DB->delete_records('surveypro'.$item->type.'_'.$item->plugin, array('itemid' => $item->id));

                    $DB->delete_records('surveypro_item', array('id' => $item->id));

                    // Event: submission_deleted.
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
        // $whereparams is supposed to hold constrains for the submissions table

        // go to delete corresponding submissions
        if (count($whereparams) == 1) { // Delete all items in this surveypro.
            $this->delete_submissions($whereparams, false);
        }

        if (count($whereparams) > 1) { // $whereparams has some more details about items.
            $whereanswerparams = array();
            foreach ($items as $item) {
                $whereanswerparams['itemid'] = $item->id;
                $this->delete_answer($whereanswerparams);
            }
        }

        // Update completion state.
        // Item deletion lead to COMPLETION_COMPLETE
        // All the students with an "in progress" submission that was missing ONLY the just deleted item,
        // maybe now reached the activity completion.
        $sql = 'SELECT DISTINCT s.userid
                FROM {surveypro_submission} s';
        if (count($whereparams) == 1) {
            $sql .= ' WHERE surveyproid = :surveyproid';
        }
        if (count($whereparams) > 1) {
            $whereparams = array();
            $sql .= ' JOIN {surveypro_answer} a ON a.submissionid = s.id
                    WHERE s.surveyproid = :surveyproid
                        AND a.id IN ('.implode(',', array_keys($items)).')';
            $whereparams['surveyproid'] = $this->surveypro->id;
        }
        $possibleusers = $DB->get_records_sql($sql, $whereparams);

        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            foreach ($possibleusers as $user) {
                $completion->update_state($this->cm, COMPLETION_COMPLETE, $user->userid);
            }
        }
    }

    /**
     * delete_submissions
     *
     * surveypro_submission           surveypro_answer
     *   id  <-----------------|        id
     *   surveyproid           |------- submissionid
     *   userid                         itemid
     *   status                         verified
     *   timecreated                    content
     *   timemodified                   contentformat
     *
     * @param $whereparams
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

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            $submissions = $DB->get_records('surveypro_submission', $whereparams, '', 'id');
            if (count($whereparams) == 1) { // Delete all submission for this surveypro.
                foreach ($submissions as $submission) {
                    $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));
                }
                $DB->delete_records('surveypro_submission', $whereparams);

                // Event: all_submissions_deleted.
                $eventdata = array('context' => $context, 'objectid' => $this->surveypro->id);
                $event = \mod_surveypro\event\all_submissions_deleted::create($eventdata);
                $event->trigger();
            }

            if (count($whereparams) > 1) { // $whereparams has some more detail about submissions.
                foreach ($submissions as $submission) {
                    $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));

                    // Event: submission_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $submission->id);
                    $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                    $event->trigger();
                }
                $DB->delete_records('surveypro_submission', $whereparams);
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        if ($updatecompletion) {
           if (count($whereparams) == 1) { // Delete all submission for this surveypro.
                // Update completion state.
                $possibleusers = surveypro_get_participants($this->surveypro->id);

                $completion = new completion_info($COURSE);
                if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                    foreach ($possibleusers as $user) {
                        $completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->id);
                    }
                }
            }

            if (count($whereparams) > 1) { // $whereparams has some more detail about submissions.
                $sql = 'SELECT DISTINCT s.userid
                        FROM {surveypro_submission} s
                        WHERE surveyproid = :surveyproid';
                foreach ($whereparams as $k => $unused) {
                    if ($k == 'surveyproid') {
                        continue;
                    }
                    $sql .= ' AND s.'.$k.' = :'.$k;
                }
                $possibleusers = $DB->get_records_sql($sql, $whereparams);

                // Update completion state.
                $completion = new completion_info($COURSE);
                if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                    foreach ($possibleusers as $user) {
                        $completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->userid);
                    }
                }
            }
        }
    }

    /**
     * delete_answer
     *
     * This is the rationale: an item was deleted.
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
     * @param $itemid
     * @return void
     */
    public function delete_answer($whereparams=null) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }

        // This is the list of the id of the submissions involved by the deletion of the answer.
        if (array_key_exists('content', $whereparams)) {
            $sql = 'SELECT submissionid
                    FROM {surveypro_answer}
                    WHERE a.content = '.$DB->sql_compare_text(':content');
            foreach ($whereparams as $k => $unused) {
                if ($k == 'content') {
                    continue;
                }
                $sql .= ' AND a.'.$k.' = :'.$k;
            }
            $answers = $DB->get_records_sql($sql, $whereparams);

            // delete answers
            $sql = 'DELETE FROM {surveypro_answer}
                    WHERE content = '.$DB->sql_compare_text($whereparams['content']);
            unset($whereparams['content']);
            foreach ($whereparams as $k => $v) {
                $sql .= ' AND '.$k.' = '.$v;
            }
            $DB->execute($sql);
        } else {
            $answers = $DB->get_records('surveypro_answer', $whereparams, '', 'submissionid');
            $DB->delete_records('surveypro_answer', $whereparams);
        }

        foreach ($answers as $answer) {
            $count = $DB->count_records('surveypro_answer', array('submissionid' => $answer->submissionid));
            if (empty($count)) {
                $this->delete_submissions(array('id' => $answer->submissionid));
            }
        }
    }

    /**
     * items_set_visibility
     *
     * @param $whereparams
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
        // If I ask for visibility == 1, I want hidden = 0
        $DB->set_field('surveypro_item', 'hidden', 1 - $visibility, $whereparams);
    }

    /**
     * items_reindex
     *
     * @return null
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
     * reset_items_pages
     *
     * @param $surveyproid
     * @return void
     */
    public function reset_items_pages() {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id);
        $DB->set_field('surveypro_item', 'formpage', 0, $whereparams);
    }

    /**
     * submissions_set_status
     *
     * @param $surveyproid
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
     * get_submissionsid_from_answers
     *
     * @param $whereparams
     * @return recordset
     */
    public function get_submissionsid_from_answers($whereparams) {
        global $DB;

        // Get submissions from constrains on surveypro_answer.
        $sql = 'SELECT s.id
                FROM {surveypro_submission} s
                    JOIN {surveypro_answer} a ON s.id = a.submissionid
                WHERE (s.surveyproid = :surveyproid)';
        if (array_key_exists('content', $whereparams)) {
            $sql .= ' AND a.content = '.$DB->sql_compare_text(':content');
        }
        foreach ($whereparams as $k => $unused) {
            if ($k == 'surveyproid') {
                continue;
            }
            if ($k == 'content') {
                continue;
            }
            $sql .= ' AND a.'.$k.' = :'.$k;
        }

        if (!array_key_exists('surveyproid', $whereparams)) {
            $whereparams['surveyproid'] = $this->surveypro->id;
        }

        return $DB->get_recordset_sql($sql, $whereparams);
    }

    /**
     * optional_to_required_followup
     *
     * @param $itemid
     * @return void
     */
    public function optional_to_required_followup($itemid) {
        $whereparams = array('itemid' => $itemid, 'content' => SURVEYPRO_NOANSWERVALUE);
        $submissions = $this->get_submissionsid_from_answers($whereparams);
        foreach ($submissions as $submission) {
            // Change to SURVEYPRO_STATUSINPROGRESS the status of submissions where was answered SURVEYPRO_NOANSWERVALUE
            $whereparams = array();
            $whereparams['surveyproid'] = $this->surveypro->id;
            $whereparams['id'] = $submission->id;
            $this->submissions_set_status($whereparams, SURVEYPRO_STATUSINPROGRESS);

            // Delete answers where content == SURVEYPRO_NOANSWERVALUE
            $whereparams = array();
            $whereparams['submissionid'] = $submission->id;
            $whereparams['content'] = SURVEYPRO_NOANSWERVALUE;
            $this->delete_answer($whereparams);
        }
        $submissions->close();
    }

    /**
     * warning_message
     *
     * @param none
     * @return void
     */
    public function warning_message() {
        global $COURSE;

        $message = get_string('hassubmissions_alert', 'mod_surveypro');
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $message .= get_string('hassubmissions_alert_activitycompletion', 'mod_surveypro');
        }

        return $message;
    }
}
