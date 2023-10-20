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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_layout;
use mod_surveypro\utility_item;
use mod_surveypro\utility_submission;
use mod_surveypro\formbase;

/**
 * The class managing the form where users are supposed to enter expected data
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissions_form extends formbase {

    /**
     * @var int $mode
     */
    protected $mode;

    /**
     * @var int Status of each answer of the submission
     */
    protected $responsestatus;

    /**
     * @var int Final validation of the submitted response
     */
    protected $userdeservesthanks;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * @var int The status of the whole submission. It can be: SURVEYPRO_STATUSCLOSED,
     */
    public $status;

    /**
     * Setup.
     *
     * @param int $submissionid
     * @param int $formpage
     * @param int $mode
     * @return void
     */
    public function setup($submissionid, $formpage, $mode) {
        global $DB;

        // Assign pages to items.
        $userformpagecount = $DB->get_field('surveypro_item', 'MAX(formpage)', ['surveyproid' => $this->surveypro->id]);
        if (!$userformpagecount) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $userformpagecount = $utilitylayoutman->assign_pages();
        }
        $this->set_userformpagecount($userformpagecount);
        $this->set_user_boundary_formpages();

        $this->set_mode($mode);
        $this->set_submissionid($submissionid);
        $this->set_formpage($formpage);

        $this->prevent_direct_user_input();
        $this->trigger_event();
    }

    // MARK set.

    /**
     * Set mode.
     *
     * @param int $mode
     * @return void
     */
    private function set_mode($mode) {
        $this->mode = $mode;
    }

    // MARK get.

    /**
     * Get mode.
     *
     * @return the content of $mode property
     */
    public function get_mode() {
        return $this->mode;
    }

    /**
     * Get responsestatus.
     *
     * @return the content of $responsestatus property
     */
    public function get_responsestatus() {
        return $this->responsestatus;
    }

    /**
     * Get userdeservesthanks.
     *
     * @return the content of $userdeservesthanks property
     */
    public function get_userdeservesthanks() {
        return $this->userdeservesthanks;
    }

    /**
     * Proccess message method
     *
     * @return String the processed message
     */
    public function get_message() {
        global $CFG, $USER, $COURSE;

        $coveredattr = 'xxxxx';
        if (!empty($this->surveypro->mailcontent)) {
            $fullname = fullname($USER);
            $surveyproname = $this->surveypro->name;
            $url = $CFG->wwwroot.'/mod/surveypro/view.php?s='.$this->surveypro->id.'&section=collectedsubmissions';

            $content = $this->surveypro->mailcontent;
            $originals = ['{FIRSTNAME}', '{LASTNAME}', '{FULLNAME}', '{COURSENAME}', '{SURVEYPRONAME}', '{SURVEYPROURL}'];
            if (empty($this->surveypro->anonymous)) {
                $replacements = [$USER->firstname, $USER->lastname, $fullname, $COURSE->fullname, $surveyproname, $url];
            } else {
                $replacements = [$coveredattr, $coveredattr, $coveredattr, $COURSE->fullname, $surveyproname, $url];
            }

            $content = str_replace($originals, $replacements, $content);
        } else {
            $a = new \stdClass();
            $a->username = empty($this->surveypro->anonymous) ? fullname($USER) : $coveredattr;
            $a->surveyproname = $this->surveypro->name;
            $a->title = get_string('reviewsubmissions', 'mod_surveypro');
            $a->href = $CFG->wwwroot.'/mod/surveypro/view.php?s='.$this->surveypro->id.'&section=collectedsubmissions';

            $content = get_string('newsubmissionbody', 'mod_surveypro', $a);
        }

        return $content;
    }

    // MARK general methods.

    /**
     * Save user submission.
     *
     * @return void
     */
    private function save_surveypro_submission() {
        global $USER, $DB;

        $timenow = time();
        $savebutton = isset($this->formdata->savebutton);
        $saveasnewbutton = isset($this->formdata->saveasnewbutton);
        $nextbutton = isset($this->formdata->nextbutton);
        $pausebutton = isset($this->formdata->pausebutton);
        $prevbutton = isset($this->formdata->prevbutton);
        if ($saveasnewbutton) {
            $this->formdata->submissionid = 0;
        }

        $submission = new \stdClass();

        // I don't save the status here because it is useless
        // the status is defined from the validity of each answer
        // and will be saved after each respose in function save_user_data.

        if (empty($this->formdata->submissionid)) {
            // Add a new record to surveypro_submission.
            $submission->surveyproid = $this->surveypro->id;
            $submission->userid = $USER->id;
            if ($savebutton || $saveasnewbutton || $pausebutton) { // I exclude previous and forward.
                $submission->timecreated = $timenow;
            }

            $submission->id = $DB->insert_record('surveypro_submission', $submission);

            $eventdata = ['context' => $this->context, 'objectid' => $submission->id];
            $eventdata['other'] = ['mode' => SURVEYPRO_NEWRESPONSEMODE];
            $event = \mod_surveypro\event\submission_created::create($eventdata);
            $event->trigger();
        } else {
            // Surveypro_submission already exists.
            // And user submitted it once again.
            $submission->id = $this->formdata->submissionid;
            $params = ['id' => $submission->id];
            $originalrecord = $DB->get_record('surveypro_submission', $params, 'status, timecreated', MUST_EXIST);

            // Define $submission times.
            if ($savebutton || $saveasnewbutton || $pausebutton) { // I exclude previous and forward.
                if ($originalrecord->timecreated) {
                    $submission->timemodified = $timenow;
                } else {
                    $submission->timecreated = $timenow;
                }

                // $DB->update_record must be inside brackets otherwise
                // using "<< previous" or "next >>" to browse the submission
                // I get the error:
                // moodle_database::update_record_raw() no fields found.
                $DB->update_record('surveypro_submission', $submission);
            }

            $eventdata = ['context' => $this->context, 'objectid' => $submission->id];
            $eventdata['other'] = ['mode' => SURVEYPRO_EDITMODE];
            $event = \mod_surveypro\event\submission_modified::create($eventdata);
            $event->trigger();
        }

        if ($savebutton || $saveasnewbutton) {
            // Does user deserve thanks?
            if (isset($originalrecord)) {
                if ($originalrecord->status == SURVEYPRO_STATUSCLOSED) {
                    // No thanks page. User is only editing and was already thanked at original submission time.
                    $this->userdeservesthanks = 0;
                } else {
                     // User deserves thanks.
                    $this->userdeservesthanks = 1;
                }
            } else {
                $this->userdeservesthanks = 1;
            }
        }

        // Before returning, set the submission id.
        $this->set_submissionid($submission->id);
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
     *        [148] => \stdClass Object (
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
     *        [149] => \stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => boolean
     *            [itemid] => 149
     *            [contentperelement] => Array (
     *                [noanswer] => 1
     *            )
     *        )
     *        [150] => \stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => character
     *            [itemid] => 150
     *            [contentperelement] => Array (
     *                [mainelement] => horse
     *            )
     *        )
     *        [151] => \stdClass Object (
     *            [surveyproid] => 1
     *            [submissionid] => 63
     *            [type] => field
     *            [plugin] => fileupload
     *            [itemid] => 151
     *            [contentperelement] => Array (
     *                [filemanager] => 667420320
     *            )
     *        )
     *        [185] => \stdClass Object (
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
     *        [186] => \stdClass Object (
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
        $nextbutton = isset($this->formdata->nextbutton);

        // Drop out undesired answers from the submission.
        if (!$this->surveypro->newpageforchild) {
            // TAKE CARE: It is not enough to drop out unexpected answers.
            // If the response is new, dropping an answer from $this->formdata make you sure the related value will not be saved
            // but if the response is not new you may have the answer already in the database
            // and you need to actually delete the old datum from DB! Ignoring it is not enough.
            $this->drop_undesired_answers();
        }

        if ($savebutton || $saveasnewbutton) {
            $this->drop_unverified_data();
        }

        // For each submission I need to save one 'surveypro_submission' and some 'surveypro_answer'.

        // Begin of: let's start by saving one record in surveypro_submission.
        // In save_surveypro_submission method I also assign $this->submissionid.
        $this->save_surveypro_submission();
        // End of: let's start by saving one record in surveypro_submission.

        // Generate $itemhelperinfo.
        foreach ($this->formdata as $elementname => $content) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                // If among returned fields there is a place holder...
                if ($matches['prefix'] == SURVEYPRO_PLACEHOLDERPREFIX) {
                    $newelement = SURVEYPRO_ITEMPREFIX.'_'.$matches['type'].'_'.$matches['plugin'].'_'.$matches['itemid'];
                    // but not the corresponding field, drop the placeholder and set to null the unexisting item.
                    if (!isset($this->formdata->$newelement)) {
                        $this->formdata->$newelement = null;
                    }
                    unset($this->formdata->$elementname);
                }
            }
        }

        $itemhelperinfo = array();
        foreach ($this->formdata as $elementname => $content) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                // With the introduction of interactive fieldset...
                // those format elements are now equipped with open/close triangle...
                // and they submit their own state.
                // Drop them out.
                $condition = false;
                $condition = $condition || ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX);
                $condition = $condition || ($matches['type'] == SURVEYPRO_TYPEFORMAT);
                if ($condition) {
                    continue; // To next foreach.
                }
            } else {
                // Button or something not relevant.
                if ($elementname == 's') {
                    $surveyproid = $content;
                }
                // This is the black hole where is thrown each useless info like:
                // - formpage
                // - nextbutton
                // and some more.
                continue; // To next foreach.
            }

            $itemid = $matches['itemid'];
            if (!isset($itemhelperinfo[$itemid])) {
                $itemhelperinfo[$itemid] = new \stdClass();
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

        // From now on I am sure I am saving answers to:
        $surveyproid = $surveyproid;
        $submissionid = $this->get_submissionid();
        foreach ($itemhelperinfo as $iteminfo) {
            $where = ['submissionid' => $submissionid, 'itemid' => $iteminfo->itemid];
            if (!$useranswer = $DB->get_record('surveypro_answer', $where)) {
                // Quickly make one new!
                $useranswer = new \stdClass();
                $useranswer->surveyproid = $surveyproid;
                $useranswer->submissionid = $submissionid;
                $useranswer->itemid = $iteminfo->itemid;
                $useranswer->content = SURVEYPRO_DUMMYCONTENT;
                // $useranswer->contentformat = null; // Useless, as null is the default.

                $useranswer->id = $DB->insert_record('surveypro_answer', $useranswer);
            }
            $useranswer->timecreated = time();
            $useranswer->verified = ($prevbutton || $pausebutton) ? 0 : 1;

            $item = surveypro_get_item($this->cm, $this->surveypro, $iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            // Now I ask to each item the answer for the db starting from what the user provided ($iteminfo->contentperelement).
            $item->userform_get_user_answer($iteminfo->contentperelement, $useranswer, false);

            if ($useranswer->content === SURVEYPRO_DUMMYCONTENT) {
                throw new \moodle_exception('wrong_userdatarec_found', 'mod_surveypro', null, SURVEYPRO_DUMMYCONTENT);
            } else {
                $DB->update_record('surveypro_answer', $useranswer);
            }
        }

        // Before closing the save session I need two more validations.

        // FIRST SCENARIO.
        // Let's suppose the following scenario.
        // 1) User is filling a surveypro divided into 4 pages.
        // 2) User fills all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 3 (the page the user still does not see) of the surveypro there is mandatory field.
        // 5) Because of 3) user jumps to page 4 and make the final submit.
        // This check is needed to verify that EACH mandatory surveypro field was actually saved.

        // SECOND SCENARIO.
        // Let's suppose the following scenario.
        // 1) User is filling a surveypro divided into 3 pages.
        // 2) User fills all the fields of first page and moves to page 2.
        // 3) User reads the url and understands that the formapge is passed in GET (visible in the url).
        // 4) At page 2 of the surveypro there is a mandatory field.
        // 5) User return back to page 1 without filling the mandatory field.
        // 6) Page 2 is saved WITHOUT the mandatory field because when the user moves back, the form VALIDATION is not executed.
        // 7) Because of 3) user jumps to page 3 and make the final submit.
        // This check is needed to verify that EACH surveypro field was actually saved as VERIFIED.

        // I have to ALWAYS check for the validity of all responses
        // and not ONLY when ($savebutton || $saveasnewbutton) are presssed because...
        // 1) a surveypro spanning multiple pages was correctly submitted
        // 2) I edit the closed submission
        // 3) I go to page 2 and I cancel a mandatory answer
        // 4) I return back to page 1
        // 5) Page 2 is saved WITHOUT the mandatory field because when the user moves back, the form VALIDATION is not executed.
        // 6) I use the breadcrumb to go somewhere else
        // 7) the submission HAS TO BE saved as IN PROGRESS and not as CLOSED
        // even if ($savebutton || $saveasnewbutton) where never pressed

        // Be optimistic. Let's start by assuming user was correct.
        $this->responsestatus = SURVEYPRO_VALIDRESPONSE;
        // Let's start with the lightest check (lightest in terms of query).
        $this->check_all_was_verified();
        if ($this->responsestatus == SURVEYPRO_VALIDRESPONSE) {
            // Each provided answer was validated but, maybe, some answer was not provided.
            $this->check_mandatories_are_in();
        }

        // If all the answers are still valid.
        if ($this->responsestatus == SURVEYPRO_VALIDRESPONSE) {
            // -> $prevbutton: I am not saving with $verified = true so $this->responsestatus != SURVEYPRO_VALIDRESPONSE
            // and this case should never be verified.
            // -> $pausebutton: Yes, all is fine but I want to pause the submission because I am not sure
            // so the submission needs to be saved as "in progress".
            // -> $nextbutton: Even if the overall response status is valid I can not close the submission
            // because I can close it using $savebutton or $saveasnewbutton ONLY.
            if ($prevbutton || $pausebutton || $nextbutton) {
                $this->status = SURVEYPRO_STATUSINPROGRESS;
            } else {
                $this->status = SURVEYPRO_STATUSCLOSED;
            }
        } else {
            // User jumped pages using direct input (or something more dangerous).
            // Set this submission as SURVEYPRO_STATUSINPROGRESS.
            $this->status = SURVEYPRO_STATUSINPROGRESS;
        }
        $conditions = ['id' => $this->get_submissionid()];
        $DB->set_field('surveypro_submission', 'status', $this->status, $conditions);

        // Update completion state.
        $completion = new \completion_info($COURSE);
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

        $canaccessreserveditems = has_capability('mod/surveypro:accessreserveditems', $this->context);

        // Get the list of used plugin.
        $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
        $pluginlist = $utilitysubmissionman->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        // Begin of: get the list of all mandatory fields.
        $requireditems = array();
        foreach ($pluginlist as $plugin) {
            $classname = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'\item';
            $canbemandatory = $classname::item_uses_mandatory_dbfield();
            if ($canbemandatory) {
                $sql = 'SELECT i.id, i.parentid, i.parentvalue, i.reserved
                        FROM {surveypro_item} i
                            JOIN {surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin.'} p ON p.itemid = i.id
                        WHERE i.surveyproid = :surveyproid
                            AND i.hidden = :hidden
                            AND p.required > :required
                        ORDER BY p.itemid';

                $whereparams = ['surveyproid' => $this->surveypro->id, 'hidden' => 0, 'required' => 0];
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
        $whereparams = ['submissionid' => $this->get_submissionid()];
        $providedanswers = $DB->get_records_menu('surveypro_answer', $whereparams, 'itemid', 'itemid, 1');

        foreach ($requireditems as $itemseed) {
            if (!isset($providedanswers[$itemseed->id])) { // Answer was not provided for the required item.
                if (empty($itemseed->parentid)) { // There is no parent item!!! Answer was jumped.
                    $this->responsestatus = SURVEYPRO_MISSINGMANDATORY;
                    break;
                } else {
                    $parentitem = surveypro_get_item($this->cm, $this->surveypro, $itemseed->parentid);
                    if ($parentitem->userform_is_child_allowed_static($this->get_submissionid(), $itemseed)) {
                        // Parent is here but it allows this item as child in this submission. Answer was jumped.
                        // Take care: this check is valid for chains of parent-child relations too.
                        // If the parent item was not allowed by its parent,
                        // it was not answered and userform_is_child_allowed_static returns false.
                        $this->responsestatus = SURVEYPRO_MISSINGMANDATORY;
                    }
                }
            }
        }
    }

    /**
     * Check that each answer of the passed submission was actually verified at submission time.
     * If at least one answer was not veritied, the responsestatus is SURVEYPRO_MISSINGVALIDATION.
     *
     * @return void
     */
    private function check_all_was_verified() {
        global $DB;

        $conditions = ['submissionid' => $this->get_submissionid(), 'verified' => 0];
        if ($DB->get_record('surveypro_answer', $conditions, 'id', IGNORE_MULTIPLE)) {
            $this->responsestatus = SURVEYPRO_MISSINGVALIDATION;
        }
    }

    /**
     * Drop old answers saved with 'verified' = 0.
     * They comes from data written in pages not validated (because user returned to previous page)
     * They were converted to answers with 'verified' = 1 whaen the users arrived to the same page and moved forward
     * and if they are still there... they must be deleted now.
     *
     * Example
     * page 1: What is your gender?
     * page 2: If the answer to question in page 1 was M then question2
     * page 2: If the answer to question in page 1 was F then question3
     *
     * User starts.
     * Page 1: What is your gender? 'M' and 'Next page >>'
     * Page 2: Question2: 'I am a male' and '<< Previous page'
     * 'I am a male' is saved as unverified
     * Page 1: What is your gender? 'F' and 'Next page >>'
     * Page 2: Question3: 'I am a female' and '<< Previous page'
     * 'I am a female' is saved as unverified
     * Page 1: What is your gender? 'M' and 'Next page >>'
     * Page 2: Question2: 'I confirm I am a male' and 'Submit'
     *
     * At submit time I have:
     * Question1: 'M' saved as verified
     * Question2: 'I am a female' saved as unverified
     * Question3: 'I confirm I am a male' saved as verified
     *
     * Submit and 'I am a female' is saved as verified
     * Now I have to delete the unverified answer
     *
     * @return void
     */
    public function drop_unverified_data() {
        global $DB;

        $whereparams = ['submissionid' => $this->formdata->submissionid, 'verified' => 0];
        $where = 'submissionid = :submissionid AND verified = :verified';
        $DB->delete_records_select('surveypro_answer', $where, $whereparams);
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
        if (empty($this->surveypro->mailroles) && empty($this->surveypro->mailextraaddresses)) {
            return;
        }

        // Course context used locally to get groups.
        $context = \context_course::instance($COURSE->id);
        $attributes = ['u.id', 'username', 'email', 'mailformat'];
        $attributes = array_merge($attributes, \core_user\fields::get_name_fields(true));
        $fields = implode(', u.', $attributes);

        $recipients = array();
        if ($this->surveypro->mailroles) {
            $rolesid = explode(',', $this->surveypro->mailroles);
            // For each roles (as selected in mod_form.php) get the list of users.
            foreach ($rolesid as $roleid) {
                $recipients += get_role_users($roleid, $context, false, $fields);
            }
            $firstlist = array();
            foreach ($recipients as $recipient) {
                $firstlist[] = $recipient->email;
            }
        }

        if (!empty($this->surveypro->mailextraaddresses)) {
            $utilityitemman = new utility_item($this->cm, $this->surveypro);
            $morerecipients = $utilityitemman->multilinetext_to_array($this->surveypro->mailextraaddresses);

            $recipient = new \stdClass();
            foreach ($attributes as $attribute) {
                $recipient->{$attribute} = '';
            }

            // Now $recipient is onboard with the correct list of fields.
            $recipient->id = -1;
            $recipient->mailformat = 1;
            foreach ($morerecipients as $moreemail) {
                // Do not create duplicates.
                if (in_array($moreemail, $firstlist)) {
                    continue;
                }
                $recipient->firstname = $moreemail;
                $recipient->lastname = $moreemail;
                $recipient->username = $moreemail;
                $recipient->email = $moreemail;
                $recipients[] = clone($recipient);
            }
        }

        // $noreplyuser = \core_user::get_noreply_user();
        $supportuser = \core_user::get_support_user();

        $body = $this->get_message();
        $htmlbody = text_to_html($body, false, false, true);

        $subject = get_string('newsubmissionsubject', 'mod_surveypro');

        foreach ($recipients as $recipient) {
            email_to_user($recipient, $supportuser, $subject, $body, $htmlbody);
        }
    }

    /**
     * Stop the page load with a warning because no submission is available.
     *
     * @return void
     */
    public function nomoresubmissions_stopexecution() {
        global $OUTPUT;

        if ($this->mode != SURVEYPRO_READONLYMODE) {
            // If $this->formdata is available, this means that the form was already displayed and submitted.
            // So it is not the time to verify the user is allowed to submit one more surveypro.
            if ($this->formdata) {
                return;
            }

            // If submissionid is already defined I am not going to create one more new submission so the problem does not exist.
            if ($this->get_submissionid()) {
                return;
            }

            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            if (!$utilitylayoutman->can_submit_more()) {
                $message = get_string('nomoresubmissionsallowed', 'mod_surveypro', $this->surveypro->maxentries);
                echo $OUTPUT->notification($message, 'notifyproblem');

                $whereparams = ['s' => $this->cm->instance, 'section' => 'submissionslist'];
                $continueurl = new \moodle_url('/mod/surveypro/view.php', $whereparams);

                echo $OUTPUT->continue_button($continueurl);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    /**
     * Add browsing buttons to the read only userform that does not display them by design.
     *
     * @return void
     */
    public function add_readonly_browsing_buttons() {
        global $OUTPUT;

        if ($this->mode == SURVEYPRO_READONLYMODE) {
            $params = array();
            $params['s'] = $this->surveypro->id;
            $params['submissionid'] = $this->get_submissionid();
            $params['mode'] = SURVEYPRO_READONLYMODE;
            $params['section'] = 'submissionform';

            $userformpagecount = $this->get_userformpagecount();
            if ($userformpagecount > 1) {
                $formpage = $this->get_formpage();

                if ($this->formpage > $this->userfirstpage) {
                    $this->next_not_empty_page(false); // False means direction = left.
                    $params['formpage'] = $this->get_nextpage();
                    $url = new \moodle_url('/mod/surveypro/view.php', $params);
                    $backwardbutton = new \single_button($url, get_string('previousformpage', 'mod_surveypro'), 'get');
                }

                if ($this->formpage < $this->userlastpage) {
                    $this->next_not_empty_page(true); // True means direction = right.
                    $params['formpage'] = $this->get_nextpage();
                    $url = new \moodle_url('/mod/surveypro/view.php', $params);
                    $forwardbutton = new \single_button($url, get_string('nextformpage', 'mod_surveypro'), 'get');
                }

                $params = ['class' => 'buttons'];
                $secondleveldiv = \html_writer::tag('div', '', ['class' => 'd-flex']);
                $firstleveldiv = \html_writer::tag('div', $secondleveldiv, ['class' => 'col-md-4']);
                if (isset($backwardbutton) && isset($forwardbutton)) {
                    // This code comes from "public function confirm(" around line 1711 in outputrenderers.php.
                    // It is not wrong. The misalign comes from bootstrapbase theme and is present in clean theme too.
                    $content = $firstleveldiv.$OUTPUT->render($backwardbutton).$OUTPUT->render($forwardbutton);
                    echo \html_writer::tag('div', $content, ['class' => 'row']);
                } else {
                    if (isset($backwardbutton)) {
                        $content = $firstleveldiv.$OUTPUT->render($backwardbutton);
                        echo \html_writer::tag('div', $content, ['class' => 'row']);
                    }
                    if (isset($forwardbutton)) {
                        $content = $firstleveldiv.$OUTPUT->render($forwardbutton);
                        echo \html_writer::tag('div', $content, ['class' => 'row']);
                    }
                }
            }
        }
    }

    /**
     * Drop all the answers returned by disabled items.
     *
     * If a child item is disabled by the parent in its same page,
     * the child answer (whether set) is undesired and must be deleted.
     *
     * Let's suppose parent and child element in the same page.
     * I provide the right parent answer so that the child becomes enabled.
     * Now I enter some input in the child element.
     * Then I return to parent element and change its answer.
     * Child is now disabled BUT equipped with an answer.
     * This is the answer I want to drop out.
     *
     * @return void
     */
    private function drop_undesired_answers() {
        // Begin of: delete all the values returned by disabled items (that were NOT supposed to be returned: MDL-34815).
        $dirtydata = (array)$this->formdata;
        $elementnames = array_keys($dirtydata);

        $disposelist = array();
        $olditemid = 0;
        foreach ($elementnames as $elementname) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                // With the introduction of interactive fieldset...
                // those format elements are now equipped with open/close triangle...
                // and they submit their own state.
                // Drop them out.
                $condition = false;
                $condition = $condition || ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX);
                $condition = $condition || ($matches['type'] == SURVEYPRO_TYPEFORMAT);
                if ($condition) {
                    continue; // To next foreach.
                }
            } else {
                continue;
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

            $parentpage = $parentitem->get_formpage();
            $childpage = $childitem->get_formpage();
            if ($parentpage == $childpage) { // If parent and child share the same page.
                // Pass to parentitem what the child needs to be displayed ($childitem->get_parentvalue())
                // and compare it with what was answered to parentitem ($dirtydata).
                if (!$parentitem->userform_is_child_allowed_dynamic($childitem->get_parentvalue(), $dirtydata)) {
                    // Parentitem, knowing itself, compares the anwer it received with child needs and provides an answer.
                    $disposelist[] = $childitem->get_itemid();
                }
            }
        } // Check next item.
        // End of: delete all the values returned by disabled items (that were NOT supposed to be returned: MDL-34815).

        // If not expected items are here...
        if (count($disposelist)) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            foreach ($elementnames as $elementname) {
                if ($matches = utility_item::get_item_parts($elementname)) {
                    // With the introduction of interactive fieldset...
                    // those format elements are now equipped with open/close triangle...
                    // and they submit their own state.
                    // Drop them out.
                    $condition = false;
                    $condition = $condition || ($matches['prefix'] == SURVEYPRO_DONTSAVEMEPREFIX);
                    $condition = $condition || ($matches['type'] == SURVEYPRO_TYPEFORMAT);
                    if ($condition) {
                        continue; // To next foreach.
                    }
                    $itemid = $matches['itemid'];
                    if (in_array($itemid, $disposelist)) {
                        unset($this->formdata->$elementname);
                        // If this datum was previously saved (when the item was not disabled)
                        // now I have to delete from database.
                        $utilitylayoutman->delete_answers(['submissionid' => $this->formdata->submissionid, 'itemid' => $itemid]);
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

        $cansubmit = has_capability('mod/surveypro:submit', $this->context);
        $canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context);
        $canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context);
        $caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context);
        $caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);

        $submissionid = $this->get_submissionid();
        if ($submissionid) {
            $where = ['id' => $submissionid];
            if (!$submission = $DB->get_record('surveypro_submission', $where, 'userid, status', IGNORE_MISSING)) {
                throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
            }
            $ownerid = $submission->userid;
            $ismine = ($ownerid == $USER->id);

            if ($canaccessallgroups) {
                $mysamegroup = true;
            } else {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode) { // Activity is divided into groups.
                    // Does the user belong to any group?
                    $mygroups = groups_get_all_groups($COURSE->id, $USER->id);
                    if (count($mygroups)) {
                        $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
                        $mygroupmates = $utilitysubmissionman->get_groupmates($this->cm);
                        $mysamegroup = in_array($ownerid, $mygroupmates);
                    } else {
                        // My group is the world so, for sure you are in my group.
                        $mysamegroup = true;
                    }
                } else {
                    $mysamegroup = true;
                }
            }
        } else {
            // The submission does not exist.
            // As a consequence, its owner is not defined.
            // Is the owner in my same group? No, of course!
            $ismine = false;
            $mysamegroup = false;
        }

        $debug = false;
        if ($debug) {
            switch ($this->mode) {
                case SURVEYPRO_NEWRESPONSEMODE:
                    echo '$this->mode = SURVEYPRO_NEWRESPONSEMODE<br>';
                    break;
                case SURVEYPRO_EDITMODE:
                    echo '$this->mode = SURVEYPRO_EDITMODE<br>';
                    break;
                case SURVEYPRO_READONLYMODE:
                    echo '$this->mode = SURVEYPRO_READONLYMODE<br>';
                    break;
                default:
                    echo '$this->mode = '.$this->mode;
            }

            if ($ismine) {
                echo '$ismine = true<br>';
            } else {
                echo '$ismine = false<br>';
            }
            if ($mysamegroup) {
                echo '$mysamegroup = true<br>';
            } else {
                echo '$mysamegroup = false<br>';
            }
            echo '$mysamegroup =';
            // print_object($mysamegroup); // <-- This is better than var_dump but codechecker doesn't like it.
        }

        $allowed = false;
        switch ($this->mode) {
            case SURVEYPRO_NEWRESPONSEMODE:

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
                    // $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSALL, $USER->id) = N - 1.
                    // When I fill the FIRST page of a survey, I get $next = N
                    // but when I go to fill the SECOND page of a survey I have one more "in progress" survey
                    // that is the one that I created when I saved the FIRST page, so...
                    // when $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N, I get
                    // $next = N + 1
                    // and I am wrongly stopped here!
                    // Because of this, I increase $next only if submissionid == 0.
                    $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
                    $next = $utilitylayoutman->has_submissions(true, SURVEYPRO_STATUSALL, $USER->id);
                    if (!$this->get_submissionid()) {
                        $next += 1;
                    }

                    $allowed = $allowed && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
                }
                break;
            case SURVEYPRO_EDITMODE:
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    // If $submission->status == SURVEYPRO_STATUSINPROGRESS it is a resume acction.
                    if ($ismine) { // Owner is me
                        $allowed = true;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $allowed = $canseeotherssubmissions;
                        }
                    }
                } else {
                    // If $submission->status == SURVEYPRO_STATUSCLOSED it is an edit acction.
                    if ($ismine) { // Owner is me
                        $allowed = $caneditownsubmissions;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $allowed = $caneditotherssubmissions;
                        }
                    }
                }
                break;
            case SURVEYPRO_READONLYMODE:
                // Whether SURVEYPRO_STATUSINPROGRESS, always deny.
                if ($submission->status == SURVEYPRO_STATUSINPROGRESS) {
                    $allowed = false;
                } else {
                    if ($ismine) { // Owner is me
                        $allowed = true;
                    } else {
                        if ($mysamegroup) { // Owner is from a group of mine.
                            $allowed = $canseeotherssubmissions;
                        }
                    }
                }
                break;
            default:
                $allowed = false;
        }
        if (!$allowed) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Trigger the submission_viewed event.
     *
     * @return void
     */
    private function trigger_event() {
        switch ($this->mode) {
            case SURVEYPRO_NOMODE:
            case SURVEYPRO_EDITMODE: // Item_modified will be, eventually, logged.
            case SURVEYPRO_NEWRESPONSEMODE:  // Item_created will be, eventually, logged.
                break;
            case SURVEYPRO_READONLYMODE:
                // Event: submission_viewed.
                $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
                $eventdata['other'] = ['mode' => SURVEYPRO_READONLYMODE];
                $event = \mod_surveypro\event\submission_viewed::create($eventdata);
                $event->trigger();
                break;
            default:
                $message = 'Unexpected $this->mode = '.$this->mode;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }
}
