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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/*
 * The base class representing a field
 */
class mod_surveyproreport_uploadformmanager {
    /*
     * $cm
     */
    public $cm = null;

    /*
     * $context
     */
    public $context = null;

    /*
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /*
     * $submissionid: the ID of the saved surbey_submission
     */
    public $submissionid = 0;

    /*
     * $formpage: the form page as recalculated according to the first non empty page
     * do not confuse this properties with $this->formdata->formpage
     */
    public $formpage = null;

    /*
     * $view
     */
    public $view = SURVEYPRO_EDITRESPONSE;

    /*
     * $moduletab: The tab of the module where the page will be shown
     */
    public $moduletab = '';

    /*
     * $modulepage: this is the page of the module. Nothing to share with $formpage
     */
    public $modulepage = '';

    /*
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /*
     * $canseeotherssubmissions
     */
    public $canseeotherssubmissions = false;

    /*
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /*
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro, $userid, $itemid, $submissionid) {
        global $DB;

        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
        $this->userid = $userid;
        $this->itemid = $itemid;
        $this->submissionid = $submissionid;
        $this->view = SURVEYPRO_EDITRESPONSE;

        // $this->canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);
        $this->canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);

        // assign pages to items
        if (!$this->maxassignedpage = $DB->get_field('surveypro_item', 'MAX(formpage)', array('surveyproid' => $surveypro->id))) {
            $this->assign_pages();
        }

        // calculare $this->firstpageright
        if ($this->canaccessadvanceditems) {
            $this->firstpageright = 1;
        } else {
            $this->next_not_empty_page(true, 0, $view); // this calculates $this->firstformpage
        }

        $this->formpage = $this->firstpageright;
    }

    /*
     * get_prefill_data
     *
     * @return
     */
    public function get_prefill_data() {
        global $DB;

        $prefill = array();

        if (!empty($this->submissionid)) {
            // $canaccessadvanceditems, $searchform=false, $type=SURVEYPRO_TYPEFIELD, $formpage=$this->formpage
            list($sql, $whereparams) = surveypro_fetch_items_seeds($this->surveypro->id, $this->canaccessadvanceditems, false, SURVEYPRO_TYPEFIELD, $this->formpage);
            if ($itemseeds = $DB->get_recordset_sql($sql, $whereparams)) {
                foreach ($itemseeds as $itemseed) {
                    if ($this->itemid) {
                        if ($this->itemid != $itemseed->id) {
                            continue;
                        }
                    }
                    $item = surveypro_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

                    $olduserdata = $DB->get_record('surveypro_answer', array('submissionid' => $this->submissionid, 'itemid' => $item->get_itemid()));
                    $singleprefill = $item->userform_set_prefill($olduserdata);
                    $prefill = array_merge($prefill, $singleprefill);
                }
                $itemseeds->close();
            }

            $prefill['submissionid'] = $this->submissionid;
        }

        return $prefill;
    }

    /*
     * prevent_direct_user_input
     *
     * @return
     */
    public function prevent_direct_user_input() {
        $allowed = has_capability('mod/surveypro:accessreports', $this->context, null, true);

        if (!$allowed) {
            print_error('incorrectaccessdetected', 'surveypro');
        }
    }
}
