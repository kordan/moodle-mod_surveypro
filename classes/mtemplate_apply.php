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
 * Surveypro mtemplate_apply class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\utility_layout;

/**
 * The class representing a master template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mtemplate_apply extends mtemplate_base {

    /**
     * @var array
     */
    protected $langtree = [];

    /**
     * @var array
     */
    protected $mastertemplate;

    // MARK set.

    /**
     * Set mastertemplate.
     *
     * @param string $mastertemplate
     * @return void
     */
    public function set_mastertemplate($mastertemplate) {
        $this->mastertemplate = $mastertemplate;
    }

    // MARK get.

    /**
     * Count available user template.
     *
     * @return array
     */
    public function get_mtemplates() {
        $mtemplates = [];
        if ($mtemplatepluginlist = \core_component::get_plugin_list('surveyprotemplate')) {
            foreach ($mtemplatepluginlist as $mtemplatename => $mtemplatepath) {
                if (!get_config('surveyprotemplate_'.$mtemplatename, 'disabled')) {
                    $mtemplates[$mtemplatename] = get_string('pluginname', 'surveyprotemplate_'.$mtemplatename);
                }
            }
            asort($mtemplates);
        }

        return $mtemplates;
    }

    // MARK other.

    /**
     * Execute last minute check before applying master templates.
     *
     * @return void
     */
    public function lastminute_template_check() {
        global $CFG;

        $templatepath = $CFG->dirroot.'/mod/surveypro/template/'.$this->mastertemplate.'/template.xml';
        $xml = file_get_contents($templatepath);

        $this->validate_xml($xml);
    }

    /**
     * Actually add items coming from template to the db.
     *
     * @return void
     */
    public function add_items_from_template() {
        global $CFG, $DB;

        // Create the class to apply mastertemplate settings.
        $classname = 'surveyprotemplate_'.$this->templatename.'\template';
        $mastertemplate = new $classname();

        $fs = get_file_storage();

        $templatepath = $CFG->dirroot.'/mod/surveypro/template/'.$this->templatename.'/template.xml';
        $templatecontent = file_get_contents($templatepath);

        $simplexml = new \SimpleXMLElement($templatecontent);

        if (!$sortindexoffset = $DB->get_field('surveypro_item', 'MAX(sortindex)', ['surveyproid' => $this->surveypro->id])) {
            $sortindexoffset = 0;
        }

        // Load it only once. You are going to use it later.
        $config = get_config('surveyprotemplate_'.$this->templatename);

        $naturalsortindex = 0;
        foreach ($simplexml->children() as $xmlitem) {
            // Read the attributes of the item node.
            foreach ($xmlitem->attributes() as $attribute => $value) {
                // The $xmlitem looks like: <item type="format" plugin="label" version="2024022701">.
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
            }

            // Load the item class in order to call its methods to validate $record before saving it.
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Surveypro_item and surveypro_<<plugin>>.
                $tablename = $xmltable->getName();

                $record = new \stdClass();
                if ($tablename == 'surveypro_item') {
                    $itemid = 0; // This is the proof the surveypro_item record has not yet been saved.

                    // $tablestructure limits the fields that are going to be saved in the database.
                    $tablestructure = $this->get_table_structure();

                    $record->surveyproid = (int)$this->surveypro->id;
                    $record->type = $currenttype;
                    $record->plugin = $currentplugin;
                    // $item->item_add_mandatory_base_fields($record);
                } else {
                    // $tablestructure limits the fields that are going to be saved in the database.
                    $tablestructure = $this->get_table_structure($currenttype, $currentplugin);

                    $record->itemid = $itemid; // It has been defined when surveypro_item record was saved.
                    // $item->item_add_mandatory_plugin_fields($record);
                }

                foreach ($xmltable->children() as $xmlfield) {
                    $xmltag = $xmlfield->getName(); // Generally $xmltag is the name of the field.

                    // Tag <parent> always belong to surveypro_item table.
                    if ($xmltag == 'parent') {
                        // Debug: $label = 'Count of attributes of the field '.$xmltag;.
                        // Debug: echo '<h5>'.$label.': '.count($xmlfield->children()).'</h5>';.
                        foreach ($xmlfield->children() as $xmlparentattribute) {
                            $xmltag = $xmlparentattribute->getName();
                            $fieldexists = in_array($xmltag, $tablestructure);
                            if ($fieldexists) {
                                $record->{$xmltag} = (string)$xmlparentattribute;
                            }
                        }
                        continue;
                    }

                    // Tag <embedded> always belong to surveypro_item table.
                    if ($xmltag == 'embedded') {
                        // Urgently create a record because its id is needed here.
                        $itemid = $DB->insert_record('surveypro_item', $record);

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
                    // Because of this, I can not SIMPLY add $xmltag to $record but I need to make some more investigation.
                    // I neglect unneeded used fields, here.
                    // I will add mandatory (but missing because the usertemplate may be old) fields,
                    // before saving in the frame of the $item->item_force_coherence.
                    $fieldexists = in_array($xmltag, $tablestructure);
                    if ($fieldexists) {
                        $record->{$xmltag} = (string)$xmlfield;
                    }
                }

                // Apply master template settings.
                [$tablename, $record] = $mastertemplate->apply_template_settings($tablename, $record, $config);

                if ($tablename == 'surveypro_item') {
                    $naturalsortindex++;
                    $record->sortindex = $naturalsortindex + $sortindexoffset;
                    if (!empty($record->parentid)) {
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
     * Apply template.
     *
     * @return void
     */
    public function apply_template() {
        global $DB, $CFG;

        // Begin of: delete all existing items.
        $utilitylayoutman = new utility_layout($this->cm);
        $whereparams = ['surveyproid' => $this->surveypro->id];
        $utilitylayoutman->delete_items($whereparams);
        // End of: delete all existing items.

        $this->templatename = $this->formdata->mastertemplate;

        $record = new \stdClass();
        $record->id = $this->surveypro->id;
        $record->template = $this->templatename;
        $DB->update_record('surveypro', $record);

        $this->add_items_from_template();

        $paramurl = ['s' => $this->surveypro->id, 'section' => 'preview'];
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
    }

    /**
     * Trigger the provided event.
     *
     * @param string $eventname Event to trigger
     * @return void
     */
    public function trigger_event($eventname) {
        $eventdata = ['context' => $this->context, 'objectid' => $this->surveypro->id];
        $eventdata['other'] = ['templatename' => $this->formdata->mastertemplate];
        switch ($eventname) {
            case 'mastertemplate_applied':
                $event = \mod_surveypro\event\mastertemplate_applied::create($eventdata);
                break;
            case 'mastertemplate_saved': // Sometimes called 'downloaded' too.
                $event = \mod_surveypro\event\mastertemplate_saved::create($eventdata);
                break;
            default:
                $message = 'Unexpected $eventname = '.$eventname;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        $event->trigger();
    }

    /**
     * Display the welcome message of the apply page.
     *
     * @return void
     */
    public function welcome_apply_message() {
        global $OUTPUT;

        $message = get_string('welcome_mtemplateapply', 'mod_surveypro');
        echo $OUTPUT->notification($message, 'notifymessage');
    }
}
