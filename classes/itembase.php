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
 * Surveypro itembase class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The base class for items
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_itembase {

    /**
     * @var object Course module object
     */
    protected $cm;

    /**
     * @var object Context object
     */
    protected $context;

    /**
     * @var object $surveypro
     */
    public $surveypro;

    /**
     * @var int Unique itemid of the surveyproitem in surveypro_item table
     */
    protected $itemid;

    /**
     * @var string Type of the item. It can only be: SURVEYPRO_TYPEFIELD or SURVEYPRO_TYPEFORMAT
     */
    protected $type;

    /**
     * @var string Item plugin
     */
    protected $plugin;

    /**
     * @var string Basename of the field as it is in the out form
     */
    protected $itemname;

    /**
     * @var bool Visibility of this item in the out form
     */
    protected $hidden;

    /**
     * @var bool Membership of the item to the search form
     */
    protected $insearchform;

    /**
     * @var bool Availability of this item: to everyone or to user with "accessreserveditems" capability only
     */
    protected $reserved;

    /**
     * @var int Positiom of this item in the surveypro form
     */
    protected $sortindex;

    /**
     * @var int Page where this item will be located
     */
    protected $formpage;

    /**
     * @var int Id of the parent item
     */
    protected $parentid;

    /**
     * @var string Answer the parent item has to have in order to show this item as child
     */
    protected $parentvalue;

    /**
     * @var int Feedback mask for the user to define the feedback once the item is edited
     */
    protected $itemeditingfeedback;

    /**
     * @var array
     */
    protected $editorlist = array('content' => SURVEYPRO_ITEMCONTENTFILEAREA);

    /**
     * @var bool Possibility for this plugin to save, as user answer, the position of the user interface elements in the out form
     */
    protected $savepositiontodb = null;

    /**
     * List of fields properties the surveypro creator will manage in the item definition form
     * By default each item property is present in the form
     * so, in each child class, I only need to "deactivate" item property not desired/needed/handled/wanted
     *
     * @var array
     */
    protected $insetupform = array(
        'common_fs' => true,
        'content' => true,
        'customnumber' => true,
        'position' => true,
        'trimonsave' => true,
        'extranote' => true,
        'hideinstructions' => true,
        'required' => true,
        'variable' => true,
        'indent' => true,
        'hidden' => true,
        'reserved' => true,
        'insearchform' => true,
        'parentid' => true
    );

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $surveypro
     * @param int $itemid
     * @param bool $getparentcontent True to include $item->parentcontent (as decoded by the parent item) too, false otherwise
     */
    public function __construct($cm, $surveypro, $itemid, $getparentcontent) {
        $this->cm = $cm;
        $this->surveypro = $surveypro;
    }

    /**
     * Item load.
     *
     * If itemid is provided, load the object (item + base + plugin) from database
     * If evaluateparentcontent is true, load the parentitem parentcontent property too
     *
     * @param integer $itemid
     * @param boolean $getparentcontent To include among item elements the 'parentcontent' too
     * @return void
     */
    protected function item_load($itemid, $getparentcontent) {
        global $DB;

        if (!$itemid) {
            $message = 'Can not load an item without its ID';
            debugging($message, DEBUG_DEVELOPER);
        }

        $sql = 'SELECT *, i.id as itemid, p.id as pluginid
                FROM {surveypro_item} i
                  JOIN {surveypro'.$this->type.'_'.$this->plugin.'} p ON p.itemid = i.id
                WHERE i.id = :itemid';

        if ($record = $DB->get_record_sql($sql, array('itemid' => $itemid))) {
            foreach ($record as $option => $value) {
                $this->{$option} = $value;
            }
            unset($this->id); // I do not care it. I already heave: itemid and pluginid.
            $this->itemname = SURVEYPRO_ITEMPREFIX.'_'.$this->type.'_'.$this->plugin.'_'.$this->itemid;
            if ($getparentcontent && $this->parentid) {
                $parentitem = surveypro_get_item($this->cm, $this->surveypro, $this->parentid);
                $this->parentcontent = $parentitem->parent_decode_child_parentvalue($this->parentvalue);
            }
        } else {
            $message = 'I can not find surveypro item ID = '.$itemid;
            debugging('Error at line '.__LINE__.' of file '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
    }

    /**
     * Verify the validity of contents of the record.
     *
     * for instance: age not greater than maximum age
     *
     * @param stdClass $record
     * @return void
     */
    public function item_force_coherence($record) {
        // Nothing to do here.
    }

    /**
     * Set in $record few elements that are common to each item.
     *
     * Common settings are the setting saved to surveypro_item
     * they are:
     *     id
     *     √ surveyproid
     *     √ type
     *     √ plugin
     *     √ hidden
     *     √ insearchform
     *     √ reserved
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
     * in addition, here I also make cleanup of:
     *     extranote
     *     parentvalue
     *
     * The following settings will be calculated later:
     *     sortindex
     *     formpage
     *
     * @param stdClass $record
     * @return void
     */
    protected function item_get_common_settings($record) {
        // You are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page.
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $utilityman->reset_items_pages();

        $timenow = time();

        // Surveyproid.
        $record->surveyproid = $this->cm->instance;

        // Plugin and type are already onboard.

        // Checkboxes content.
        $checkboxessettings = array('hidden', 'insearchform', 'reserved', 'hideinstructions', 'required', 'trimonsave');
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
        if (!empty($record->parentid)) {
            $parentitem = surveypro_get_item($this->cm, $this->surveypro, $record->parentid);
            $record->parentvalue = $parentitem->parent_encode_child_parentcontent($record->parentcontent);
            unset($record->parentcontent);
        }
    }

    /**
     * Item save
     * Executes surveyproitem_<<plugin>> global level actions
     * this is the save point of the global part of each plugin
     *
     * Here is the explanation of $this->itemeditingfeedback
     * $this->itemeditingfeedback
     *   +--- children inherited reserved access
     *   |     +--- parents were made available for all
     *   |     |     +--- children were hided because this item was hided
     *   |     |     |     +--- parents were shown because this item was shown
     *   |     |     |     |     +--- new|edit
     *   |     |     |     |     |     +--- success|fail
     * [0|1] [0|1] [0|1] [0|1] [0|1] [0|1]
     *
     * (digit 0) == 1 means that the process was globally successfull
     * (digit 0) == 0 means that the process was globally NOT successfull
     *
     * (digit 1) == 0 means NEW
     * (digit 1) == 1 means EDIT
     *
     * (digit 2) == 0: no chain of parent items was shown because this item (as child) was shown
     * (digit 2) == 1: a chain of parent items was shown because this item (as child) was shown
     *
     * (digit 3) == 0: no chain of children items was hided because this item (as parent) was hided
     * (digit 3) == 1: a chain of children items was hided because this item (as parent) was hided
     *
     * (digit 4) == 0: no chain of parents items inherited free access because this item (as child) was changed to free
     * (digit 4) == 1: a chain of parents items inherited free access because this item (as child) was changed to free
     *
     * (digit 5) == 0: no chain of children items inherited reserved access because this item (as parent) was changed to reserved
     * (digit 5) == 1: a chain of children items inherited reserved access because this item (as parent) was changed to reserved
     *
     * @param object $record
     * @return void
     */
    public function item_save($record) {
        global $DB, $COURSE;

        $context = context_module::instance($this->cm->id);

        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $hassubmission = $utilityman->has_submissions(false);

        $tablename = 'surveypro'.$this->type.'_'.$this->plugin;
        $this->itemeditingfeedback = SURVEYPRO_NOFEEDBACK;

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

                if ($itemid = $DB->insert_record('surveypro_item', $record)) { // First surveypro_item save.
                    // Now think to $tablename.

                    // Before saving to the the plugin table, validate the variable name.
                    $this->item_validate_variablename($record, $itemid);

                    $record->itemid = $itemid;
                    if ($pluginid = $DB->insert_record($tablename, $record)) { // First save of $tablename.
                        $this->itemeditingfeedback += 1; // 0*2^1+1*2^0.
                    }
                }

                // Special care to "editors".
                if ($editors = $this->get_editorlist()) {
                    $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                    foreach ($editors as $fieldname => $filearea) {
                        $record = file_postupdate_standard_editor(
                                      $record, $fieldname, $editoroptions,
                                      $context, 'mod_surveypro', $filearea, $record->itemid
                                  );
                        $record->{$fieldname.'format'} = FORMAT_HTML;
                    }

                    // Tablename.
                    $record->id = $pluginid;

                    if (!$DB->update_record($tablename, $record)) { // Update of $tablename.
                        $this->itemeditingfeedback -= ($this->itemeditingfeedback % 2); // Whatever it was, now it is a fail.
                        // Otherwise...
                        // Leave the previous $this->itemeditingfeedback.
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

            if ($hassubmission) {
                // Set to SURVEYPRO_STATUSINPROGRESS each already sent submission.
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $utilityman->submissions_set_status($whereparams, SURVEYPRO_STATUSINPROGRESS);
            }
        } else {
            // Item already exists.

            // Special care to "editors".
            if ($editors = $this->get_editorlist()) {
                $editoroptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context);
                foreach ($editors as $fieldname => $filearea) {
                    $record = file_postupdate_standard_editor(
                                  $record, $fieldname, $editoroptions,
                                  $context, 'mod_surveypro', $filearea, $record->itemid
                              );
                    $record->{$fieldname.'format'} = FORMAT_HTML;
                }
            }

            // Begin of: Hide/unhide part 1.
            $oldhidden = $this->get_hidden(); // Used later.
            // End of: hide/unhide 1.

            // Begin of: Make reserved/free part 1.
            $oldreserved = $this->get_reserved(); // Used later.
            // End of: make reserved/free part 1.

            // Sortindex.
            // Doesn't change at item editing time.

            // Surveypro_item.
            $record->id = $record->itemid;

            try {
                $transaction = $DB->start_delegated_transaction();

                if ($DB->update_record('surveypro_item', $record)) {
                    // Before saving to the the plugin table, I validate the variable name.
                    $this->item_validate_variablename($record, $record->itemid);

                    $record->id = $record->pluginid;
                    if ($DB->update_record($tablename, $record)) {
                        $this->itemeditingfeedback += 3; // 1*2^1+1*2^0 alias: editing + success.
                    } else {
                        $this->itemeditingfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail.
                    }
                } else {
                    $this->itemeditingfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail.
                }

                $transaction->allow_commit();

                $this->item_manage_chains($record->itemid, $oldhidden, $record->hidden, $oldreserved, $record->reserved);

                // Event: item_modified.
                $eventdata = array('context' => $context, 'objectid' => $record->itemid);
                $eventdata['other'] = array('type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_EDITITEM);
                $event = \mod_surveypro\event\item_modified::create($eventdata);
                $event->trigger();
            } catch (Exception $e) {
                // Extra cleanup steps.
                $transaction->rollback($e); // Rethrows exception.
            }

            // If this item WAS NOT mandatory and it IS NOW mandatory
            // then EACH submission (if any)
            // where the answer to this item was SURVEYPRO_NOANSWERVALUE
            // needs to be switched to SURVEYPRO_STATUSINPROGRESS.
            if ($hassubmission) {
                if (isset($this->required)) { // This plugin uses required.
                    $oldrequired = $this->get_required(); // This is the value of required as it was when it was loaded.
                    if ($oldrequired == 0) { // This item was not required.
                        if (isset($record->required) && ($record->required == 1)) { // This item is now required.
                            // This item that was not mandatory is NOW mandatory.
                            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
                            $utilityman->optional_to_required_followup($record->itemid);
                        }
                    }
                }
            }
        }
        // Save process is over.

        if ($hassubmission) {
            // Update completion state.
            $possibleusers = surveypro_get_participants($this->surveypro->id);
            $completion = new completion_info($COURSE);
            if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                foreach ($possibleusers as $user) {
                    $completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->id);
                }
            }
        }

        // Property $this->itemeditingfeedback is going to be part of $returnurl in layout_itemsetup.php
        // and there it will be send to layout_itemlist.php.
        return $record->itemid;
    }

    /**
     * Validate the name of the variable to make sure it is unique.
     *
     * @param stdobject $record
     * @param integer $itemid
     * @return void
     */
    public function item_validate_variablename($record, $itemid) {
        global $DB;

        // If variable does not exist.
        if ($this->type == SURVEYPRO_TYPEFORMAT) {
            return;
        }

        // Verify variable was set. If not, set $userchoosedname and $basename starting from the plugin name.
        if (!isset($record->variable) || empty($record->variable)) {
            $userchoosedname = $this->plugin.'_001';
            $basename = $this->plugin;
        } else {
            $userchoosedname = clean_param($record->variable, PARAM_TEXT);
            if (preg_match('~^(.*)_[0-9]{3}$~', $userchoosedname, $matches)) {
                $basename = $matches[1];
            } else {
                $basename = $userchoosedname;
            }
        }
        $testname = $userchoosedname;

        // Bloody Editing Teachers can create a boolean element, for instance, naming it 'age_001'
        // having an age element named 'age_001' already onboard!
        // Because of this I need to make as much queries as the number of used plugins in my surveypro!

        // Get the list of used plugin.
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $pluginlist = $utilityman->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        $usednames = array();
        foreach ($pluginlist as $plugin) {
            $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $sql = 'SELECT p.itemid, p.variable
                    FROM {surveypro_item} i
                      JOIN {'.$tablename.'} p ON p.itemid = i.id
                    WHERE ((i.surveyproid = :surveyproid)
                      AND (p.itemid <> :itemid))';
            $whereparams = array('surveyproid' => (int)$record->surveyproid, 'itemid' => $itemid);
            $usednames += $DB->get_records_sql_menu($sql, $whereparams);
        }

        // Verify the $userchoosedname name is unique. If not, change it.
        $i = 0; // If the name is a duplicate, concatenate a suffix starting from 1.
        while (in_array($testname, $usednames)) {
            $i++;
            $testname = $basename.'_'.str_pad($i, 3, '0', STR_PAD_LEFT);
        }

        $record->variable = $testname;
    }

    /**
     * Show/Hide chains of descendant/ancestors on the basis of the settings provided in the current editing process
     * Make reserved/standard chains of descendant/ancestors on the basis of the settings provided in the current editing process
     *
     * @param integer $itemid
     * @param boolean $oldhidden
     * @param boolean $newhidden
     * @param boolean $oldreserved
     * @param boolean $newreserved
     * @return void
     */
    private function item_manage_chains($itemid, $oldhidden, $newhidden, $oldreserved, $newreserved) {
        $context = context_module::instance($this->cm->id);

        // Now hide or unhide (whether needed) chain of ancestors or descendents.
        if ($this->itemeditingfeedback & 1) { // Bitwise logic, alias: if the item was successfully saved.

            // Management of ($oldhidden != $newhidden).
            if ($oldhidden != $newhidden) {
                $action = ($oldhidden) ? SURVEYPRO_SHOWITEM : SURVEYPRO_HIDEITEM;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $context, $this->surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);

                // Begin of: Hide/unhide part 2.
                if ( ($oldhidden == 1) && ($newhidden == 0) ) {
                    $itemlistman->item_show_execute();
                    // A chain of parent items was shown.
                    $this->itemeditingfeedback += 4; // 1*2^2.
                }
                if ( ($oldhidden == 0) && ($newhidden == 1) ) {
                    $itemlistman->item_hide_execute();
                    // Chain of children items was hided.
                    $this->itemeditingfeedback += 8; // 1*2^3.
                }
                // End of: hide/unhide part 2.
            }

            // Management of ($oldreserved != $newreserved).
            if ($oldreserved != $newreserved) {
                $action = ($oldreserved) ? SURVEYPRO_MAKEAVAILABLE : SURVEYPRO_MAKERESERVED;

                $itemlistman = new mod_surveypro_itemlist($this->cm, $context, $this->surveypro);
                $itemlistman->set_type($this->type);
                $itemlistman->set_plugin($this->plugin);
                $itemlistman->set_itemid($itemid);
                $itemlistman->set_action($action);
                $itemlistman->set_view(SURVEYPRO_NOVIEW);
                $itemlistman->set_confirm(SURVEYPRO_CONFIRMED_YES);

                // Begin of: Make reserved/free part 2.
                if ( ($oldreserved == 1) && ($newreserved == 0) ) {
                    if ($itemlistman->item_makeavailable_execute()) {
                        // A chain of parents items inherited free access.
                        $this->itemeditingfeedback += 16; // 1*2^4.
                    }
                }
                if ( ($oldreserved == 0) && ($newreserved == 1) ) {
                    if ($itemlistman->item_makereserved_execute()) {
                        // A chain of children items inherited reserved access.
                        $this->itemeditingfeedback += 32; // 1*2^5.
                    }
                }
                // End of: make reserved/free part 2.
            }
        }
    }

    /**
     * redefine the parentvalue of children items according to the new parameters of the just saved parent item.
     *
     * for instance: if I removed an item from the parent item
     * while some children were using that item as condition to appear,
     * I drop that item from the parentvalue of that children
     *
     * @return void
     */
    public function item_update_childrenparentvalue() {
        global $DB, $CFG;

        $classname = 'surveypro'.$this->type.'_'.$this->plugin.'_'.$this->type;
        if ($classname::item_get_canbeparent()) {
            // Take care: you can not use $this->item_get_content_array(SURVEYPRO_VALUES, 'options') to evaluate values
            // because $item was loaded before last save, so $this->item_get_content_array(SURVEYPRO_VALUES, 'options')
            // will still return the previous values.

            $childrenitems = $DB->get_records('surveypro_item', array('parentid' => $this->itemid), 'id', 'id, parentvalue');
            foreach ($childrenitems as $childitem) {
                $childparentvalue = $childitem->parentvalue;

                // Decode $childparentvalue to $childparentcontent.
                $childparentcontent = $this->parent_decode_child_parentvalue($childparentvalue);

                // Encode $childparentcontent to $childparentvalue, once again.
                $childitem->parentvalue = $this->parent_encode_child_parentcontent($childparentcontent);

                // Save the child.
                $DB->update_record('surveypro_item', $childitem);
            }
        }
    }

    /**
     * This function is used to populate empty strings according to the user language.
     *
     * @return void
     */
    protected function item_builtin_string_load_support() {
        global $CFG, $DB;

        $surveyproid = $this->get_surveyproid();
        $template = $DB->get_field('surveypro', 'template', array('id' => $surveyproid), MUST_EXIST);
        if (empty($template)) {
            return;
        }

        // Take care: I verify the existence of the english folder even if, maybe, I will ask for strings in a different language.
        if (!file_exists($CFG->dirroot.'/mod/surveypro/template/'.$template.'/lang/en/surveyprotemplate_'.$template.'.php')) {
            // This template does not support multilang.
            return;
        }

        if ($multilangfields = $this->item_get_multilang_fields()) { // Pagebreak and fieldsetend have no multilang_fields.
            foreach ($multilangfields as $plugin) {
                foreach ($plugin as $fieldname) {
                    // Backward compatibility.
                    // In the frame of https://github.com/kordan/moodle-mod_surveypro/pull/447 few multilang fields were added.
                    // This was really a mandatory addition but,
                    // opening surveypros created (from mastertemplates) before this addition,
                    // I may find that they don't have new added fields filled in the database
                    // so the corresponding property $this->{$fieldname} does not exist.
                    if (isset($this->{$fieldname})) {
                        $stringkey = $this->{$fieldname};
                        $this->{$fieldname} = get_string($stringkey, 'surveyprotemplate_'.$template);
                    } else {
                        $this->{$fieldname} = '';
                    }
                }
            }
        }
    }

    /**
     * Item split unix time.
     *
     * @param integer $time
     * @param boolean $applyusersettings
     * @return void
     */
    protected static function item_split_unix_time($time, $applyusersettings=false) {
        if ($applyusersettings) {
            $datestring = userdate($time, '%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', 0);
        } else {
            $datestring = gmstrftime('%B_%A_%j_%Y_%m_%w_%d_%H_%M_%S', $time);
        }
        // May_Tuesday_193_2012_07_3_11_16_03_59.

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

        return $getdate;
    }

    /**
     * Does this item live into a form page?.
     *
     * Each item lives into a form page but not the pagebreak
     *
     * @return boolean
     */
    public function item_uses_form_page() {
        return true;
    }

    /**
     * Does this item allow the question in left position?.
     *
     * Each item allows the question in left position but not the rate
     *
     * @return boolean
     */
    public function item_left_position_allowed() {
        return true;
    }

    /**
     * Defines presets for the editor field of surveyproitem in itembase_form.php.
     *
     * (copied from moodle20/cohort/edit.php)
     *
     * Some examples:
     * Each SURVEYPRO_ITEMFIELD has: $this->insetupform['content'] == true  and $editors == array('content')
     * Fieldset plugin          has: $this->insetupform['content'] == true  and $editors == null
     * Pagebreak plugin         has: $this->insetupform['content'] == false and $editors == null
     *
     * @return void
     */
    public function item_set_editor() {
        if (!$editors = $this->get_editorlist()) {
            return;
        }

        $context = context_module::instance($this->cm->id);
        $editoroptions = array('trusttext' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $context);
        foreach ($editors as $fieldname => $filearea) {
            $this->{$fieldname.'format'} = FORMAT_HTML;
            $this->{$fieldname.'trust'} = 1;
            file_prepare_standard_editor($this, $fieldname, $editoroptions, $context, 'mod_surveypro', $filearea, $this->itemid);
        }
    }

    /**
     * Get the content of textareas. Get the first or the second part of each row based on $content
     * Each row is written with the format:
     *     value::label
     *     or
     *     label
     *
     * @param string $content Part of the line I want to get (SURVEYPRO_VALUES|SURVEYPRO_LABELS)
     * @param string $field Name of the text area field, source of the multiline text
     * @return array $values
     */
    public function item_get_content_array($content, $field) {
        if (($content != SURVEYPRO_VALUES) && ($content != SURVEYPRO_LABELS)) {
            throw new Exception('Bad parameter passed to item_get_content_array');
        }

        $index = ($content == SURVEYPRO_VALUES) ? 1 : 2;
        $options = surveypro_multilinetext_to_array($this->{$field});

        $values = array();
        foreach ($options as $option) {
            if (preg_match('~^(.*)'.SURVEYPRO_VALUELABELSEPARATOR.'(.*)$~', $option, $match)) {
                $values[] = $match[$index];
            } else {
                $values[] = $option;
            }
        }

        return $values;
    }

    /**
     * clean the content of the field $record->{$field} (remove blank lines, trailing \r).
     *
     * @param object $record Item record
     * @param array $fieldlist List of fields to clean
     * @return void
     */
    protected function item_clean_textarea_fields($record, $fieldlist) {
        foreach ($fieldlist as $field) {
            // Some item may be undefined causing: "Notice: Undefined property: stdClass::$defaultvalue"
            // as, for instance, disabled field when $defaultoption == invite.
            if (isset($record->{$field})) {
                $temparray = surveypro_multilinetext_to_array($record->{$field});
                $record->{$field} = implode("\n", $temparray);
            }
        }
    }

    /**
     * Parse $this->labelother in $value and $label.
     *
     * @return $value
     * @return $label
     */
    protected function item_get_other() {
        if (preg_match('~^(.*)'.SURVEYPRO_OTHERSEPARATOR.'(.*)$~', $this->labelother, $match)) {
            $label = trim($match[1]);
            $value = trim($match[2]);
        } else {
            $label = trim($this->labelother);
            $value = '';
        }

        return array($value, $label);
    }

    /**
     * This method defines if an item can be switched to mandatory or not.
     *
     * Used by mod_surveypro_itemlist->display_items_table() to define the icon to show
     *
     * @return boolean
     */
    public function item_canbemandatory() {
        if (property_exists($this, 'defaultoption')) {
            if ($this->defaultoption == SURVEYPRO_NOANSWERDEFAULT) {
                $return = false;
            } else {
                $return = true;
            }
        } else {
            $return = true;
        }

        return $return;
    }

    /**
     * Add to the item record that is going to be saved, items that can not be omitted with default value
     * They, maybe, will be overwritten
     *
     * @param stdClass $record
     * @return void
     */
    public function item_add_mandatory_base_fields(&$record) {
        $record->hidden = 0;
        $record->insearchform = 0;
        $record->reserved = 0;
        $record->formpage = 0;
        $record->timecreated = time();
    }

    /**
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function item_get_itembase_schema() {
        // Fields: surveyproid, formpage, timecreated and timemodified are not supposed to be part of the file!
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="surveypro_item">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="hidden" type="xs:int"/>
                <xs:element name="insearchform" type="xs:int"/>
                <xs:element name="reserved" type="xs:int"/>
                <xs:element name="parent" minOccurs="0">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="parentid" type="xs:int"/>
                            <xs:element name="parentvalue" type="xs:string"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
            </xs:sequence>
        </xs:complexType>
    </xs:element>
</xs:schema>
EOS;

        return $schema;
    }

    /**
     * Add a dummy row to the mform to preserve colour alternation also for items using more than a single mform element.
     *
     * Credits for this amazing solution to the great Eloy Lafuente! He is a genius.
     * Add a dummy useless row (with height = 0) to the form in order to preserve the color alternation
     * mainly used for rate items, but not only.
     *
     * @param moodleform $mform Form to which add the row
     * @return void
     */
    public function item_add_color_unifier($mform) {
        $mform->addElement('html', '<div class="hidden fitem fitem_fgroup colorunifier"></div>');
    }

    /**
     * Get the requested property.
     *
     * @param string $field
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
     * Uses mandatory database field?
     *
     * Each item uses teh "mandatory" database field but not the autofill
     *
     * @return whether the item uses the "mandatory" database field
     */
    public static function item_uses_mandatory_dbfield() {
        return true;
    }

    /**
     * Assign the template that better match the structure of the item to optimally display it into the PDF.
     *
     * @return the template to use at response report creation
     */
    public static function item_get_pdf_template() {
        return SURVEYPRO_3COLUMNSTEMPLATE;
    }

    /**
     * Was the user input marked as "to trim"?
     *
     * @return if the calling plugin requires a user input trim
     */
    public function item_get_trimonsave() {
        return false;
    }

    /**
     * Returns if the field plugin needs contentformat
     *
     * @return bool
     */
    public static function item_needs_contentformat() {
        return false;
    }

    /**
     * Returns if the item has children
     *
     * @return bool
     */
    public function item_has_children() {
        global $DB;

        $itemid = $this->itemid;
        $childrenitemscount = $DB->count_records('surveypro_item', array('parentid' => $itemid));

        return ($childrenitemscount > 0);
    }

    /**
     * Returns if the item is a child
     *
     * @return bool
     */
    public function item_is_child() {

        if ($this->get_parentid()) {
            $return = true;
        } else {
            $return = false;
        }

        return $return;
    }

    // MARK response.

    /**
     * Report how the sql query does fit for this plugin
     *
     * @param int $itemid
     * @param string $searchrestriction
     * @return the specific where clause for this plugin
     */
    public static function response_get_whereclause($itemid, $searchrestriction) {
        $whereclause = 'a.content = :content_'.$itemid;
        $whereparam = $searchrestriction;

        return array($whereclause, $whereparam);
    }

    // MARK get.

    /**
     * Get course module.
     *
     * @return the content of $cm property
     */
    public function get_cm() {
        return $this->cm;
    }

    /**
     * Get surveyproid.
     *
     * @return the content of $surveyproid property
     */
    public function get_surveyproid() {
        return $this->cm->instance;
    }

    /**
     * Get editorlist.
     *
     * @return the content of $editorlist property
     */
    public function get_editorlist() {
        return $this->editorlist;
    }

    /**
     * Get savepositiontodb.
     *
     * @return the content of $savepositiontodb property
     */
    public function get_savepositiontodb() {
        return $this->savepositiontodb;
    }

    /**
     * Get the preset for the item setup form.
     *
     * @return array $data
     */
    public function get_itemform_preset() {
        if (!empty($this->itemid)) {
            $data = get_object_vars($this);

            // Just to save few nanoseconds.
            unset($data['cm']);
            unset($data['surveypro']);
            unset($data['insetupform']);
        } else {
            $data = array();
            $data['type'] = $this->type;
            $data['plugin'] = $this->plugin;
        }

        return $data;
    }

    /**
     * Get if the mform element corresponding to the propery $itemformelement has to be shown in the form.
     *
     * @param string $itemformelement
     * @return true if the corresponding element has to be shown in the form; false otherwise
     */
    public function get_insetupform($itemformelement) {
        return $this->insetupform[$itemformelement];
    }

    /**
     * Get item id.
     *
     * @return the content of $itemid property
     */
    public function get_itemid() {
        if (isset($this->itemid)) {
            return $this->itemid;
        } else {
            return 0;
        }
    }

    /**
     * Get type.
     *
     * @return the content of $type property
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get plugin.
     *
     * @return the content of $plugin property
     */
    public function get_plugin() {
        return $this->plugin;
    }

    /**
     * Get content.
     *
     * @return the content of $content property
     */
    public function get_content() {
        $context = context_module::instance($this->cm->id);

        return file_rewrite_pluginfile_urls(
                   $this->content, 'pluginfile.php', $context->id,
                   'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $this->itemid);
    }

    /**
     * Get content format.
     *
     * @return the content of $contentformat property
     */
    public function get_contentformat() {
        return $this->contentformat;
    }

    /**
     * Get plugin id.
     *
     * @return the content of $pluginid property
     */
    public function get_pluginid() {
        if (isset($this->pluginid)) {
            return $this->pluginid;
        } else {
            return 0;
        }
    }

    /**
     * Get item name.
     *
     * @return the content of $itemname property
     */
    public function get_itemname() {
        return $this->itemname;
    }

    /**
     * Get hidden.
     *
     * @return the content of $hidden property
     */
    public function get_hidden() {
        return $this->hidden;
    }

    /**
     * Get in search form.
     *
     * @return the content of $insearchform property
     */
    public function get_insearchform() {
        return $this->insearchform;
    }

    /**
     * Get reserved.
     *
     * @return the content of $reserved property
     */
    public function get_reserved() {
        return $this->reserved;
    }

    /**
     * Get sortindex.
     *
     * @return the content of $sortindex property
     */
    public function get_sortindex() {
        return $this->sortindex;
    }

    /**
     * Get form page.
     *
     * @return the content of $formpage property
     */
    public function get_formpage() {
        return $this->formpage;
    }

    /**
     * Get parent id.
     *
     * @return the content of $parentid property
     */
    public function get_usesoptionother() {
        if (property_exists($this, 'labelother')) {
            return !empty($this->labelother);
        } else {
            return false;
        }
    }

    /**
     * Get parent id.
     *
     * @return the content of $parentid property
     */
    public function get_parentid() {
        return $this->parentid;
    }

    /**
     * Get parent content.
     *
     * @param string $separator Required separator
     * @return the content of $parentcontent property, properly separated
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
     * Get parentvalue.
     *
     * @return the content of $parentvalue property
     */
    public function get_parentvalue() {
        return $this->parentvalue;
    }

    /**
     * Get variable.
     *
     * @return the content of $variable property
     */
    public function get_variable() {
        return $this->variable;
    }

    /**
     * Get customnumber.
     *
     * @return the content of $customnumber property whether defined
     */
    public function get_customnumber() {
        if (isset($this->customnumber)) {
            return $this->customnumber;
        } else {
            return false;
        }
    }

    /**
     * Get required.
     *
     * @return bool false if the property is not set for the class (like, for instance, for autofill or label)
     *         int  0|1 acording to the property
     */
    public function get_required() {
        // It may be not set as in page_break, autofill or some more.
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
     * Get indent.
     *
     * @return the content of $indent property whether defined
     */
    public function get_indent() {
        if (property_exists($this, 'indent')) { // It may not exist as in page_break, fieldset and some more.
            return $this->indent;
        } else {
            return false;
        }
    }

    /**
     * Get hideinstructions.
     *
     * @return the content of $hideinstructions property whether defined
     */
    public function get_hideinstructions() {
        if (isset($this->hideinstructions)) {
            return $this->hideinstructions;
        } else {
            return false;
        }
    }

    /**
     * Get position.
     *
     * @return the content of $position property whether defined
     */
    public function get_position() {
        if (isset($this->position)) {
            return $this->position;
        } else {
            return false;
        }
    }

    /**
     * Get extranote.
     *
     * @return the content of $extranote property whether defined
     */
    public function get_extranote() {
        if (isset($this->extranote)) {
            return $this->extranote;
        } else {
            return false;
        }
    }

    /**
     * Get downloadformat.
     *
     * @return the content of $downloadformat property whether defined
     */
    public function get_downloadformat() {
        if (isset($this->downloadformat)) {
            return $this->downloadformat;
        } else {
            return false;
        }
    }

    /**
     * Get itemeditingfeedback.
     *
     * @return the content of $itemeditingfeedback property whether defined
     */
    public function get_itemeditingfeedback() {
        return $this->itemeditingfeedback;
    }

    // MARK set.

    /**
     * Set contentformat.
     *
     * @param string $contentformat
     * @return void
     */
    public function set_contentformat($contentformat) {
        $this->contentformat = $contentformat;
    }

    /**
     * Set contenttrust.
     *
     * @param string $contenttrust
     * @return void
     */
    public function set_contenttrust($contenttrust) {
        $this->contenttrust = $contenttrust;
    }

    /**
     * Set variable.
     *
     * @param string $variable
     * @return the content of $variable property
     */
    public function set_variable($variable) {
        $variable = format_string($variable);
        $this->variable = $variable;
    }

    // MARK parent.

    /**
     * I can not make ANY assumption about $childparentvalue because of the following explanation:
     * At child item save time, I encode its $parentcontent to $parentvalue
     * The encoding is done through a parent method according to parent values
     * Once the child is saved, I can return to parent and I can change it as much as I want
     * like, for instance, the number and the content of its options
     * At parent save time, the child parentvalue is rewritten
     * -> but it may result in a too short or too long list of keys
     * -> or with a wrong number of unrecognized keys
     * Because of this, I need to..
     * ...implement all possible checks to avoid crashes/malfunctions during code execution
     *
     * @param string $childparentvalue
     * @return status of child relation
     */
    public function parent_validate_child_constraints($childparentvalue) {
        // Read introduction.
    }

    // MARK userform.

    /**
     * Get full info == extranote + fillinginstruction
     * provides extra info THAT IS NOT SAVED IN THE DATABASE but is shown in the Add/Search form
     *
     * @param boolean $searchform
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
                if (isset($this->extranote)) {
                    $extranote = strip_tags($this->extranote);
                }
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
     * Provides extra fillinginstruction THAT IS NOT SAVED IN THE DATABASE but is shown in the "Add"/"Search" form.
     *
     * if this method is not handled at plugin level,
     * it means it is supposed to return an empty fillinginstruction
     *
     * @return empty string
     */
    protected function userform_get_filling_instructions() {
        return '';
    }

    /**
     * This method is called if (and only if) parent item and child item DON'T live in the same form page
     * this method has two purposes:
     * - skip the item from the current page of $userpageform
     * - get if a page has items
     *
     * as parentitem declare whether my child item is allowed to in the page that is going to be displayed
     *
     * @param int $submissionid
     * @param array $childitemrecord
     * @return true if the item is allowed; false if the item must be dropped out
     */
    public function userform_is_child_allowed_static($submissionid, $childitemrecord) {
        global $DB;

        if (!isset($childitemrecord->parentid)) {
            $message = 'Unexpected $childitemrecord->parentid not set';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        if (!isset($childitemrecord->parentvalue)) {
            $message = 'Unexpected $childitemrecord->parentvalue not set';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        if (!$childitemrecord->parentid) {
            return true;
        }

        $where = array('submissionid' => $submissionid, 'itemid' => $this->itemid);
        $givenanswer = $DB->get_field('surveypro_answer', 'content', $where);

        return ($givenanswer === $childitemrecord->parentvalue);
    }

    /**
     * This function is used ONLY if $surveypro->newpageforchild == false
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is loaded and is requested to provide the disabledif conditions for its child
     *
     * @param moodleform $mform
     * @return void
     */
    public function userform_add_disabledif($mform) {
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
            // Take care!
            // Even if (!$surveypro->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually.
            // Because of this, I need to chech page numbers.
            $where = array('id' => $currentitem->parentid);
            $parentitem = $DB->get_record('surveypro_item', $where, 'parentid, parentvalue, formpage');
            $parentpage = $parentitem->formpage;
            if ($parentpage == $mypage) {
                $parentid = $currentitem->parentid;
                $parentrestrictions[$parentid] = $currentitem->parentvalue;
            } else {
                // My parent is in a page before mine.
                // No need to investigate more for older ancestors.
                break;
            }

            $currentitem = $parentitem;
        } while (!empty($parentitem->parentid));
        // Array $parentrestrictions is an associative array.
        // The array key is the the parent item ID, the corresponding value is the constrain that the parent imposes to the child.

        $displaydebuginfo = false;
        foreach ($parentrestrictions as $parentid => $childparentvalue) {
            $parentitem = surveypro_get_item($this->cm, $this->surveypro, $parentid);
            $disabilitationinfo = $parentitem->userform_get_parent_disabilitation_info($childparentvalue);

            if ($displaydebuginfo) {
                foreach ($disabilitationinfo as $parentinfo) {
                    if (is_array($parentinfo->content)) {
                        $contentdisplayed = 'array('.implode(',', $parentinfo->content).')';
                    } else {
                        $contentdisplayed = '\''.$parentinfo->content.'\'';
                    }
                    foreach ($fieldnames as $fieldname) {
                        echo '<span style="color:green;">';
                        echo '$mform->disabledIf(\''.$fieldname.'\', ';
                        echo '\''.$parentinfo->parentname.'\', ';
                        if (isset($parentinfo->operator)) {
                            echo '\''.$parentinfo->operator.'\', ';
                        }
                        echo $contentdisplayed.');';
                        echo '</span><br />';
                    }
                }
            }

            // Add disabledIf.
            foreach ($disabilitationinfo as $parentinfo) {
                foreach ($fieldnames as $fieldname) {
                    if (isset($parentinfo->operator)) {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->operator, $parentinfo->content);
                    } else {
                        $mform->disabledIf($fieldname, $parentinfo->parentname, $parentinfo->content);
                    }
                }
            }
        }
    }

    /**
     * Manage standard content used by all items.
     *
     * @param string $content
     * @return string - the string for the export file
     */
    public static function userform_standardcontent_to_string($content) {
        $quickresponse = null;

        // The content of the provided answer.
        if (!isset($content)) { // Item was disabled.
            $quickresponse = get_string('answernotsubmitted', 'mod_surveypro');
        } else if ($content == SURVEYPRO_NOANSWERVALUE) { // Answer was "no answer".
            $quickresponse = get_string('answerisnoanswer', 'mod_surveypro');
        }

        return $quickresponse;
    }

    /**
     * Starting from the info stored into $answer, this function returns the corresponding content for the export file.
     *
     * @param object $answer
     * @param string $format
     * @return string - the string for the export file
     */
    public function userform_db_to_export($answer, $format='') {
        // The content of the provided answer.
        $content = $answer->content;

        // Trigger 'answernotsubmitted' and 'answerisnoanswer'.
        $quickresponse = self::userform_standardcontent_to_string($content);
        if (isset($quickresponse)) { // Parent method provided the response.
            return $quickresponse;
        }

        if (strlen($content)) {
            $return = $content;
        } else {
            if ($format == SURVEYPRO_FRIENDLYFORMAT) {
                $return = get_string('emptyanswer', 'mod_surveypro');
            } else {
                $return = '';
            }
        }

        return $return;
    }
}
