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
 * Surveypro utemplate_apply class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\utility_layout;

use mod_surveypro\local\ipe\usertemplate_name;

/**
 * The class representing a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utemplate_apply extends utemplate_base {

    /**
     * @var int User confirmation to actions
     */
    protected $confirm;

    /**
     * Setup.
     *
     * @param int $utemplateid
     * @param int $action
     * @param int $confirm
     * @return void
     */
    public function setup($utemplateid, $action, $confirm) {
        $this->set_utemplateid($utemplateid);
        $this->set_action($action);
        $this->set_confirm($confirm);
    }

    // MARK set.

    /**
     * Set utemplateid.
     *
     * @param int $utemplateid
     * @return void
     */
    private function set_utemplateid($utemplateid) {
        $this->utemplateid = $utemplateid;
    }

    /**
     * Set action.
     *
     * @param int $action
     * @return void
     */
    private function set_action($action) {
        $this->action = $action;
    }

    /**
     * Set confirm.
     *
     * @param int $confirm
     * @return void
     */
    private function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    // MARK get.

    /**
     * Get the content of the user template drop down menu.
     *
     * @return array
     */
    public function get_utemplates_items() {
        $xmlfiles = $this->get_xmlfiles_list();

        $items = [];
        foreach ($xmlfiles as $contextid => $unused) {
            $contextlabel = $this->get_label_forcontextid($contextid);
            foreach ($xmlfiles[$contextid] as $xmlfile) {
                $xmlid = $xmlfile->get_id();
                $filename = $xmlfile->get_filename();
                $items[$contextid.'_'.$xmlid] = '('.$contextlabel.') '.$filename;
            }
        }
        asort($items);

        return $items;
    }

    // MARK other.

    /**
     * Actually add items from template.
     *
     * @return void
     */
    public function add_items_from_template() {
        global $CFG, $DB;

        $fs = get_file_storage();

        $this->templatename = $this->get_utemplate_name();
        $templatecontent = $this->get_utemplate_content();

        $simplexml = new \SimpleXMLElement($templatecontent);
        // Debug: echo '<h2>Items saved in the file ('.count($simplexml->item).')</h2>';.

        if (!$sortindexoffset = $DB->get_field('surveypro_item', 'MAX(sortindex)', ['surveyproid' => $this->surveypro->id])) {
            $sortindexoffset = 0;
        }

        $naturalsortindex = 0;
        foreach ($simplexml->children() as $xmlitem) {
            // Read the attributes of the item node:
            // The xmlitem looks like: <item type="field" plugin="character" version="2024032800">.
            foreach ($xmlitem->attributes() as $attribute => $value) {
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
            }

            // Load the item class in order to call its methods to validate $record before saving it.
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Tables are: surveypro_item and surveypro(field|format)_(plugin).
                $tablename = $xmltable->getName();

                $record = new \stdClass();
                if ($tablename == 'surveypro_item') {
                    $itemid = 0; // This is the proof the surveypro_item record has not yet been saved.

                    // $tablestructure limits the fields that are going to be saved in the database.
                    $tablestructure = $this->get_table_structure();

                    $record->surveyproid = (int)$this->surveypro->id;
                    $record->type = $currenttype;
                    $record->plugin = $currentplugin;
                    $item->item_add_fields_default_to_parent_table($record);
                } else {
                    // $tablestructure limits the fields that are going to be saved in the database.
                    $tablestructure = $this->get_table_structure($currenttype, $currentplugin);

                    $record->itemid = $itemid; // It has been defined when surveypro_item record was saved.
                    $item->item_add_fields_default_to_child_table($record);
                }

                foreach ($xmltable->children() as $xmlfield) { // Run over fields listed in the xml.
                    $xmltag = $xmlfield->getName(); // Generally $xmltag is the name of the field.

                    // Tag <parent> always belong to surveypro_item table.
                    if ($xmltag == 'parent') {
                        // Debug: $label = 'Count of attributes of the field '.$xmltag;.
                        // Debug: echo '<h5>'.$label.': '.count($xmlfield->children()).'</h5>';.
                        foreach ($xmlfield->children() as $xmlchildattribute) {
                            $xmltag = $xmlchildattribute->getName();
                            $fieldexists = in_array($xmltag, $tablestructure);
                            if ($fieldexists) {
                                $record->{$xmltag} = (string)$xmlchildattribute;
                            }
                        }
                        continue;
                    }

                    // Tag <embedded> always belong to surveypro_item table.
                    if ($xmltag == 'embedded') {
                        // Urgently create a record because its id is needed here.
                        // Please do not create a new record twice.
                        // If 2 embedded pictures are part of the content, take care to create only one record.
                        // If you already created the record for the first embedded picture, do not create one more record now.
                        if (empty($itemid)) {
                            $itemid = $DB->insert_record('surveypro_item', $record);
                        }

                        // Debug: $label = 'Count of attributes of the field '.$xmltag;
                        // Debug: echo '<h5>'.$label.': '.count($xmlfield->children()).'</h5>';.
                        foreach ($xmlfield->children() as $xmlfileattribute) {
                            $fileattributename = $xmlfileattribute->getName();
                            if ($fileattributename == 'filename') {
                                $filename = $xmlfileattribute;
                            }
                            if ($fileattributename == 'filecontent') {
                                $filecontent = base64_decode($xmlfileattribute);
                            }
                        }

                        // Debug: echo 'I need to add: "'.$filename.'" to the filearea<br>';.

                        // Add the file described by $filename and $filecontent to filearea.
                        // Alias, add pictures found in the utemplate to filearea.
                        $filerecord = new \stdClass();
                        $filerecord->contextid = $this->context->id;
                        $filerecord->component = 'mod_surveypro';
                        $filerecord->filearea = SURVEYPRO_ITEMCONTENTFILEAREA;
                        $filerecord->itemid = $itemid;
                        $filerecord->filepath = '/';
                        $filerecord->filename = $filename;
                        $fileinfo = $fs->create_file_from_string($filerecord, $filecontent);
                        continue;
                    }

                    // The method xml_validation checks only the formal schema validity.
                    // It does not know whether the xml is old and holds no longer needed fields
                    // or does not hold fields that are now mandatory.
                    // Because of this, I can not SIMPLY add $fieldname to $record but I need to make some more investigation.
                    // I neglect unneeded used fields, here.
                    // I will add mandatory (but missing because the usertemplate may be old) fields,
                    // before saving in the frame of the $item->item_force_coherence.
                    $fieldexists = in_array($xmltag, $tablestructure);
                    if ($fieldexists) {
                        $record->{$xmltag} = (string)$xmlfield;
                    }
                }

                if ($tablename == 'surveypro_item') {
                    $naturalsortindex++;
                    $record->sortindex = $naturalsortindex + $sortindexoffset;
                    if (!empty($record->parentid)) { // If I have a parent, its record was already saved.
                        $whereparams = ['surveyproid' => $this->surveypro->id];
                        $whereparams['sortindex'] = $record->parentid + $sortindexoffset;
                        $record->parentid = $DB->get_field('surveypro_item', 'id', $whereparams, MUST_EXIST);
                    }

                    if (empty($itemid)) { // If the record in surveypro_item has NOT already been added.
                        $itemid = $DB->insert_record('surveypro_item', $record);
                    } else {
                         // I had to urgently create a record to get its id in order to give it to $fs->create_file_from_string.
                         // Now I can not create a different record because I passed the id of the existing one.
                         // So I update the found record.
                        $record->id = $itemid;
                        $DB->update_record('surveypro_item', $record);
                    }
                } else {
                    // Take care to details.
                    $item->item_force_coherence($record);
                    $item->item_validate_variablename($record, $itemid);

                    $DB->insert_record($tablename, $record, false);
                }
            } // Closes foreach ($xmlitem->children() as $xmltable) alias: Surveypro_item and surveypro_<<plugin>>.
        }
    }

    /**
     * Execute last minute check before applying user templates.
     *
     * @return void
     */
    public function lastminute_template_check() {
        $parts = explode('_', $this->formdata->usertemplateinfo);
        $utemplateid = $parts[1];

        $xml = $this->get_utemplate_content($utemplateid);
        $this->validate_xml($xml);
    }

    /**
     * Apply template.
     *
     * @return void
     */
    public function apply_template() {
        $applyaction = $this->formdata->action;
        $parts = explode('_', $this->formdata->usertemplateinfo);
        $this->utemplateid = $parts[1];

        // Before continuing.
        if ($applyaction != SURVEYPRO_DELETEALLITEMS) {
            // Dispose assignemnt of pages.
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $utilitylayoutman->reset_pages();
        }

        $this->trigger_event('usertemplate_applied', $applyaction);

        // Begin the process executing preliminary actions.
        switch ($applyaction) {
            case SURVEYPRO_IGNOREITEMS:
                break;
            case SURVEYPRO_HIDEALLITEMS:
                $whereparams = ['surveyproid' => $this->surveypro->id];
                $utilitylayoutman->items_set_visibility($whereparams, 0);

                $utilitylayoutman->reset_pages();

                break;
            case SURVEYPRO_DELETEALLITEMS:
                $utilitylayoutman = new utility_layout($this->cm);
                $whereparams = ['surveyproid' => $this->surveypro->id];
                $utilitylayoutman->delete_items($whereparams);
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
                $whereparams = ['surveyproid' => $this->surveypro->id];
                $whereparams['hidden'] = 0;
                $utilitylayoutman->delete_items($whereparams);

                $utilitylayoutman->items_reindex();

                break;
            case SURVEYPRO_DELETEHIDDENITEMS:
                $whereparams = ['surveyproid' => $this->surveypro->id];
                $whereparams['hidden'] = 1;
                $utilitylayoutman->delete_items($whereparams);

                $utilitylayoutman->items_reindex();

                break;
                break;
            default:
                $message = 'Unexpected $applyaction = '.$applyaction;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        // Now actually add items from template.
        $this->add_items_from_template();

        $paramurl = ['s' => $this->surveypro->id, 'section' => 'itemslist'];
        $redirecturl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
        redirect($redirecturl);
    }

    /**
     * Display a friendly message to stop the page load under particular conditions.
     *
     * @return void
     */
    public function friendly_stop() {
        global $OUTPUT;

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
        $hassubmissions = $utilitylayoutman->has_submissions();

        if ($hassubmissions && (!$riskyediting)) {
            echo $OUTPUT->notification(get_string('applyusertemplatedenied01', 'mod_surveypro'), 'notifyproblem');
            $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $this->surveypro->id, 'section' => 'submissionslist']);
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->surveypro->template && (!$riskyediting)) { // This survey comes from a master template so it is multilang.
            echo $OUTPUT->notification(get_string('applyusertemplatedenied02', 'mod_surveypro'), 'notifyproblem');
            $url = new \moodle_url('/mod/surveypro/view.php', ['s' => $this->surveypro->id, 'section' => 'submissionform']);
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        if ($this->action != SURVEYPRO_NOACTION) {
            require_sesskey();
        }
        if ($this->action == SURVEYPRO_DELETEUTEMPLATE) {
            require_capability('mod/surveypro:deleteusertemplates', $this->context);
        }
        if ($this->action == SURVEYPRO_DELETEALLITEMS) {
            require_capability('mod/surveypro:manageusertemplates', $this->context);
        }
        if ($this->action == SURVEYPRO_EXPORTUTEMPLATE) {
            require_capability('mod/surveypro:downloadusertemplates', $this->context);
        }
    }

    /**
     * Display the welcome message of the apply page.
     *
     * @return void
     */
    public function welcome_apply_message() {
        global $OUTPUT;

        $a = new \stdClass();
        $a->uploadpage = get_string('utemplate_import', 'mod_surveypro');
        $a->savepage = get_string('utemplate_save', 'mod_surveypro');
        $message = get_string('welcome_utemplateapply', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }
}
