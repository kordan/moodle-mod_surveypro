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
 * Surveypro formbase class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\utility_layout;

/**
 * The base class representing the commom part of the item form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class formbase {

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
     * @var int Id of the saved submission
     */
    protected $submissionid;

    /**
     * @var int Form page as recalculated according to the first non empty page
     */
    protected $formpage;

    /**
     * @var int Last page of the out form
     */
    protected $maxassignedpage;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    /**
     * Stop the page load with a warning because no item is available.
     *
     * @return void
     */
    public function noitem_stopexecution() {
        global $COURSE, $OUTPUT;

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);

        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        if (!$utilitylayoutman->layout_has_items(0, SURVEYPRO_TYPEFIELD, false, $canaccessreserveditems)) {
            $canmanageitems = has_capability('mod/surveypro:manageitems', $this->context);

            if ($canmanageitems) {
                $a = get_string('tablayoutname', 'mod_surveypro');
                $a .= ' > ';
                $a .= get_string('tabitemspage2', 'mod_surveypro');
                $message = get_string('noitemsfoundadmin', 'mod_surveypro', $a);
                echo $OUTPUT->notification($message, 'notifyproblem');
            } else {
                // More or less no user without $canmanageitems should ever be here.
                $message = get_string('noitemsfound', 'mod_surveypro');
                echo $OUTPUT->container($message, 'notifyproblem');

                $continueurl = new \moodle_url('/course/view.php', array('id' => $COURSE->id));
                echo $OUTPUT->continue_button($continueurl);
            }

            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * Get prefill data.
     *
     * @return array
     */
    public function get_prefill_data() {
        global $DB;

        $prefill = array();
        if (empty($this->submissionid)) {
            return $prefill;
        }

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);
        $id = $this->surveypro->id;
        $page = $this->formpage;
        list($where, $params) = surveypro_fetch_items_seeds($id, true, $canaccessreserveditems, null, SURVEYPRO_TYPEFIELD, $page);
        if ($itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin')) {
            foreach ($itemseeds as $itemseed) {
                $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

                $where = array('submissionid' => $this->submissionid, 'itemid' => $item->get_itemid());
                $olduserdata = $DB->get_record('surveypro_answer', $where);
                $singleprefill = $item->userform_set_prefill($olduserdata);
                $prefill = array_merge($prefill, $singleprefill);
            }
            $itemseeds->close();
        }

        $prefill['submissionid'] = $this->submissionid;

        return $prefill;
    }

    /**
     * Display the text "Page x of y".
     *
     * @return void
     */
    public function display_page_x_of_y() {
        global $OUTPUT;

        if ($this->maxassignedpage > 1) {
            $a = new \stdClass();
            $a->formpage = $this->formpage;
            if ($this->formpage == SURVEYPRO_LEFT_OVERFLOW) {
                $a->formpage = 1;
            }
            if ($this->formpage == SURVEYPRO_RIGHT_OVERFLOW) {
                $a->formpage = $this->maxassignedpage;
            }

            $a->maxassignedpage = $this->maxassignedpage;
            echo $OUTPUT->heading(get_string('pagexofy', 'mod_surveypro', $a));
        }
    }

    /**
     * Warn this submission is a copy.
     *
     * @return void
     */
    public function warning_submission_copy() {
        global $OUTPUT;

        if ( (!empty($this->surveypro->history)) && (!empty($this->submissionid)) ) {
            echo $OUTPUT->notification(get_string('editingcopy', 'mod_surveypro'), 'notifysuccess');
        }
    }

    // MARK set.

    /**
     * Set submissionid.
     *
     * @param int $submissionid
     * @return void
     */
    public function set_submissionid($submissionid) {
        $this->submissionid = $submissionid;
    }

    /**
     * Set maxassignedpage.
     *
     * @param int $maxassignedpage
     * @return void
     */
    public function set_maxassignedpage($maxassignedpage) {
        $this->maxassignedpage = $maxassignedpage;
    }

    /**
     * Set formpage.
     *
     * @param int $formpage
     * @return void
     */
    public function set_formpage($formpage) {
        $this->formpage = ($formpage == 0) ? 1 : $formpage;
    }

    // MARK get.

    /**
     * Get submissionid.
     *
     * @return the content of $submissionid property
     */
    public function get_submissionid() {
        return $this->submissionid;
    }

    /**
     * Get submissionid.
     *
     * @return the content of $formpage property
     */
    public function get_formpage() {
        return $this->formpage;
    }

    /**
     * Get max assigned page.
     *
     * @return the content of $maxassignedpage property
     */
    public function get_maxassignedpage() {
        return $this->maxassignedpage;
    }
}
