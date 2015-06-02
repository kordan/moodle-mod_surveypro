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
class mod_surveypro_userformmanager {
    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $submissionid: the ID of the saved surbey_submission
     */
    public $submissionid = 0;

    /**
     * $hasinputitems
     */
    public $hasinputitems = false;

    /**
     * $formpage: the form page as recalculated according to the first non empty page
     * do not confuse this properties with $this->formdata->formpage
     */
    public $formpage = null;

    /**
     * $maxassignedpage
     */
    public $maxassignedpage = 0;

    /**
     * $firstpageright
     */
    public $firstpageright = 0;

    /**
     * $firstpageleft
     */
    public $firstpageleft = 0;

    /**
     * $view
     */
    public $view = null;

    /**
     * $moduletab: The tab of the module where the page will be shown
     */
    public $moduletab = '';

    /**
     * $modulepage: this is the page of the module. Nothing to share with $formpage
     */
    public $modulepage = '';

    /**
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /**
     * $canseeotherssubmissions
     */
    public $canseeotherssubmissions = false;

    /**
     * $caneditownsubmissions
     */
    public $caneditownsubmissions = false;

    /**
     * $caneditotherssubmissions
     */
    public $caneditotherssubmissions = false;

    /**
     * $cansubmit
     */
    public $cansubmit = false;

    /**
     * $canignoremaxentries
     */
    public $canignoremaxentries = false;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        global $DB;

        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
        $this->hasinputitems = $this->has_input_items();

        // $this->canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);
        $this->cansubmit = has_capability('mod/surveypro:submit', $this->context, null, true);
        $this->canignoremaxentries = has_capability('mod/surveypro:ignoremaxentries', $this->context, null, true);

        $this->canseeotherssubmissions = has_capability('mod/surveypro:seeotherssubmissions', $this->context, null, true);

        $this->caneditownsubmissions = has_capability('mod/surveypro:editownsubmissions', $this->context, null, true);
        $this->caneditotherssubmissions = has_capability('mod/surveypro:editotherssubmissions', $this->context, null, true);

