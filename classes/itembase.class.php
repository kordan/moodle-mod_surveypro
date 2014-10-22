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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class defining an item
 */
class mod_surveypro_itembase {

    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = '';

    /**
     * unique itemid of the surveyproitem in surveypro_item table
     */
    public $itemid = 0;

    /**
     * $type = the type of the item. It can only be: SURVEYPRO_TYPEFIELD or SURVEYPRO_TYPEFORMAT
     */
    public $type = '';

    /**
     * $plugin = the item plugin
     */
    public $plugin = '';

    /**
     * $itemname = the name of the field as it is in userpageform
     */
    public $itemname = '';

    /**
     * $hidden = is this field going to be shown in the form?
     */
    public $hidden = 0;

    /**
     * $insearchform = is this field going to be part of the search form?
     */
    public $insearchform = 0;

    /**
     * $advanced = is this field going to be available only to users with accessadvanceditems capability?
     */
    public $advanced = 0;

    /**
     * $sortindex = the order of this item in the surveypro form
     */
    public $sortindex = 0;

    /**
     * $formpage = the user surveypro page for this item
     */
    public $formpage = 0;

    /**
     * $parentid = the item this item depends from
     */
    public $parentid = 0;

    /**
     * $parentvalue = the answer the parent item has to have in order to show this item as child
     */
    public $parentvalue = '';

    /**
     * $timecreated = the creation time of this item
     */
    public $timecreated = 0;

    /**
     * $timemodified = the modification time of this item
     */
    public $timemodified = null;

    /**
     * $userfeedback
     */
    public $userfeedback = SURVEYPRO_NOFEEDBACK;

    /**
     * $flag = features describing the object
     * I can redeclare the public and protected method/property, but not private
     * so I choose to not declare this properties here
     * public $flag = null;
     */

    /**
     * Class constructor
     */
    public function __construct($cm, $itemid, $evaluateparentcontent) {
        $this->cm = $cm;

        // if (isset($cm)) { // it is not set during upgrade whether an item is loaded
        $this->context = context_module::instance($cm->id);
        // }
    }

    /**
     * $isinitemform = list of fields properties the surveypro creator will have in the item definition form
     * By default each field property is present in the form
     * so, in each child class, I only need to "deactivate" field property (mform element) I don't want to have
     */
    public $isinitemform = array(
        'common_fs' => true,
        'content' => true,
        'customnumber' => true,
        'position' => true,
        'extranote' => true,
        'hideinstructions' => true,
        'required' => true,
        'variable' => true,
        'indent' => true,
        'hidden' => true,
        'advanced' => true,
        'insearchform' => true,
        'parentid' => true
    );

    /**
     * item_load
     *
     * @param integer $itemid
     * @param boolean $evaluateparentcontent
     * @return
     */
    public function item_load($itemid, $evaluateparentcontent) {
        global $DB;

        if (!$itemid) {
            $message = 'Something was wrong at line '.__LINE__.' of file '.__FILE__.'! Can not load an item without its ID';
            debugging($message, DEBUG_DEVELOPER);
        }

        $sql = 'SELECT *, si.id as itemid, plg.id as pluginid
                FROM {surveypro_item} si
                    JOIN {surveypro'.$this->type.'_'.$this->plugin.'} plg ON si.id = plg.itemid
                WHERE si.id = :itemid';

        if ($record = $DB->get_record_sql($sql, array('itemid' => $itemid))) {
            foreach ($record as $option => $value) {
                $this->{$option} = $value;
            }
            unset($this->id); // I do not care it. I already heave: itemid and pluginid
            $this->itemname = SURVEYPRO_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            if ($evaluateparentcontent && $this->parentid) {
                $parentitem = surveypro_get_item($this->parentid, null, null, false);
                $this->parentcontent = $parentitem->parent_decode_child_parentvalue($this->parentvalue);
            }
        } else {
            debugging('Something was wrong at line '.__LINE__.' of file '.__FILE__.'!<br />I can not find the surveypro item ID = '.$itemid.' using:<br />'.$sql, DEBUG_DEVELOPER);
        }
    }

