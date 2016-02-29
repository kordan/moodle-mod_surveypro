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

require_once($CFG->dirroot.'/mod/surveypro/classes/templatebase.class.php');

class mod_surveypro_usertemplate extends mod_surveypro_templatebase {
    /**
     * $utemplateid: the ID of the current working user template
     */
    protected $utemplateid;

    /**
     * $confirm: is the action confirmed by the user?
     */
    protected $confirm;

    /**
     * setup
     */
    public function setup($utemplateid, $action, $confirm) {
        $this->set_utemplateid($utemplateid);
        $this->set_action($action);
        $this->set_confirm($confirm);
    }

    // MARK set

    /**
     * set_utemplateid
     *
     * @param $utemplateid
     * @return void
     */
    private function set_utemplateid($utemplateid) {
        $this->utemplateid = $utemplateid;
    }

    /**
     * set_action
     *
     * @param $action
     * @return void
     */
    private function set_action($action) {
        $this->action = $action;
    }

    /**
     * set_confirm
     *
     * @param $confirm
     * @return void
     */
    private function set_confirm($confirm) {
        $this->confirm = $confirm;
    }

    // MARK get

    /**
     * get_filemanager_options
     *
     * @param none
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
     * get_contextid_from_sharinglevel
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
     * @param sharinglevel
     * @return $context->id
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
     * get_contextstring_from_sharinglevel
     *
     * @param $contextlevel
     * @return $contextstring
     */
    public function get_contextstring_from_sharinglevel($contextlevel) {
        // Depending on the context level the component can be:
        // -> system, -> category, -> course, -> module, -> user
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
     * get_sharinglevel_options
     *
     * @param none
     * @return $options
     */
    public function get_sharinglevel_options() {
        global $USER;

        $labelsep = get_string('labelsep', 'langconfig'); // ': '

        $options = array();
        $options[CONTEXT_USER.'_'.$USER->id] = get_string('user').$labelsep.fullname($USER);
        // $options[CONTEXT_MODULE.'_'.$this->cm->id] = get_string('module', 'mod_surveypro').$labelsep.$this->surveypro->name;

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
     * get_utemplate_content
     *
     * @param $utemplateid
     * @return
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
     * get_utemplate_name
     *
     * @param none
     * @return
     */
    public function get_utemplate_name() {
        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($this->utemplateid);

        return $xmlfile->get_filename();
    }

    /**
     * Gets an array of all of the templates that users have saved to the site.
     *
     * @param stdClass $context The context that we are looking for.
     * @return array An array of templates
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
     * write_template_content
     *
     * @param boolean $visiblesonly
     * @return
     */
    public function write_template_content($visiblesonly=true) {
        global $DB;

        $uselessitemfields = array();
        $uselessitemfields[] = 'type';
        $uselessitemfields[] = 'plugin';
        $uselessitemfields[] = 'surveyproid';
        $uselessitemfields[] = 'sortindex';
        $uselessitemfields[] = 'formpage';
        $uselessitemfields[] = 'timecreated';
        $uselessitemfields[] = 'timemodified';

        $uselesspluginfields = array();
        $uselesspluginfields[] = 'surveyproid';
        $uselesspluginfields[] = 'itemid';

        $versiondisk = $this->get_plugin_versiondisk();

        $where = array('surveyproid' => $this->surveypro->id);
        if ($visiblesonly) {
            $where['hidden'] = '0';
        }
        $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();
        $context = context_module::instance($this->cm->id);

        $xmltemplate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($this->cm, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $xmlitem->addAttribute('version', $versiondisk["$itemseed->plugin"]);

            // Surveypro_item.
            $xmltable = $xmlitem->addChild('surveypro_item');

            $structure = $this->get_table_structure('surveypro_item');
            foreach ($structure as $field) {
                if (in_array($field, $uselessitemfields)) {
                    continue;
                }
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        $whereparams = array('id' => $parentid);
                        // I store sortindex instead of parentid, because at restore time parent id will change.
                        $val = $DB->get_field('surveypro_item', 'sortindex', $whereparams);
                        $xmlfield = $xmltable->addChild($field, $val);
                        // } else {
                        // It is empty, do not evaluate: jump.
                    }
                    continue;
                }

                $val = $item->item_get_generic_property($field);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                    // } else {
                    // It is empty, do not evaluate: jump.
                }
            }

            // Child table.
            $xmltable = $xmlitem->addChild('surveypro'.$itemseed->type.'_'.$itemseed->plugin);

            $structure = $this->get_table_structure('surveypro'.$itemseed->type.'_'.$itemseed->plugin);
            foreach ($structure as $field) {
                if (in_array($field, $uselesspluginfields)) {
                    continue;
                }

                $val = $item->item_get_generic_property($field);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, htmlspecialchars($val));
                    // } else {
                    // It is empty, do not evaluate: jump.
                }

