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

require_once($CFG->dirroot.'/mod/surveypro/classes/formbase.class.php');

/**
 * The base class representing a field
 */
class mod_surveypro_userform extends mod_surveypro_formbase {
    /**
     * $firstpageright
     */
    protected $firstpageright;

    /**
     * $firstpageleft
     */
    protected $firstpageleft;

    /**
     * $view
     */
    protected $view;

    /**
     * $moduletab: The tab of the module where the page will be shown
     */
    protected $moduletab;

    /**
     * $modulepage: this is the page of the module. Nothing to share with $formpage
     */
    protected $modulepage;

    /**
     * $finalresponseevaluation: final validation of the submitted response
     */
    protected $finalresponseevaluation;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata;

    /**
     * Do what is needed ONLY AFTER the view parameter is set
     * setup
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

    // MARK set

    /**
     * set_view
     *
     * @param $view
     * @return void
     */
    private function set_view($view) {
        $this->view = $view;
    }

    /**
     * set_formpage
     *
     * @param $formpage
     * @return void
     */
    public function set_formpage($formpage) {
        if ($this->view === null) {
            $message = 'Please call set_view method of the class mod_surveypro_userform before calling set_formpage';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);

        if ($canaccessadvanceditems) {
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
     * set_firstpageleft
     *
     * @param $firstpageleft
     * @return void
     */
    public function set_firstpageleft($firstpageleft) {
        $this->firstpageleft = $firstpageleft;
    }

    /**
     * set_firstpageright
     *
     * @param $firstpageright
     * @return void
     */
    public function set_firstpageright($firstpageright) {
        $this->firstpageright = $firstpageright;
    }

    /**
     * set_moduletab
     *
     * @param $moduletab
     * @return void
     */
    public function set_moduletab($moduletab) {
        $this->moduletab = $moduletab;
    }

    /**
     * set_modulepage
     *
     * @param $modulepage
     * @return void
     */
    public function set_modulepage($modulepage) {
        $this->modulepage = $modulepage;
    }

    /**
     * get_firstpageleft
     *
     * @param none
     * @return the content of the $firstpageleft property
     */
    public function get_firstpageleft() {
        return $this->firstpageleft;
    }

    /**
     * get_firstpageright
     *
     * @param none
     * @return the content of the $firstpageright property
     */
    public function get_firstpageright() {
        return $this->firstpageright;
    }

    /**
     * get_moduletab
     *
     * @param none
     * @return the content of the $moduletab property
     */
    public function get_moduletab() {
        return $this->moduletab;
    }

    /**
     * get_modulepage
     *
     * @param none
     * @return the content of the $modulepage property
     */
    public function get_modulepage() {
        return $this->modulepage;
    }

    /**
     * Get the first NON EMPTY page on the right or on the left
     *
     * Depending on answers provided by the user, the previous or next page may have no items to display.
     * The purpose of this function is to get the first page WITH items.
     *
     * If $rightdirection == true, this method sets...
     *     the page number of the lower non empty page (according to user answers) greater than $startingpage in $this->firstpageright;
     *     returns $nextpage or SURVEYPRO_RIGHT_OVERFLOW if no more empty pages are found on the right.
     * If $rightdirection == false, this method sets...
     *     the page number of the greater non empty page (according to user answers) lower than $startingpage in $this->firstpageleft;
     *     returns $nextpage or SURVEYPRO_LEFT_OVERFLOW if no more empty pages are found on the left.
     *
     * @param $rightdirection
     * @param $startingpage
     * @return
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
            $overflowpage = $this->get_maxassignedpage() + 1; // Maxpage = $maxformpage, but I have to add      1 because of ($i != $overflowpage).
        } else {
            $nextpage = --$startingpage;
            $overflowpage = 0;                          // Minpage = 1,            but I have to subtract 1 because of ($i != $overflowpage).
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
     * page_has_items
     *
     * In this method, I am not ONLY going to check if the page $formpage has item
     * but I am also verifying that those items are supposed to be displayed
     * on the basis of the answers provided to their parents.
     *
     * @param $formpage
     * @return
     */
    private function page_has_items($formpage) {
        global $CFG, $DB;

        $canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);

        list($sql, $whereparams) = surveypro_fetch_items_seeds($this->surveypro->id, $canaccessadvanceditems, false, false, $formpage);
        $itemseeds = $DB->get_records_sql($sql, $whereparams);

        // Start looking ONLY at empty($itemseed->parentid) because it doesn't involve extra queries.
        foreach ($itemseeds as $itemseed) {
            if (empty($itemseed->parentid)) {
                // If at least one item has no parent, I finished. The page is going to display items.
                return true;
            }
        }

        foreach ($itemseeds as $itemseed) {
            // Skip format items.
            if ($itemseed->type == SURVEYPRO_TYPEFORMAT) {
                continue;
            }

            $parentplugin = $DB->get_field('surveypro_item', 'plugin', array('id' => $itemseed->parentid));
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$parentplugin.'/classes/plugin.class.php');

            $itemclass = 'mod_surveypro_field_'.$parentplugin;
            $parentitem = new $itemclass($this->cm, $itemseed->parentid, false);

            if ($parentitem->userform_child_item_allowed_static($this->get_submissionid(), $itemseed)) {
                // If at least one parent allows its child, I finished. The page is going to display items.
                return true;
            }
        }

        // If you were not able to get out in the two previous occasions... this page is empty.
        return false;
    }