    /**
     * item_get_common_settings
     * common settings are the setting saved to surveypro_item
     * they are:
     *     id
     *     √ surveyproid
     *     √ type
     *     √ plugin
     *     √ hidden
     *     √ insearchform
     *     √ advanced
     *     sortindex
     *     formpage
     *     √ parentid
     *     √ parentvalue
     *     √ timecreated
     *     √ timemodified
     *
     * in spite of this, here I also get:
     *     hideinstructions
     *     required
     *
     * in spite of this, here I also cleanup:
     *     extranote
     *     parentvalue
     *
     * The following settings will be calculated later
     *     sortindex
     *     formpage
     *
     * @param stdClass $record
     * @return
     */
    public function item_get_common_settings($record) {
        // you are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page
        surveypro_reset_items_pages($this->cm->instance);

        $timenow = time();

        // surveyproid
        $record->surveyproid = $this->cm->instance;

        // plugin and type are already onboard

        // checkboxes content
        // hidden, insearchform, advanced, hideinstructions, required
        $checkboxessettings = array('hidden', 'insearchform', 'advanced', 'hideinstructions', 'required');
        foreach ($checkboxessettings as $checkboxessetting) {
            if ($this->isinitemform[$checkboxessetting]) {
                $record->{$checkboxessetting} = isset($record->{$checkboxessetting}) ? 1 : 0;
            } else {
                $record->{$checkboxessetting} = 0;
            }
        }

        // parentid is already onboard

        // timecreated, timemodified
        if (empty($record->itemid)) {
            $record->timecreated = $timenow;
        }
        $record->timemodified = $timenow;

        // cleanup section

        // truncate extranote if longer than maximum allowed (255 characters)
        if (isset($record->extranote) && (strlen($record->extranote) > 255)) {
            $record->extranote = substr($record->extranote, 0, 255);
        }

        // surveypro can be multilang
        // so I can not save labels to parentvalue as they may change
        // because of this, even if the user writes, for instance, "bread\nmilk" to parentvalue
        // I have to encode it to key(bread);key(milk)
        if (isset($record->parentid) && $record->parentid) {
            $parentitem = surveypro_get_item($record->parentid);
            $record->parentvalue = $parentitem->parent_encode_child_parentcontent($record->parentcontent);
            unset($record->parentcontent);
        }
    }

    /**
     * item_save
     * Executes surveyproitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     *
     * @param stdClass $record
     * @return
     */
    public function item_save($record) {
        // $this->userfeedback
        //   +--- children inherited limited access
        //   |       +--- parents were made available for all
        //   |       |       +--- children were hided because this item was hided
        //   |       |       |       +--- parents were shown because this item was shown
        //   |       |       |       |       +--- new|edit
        //   |       |       |       |       |       +--- success|fail
        // [0|1] - [0|1] - [0|1] - [0|1] - [0|1] - [0|1]
        // last digit (on the right, of course) == 1 means that the process was globally successfull
        // last digit (on the right, of course) == 0 means that the process was globally NOT successfull

        // beforelast digit == 0 means NEW
        // beforelast digit == 1 means EDIT

        // (digit in place 2) == 1 means items were shown because this (as child) was shown
        // (digit in place 3) == 1 means items were hided because this (as parent) was hided
        // (digit in place 4) == 1 means items reamin as they are because unlimiting parent does not force any change to children
        // (digit in place 5) == 1 means items inherited limited access because this (as parent) got a limited access

        $tablename = 'surveypro'.$this->type.'_'.$this->plugin;
        $this->userfeedback = SURVEYPRO_NOFEEDBACK;

        // Does this record need to be saved as new record or as un update on a preexisting record?
        if (empty($record->itemid)) {
            // item is new

            // sortindex
            $sql = 'SELECT COUNT(\'x\')
                    FROM {surveypro_item}
                    WHERE surveyproid = :surveyproid
                        AND sortindex > 0';
            $whereparams = array('surveyproid' => $this->cm->instance);
            $record->sortindex = 1 + $DB->count_records_sql($sql, $whereparams);

            // itemid
            try {
                $transaction = $DB->start_delegated_transaction();

                if ($itemid = $DB->insert_record('surveypro_item', $record)) { // <-- first surveypro_itemsave

                    // $tablename
                    // before saving to the the plugin table, validate the variable name
                    $this->item_validate_variablename($record, $itemid);

                    $record->itemid = $itemid;
                    if ($pluginid = $DB->insert_record($tablename, $record)) { // <-- first $tablename save
                        $this->userfeedback += 1; // 0*2^1+1*2^0
                    }
                }

                // special care to "editors"
                if ($this->flag->editorslist) {
                    $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
                    foreach ($this->flag->editorslist as $fieldname => $filearea) {
                        $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $this->context, 'mod_surveypro', $filearea, $record->itemid);
                        $record->{$fieldname.'format'} = FORMAT_HTML;
                    }

                    // tablename
                    // id
                    $record->id = $pluginid;

                    if (!$DB->update_record($tablename, $record)) { // <-- $tablename update
                        $this->userfeedback -= ($this->userfeedback % 2); // whatever it was, now it is a fail
                    // } else {
                        // leave the previous $this->userfeedback
                        // if it was a success, leave it as now you got one more success
                        // if it was a fail, leave it as you can not cover the previous fail
                    }
                    // record->content follows standard flow and has already been saved at first save time
                }

                $transaction->allow_commit();

                // event: item_created
                $eventdata = array('context' => $this->context, 'objectid' => $record->itemid);
                $eventdata['other'] = array('type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_EDITITEM);
                $event = \mod_surveypro\event\item_created::create($eventdata);
                $event->trigger();
            } catch (Exception $e) {
                //extra cleanup steps
                $transaction->rollback($e); // rethrows exception
            }
        } else {
            // item is already known

            // special care to "editors"
            if ($this->flag->editorslist) {
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $this->context);
                foreach ($this->flag->editorslist as $fieldname => $filearea) {
                    $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $this->context, 'mod_surveypro', $filearea, $record->itemid);
                    $record->{$fieldname.'format'} = FORMAT_HTML;
                }
                // } else {
                // record->content follows standard flow and will be evaluated in the standard way
            }