                if ($field == 'content') {
                    if ($files = $fs->get_area_files($context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $item->get_itemid())) {
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

        // $option == false if 100% waste of time BUT BUT BUT
        // The output in the file is well written.
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
     * apply_template
     *
     * @param none
     * @return null
     */
    public function apply_template() {
        global $DB;

        $action = $this->formdata->action;
        $parts = explode('_', $this->formdata->usertemplateinfo);
        $this->utemplateid = $parts[1];

        // Before continuing.
        if ($action != SURVEYPRO_DELETEALLITEMS) {
            // Dispose assignemnt of pages.
            surveypro_reset_items_pages($this->surveypro->id);
        }

        $this->trigger_event('usertemplate_applied', $action);

        switch ($action) {
            case SURVEYPRO_IGNOREITEMS:
                break;
            case SURVEYPRO_HIDEITEMS:
                // Begin of: hide all other items.
                $DB->set_field('surveypro_item', 'hidden', 1, array('surveyproid' => $this->surveypro->id, 'hidden' => 0));
                // End of: hide all other items.
                break;
            case SURVEYPRO_DELETEALLITEMS:
                // Begin of: delete all existing items.
                $parambase = array('surveyproid' => $this->surveypro->id);
                $sql = 'SELECT si.plugin, si.type
                        FROM {surveypro_item} si
                        WHERE si.surveyproid = :surveyproid
                        GROUP BY si.plugin, si.type';
                $pluginseeds = $DB->get_records_sql($sql, $parambase);

                $this->items_deletion($pluginseeds, $parambase);
                // End of: delete all existing items.
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
            case SURVEYPRO_DELETEHIDDENITEMS:
                // Begin of: delete other items.
                $parambase = array('surveyproid' => $this->surveypro->id);
                if ($this->formdata->action == SURVEYPRO_DELETEVISIBLEITEMS) {
                    $parambase['hidden'] = 0;
                }
                if ($this->formdata->action == SURVEYPRO_DELETEHIDDENITEMS) {
                    $parambase['hidden'] = 1;
                }

                $sql = 'SELECT si.plugin, si.type
                        FROM {surveypro_item} si
                        WHERE si.surveyproid = :surveyproid
                            AND si.hidden = :hidden
                        GROUP BY si.plugin';
                $pluginseeds = $DB->get_records_sql($sql, $parambase);

                $this->items_deletion($pluginseeds, $parambase);
                $this->items_reindex();
                // End of: delete other items.
                break;
            default:
                $message = 'Unexpected $action = '.$action;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }

        $this->add_items_from_template();

        $paramurl = array('s' => $this->surveypro->id);
        $redirecturl = new moodle_url('/mod/surveypro/layout_manage.php', $paramurl);
        redirect($redirecturl);
    }

    /**
     * friendly_stop
     *
     * @param none
     * @return null
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
     * add_items_from_template
     *
     * @param $templateid
     * @return
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
            // <item type="field" plugin="character" version="2015123000">
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
            require_once($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/classes/plugin.class.php');
            $item = surveypro_get_item($this->cm, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Tables are: surveypro_item and surveypro(field|format)_<<plugin>>.
                $tablename = $xmltable->getName();

                $currenttablestructure = $this->get_table_structure($tablename);

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

                    // Tag <embedded> always belong to surveypro(field|format)_<<plugin>> table,
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
                        // before saving in the frame of the $item->item_force_coherence
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
                        $whereparams = array('surveyproid' => $this->surveypro->id, 'sortindex' => ($record->parentid + $sortindexoffset));
                        $record->parentid = $DB->get_field('surveypro_item', 'id', $whereparams, MUST_EXIST);
                    }

                    $itemid = $DB->insert_record($tablename, $record);
                } else {
                    // $item has already been defined few lines before $tablename was == 'surveypro_item'.

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
     * export_utemplate
     *
     * @param none
     * @return
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
     * upload_utemplate
     *
     * @param none
     * @return null
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
                // ...are not passed to file_save_draft_area_files()
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
                file_set_sortorder($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, $file->get_filepath(), $file->get_filename(), 1);
            }
        }

        $this->utemplateid = $file->get_id();
    }

    /**
     * generate_utemplate
     *
     * @param none
     * @return
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
     * manage_utemplates
     *
     * @param none
     * @return
     */
    public function manage_utemplates() {
        global $CFG, $USER, $OUTPUT;

        $candownloadutemplates = has_capability('mod/surveypro:downloadusertemplates', $this->context, null, true);
        $candeleteutemplates = has_capability('mod/surveypro:deleteusertemplates', $this->context, null, true);

        require_once($CFG->libdir.'/tablelib.php');

        // Begin of: $paramurlbase definition.
        $paramurlbase = array();
        $paramurlbase['id'] = $this->cm->id;
        // End of $paramurlbase definition.

        $table = new flexible_table('templatelist');

        $paramurl = array('id' => $this->cm->id);
        $baseurl = new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl);
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

        // $table->collapsible(true);
        $table->sortable(true, 'templatename'); // Sorted by sortindex by default.
        $table->no_sorting('actions');

        $table->column_class('templatename', 'templatename');
        $table->column_class('sharinglevel', 'sharinglevel');
        $table->column_class('timecreated', 'timecreated');
        $table->column_class('actions', 'actions');

        // General properties for the whole table.
        // $table->set_attribute('cellpadding', '5');
        $table->set_attribute('id', 'managetemplates');
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        $applytitle = get_string('applytemplate', 'mod_surveypro');
        $deletetitle = get_string('delete');
        $exporttitle = get_string('exporttemplate', 'mod_surveypro');

        $options = $this->get_sharinglevel_options();

        $templates = new stdClass();
        foreach ($options as $sharinglevel => $v) {
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
                // echo '$xmlfile:';
                // var_dump($xmlfile);
                $tablerow = array();
                // $tablerow[] = $xmlfile->get_filename();
                $tablerow[] = $dummysort[$row]['templatename'];
                // $tablerow[] = get_string($contextstring, 'mod_surveypro');
                $tablerow[] = $dummysort[$row]['sharinglevel'];
                // $tablerow[] = userdate($xmlfile->get_timecreated());
                $tablerow[] = userdate($dummysort[$row]['creationdate']);

                // $paramurlbase['fid'] = $xmlfile->get_id();
                $paramurlbase['fid'] = $dummysort[$row]['xmlfileid'];
                $row++;

                $icons = '';
                // SURVEYPRO_DELETEUTEMPLATE.
                if ($candeleteutemplates) {
                    if ($xmlfile->get_userid() == $USER->id) { // Only the owner can delete his/her template.
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_DELETEUTEMPLATE;
                        $paramurl['sesskey'] = sesskey();

                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl),
                            new pix_icon('t/delete', $deletetitle, 'moodle', array('title' => $deletetitle)),
                            null, array('title' => $deletetitle));
                    }
                }

                // SURVEYPRO_EXPORTUTEMPLATE.
                if ($candownloadutemplates) {
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_EXPORTUTEMPLATE;
                    $paramurl['sesskey'] = sesskey();

                    $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl),
                        new pix_icon('i/export', $exporttitle, 'moodle', array('title' => $exporttitle)),
                        null, array('title' => $exporttitle));
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
     * create_fictitious_table
     *
     * @param $templates
     * @param $usersort
     * @return null
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
        foreach ($templatenamecol as $k => $value) {
            $tablerow = array();
            $tablerow['templatename'] = $templatenamecol[$k];
            $tablerow['sharinglevel'] = $sharinglevelcol[$k];
            $tablerow['creationdate'] = $creationdatecol[$k];
            $tablerow['xmlfileid'] = $xmlfileidcol[$k];

            $originaltableperrows[] = $tablerow;
        }

