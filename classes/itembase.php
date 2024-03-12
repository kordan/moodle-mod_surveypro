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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use core_date;
use mod_surveypro\layout_itemsetup;
use mod_surveypro\utility_layout;
use mod_surveypro\utility_item;
use mod_surveypro\utility_submission;

/**
 * The base class for items
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class itembase {

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
     * @var int Indent of the item in the form page
     */
    protected $indent;

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
     * @var string The answer (as saved to db) that the parent item has to have in order to show this item as child
     */
    protected $parentvalue;

    /**
     * @var string The user friendly answer that the parent item has to have in order to show this item as child
     */
    protected $parentcontent;

    /**
     * @var int Feedback mask for the user to define the feedback once the item is edited
     */
    protected $itemeditingfeedback;

    /**
     * @var bool Does this item use a specific table?
     */
    protected $usesplugintable;

    /**
     * List of fields properties the surveypro creator will manage in the item definition form
     * By default each item property is present in the form
     * so, in each child class, I only need to "deactivate" item property not desired/needed/handled/wanted
     *
     * @var array
     */
    public $insetupform = [
        'common_fs' => true,
        'content' => true,
        'contentformat' => true,
        'customnumber' => true,
        'position' => true,
        'trimonsave' => true,
        'extranote' => true,
        'hideinstructions' => true,
        'required' => true,
        'variable' => true,
        'indent' => true,
        'hidden' => true,
        'insearchform' => true,
        'reserved' => true,
        'parentid' => true,
    ];

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

        $context = \context_module::instance($this->cm->id);

        // Some item, like pagebreak or fieldsetend, may be free of the plugin table.
        if ($this->usesplugintable) {
            $tablename = 'surveypro'.$this->type.'_'.$this->plugin;
            $sql = 'SELECT *, i.id as itemid, p.id as pluginid
                    FROM {surveypro_item} i
                      JOIN {'.$tablename.'} p ON p.itemid = i.id
                    WHERE i.id = :itemid';
        } else {
            $sql = 'SELECT *, i.id as itemid
                    FROM {surveypro_item} i
                    WHERE i.id = :itemid';
        }

        if ($record = $DB->get_record_sql($sql, ['itemid' => $itemid])) {
            foreach ($record as $option => $value) {
                $this->{$option} = $value;
            }
            // Plugins not using contentformat (only Fieldset, at the moment) are satisfied.
            // Pagebreak and fieldsetend are missing content too.

            // Special care to fields with format.
            $this->content = file_rewrite_pluginfile_urls(
               $this->content, 'pluginfile.php', $context->id,
               'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $itemid
            );

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
     * @param \stdClass $record
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
     * @param \stdClass $record
     * @return void
     */
    protected function get_common_settings($record) {
        // You are going to change item content (maybe sortindex, maybe the parentitem)
        // so, do not forget to reset items per page.
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $utilitylayoutman->reset_pages();

        $timenow = time();

        // Surveyproid.
        $record->surveyproid = $this->cm->instance;

        // Plugin and type are already onboard.

        // Checkboxes content.
        $checkboxessettings = ['hidden', 'insearchform', 'reserved', 'hideinstructions', 'required', 'trimonsave'];
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
        if (isset($record->extranote) && (\core_text::strlen($record->extranote) > 255)) {
            $record->extranote = \core_text::substr($record->extranote, 0, 255);
        }

        // Surveypro can be multilang
        // so I can not save labels to parentvalue as they may change.
        // Because of this, even if the user writes, for instance, "bread\nmilk" to parentvalue
        // I have to encode it to key(bread);key(milk).
        if (!empty($record->parentid)) {
            $parentitem = surveypro_get_item($this->cm, $this->surveypro, $record->parentid);
            $record->parentvalue = $parentitem->parent_encode_child_parentcontent($record->parentcontent);
            unset($record->parentcontent); // Why do I drop $record->parentcontent?
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

        $context = \context_module::instance($this->cm->id);

        $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmission = $utilitylayoutman->has_submissions(false);

        $tablename = 'surveypro'.$this->type.'_'.$this->plugin;
        $this->itemeditingfeedback = SURVEYPRO_NOFEEDBACK;

        // Does this record need to be saved as new record or as un update of a existing record?
        if (empty($record->itemid)) { // Item is new.

            // Sortindex.
            $sql = 'SELECT COUNT(\'x\')
                    FROM {surveypro_item}
                    WHERE surveyproid = :surveyproid
                      AND sortindex > 0';
            $whereparams = ['surveyproid' => $this->cm->instance];
            $record->sortindex = 1 + $DB->count_records_sql($sql, $whereparams);

            // Itemid.
            try {
                $transaction = $DB->start_delegated_transaction();

                if ($itemid = $DB->insert_record('surveypro_item', $record)) { // First surveypro_item save.
                    if ($this->insetupform['contentformat']) {
                        // Special care to the field content equipped with an editor.
                        $editoroptions = ['trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context];
                        $record->id = $itemid;
                        $record = file_postupdate_standard_editor(
                                      $record, 'content', $editoroptions,
                                      $context, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $record->id
                                  );
                        $DB->update_record('surveypro_item', $record);
                    }

                    // Now think to $tablename.
                    if ($this->usesplugintable) {
                        // Before saving to the the plugin table, validate the variable name.
                        $this->item_validate_variablename($record, $itemid);

                        $record->itemid = $itemid;
                        if ($pluginid = $DB->insert_record($tablename, $record)) { // First save of $tablename.
                            $this->itemeditingfeedback += 1; // 0*2^1+1*2^0.
                        }
                    } else {
                        $record->itemid = $itemid;
                    }
                }

                $transaction->allow_commit();

                // Event: item_created.
                $eventdata = ['context' => $context, 'objectid' => $itemid];
                $eventdata['other'] = ['type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_NEWITEM];
                $event = \mod_surveypro\event\item_created::create($eventdata);
                $event->trigger();
            } catch (\Exception $e) {
                // Extra cleanup steps.
                $transaction->rollback($e); // Rethrows exception.
            }

            if ($hassubmission) {
                // Set to SURVEYPRO_STATUSINPROGRESS each already sent submission.
                $whereparams = ['surveyproid' => $this->surveypro->id];
                $utilitysubmissionman->submissions_set_status($whereparams, SURVEYPRO_STATUSINPROGRESS);
            }
        } else {
            // Item already exists.

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

                // Special care to "editors".
                $editoroptions = ['trusttext' => true, 'subdirs' => false, 'maxfiles' => -1, 'context' => $context];
                $record = file_postupdate_standard_editor(
                              $record, 'content', $editoroptions,
                              $context, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $record->id
                          );
                if ($DB->update_record('surveypro_item', $record)) {
                    if ($this->usesplugintable) {
                        // Before saving to the plugin table, validate the variable name.
                        $this->item_validate_variablename($record, $record->itemid);

                        $record->id = $record->pluginid;

                        if ($DB->update_record($tablename, $record)) {
                            $this->itemeditingfeedback += 3; // 1*2^1+1*2^0 alias: editing + success.
                        } else {
                            $this->itemeditingfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail.
                        }
                    } else {
                        $this->itemeditingfeedback += 3; // 1*2^1+1*2^0 alias: editing + success.
                    }
                } else {
                    $this->itemeditingfeedback += 2; // 1*2^1+0*2^0 alias: editing + fail.
                }

                $transaction->allow_commit();

                $this->item_manage_chains($record->itemid, $oldhidden, $record->hidden, $oldreserved, $record->reserved);

                // Event: item_modified.
                $eventdata = ['context' => $context, 'objectid' => $record->itemid];
                $eventdata['other'] = ['type' => $record->type, 'plugin' => $record->plugin, 'view' => SURVEYPRO_NEWITEM];
                $event = \mod_surveypro\event\item_modified::create($eventdata);
                $event->trigger();
            } catch (\Exception $e) {
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
                            $utilitylayoutman->optional_to_required_followup($record->itemid);
                        }
                    }
                }
            }
        }
        // Save process is over.

        if ($hassubmission) {
            // Update completion state.
            $possibleusers = surveypro_get_participants($this->surveypro->id);
            $completion = new \completion_info($COURSE);
            if ($completion->is_enabled($this->cm) && $this->surveypro->completionsubmit) {
                foreach ($possibleusers as $user) {
                    $completion->update_state($this->cm, COMPLETION_INCOMPLETE, $user->id);
                }
            }
        }

        // Property $this->itemeditingfeedback is going to be part of $returnurl in layout.php with ['section' => 'itemsetup']
        // ... and there it will be send to layout.php. ['section' => 'itemslist'].
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
        $utilitysubmissionman = new utility_submission($this->cm, $this->surveypro);
        $pluginlist = $utilitysubmissionman->get_used_plugin_list(SURVEYPRO_TYPEFIELD);

        $usednames = [];
        foreach ($pluginlist as $plugin) {
            $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$plugin;
            $sql = 'SELECT p.itemid, p.variable
                    FROM {surveypro_item} i
                      JOIN {'.$tablename.'} p ON p.itemid = i.id
                    WHERE ((i.surveyproid = :surveyproid)
                      AND (p.itemid <> :itemid))';
            $whereparams = ['surveyproid' => $this->surveypro->id, 'itemid' => $itemid];
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
        $context = \context_module::instance($this->cm->id);

        // Now hide or unhide (whether needed) chain of ancestors or descendents.
        if ($this->itemeditingfeedback & 1) { // Bitwise logic, alias: if the item was successfully saved.

            // Management of ($oldhidden != $newhidden).
            if ($oldhidden != $newhidden) {
                $action = ($oldhidden) ? SURVEYPRO_SHOWITEM : SURVEYPRO_HIDEITEM;

                $itemsetupman = new layout_itemsetup($this->cm, $context, $this->surveypro);
                $itemsetupman->setup();
                $itemsetupman->set_type($this->type);
                $itemsetupman->set_plugin($this->plugin);
                $itemsetupman->set_itemid($itemid);
                $itemsetupman->set_action($action);
                $itemsetupman->set_view(SURVEYPRO_NOMODE);
                $itemsetupman->set_confirm(SURVEYPRO_CONFIRMED_YES);

                // Begin of: Hide/unhide part 2.
                if ( ($oldhidden == 1) && ($newhidden == 0) ) {
                    $itemsetupman->item_show_execute();
                    // A chain of parent items was shown.
                    $this->itemeditingfeedback += 4; // 1*2^2.
                }
                if ( ($oldhidden == 0) && ($newhidden == 1) ) {
                    $itemsetupman->item_hide_execute();
                    // Chain of children items was hided.
                    $this->itemeditingfeedback += 8; // 1*2^3.
                }
                // End of: hide/unhide part 2.
            }

            // Management of ($oldreserved != $newreserved).
            if ($oldreserved != $newreserved) {
                $action = ($oldreserved) ? SURVEYPRO_MAKEAVAILABLE : SURVEYPRO_MAKERESERVED;

                $itemsetupman = new layout_itemsetup($this->cm, $context, $this->surveypro);
                $itemsetupman->setup();
                $itemsetupman->set_type($this->type);
                $itemsetupman->set_plugin($this->plugin);
                $itemsetupman->set_itemid($itemid);
                $itemsetupman->set_action($action);
                $itemsetupman->set_view(SURVEYPRO_NOMODE);
                $itemsetupman->set_confirm(SURVEYPRO_CONFIRMED_YES);

                // Begin of: Make reserved/free part 2.
                if ( ($oldreserved == 1) && ($newreserved == 0) ) {
                    if ($itemsetupman->item_makeavailable_execute()) {
                        // A chain of parents items inherited free access.
                        $this->itemeditingfeedback += 16; // 1*2^4.
                    }
                }
                if ( ($oldreserved == 0) && ($newreserved == 1) ) {
                    if ($itemsetupman->item_makereserved_execute()) {
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

        $classname = 'surveypro'.$this->type.'_'.$this->plugin.'\item';
        if ($classname::get_canbeparent()) {
            // Take care: you can not use $this->get_content_array(SURVEYPRO_VALUES, 'options') to evaluate values
            // because $item was loaded before last save, so $this->get_content_array(SURVEYPRO_VALUES, 'options')
            // will still return the previous values.

            $childrenitems = $DB->get_records('surveypro_item', ['parentid' => $this->itemid], 'id', 'id, parentvalue');
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
        $template = $DB->get_field('surveypro', 'template', ['id' => $surveyproid], MUST_EXIST);
        if (empty($template)) {
            return;
        }

        // Take care: I verify the existence of the english folder even if, maybe, I will ask for strings in a different language.
        if (!file_exists($CFG->dirroot.'/mod/surveypro/template/'.$template.'/lang/en/surveyprotemplate_'.$template.'.php')) {
            // This template does not support multilang.
            return;
        }

        if ($multilangfields = $this->get_multilang_fields()) { // Pagebreak and fieldsetend have no multilang_fields.
            foreach ($multilangfields as $table => $mlfields) {
                foreach ($mlfields as $mlfield) {
                    // Backward compatibility.
                    // In the frame of https://github.com/kordan/moodle-mod_surveypro/pull/447 few multilang fields were added.
                    // This was really a mandatory addition but,
                    // opening surveypros created (from mastertemplates) before this addition,
                    // I may find that they don't have new added fields filled in the database
                    // so the corresponding property $this->{$fieldname} does not exist.
                    if (isset($this->{$mlfield})) {
                        $stringkey = $this->{$mlfield};
                         $this->{$mlfield} = get_string($stringkey, 'surveyprotemplate_'.$template);
                    } else {
                        $this->{$mlfield} = '';
                    }
                }
            }
        }
    }

    /**
     * Item split unix time.
     * Unix timestamps do not handle timezones
     * Since php 8.0.0 timestamp is nullable.
     *
     * Take in mind that date('Y_m_d_H_i', 0) returns:
     * Array (
     *     [year] => 1970
     *     [mon] => 01
     *     [mday] => 01
     *     [hours] => 01
     *     [minutes] => 00
     * )
     *
     * @param integer $time
     * @return void
     */
    protected function item_split_unix_time($time) {
        $datestring = date('Y_m_d_H_i', $time);

        // 2012_07_11_16_03.
        $getdate = [];
        [$getdate['year'], $getdate['mon'], $getdate['mday'], $getdate['hours'], $getdate['minutes']] = explode('_', $datestring);

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
     * clean the content of the field $record->{$field} (remove blank lines, trailing \r).
     *
     * @param object $record Item record
     * @param array $fieldlist List of fields to clean
     * @return void
     */
    protected function item_clean_textarea_fields($record, $fieldlist) {
        $utilityitemman = new utility_item($this->cm, $this->surveypro);

        foreach ($fieldlist as $field) {
            // Some item may be undefined causing: "Notice: Undefined property: \stdClass::$defaultvalue"
            // as, for instance, disabled field when $defaultoption == invite.
            if (isset($record->{$field})) {
                $temparray = $utilityitemman->multilinetext_to_array($record->{$field});
                $record->{$field} = implode("\n", $temparray);
            }
        }
    }

    /**
     * This method defines if an item can be switched to mandatory or not.
     *
     * Used by layout_itemsetup->display_items_table() to define the icon to show
     *
     * There are two types of fields.
     * 1) those for which (like the boolean)
     * defaultoption discriminates on the desired type of default: "Custom", "Invite", "No response"
     * and, if defaultoption == "Custom", defaultvalue intervenes and declares which custom default is chosen.
     *
     * 2) those for which (like multiselect).
     * noanswerdefault = 1 means "No response".
     *
     * @return boolean
     */
    public function item_canbemandatory() {
        $return = true;
        if (isset($this->defaultoption)) {
            if ($this->defaultoption == SURVEYPRO_NOANSWERDEFAULT) {
                $return = false;
            } else {
                $return = true;
            }
        } else if (isset($this->noanswerdefault)) {
            if ($this->noanswerdefault == 1) {
                $return = false;
            } else {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Add to the item record that is going to be saved, items that can not be omitted with default value
     * They, maybe, will be overwritten
     *
     * @param \stdClass $record
     * @return void
     */
    public function item_add_mandatory_base_fields(&$record) {
        $record->content = 'itembase';
        $record->contentformat = 1;
        $record->hidden = 0;
        $record->insearchform = 0;
        $record->reserved = 0;
        $record->formpage = 0;
        $record->timecreated = time();
    }

    /**
     * Add a dummy row to the mform to preserve colour alternation also for items using more than a single mform element.
     *
     * Credits for this amazing solution to the great Eloy Lafuente! He is a genius.
     * Add a dummy useless row (with height = 0) to the form in order to preserve the color alternation
     * mainly used for rate items, but not only.
     *
     * @param \moodleform $mform Form to which add the row
     * @return void
     */
    public function item_add_color_unifier($mform) {
        $mform->addElement('html', '<div class="hidden fitem fitem_fgroup colorunifier"></div>');
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
     * Returns if the field plugin needs contentformat
     *
     * @return bool
     */
    public static function response_uses_format() {
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
        $childrenitemscount = $DB->count_records('surveypro_item', ['parentid' => $itemid]);

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

        return [$whereclause, $whereparam];
    }

    // MARK set.

    /**
     * Defines presets for the editor field of surveyproitem in itembaseform.php.
     *
     * (copied from moodle20/cohort/edit.php)
     *
     * Some examples:
     * Each SURVEYPRO_ITEMFIELD:
     *     $this->insetupform['content'] == true
     *     $this->insetupform['contentformat'] = true
     *
     * Fieldset plugin:
     *     $this->insetupform['content'] == true
     *     $this->insetupform['contentformat'] = false
     *
     * Pagebreak plugin:
     *     $this->insetupform['content'] == false
     *     $this->insetupform['contentformat'] = false
     *
     * @return void
     */
    public function set_editor() {
        if ($this->insetupform['contentformat']) {
            $context = \context_module::instance($this->cm->id);
            // I have to set 'trusttext' => false because 'noclean' is ignored if trusttext is enabled!
            $editoroptions = ['noclean' => true, 'subdirs' => true, 'maxfiles' => -1, 'context' => $context];
            $filearea = SURVEYPRO_ITEMCONTENTFILEAREA;
            file_prepare_standard_editor($this, 'content', $editoroptions, $context, 'mod_surveypro', $filearea, $this->itemid);
        }
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
     * This method returns the list of the field used by the plugin.
     *
     * AT THE MOMENT, this method is never used.
     *
     * @param string $plugin
     * @param string $type
     * @return array
     */
    public function get_plugin_fields($plugin, $type) {
        global $CFG;

        if ((empty($type) && !empty($plugin)) || (!empty($type) && empty($plugin))) {
            $message = '$type and $plugin must be provided both or none.';
            debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $installxmls = ['surveypro_item', 'db/install.xml'];
        if (!empty($type) && !empty($plugin)) {
            $installxml = $CFG->dirroot.'/mod/surveypro/'.$type.'/'.$plugin.'/db/install.xml';
            // Some plugins are missing the install.xml because they don't have specific attributes.
            if (file_exists($installxml)) {
                $installxmls['surveypro'.$type.'_'.$plugin] = $type.'/'.$plugin.'/db/install.xml';
            }
        }

        foreach ($installxmls as $targettable => $installxml) {
            $currentfile = $CFG->dirroot.'/mod/surveypro/'.$installxml;
            $xmlall = simplexml_load_file($installxml);
            foreach ($xmlall->children() as $xmltables) { // TABLES opening tag.
                foreach ($xmltables->children() as $xmltable) { // TABLE opening tag.
                    $attributes = $xmltable->attributes();
                    $tablename = $attributes['NAME'];
                    if ($tablename != $targettable) {
                        continue;
                    }
                    foreach ($xmltable->children() as $xmlfields) { // FIELDS opening tag.
                        $curenttablefields = [];
                        foreach ($xmlfields->children() as $xmlfield) { // FIELD opening tag.
                            $attributes = $xmlfield->attributes();
                            $fieldname = $attributes['NAME'];
                            $curenttablefields[] = (string)$attributes['NAME'];
                        }
                        $fieldlist[$tablename] = $curenttablefields;
                        break;
                    }
                    // If the correct table has been found, don't go searching for one more table. Stop!
                    break;
                }
                // If the correct table has been found, don't go searching for one more table. Stop!
                break;
            }
        }

        return $fieldlist;
    }

    /**
     * Get if the plugin uses the position of options to save user answers.
     *
     * @return bool The plugin uses the position of options to save user answers.
     */
    public function get_uses_positional_answer() {
        return false;
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
            $data = [];
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
        $options = ['overflowdiv' => false, 'allowid' => true, 'para' => false];
        return format_text($this->content, $this->contentformat, $options);
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
     * Parse $this->labelother in $value and $label.
     *
     * @return $value
     * @return $label
     */
    protected function get_other() {
        if (preg_match('~^(.*)'.SURVEYPRO_OTHERSEPARATOR.'(.*)$~', $this->labelother, $match)) {
            $label = trim($match[1]);
            $value = trim($match[2]);
        } else {
            $label = trim($this->labelother);
            $value = '';
        }

        return [$value, $label];
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
    public function get_content_array($content, $field) {
        if (($content != SURVEYPRO_VALUES) && ($content != SURVEYPRO_LABELS)) {
            throw new Exception('Bad parameter passed to get_content_array');
        }

        $index = ($content == SURVEYPRO_VALUES) ? 1 : 2;
        $utilityitemman = new utility_item($this->cm, $this->surveypro);
        $options = $utilityitemman->multilinetext_to_array($this->{$field});

        $values = [];
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
     * Make the list of the fields using multilang
     * This is the "default" list that is supposed to be empty because Pagebreak and fieldset inherit from it
     *
     * @return array of felds
     */
    abstract public function get_multilang_fields();

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
        } else {
            $parentcontent = $this->parentcontent;
        }

        return $parentcontent;
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
     * Get the requested property.
     *
     * @param string $field
     * @return the content of the field whether defined
     */
    public function get_generic_property($field) {
        if (isset($this->{$field})) {
            return $this->{$field};
        } else {
            return false;
        }
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
     * Returns the content of the question anticipated by the question number.
     *
     * Given an $elementnumber, like, for instance, 'IIa'
     *
     * Case 0: If $questioncontent is very simple like: 'Is it true?' I want 'IIa Is it true?'
     *
     * Case 1: If $questioncontent = 'Hello guys<span><div>I like beer</div></span>
     * <p class="rtl">Share your opinion here, please.</p>
     * <p><p class="coolclass">Do you like beer, too?</p></p>
     * <p></p>'
     * I want: 'IIa Hello guys<span><div>I like beer</div></span>
     * <p class="rtl">Share your opinion here, please.</p>
     * <p><p class="coolclass">Do you like beer, too?</p></p>
     * <p></p>'
     *
     * Case 2: If $questioncontent = '<span><div>I like beer</div></span>
     * <p class="rtl">Share your opinion here, please.</p>
     * <p><p class="coolclass">Do you like beer, too?</p></p>
     * <p></p>'
     * I want: '<span><div>IIa I like beer</div></span>
     * <p class="rtl">Share your opinion here, please.</p>
     * <p><p class="coolclass">Do you like beer, too?</p></p>
     * <p></p>'
     *
     * I get:
     * $matches = array(4) {
     *   [0] => array(11) {
     *     [0] => string(10) "Hello guys"
     *     [1] => string(6) "<span>"
     *     [2] => string(22) "<div>I like beer</div>"
     *     [3] => string(12) "</span>"
     *     [4] => string(50) "<p class="rtl">Share your opinion here, please.</p>"
     *     [5] => string(5) ""
     *     [6] => string(3) "<p>"
     *     [7] => string(48) "<p class="coolclass">Do you like beer, too?</p>"
     *     [8] => string(9) "</p>"
     *     [9] => string(7) "<p></p>"
     *     [10] => string(0) ""
     *   }
     *   [1] =>  array(11) {
     *     [0] => string(0) ""
     *     [1] => string(6) "<span>"
     *     [2] => string(5) "<div>"
     *     [3] => string(7) "</span>"
     *     [4] => string(15) "<p class="rtl">"
     *     [5] => string(0) ""
     *     [6] => string(3) "<p>"
     *     [7] => string(21) "<p class="coolclass">"
     *     [8] => string(4) "</p>"
     *     [9] => string(3) "<p>"
     *     [10] => string(0) ""
     *   }
     *   [2] => array(11) {
     *     [0] => string(10) "Hello guys"
     *     [1] => string(0) ""
     *     [2] => string(11) "I like beer"
     *     [3] => string(5) ""
     *     [4] => string(31) "Share your opinion here, please."
     *     [5] => string(5) ""
     *     [6] => string(0) ""
     *     [7] => string(23) "Do you like beer, too?"
     *     [8] => string(5) ""
     *     [9] => string(0) ""
     *     [10] => string(0) ""
     *   }
     *   [3] => array(11) {
     *     [0] => string(0) ""
     *     [1] => string(0) ""
     *     [2] => string(6) "</div>"
     *     [3] => string(0) ""
     *     [4] => string(4) "</p>"
     *     [5] => string(0) ""
     *     [6] => string(0) ""
     *     [7] => string(4) "</p>"
     *     [8] => string(0) ""
     *     [9] => string(4) "</p>"
     *     [10] => string(0) ""
     *   }
     * }
     *
     * By omitting 'Hello guys' at the beginning (as in case 2), I miss match[0][0], match[1][0], match[2][0], match[3][0]. Correct.
     *
     * @return string $return
     */
    public function get_contentwithnumber() {
        $questioncontent = $this->get_content();
        $elementnumber = $this->get_customnumber();
        if (!$elementnumber) {
            return $questioncontent;
        }

        $return = $elementnumber.' '.$questioncontent;
        $regex = '~(<[^>]*>)?([^<]*)?(<\/[^>]*>)?~';
        if (preg_match_all($regex, $questioncontent, $matches)) {
            foreach ($matches[2] as $tagcontent) {
                $cleanedtagcontent = $tagcontent;
                $cleanedtagcontent = preg_replace('~(^\s+|\s+$)~', '', $cleanedtagcontent);
                $cleanedtagcontent = trim($cleanedtagcontent, ' '.chr(194).chr(160));
                if (!empty($cleanedtagcontent)) {
                    $newcontent = $elementnumber.' '.$tagcontent;
                    // I don't want regular expression meta-characters to be interpreted.
                    // I use preg_replace instead of str_replace because I want to replace ONLY the first occurrence of $tagcontent.
                    $return = preg_replace('~\Q'.$tagcontent.'\E~', $newcontent, $questioncontent, 1);
                    break;
                }
            }
        }

        return $return;
    }

    /**
     * Assign the template that better match the structure of the item to optimally display it into the PDF.
     *
     * @return the template to use at response report creation
     */
    public static function get_pdf_template() {
        return SURVEYPRO_3COLUMNSTEMPLATE;
    }

    /**
     * Was the user input marked as "to trim"?
     *
     * @return if the calling plugin requires a user input trim
     */
    public function get_trimonsave() {
        return false;
    }

    /**
     * Get itemeditingfeedback.
     *
     * @return the content of $itemeditingfeedback property whether defined
     */
    public function get_itemeditingfeedback() {
        return $this->itemeditingfeedback;
    }

    /**
     * Does this item use a specific table?
     *
     * @return the content of the static property "usesplugintable"
     */
    public static function get_usesplugintable() {
        return self::usesplugintable;
    }

    /**
     * Return the xml schema for surveypro_<<plugin>> table.
     *
     * @return string $schema
     */
    public static function get_itembase_schema() {
        // Fields: surveyproid, formpage, timecreated and timemodified are not supposed to be part of the file!
        $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
    <xs:element name="surveypro_item">
        <xs:complexType>
            <xs:sequence>
                <xs:element name="content" type="xs:string" minOccurs="0"/>
                <xs:element name="embedded" minOccurs="0" maxOccurs="unbounded">
                    <xs:complexType>
                        <xs:sequence>
                            <xs:element name="filename" type="xs:string"/>
                            <xs:element name="filecontent" type="xs:base64Binary"/>
                        </xs:sequence>
                    </xs:complexType>
                </xs:element>
                <xs:element name="contentformat" type="xs:int" minOccurs="0"/>

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
     * @return void
     */
    public function set_variable($variable) {
        $variable = format_string($variable);
        $this->variable = $variable;
    }

    /**
     * Set defaultoption.
     *
     * Introduced as needed by unit test.
     *
     * @param string $defaultoption
     * @return void
     */
    public function set_defaultoption($defaultoption) {
        $condition = false;
        $condition = $condition || ($defaultoption == SURVEYPRO_CUSTOMDEFAULT);
        $condition = $condition || ($defaultoption == SURVEYPRO_INVITEDEFAULT);
        $condition = $condition || ($defaultoption == SURVEYPRO_NOANSWERDEFAULT);
        if (!$condition) {
            $message = 'Passed defaultoption is not allowed.';
            debugging($message, DEBUG_DEVELOPER);
        }

        $this->defaultoption = $defaultoption;
    }

    /**
     * Set labelother.
     *
     * Introduced as needed by unit test.
     *
     * @param string $labelother
     * @return void
     */
    public function set_labelother($labelother) {
        $this->labelother = (string)$labelother;
    }

    /**
     * Set required.
     *
     * Introduced as needed by unit test.
     *
     * @param string $required
     * @return void
     */
    public function set_required($required) {
        $this->labelother = $required;
    }

    /**
     * Set options.
     *
     * Introduced as needed by unit test.
     *
     * @param string $options
     * @return void
     */
    public function set_options($options) {
        $this->options = $options;
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
            return $fillinginstruction.'<br>'.$extranote;
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

        $where = ['submissionid' => $submissionid, 'itemid' => $this->itemid];
        $givenanswer = $DB->get_field('surveypro_answer', 'content', $where);

        return ($givenanswer === $childitemrecord->parentvalue);
    }

    /**
     * This function is used ONLY if $surveypro->newpageforchild == false
     * it adds as much as needed $mform->disabledIf to disable items when parent condition does not match
     * This method is used by the child item
     * In the frame of this method the parent item is loaded and is requested to provide the disabledif conditions for its child
     *
     * @param \moodleform $mform
     * @return void
     */
    public function userform_add_disabledif($mform) {
        global $DB;

        if (!$this->parentid || ($this->type == SURVEYPRO_TYPEFORMAT)) {
            return;
        }

        $fieldnames = $this->userform_get_root_elements_name();

        $parentrestrictions = [];

        // If I am here this means I have a parent FOR SURE.
        // Instead of making one more query, I assign two variables manually.
        // At the beginning, $currentitem is me.
        $currentitem = new \stdClass();
        $currentitem->parentid = $this->get_parentid();
        $currentitem->parentvalue = $this->get_parentvalue();
        $mypage = $this->get_formpage(); // Once and forever.
        do {
            // Take care!
            // Even if (!$surveypro->newpageforchild) I can have all my ancestors into previous pages by adding pagebreaks manually.
            // Because of this, I need to chech page numbers.
            $where = ['id' => $currentitem->parentid];
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
                        echo '</span><br>';
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
        if (!core_text::strlen($content)) { // Item was disabled.
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

        if (core_text::strlen($content)) {
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