            // hide/unhide part 1
            $oldhidden = $this->get_hidden(); // used later
            // $oldhidden = $DB->get_field('surveypro_item', 'hidden', array('id' => $record->itemid)); // used later
            // end of: hide/unhide 1

            // limit/unlimit access part 1
            $oldadvanced = $this->get_advanced(); // used later
            // end of: limit/unlimit access part 1

            // sortindex
            // doesn't change at item editing time

            // surveypro_item
            // id
            $record->id = $record->itemid;

            try {
                $transaction = $DB->start_delegated_transaction();

                if ($DB->update_record('surveypro_item', $record)) {
                    // $tablename

                    // before saving to the the plugin table, I validate the variable name
                    $this->item_validate_variablename($record, $record->itemid);

                    $record->id = $record->pluginid;
                    if ($DB->update_record($tablename, $record)) {
                        $this->userfeedback += 3; // 1*2^1+1*2^0 alias: editing + success
                    } else {
                        $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
                    }
                } else {
                    $this->userfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail
                }

                $transaction->allow_commit();
            } catch (Exception $e) {
                //extra cleanup steps
                $transaction->rollback($e); // rethrows exception
            }

            // save process is over

            $this->item_manage_chains($record->itemid, $oldhidden, $record->hidden, $oldadvanced, $record->advanced);

            // event: item_modified
            $eventdata = array('context' => $this->context, 'objectid' => $record->itemid);
            $eventdata['other'] = array('type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_EDITITEM);
            $event = \mod_surveypro\event\item_modified::create($eventdata);
            $event->trigger();
        }

