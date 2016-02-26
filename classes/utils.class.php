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
     * @param $includeadvanced
     * @return bool|int as required by $returncount
     */
    public function has_input_items($formpage=0, $returncount=false, $includehidden=false, $includeadvanced=false) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
        if (!empty($formpage)) {
            $whereparams['formpage'] = $formpage;
        }
        if (!$includehidden) {
            $whereclause['hidden'] = 0;
        }
        if (!$includeadvanced) {
            $whereclause['advanced'] = 0;
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
     * surveypro_count_submissions
     *
     * @param $surveyproid
     * @param $status
     * @return int
     */
    public function has_submissions($returncount=false, $status=SURVEYPRO_STATUSALL) {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $params['status'] = $status;
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

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            if (count($whereparams) == 0) { // Delete all items was requested for EACH surveypro.
                $types = array(SURVEYPRO_TYPEFIELD, SURVEYPRO_TYPEFORMAT);
                foreach ($types as $type) {
                    $plugins = core_component::get_plugin_list('surveypro'.$type);
                    foreach ($plugins as $plugin => $unused) {
                        $DB->delete_records('surveypro'.$type.'_'.$plugin);
                    }
                }
                $DB->delete_records('surveypro_item');

                $surveypros = $DB->get_records('surveypro', $whereparams, '', 'id');
                foreach ($surveypros as $surveypro) {
                    // Event: all_items_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $surveypro->id);
                    $event = \mod_surveypro\event\all_items_deleted::create($eventdata);
                    $event->trigger();
                }
            } else {
                // $whereparams was not null
                // so it affects this surveypro ONLY.
                // Just in case the call is missing the surveypro id, I add it.
                if (!array_key_exists('surveyproid', $whereparams)) {
                    $whereparams['surveyproid'] = $this->surveypro->id;
                }

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

                if (count($whereparams) > 1) { // $whereparams has some more detail about items.
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
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        $this->reset_items_pages();


        // go to delete corresponding submissions
        if (count($whereparams) == 0) { // Delete all items was requested for EACH surveypro.
            $this->delete_submissions();
        }
        if (count($whereparams) == 1) { // Delete all items this surveypro.
            $this->delete_submissions($whereparams);
        }
        if (count($whereparams) > 1) { // $whereparams has some more detail about items.
            foreach ($items as $item) {
                $this->delete_answer($item->id);
            }
        }

        // Update completion state.
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_UNKNOWN);
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
    public function delete_submissions($whereparams=null) {
        global $DB, $COURSE;

        if (empty($whereparams)) {
            $whereparams = array();
        }

        $context = context_module::instance($this->cm->id);
        try {
            $transaction = $DB->start_delegated_transaction();

            if (count($whereparams) == 0) { // Delete all submission was requested for EACH surveypro.
                $DB->delete_records('surveypro_answer');
                $DB->delete_records('surveypro_submission');

                $surveypros = $DB->get_records('surveypro', $whereparams, '', 'id');
                foreach ($surveypros as $surveypro) {
                    // Event: all_submissions_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $surveypro->id);
                    $event = \mod_surveypro\event\all_submissions_deleted::create($eventdata);
                    $event->trigger();
                }
            } else {
                // $whereparams was not null
                // so it affects this surveypro ONLY.
                // Just in case the call is missing the surveypro id, I add it.
                if (!array_key_exists('surveyproid', $whereparams)) {
                    $whereparams['surveyproid'] = $this->surveypro->id;
                }

                $submissions = $DB->get_records('surveypro_submission', $whereparams, '', 'id');
                if (count($whereparams) == 1) { // Delete all submissionfor this surveypro.
                    foreach ($submissions as $submission) {
                        $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));
                    }
                    $DB->delete_records('surveypro_submission', array('surveyproid' => $this->surveypro->id));

                    // Event: all_submissions_deleted.
                    $eventdata = array('context' => $context, 'objectid' => $this->surveypro->id);
                    $event = \mod_surveypro\event\all_submissions_deleted::create($eventdata);
                    $event->trigger();
                }

                if (count($whereparams) > 1) { // $whereparams has some more detail about submissions.
                    foreach ($submissions as $submission) {
                        $DB->delete_records('surveypro_answer', array('submissionid' => $submission->id));

                        $DB->delete_records('surveypro_submission', array('id' => $submission->id));

                        // Event: submission_deleted.
                        $eventdata = array('context' => $context, 'objectid' => $submission->id);
                        $event = \mod_surveypro\event\submission_deleted::create($eventdata);
                        $event->trigger();
                    }
                }
            }

            $transaction->allow_commit();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }

        // Update completion state.
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_UNKNOWN);
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
    public function delete_answer($itemid) {
        global $DB, $COURSE;

        // This is the list of the id of the submissions involved by the deletion of the answer to the item $itemid.
        $submissionids = $DB->get_records('surveypro_answer', array('itemid' => $itemid), '', 'submissionid');

        $DB->delete_records('surveypro_answer', array('itemid' => $itemid), '', 'submissionid');

        foreach ($submissionids as $submissionid) {
            $count = $DB->count_records('surveypro_answer', array('submissionid' => $submissionid));
            if (empty($count)) {
                $DB->delete_records('surveypro_submission', array('id' => $submissionid));
            }
        }
    }

    /**
     * show_items
     *
     * @param $whereparams
     * @return void
     */
    public function show_items($whereparams=null) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }

        if (!array_key_exists('hidden', $whereparams)) {
            $whereparams['hidden'] = 1;
        }
        $DB->set_field('surveypro_item', 'hidden', 0, $whereparams);
    }

    /**
     * hide_items
     *
     * @param $whereparams
     * @return void
     */
    public function hide_items($whereparams=null) {
        global $DB;

        if (empty($whereparams)) {
            $whereparams = array();
        }

        if (!array_key_exists('hidden', $whereparams)) {
            $whereparams['hidden'] = 0;
        }
        $DB->set_field('surveypro_item', 'hidden', 1, $whereparams);
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

}
