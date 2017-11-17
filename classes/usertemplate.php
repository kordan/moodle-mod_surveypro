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
 * Surveypro usertemplate class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class representing a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_usertemplate extends mod_surveypro_templatebase {

    /**
     * @var int ID of the current user template
     */
    protected $utemplateid;

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
     * Get filemanager options.
     *
     * @return $filemanageroptions
     */
    public function get_filemanager_options() {
        $templateoptions = array();
        $templateoptions['accepted_types'] = '.xml';
        $templateoptions['maxbytes'] = 0;
        $templateoptions['maxfiles'] = -1;
        $templateoptions['mainfile'] = true;
        $templateoptions['subdirs'] = false;

        return $templateoptions;
    }

    /**
     * Get context id from sharing level.
     *
     * It follow how $sharinglevel is formed:
     *
     *       $parts[0]    |   $parts[1]
     *  ----------------------------------
     *     CONTEXT_SYSTEM | 0
     *  CONTEXT_COURSECAT | $category->id
     *     CONTEXT_COURSE | $COURSE->id
     *     CONTEXT_MODULE | $cm->id
     *       CONTEXT_USER | $USER->id
     *
     * @param string $sharinglevel
     * @return int $context->id
     */
    public function get_contextid_from_sharinglevel($sharinglevel='') {
        if (empty($sharinglevel)) {
            $sharinglevel = $this->formdata->sharinglevel;
        }

        $parts = explode('_', $sharinglevel);
        $contextlevel = $parts[0];
        $contextid = $parts[1];

        if (!isset($parts[0]) || !isset($parts[1])) {
            $a = new stdClass();
            $a->sharinglevel = $sharinglevel;
            $a->methodname = 'get_contextid_from_sharinglevel';
            print_error('wrong_sharinglevel_found', 'mod_surveypro', null, $a);
        }

        switch ($contextlevel) {
            case CONTEXT_USER:
                $context = context_user::instance($contextid);
                break;
            case CONTEXT_MODULE:
                $context = context_module::instance($contextid);
                break;
            case CONTEXT_COURSE:
                $context = context_course::instance($contextid);
                break;
            case CONTEXT_COURSECAT:
                $context = context_coursecat::instance($contextid);
                break;
            case CONTEXT_SYSTEM:
                $context = context_system::instance();
                break;
            default:
                $message = 'Unexpected $contextlevel = '.$contextlevel;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        return $context->id;
    }

    /**
     * Get context string from sharing level.
     *
     * @param int $contextlevel
     * @return string $contextstring
     */
    public function get_contextstring_from_sharinglevel($contextlevel) {
        // Depending on the context level the component can be:
        // -> system, -> category, -> course, -> module, -> user.
        switch ($contextlevel) {
            case CONTEXT_SYSTEM:
                $contextstring = 'system';
                break;
            case CONTEXT_COURSECAT:
                $contextstring = 'category';
                break;
            case CONTEXT_COURSE:
                $contextstring = 'course';
                break;
            case CONTEXT_MODULE:
                $contextstring = 'module';
                break;
            case CONTEXT_USER:
                $contextstring = 'user';
                break;
            default:
                $message = 'Unexpected $contextlevel = '.$contextlevel;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        return $contextstring;
    }

    /**
     * Get sharing level options.
     *
     * @return $options
     */
    public function get_sharinglevel_options() {
        global $USER;

        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '..

        $options = array();
        $options[CONTEXT_USER.'_'.$USER->id] = get_string('user').$labelsep.fullname($USER);

        $parentcontexts = $this->context->get_parent_contexts();
        foreach ($parentcontexts as $context) {
            if (has_capability('mod/surveypro:saveusertemplates', $this->context)) {
                $options[$context->contextlevel.'_'.$context->instanceid] = $context->get_context_name();
            }
        }

        $context = context_system::instance();
        if (has_capability('mod/surveypro:saveusertemplates', $this->context)) {
            $options[CONTEXT_SYSTEM.'_0'] = get_string('site');
        }

        return $options;
    }

    /**
     * Get user template content.
     *
     * @param int $utemplateid
     * @return void
     */
    public function get_utemplate_content($utemplateid=0) {
        $fs = get_file_storage();
        if (empty($utemplateid)) {
            $utemplateid = $this->utemplateid;
        }
        $xmlfile = $fs->get_file_by_id($utemplateid);

        return $xmlfile->get_content();
    }

    /**
     * Get user template name.
     *
     * @return void
     */
    public function get_utemplate_name() {
        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);

        return $xmlfile->get_filename();
    }

    /**
     * Gets an array of all of the templates that users have saved to the site.
     *
     * @param int $contextid Context that we are looking for
     * @return array $templates
     */
    public function get_available_templates($contextid) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false);
        if (empty($files)) {
            return array();
        }

        $templates = array();
        foreach ($files as $file) {
            $templates[] = $file;
        }

        return $templates;
    }

    /**
     * Display the welcome message of the save page.
     *
     * @return void
     */
    public function welcome_save_message() {
        global $OUTPUT;

        $a = get_string('sharinglevel', 'mod_surveypro');
        $message = get_string('welcome_utemplatesave', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Display the welcome message of the import page.
     *
     * @return void
     */
    public function welcome_import_message() {
        global $OUTPUT;

        $a = get_string('tabutemplatepage2', 'mod_surveypro');
        $message = get_string('welcome_utemplateimport', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Display the welcome message of the apply page.
     *
     * @return void
     */
    public function welcome_apply_message() {
        global $OUTPUT;

        $a = new stdClass();
        $a->uploadpage = get_string('tabutemplatepage3', 'mod_surveypro');
        $a->savepage = get_string('tabutemplatepage2', 'mod_surveypro');
        $message = get_string('welcome_utemplateapply', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }

    /**
     * Write template content.
     *
     * @param boolean $visiblesonly
     * @return void
     */
    public function write_template_content($visiblesonly=true) {
        global $DB;

        $pluginversion = self::get_subplugin_versions();
        $where = array('surveyproid' => $this->surveypro->id);
        if ($visiblesonly) {
            $where['hidden'] = '0';
        }
        $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();
        $context = context_module::instance($this->cm->id);

        $xmltemplate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $index = $itemseed->type.'_'.$itemseed->plugin;
            $xmlitem->addAttribute('version', $pluginversion[$index]);

            // Surveypro_item.
            $xmltable = $xmlitem->addChild('surveypro_item');

            $structure = $this->get_table_structure();
            foreach ($structure as $field) {
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        $whereparams = array('id' => $parentid);
                        // I store sortindex instead of parentid, because at restore time parent id will change.
                        $val = $DB->get_field('surveypro_item', 'sortindex', $whereparams);
                        $xmlfield = $xmltable->addChild($field, $val);
                    } // Otherwise: It is empty, do not evaluate: jump.
                    continue;
                }

                $val = $item->item_get_generic_property($field);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }

            // Child table.
            $xmltable = $xmlitem->addChild('surveypro'.$itemseed->type.'_'.$itemseed->plugin);

            $structure = $this->get_table_structure($itemseed->type, $itemseed->plugin);
            foreach ($structure as $field) {
                $val = $item->item_get_generic_property($field);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, htmlspecialchars($val));
                } // Otherwise: It is empty, do not evaluate: jump.

                if ($field == 'content') {
                    $itemid = $item->get_itemid();
                    if ($files = $fs->get_area_files($context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $itemid)) {
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            if ($filename == '.') {
                                continue;
                            }
                            $xmlembedded = $xmltable->addChild('embedded');
                            $xmlembedded->addChild('filename', $filename);
                            $xmlembedded->addChild('filecontent', base64_encode($file->get_content()));
                        }
                    }
                }
            }
        }

        // In the coming code, "$option == false;" if 100% waste of time and should be changed to "$option == true;"
        // BUT BUT BUT...
        // the output in $dom->saveXML() is well written.
        // I prefer a more readable xml file instead of few nanoseconds saved.
        $option = false;
        if ($option) {
            // echo '$xmltemplate->asXML() = <br />';
            // print_object($xmltemplate->asXML());

            return $xmltemplate->asXML();
        } else {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmltemplate->asXML());

            // echo '$xmltemplate = <br />';
            // print_object($xmltemplate);

            return $dom->saveXML();
        }
    }

    /**
     * Apply template.
     *
     * @return void
     */
    public function apply_template() {
        $action = $this->formdata->action;
        $parts = explode('_', $this->formdata->usertemplateinfo);
        $this->utemplateid = $parts[1];

        // Before continuing.
        if ($action != SURVEYPRO_DELETEALLITEMS) {
            // Dispose assignemnt of pages.
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $utilityman->reset_items_pages();
        }

        $this->trigger_event('usertemplate_applied', $action);

        // Begin the process executing preliminary actions.
        switch ($action) {
            case SURVEYPRO_IGNOREITEMS:
                break;
            case SURVEYPRO_HIDEALLITEMS:
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $utilityman->items_set_visibility($whereparams, 0);

                $utilityman->reset_items_pages();

                break;
            case SURVEYPRO_DELETEALLITEMS:
                $utilityman = new mod_surveypro_utility($this->cm);
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $utilityman->delete_items($whereparams);
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $whereparams['hidden'] = 0;
                $utilityman->delete_items($whereparams);

                $utilityman->items_reindex();

                break;
            case SURVEYPRO_DELETEHIDDENITEMS:
                $whereparams = array('surveyproid' => $this->surveypro->id);
                $whereparams['hidden'] = 1;
                $utilityman->delete_items($whereparams);

                $utilityman->items_reindex();

                break;
                break;
            default:
                $message = 'Unexpected $action = '.$action;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        // Now actually add items from template.
        $this->add_items_from_template();

        $paramurl = array('s' => $this->surveypro->id);
        $redirecturl = new moodle_url('/mod/surveypro/layout_items.php', $paramurl);
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
        $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
        $hassubmissions = $utilityman->has_submissions();

        if ($hassubmissions && (!$riskyediting)) {
            echo $OUTPUT->notification(get_string('applyusertemplatedenied01', 'mod_surveypro'), 'notifyproblem');
            $url = new moodle_url('/mod/surveypro/view.php', array('s' => $this->surveypro->id));
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->surveypro->template && (!$riskyediting)) { // This survey comes from a master template so it is multilang.
            echo $OUTPUT->notification(get_string('applyusertemplatedenied02', 'mod_surveypro'), 'notifyproblem');
            $url = new moodle_url('/mod/surveypro/view_userform.php', array('s' => $this->surveypro->id));
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }
    }

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

        $simplexml = new SimpleXMLElement($templatecontent);
        // echo '<h2>Items saved in the file ('.count($simplexml->item).')</h2>';

        if (!$sortindexoffset = $DB->get_field('surveypro_item', 'MAX(sortindex)', array('surveyproid' => $this->surveypro->id))) {
            $sortindexoffset = 0;
        }

        $naturalsortindex = 0;
        foreach ($simplexml->children() as $xmlitem) {

            // Read the attributes of the item node:
            // The xmlitem looks like: <item type="field" plugin="character" version="2015123000">.
            foreach ($xmlitem->attributes() as $attribute => $value) {
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
            }

            // Take care to details.
            // Load the item class in order to call its methods to validate $record before saving it.
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Tables are: surveypro_item and surveypro(field|format)_<<plugin>>.
                $tablename = $xmltable->getName();
                if ($tablename == 'surveypro_item') {
                    $currenttablestructure = $this->get_table_structure();
                } else {
                    $currenttablestructure = $this->get_table_structure($currenttype, $currentplugin);
                }

                $record = new stdClass();

                // Add to $record mandatory fields that will be overwritten, hopefully, with the content of the usertemplate.

                $record->surveyproid = (int)$this->surveypro->id;
                $record->type = $currenttype;
                $record->plugin = $currentplugin;
                if ($tablename == 'surveypro_item') {
                    $item->item_add_mandatory_base_fields($record);
                } else {
                    $item->item_add_mandatory_plugin_fields($record);
                }

                foreach ($xmltable->children() as $xmlfield) {
                    $fieldname = $xmlfield->getName();

                    // Tag <embedded> always belong to surveypro(field|format)_<<plugin>> table
                    // so: ($fieldname == 'embedded') only when surveypro_item has already been saved...
                    // so: $itemid is known.
                    if ($fieldname == 'embedded') {
                        // echo '<h5>Count of attributes of the field '.$fieldname.': '.count($xmlfield->children()).'</h5>';
                        foreach ($xmlfield->children() as $xmlfileattribute) {
                            $fileattributename = $xmlfileattribute->getName();
                            if ($fileattributename == 'filename') {
                                $filename = $xmlfileattribute;
                            }
                            if ($fileattributename == 'filecontent') {
                                $filecontent = base64_decode($xmlfileattribute);
                            }
                        }

                        // echo 'I need to add: "'.$filename.'" to the filearea<br />';

                        // Add the file described by $filename and $filecontent to filearea.
                        // Alias, add pictures found in the utemplate to filearea.
                        $filerecord = new stdClass();
                        $filerecord->contextid = $this->context->id;
                        $filerecord->component = 'mod_surveypro';
                        $filerecord->filearea = SURVEYPRO_ITEMCONTENTFILEAREA;
                        $filerecord->itemid = $itemid;
                        $filerecord->filepath = '/';
                        $filerecord->filename = $filename;
                        $fileinfo = $fs->create_file_from_string($filerecord, $filecontent);
                    } else {
                        // The method xml_validation checks only the formal schema validity.
                        // It does not know whether the xml is old and holds no longer needed fields
                        // or does not hold fields that are now mandatory.
                        // Because of this, I can not SIMPLY add $fieldname to $record but I need to make some more investigation.
                        // I neglect no longer used fields, here.
                        // I will add mandatory (but missing because the usertemplate may be old) fields,
                        // before saving in the frame of the $item->item_force_coherence.
                        $fieldexists = in_array($fieldname, $currenttablestructure);
                        if ($fieldexists) {
                            $record->{$fieldname} = (string)$xmlfield;
                        }
                    }
                }

                unset($record->id);

                if ($tablename == 'surveypro_item') {
                    $naturalsortindex++;
                    $record->sortindex = $naturalsortindex + $sortindexoffset;
                    if (!empty($record->parentid)) {
                        $whereparams = array();
                        $whereparams['surveyproid'] = $this->surveypro->id;
                        $whereparams['sortindex'] = $record->parentid + $sortindexoffset;
                        $record->parentid = $DB->get_field('surveypro_item', 'id', $whereparams, MUST_EXIST);
                    }

                    $itemid = $DB->insert_record($tablename, $record);
                } else {
                    // Take care to details.
                    $item->item_force_coherence($record);
                    $item->item_validate_variablename($record, $itemid);
                    $record->itemid = $itemid;

                    $DB->insert_record($tablename, $record, false);
                }
            }
        }
    }

    /**
     * Make the usertemplate available for the download.
     *
     * @return void
     */
    public function export_utemplate() {
        global $CFG;

        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);
        $filename = $xmlfile->get_filename();
        $content = $xmlfile->get_content();

        // echo '<textarea rows="10" cols="100">'.$content.'</textarea>';

        $templatename = clean_filename('temptemplate-' . gmdate("Ymd_Hi"));
        $exportsubdir = "mod_surveypro/templateexport";
        make_temp_directory($exportsubdir);
        $exportdir = "$CFG->tempdir/$exportsubdir";
        $exportfile = $exportdir.'/'.$templatename;
        if (!preg_match('~\.xml$~', $exportfile)) {
            $exportfile .= '.xml';
        }
        $this->templatename = basename($exportfile);

        $this->trigger_event('usertemplate_exported');

        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        header('Pragma: public');
        $xmlfile = fopen($exportdir.'/'.$this->templatename, 'w');
        print $content;
        fclose($xmlfile);
        unlink($exportdir.'/'.$this->templatename);
    }

    /**
     * Upload the usertemplate.
     *
     * @return void
     */
    public function upload_utemplate() {

        $templateoptions = $this->get_filemanager_options();
        $contextid = $this->get_contextid_from_sharinglevel();
        $fs = get_file_storage();

        // Look at what is already on board.
        $oldfiles = array();
        if ($files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            foreach ($files as $file) {
                $oldfiles[] = $file->get_filename();
            }
        }

        // Add current files.
        $fieldname = 'importfile';
        if ($draftitemid = $this->formdata->{$fieldname.'_filemanager'}) {
            if (isset($templateoptions['return_types']) && !($templateoptions['return_types'] & FILE_REFERENCE)) {
                // We assume that if $options['return_types'] is NOT specified, we DO allow references.
                // This is not exactly right. BUT there are many places in code where filemanager options...
                // ...are not passed to file_save_draft_area_files().
                $allowreferences = false;
            }

            file_save_draft_area_files($draftitemid, $contextid, 'mod_surveypro', 'temporaryarea', 0, $templateoptions);
            $files = $fs->get_area_files($contextid, 'mod_surveypro', 'temporaryarea');
            $filecount = 0;
            foreach ($files as $file) {
                if (in_array($file->get_filename(), $oldfiles)) {
                    continue;
                }

                $filerecord = array();
                $filerecord['contextid'] = $contextid;
                $filerecord['component'] = 'mod_surveypro';
                $filerecord['filearea'] = SURVEYPRO_TEMPLATEFILEAREA;
                $filerecord['itemid'] = 0;
                $filerecord['timemodified'] = time();
                if (!$templateoptions['subdirs']) {
                    if ($file->get_filepath() !== '/' or $file->is_directory()) {
                        continue;
                    }
                }
                if ($templateoptions['maxbytes'] and $templateoptions['maxbytes'] < $file->get_filesize()) {
                    // Oversized file - should not get here at all.
                    continue;
                }
                if ($templateoptions['maxfiles'] != -1 and $templateoptions['maxfiles'] <= $filecount) {
                    // More files - should not get here at all.
                    break;
                }
                if (!$file->is_directory()) {
                    $filecount++;
                }

                if ($file->is_external_file()) {
                    if (!$allowreferences) {
                        continue;
                    }
                    $repoid = $file->get_repository_id();
                    if (!empty($repoid)) {
                        $filerecord['repositoryid'] = $repoid;
                        $filerecord['reference'] = $file->get_reference();
                    }
                }

                $fs->create_file_from_storedfile($filerecord, $file);
            }
        }

        if ($files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            if (count($files) == 1) {
                // Only one file attached, set it as main file automatically.
                $file = array_shift($files);
                $filepath = $file->get_filepath();
                $filename = $file->get_filename();
                file_set_sortorder($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, $filepath, $filename, 1);
            }
        }

        $this->utemplateid = $file->get_id();
    }

    /**
     * Generate the usertemplate.
     *
     * @return void
     */
    public function generate_utemplate() {
        global $USER;

        $this->templatename = $this->formdata->templatename;
        if (!preg_match('~\.xml$~', $this->templatename)) {
            $this->templatename .= '.xml';
        }
        $xmlcontent = $this->write_template_content($this->formdata->visiblesonly);
        // echo '<textarea rows="80" cols="100">'.$xmlcontent.'</textarea>';

        $fs = get_file_storage();
        $filerecord = new stdClass;

        $contextid = $this->get_contextid_from_sharinglevel();
        $filerecord->contextid = $contextid;

        $filerecord->component = 'mod_surveypro';
        $filerecord->filearea = SURVEYPRO_TEMPLATEFILEAREA;
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';
        $filerecord->userid = $USER->id;

        $filerecord->filename = str_replace(' ', '_', $this->templatename);
        if (!preg_match('~\.xml$~', $filerecord->filename)) {
            $filerecord->filename .= '.xml';
        }
        $fs->create_file_from_string($filerecord, $xmlcontent);

        return true;
    }

    /**
     * Display the usertemplates table.
     *
     * @return void
     */
    public function display_usertemplates_table() {
        global $CFG, $USER, $OUTPUT;

        require_once($CFG->libdir.'/tablelib.php');

        $candownloadutemplates = has_capability('mod/surveypro:downloadusertemplates', $this->context);
        $candeleteutemplates = has_capability('mod/surveypro:deleteusertemplates', $this->context);

        // Begin of: $paramurlbase definition.
        $paramurlbase = array();
        $paramurlbase['id'] = $this->cm->id;
        // End of $paramurlbase definition.

        $deletetitle = get_string('delete');
        $iconparams = array('title' => $deletetitle, 'class' => 'iconsmall');
        $deleteicn = new pix_icon('t/delete', $deletetitle, 'moodle', $iconparams);

        $importtitle = get_string('exporttemplate', 'mod_surveypro');
        $iconparams = array('title' => $importtitle, 'class' => 'iconsmall');
        $importicn = new pix_icon('i/import', $importtitle, 'moodle', $iconparams);

        $table = new flexible_table('templatelist');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = array();
        $tablecolumns[] = 'templatename';
        $tablecolumns[] = 'sharinglevel';
        $tablecolumns[] = 'timecreated';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = array();
        $tableheaders[] = get_string('templatename', 'mod_surveypro');
        $tableheaders[] = get_string('sharinglevel', 'mod_surveypro');
        $tableheaders[] = get_string('timecreated', 'mod_surveypro');
        $tableheaders[] = get_string('actions');
        $table->define_headers($tableheaders);

        $table->sortable(true, 'templatename'); // Sorted by templatename by default.
        $table->no_sorting('actions');

        $table->column_class('templatename', 'templatename');
        $table->column_class('sharinglevel', 'sharinglevel');
        $table->column_class('timecreated', 'timecreated');
        $table->column_class('actions', 'actions');

        $table->set_attribute('id', 'managetemplates');
        $table->set_attribute('class', 'generaltable');
        $table->setup();

        $options = $this->get_sharinglevel_options();

        $templates = new stdClass();
        foreach ($options as $sharinglevel => $unused) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];

            $contextid = $this->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $this->get_contextstring_from_sharinglevel($contextlevel);
            $templates->{$contextstring} = $this->get_available_templates($contextid);
        }

        $dummysort = $this->create_fictitious_table($templates, $table->get_sql_sort());

        $row = 0;
        foreach ($templates as $contextstring => $contextfiles) {
            foreach ($contextfiles as $xmlfile) {
                $tablerow = array();
                $tablerow[] = $dummysort[$row]['templatename'];
                $tablerow[] = $dummysort[$row]['sharinglevel'];
                $tablerow[] = userdate($dummysort[$row]['creationdate']);

                $paramurlbase['fid'] = $dummysort[$row]['xmlfileid'];
                $row++;

                $icons = '';
                // SURVEYPRO_DELETEUTEMPLATE.
                if ($candeleteutemplates) {
                    if ($xmlfile->get_userid() == $USER->id) { // Only the owner can delete his/her template.
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_DELETEUTEMPLATE;
                        $paramurl['sesskey'] = sesskey();

                        $link = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurl);
                        $icons .= $OUTPUT->action_icon($link, $deleteicn, null, array('title' => $deletetitle));
                    }
                }

                // SURVEYPRO_EXPORTUTEMPLATE.
                if ($candownloadutemplates) {
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_EXPORTUTEMPLATE;
                    $paramurl['sesskey'] = sesskey();

                    $link = new moodle_url('/mod/surveypro/utemplate_manage.php', $paramurl);
                    $icons .= $OUTPUT->action_icon($link, $importicn, null, array('title' => $importtitle));
                }

                $tablerow[] = $icons;

                $table->add_data($tablerow);
            }
        }
        $table->set_attribute('align', 'center');
        $table->summary = get_string('templatelist', 'mod_surveypro');
        $table->print_html();
    }

    /**
     * Create the tool to sort usertemplates in the table.
     *
     * @param array $templates
     * @param string $usersort
     * @return void
     */
    private function create_fictitious_table($templates, $usersort) {
        // Original table per columns: originaltablepercols.
        $templatenamecol = array();
        $sharinglevelcol = array();
        $creationdatecol = array();
        $xmlfileidcol = array();
        foreach ($templates as $contextstring => $contextfiles) {
            foreach ($contextfiles as $xmlfile) {
                $templatenamecol[] = $xmlfile->get_filename();
                $sharinglevelcol[] = get_string($contextstring, 'mod_surveypro');
                $creationdatecol[] = $xmlfile->get_timecreated();
                $xmlfileidcol[] = $xmlfile->get_id();
            }
        }
        $originaltablepercols = array($templatenamecol, $sharinglevelcol, $creationdatecol, $xmlfileidcol);

        // Original table per rows: originaltableperrows.
        $originaltableperrows = array();
        foreach ($templatenamecol as $k => $unused) {
            $tablerow = array();
            $tablerow['templatename'] = $templatenamecol[$k];
            $tablerow['sharinglevel'] = $sharinglevelcol[$k];
            $tablerow['creationdate'] = $creationdatecol[$k];
            $tablerow['xmlfileid'] = $xmlfileidcol[$k];

            $originaltableperrows[] = $tablerow;
        }

        // Add orderpart.
        $orderparts = explode(', ', $usersort);
        $orderparts = str_replace('templatename', '0', $orderparts);
        $orderparts = str_replace('sharinglevel', '1', $orderparts);
        $orderparts = str_replace('timecreated', '2', $orderparts);

        // Include $fieldindex and $sortflag.
        $fieldindex = array(0, 0, 0);
        $sortflag = array(SORT_ASC, SORT_ASC, SORT_ASC);
        foreach ($orderparts as $k => $orderpart) {
            $pair = explode(' ', $orderpart);
            $fieldindex[$k] = (int)$pair[0];
            $sortflag[$k] = ($pair[1] == 'ASC') ? SORT_ASC : SORT_DESC;
        }

        array_multisort($originaltablepercols[$fieldindex[0]], $sortflag[0],
                        $originaltablepercols[$fieldindex[1]], $sortflag[1],
                        $originaltablepercols[$fieldindex[2]], $sortflag[2], $originaltableperrows);

        return $originaltableperrows;
    }

    /**
     * Delete usertemplate.
     *
     * @return void
     */
    public function delete_utemplate() {
        global $OUTPUT;

        if ($this->action != SURVEYPRO_DELETEUTEMPLATE) {
            return;
        }

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $a = $this->get_utemplate_name();
            $message = get_string('confirm_delete1utemplate', 'mod_surveypro', $a);
            $optionsbase = array('s' => $this->surveypro->id, 'act' => SURVEYPRO_DELETEUTEMPLATE);

            $optionsyes = $optionsbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['fid'] = $this->utemplateid;
            $urlyes = new moodle_url('/mod/surveypro/utemplate_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionsbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/utemplate_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
            // Put the name in the gobal vaiable, to remember it for the log.
            $this->templatename = $this->get_utemplate_name();

            $fs = get_file_storage();
            $xmlfile = $fs->get_file_by_id($this->utemplateid);
            $a = $xmlfile->get_filename();
            $xmlfile->delete();

            $this->trigger_event('usertemplate_deleted');

            // Feedback.
            $message = get_string('feedback_delete1utemplate', 'mod_surveypro', $a);
            echo $OUTPUT->notification($message, 'notifymessage');
        }

        if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
            $message = get_string('usercanceled', 'mod_surveypro');
            echo $OUTPUT->notification($message, 'notifymessage');
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
     * Trigger the provided event.
     *
     * @param string $eventname Event to trigger
     * @param int $action
     * @return void
     */
    public function trigger_event($eventname, $action=null) {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        switch ($eventname) {
            case 'all_usertemplates_viewed':
                $event = \mod_surveypro\event\all_usertemplates_viewed::create($eventdata);
                break;
            case 'usertemplate_applied':
                if ($action == SURVEYPRO_IGNOREITEMS) {
                    $straction = get_string('ignoreitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_HIDEALLITEMS) {
                    $straction = get_string('hideitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEALLITEMS) {
                    $straction = get_string('deleteallitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEVISIBLEITEMS) {
                    $straction = get_string('deletevisibleitems', 'mod_surveypro');
                }
                if ($action == SURVEYPRO_DELETEHIDDENITEMS) {
                    $straction = get_string('deletehiddenitems', 'mod_surveypro');
                }
                $other = array();
                $other['templatename'] = $this->get_utemplate_name();
                $other['action'] = $straction;
                $eventdata['other'] = $other;
                $event = \mod_surveypro\event\usertemplate_applied::create($eventdata);
                break;
            case 'usertemplate_exported':
                $eventdata['other'] = array('templatename' => $this->get_utemplate_name());
                $event = \mod_surveypro\event\usertemplate_exported::create($eventdata);
                break;
            case 'usertemplate_imported':
                $eventdata['other'] = array('templatename' => $this->get_utemplate_name());
                $event = \mod_surveypro\event\usertemplate_imported::create($eventdata);
                break;
            case 'usertemplate_saved':
                $eventdata['other'] = array('templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_saved::create($eventdata);
                break;
            case 'usertemplate_deleted':
                $eventdata['other'] = array('templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_deleted::create($eventdata);
                break;
            default:
                $message = 'Unexpected $event = '.$event;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        $event->trigger();
    }
}