        // $usersort
        $orderparts = explode(', ', $usersort);
        $orderparts = str_replace('templatename', '0', $orderparts);
        $orderparts = str_replace('sharinglevel', '1', $orderparts);
        $orderparts = str_replace('timecreated', '2', $orderparts);

        // $fieldindex and $sortflag
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
     * delete_utemplate
     *
     * @param none
     * @return null
     */
    public function delete_utemplate() {
        global $OUTPUT;

        if ($this->action != SURVEYPRO_DELETEUTEMPLATE) {
            return;
        }
        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $a = $this->get_utemplate_name();
            $message = get_string('askdeleteonetemplate', 'mod_surveypro', $a);
            $optionsbase = array('s' => $this->surveypro->id, 'act' => SURVEYPRO_DELETEUTEMPLATE);

            $optionsyes = $optionsbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['fid'] = $this->utemplateid;
            $urlyes = new moodle_url('/mod/surveypro/utemplates_manage.php', $optionsyes);
            $buttonyes = new single_button($urlyes, get_string('yes'));

            $optionsno = $optionsbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $urlno = new moodle_url('/mod/surveypro/utemplates_manage.php', $optionsno);
            $buttonno = new single_button($urlno, get_string('no'));

            echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
            echo $OUTPUT->footer();
            die();
        } else {
            switch ($this->confirm) {
                case SURVEYPRO_CONFIRMED_YES:
                    // Put the name in the gobal vaiable, to remember it for the log.
                    $this->templatename = $this->get_utemplate_name();

                    $fs = get_file_storage();
                    $xmlfile = $fs->get_file_by_id($this->utemplateid);
                    $xmlfile->delete();

                    $this->trigger_event('usertemplate_deleted');
                    break;
                case SURVEYPRO_CONFIRMED_NO:
                    $message = get_string('usercanceled', 'mod_surveypro');
                    echo $OUTPUT->notification($message, 'notifymessage');
                    break;
                default:
                    $message = 'Unexpected $this->confirm = '.$this->confirm;
                    debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * prevent_direct_user_input
     *
     * @param none
     * @return null
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
     * trigger_event
     *
     * @param string $event: event to trigger
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
                if ($action == SURVEYPRO_HIDEITEMS) {
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
