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
 * The base class defining an item
 */
class mod_surveypro_itembase {
    /**
     * Basic necessary essential ingredients
     */
    protected $cm;
    // protected $context;

    /**
     * unique itemid of the surveyproitem in surveypro_item table
     */
    protected $itemid;

    /**
     * $type = the type of the item. It can only be: SURVEYPRO_TYPEFIELD or SURVEYPRO_TYPEFORMAT
     */
    protected $type;

    /**
     * $plugin = the item plugin
     */
    protected $plugin;

    /**
     * $itemname = the name of the field as it is in userpageform
     */
    protected $itemname;

    /**
     * $hidden = is this field going to be shown in the form?
     */
    protected $hidden;

    /**
     * $insearchform = is this field going to be part of the search form?
     */
    protected $insearchform;

    /**
     * $advanced = is this field going to be available only to users with accessadvanceditems capability?
     */
    protected $advanced;

    /**
     * $sortindex = the order of this item in the surveypro form
     */
    protected $sortindex;

    /**
     * $formpage = the user surveypro page for this item
     */
    protected $formpage;

    /**
     * $parentid = the item this item depends from
     */
    protected $parentid;

    /**
     * $parentvalue = the answer the parent item has to have in order to show this item as child
     */
    protected $parentvalue;

    /**
     * $savefeedbackmask
     */
    protected $savefeedbackmask;