    /**
     * set_tabs_params
     *
     * @param none
     * @return
     */
    private function set_tabs_params() {
        switch ($this->view) {
            case SURVEYPRO_NOVIEW:
                $this->set_moduletab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_modulepage(SURVEYPRO_SUBMISSION_CPANEL); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_NEWRESPONSE:
                $this->set_moduletab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_modulepage(SURVEYPRO_SUBMISSION_INSERT); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_EDITRESPONSE:
                $this->set_moduletab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_modulepage(SURVEYPRO_SUBMISSION_EDIT); // Needed by tabs.class.php.
                break;
            case SURVEYPRO_READONLYRESPONSE:
                $this->set_moduletab(SURVEYPRO_TABSUBMISSIONS); // Needed by tabs.class.php.
                $this->set_modulepage(SURVEYPRO_SUBMISSION_READONLY); // Needed by tabs.class.php.
                break;
            default:
                $message = 'Unexpected $this->view = '.$this->view;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * surveypro_add_custom_css
     *
     * @param none
     * @return
     */
    public function surveypro_add_custom_css() {
        global $PAGE;

        $fs = get_file_storage();
        if ($files = $fs->get_area_files($this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false)) {
            $PAGE->requires->css('/mod/surveypro/userstyle.php?id='.$this->surveypro->id.'&amp;cmid='.$this->cm->id); // Not overridable via themes!
        }
    }

    /**
     * save_surveypro_submission
     *
     * @param none
     * @return surveypro_submission record
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
     * there are items spreading out their value over more than one single field
     * so you may have more than one $this->formdata element referring to the same item
     * Es.:
     *   $fieldname = surveypro_datetime_1452_day
     *   $fieldname = surveypro_datetime_1452_year
     *   $fieldname = surveypro_datetime_1452_month
     *   $fieldname = surveypro_datetime_1452_hour
     *   $fieldname = surveypro_datetime_1452_minute
     *
     *   $fieldname = surveypro_select_1452_select
     *
     *   $fieldname = surveypro_age_1452_check
     *
     *   $fieldname = surveypro_rate_1452_group
     *   $fieldname = surveypro_rate_1452_1
     *   $fieldname = surveypro_rate_1452_2
     *   $fieldname = surveypro_rate_1452_3
     *
     *   $fieldname = surveypro_radio_1452_noanswer
     *   $fieldname = surveypro_radio_1452_text
     *
     * This method performs the following task:
     * 1. groups informations (eventually distributed over more mform elements)
     *    by itemid in the array $itemhelperinfo
     *
     *    i.e.:
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
     * 2. once $itemhelperinfo is onboard...
     *    I update or I create the corresponding record
     *    asking to the parent class to manage its own data
     *    passing it $iteminfo->contentperelement
     */
    public function save_user_data() {
        global $DB;

        $savebutton = isset($this->formdata->savebutton);
        $saveasnewbutton = isset($this->formdata->saveasnewbutton);
        $pausebutton = isset($this->formdata->pausebutton);
        $prevbutton = isset($this->formdata->prevbutton);

        // At each submission I need to save one 'surveypro_submission' and some 'surveypro_answer'.

        // Begin of: let's start by saving one record in surveypro_submission.
        // In save_surveypro_submission method I also assign $this->submissionid and $this->status.
        $this->save_surveypro_submission();
        // End of: let's start by saving one record in surveypro_submission.

        // Save now all the answers provided by the user.
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_DONTSAVEMEPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

        $itemhelperinfo = array();
        foreach ($this->formdata as $itemname => $content) {
            if (!preg_match($regexp, $itemname, $matches)) {
                // Button or something not relevant.
                if ($itemname == 's') {
                    $surveyproid = $content;
                    // } else {
                    // This is the black hole where is thrown each useless info like:
                    // - formpage
                    // - nextbutton
                    // and some more.
                }
                continue; // To next foreach.
            }

            // var_dump($matches);
            // $matches = array{
            // ->    0 => string 'surveypro_field_radiobutton_1452' (length=27)
            // ->    1 => string 'surveypro' (length=6)
            // ->    2 => string 'field' (length=5)
            // ->    3 => string 'radiobutton' (length=11)
            // ->    4 => string '1452' (length=4)
            // }
            // $matches = array{
            // ->    0 => string 'surveypro_field_radiobutton_1452_check' (length=33)
            // ->    1 => string 'surveypro' (length=6)
            // ->    2 => string 'field' (length=5)
            // ->    3 => string 'radiobutton' (length=11)
            // ->    4 => string '1452' (length=4)
            // ->    5 => string 'check' (length=5)
            // }
            // $matches = array{
            // ->    0 => string 'surveypro_field_checkbox_1452_73' (length=30)
            // ->    1 => string 'surveypro' (length=6)
            // ->    2 => string 'field' (length=5)
            // ->    3 => string 'checkbox' (length=8)
            // ->    4 => string '1452' (length=4)
            // ->    5 => string '73' (length=2)
            // }
            // $matches = array{
            // ->    0 => string 'placeholder_field_multiselect_199_placeholder' (length=45)
            // ->    1 => string 'placeholder' (length=11)
            // ->    2 => string 'field' (length=5)
            // ->    3 => string 'multiselect' (length=11)
            // ->    4 => string '199' (length=3)
            // ->    5 => string 'placeholder' (length=11)
            // }

            $itemid = $matches[4]; // Itemid of the mform element (or of the group of mform elements referring to the same item).
            if (!isset($itemhelperinfo[$itemid])) {
                $itemhelperinfo[$itemid] = new stdClass();
                $itemhelperinfo[$itemid]->surveyproid = $surveyproid;
                $itemhelperinfo[$itemid]->submissionid = $this->get_submissionid();
                $itemhelperinfo[$itemid]->type = $matches[2];
                $itemhelperinfo[$itemid]->plugin = $matches[3];
                $itemhelperinfo[$itemid]->itemid = $itemid;
            }
            if (!isset($matches[5])) {
                $itemhelperinfo[$itemid]->contentperelement['mainelement'] = $content;
            } else {
                $itemhelperinfo[$itemid]->contentperelement[$matches[5]] = $content;
            }
        }

        // once $itemhelperinfo is onboard...
        // ->   I update/create the corresponding record
        // ->   asking to each item class to manage its informations

        foreach ($itemhelperinfo as $iteminfo) {
            if (!$useranswer = $DB->get_record('surveypro_answer', array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid))) {
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

            $item = surveypro_get_item($this->cm, $iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

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

        // FIRST VERIFICATION
        // Let's suppose the following scenario.
        // 1) User is filling a surveypro divided into 4 pages.
        // 2) User fill all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 3 (the page the user still does not see) of the surveypro there is mandatory field.
        // 5) Because of 3) user jumps to page 4 and make the final submit.
        // This check is needed to verify that EACH mandatory surveypro field was actually saved

        // SECOND VERIFICATION
        // 1) User is filling a surveypro divided into 3 pages.
        // 2) User fill all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 2 of the surveypro there is a mandatory field.
        // 5) User return back to page 1 without filling the mandatory field.
        // 6) Page 2 is saved WITHOUT the mandatory field because when the user moves back, the form validation is not executed.
        // 7) Because of 3) user jumps to page 3 and make the final submit.
        // This check is needed to verify that EACH surveypro field was actually saved as VERIFIED

        if ($savebutton || $saveasnewbutton) {
            // Let's start with the lightest check (lightest in terms of query).
            $this->check_all_was_verified();
            if ($this->finalresponseevaluation == SURVEYPRO_VALIDRESPONSE) { // If this answer is still considered valid, check more.
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

        // Update completion state
        $course = $DB->get_record('course', array('id' => $this->cm->course), '*', MUST_EXIST);
        $completion = new completion_info($course);
        if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
            $completion->update_state($this->cm, COMPLETION_COMPLETE);
        }
    }

    /**
     * check_mandatories_are_in
     *
     * @param none
     * @return
     */
    private function check_mandatories_are_in() {
        global $CFG, $DB;

        $canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);

        // Begin of: get the list of all mandatory fields.
        $sql = 'SELECT MIN(id), plugin
            FROM {surveypro_item}
            WHERE surveyproid = :surveyproid
                AND type = :type
            GROUP BY plugin';
        $whereparams = array('surveyproid' => $this->surveypro->id, 'type' => SURVEYPRO_TYPEFIELD);
        $pluginlist = $DB->get_records_sql_menu($sql, $whereparams);

        $requireditems = array();
        foreach ($pluginlist as $plugin) {
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$plugin.'/classes/plugin.class.php');

            $itemclass = 'mod_surveypro_'.SURVEYPRO_TYPEFIELD.'_'.$plugin;

            $itemcanbemandatory = $itemclass::item_get_can_be_mandatory();
            if ($itemcanbemandatory) {
                $sql = 'SELECT i.id, i.parentid, i.parentvalue, i.advanced, p.required
                    FROM {surveypro_item} i
                        JOIN {surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'} p ON i.id = p.itemid
                    WHERE i.surveyproid = :surveyproid
                    ORDER BY p.itemid';

                $whereparams = array('surveyproid' => $this->surveypro->id);
                $pluginitems = $DB->get_records_sql($sql, $whereparams);

                foreach ($pluginitems as $pluginitem) {
                    if ($pluginitem->required > 0) {
                        if ( (!$pluginitem->advanced) || $canaccessadvanceditems ) {
                            // Just to save few bits of RAM.
                            unset ($pluginitem->required);
                            unset ($pluginitem->advanced);

                            $requireditems[$pluginitem->id] = $pluginitem;
                        }
                    }
                }
            }
        }

        // $requireditems = array (size=3)
        // ->  7521 =>
        // ->    object(stdClass)[1108]
        // ->      public 'id' => string '7521' (length=4)
        // ->      public 'parentid' => string '0' (length=1)
        // ->      public 'parentvalue' => null
        // ->  7527 =>
        // ->    object(stdClass)[889]
        // ->      public 'id' => string '7527' (length=4)
        // ->      public 'parentid' => string '0' (length=1)
        // ->      public 'parentvalue' => null
        // ->  7528 =>
        // ->    object(stdClass)[1107]
        // ->      public 'id' => string '7528' (length=4)
        // ->      public 'parentid' => string '0' (length=1)
        // ->      public 'parentvalue' => null
        // End of: get the list of all mandatory fields.

        // Make only ONE query taking ALL the answer provided in the frame of this submission.
        // (and, implicitally, for this surveypro).
        $whereparams = array('submissionid' => $this->get_submissionid());
        $providedanswers = $DB->get_records_menu('surveypro_answer', $whereparams, 'itemid', 'itemid, 1');

        foreach ($requireditems as $itemseed) {
            if (!isset($providedanswers[$itemseed->id])) { // Required item was not answered.
                if (empty($itemseed->parentid)) { // There is no parent item!!! Answer was jumped.
                    $this->finalresponseevaluation = SURVEYPRO_MISSINGMANDATORY;
                    break;
                } else {
                    $parentitem = surveypro_get_item($this->cm, $itemseed->parentid);
                    if ($parentitem->userform_child_item_allowed_static($this->get_submissionid(), $itemseed)) {
                        // Parent is here but it allows this item as child in this submission. Answer was jumped.
                        // TAKE CARE: this check is valid for chains of parent-child relations too.
                        // If the parent item was not allowed by its parent,
                        // it was not answered and userform_child_item_allowed_static returns false.
                        $this->finalresponseevaluation = SURVEYPRO_MISSINGMANDATORY;
                    }
                }
            }
        }
    }

    /**
     * check_all_verified
     *
     * @param none
     * @return
     */
    private function check_all_was_verified() {
        global $DB;

        $conditions = array('submissionid' => $this->get_submissionid(), 'verified' => 0);
        if ($DB->get_record('surveypro_answer', $conditions, 'id', IGNORE_MULTIPLE)) {
            $this->finalresponseevaluation = SURVEYPRO_MISSINGVALIDATION;
        }
        // echo '$this->finalresponseevaluation:';
        // var_dump($this->finalresponseevaluation);
    }

    /**
     * drop_jumped_saved_data
     *
     * @param none
     * @return
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
     * notifypeople
     *
     * @param none
     * @return
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
                    $groupmemberroles = groups_get_members_by_role($mygroup, $COURSE->id, user_picture::fields('u').', u.maildisplay, u.mailformat');

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
                // get_users_from_role_on_context($role, $context);  <-- this is ok but I need to call it once per $role, below I make the query once all together.
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

            $morerecipients = surveypro_textarea_to_array($this->surveypro->notifymore);
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
     * submissions_allowed
     *
     * @param none
     * @return
     */
    public function submissions_allowed() {
        // If $this->formdata is available, this means that the form was already displayed and submitted.
        // So it is not the time to say the user is not allowed to submit one more surveypro.
        if ($this->formdata) {
            return true;
        }
        // If submissionid is already defined I am not going to create one more new submission.
        if ($this->get_submissionid()) {
            return true;
        }
        if (!$this->surveypro->maxentries) {
            return true;
        }
        if (has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true)) {
            return true;
        }

        return ($this->user_sent_submissions(SURVEYPRO_STATUSALL) < $this->surveypro->maxentries);
    }

    /**
     * user_sent_submissions
     *
     * @param $status
     * @return
     */
    private function user_sent_submissions($status=SURVEYPRO_STATUSALL) {
        global $USER, $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'userid' => $USER->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $statuslist = array(SURVEYPRO_STATUSCLOSED, SURVEYPRO_STATUSINPROGRESS);
            if (!in_array($status, $statuslist)) {
                $a = 'user_sent_submissions';
                print_error('invalid_status', 'mod_surveypro', null, $a);
            }
            $whereparams['status'] = $status;
        }

        return $DB->count_records('surveypro_submission', $whereparams);
    }

    /**
     * nomoresubmissions_stopexecution
     *
     * @param none
     * @return
     */
    public function nomoresubmissions_stopexecution() {
        global $OUTPUT;

        $modulepage = $this->get_modulepage();
        if ($modulepage != SURVEYPRO_SUBMISSION_READONLY) {
            if (!$this->submissions_allowed()) {
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
     * manage_thanks_page
     *
     * @param none
     * @return
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
     * surveypro_show_thanks_page
     *
     * @param none
     * @return
     */
    private function show_thanks_page() {
        global $DB, $OUTPUT, $USER;

        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);

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
            $message = get_string('defaulteditingthanksmessage', 'mod_surveypro');
        } else {
            if (!empty($this->surveypro->thankshtml)) {
                $message = file_rewrite_pluginfile_urls($this->surveypro->thankshtml, 'pluginfile.php', $this->context->id, 'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA, $this->surveypro->id);
            } else {
                $message = get_string('defaultcreationthanksmessage', 'mod_surveypro');
            }
        }

        $paramurl = array('id' => $this->cm->id);
        // Just to save a query.
        if (empty($this->surveypro->maxentries)) {
            $alreadysubmitted = -1;
        } else {
            $alreadysubmitted = $DB->count_records('surveypro_submission', array('surveyproid' => $this->surveypro->id, 'userid' => $USER->id));
        }
        $condition = ($alreadysubmitted < $this->surveypro->maxentries);
        $condition = $condition || empty($this->surveypro->maxentries);
        $condition = $condition || $canignoremaxentries;
        if ($condition) { // If the user is allowed to submit one more surveypro.
            $buttonurl = new moodle_url('/mod/surveypro/view_form.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            $onemore = new single_button($buttonurl, get_string('addnewsubmission', 'mod_surveypro'));

            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $gotolist = new single_button($buttonurl, get_string('gotolist', 'mod_surveypro'));

            echo $OUTPUT->confirm($message, $onemore, $gotolist);
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
            echo $OUTPUT->box($OUTPUT->single_button($buttonurl, get_string('gotolist', 'mod_surveypro'), 'get'), 'clearfix mdl-align');
        }
    }

    /**
     * add_browsing_buttons
     *
     * @param none
     * @return
     */
    public function add_readonly_browsing_buttons() {
        global $OUTPUT;

        $params = array();
        $params['s'] = $this->surveypro->id;
        $params['submissionid'] = $this->get_submissionid();
        $params['view'] = SURVEYPRO_READONLYRESPONSE;

        $modulepage = $this->get_modulepage();
        if ($modulepage == SURVEYPRO_SUBMISSION_READONLY) {
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

                if (isset($backwardbutton) && isset($forwardbutton)) {
                    // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
                    // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
                    echo html_writer::tag('div', $OUTPUT->render($backwardbutton).$OUTPUT->render($forwardbutton), array('class' => 'buttons'));
                } else {
                    if (isset($backwardbutton)) {
                        echo html_writer::tag('div', $OUTPUT->render($backwardbutton), array('class' => 'buttons'));
                    }
                    if (isset($forwardbutton)) {
                        echo html_writer::tag('div', $OUTPUT->render($forwardbutton), array('class' => 'buttons'));
                    }
                }
            }
        }
    }

    /**
     * drop_unexpected_values
     *
     * @param none
     * @return
     */
    private function drop_unexpected_values() {
        // Begin of: delete all the bloody values that were NOT supposed to be returned: MDL-34815
        $dirtydata = (array)$this->formdata;
        $indexes = array_keys($dirtydata);

        $disposelist = array();
        $olditemid = 0;
        $regexp = '~'.SURVEYPRO_ITEMPREFIX.'_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        foreach ($indexes as $itemname) {
            if (!preg_match($regexp, $itemname, $matches)) { // If it starts with SURVEYPRO_ITEMPREFIX_.
                continue;
            }
            $type = $matches[1]; // Item type.
            $plugin = $matches[2]; // Item plugin.
            $itemid = $matches[3]; // Item id.

            if ($itemid == $olditemid) {
                continue;
            }

            // Let's start.
            $olditemid = $itemid;

            $childitem = surveypro_get_item($this->cm, $itemid, $type, $plugin);

            if (empty($childitem->get_parentid())) {
                continue;
            }

            // If my parent is already in $disposelist, I have to go to $disposelist FOR SURE.
            if (in_array($childitem->get_parentid(), $disposelist)) {
                $disposelist[] = $childitem->get_itemid();
                continue;
            }

            // Call parentitem.
            $parentitem = surveypro_get_item($this->cm, $childitem->get_parentid());

            $parentinsamepage = false;
            foreach ($indexes as $itemname) {
                if (strpos($itemname, $parentitem->get_itemid())) {
                    $parentinsamepage = true;
                    break;
                }
            }

            if ($parentinsamepage) { // If parent is in this same page.
                // Tell parentitem what child needs in order to be displayed and compare it with what was answered to parentitem ($dirtydata).
                $expectedvalue = $parentitem->userform_child_item_allowed_dynamic($childitem->get_parentvalue(), $dirtydata);
                // Parentitem, knowing itself, compare what is needed and provide an answer.

                if (!$expectedvalue) {
                    $disposelist[] = $childitem->get_itemid();
                }
            }
        } // Check next item.
        // End of: delete all the bloody values that were supposed to NOT be returned: MDL-34815

        // If not expected items are here...
        if (count($disposelist)) {
            $regexp = '~'.SURVEYPRO_ITEMPREFIX.'_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
            foreach ($indexes as $itemname) {
                if (preg_match($regexp, $itemname, $matches)) {
                    // $type = $matches[1]; // Item type.
                    // $plugin = $matches[2]; // Item plugin.
                    $itemid = $matches[3]; // Item id.
                    // $option = $matches[4]; // _text or _noanswer or...
                    if (in_array($itemid, $disposelist)) {
                        unset($this->formdata->$itemname);
                    }
                }
            }
        }
    }

    /**
     * prevent_direct_user_input
     *
     * @param none
     * @return
     */
    private function prevent_direct_user_input() {
        global $DB, $USER, $COURSE;

        $cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);

        if (($this->view == SURVEYPRO_READONLYRESPONSE) || ($this->view == SURVEYPRO_EDITRESPONSE)) {
            if (!$submission = $DB->get_record('surveypro_submission', array('id' => $this->get_submissionid()), '*', IGNORE_MISSING)) {
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
                // Take care! Let's suppose this scenario:
                // $this->surveypro->maxentries = N
                // $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N - 1
                // When I fill the FIRST page of a survey, I get $next = N
                // But when I go to fill the SECOND page of a survey I have one more "in progress" survey
                // that is the one that I created when I saved the FIRST page, so...
                // $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N
                // $next = N + 1
                // I am wrongly stopped here!
                // Because of this:
                if ($this->get_submissionid()) {
                    $next = $this->user_sent_submissions(SURVEYPRO_STATUSALL);
                } else {
                    $next = 1 + $this->user_sent_submissions(SURVEYPRO_STATUSALL);
                }

                $allowed = $cansubmit;
                if ($this->surveypro->timeopen) {
                    $allowed = $allowed && ($this->surveypro->timeopen < $timenow);
                }
                if ($this->surveypro->timeclose) {
                    $allowed = $allowed && ($this->surveypro->timeclose > $timenow);
                }
                if (!$canignoremaxentries) {
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
                    } else { // NOGROUPS || VISIBLEGROUPS
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
                    } else { // NOGROUPS || VISIBLEGROUPS
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
     * duplicate_submission
     *
     * @param $allpages
     * @return
     */
    private function duplicate_submission() {
        global $DB;

        $submissions = $DB->get_record('surveypro_submission', array('id' => $this->get_submissionid()));
        $submissions->timecreated = time();
        $submissions->status = SURVEYPRO_STATUSINPROGRESS;
        unset($submissions->timemodified);
        $submissionid = $DB->insert_record('surveypro_submission', $submissions);

        $surveyprouserdata = $DB->get_recordset('surveypro_answer', array('submissionid' => $this->get_submissionid()));
        foreach ($surveyprouserdata as $userdatum) {
            unset($userdatum->id);
            $userdatum->set_submissionid($submissionid);
            $DB->insert_record('surveypro_answer', $userdatum);
        }
        $surveyprouserdata->close();
        $this->set_submissionid($submissionid);
    }

    /**
     * trigger_event
     *
     * @param $view
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