        // $this->userfeedback is going to be part of $returnurl in items_setup.php and to be send to items_manage.php
        return $record->itemid;
    }

    /**
     * item_validate_variablename
     *
     * @param stdobject $record
     * @param integer $itemid
     *
     * @return
     */
    public function item_validate_variablename($record, $itemid) {
        global $DB;

        // if variable does not exist
        if ($this->type == SURVEYPRO_TYPEFORMAT) {
            return;
        }

        $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$this->plugin;
        $whereparams = array('itemid' => $itemid, 'surveyproid' => (int)$record->surveyproid);
        $select = 'SELECT COUNT(p.id)
                FROM {'.$tablename.'} p
                    JOIN {surveypro_item} i ON i.id = p.itemid ';
        $whereset = 'WHERE ((p.itemid <> :itemid) AND (i.surveyproid = :surveyproid))';
        $whereverify = 'WHERE ((p.itemid <> :itemid) AND (i.surveyproid = :surveyproid) AND (p.variable = :variable))';

        // Verify variable was set. If not, set it.
        if (!isset($record->variable) || empty($record->variable)) {
            $sql = $select.$whereset;
            $plugincount = 1 + $DB->count_records_sql($sql, $whereparams);
            $plugincount = str_pad($plugincount, 3, '0', STR_PAD_LEFT);

            $candidatevariable = $this->plugin.'_'.$plugincount;
        } else {
            $candidatevariable = $record->variable;
        }

        // verify the given name is unique. If not, change it.
        $i = 0; // if name is duplicate, restart verification from 0
        $whereparams['variable'] = $candidatevariable;
        $sql = $select.$whereverify;

        // while ($DB->record_exists_sql($sql, $whereparams)) {
        while ($DB->count_records_sql($sql, $whereparams)) {
            $i++;
            $candidatevariable = $record->plugin.'_'.str_pad($i, 3, '0', STR_PAD_LEFT);
            $whereparams['variable'] = $candidatevariable;
        }

        $record->variable = $candidatevariable;
    }

    /**
     * item_save
     * Executes surveyproitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     *
     * @param integer $itemid
     * @param boolean 0/1 $oldhidden
     * @param boolean 0/1 $newhidden
     * @param boolean 0/1 $oldadvanced
     * @param boolean 0/1 $newadvanced
     * @return
     */
    public function item_manage_chains($itemid, $oldhidden, $newhidden, $oldadvanced, $newadvanced) {
        // now hide or unhide (whether needed) chain of ancestors or descendents
        if ($this->userfeedback & 1) { // bitwise logic, alias: if the item was successfully saved
            // -----------------------------
            // manage ($oldhidden != $newhidden)
            // -----------------------------
            if ($oldhidden != $newhidden) {
                $surveypro = $DB->get_record('surveypro', array('id' => $this->cm->instance), '*', MUST_EXIST);
                $action = ($oldhidden) ? SURVEYPRO_SHOWITEM : SURVEYPRO_HIDEITEM;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $this->context, $surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);
            }

            // hide/unhide part 2
            if ( ($oldhidden == 1) && ($newhidden == 0) ) {
                if ($itemlistman->manage_item_show()) {
                    // a chain of parent items has been showed
                    $this->userfeedback += 4; // 1*2^2
                }
            }
            if ( ($oldhidden == 0) && ($newhidden == 1) ) {
                if ($itemlistman->manage_item_hide()) {
                    // a chain of child items has been hided
                    $this->userfeedback += 8; // 1*2^3
                }
            }
            // end of: hide/unhide part 2

            // -----------------------------
            // manage ($oldadvanced != $newadvanced)
            // -----------------------------
            if ($oldadvanced != $newadvanced) {
                $surveypro = $DB->get_record('surveypro', array('id' => $this->cm->instance), '*', MUST_EXIST);
                $action = ($oldadvanced) ? SURVEYPRO_MAKEFORALL : SURVEYPRO_MAKELIMITED;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $this->context, $surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);
            }

            // limit/unlimit access part 2
            if ( ($oldadvanced == 1) && ($newadvanced == 0) ) {
                if ($itemlistman->manage_item_makestandard()) {
                    // a chain of parent items has been made available for all
                    $this->userfeedback += 16; // 1*2^4
                }
            }
            if ( ($oldadvanced == 0) && ($newadvanced == 1) ) {
                if ($itemlistman->manage_item_makeadvanced()) {
                    // a chain of child items got a limited access
                    $this->userfeedback += 32; // 1*2^5
                }
            }
            // end of: limit/unlimit access part 2
        }
    }

    /**
     * item_update_childparentvalue
     *
     * @param none
     * @return
     */
    public function item_update_childrenparentvalue() {
        global $DB;

        if ($this::$canbeparent) {
            // take care: you can not use $this->item_get_content_array(SURVEYPRO_VALUES, 'options') to evaluate values
            // because $item was loaded before last save, so $this->item_get_content_array(SURVEYPRO_VALUES, 'options')
            // is still returning the previous values

            $children = $DB->get_records('surveypro_item', array('parentid' => $this->itemid), 'id', 'id, parentvalue');
            foreach ($children as $child) {
                $childparentvalue = $child->parentvalue;

                // decode $childparentvalue to $childparentcontent
                $childparentcontent = $this->parent_decode_child_parentvalue($childparentvalue);

                // encode $childparentcontent to $childparentvalue, once again
                $child->parentvalue = $this->parent_encode_child_parentcontent($childparentcontent);

                // save the child
                $DB->update_record('surveypro_item', $child);
            }
        }
    }

    /**
     * item_builtin_string_load_support
     * This function is used to populate empty strings according to the user language
     *
     * @param none
     * @return
     */
    public function item_builtin_string_load_support() {
        global $CFG, $DB;

        $surveyproid = $this->get_surveyproid();
        $template = $DB->get_field('surveypro', 'template', array('id' => $surveyproid), MUST_EXIST);
        if (empty($template)) {
            return;
        }

        // Take care: I verify the existence of the english folder even if, maybe, I will ask for the string in a different language
        if (!file_exists($CFG->dirroot.'/mod/surveypro/template/'.$template.'/lang/en/surveyprotemplate_'.$template.'.php')) {
            // this template does not support multilang
            return;
        }

        if ($multilangfields = $this->item_get_multilang_fields()) {
            foreach ($multilangfields as $plugin => $fieldnames) {
                foreach ($fieldnames as $fieldname) {
                    $stringkey = $this->{$fieldname};
                    $this->{$fieldname} = get_string($stringkey, 'surveyprotemplate_'.$template);
                }
            }
        }
    }

    /**
     * item_split_unix_time
     *
     * @param integer $time
     * @param boolean $applyusersettings
     * @return
     */
    public function item_split_unix_time($time, $applyusersettings=false) {
        if ($applyusersettings) {
            $datestring = userdate($time, '%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', 0);
        } else {
            $datestring = gmstrftime('%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', $time);
        }
        // May_Tuesday_193_2012_07_3_11_16_03_59

        list(
            $getdate['month'],
            $getdate['weekday'],
            $getdate['yday'],
            $getdate['year'],
            $getdate['mon'],
            $getdate['wday'],
            $getdate['mday'],
            $getdate['hours'],
            $getdate['minutes'],
            $getdate['seconds']
        ) = explode('_', $datestring);

        // print_object($getdate);
        return $getdate;
    }

    /**
     * item_delete
     *
     * @param integer $itemid
     * @return
     */
    public function item_delete($itemid) {
        $recordtokill = $DB->get_record('surveypro_item', array('id' => $itemid));
        if (!$DB->delete_records('surveypro_item', array('id' => $itemid))) {
            print_error('notdeleted_item', 'surveypro', null, $itemid);
        }

        if (!$DB->delete_records('surveypro'.$this->type.'_'.$this->plugin, array('id' => $this->pluginid))) {
            $a = new stdClass();
            $a->pluginid = $this->pluginid;
            $a->type = $this->type;
            $a->plugin = $this->plugin;
            print_error('notdeleted_plugin', 'surveypro', null, $a);
        }

        $eventdata = array('context' => $this->context, 'objectid' => $this->cm->instance);
        $eventdata['other'] = array('plugin' => $this->plugin);
        $event = \mod_surveypro\event\item_deleted::create($eventdata);
        $event->trigger();

        surveypro_reset_items_pages($this->cm->instance);

        // delete records from surveypro_answer
        // if, at the end, the related surveypro_submission has no data, then, delete it too.
        if (!$DB->delete_records('surveypro_answer', array('itemid' => $itemid))) {
            print_error('notdeleted_userdata', 'surveypro', null, $itemid);
        }

        $emptysubmissions = 'SELECT c.id
                             FROM {surveypro_submission} c
                                 LEFT JOIN {surveypro_answer} d ON c.id = d.submissionid
                             WHERE (d.id IS null)';
        if ($surveyprotodelete = $DB->get_records_sql($emptysubmissions)) {
            $surveyprotodelete = array_keys($surveyprotodelete);
            if (!$DB->delete_records_select('surveypro_submission', 'id IN ('.implode(',', $surveyprotodelete).')')) {
                $a = implode(',', $surveyprotodelete);
                print_error('notdeleted_submission', 'surveypro', null, $a);
            }
        }
    }

    /**
     * item_uses_form_page
     *
     * @return: boolean
     */
    public function item_uses_form_page() {
        return true;
    }

    /**
     * item_left_position_allowed
     *
     * @return: boolean
     */
    public function item_left_position_allowed() {
        return true;
    }

    /**
     * item_set_editor
     * defines presets for the editor field of surveyproitem in itembase_form.php
     * (copied from moodle20/cohort/edit.php)
     *
     * @param &$saveditem
     * @return
     */
    public function item_set_editor() {
        if (!$this->flag->editorslist) {
            return;
        }

        // some examples
        // each SURVEYPRO_ITEMFIELD has: $this->isinitemform['content'] == true  and $this->flag->editorslist == array('content')
        // fieldset              has: $this->isinitemform['content'] == true  and $this->flag->editorslist == null
        // pagebreak             has: $this->isinitemform['content'] == false and $this->flag->editorslist == null
        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $this->context);
        foreach ($this->flag->editorslist as $fieldname => $filearea) {
            $this->{$fieldname.'format'} = FORMAT_HTML;
            $this->{$fieldname.'trust'} = 1;
            file_prepare_standard_editor($this, $fieldname, $editoroptions, $this->context, 'mod_surveypro', $filearea, $this->itemid);
        }
    }

    /**
     * item_get_content_array
     * get the first or the second part of each row of a textarea field
     * where each row is written with the format:
     *     value::label
     *     or
     *     label
     *
     * @param string $content: the part of the line I want to get
     * @param string $field: the text area field, source of the multiline text
     * @return array $values
     */
    public function item_get_content_array($content, $field) {
        if (($content != SURVEYPRO_VALUES) && ($content != SURVEYPRO_LABELS)) {
            throw new Exception('Bad parameter passed to item_get_content_array');
        }

        $index = ($content == SURVEYPRO_VALUES) ? 1 : 2;
        $options = surveypro_textarea_to_array($this->{$field});

        $values = array();
        foreach ($options as $k => $option) {
            if (preg_match('/^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$/', $option, $match)) {
                $values[] = $match[$index];
            } else {
                $values[] = $option;
            }
        }

        return $values;
    }

    /**
     * $this->item_clean_textarea_fields
     * clean the content of the field $record->{$field}
     *
     * @param stadCalss $record: the item record
     * @param array $fieldlist: the list of fields to clean
     * @return nothing
     */
    public function item_clean_textarea_fields($record, $fieldlist) {
        foreach ($fieldlist as $field) {
            // Some item may be undefined causing:
            // Notice: Undefined property: stdClass::$defaultvalue
            // as, for instance, disabled $defaultvalue field when $delaultoption == invitation
            if (isset($record->{$field})) {
                $temparray = surveypro_textarea_to_array($record->{$field});
                $record->{$field} = implode("\n", $temparray);
            }
        }
    }

    /**
     * item_get_other
     * parse $this->labelother in $value and $label
     *
     * @return array($value, $label)
     */
    public function item_get_other() {
        if (preg_match('/^(.*)'.SURVEYPRO_OTHERSEPARATOR.'(.*)$/', $this->labelother, $match)) { // do not warn: it can never be equal to zero
            $value = trim($match[2]);
            $label = trim($match[1]);
        } else {
            $value = '';
            $label = trim($this->labelother);
        }

        return array($value, $label);
    }

    /**
     * item_mandatory_is_allowed
     * this method defines if an item can be switched to mandatory or not.
     *
     * @return boolean
     */
    public function item_mandatory_is_allowed() {
        // a mandatory field is allowed ONLY if
        //     -> !isset($this->defaultoption)
        //     -> $this->defaultoption != SURVEYPRO_NOANSWERDEFAULT
        if (isset($this->defaultoption)) {
            return ($this->defaultoption != SURVEYPRO_NOANSWERDEFAULT);
        } else {
            return true;
        }
    }

    /**
     * item_get_multilang_fields
     * make the list of multilang plugin fields
     *
     * @return array of felds
     */
    public function item_get_multilang_fields() {
        $fieldlist = array();
        $fieldlist[$this->plugin] = array('content');

        return $fieldlist;
    }

    /**
     * item_get_item_schema
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function item_get_item_schema() {
        $schema = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $schema .= '<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">'."\n";
        $schema .= '    <xs:element name="surveypro_item">'."\n";
        $schema .= '        <xs:complexType>'."\n";
        $schema .= '            <xs:sequence>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="surveyproid"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="hidden"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="insearchform"/>'."\n";
        $schema .= '                <xs:element type="xs:int" name="advanced"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="sortindex"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="formpage"/>'."\n";

        $schema .= '                <xs:element type="xs:int" name="parentid" minOccurs="0"/>'."\n";
        $schema .= '                <xs:element type="xs:string" name="parentvalue" minOccurs="0"/>'."\n";

        // $schema .= '                <xs:element type="xs:int" name="timecreated"/>'."\n";
        // $schema .= '                <xs:element type="xs:int" name="timemodified"/>'."\n";
        $schema .= '            </xs:sequence>'."\n";
        $schema .= '        </xs:complexType>'."\n";
        $schema .= '    </xs:element>'."\n";
        $schema .= '</xs:schema>';

        return $schema;
    }

    /**
     * item_add_color_unifier
     * credits for this amazing solution to the great Eloy Lafuente! He is a genius.
     * add a dummy useless row (with height = 0) to the form in order to drop the color alternation
     * mainly used for rate items, but not only
     *
     * @param $mform: the form to which add the row
     * @param integer $currentposition: a counter to decide whether add the row
     * @param integer $allpositions: one more counter to decide whether add the row
     * @return nothing
     */
    public function item_add_color_unifier($mform, $currentposition=null, $allpositions=null) {
        if (is_null($currentposition) && is_null($allpositions)) {
            $addcolorunifier = true;
        } else {
            if ( (!is_null($currentposition) && is_null($allpositions)) ||
                 (is_null($currentposition) && !is_null($allpositions)) ) {
                debugging('Bad parameters passed to item_add_color_unifier', DEBUG_DEVELOPER);
            }
            if ($currentposition < $allpositions) {
                $addcolorunifier = true;
            } else {
                $addcolorunifier = !$this->required; // ????
            }
        }

        if ($addcolorunifier) {
            $mform->addElement('html', '<div class="hidden fitem fitem_fgroup colorunifier"></div>');
        }
    }

    /**
     * item_get_generic_property
     *
     * @param $field
     * @return the content of the field whether defined
     */
    public function item_get_generic_property($field) {
        if (isset($this->{$field})) {
            return $this->{$field};
        } else {
            return false;
        }
    }

    // MARK get

    /**
     * get_issearchable
     *
     * @return the content of the flag
     */
    public function get_issearchable() {
        return $this->flag->issearchable;
    }

    /**
     * get_usescontenteditor
     *
     * @return the content of the flag
     */
    public function get_usescontenteditor() {
        return $this->flag->usescontenteditor;
    }

    /**
     * get_editorslist
     *
     * @return the content of the flag
     */
    public function get_editorslist() {
        return $this->flag->editorslist;
    }

    /**
     * get_editorslist
     *
     * @return the content of the flag
     */
    public function get_savepositiontodb() {
        return $this->flag->savepositiontodb;
    }

    /**
     * get_isinitemform
     *
     * @return the content of the corresponding element of $this->isinitemform
     */
    public function get_isinitemform($itemformelement) {
        return $this->isinitemform[$itemformelement];
    }

    /**
     * get_itemid
     *
     * @return the content of the field
     */
    public function get_itemid() {
        return $this->itemid;
    }

    /**
     * get_type
     *
     * @return the content of the field
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * get_plugin
     *
     * @return the content of the field
     */
    public function get_plugin() {
        return $this->plugin;
    }

    /**
     * get_content
     *
     * @return the content of the field
     */
    public function get_content() {
        return file_rewrite_pluginfile_urls($this->content, 'pluginfile.php', $this->context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $this->itemid);
    }

    /**
     * get_contentformat
     *
     * @return the content of the field
     */
    public function get_contentformat() {
        return $this->contentformat;
    }

    /**
     * get_surveyproid
     *
     * @return the content of the field
     */
    public function get_surveyproid() {
        return $this->surveyproid;
    }

    /**
     * get_pluginid
     *
     * @return the content of the field
     */
    public function get_pluginid() {
        return $this->pluginid;
    }

    /**
     * get_itemname
     *
     * @return the content of the field
     */
    public function get_itemname() {
        return $this->itemname;
    }

    /**
     * get_hidden
     *
     * @return the content of the field
     */
    public function get_hidden() {
        return $this->hidden;
    }

    /**
     * get_insearchform
     *
     * @return the content of the field
     */
    public function get_insearchform() {
        return $this->insearchform;
    }

    /**
     * get_advanced
     *
     * @return the content of the field
     */
    public function get_advanced() {
        return $this->advanced;
    }

    /**
     * get_sortindex
     *
     * @return the content of the field
     */
    public function get_sortindex() {
        return $this->sortindex;
    }

    /**
     * get_formpage
     *
     * @return the content of the field
     */
    public function get_formpage() {
        return $this->formpage;
    }

    /**
     * get_parentid
     *
     * @return the content of the field
     */
    public function get_parentid() {
        return $this->parentid;
    }

    /**
     * get_parentcontent
     *
     * @param string $separator: the required separator
     * @return the content of the field properly separated
     */
    public function get_parentcontent($separator="\n") {
        if ($separator != "\n") {
            $parentcontent = explode("\n", $this->parentcontent);
            $parentcontent = implode($separator, $parentcontent);
            return $parentcontent;
        } else {
            return $this->parentcontent;
        }
    }

    /**
     * get_parentvalue
     *
     * @return the content of the field
     */
    public function get_parentvalue() {
        return $this->parentvalue;
    }

    /**
     * get_variable
     *
     * @return the content of the field whether defined
     */
    public function get_variable() {
        return $this->variable;
    }

    /**
     * get_customnumber
     *
     * @return the content of the field whether defined
     */
    public function get_customnumber() {
        if (isset($this->customnumber)) {
            return $this->customnumber;
        } else {
            return false;
        }
    }

    /**
     * get_required
     *
     * @return bool
     */
    public function get_required() {
        // it may not be set as in page_break, autofill or some more
        if (!isset($this->required)) {
            return false;
        } else {
            if (empty($this->required)) {
                return 0;
            } else {
                return 1;
            }
        }
    }

    /**
     * get_indent
     *
     * @return the content of the field whether defined
     */
    public function get_indent() {
        if (isset($this->indent)) {
            return $this->indent;
        } else {
            return false;
        }
    }

    /**
     * get_hideinstructions
     *
     * @return the content of the field whether defined
     */
    public function get_hideinstructions() {
        if (isset($this->hideinstructions)) {
            return $this->hideinstructions;
        } else {
            return false;
        }
    }

    /**
     * get_position
     *
     * @return the content of the field whether defined
     */
    public function get_position() {
        if (isset($this->position)) {
            return $this->position;
        } else {
            return false;
        }
    }

    /**
     * get_extranote
     *
     * @return the content of the field whether defined
     */
    public function get_extranote() {
        if (isset($this->extranote)) {
            return $this->extranote;
        } else {
            return false;
        }
    }

    /**
     * get_downloadformat
     *
     * @return the content of the field whether defined
     */
    public function get_downloadformat() {
        if (isset($this->downloadformat)) {
            return $this->downloadformat;
        } else {
            return false;
        }
    }

    /**
     * get_requiredfieldname
     *
     * @return string the name of the database table field specifying if the item is required
     */
    public static function get_requiredfieldname() {
        return 'required';
    }

    // MARK set

    /**
     * set_contentformat
     *
     * @param string $contentformat
     * @return nothing
     */
    public function set_contentformat($contentformat) {
        $this->contentformat = $contentformat;
    }

    /**
     * set_contenttrust
     *
     * @param string $contenttrust
     * @return nothing
     */
    public function set_contenttrust($contenttrust) {
        $this->contenttrust = $contenttrust;
    }

    /**
     * set_required
     *
     * @param integer $value; the value to set
     * @return nothing
     */
    public function set_required($value) {
        global $DB;

        if (($value != 0) && ($value != 1)) {
            throw new Exception('Bad parameter passed to set_required');
        }

        $DB->set_field('surveypro'.$this->type.'_'.$this->plugin, 'required', $value, array('itemid' => $this->itemid));
    }

    // MARK parent

    /**
     * parent_validate_child_constraints
     *
     * @param $childvalue
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childparentvalue) {
        /**
         * I can not make ANY assumption about $childparentvalue because of the following explanation:
         * At child save time, I encode its $parentcontent to $parentvalue.
         * The encoding is done through a parent method according to parent values.
         * Once the child is saved, I can return to parent and I can change it as much as I want.
         * For instance by changing the number and the content of its options.
         * At parent save time, the child parentvalue is rewritten
         * -> but it may result in a too short or too long list of keys
         * -> or with a wrong number of unrecognized keys so I need to...
         * ...implement all possible checks to avoid crashes/malfunctions during code execution.
         */
    }


    // MARK userform

    /**
     * userform_get_full_info == extranote + fillinginstruction
     *     full_info == extranote + fillinginstruction
     * provides extra info THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     *
     * @param boolean $searchform: is this method called
     * @return string fullinfo
     */
    public function userform_get_full_info($searchform) {
        $config = get_config('surveypro');

        if (!$searchform) {
            if (!$this->get_hideinstructions()) {
                $fillinginstruction = $this->userform_get_filling_instructions();
            }
            if (isset($this->extranote)) {
                $extranote = strip_tags($this->extranote);
            }
        } else {
            if ($config->fillinginstructioninsearch) {
                if (!$this->get_hideinstructions()) {
                    $fillinginstruction = $this->userform_get_filling_instructions();
                }
            }
            if ($config->extranoteinsearch) {
                $extranote = strip_tags($this->extranote);
            }
        }
        if (isset($fillinginstruction) && $fillinginstruction && isset($extranote) && $extranote) {
            return ($fillinginstruction.'<br />'.$extranote);
        } else {
            if (isset($fillinginstruction) && $fillinginstruction) {
                return $fillinginstruction;
            }
            if (isset($extranote) && $extranote) {
                return $extranote;
            }
        }
    }

    /**
     * userform_get_filling_instructions
     * provides extra fillinginstruction THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form
     *
     * if this method is not handled at plugin level,
     * it means it is supposed to return an empty fillinginstruction
     *
     * @return empty string
     */
    public function userform_get_filling_instructions() {
        return '';
    }

    /**
     * userform_child_item_allowed_static
     * this method is called if (and only if) parent item and child item DON'T live in the same form page
     * this method has two purposes:
     * - skip the iitem from the current page of $userpageform
     * - get if a page has items
     *
     * as parentitem declare whether my child item is allowed to in the page that is going to be displayed
     *
     * @param int $submissionid:
     * @param array $childitemrecord:
     * @return $status: true: the item is welcome; false: the item must be dropped out
     */
    public function userform_child_item_allowed_static($submissionid, $childitemrecord) {
        global $DB;

        if (!$childitemrecord->parentid) {
            return true;
        }

        $where = array('submissionid' => $submissionid, 'itemid' => $this->itemid);
        $givenanswer = $DB->get_field('surveypro_answer', 'content', $where);

        return ($givenanswer === $childitemrecord->parentvalue);
    }

    /**
     * userform_disable_element
     * this function is used ONLY if $surveypro->newpageforchild == false
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is calculated and is requested to provide the disabledif conditions to disable its child item
     *
     * @param $mform
     * @param $canaccessadvanceditems
     * @return
     */
    public function userform_disable_element($mform, $canaccessadvanceditems) {
        global $DB;

        if (!$this->parentid || ($this->type == SURVEYPRO_TYPEFORMAT)) {
            return;
        }

        $fieldnames = $this->userform_get_root_elements_name();

        $parentrestrictions = array();

        // if I am here this means I have a parent FOR SURE
        // instead of making one more query, I assign two variables manually
        // at the beginning, $currentitem is me
        $currentitem = new stdClass();
        $currentitem->parentid = $this->get_parentid();
        $currentitem->parentvalue = $this->get_parentvalue();
        $mypage = $this->get_formpage(); // once and forever
        do {
            /**
             * Take care.
             * Even if (!$surveypro->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually
             * Because of this, I need to chech page numbers
             */
            $parentitem = $DB->get_record('surveypro_item', array('id' => $currentitem->parentid), 'parentid, parentvalue, formpage');
            $parentpage = $parentitem->formpage;
            if ($parentpage == $mypage) {
                $parentid = $currentitem->parentid;
                $parentvalue = $currentitem->parentvalue;
                $parentrestrictions[$parentid] = $parentvalue; // The element with ID == $parentid requires, as constain, $parentvalue
            } else {
                // my parent is in a page before mine
                // no need to investigate more for older ancestors
                break;
            }

            $currentitem = $parentitem;
        } while (!empty($parentitem->parentid));
        // $parentrecord is an associative array
        // The array key is the ID of the parent item, the corresponding value is the constrain that $this has to be submitted to

        $displaydebuginfo = false;
        foreach ($parentrestrictions as $parentid => $childparentvalue) {
            $parentitem = surveypro_get_item($parentid);
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childparentvalue);

            if ($displaydebuginfo) {
                foreach ($disabilitationinfo as $parentinfo) {
                    if (is_array($parentinfo->content)) {
                        $contentdisplayed = 'array('.implode(',', $parentinfo->content).')';
                    } else {
                        $contentdisplayed = '\''.$parentinfo->content.'\'';
                    }
                    foreach ($fieldnames as $fieldname) {
                        if (isset($parentinfo->operator)) {
                            echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.
                                    $parentinfo->parentname.'\', \''.$parentinfo->operator.'\', '.$contentdisplayed.');</span><br />';
                        } else {
                            echo '<span style="color:green;">$mform->disabledIf(\''.$fieldname.'\', \''.
                                    $parentinfo->parentname.'\', '.$contentdisplayed.');</span><br />';
                        }
                    }
                }
            }

            // write disableIf
            foreach ($disabilitationinfo as $parentinfo) {
                foreach ($fieldnames as $fieldname) {
                    if (isset($parentinfo->operator)) {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->operator, $parentinfo->content);
                    } else {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->content);
                    }
                }
            }
            // $mform->disabledIf('surveypro_field_select_2491', 'surveypro_field_multiselect_2490[]', 'neq', array(0,4));
        }
    }

    /**
     * userform_db_to_export
     * strating from the info stored in the database, this function returns the corresponding content for the export file
     *
     * @param $answers
     * @param $format
     * @return
     */
    public function userform_db_to_export($answer, $format='') {
        $content = trim($answer->content);
        if ($content == SURVEYPRO_NOANSWERVALUE) { // answer was "no answer"
            return get_string('answerisnoanswer', 'surveypro');
        }
        if ($content === null) { // item was disabled
            return get_string('notanswereditem', 'surveypro');
        }

        return $content;
    }
}