    /**
     * $editorlist
     */
    protected $editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA);

    /**
     * $savepositiontodb: can this plugin save, as user answer, the position of the user interface elements in the form?
     */
    protected $savepositiontodb = null;

    /**
     * $insetupform = list of fields properties the surveypro creator will have in the item definition form
     * By default each field property is present in the form
     * so, in each child class, I only need to "deactivate" field property (mform element) I don't want to have
     */
    protected $insetupform = array(
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
     * Class constructor
     */
    public function __construct($cm, $itemid, $evaluateparentcontent) {
        $this->cm = $cm;
        // $this->context = context_module::instance($cm->id);
    }

    /**
     * item_load
     *
     * @param integer $itemid
     * @param boolean $evaluateparentcontent: include among item elements the 'parentcontent' too
     * @return
     */
    protected function item_load($itemid, $evaluateparentcontent) {
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
            unset($this->id); // I do not care it. I already heave: itemid and pluginid.
            $this->itemname = SURVEYPRO_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            if ($evaluateparentcontent && $this->parentid) {
                $parentitem = surveypro_get_item($this->cm, $this->parentid);
                $this->parentcontent = $parentitem->parent_decode_child_parentvalue($this->parentvalue);
            }
        } else {
            $message = 'I can not find surveypro item ID = '.$itemid;
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * item_force_coherence
     * verify the validity of contents of the record
     * for instance: age not greater than maximumage
     *
     * @param stdClass $record
     * @return stdClass $record
     */
    public function item_force_coherence($record) {
        // Nothing to do here.
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
    protected function item_get_common_settings($record) {
        // You are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page.
        surveypro_reset_items_pages($this->cm->instance);

        $timenow = time();

        // Surveyproid.
        $record->surveyproid = $this->cm->instance;

        // Plugin and type are already onboard.

        // Checkboxes content.
        $checkboxessettings = array('hidden', 'insearchform', 'advanced', 'hideinstructions', 'required');
        foreach ($checkboxessettings as $checkboxessetting) {
            if ($this->insetupform[$checkboxessetting]) {
                $record->{$checkboxessetting} = isset($record->{$checkboxessetting}) ? 1 : 0;
            } else {
                $record->{$checkboxessetting} = 0;
            }
        }

        // Parentid is already onboard.

        // Timecreated, timemodified.
        if (empty($record->itemid)) {
            $record->timecreated = $timenow;
        }
        $record->timemodified = $timenow;

        // Cleanup section.

        // Truncate extranote if longer than maximum allowed (255 characters).
        if (isset($record->extranote) && (strlen($record->extranote) > 255)) {
            $record->extranote = substr($record->extranote, 0, 255);
        }

        // Surveypro can be multilang
        // so I can not save labels to parentvalue as they may change.
        // Because of this, even if the user writes, for instance, "bread\nmilk" to parentvalue
        // I have to encode it to key(bread);key(milk).
        if (isset($record->parentid) && $record->parentid) {
            $parentitem = surveypro_get_item($this->cm, $record->parentid);
            $record->parentvalue = $parentitem->parent_encode_child_parentcontent($record->parentcontent);
            unset($record->parentcontent);
        }
    }

    /**
     * item_save
     * Executes surveyproitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     *
     * Here is the explanation of $this->savefeedbackmask
     * $this->savefeedbackmask
     *   +--- children inherited limited access
     *   |       +--- parents were made available for all
     *   |       |       +--- children were hided because this item was hided
     *   |       |       |       +--- parents were shown because this item was shown
     *   |       |       |       |       +--- new|edit
     *   |       |       |       |       |       +--- success|fail
     * [0|1] - [0|1] - [0|1] - [0|1] - [0|1] - [0|1]
     * Last digit (on the right, of course) == 1 means that the process was globally successfull.
     * Last digit (on the right, of course) == 0 means that the process was globally NOT successfull.
     *
     * Beforelast digit == 0 means NEW.
     * Beforelast digit == 1 means EDIT.
     *
     * (digit in place 2) == 1 means items were shown because this (as child) was shown
     * (digit in place 3) == 1 means items were hided because this (as parent) was hided
     * (digit in place 4) == 1 means items reamin as they are because unlimiting parent does not force any change to children
     * (digit in place 5) == 1 means items inherited limited access because this (as parent) got a limited access
     *
     * @param stdClass $record
     * @return
     */
    public function item_save($record) {
        global $DB;

        $context = context_module::instance($this->cm->id);

        $tablename = 'surveypro'.$this->type.'_'.$this->plugin;
        $this->savefeedbackmask = SURVEYPRO_NOFEEDBACK;

        // Does this record need to be saved as new record or as un update on a preexisting record?
        if (empty($record->itemid)) {
            // Item is new.

            // Sortindex.
            $sql = 'SELECT COUNT(\'x\')
                    FROM {surveypro_item}
                    WHERE surveyproid = :surveyproid
                        AND sortindex > 0';
            $whereparams = array('surveyproid' => $this->cm->instance);
            $record->sortindex = 1 + $DB->count_records_sql($sql, $whereparams);

            // Itemid.
            try {
                $transaction = $DB->start_delegated_transaction();

                if ($itemid = $DB->insert_record('surveypro_item', $record)) { // <-- first surveypro_item save

                    // $tablename
                    // Before saving to the the plugin table, validate the variable name.
                    $this->item_validate_variablename($record, $itemid);

                    $record->itemid = $itemid;
                    if ($pluginid = $DB->insert_record($tablename, $record)) { // <-- first $tablename save
                        $this->savefeedbackmask += 1; // 0*2^1+1*2^0
                    }
                }

                // Special care to "editors".
                if ($editors = $this->get_editorlist()) {

                    $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                    foreach ($editors as $fieldname => $filearea) {
                        $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $context, 'mod_surveypro', $filearea, $record->itemid);
                        $record->{$fieldname.'format'} = FORMAT_HTML;
                    }

                    // Tablename.
                    $record->id = $pluginid;

                    if (!$DB->update_record($tablename, $record)) { // <-- $tablename update
                        $this->savefeedbackmask -= ($this->savefeedbackmask % 2); // Whatever it was, now it is a fail.
                        // } else {
                        // Leave the previous $this->savefeedbackmask.
                        // If it was a success, leave it as now you got one more success.
                        // If it was a fail, leave it as you can not cover the previous fail.
                    }
                    // Record->content follows standard flow and has already been saved at first save time.
                }

                $transaction->allow_commit();

                // Event: item_created.
                $eventdata = array('context' => $context, 'objectid' => $record->itemid);
                $eventdata['other'] = array('type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_EDITITEM);
                $event = \mod_surveypro\event\item_created::create($eventdata);
                $event->trigger();
            } catch (Exception $e) {
                // Extra cleanup steps.
                $transaction->rollback($e); // Rethrows exception.
            }
        } else {
            // Item already exists.

            // Special care to "editors".
            if ($editors = $this->get_editorlist()) {
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                foreach ($editors as $fieldname => $filearea) {
                    $record = file_postupdate_standard_editor($record, $fieldname, $editoroptions, $context, 'mod_surveypro', $filearea, $record->itemid);
                    $record->{$fieldname.'format'} = FORMAT_HTML;
                }
                // } else {
                // Record->content follows standard flow and will be evaluated in the standard way.
            }

            // Begin of: Hide/unhide part 1.
            $oldhidden = $this->get_hidden(); // Used later.
            // $oldhidden = $DB->get_field('surveypro_item', 'hidden', array('id' => $record->itemid)); // Used later.
            // End of: hide/unhide 1.

            // Begin of: Limit/unlimit access part 1.
            $oldadvanced = $this->get_advanced(); // Used later.
            // End of: limit/unlimit access part 1.

            // Sortindex.
            // Doesn't change at item editing time.

            // Surveypro_item.
            $record->id = $record->itemid;

            try {
                $transaction = $DB->start_delegated_transaction();

                if ($DB->update_record('surveypro_item', $record)) {
                    // $tablename

                    // Before saving to the the plugin table, I validate the variable name.
                    $this->item_validate_variablename($record, $record->itemid);

                    $record->id = $record->pluginid;
                    if ($DB->update_record($tablename, $record)) {
                        $this->savefeedbackmask += 3; // 1*2^1+1*2^0 alias: editing + success
                    } else {
                        $this->savefeedbackmask += 2; // 1*2^1+0*2^0 alias: editing + fail
                    }
                } else {
                    $this->savefeedbackmask += 2; // 1*2^1+0*2^0 alias: editing + fail
                }

                $transaction->allow_commit();

                $this->item_manage_chains($record->itemid, $oldhidden, $record->hidden, $oldadvanced, $record->advanced);

                // Event: item_modified.
                $eventdata = array('context' => $context, 'objectid' => $record->itemid);
                $eventdata['other'] = array('type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_EDITITEM);
                $event = \mod_surveypro\event\item_modified::create($eventdata);
                $event->trigger();
            } catch (Exception $e) {
                // Extra cleanup steps.
                $transaction->rollback($e); // Rethrows exception.
            }

            // Save process is over.
        }

        // $this->savefeedbackmask is going to be part of $returnurl in layout_itemsetup.php and to be send to layout_manage.php
        return $record->itemid;
    }

    /**
     * item_validate_variablename
     *
     * @param stdobject $record
     * @param integer $itemid
     * @return
     */
    public function item_validate_variablename($record, $itemid) {
        global $DB;

        // If variable does not exist.
        if ($this->type == SURVEYPRO_TYPEFORMAT) {
            return;
        }

        $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$this->plugin;
        $whereparams = array('itemid' => $itemid, 'surveyproid' => (int)$record->surveyproid);
        $sql = 'SELECT COUNT(p.id)
                FROM {'.$tablename.'} p
                    JOIN {surveypro_item} i ON i.id = p.itemid
                WHERE ((p.itemid <> :itemid)
                    AND (i.surveyproid = :surveyproid)
                    AND ('.$DB->sql_length('p.variable').' > 0))';

        // Verify variable was set. If not, set it.
        if (!isset($record->variable) || empty($record->variable)) {
            $plugincount = 1 + $DB->count_records_sql($sql, $whereparams);
            $plugincount = str_pad($plugincount, 3, '0', STR_PAD_LEFT);

            $candidatevariable = $this->plugin.'_'.$plugincount;
        } else {
            $candidatevariable = $record->variable;
        }

        // Verify the given name is unique. If not, change it.
        $sql = 'SELECT p.id, p.variable
                FROM {'.$tablename.'} p
                    JOIN {surveypro_item} i ON i.id = p.itemid
                WHERE ((p.itemid <> :itemid)
                    AND (i.surveyproid = :surveyproid))';
        $whereparams['variable'] = $candidatevariable;

        $i = 0; // If name is duplicate, restart verification from 1.
        $usednames = $DB->get_records_sql_menu($sql, $whereparams);
        while (in_array($candidatevariable, $usednames)) {
            $i++;
            $candidatevariable = $record->plugin.'_'.str_pad($i, 3, '0', STR_PAD_LEFT);
            $whereparams['variable'] = $candidatevariable;
        }

        $record->variable = $candidatevariable;
    }

    /**
     * item_manage_chains
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
    private function item_manage_chains($itemid, $oldhidden, $newhidden, $oldadvanced, $newadvanced) {
        global $DB;

        $context = context_module::instance($this->cm->id);

        // Now hide or unhide (whether needed) chain of ancestors or descendents.
        if ($this->savefeedbackmask & 1) { // Bitwise logic, alias: if the item was successfully saved.
            // Manage ($oldhidden != $newhidden).
            if ($oldhidden != $newhidden) {
                $surveypro = $DB->get_record('surveypro', array('id' => $this->cm->instance), '*', MUST_EXIST);
                $action = ($oldhidden) ? SURVEYPRO_SHOWITEM : SURVEYPRO_HIDEITEM;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $context, $surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);
            }

            // Begin of: Hide/unhide part 2.
            if ( ($oldhidden == 1) && ($newhidden == 0) ) {
                if ($itemlistman->manage_item_show()) {
                    // A chain of parent items has been showed.
                    $this->savefeedbackmask += 4; // 1*2^2.
                }
            }
            if ( ($oldhidden == 0) && ($newhidden == 1) ) {
                if ($itemlistman->manage_item_hide()) {
                    // A chain of child items has been hided.
                    $this->savefeedbackmask += 8; // 1*2^3.
                }
            }
            // End of: hide/unhide part 2.

            // Manage ($oldadvanced != $newadvanced).
            if ($oldadvanced != $newadvanced) {
                $surveypro = $DB->get_record('surveypro', array('id' => $this->cm->instance), '*', MUST_EXIST);
                $action = ($oldadvanced) ? SURVEYPRO_MAKESTANDARD : SURVEYPRO_MAKEADVANCED;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $context, $surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);
            }

            // Begin of: Limit/unlimit access part 2.
            if ( ($oldadvanced == 1) && ($newadvanced == 0) ) {
                if ($itemlistman->manage_item_makestandard()) {
                    // A chain of parent items has been made available for all.
                    $this->savefeedbackmask += 16; // 1*2^4.
                }
            }
            if ( ($oldadvanced == 0) && ($newadvanced == 1) ) {
                if ($itemlistman->manage_item_makeadvanced()) {
                    // A chain of child items got a limited access.
                    $this->savefeedbackmask += 32; // 1*2^5
                }
            }
            // End of: limit/unlimit access part 2.
        }
    }

    /**
     * item_update_childparentvalue
     *
     * @param none
     * @return
     */
    public function item_update_childrenparentvalue() {
        global $DB, $CFG;

        require_once($CFG->dirroot.'/mod/surveypro/'.$this->type.'/'.$this->plugin.'/classes/plugin.class.php');
        $itemclassname = 'mod_surveypro_'.$this->type.'_'.$this->plugin;
        if ($itemclassname::item_get_canbeparent()) {
            // Take care: you can not use $this->item_get_content_array(SURVEYPRO_VALUES, 'options') to evaluate values.
            // Because $item was loaded before last save, so $this->item_get_content_array(SURVEYPRO_VALUES, 'options').
            // Is still returning the previous values.

            $children = $DB->get_records('surveypro_item', array('parentid' => $this->itemid), 'id', 'id, parentvalue');
            foreach ($children as $child) {
                $childparentvalue = $child->parentvalue;

                // Decode $childparentvalue to $childparentcontent.
                $childparentcontent = $this->parent_decode_child_parentvalue($childparentvalue);

                // Encode $childparentcontent to $childparentvalue, once again.
                $child->parentvalue = $this->parent_encode_child_parentcontent($childparentcontent);

                // Save the child.
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
    protected function item_builtin_string_load_support() {
        global $CFG, $DB;

        $surveyproid = $this->get_surveyproid();
        $template = $DB->get_field('surveypro', 'template', array('id' => $surveyproid), MUST_EXIST);
        if (empty($template)) {
            return;
        }

        // Take care: I verify the existence of the english folder even if, maybe, I will ask for the string in a different language.
        if (!file_exists($CFG->dirroot.'/mod/surveypro/template/'.$template.'/lang/en/surveyprotemplate_'.$template.'.php')) {
            // This template does not support multilang.
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
    protected function item_split_unix_time($time, $applyusersettings=false) {
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

        // Print_object($getdate);.
        return $getdate;
    }

    /**
     * item_delete
     *
     * @param integer $itemid
     * @return
     */
    public function item_delete($itemid) {
        global $DB;

        $context = context_module::instance($this->cm->id);

        try {
            $transaction = $DB->start_delegated_transaction();

            if (!$DB->delete_records('surveypro_item', array('id' => $itemid))) {
                print_error('notdeleted_item', 'mod_surveypro', null, $itemid);
            }

            if (!$DB->delete_records('surveypro'.$this->type.'_'.$this->plugin, array('itemid' => $itemid))) {
                $a = new stdClass();
                $a->pluginid = $this->pluginid;
                $a->type = $this->type;
                $a->plugin = $this->plugin;
                print_error('notdeleted_plugin', 'mod_surveypro', null, $a);
            }

            surveypro_reset_items_pages($this->cm->instance);

            // Delete records from surveypro_answer.
            // If, at the end, the related surveypro_submission has no data, then, delete it too.
            if (!$DB->delete_records('surveypro_answer', array('itemid' => $itemid))) {
                print_error('notdeleted_userdata', 'mod_surveypro', null, $itemid);
            }

            $emptysubmissions = 'SELECT c.id
                                 FROM {surveypro_submission} c
                                     LEFT JOIN {surveypro_answer} d ON c.id = d.submissionid
                                 WHERE (d.id IS null)';
            if ($surveyprotodelete = $DB->get_records_sql($emptysubmissions)) {
                $surveyprotodelete = array_keys($surveyprotodelete);
                if (!$DB->delete_records_select('surveypro_submission', 'id IN ('.implode(',', $surveyprotodelete).')')) {
                    $a = implode(',', $surveyprotodelete);
                    print_error('notdeleted_submission', 'mod_surveypro', null, $a);
                }
            }

            $transaction->allow_commit();

            $eventdata = array('context' => $context, 'objectid' => $this->cm->instance);
            $eventdata['other'] = array('plugin' => $this->plugin);
            $event = \mod_surveypro\event\item_deleted::create($eventdata);
            $event->trigger();
        } catch (Exception $e) {
            // Extra cleanup steps.
            $transaction->rollback($e); // Rethrows exception.
        }
    }

    /**
     * item_uses_form_page
     *
     * @param none
     * @return: boolean
     */
    public function item_uses_form_page() {
        return true;
    }

    /**
     * item_left_position_allowed
     *
     * @param none
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
     * @param none
     * @return
     */
    public function item_set_editor() {
        if (!$editors = $this->get_editorlist()) {
            return;
        }

        // Some examples.
        // Each SURVEYPRO_ITEMFIELD has: $this->insetupform['content'] == true  and $editors == array('content').
        // Fieldset                 has: $this->insetupform['content'] == true  and $editors == null.
        // Pagebreak                has: $this->insetupform['content'] == false and $editors == null.
        $context = context_module::instance($this->cm->id);
        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $context);
        foreach ($editors as $fieldname => $filearea) {
            $this->{$fieldname.'format'} = FORMAT_HTML;
            $this->{$fieldname.'trust'} = 1;
            file_prepare_standard_editor($this, $fieldname, $editoroptions, $context, 'mod_surveypro', $filearea, $this->itemid);
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
            if (preg_match('~^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$~', $option, $match)) {
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
     * @return none
     */
    protected function item_clean_textarea_fields($record, $fieldlist) {
        foreach ($fieldlist as $field) {
            // Some item may be undefined causing:
            // Notice: Undefined property: stdClass::$defaultvalue
            // As, for instance, disabled $defaultvalue field when $delaultoption == invite.
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
     * @param none
     * @return $value
     * @return $label
     */
    protected function item_get_other() {
        if (preg_match('~^(.*)'.SURVEYPRO_OTHERSEPARATOR.'(.*)$~', $this->labelother, $match)) { // Do not warn: it can never be equal to zero.
            $label = trim($match[1]);
            $value = trim($match[2]);
        } else {
            $label = trim($this->labelother);
            $value = '';
        }

        return array($value, $label);
    }

    /**
     * item_mandatory_is_allowed
     * this method defines if an item can be switched to mandatory or not.
     *
     * A mandatory field is allowed ONLY if:
     *     -> !isset($this->defaultoption)
     *     -> $this->defaultoption != SURVEYPRO_NOANSWERDEFAULT
     *
     * @param none
     * @return boolean
     */
    public function item_mandatory_is_allowed() {
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
     * @param none
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

        // $schema .= '                <xs:element type="xs:int" name="sortindex"/>'."\n";
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
     * @return none
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

    /**
     * get_can_be_mandatory
     *
     * @param none
     * @return whether the item of this plugin can be mandatory
     */
    public static function item_get_can_be_mandatory() {
        return true;
    }

    /**
     * item_get_pdf_template
     *
     * @param none
     * @return the template to use at response report creation
     */
    public static function item_get_pdf_template() {
        return SURVEYPRO_3COLUMNSTEMPLATE;
    }

    // MARK get

    /**
     * get_cm
     *
     * @param none
     * @return the content of the $cm property
     */
    public function get_cm() {
        return $this->cm;
    }

    /**
     * get_context
     *
     * @param none
     * @return the content of the $context property
     */
    // public function get_context() {
    //     return $this->context;
    // }

    /**
     * get_editorlist
     *
     * @param none
     * @return the content of the $editorlist property
     */
    public function get_editorlist() {
        return $this->editorlist;
    }

    /**
     * get_savepositiontodb
     *
     * @param none
     * @return the content of the $savepositiontodb property
     */
    public function get_savepositiontodb() {
        return $this->savepositiontodb;
    }

    /**
     * get_itemform_preset
     *
     * @param $itemformelement
     * @return the content of the corresponding element of $this->insetupform
     */
    public function get_itemform_preset() {
        $data = get_object_vars($this);

        // Just to save few nanoseconds.
        unset($data['cm']);
        // unset($data['context']);
        unset($data['insetupform']);

        return $data;
    }

    /**
     * get_insetupform
     *
     * @param $itemformelement
     * @return the content of the corresponding element of $this->insetupform
     */
    public function get_insetupform($itemformelement) {
        return $this->insetupform[$itemformelement];
    }

    /**
     * get_itemid
     *
     * @param none
     * @return the content of the $itemid property
     */
    public function get_itemid() {
        if (isset($this->itemid)) {
            return $this->itemid;
        } else {
            return 0;
        }
    }

    /**
     * get_type
     *
     * @param none
     * @return the content of the $type property
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * get_plugin
     *
     * @param none
     * @return the content of the $plugin property
     */
    public function get_plugin() {
        return $this->plugin;
    }

    /**
     * get_content
     *
     * @param none
     * @return the content of the $content property
     */
    public function get_content() {
        $context = context_module::instance($this->cm->id);

        return file_rewrite_pluginfile_urls($this->content, 'pluginfile.php', $context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $this->itemid);
    }

    /**
     * get_contentformat
     *
     * @param none
     * @return the content of the $contentformat property
     */
    public function get_contentformat() {
        return $this->contentformat;
    }

    /**
     * get_surveyproid
     *
     * @param none
     * @return the content of the $surveyproid property
     */
    public function get_surveyproid() {
        return $this->cm->instance;
    }

    /**
     * get_pluginid
     *
     * @param none
     * @return the content of the $pluginid property
     */
    public function get_pluginid() {
        if (isset($this->pluginid)) {
            return $this->pluginid;
        } else {
            return 0;
        }
    }

    /**
     * get_itemname
     *
     * @param none
     * @return the content of the $itemname property
     */
    public function get_itemname() {
        return $this->itemname;
    }

    /**
     * get_hidden
     *
     * @param none
     * @return the content of the $hidden property
     */
    public function get_hidden() {
        return $this->hidden;
    }

    /**
     * get_insearchform
     *
     * @param none
     * @return the content of the $insearchform property
     */
    public function get_insearchform() {
        return $this->insearchform;
    }

    /**
     * get_advanced
     *
     * @param none
     * @return the content of the $advanced property
     */
    public function get_advanced() {
        return $this->advanced;
    }

    /**
     * get_sortindex
     *
     * @param none
     * @return the content of the $sortindex property
     */
    public function get_sortindex() {
        return $this->sortindex;
    }

    /**
     * get_formpage
     *
     * @param none
     * @return the content of the $formpage property
     */
    public function get_formpage() {
        return $this->formpage;
    }

    /**
     * get_parentid
     *
     * @param none
     * @return the content of the $parentid property
     */
    public function get_parentid() {
        return $this->parentid;
    }

    /**
     * get_parentcontent
     *
     * @param string $separator: the required separator
     * @return the content of the $parentcontent property, properly separated
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
     * @param none
     * @return the content of the $parentvalue property
     */
    public function get_parentvalue() {
        return $this->parentvalue;
    }

    /**
     * get_variable
     *
     * @param none
     * @return the content of the $variable property
     */
    public function get_variable() {
        return $this->variable;
    }

    /**
     * get_customnumber
     *
     * @param none
     * @return the content of the $customnumber property whether defined
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
     * @param none
     * @return bool: the content of the $required property whether defined
     */
    public function get_required() {
        // It may not be set as in page_break, autofill or some more.
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
     * @param none
     * @return the content of the $indent property whether defined
     */
    public function get_indent() {
        if (isset($this->indent)) {
            return $this->indent;
        } else {
            return 0;
        }
    }

    /**
     * get_hideinstructions
     *
     * @param none
     * @return the content of the $hideinstructions property whether defined
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
     * @param none
     * @return the content of the $position property whether defined
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
     * @param none
     * @return the content of the $extranote property whether defined
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
     * @param none
     * @return the content of the $downloadformat property whether defined
     */
    public function get_downloadformat() {
        if (isset($this->downloadformat)) {
            return $this->downloadformat;
        } else {
            return false;
        }
    }

    /**
     * get_savefeedbackmask
     *
     * @param none
     * @return the content of the $savefeedbackmask property whether defined
     */
    public function get_savefeedbackmask() {
        return $this->savefeedbackmask;
    }


    // MARK set

    /**
     * set_contentformat
     *
     * @param string $contentformat
     * @return none
     */
    public function set_contentformat($contentformat) {
        $this->contentformat = $contentformat;
    }

    /**
     * set_contenttrust
     *
     * @param string $contenttrust
     * @return none
     */
    public function set_contenttrust($contenttrust) {
        $this->contenttrust = $contenttrust;
    }

    /**
     * set_required
     *
     * @param integer $value; the value to set
     * @return none
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
     * I can not make ANY assumption about $childparentvalue because of the following explanation:
     * At child save time, I encode its $parentcontent to $parentvalue.
     * The encoding is done through a parent method according to parent values.
     * Once the child is saved, I can return to parent and I can change it as much as I want.
     * For instance by changing the number and the content of its options.
     * At parent save time, the child parentvalue is rewritten
     * -> but it may result in a too short or too long list of keys
     * -> or with a wrong number of unrecognized keys
     * Because of this, I need to...
     * ...implement all possible checks to avoid crashes/malfunctions during code execution.
     *
     * @param $childvalue
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // Read introduction.
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
        $config = get_config('mod_surveypro');

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
     * @param none
     * @return empty string
     */
    protected function userform_get_filling_instructions() {
        return '';
    }

    /**
     * userform_child_item_allowed_static
     * this method is called if (and only if) parent item and child item DON'T live in the same form page
     * this method has two purposes:
     * - skip the item from the current page of $userpageform
     * - get if a page has items
     *
     * as parentitem declare whether my child item is allowed to in the page that is going to be displayed
     *
     * @param int $submissionid:
     * @param array $childitemrecord:
     * @return $status: true: the item is allowed; false: the item must be dropped out
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
     * userform_add_disabledif
     * this function is used ONLY if $surveypro->newpageforchild == false
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is calculated and is requested to provide the disabledif conditions to disable its child item
     *
     * @param $mform
     * @param $canaccessadvanceditems
     * @return
     */
    public function userform_add_disabledif($mform, $canaccessadvanceditems) {
        global $DB;

        if (!$this->parentid || ($this->type == SURVEYPRO_TYPEFORMAT)) {
            return;
        }

        $fieldnames = $this->userform_get_root_elements_name();

        $parentrestrictions = array();

        // If I am here this means I have a parent FOR SURE.
        // Instead of making one more query, I assign two variables manually.
        // At the beginning, $currentitem is me.
        $currentitem = new stdClass();
        $currentitem->parentid = $this->get_parentid();
        $currentitem->parentvalue = $this->get_parentvalue();
        $mypage = $this->get_formpage(); // Once and forever.
        do {
            // Take care.
            // Even if (!$surveypro->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually.
            // Because of this, I need to chech page numbers.
            $parentitem = $DB->get_record('surveypro_item', array('id' => $currentitem->parentid), 'parentid, parentvalue, formpage');
            $parentpage = $parentitem->formpage;
            if ($parentpage == $mypage) {
                $parentid = $currentitem->parentid;
                $parentvalue = $currentitem->parentvalue;
                $parentrestrictions[$parentid] = $parentvalue; // The element with ID == $parentid requires, as constain, $parentvalue.
            } else {
                // My parent is in a page before mine.
                // No need to investigate more for older ancestors.
                break;
            }

            $currentitem = $parentitem;
        } while (!empty($parentitem->parentid));
        // $parentrecord is an associative array
        // The array key is the ID of the parent item, the corresponding value is the constrain that $this has to be submitted to.

        $displaydebuginfo = false;
        foreach ($parentrestrictions as $parentid => $childparentvalue) {
            $parentitem = surveypro_get_item($this->cm, $parentid);
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

            // Write disabledIf.
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
        if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            return get_string('answerisnoanswer', 'mod_surveypro');
        }
        if ($content === null) { // Item was disabled.
            return get_string('notanswereditem', 'mod_surveypro');
        }

        return $content;
    }
}
