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
 * The userform class
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class managing the form where users are supposed to enter expected data
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_view_form extends mod_surveypro_formbase {

    /**
     * @var int Next non empty page
     */
    protected $firstpageright;

    /**
     * @var int First non empty page
     */
    protected $firstpageleft;

    /**
     * @var int $view
     */
    protected $view;

    /**
     * @var int Tab of the module where the page will be shown
     */
    protected $tabtab;

    /**
     * @var int This is the page of the module. Nothing to share with $formpage
     */
    protected $tabpage;

    /**
     * @var int Final validation of the submitted response
     */
    protected $finalresponseevaluation;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Setup.
     *
     * @param int $submissionid
     * @param int $formpage
     * @param int $view
     * @return void
     */
    public function setup($submissionid, $formpage, $view) {
        global $DB;

        // Assign pages to items.
        $maxassignedpage = $DB->get_field('surveypro_item', 'MAX(formpage)', array('surveyproid' => $this->surveypro->id));
        if (!$maxassignedpage) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $maxassignedpage = $utilityman->assign_pages();
            $this->set_maxassignedpage($maxassignedpage);
        } else {
            $this->set_maxassignedpage($maxassignedpage);
        }

        $this->set_view($view);
        $this->set_submissionid($submissionid);
        $this->set_formpage($formpage);

        $this->set_tabs_params();
        $this->prevent_direct_user_input();
        $this->trigger_event();
    }

    // MARK set.

    /**
     * Set view.
     *
     * @param int $view
     * @return void
     */
    private function set_view($view) {
        $this->view = $view;
    }

    /**
     * Set formpage.
     *
     * @param int $formpage
     * @return void
     */
    public function set_formpage($formpage) {
        if ($this->view === null) {
            $message = 'Please call set_view method of the class mod_surveypro_view_form before calling set_formpage';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, null, true);

        if ($canaccessreserveditems) {
            $this->firstpageright = 1;
        } else {
            $this->next_not_empty_page(true, 0); // This sets $this->firstformpage.
        }

        if ($formpage == 0) { // You are viewing the surveypro for the first time.
            $this->formpage = $this->firstpageright;
        } else {
            $this->formpage = $formpage;
        }
    }

    /**
     * Set first page left.
     *
     * @param int $firstpageleft
     * @return void
     */
    public function set_firstpageleft($firstpageleft) {
        $this->firstpageleft = $firstpageleft;
    }

    /**
     * Set first page right.
     *
     * @param int $firstpageright
     * @return void
     */
    public function set_firstpageright($firstpageright) {
        $this->firstpageright = $firstpageright;
    }

    /**
     * Set TAB tab.
     *
     * @param int $tabtab
     * @return void
     */
    public function set_tabtab($tabtab) {
        $this->tabtab = $tabtab;
    }

    /**
     * Set TAB page.
     *
     * @param int $tabpage
     * @return void
     */
    public function set_tabpage($tabpage) {
        $this->tabpage = $tabpage;
    }

    /**
     * Get first page left.
     *
     * @return the content of the $firstpageleft property
     */
    public function get_firstpageleft() {
        return $this->firstpageleft;
    }

    /**
     * Get first page right.
     *
     * @return the content of the $firstpageright property
     */
    public function get_firstpageright() {
        return $this->firstpageright;
    }

    /**
     * Get TAB tab.
     *
     * @return the content of the $tabtab property
     */
    public function get_tabtab() {
        return $this->tabtab;
    }

    /**
     * Get TAB page.
     *
     * @return the content of the $tabpage property
     */
    public function get_tabpage() {
        return $this->tabpage;
    }

    /**
     * Get the first NON EMPTY page on the right or on the left.
     *
     * Depending on answers provided by the user, the previous or next page may have no items to display
     * The purpose of this function is to get the first page WITH items
     *
     * If $rightdirection == true, this method sets...
     *     the page number of the lower non empty page (according to user answers)
     *     greater than $startingpage in $this->firstpageright;
     *     returns $nextpage or SURVEYPRO_RIGHT_OVERFLOW if no more empty pages are found on the right
     * If $rightdirection == false, this method sets...
     *     the page number of the greater non empty page (according to user answers)
     *     lower than $startingpage in $this->firstpageleft;
     *     returns $nextpage or SURVEYPRO_LEFT_OVERFLOW if no more empty pages are found on the left
     *
     * @param bool $rightdirection
     * @param int $startingpage
     * @return void
     */
    public function next_not_empty_page($rightdirection, $startingpage=null) {
        if ($startingpage === null) {
            $startingpage = $this->get_formpage();
        }

        $condition = ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) && ($rightdirection);
        $condition = $condition || (($startingpage == SURVEYPRO_LEFT_OVERFLOW) && (!$rightdirection));
        if ($condition) {
            $a = new stdClass();
            if ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) {
                $a->startingpage = 'SURVEYPRO_RIGHT_OVERFLOW';
            } else {
                $a->startingpage = 'SURVEYPRO_LEFT_OVERFLOW';
            }
            $a->methodname = 'next_not_empty_page';
            print_error('wrong_direction_found', 'mod_surveypro', null, $a);
        }

        if ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) {
            $startingpage = $this->get_maxassignedpage() + 1;
        }
        if ($startingpage == SURVEYPRO_LEFT_OVERFLOW) {
            $startingpage = 0;
        }

        if ($rightdirection) {
            $nextpage = ++$startingpage;
            // Here maxpage should be $maxformpage, but I have to add 1 because of ($i != $overflowpage).
            $overflowpage = $this->get_maxassignedpage() + 1;
        } else {
            $nextpage = --$startingpage;
            // Here minpage should be 1, but I have to take 1 out because of ($i != $overflowpage).
            $overflowpage = 0;
        }

        do {
            if ($this->page_has_items($nextpage)) {
                break;
            }
            $nextpage = ($rightdirection) ? ++$nextpage : --$nextpage;
        } while ($nextpage != $overflowpage);

        if ($rightdirection) {
            $firstpageright = ($nextpage == $overflowpage) ? SURVEYPRO_RIGHT_OVERFLOW : $nextpage;
            $this->set_firstpageright($firstpageright);
        } else {
            $firstpageleft = ($nextpage == $overflowpage) ? SURVEYPRO_LEFT_OVERFLOW : $nextpage;
            $this->set_firstpageleft($firstpageleft);
        }
    }

    /**
     * Declares if the passed page of the survey is going to hold at least one item.
     *
     * In this method, I am not ONLY going to check if the page $formpage has items
     * but I also verify that those items are going to be displayed
     * on the basis of the answers provided to their parent
     *
     * @param int $formpage
     * @return bool
     */
    private function page_has_items($formpage) {
        global $DB;

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, null, true);

        list($where, $params) = surveypro_fetch_items_seeds($this->surveypro->id, true, $canaccessreserveditems, null, null, $formpage);
        // Here I can not use get_recordset_select because I could browse returned records twice.
        $itemseeds = $DB->get_records_select('surveypro_item', $where, $params, 'sortindex', 'id, parentid, parentvalue');

        // Start looking ONLY at empty($itemseed->parentid) because it doesn't involve extra queries.
        foreach ($itemseeds as $itemseed) {
            if (empty($itemseed->parentid)) {
                // If at least one item has no parent, I finished. The page is going to display items.
                return true;
            }
        }

        foreach ($itemseeds as $itemseed) {
            $parentitem = surveypro_get_item($this->cm, $this->surveypro, $itemseed->parentid);
            if ($parentitem->userform_child_item_allowed_static($this->get_submissionid(), $itemseed)) {
                // If at least one parent allows its child, I finished. The page is going to display items.
                return true;
            }
        }

        // If you were not able to get out in the two previous occasions... this page is empty.
        return false;
    }

    /**
     * Set tabs params.
     *
     * @return void
     */
    private function set_tabs_params() {
        switch ($this->view) {
            case SURVEYPRO_NOVIEW:
                $this->set_tabtab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_tabpage(SURVEYPRO_SUBMISSION_CPANEL); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_NEWRESPONSE:
                $this->set_tabtab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_tabpage(SURVEYPRO_SUBMISSION_INSERT); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_EDITRESPONSE:
                $this->set_tabtab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_tabpage(SURVEYPRO_SUBMISSION_EDIT); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_READONLYRESPONSE:
                $this->set_tabtab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_tabpage(SURVEYPRO_SUBMISSION_READONLY); // Needed by tabs.class.php.
                break;
            default:
                $message = 'Unexpected $this->view = '.$this->view;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Save user submission.
     *
     * @return void
     */
    private function save_surveypro_submission() {
        global $USER, $DB;

        if (!$this->surveypro->newpageforchild) {
            $this->drop_unexpected_values();
        }

        $timenow = time();
        $savebutton = isset($this->formdata->savebutton);
        $saveasnewbutton = isset($this->formdata->saveasnewbutton);
        $nextbutton = isset($this->formdata->nextbutton);
        $pausebutton = isset($this->formdata->pausebutton);
        $prevbutton = isset($this->formdata->prevbutton);
        if ($saveasnewbutton) {
            $this->formdata->submissionid = 0;
        }

        $submission = new stdClass();
        if (empty($this->formdata->submissionid)) {
            // Add a new record to surveypro_submission.
            $submission->surveyproid = $this->surveypro->id;
            $submission->userid = $USER->id;
            $submission->timecreated = $timenow;

            // The idea is that I ALWAYS save, without care about which button was pressed.
            // Probably if empty($this->formdata->submissionid) then $prevbutton can't be pressed, but I don't care.
            // In the worst hypothesis it is a case that will never be verified.
            if ($nextbutton || $pausebutton || $prevbutton) {
                $submission->status = SURVEYPRO_STATUSINPROGRESS;
            }
            if ($savebutton || $saveasnewbutton) {
                $submission->status = SURVEYPRO_STATUSCLOSED;
            }

            $submission->id = $DB->insert_record('surveypro_submission', $submission);

            $eventdata = array('context' => $this->context, 'objectid' => $submission->id);
            $eventdata['other'] = array('view' => SURVEYPRO_NEWRESPONSE);
            $event = \mod_surveypro\event\submission_created::create($eventdata);
            $event->trigger();
        } else {
            // Surveypro_submission already exists.
            // And I asked to save again.
            if ($savebutton) {
                $submission->id = $this->formdata->submissionid;
                $submission->status = SURVEYPRO_STATUSCLOSED;
                $submission->timemodified = $timenow;
                $DB->update_record('surveypro_submission', $submission);
            } else {
                // I have $this->formdata->submissionid.
                // Case: "save" was requested, I am not here.
                // Case: "save as" was requested, I am not here.
                // Case: "prev" was requested, I am not here because in view_form.php the save_user_data() method is jumped.
                // Case: "next" was requested, so status = SURVEYPRO_STATUSINPROGRESS.
                // Case: "pause" was requested, I am not here because in view_form.php the save_user_data() method is jumped.
                $submission->id = $this->formdata->submissionid;
                $submission->status = SURVEYPRO_STATUSINPROGRESS;
            }

            $eventdata = array('context' => $this->context, 'objectid' => $submission->id);
            $eventdata['other'] = array('view' => SURVEYPRO_EDITRESPONSE);
            $event = \mod_surveypro\event\submission_modified::create($eventdata);
            $event->trigger();
        }

        // Before returning, set two class properties.
        $this->set_submissionid($submission->id);
        $this->status = $submission->status;
    }

    /**
     * There are items spreading out their value over more than one single field
     * so you may have more than one $this->formdata element referring to the same item
     * Es.:
     *   $fieldname = surveypro_field_datetime_1452_day
     *   $fieldname = surveypro_field_datetime_1452_year
     *   $fieldname = surveypro_field_datetime_1452_month
     *   $fieldname = surveypro_field_datetime_1452_hour
     *   $fieldname = surveypro_field_datetime_1452_minute
     *
     *   $fieldname = surveypro_field_select_1453_select
     *
     *   $fieldname = surveypro_field_age_1454_check
     *
     *   $fieldname = surveypro_field_rate_1455_group
     *   $fieldname = surveypro_field_rate_1455_1
     *   $fieldname = surveypro_field_rate_1455_2
     *   $fieldname = surveypro_field_rate_1455_3
     *
     *   $fieldname = surveypro_field_radiobutton_1456_noanswer
     *   $fieldname = surveypro_field_radiobutton_1456_text
     *
     * This method performs the following task:
     * 1. groups informations (eventually distributed over more mform elements)
     *    by itemid in the array $itemhelperinfo
     *
     * To do this, I start from:
     *    preg_match($regex, $elementname, $matches)
     *    var_dump($matches);
     *    $matches = array{
     *        0 => string 'surveypro_field_radiobutton_1456' (length=27)
     *        1 => string 'surveypro' (length=6)
     *        2 => string 'field' (length=5)
     *        3 => string 'radiobutton' (length=11)
     *        4 => string '1456' (length=4)
     *    }
     *    $matches = array{
     *        0 => string 'surveypro_field_radiobutton_1456_check' (length=33)
     *        1 => string 'surveypro' (length=6)
     *        2 => string 'field' (length=5)
     *        3 => string 'radiobutton' (length=11)
     *        4 => string '1456' (length=4)
     *        5 => string 'check' (length=5)
     *    }
     *    $matches = array{
     *        0 => string 'surveypro_field_checkbox_1452_73' (length=30)
     *        1 => string 'surveypro' (length=6)
     *        2 => string 'field' (length=5)
     *        3 => string 'checkbox' (length=8)
     *        4 => string '1452' (length=4)
     *        5 => string '73' (length=2)
     *    }
     *    $matches = array{
     *        0 => string 'placeholder_field_multiselect_199_placeholder' (length=45)
     *        1 => string 'placeholder' (length=11)
     *        2 => string 'field' (length=5)
     *        3 => string 'multiselect' (length=11)
     *        4 => string '199' (length=3)
     *        5 => string 'placeholder' (length=11)
     *    }
     *
     *    and I arrive to define:
     *
     *    $itemhelperinfo = Array (
     *        [148] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 60
     *            [type] => field
     *            [plugin] => age
     *            [itemid] => 148
     *            [contentperelement] => Array (
     *                [year] => 5
     *                [month] => 9
     *            )
     *        )
     *        [149] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => boolean
     *            [itemid] => 149
     *            [contentperelement] => Array (
     *                [noanswer] => 1
     *            )
     *        )
     *        [150] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => character
     *            [itemid] => 150
     *            [contentperelement] => Array (
     *                [mainelement] => horse
     *            )
     *        )
     *        [151] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => fileupload
     *            [itemid] => 151
     *            [contentperelement] => Array (
     *                [filemanager] => 667420320
     *            )
     *        )
     *        [185] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => checkbox
     *            [itemid] => 185
     *            [contentperelement] => Array (
     *                [0] => 1
     *                [1] => 0
     *                [2] => 1
     *                [3] => 0
     *                [noanswer] => 0
     *            )
     *        )
     *        [186] => stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => checkbox
     *            [itemid] => 186
     *            [contentperelement] => Array (
     *                [0] => 1
     *                [1] => 1
     *                [2] => 0
     *                [3] => 0
     *                [other] => 1
     *                [text] => Apple juice
     *                [noanswer] => 1
     *            )
     *        )
     *
     * 2. once $itemhelperinfo is onboard..
     *    I create or update the corresponding record
     *    asking to the parent class to manage its own data
     *    passing it $iteminfo->contentperelement.
     */
    public function save_user_data() {
        global $DB, $COURSE;

        $savebutton = isset($this->formdata->savebutton);
        $saveasnewbutton = isset($this->formdata->saveasnewbutton);
        $pausebutton = isset($this->formdata->pausebutton);
        $prevbutton = isset($this->formdata->prevbutton);

        // At each submission I need to save one 'surveypro_submission' and some 'surveypro_answer'.

        // Begin of: let's start by saving one record in surveypro_submission.
        // In save_surveypro_submission method I also assign $this->submissionid and $this->status.
        $this->save_surveypro_submission();
        // End of: let's start by saving one record in surveypro_submission.

        $itemhelperinfo = array();
        foreach ($this->formdata as $elementname => $content) {
            if (!$matches = mod_surveypro_utility::get_item_parts($elementname)) {
                // Button or something not relevant.
                if ($elementname == 's') {
                    $surveyproid = $content;
                }
                // This is the black hole where is thrown each useless info like:
                // - formpage
                // - nextbutton
                // and some more.
                continue; // To next foreach.
            } else {
                if ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX) {
                    continue; // To next foreach.
                }
            }

            $itemid = $matches['itemid'];
            if (!isset($itemhelperinfo[$itemid])) {
                $itemhelperinfo[$itemid] = new stdClass();
                $itemhelperinfo[$itemid]->surveyproid = $surveyproid;
                $itemhelperinfo[$itemid]->submissionid = $this->get_submissionid();
                $itemhelperinfo[$itemid]->type = $matches['type'];
                $itemhelperinfo[$itemid]->plugin = $matches['plugin'];
                $itemhelperinfo[$itemid]->itemid = $itemid;
            }
            if (!isset($matches['option'])) {
                $itemhelperinfo[$itemid]->contentperelement['mainelement'] = $content;
            } else {
                $itemhelperinfo[$itemid]->contentperelement[$matches['option']] = $content;
            }
        }

        // Once $itemhelperinfo is onboard...
        // ->   I update/create the corresponding record asking to each item class to manage its informations.

        foreach ($itemhelperinfo as $iteminfo) {
            $where = array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid);
            if (!$useranswer = $DB->get_record('surveypro_answer', $where)) {
                // Quickly make one new!
                $useranswer = new stdClass();
                $useranswer->surveyproid = $iteminfo->surveyproid;
                $useranswer->submissionid = $iteminfo->submissionid;
                $useranswer->itemid = $iteminfo->itemid;
                $useranswer->content = SURVEYPRO_DUMMYCONTENT;
                $useranswer->contentformat = null;

                $useranswer->id = $DB->insert_record('surveypro_answer', $useranswer);
            }
            $useranswer->timecreated = time();
            $useranswer->verified = ($prevbutton || $pausebutton) ? 0 : 1;

            $item = surveypro_get_item($this->cm, $this->surveypro, $iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            // In this method I only update $useranswer->content.
            // I do not really save to database.
            $item->userform_save_preprocessing($iteminfo->contentperelement, $useranswer, false);

            if ($useranswer->content == SURVEYPRO_DUMMYCONTENT) {
                print_error('wrong_userdatarec_found', 'mod_surveypro', null, SURVEYPRO_DUMMYCONTENT);
            } else {
                $DB->update_record('surveypro_answer', $useranswer);
            }
        }

        // Before closing the save session I need two more verifications.

        // FIRST VERIFICATION.
        // Let's suppose the following scenario.
        // 1) User is filling a surveypro divided into 4 pages.
        // 2) User fill all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 3 (the page the user still does not see) of the surveypro there is mandatory field.
        // 5) Because of 3) user jumps to page 4 and make the final submit.
        // This check is needed to verify that EACH mandatory surveypro field was actually saved.

        // SECOND VERIFICATION.
        // 1) User is filling a surveypro divided into 3 pages.
        // 2) User fill all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 2 of the surveypro there is a mandatory field.
        // 5) User return back to page 1 without filling the mandatory field.
        // 6) Page 2 is saved WITHOUT the mandatory field because when the user moves back, the form VALIDATION is not executed.
        // 7) Because of 3) user jumps to page 3 and make the final submit.
        // This check is needed to verify that EACH surveypro field was actually saved as VERIFIED.

        if ($savebutton || $saveasnewbutton) {
            // Let's start with the lightest check (lightest in terms of query).
            $this->check_all_was_verified();
            if ($this->finalresponseevaluation == SURVEYPRO_VALIDRESPONSE) { // If this answer is still considered valid...
                // ...check more.
                $this->check_mandatories_are_in();
            }

            // If this answer is not valid for some reason.
            if ($this->finalresponseevaluation != SURVEYPRO_VALIDRESPONSE) {
                // User jumped pages using direct input (or something more dangerous).
                // Set this submission as SURVEYPRO_STATUSINPROGRESS.
                $conditions = array('id' => $this->get_submissionid());
                $DB->set_field('surveypro_submission', 'status', SURVEYPRO_STATUSINPROGRESS, $conditions);
            }
        }

        // Update completion state.
        $completion = new completion_info($COURSE);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_COMPLETE);
        }
    }

    /**
     * Check mandatory item were filled.
     *
     * @return void
     */
    private function check_mandatories_are_in() {
        global $CFG, $DB;

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context, null, true);

        // Get the list of used plugin.
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $pluginlist = $utilityman->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        // Begin of: get the list of all mandatory fields.
        $requireditems = array();
        foreach ($pluginlist as $plugin) {
            $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'_'.SURVEYPRO_TYPEFIELD;
            $canbemandatory = $classname::item_uses_mandatory_dbfield();
            if ($canbemandatory) {
                $sql = 'SELECT i.id, i.parentid, i.parentvalue, i.reserved
                        FROM {surveypro_item} i
                          JOIN {surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'} p ON i.id = p.itemid
                        WHERE i.surveyproid = :surveyproid
                            AND i.hidden = :hidden
                            AND p.required > :required
                        ORDER BY p.itemid';

                $whereparams = array('surveyproid' => $this->surveypro->id, 'hidden' => 0, 'required' => 0);
                $pluginitems = $DB->get_records_sql($sql, $whereparams);

                foreach ($pluginitems as $pluginitem) {
                    if ( (!$pluginitem->reserved) || $canaccessreserveditems ) {
                        // Just to save few bits of RAM.
                        unset ($pluginitem->reserved);

                        $requireditems[] = $pluginitem;
                    }
                }
            }
        }

        // Make only ONE query taking ALL the answer provided in the frame of this submission.
        // (and, implicitally, for this surveypro).
        $whereparams = array('submissionid' => $this->get_submissionid());
        $providedanswers = $DB->get_records_menu('surveypro_answer', $whereparams, 'itemid', 'itemid, 1');

        foreach ($requireditems as $itemseed) {
            if (!isset($providedanswers[$itemseed->id])) { // Answer was not provided for the required item.
                if (empty($itemseed->parentid)) { // There is no parent item!!! Answer was jumped.
                    $this->finalresponseevaluation = SURVEYPRO_MISSINGMANDATORY;
                    break;
                } else {
                    $parentitem = surveypro_get_item($this->cm, $this->surveypro, $itemseed->parentid);
                    if ($parentitem->userform_child_item_allowed_static($this->get_submissionid(), $itemseed)) {
                        // Parent is here but it allows this item as child in this submission. Answer was jumped.
                        // Take care: this check is valid for chains of parent-child relations too.
                        // If the parent item was not allowed by its parent,
                        // it was not answered and userform_child_item_allowed_static returns false.
                        $this->finalresponseevaluation = SURVEYPRO_MISSINGMANDATORY;
                    }
                }
            }
        }
    }

    /**
     * Check that each answer of the passed submission was actually verified at submission time.
     *
     * @return void
     */
    private function check_all_was_verified() {
        global $DB;

        $conditions = array('submissionid' => $this->get_submissionid(), 'verified' => 0);
        if ($DB->get_record('surveypro_answer', $conditions, 'id', IGNORE_MULTIPLE)) {
            $this->finalresponseevaluation = SURVEYPRO_MISSINGVALIDATION;
        }
    }

    /**
     * Drop old answers into pages no longer valid.
     *
     * Ok, I am moving from $userformman->formpage to page $userformman->firstpageright.
     * I need to delete all the answer that were (maybe) written during last input session in this surveypro.
     * Answers to each item in a page between ($this->formpage + 1) and ($this->firstpageright - 1) included, must be deleted.
     *
     * Example: I am leaving page 3. On the basis of current input (in this page), I have $userformman->firstpageright = 10.
     * Maybe yesterday I had different data in $userformman->formpage = 3 and on that basis I was redirected to page 4.
     * Now that data of $userformman->formpage = 3 redirects me to page 10, for sure answers to items in page 4 must be deleted.
     *
     * @return void
     */
    public function drop_jumped_saved_data() {
        global $DB;

        if ($this->firstpageright == ($this->get_formpage() + 1)) {
            return;
        }

        $pages = range($this->get_formpage() + 1, $this->firstpageright - 1);
        $where = 'surveyproid = :surveyproid
                AND formpage IN ('.implode(',', $pages).')';
        $itemlistid = $DB->get_records_select('surveypro_item', $where, array('surveyproid' => $this->surveypro->id), 'id', 'id');
        $itemlistid = array_keys($itemlistid);

        $where = 'submissionid = :submissionid
            AND itemid IN ('.implode(',', $itemlistid).')';
        $DB->delete_records_select('surveypro_answer', $where, array('submissionid' => $this->formdata->submissionid));
    }

    /**
     * Notifypeople.
     *
     * @return void
     */
    public function notifypeople() {
        global $CFG, $DB, $COURSE, $USER;

        require_once($CFG->dirroot.'/group/lib.php');

        if ($this->status != SURVEYPRO_STATUSCLOSED) {
            return;
        }
        if (empty($this->surveypro->notifyrole) && empty($this->surveypro->notifymore)) {
            return;
        }

        // Course context used locally to get groups.
        $context = context_course::instance($COURSE->id);

        $mygroups = groups_get_all_groups($COURSE->id, $USER->id, $this->cm->groupingid);
        $mygroups = array_keys($mygroups);
        if ($this->surveypro->notifyrole) {
            if (count($mygroups)) {
                $roles = explode(',', $this->surveypro->notifyrole);
                $recipients = array();
                foreach ($mygroups as $mygroup) {
                    $fields = user_picture::fields('u').', u.maildisplay, u.mailformat';
                    $groupmemberroles = groups_get_members_by_role($mygroup, $COURSE->id, $fields);

                    foreach ($roles as $role) {
                        if (isset($groupmemberroles[$role])) {
                            $roledata = $groupmemberroles[$role];

                            foreach ($roledata->users as $singleuser) {
                                unset($singleuser->roles);
                                $recipients[] = $singleuser;
                            }
                        }
                    }
                }
            } else {
                // get_enrolled_users($courseid, $options = array()) <-- role is missing.
                // get_users_from_role_on_context($role, $context);  <-- this is ok but I need to call it once per $role.
                $whereparams = array('contextid' => $context->id);
                $sql = 'SELECT DISTINCT '.user_picture::fields('u').', u.maildisplay, u.mailformat
                        FROM {user} u
                          JOIN {role_assignments} ra ON u.id = ra.userid
                        WHERE contextid = :contextid
                          AND roleid IN ('.$this->surveypro->notifyrole.')';
                $recipients = $DB->get_records_sql($sql, $whereparams);
            }
        } else {
            // Notification to roles was not requested.
            $recipients = array();
        }

        if (!empty($this->surveypro->notifymore)) {
            $singleuser = new stdClass();
            $singleuser->id = -1;
            $singleuser->firstname = '';
            $singleuser->lastname = '';
            $singleuser->firstnamephonetic = '';
            $singleuser->lastnamephonetic = '';
            $singleuser->middlename = '';
            $singleuser->alternatename = '';
            $singleuser->maildisplay = 2;
            $singleuser->mailformat = 1;

            $morerecipients = surveypro_multilinetext_to_array($this->surveypro->notifymore);
            foreach ($morerecipients as $moreemail) {
                $singleuser->email = $moreemail;
                $recipients[] = $singleuser;
            }
        }

        $mailheader = '<head></head>
    <body id="email"><div>';
        $mailfooter = '</div></body>';

        $from = new object;
        $from->firstname = $COURSE->shortname;
        $from->lastname = $this->surveypro->name;
        $from->email = $CFG->noreplyaddress;
        $from->firstnamephonetic = '';
        $from->lastnamephonetic = '';
        $from->middlename = '';
        $from->alternatename = '';
        $from->maildisplay = 2;

        $a = new stdClass();
        $a->username = fullname($USER);
        $a->surveyproname = $this->surveypro->name;
        $a->title = get_string('reviewsubmissions', 'mod_surveypro');
        $a->href = $CFG->wwwroot.'/mod/surveypro/view.php?s='.$this->surveypro->id;

        $htmlbody = $mailheader;
        $htmlbody .= get_string('newsubmissionbody', 'mod_surveypro', $a);
        $htmlbody .= $mailfooter;

        $body = strip_tags($htmlbody);

        $subject = get_string('newsubmissionsubject', 'mod_surveypro');

        foreach ($recipients as $recipient) {
            email_to_user($recipient, $from, $subject, $body, $htmlbody);
        }
    }

    /**
     * Stop the page load with a warning because no submission is available.
     *
     * @return void
     */
    public function nomoresubmissions_stopexecution() {
        global $OUTPUT;

        $tabpage = $this->get_tabpage();
        if ($tabpage != SURVEYPRO_SUBMISSION_READONLY) {
            // If $this->formdata is available, this means that the form was already displayed and submitted.
            // So it is not the time to verify the user is allowed to submit one more surveypro.
            if ($this->formdata) {
                return;
            }

            // If submissionid is already defined I am not going to create one more new submission so the problem does not exist.
            if ($this->get_submissionid()) {
                return;
            }

            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            if (!$utilityman->can_submit_more()) {
                $message = get_string('nomoresubmissionsallowed', 'mod_surveypro', $this->surveypro->maxentries);
                echo $OUTPUT->notification($message, 'notifyproblem');

                $whereparams = array('id' => $this->cm->id);
                $continueurl = new moodle_url('/mod/surveypro/view.php', $whereparams);

                echo $OUTPUT->continue_button($continueurl);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    /**
     * Verify if the thanks page has to be displayed.
     *
     * Stop execution if thanks page is shown
     *
     * @return void
     */
    public function manage_thanks_page() {
        global $OUTPUT;

        $savebutton = isset($this->formdata->savebutton);
        $saveasnewbutton = isset($this->formdata->saveasnewbutton);
        if ($savebutton || $saveasnewbutton) {
            $this->show_thanks_page();
            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * Actually display the thanks page.
     *
     * @return void
     */
    private function show_thanks_page() {
        global $OUTPUT;

        if ($this->finalresponseevaluation == SURVEYPRO_MISSINGMANDATORY) {
            $a = get_string('statusinprogress', 'mod_surveypro');
            $message = get_string('missingmandatory', 'mod_surveypro', $a);
            echo $OUTPUT->notification($message, 'notifyproblem');
        }

        if ($this->finalresponseevaluation == SURVEYPRO_MISSINGVALIDATION) {
            $a = get_string('statusinprogress', 'mod_surveypro');
            $message = get_string('missingvalidation', 'mod_surveypro', $a);
            echo $OUTPUT->notification($message, 'notifyproblem');
        }

        if ($this->view == SURVEYPRO_EDITRESPONSE) {
            $message = get_string('basic_editthanks', 'mod_surveypro');
        } else {
            if (!empty($this->surveypro->thankshtml)) {
                $htmlbody = $this->surveypro->thankshtml;
                $component = 'mod_surveypro';
                $filearea = SURVEYPRO_THANKSHTMLFILEAREA;
                $message = file_rewrite_pluginfile_urls($htmlbody, 'pluginfile.php', $this->context->id, $component, $filearea, $this->surveypro->id);
            } else {
                $message = get_string('basic_submitthanks', 'mod_surveypro');
            }
        }

        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $cansubmitmore = $utilityman->can_submit_more();

        $paramurlbase = array('id' => $this->cm->id);
        if ($cansubmitmore) { // If the user is allowed to submit one more response.
            $paramurl = $paramurlbase + array('view' => SURVEYPRO_NEWRESPONSE);
            $buttonurl = new moodle_url('/mod/surveypro/view_form.php', $paramurl);
            $onemore = new single_button($buttonurl, get_string('addnewsubmission', 'mod_surveypro'));

            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
            $gotolist = new single_button($buttonurl, get_string('gotolist', 'mod_surveypro'));

            echo $OUTPUT->box_start('generalbox centerpara', 'notice');
            echo html_writer::tag('p', $message);
            echo html_writer::tag('div', $OUTPUT->render($onemore).$OUTPUT->render($gotolist), array('class' => 'buttons'));
            echo $OUTPUT->box_end();
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurlbase);
            $buttonlabel = get_string('gotolist', 'mod_surveypro');
            echo $OUTPUT->box($OUTPUT->single_button($buttonurl, $buttonlabel, 'get'), 'generalbox centerpara');
        }
    }

    /**
     * Add browsing buttons to the read only outform that does not display them by design.
     *
     * @return void
     */
    public function add_readonly_browsing_buttons() {
        global $OUTPUT;

        $params = array();
        $params['s'] = $this->surveypro->id;
        $params['submissionid'] = $this->get_submissionid();
        $params['view'] = SURVEYPRO_READONLYRESPONSE;

        $tabpage = $this->get_tabpage();
        if ($tabpage == SURVEYPRO_SUBMISSION_READONLY) {
            $maxassignedpage = $this->get_maxassignedpage();
            if ($maxassignedpage > 1) {
                $formpage = $this->get_formpage();
                if (($formpage != SURVEYPRO_LEFT_OVERFLOW) && ($formpage != 1)) {
                    $params['formpage'] = $formpage - 1;
                    $url = new moodle_url('/mod/surveypro/view_form.php', $params);
                    $backwardbutton = new single_button($url, get_string('previousformpage', 'mod_surveypro'), 'get');
                }

                if (($formpage != SURVEYPRO_RIGHT_OVERFLOW) && ($formpage != $maxassignedpage)) {
                    $params['formpage'] = $formpage + 1;
                    $url = new moodle_url('/mod/surveypro/view_form.php', $params);
                    $forwardbutton = new single_button($url, get_string('nextformpage', 'mod_surveypro'), 'get');
                }

                $params = array('class' => 'buttons');
                if (isset($backwardbutton) && isset($forwardbutton)) {
                    // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
                    // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
                    echo html_writer::tag('div', $OUTPUT->render($backwardbutton).$OUTPUT->render($forwardbutton), $params);
                } else {
                    if (isset($backwardbutton)) {
                        echo html_writer::tag('div', $OUTPUT->render($backwardbutton), $params);
                    }
                    if (isset($forwardbutton)) {
                        echo html_writer::tag('div', $OUTPUT->render($forwardbutton), $params);
                    }
                }
            }
        }
    }

    /**
     * Drop all the values returned by disabled items.
     *
     * If a child item is deisabled by the parent that is in the same child page,
     * the child answer is useless and must be deleted.
     *
     * @return void
     */
    private function drop_unexpected_values() {
        // Begin of: delete all the values returned by disabled items (that were NOT supposed to be returned: MDL-34815).
        $dirtydata = (array)$this->formdata;
        $indexes = array_keys($dirtydata);

        $disposelist = array();
        $olditemid = 0;

        foreach ($indexes as $elementname) {
            if (!$matches = mod_surveypro_utility::get_item_parts($elementname)) {
                continue;
            } else {
                if ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX) {
                    continue; // To next foreach.
                }
            }
            $type = $matches['type'];
            $plugin = $matches['plugin'];
            $itemid = $matches['itemid'];

            if ($itemid == $olditemid) {
                continue;
            }

            // Let's start.
            $olditemid = $itemid;

            $childitem = surveypro_get_item($this->cm, $this->surveypro, $itemid, $type, $plugin);

            $parentid = $childitem->get_parentid();
            if (empty($parentid)) {
                continue;
            }

            // If my parent is already in $disposelist, I have to go to $disposelist FOR SURE.
            if (in_array($childitem->get_parentid(), $disposelist)) {
                $disposelist[] = $childitem->get_itemid();
                continue;
            }

            // Call parentitem.
            $parentitem = surveypro_get_item($this->cm, $this->surveypro, $childitem->get_parentid());

            $parentinsamepage = false;
            foreach ($indexes as $elementname) {
                if (strpos($elementname, $parentitem->get_itemid())) {
                    $parentinsamepage = true;
                    break;
                }
            }

            if ($parentinsamepage) { // If parent is in this same page.
                // Tell parentitem what child needs in order to be displayed
                // and compare it with what was answered to parentitem ($dirtydata).
                $expectedvalue = $parentitem->userform_child_item_allowed_dynamic($childitem->get_parentvalue(), $dirtydata);
                // Parentitem, knowing itself, compare what is needed and provides an answer.

                if (!$expectedvalue) {
                    $disposelist[] = $childitem->get_itemid();
                }
            }
        } // Check next item.
        // End of: delete all the values returned by disabled items (that were NOT supposed to be returned: MDL-34815).

        // If not expected items are here...
        if (count($disposelist)) {
            foreach ($indexes as $elementname) {
                if ($matches = mod_surveypro_utility::get_item_parts($elementname)) {
                    if ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX) {
                        continue; // To next foreach.
                    }
                    $itemid = $matches['itemid'];
                    if (in_array($itemid, $disposelist)) {
                        unset($this->formdata->$elementname);
                    }
                }
            }
        }
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    private function prevent_direct_user_input() {
        global $DB, $USER, $COURSE;

        $cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);

        if (($this->view == SURVEYPRO_READONLYRESPONSE) || ($this->view == SURVEYPRO_EDITRESPONSE)) {
            $where = array('id' => $this->get_submissionid());
            if (!$submission = $DB->get_record('surveypro_submission', $where, '*', IGNORE_MISSING)) {
                print_error('incorrectaccessdetected', 'mod_surveypro');
            }
            if ($submission->userid != $USER->id) {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode == SEPARATEGROUPS) {
                    $mygroupmates = surveypro_groupmates($this->cm);
                    // If I am a teacher, $mygroupmates is empty but I still have the right to see all my students.
                    if (!$mygroupmates) { // I have no $mygroupmates. I am a teacher. I am active part of each group.
                        $groupuser = true;
                    } else {
                        $groupuser = in_array($submission->userid, $mygroupmates);
                    }
                }
            }
        }

        switch ($this->view) {
            case SURVEYPRO_NEWRESPONSE:
                $timenow = time();
                $allowed = $cansubmit;
                if ($this->surveypro->timeopen) {
                    $allowed = $allowed && ($this->surveypro->timeopen < $timenow);
                }
                if ($this->surveypro->timeclose) {
                    $allowed = $allowed && ($this->surveypro->timeclose > $timenow);
                }

                if (!$canignoremaxentries) {
                    // Take care! Let's suppose this scenario:
                    // $this->surveypro->maxentries = N
                    // $utilityman->has_submissions(true, SURVEYPRO_STATUSALL, $USER->id) = N - 1.
                    // When I fill the FIRST page of a survey, I get $next = N
                    // but when I go to fill the SECOND page of a survey I have one more "in progress" survey
                    // that is the one that I created when I saved the FIRST page, so...
                    // when $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N, I get
                    // $next = N + 1
                    // and I am wrongly stopped here!
                    // Because of this, I increase $next only if submissionid == 0.
                    $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                    $next = $utilityman->has_submissions(true, SURVEYPRO_STATUSALL, $USER->id);
                    if (!$this->get_submissionid()) {
                        $next += 1;
                    }

                    $allowed = $allowed && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
                }
                break;
            case SURVEYPRO_EDITRESPONSE:
                if ($USER->id == $submission->userid) {
                    // Whether in progress, always allow.
                    $allowed = ($submission->status == SURVEYPRO_STATUSINPROGRESS) ? true : $caneditownsubmissions;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $allowed = $groupuser && $caneditotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS.
                        $allowed = $caneditotherssubmissions;
                    }
                }
                break;
            case SURVEYPRO_READONLYRESPONSE:
                if ($USER->id == $submission->userid) {
                    $allowed = true;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $allowed = $groupuser && $canseeotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS.
                        $allowed = $canseeotherssubmissions;
                    }
                }
                break;
            default:
                $allowed = false;
        }
        if (!$allowed) {
            print_error('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Trigger the submission_viewed event.
     *
     * @return void
     */
    private function trigger_event() {
        switch ($this->view) {
            case SURVEYPRO_NOVIEW:
            case SURVEYPRO_EDITRESPONSE: // Item_modified will be, eventually, logged.
            case SURVEYPRO_NEWRESPONSE:  // Item_created will be, eventually, logged.
                break;
            case SURVEYPRO_READONLYRESPONSE:
                // Event: submission_viewed.
                $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
                $eventdata['other'] = array('view' => SURVEYPRO_READONLYRESPONSE);
                $event = \mod_surveypro\event\submission_viewed::create($eventdata);
                $event->trigger();
                break;
            default:
                $message = 'Unexpected $this->view = '.$this->view;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }
}