        // assign pages to items
        if (!$this->maxassignedpage = $DB->get_field('surveypro_item', 'MAX(formpage)', array('surveyproid' => $surveypro->id))) {
            $this->assign_pages();
        }
    }

    // MARK set

    /**
     * set_submissionid
     *
     * @param $submissionid
     * @return none
     */
    public function set_submissionid($submissionid) {
        $this->submissionid = $submissionid;
    }

    /**
     * set_view
     *
     * @param $view
     * @return none
     */
    public function set_view($view) {
        $this->view = $view;
        $this->set_tabs_params();
    }

    /**
     * set_formpage
     *
     * @param $formpage
     * @return none
     */
    public function set_formpage($formpage) {
        if ($this->view === null) {
            debugging('Please call set_view method of the class mod_surveypro_userformmanager before calling set_formpage', DEBUG_DEVELOPER);
        }

        // calculare $this->firstpageright
        if ($this->canaccessadvanceditems) {
            $this->firstpageright = 1;
        } else {
            $this->next_not_empty_page(true, 0, $this->view); // this assign $this->firstformpage
        }

        if ($formpage == 0) { // you are viewing the surveypro for the first time
            $this->formpage = $this->firstpageright;
        } else {
            $this->formpage = $formpage;
        }
    }

    /**
     * next_not_empty_page
     *
     * @param $forward
     * @param $startingpage
     * @return
     */
    public function next_not_empty_page($forward, $startingpage, $modulepage) {
        // depending on user provided answer, in the previous or next page there may be no items to display
        // get the first page WITH items
        //
        // this method writes:
        // if $forward == true:
        //     the page number of the first non empty page (according to user answers) in $this->firstpageright
        //     returns $nextpage or SURVEYPRO_RIGHT_OVERFLOW if no more empty pages are found on the right
        // if $forward == false:
        //     the page number of the bigger non empty page lower than $startingpage (according to user answers) in $this->firstpageleft
        //     returns $nextpage or SURVEYPRO_LEFT_OVERFLOW if no more empty pages are found on the left

        if ($modulepage == SURVEYPRO_ITEMS_PREVIEW) { // I do not care item parent-child relations, I am in "preview mode"
            if ($forward) {
                $this->firstpageright = ++$startingpage;
            } else {
                $this->firstpageleft = --$startingpage;
            }
            return;
        }

        $condition = ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) && ($forward);
        $condition = $condition || (($startingpage == SURVEYPRO_LEFT_OVERFLOW) && (!$forward));
        if ($condition) {
            $a = new stdClass();
            if ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) {
                $a->startingpage = 'SURVEYPRO_RIGHT_OVERFLOW';
            } else {
                $a->startingpage = 'SURVEYPRO_LEFT_OVERFLOW';
            }
            $a->methodname = 'next_not_empty_page';
            print_error('wrong_direction_found', 'surveypro', null, $a);
        }

        if ($startingpage == SURVEYPRO_RIGHT_OVERFLOW) {
            $startingpage = $this->maxassignedpage + 1;
        }
        if ($startingpage == SURVEYPRO_LEFT_OVERFLOW) {
            $startingpage = 0;
        }

        if ($forward) {
            $nextpage = ++$startingpage;
            $overflowpage = $this->maxassignedpage + 1; // maxpage = $maxformpage, but I have to add      1 because of ($i != $overflowpage)
        } else {
            $nextpage = --$startingpage;
            $overflowpage = 0;                          // minpage = 1,            but I have to subtract 1 because of ($i != $overflowpage)
        }

        do {
            if ($this->page_has_items($nextpage)) {
                break;
            }
            $nextpage = ($forward) ? ++$nextpage : --$nextpage;
        } while ($nextpage != $overflowpage);

        if ($forward) {
            $this->firstpageright = ($nextpage == $overflowpage) ? SURVEYPRO_RIGHT_OVERFLOW : $nextpage;
        } else {
            $this->firstpageleft = ($nextpage == $overflowpage) ? SURVEYPRO_LEFT_OVERFLOW : $nextpage;
        }
    }

    /**
     * page_has_items
     *
     * @param $formpage
     * @return
     */
    public function page_has_items($formpage) {
        global $CFG, $DB;

        // $canaccessadvanceditems, $searchform=false, $type=SURVEYPRO_TYPEFIELD, $formpage=$formpage
        list($sql, $whereparams) = surveypro_fetch_items_seeds($this->surveypro->id, $this->canaccessadvanceditems, false, false, $formpage);
        $itemseeds = $DB->get_records_sql($sql, $whereparams);

        // start looking ONLY at empty($item->parentid) because it doesn't involve extra queries
        foreach ($itemseeds as $itemseed) {
            if (empty($itemseed->parentid)) {
                // if at least one item has an empty parentid, I finished
                return true;
            }
        }

        foreach ($itemseeds as $itemseed) {
            // make sure that the visibility condition is verified
            if ($itemseed->type == SURVEYPRO_TYPEFORMAT) {
                continue;
            }

            $parentplugin = $DB->get_field('surveypro_item', 'plugin', array('id' => $itemseed->parentid));
            require_once($CFG->dirroot.'/mod/surveypro/field/'.$parentplugin.'/classes/plugin.class.php');

            $itemclass = 'mod_surveypro_field_'.$parentplugin;
            $parentitem = new $itemclass($this->cm, $itemseed->parentid, false);

            if ($parentitem->userform_child_item_allowed_static($this->submissionid, $itemseed)) {
                // if (userform_child_item_allowed_static($this->submissionid, $itemseed)) {
                return true;
            }
        }

        // if you're not able to get out in the two previous occasions ... declares defeat
        return false;
    }

    /**
     * set_tabs_params
     *
     * @param none
     * @return
     */
    public function set_tabs_params() {
        switch ($this->view) {
            case SURVEYPRO_NOVIEW:
                $this->moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEYPRO_SUBMISSION_CPANEL; // needed by tabs.php
                break;
            case SURVEYPRO_NEWRESPONSE:
                $this->moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEYPRO_SUBMISSION_INSERT; // needed by tabs.php
                break;
            case SURVEYPRO_PREVIEWSURVEYFORM:
                $this->moduletab = SURVEYPRO_TABITEMS; // needed by tabs.php
                $this->modulepage = SURVEYPRO_ITEMS_PREVIEW; // needed by tabs.php
                break;
            case SURVEYPRO_EDITRESPONSE:
                $this->moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEYPRO_SUBMISSION_EDIT; // needed by tabs.php
                break;
            case SURVEYPRO_READONLYRESPONSE:
                $this->moduletab = SURVEYPRO_TABSUBMISSIONS; // needed by tabs.php
                $this->modulepage = SURVEYPRO_SUBMISSION_READONLY; // needed by tabs.php
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->view = '.$this->view, DEBUG_DEVELOPER);
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
            $PAGE->requires->css('/mod/surveypro/userstyle.php?id='.$this->surveypro->id.'&amp;cmid='.$this->cm->id); // not overridable via themes!
        }
    }

    /**
     * assign_pages
     *
     * @param none
     * @return
     */
    public function assign_pages() {
        global $DB;

        $where = array();
        $where['surveyproid'] = $this->surveypro->id;
        $where['hidden'] = 0;

        $lastwaspagebreak = true; // whether 2 page breaks in line, the second one is ignored
        $pagenumber = 1;
        $items = $DB->get_recordset('surveypro_item', $where, 'sortindex', 'id, type, plugin, parentid, formpage, sortindex');
        if ($items) {
            foreach ($items as $item) {
                if ($item->plugin == 'pagebreak') { // it is a page break
                    if (!$lastwaspagebreak) {
                        $pagenumber++;
                    }
                    $lastwaspagebreak = true;
                    continue;
                } else {
                    $lastwaspagebreak = false;
                }
                if ($this->surveypro->newpageforchild) {
                    $parentitemid = $item->parentid;
                    if (!empty($parentitemid)) {
                        $parentpage = $DB->get_field('surveypro_item', 'formpage', array('id' => $item->parentid), MUST_EXIST);
                        if ($parentpage == $pagenumber) {
                            $pagenumber++;
                        }
                    }
                }
                // echo 'Assigning pages: $DB->set_field(\'surveypro_item\', \'formpage\', '.$pagenumber.', array(\'id\' => '.$item->id.'));<br />';
                $DB->set_field('surveypro_item', 'formpage', $pagenumber, array('id' => $item->id));
            }
            $items->close();
            $this->maxassignedpage = $pagenumber;
        }
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

        // at each submission I need to save one 'surveypro_submission' and some 'surveypro_answer'

        // -----------------------------

        // let's start by saving one record in surveypro_submission
        // in save_surveypro_submission method I also assign $this->submissionid and $this->status
        $this->save_surveypro_submission();
        // end of: let's start by saving one record in surveypro_submission

        // -----------------------------

        // save now all the answers provided by the user
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_PLACEHOLDERPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';

        $itemhelperinfo = array();
        foreach ($this->formdata as $itemname => $content) {
            if (!preg_match($regexp, $itemname, $matches)) {
                // button or something not relevant
                switch ($itemname) {
                    case 's': // <-- s is the surveypro id
                        $surveyproid = $content;
                        break;
                    default:
                        // this is the black hole where is thrown each useless info like:
                        // - formpage
                        // - nextbutton
                        // and some more
                }
                continue; // to next foreach
            }

            // var_dump($matches);
            // $matches = array{
            //   0 => string 'surveypro_field_radiobutton_1452' (length=27)
            //   1 => string 'surveypro' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'radiobutton' (length=11)
            //   4 => string '1452' (length=4)
            // }
            // $matches = array{
            //   0 => string 'surveypro_field_radiobutton_1452_check' (length=33)
            //   1 => string 'surveypro' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'radiobutton' (length=11)
            //   4 => string '1452' (length=4)
            //   5 => string 'check' (length=5)
            // }
            // $matches = array{}
            //   0 => string 'surveypro_field_checkbox_1452_73' (length=30)
            //   1 => string 'surveypro' (length=6)
            //   2 => string 'field' (length=5)
            //   3 => string 'checkbox' (length=8)
            //   4 => string '1452' (length=4)
            //   5 => string '73' (length=2)
            // $matches = array{}
            //   0 => string 'placeholder_field_multiselect_199_placeholder' (length=45)
            //   1 => string 'placeholder' (length=11)
            //   2 => string 'field' (length=5)
            //   3 => string 'multiselect' (length=11)
            //   4 => string '199' (length=3)
            //   5 => string 'placeholder' (length=11)

            $itemid = $matches[4]; // itemid of the mform element (or of the group of mform elements referring to the same item)
            if (!isset($itemhelperinfo[$itemid])) {
                $itemhelperinfo[$itemid] = new stdClass();
                $itemhelperinfo[$itemid]->surveyproid = $surveyproid;
                $itemhelperinfo[$itemid]->submissionid = $this->submissionid;
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

        // if (isset($itemhelperinfo)) {
        //     echo '$itemhelperinfo = <br />';
        //     print_object($itemhelperinfo);
        // } else {
        //     echo 'Nothing has been found<br />';
        // }
        // die;

        // once $itemhelperinfo is onboard...
        //    I update/create the corresponding record
        //    asking to each item class to manage its informations

        foreach ($itemhelperinfo as $iteminfo) {
            if (!$userdatarec = $DB->get_record('surveypro_answer', array('submissionid' => $iteminfo->submissionid, 'itemid' => $iteminfo->itemid))) {
                // Quickly make one new!
                $userdatarec = new stdClass();
                $userdatarec->surveyproid = $iteminfo->surveyproid;
                $userdatarec->submissionid = $iteminfo->submissionid;
                $userdatarec->itemid = $iteminfo->itemid;
                $userdatarec->content = '__my_dummy_content@@';
                $userdatarec->contentformat = null;

                $id = $DB->insert_record('surveypro_answer', $userdatarec);
                $userdatarec = $DB->get_record('surveypro_answer', array('id' => $id));
            }
            $userdatarec->timecreated = time();

            $item = surveypro_get_item($iteminfo->itemid, $iteminfo->type, $iteminfo->plugin);

            // In this method I update $userdatarec->content
            // I do not really save to database
            $item->userform_save_preprocessing($iteminfo->contentperelement, $userdatarec, false);

            if ($userdatarec->content != '__my_dummy_content@@') {
                $DB->update_record('surveypro_answer', $userdatarec);
            } else {
                $a = '__my_dummy_content@@';
                print_error('wrong_userdatarec_found', 'surveypro', null, $a);
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
     * drop_jumped_saved_data
     *
     * @param none
     * @return
     */
    public function drop_jumped_saved_data() {
        global $DB;

        if ($this->firstpageright == ($this->formpage + 1)) {
            return;
        }

        $pages = range($this->formpage + 1, $this->firstpageright - 1);
        $where = 'surveyproid = :surveyproid
                AND formpage IN ('.implode(',', $pages).')';
        $itemlistid = $DB->get_records_select('surveypro_item', $where, array('surveyproid' => $this->surveypro->id), 'id', 'id');
        $itemlistid = array_keys($itemlistid);

        $where = 'submissionid = :submissionid
            AND itemid IN ('.implode(',', $itemlistid).')';
        $DB->delete_records_select('surveypro_answer', $where, array('submissionid' => $this->formdata->submissionid));
    }

    /**
     * save_surveypro_submission
     *
     * @param none
     * @return surveypro_submission record
     */
    public function save_surveypro_submission() {
        global $USER, $DB;

        if (!$this->surveypro->newpageforchild) {
            $this->drop_unexpected_values();
        }

        $timenow         = time();
        $savebutton      = (isset($this->formdata->savebutton) && ($this->formdata->savebutton));
        $saveasnewbutton = (isset($this->formdata->saveasnewbutton) && ($this->formdata->saveasnewbutton));
        $nextbutton      = (isset($this->formdata->nextbutton) && ($this->formdata->nextbutton));
        $pausebutton     = (isset($this->formdata->pausebutton) && ($this->formdata->pausebutton));
        $prevbutton      = (isset($this->formdata->prevbutton) && ($this->formdata->prevbutton));
        if ($saveasnewbutton) {
            $this->formdata->submissionid = 0;
        }

        $submissions = new stdClass();
        if (empty($this->formdata->submissionid)) {
            // add a new record to surveypro_submission
            $submissions->surveyproid = $this->surveypro->id;
            $submissions->userid = $USER->id;
            $submissions->timecreated = $timenow;

            // submit buttons are 3 and only 3
            if ($nextbutton || $pausebutton || $prevbutton) {
                $submissions->status = SURVEYPRO_STATUSINPROGRESS;
            }
            if ($savebutton || $saveasnewbutton) {
                $submissions->status = SURVEYPRO_STATUSCLOSED;
            }

            $submissions->id = $DB->insert_record('surveypro_submission', $submissions);

            $eventdata = array('context' => $this->context, 'objectid' => $submissions->id);
            $eventdata['other'] = array('view' => SURVEYPRO_NEWRESPONSE);
            $event = \mod_surveypro\event\submission_created::create($eventdata);
            $event->trigger();
        } else {
            // surveypro_submission already exists
            // but I asked to save
            if ($savebutton) {
                $submissions->id = $this->formdata->submissionid;
                $submissions->status = SURVEYPRO_STATUSCLOSED;
                $submissions->timemodified = $timenow;
                $DB->update_record('surveypro_submission', $submissions);
            } else {
                // I have $this->formdata->submissionid
                // case: "save" was requested, I am not here
                // case: "save as" was requested, I am not here
                // case: "next" was requested, so status = SURVEYPRO_STATUSINPROGRESS
                $status = $DB->get_field('surveypro_submission', 'status', array('id' => $this->formdata->submissionid), MUST_EXIST);
                $submissions->id = $this->formdata->submissionid;
                $submissions->status = $status;
            }
            $eventdata = array('context' => $this->context, 'objectid' => $submissions->id);
            $eventdata['other'] = array('view' => SURVEYPRO_EDITRESPONSE);
            $event = \mod_surveypro\event\submission_modified::create($eventdata);
            $event->trigger();
        }

        // before returning, set two class properties
        $this->submissionid = $submissions->id;
        $this->status = $submissions->status;
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

        // course context used locally to get groups
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
                // get_enrolled_users($courseid, $options = array()) <-- role is missing
                // get_users_from_role_on_context($role, $context);  <-- this is ok but I need to call it once per $role, below I make the query once all together
                $whereparams = array('contextid' => $context->id);
                $sql = 'SELECT DISTINCT '.user_picture::fields('u').', u.maildisplay, u.mailformat
                        FROM {user} u
                            JOIN {role_assignments} ra ON u.id = ra.userid
                        WHERE contextid = :contextid
                            AND roleid IN ('.$this->surveypro->notifyrole.')';
                $recipients = $DB->get_records_sql($sql, $whereparams);
            }
        } else {
            // notification to roles was not requested
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
        $a->title = get_string('reviewsubmissions', 'surveypro');
        $a->href = $CFG->wwwroot.'/mod/surveypro/view.php?s='.$this->surveypro->id;

        $htmlbody = $mailheader;
        $htmlbody .= get_string('newsubmissionbody', 'surveypro', $a);
        $htmlbody .= $mailfooter;

        $body = strip_tags($htmlbody);

        $subject = get_string('newsubmissionsubject', 'surveypro');

        foreach ($recipients as $recipient) {
            email_to_user($recipient, $from, $subject, $body, $htmlbody);
        }
    }

    /**
     * has_input_items as opposed to "has_search_items"
     *
     * @param none
     * @return
     */
    public function has_input_items() {
        global $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'hidden' => 0);
        if (!empty($this->formpage)) {
            $whereparams['formpage'] = $this->formpage;
        }
        if (!$this->canaccessadvanceditems) {
            $whereclause['advanced'] = 0;
        }

        return ($DB->count_records('surveypro_item', $whereparams) > 0);
    }

    /**
     * noitem_stopexecution
     *
     * @param none
     * @return
     */
    public function noitem_stopexecution() {
        global $COURSE, $OUTPUT;

        $message = get_string('noitemsfound', 'surveypro');
        echo $OUTPUT->notification($message, 'notifyproblem');

        $continueurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die();
    }

    /**
     * submissions_allowed
     *
     * @param none
     * @return
     */
    public function submissions_allowed() {
        // if $this->formdata is available, this means that the form was already displayed and submitted
        // so it is not the time to say the user is not allowed to submit one more surveypro
        if ($this->submissionid) { // submissionid is already defined, so I am not going to create one more new submission
            return true;
        }
        if ($this->formdata) {
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
    public function user_sent_submissions($status=SURVEYPRO_STATUSALL) {
        global $USER, $DB;

        $whereparams = array('surveyproid' => $this->surveypro->id, 'userid' => $USER->id);
        if ($status != SURVEYPRO_STATUSALL) {
            $statuslist = array(SURVEYPRO_STATUSCLOSED, SURVEYPRO_STATUSINPROGRESS);
            if (!in_array($status, $statuslist)) {
                $a = 'user_sent_submissions';
                print_error('invalid_status', 'surveypro', null, $a);
            }
            $whereparams['status'] = $status;
        }

        return $DB->count_records('surveypro_submission', $whereparams);
    }

    /**
     * submissions_exceeded_stopexecution
     *
     * @param none
     * @return
     */
    public function submissions_exceeded_stopexecution() {
        global $OUTPUT;

        $message = get_string('nomoresubmissionsallowed', 'surveypro', $this->surveypro->maxentries);
        echo $OUTPUT->notification($message, 'notifyproblem');

        $whereparams = array('id' => $this->cm->id, 'cover' => 0);
        $continueurl = new moodle_url('/mod/surveypro/view.php', $whereparams);

        echo $OUTPUT->continue_button($continueurl);
        echo $OUTPUT->footer();
        die();
    }

    /**
     * manage_thanks_page
     *
     * @param none
     * @return
     */
    public function manage_thanks_page() {
        global $OUTPUT;

        $savebutton = (isset($this->formdata->savebutton) && ($this->formdata->savebutton));
        $saveasnewbutton = (isset($this->formdata->saveasnewbutton) && ($this->formdata->saveasnewbutton));
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
    public function show_thanks_page() {
        global $DB, $OUTPUT, $USER;

        if ($this->view == SURVEYPRO_EDITRESPONSE) {
            $message = get_string('defaulteditingthanksmessage', 'surveypro');
        } else {
            if (!empty($this->surveypro->thankshtml)) {
                $message = file_rewrite_pluginfile_urls($this->surveypro->thankshtml, 'pluginfile.php', $this->context->id, 'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA, $this->surveypro->id);
            } else {
                $message = get_string('defaultcreationthanksmessage', 'surveypro');
            }
        }

        $paramurl = array('id' => $this->cm->id, 'cover' => 0);
        // just to save a query
        if (empty($this->surveypro->maxentries)) {
            $alreadysubmitted = -1;
        } else {
            $alreadysubmitted = $DB->count_records('surveypro_submission', array('surveyproid' => $this->surveypro->id, 'userid' => $USER->id));
        }
        $condition = ($alreadysubmitted < $this->surveypro->maxentries);
        $condition = $condition || empty($this->surveypro->maxentries);
        $condition = $condition || $this->canignoremaxentries;
        if ($condition) { // if the user is allowed to submit one more surveypro
            $buttonurl = new moodle_url('/mod/surveypro/view_userform.php', array('id' => $this->cm->id, 'view' => SURVEYPRO_NEWRESPONSE));
            $onemore = new single_button($buttonurl, get_string('addnewsubmission', 'surveypro'));

            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
            $gotolist = new single_button($buttonurl, get_string('gotolist', 'surveypro'));

            echo $OUTPUT->confirm($message, $onemore, $gotolist);
        } else {
            echo $OUTPUT->box($message, 'notice centerpara');
            $buttonurl = new moodle_url('/mod/surveypro/view.php', $paramurl);
            echo $OUTPUT->box($OUTPUT->single_button($buttonurl, get_string('gotolist', 'surveypro'), 'get'), 'clearfix mdl-align');
        }
    }

    /**
     * message_preview_mode
     *
     * @param none
     * @return
     */
    public function message_preview_mode() {
        global $OUTPUT;

        if ($this->modulepage == SURVEYPRO_ITEMS_PREVIEW) {
            $a = get_string('tabitemspage1', 'surveypro');
            $previewmodestring = get_string('previewmode', 'surveypro', $a);
            echo $OUTPUT->heading($previewmodestring, 4);
        }
    }

    /**
     * display_page_x_of_y
     *
     * @param none
     * @return
     */
    public function display_page_x_of_y() {
        global $OUTPUT;

        if ($this->maxassignedpage > 1) {
            $a = new stdClass();
            $a->formpage = $this->formpage;
            if ($this->formpage == SURVEYPRO_LEFT_OVERFLOW) {
                $a->formpage = 1;
            }
            if ($this->formpage == SURVEYPRO_RIGHT_OVERFLOW) {
                $a->formpage = $this->maxassignedpage;
            }

            $a->maxassignedpage = $this->maxassignedpage;
            echo $OUTPUT->heading(get_string('pagexofy', 'surveypro', $a));
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
        $params['submissionid'] = $this->submissionid;
        $params['view'] = SURVEYPRO_READONLYRESPONSE;

        if ($this->modulepage == SURVEYPRO_SUBMISSION_READONLY) {
            if ($this->maxassignedpage > 1) {
                if (($this->formpage != SURVEYPRO_LEFT_OVERFLOW) && ($this->formpage != 1)) {
                    $params['formpage'] = $this->formpage - 1;
                    $url = new moodle_url('/mod/surveypro/view_userform.php', $params);
                    $backwardbutton = new single_button($url, get_string('previousformpage', 'surveypro'), 'get');
                }

                if (($this->formpage != SURVEYPRO_RIGHT_OVERFLOW) && ($this->formpage != $this->maxassignedpage)) {
                    $params['formpage'] = $this->formpage + 1;
                    $url = new moodle_url('/mod/surveypro/view_userform.php', $params);
                    $forwardbutton = new single_button($url, get_string('nextformpage', 'surveypro'), 'get');
                }

                if (isset($backwardbutton) && isset($forwardbutton)) {
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
     * get_prefill_data
     *
     * @param none
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

    /**
     * drop_unexpected_values
     *
     * @param none
     * @return
     */
    public function drop_unexpected_values() {
        // BEGIN: delete all the bloody values that were NOT supposed to be returned: MDL-34815
        $dirtydata = (array)$this->formdata;
        $indexes = array_keys($dirtydata);

        $disposelist = array();
        $olditemid = 0;
        $regexp = '~'.SURVEYPRO_ITEMPREFIX.'_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        foreach ($indexes as $itemname) {
            if (!preg_match($regexp, $itemname, $matches)) { // if it starts with SURVEYPRO_ITEMPREFIX_
                continue;
            }
            $type = $matches[1]; // item type
            $plugin = $matches[2]; // item plugin
            $itemid = $matches[3]; // item id

            if ($itemid == $olditemid) {
                continue;
            }

            // let's start
            $olditemid = $itemid;

            $childitem = surveypro_get_item($itemid, $type, $plugin);

            if (empty($childitem->parentid)) {
                continue;
            }

            // if my parent is already in $disposelist, I have to go to $disposelist FOR SURE
            if (in_array($childitem->parentid, $disposelist)) {
                $disposelist[] = $childitem->itemid;
                continue;
            }

            // call parentitem
            $parentitem = surveypro_get_item($childitem->parentid);

            $parentinsamepage = false;
            foreach ($indexes as $itemname) {
                if (strpos($itemname, $parentitem->itemid)) {
                    $parentinsamepage = true;
                    break;
                }
            }

            if ($parentinsamepage) { // if parent is in this same page
                // tell parentitem what child needs in order to be displayed and compare it with what was answered to parentitem ($dirtydata)
                $expectedvalue = $parentitem->userform_child_item_allowed_dynamic($childitem->parentvalue, $dirtydata);
                // parentitem, knowing itself, compare what is needed and provide an answer

                if (!$expectedvalue) {
                    $disposelist[] = $childitem->itemid;
                }
            }
        } // check next item
        // END: delete all the bloody values that were supposed to NOT be returned: MDL-34815

        // if not expected items are here...
        if (count($disposelist)) {
            $regexp = '~'.SURVEYPRO_ITEMPREFIX.'_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
            foreach ($indexes as $itemname) {
                if (preg_match($regexp, $itemname, $matches)) {
                    // $type = $matches[1]; // item type
                    // $plugin = $matches[2]; // item plugin
                    $itemid = $matches[3]; // item id
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
    public function prevent_direct_user_input() {
        global $DB, $USER, $COURSE;

        if (($this->view == SURVEYPRO_READONLYRESPONSE) || ($this->view == SURVEYPRO_EDITRESPONSE)) {
            if (!$submission = $DB->get_record('surveypro_submission', array('id' => $this->submissionid), '*', IGNORE_MISSING)) {
                print_error('incorrectaccessdetected', 'surveypro');
            }
            if ($submission->userid != $USER->id) {
                $groupmode = groups_get_activity_groupmode($this->cm, $COURSE);
                if ($groupmode == SEPARATEGROUPS) {
                    $mygroupmates = surveypro_groupmates($this->cm);
                    // if I am a teacher, $mygroupmates is empty but I still have the right to see all my students
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
                // take care! Let's suppose this scenario:
                // $this->surveypro->maxentries = N
                // $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N - 1
                // when I fill the FIRST page of a survey, I get $next = N
                // but when I go to fill the SECOND page of a survey I have one more "in progress" survey
                // that is the one that I created when I saved the FIRST page, so...
                // $this->user_sent_submissions(SURVEYPRO_STATUSALL) = N
                // $next = N + 1
                // I am wrongly stopped here!
                // because of this:
                if ($this->submissionid) {
                    $next = $this->user_sent_submissions(SURVEYPRO_STATUSALL);
                } else {
                    $next = 1 + $this->user_sent_submissions(SURVEYPRO_STATUSALL);
                }

                $allowed = $this->cansubmit;
                if ($this->surveypro->timeopen) {
                    $allowed = $allowed && ($this->surveypro->timeopen < $timenow);
                }
                if ($this->surveypro->timeclose) {
                    $allowed = $allowed && ($this->surveypro->timeclose > $timenow);
                }
                if (!$this->canignoremaxentries) {
                    $allowed = $allowed && (($this->surveypro->maxentries == 0) || ($next <= $this->surveypro->maxentries));
                }
                break;
            case SURVEYPRO_PREVIEWSURVEYFORM:
                $allowed = has_capability('mod/surveypro:preview', $this->context);
                break;
            case SURVEYPRO_EDITRESPONSE:
                if ($USER->id == $submission->userid) {
                    // whether in progress, always allow
                    $allowed = ($submission->status == SURVEYPRO_STATUSINPROGRESS) ? true : $this->caneditownsubmissions;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $allowed = $groupuser && $this->caneditotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS
                        $allowed = $this->caneditotherssubmissions;
                    }
                }
                break;
            case SURVEYPRO_READONLYRESPONSE:
                if ($USER->id == $submission->userid) {
                    $allowed = true;
                } else {
                    if ($groupmode == SEPARATEGROUPS) {
                        $allowed = $groupuser && $this->canseeotherssubmissions;
                    } else { // NOGROUPS || VISIBLEGROUPS
                        $allowed = $this->canseeotherssubmissions;
                    }
                }
                break;
            default:
                $allowed = false;
        }
        if (!$allowed) {
            print_error('incorrectaccessdetected', 'surveypro');
        }
    }

    /**
     * duplicate_submission
     *
     * @param $allpages
     * @return
     */
    public function duplicate_submission() {
        global $DB;

        $submissions = $DB->get_record('surveypro_submission', array('id' => $this->submissionid));
        $submissions->timecreated = time();
        $submissions->status = SURVEYPRO_STATUSINPROGRESS;
        unset($submissions->timemodified);
        $submissionid = $DB->insert_record('surveypro_submission', $submissions);

        $surveyprouserdata = $DB->get_recordset('surveypro_answer', array('submissionid' => $this->submissionid));
        foreach ($surveyprouserdata as $userdatum) {
            unset($userdatum->id);
            $userdatum->submissionid = $submissionid;
            $DB->insert_record('surveypro_answer', $userdatum);
        }
        $surveyprouserdata->close();
        $this->submissionid = $submissionid;
    }

    /**
     * trigger_event
     *
     * @param $view
     * @return none
     */
    public function trigger_event($view) {
        switch ($view) {
            case SURVEYPRO_NOVIEW:
            case SURVEYPRO_EDITRESPONSE: // item_modified will be, eventually, logged
            case SURVEYPRO_NEWRESPONSE:  // item_created will be, eventually, logged
                break;
            case SURVEYPRO_PREVIEWSURVEYFORM:
                // event: form_previewed
                $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
                $eventdata['other'] = array('view' => SURVEYPRO_PREVIEWSURVEYFORM);
                $event = \mod_surveypro\event\form_previewed::create($eventdata);
                $event->trigger();
                break;
            case SURVEYPRO_READONLYRESPONSE:
                // event: submission_viewed
                $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
                $eventdata['other'] = array('view' => SURVEYPRO_READONLYRESPONSE);
                $event = \mod_surveypro\event\submission_viewed::create($eventdata);
                $event->trigger();
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $view = '.$view, DEBUG_DEVELOPER);
        }
    }
}
