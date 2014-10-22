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
 * The base class representing a field
 */
class mod_surveypro_templatebase {
    /**
     * $templatename
     */
    public $templatename = '';

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    /**
     * get_table_structure
     *
     * @param $tablename
     * @param $dropid
     * @return
     */
    public function get_table_structure($tablename, $dropid=true) {
        global $DB;

        $dbman = $DB->get_manager();

        if ($dbman->table_exists($tablename)) {
            $dbstructure = array();

            if ($dbfields = $DB->get_columns($tablename)) {
                foreach ($dbfields as $dbfield) {
                    $dbstructure[] = $dbfield->name;
                }
            }

            if ($dropid) {
                array_shift($dbstructure); // ID is always the first item
            }
            return $dbstructure;
        } else {
            return false;
        }
    }

    /**
     * write_template_content
     *
     * @param strin $templatetype
     * @param boolean $visiblesonly
     * @return
     */
    public function write_template_content($visiblesonly=true) {
        global $DB;

        $versiondisk = $this->get_plugin_versiondisk();

        $where = array('surveyproid' => $this->surveypro->id);
        if ($visiblesonly) {
            $where['hidden'] = '0';
        }
        $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();

        $counter = array();
        $xmltemplate = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);
            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $xmlitem->addAttribute('version', $versiondisk["$itemseed->plugin"]);

            // surveypro_item
            $xmltable = $xmlitem->addChild('surveypro_item');

            if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) {
                if ($multilangfields = $item->item_get_multilang_fields()) { // pagebreak and fieldset have not multilang_fields
                    $this->build_langtree('item', $multilangfields, $item);
                }
            }

            $structure = $this->get_table_structure('surveypro_item');
            foreach ($structure as $field) {
                if ($field == 'type') {
                    continue;
                }
                if ($field == 'plugin') {
                    continue;
                }
                if ($field == 'surveyproid') {
                    continue;
                }
                if ($field == 'formpage') {
                    continue;
                }
                if ($field == 'timecreated') {
                    continue;
                }
                if ($field == 'timemodified') {
                    continue;
                }
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        $whereparams = array('id' => $parentid);
                        // I store sortindex instead of parentid, because at restore time parent id will change
                        $val = $DB->get_field('surveypro_item', 'sortindex', $whereparams);
                        $xmlfield = $xmltable->addChild($field, $val);
                        // } else {
                        // it is empty, do not evaluate: jump
                    }

                    continue;
                }

                if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) {
                    $val = $this->xml_get_field_content($item, 'item', $field, $multilangfields);
                } else {
                    $val = $item->item_get_generic_property($field);
                }

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                    // } else {
                    // it is empty, do not evaluate: jump
                }
            }

            // child table
            $xmltable = $xmlitem->addChild('surveypro'.$itemseed->type.'_'.$itemseed->plugin);

            $structure = $this->get_table_structure('surveypro'.$itemseed->type.'_'.$itemseed->plugin);
            foreach ($structure as $field) {
                if ($field == 'surveyproid') {
                    continue;
                }
                if ($field == 'itemid') {
                    continue;
                }

                if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) {
                    $val = $this->xml_get_field_content($item, $itemseed->plugin, $field, $multilangfields);
                } else {
                    $val = $item->item_get_generic_property($field);
                }

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, htmlspecialchars($val));
                    // } else {
                    // it is empty, do not evaluate: jump
                }

                if ($field == 'content') {
                    if ($files = $fs->get_area_files($item->context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $item->itemid)) {
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
        // the output in the file is well written
        // I prefer a more readable xml file instead of few nanoseconds saved
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
     * xml_get_field_content
     *
     * @param $item
     * @param $dummyplugin
     * @param $field
     * @param $multilangfields
     * @return
     */
    public function xml_get_field_content($item, $dummyplugin, $field, $multilangfields) {
        // 1st: which fields are multilang for the current item?
        if (isset($multilangfields[$dummyplugin])) { // has the plugin $dummyplugin multilang fields?
            if (in_array($field, $multilangfields[$dummyplugin])) { // if the field that is going to be assigned belongs to your multilang fields
                $component = $dummyplugin.'_'.$field;

                if (isset($this->langtree[$component])) {
                    end($this->langtree[$component]);
                    $val = key($this->langtree[$component]);
                    return $val;
                }
            }
        }

        $content = $item->item_get_generic_property($field);
        if (strlen($content)) {
            $val = $content;
        } else {
            // it is empty, do not evaluate: jump
            $val = null;
        }

        return $val;
    }

    /**
     * items_deletion
     *
     * @param records $pluginseeds
     *
     * @return null
     */
    public function items_deletion($pluginseeds, $parambase) {
        global $DB;

        $dbman = $DB->get_manager();

        $pluginparams = $parambase;
        foreach ($pluginseeds as $pluginseed) {
            $tablename = 'surveypro'.$pluginseed->type.'_'.$pluginseed->plugin;
            if ($dbman->table_exists($tablename)) {
                $pluginparams['plugin'] = $pluginseed->plugin;

                if ($deletelist = $DB->get_records('surveypro_item', $pluginparams, 'id', 'id')) {
                    $deletelist = array_keys($deletelist);

                    $select = 'itemid IN ('.implode(',', $deletelist).')';
                    $DB->delete_records_select($tablename, $select);
                }
            }
        }
        $DB->delete_records('surveypro_item', $parambase);
    }

    /**
     * apply_template
     *
     * @param $templatetype
     * @return null
     */
    public function apply_template() {
        global $DB;

        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            if (!empty($this->nonmatchingplugin)) { // master templates do not use $this->nonmatchingplugin
                return;
            }

            if ($this->confirm == SURVEYPRO_CONFIRMED_YES) {
                // I arrived here after the confirmation of the COMPLETE deletion of each item in the surveypro due to
                // User templates = None
                // Preexisting elements = Delete all elements
                $action = SURVEYPRO_DELETEALLITEMS;
                $this->utemplateid = 0;
            } else {
                $action = $this->formdata->action;
                $this->utemplateid = $this->formdata->usertemplate;
            }

            // --> --> VERY DANGEROUS ACTION: User is going to erase all the items of the survey <-- <--
            if ((empty($this->utemplateid)) && ($action == SURVEYPRO_DELETEALLITEMS)) {
                // if you really are in the dangerous situation, ask!
                if ($this->confirm != SURVEYPRO_CONFIRMED_YES) {
                    // Do not operate. Ask for confirmation before!
                    return;
                }
            }
        } else {
            $action = SURVEYPRO_DELETEALLITEMS;
        }

        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            $this->trigger_event('usertemplate_applied');
        } else {
            $this->trigger_event('mastertemplate_applied');
        }

        switch ($action) {
            case SURVEYPRO_IGNOREITEMS:
                break;
            case SURVEYPRO_HIDEITEMS:
                // BEGIN: hide all other items
                $DB->set_field('surveypro_item', 'hidden', 1, array('surveyproid' => $this->surveypro->id, 'hidden' => 0));
                // END: hide all other items
                break;
            case SURVEYPRO_DELETEALLITEMS:
                // BEGIN: delete all existing items
                $parambase = array('surveyproid' => $this->surveypro->id);
                $sql = 'SELECT si.plugin, si.type
                        FROM {surveypro_item} si
                        WHERE si.surveyproid = :surveyproid
                        GROUP BY si.plugin';
                $pluginseeds = $DB->get_records_sql($sql, $parambase);

                $this->items_deletion($pluginseeds, $parambase);
                // END: delete all existing items
                break;
            case SURVEYPRO_DELETEVISIBLEITEMS:
            case SURVEYPRO_DELETEHIDDENITEMS:
                // BEGIN: delete other items
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
                // END: delete other items
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $this->formdata->action = '.$this->formdata->action, DEBUG_DEVELOPER);
        }

        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            if (!empty($this->utemplateid)) { // something was selected
                $this->add_items_from_template();
            }
        } else {
            $this->templatename = $this->formdata->mastertemplate;
            $record = new stdClass();

            $record->id = $this->surveypro->id;
            $record->template = $this->templatename;
            $DB->update_record('surveypro', $record);

            $this->add_items_from_template();
        }

        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            $paramurl = array('s' => $this->surveypro->id);
            $redirecturl = new moodle_url('/mod/surveypro/items_manage.php', $paramurl);
            redirect($redirecturl);
        } else {
            $paramurl = array('s' => $this->surveypro->id, 'view' => SURVEYPRO_PREVIEWSURVEYFORM);
            $redirecturl = new moodle_url('/mod/surveypro/view_userform.php', $paramurl);
            redirect($redirecturl);
        }
    }

    /**
     * friendly_stop
     *
     * @return null
     */
    public function friendly_stop() {
        global $OUTPUT;

        // master templates do not use $this->nonmatchingplugin
        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            if (!empty($this->nonmatchingplugin)) {
                // for sure I am dealing witha usertemplate
                $a = new stdClass();
                $a->plugins = '<li>'.implode('</li>;<li>', array_keys($this->nonmatchingplugin)).'.</li>';
                $a->tab = get_string('tabutemplatename', 'surveypro');
                $a->page1 = get_string('tabutemplatepage1' , 'surveypro');
                $a->page3 = get_string('tabutemplatepage3' , 'surveypro');

                $message = get_string('frendlyversionmismatchuser', 'surveypro', $a);
                echo $OUTPUT->notification($message, 'notifyproblem');
                return;
            }
        }

        $riskyediting = ($this->surveypro->riskyeditdeadline > time());
        $hassubmissions = surveypro_count_submissions($this->surveypro->id);

        if ($hassubmissions && (!$riskyediting)) {
            echo $OUTPUT->box(get_string('applyusertemplatedenied01', 'surveypro'));
            $url = new moodle_url('/mod/surveypro/view_userform.php', array('s' => $this->surveypro->id));
            echo $OUTPUT->continue_button($url);
            echo $OUTPUT->footer();
            die();
        }

        if ($this->templatetype == SURVEYPRO_USERTEMPLATE) {
            if ($this->surveypro->template && (!$riskyediting)) { // this survey comes from a master template so it is multilang
                echo $OUTPUT->box(get_string('applyusertemplatedenied02', 'surveypro'));
                $url = new moodle_url('/mod/surveypro/view_userform.php', array('s' => $this->surveypro->id));
                echo $OUTPUT->continue_button($url);
                echo $OUTPUT->footer();
                die();
            }

            if (!$this->formdata) {
                if (($this->action == SURVEYPRO_DELETEALLITEMS) && ($this->utemplateid == 0)) {
                    // if you really were in the dangerous situation,
                    if ($this->confirm == SURVEYPRO_CONFIRMED_NO) {
                        // but you got a disconfirmation: declare it and give up.
                        $message = get_string('usercanceled', 'surveypro');
                        echo $OUTPUT->notification($message, 'notifymessage');
                    }
                }
                return;
            }

            if ((!empty($this->formdata->usertemplate)) || ($this->formdata->action != SURVEYPRO_DELETEALLITEMS)) {
                return;
            }

            // --> --> VERY DANGEROUS ACTION: User is going to erase all the items of the survey <-- <--
            // if you really are in the dangerous situation, ask!
            if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
                // ask for confirmation
                $message = get_string('askallitemserase', 'surveypro');

                $optionbase = array();
                $optionbase['s'] = $this->surveypro->id;
                $optionbase['usertemplate'] = $this->formdata->usertemplate;
                $optionbase['act'] = SURVEYPRO_DELETEALLITEMS;
                $optionbase['sesskey'] = sesskey();

                $optionsyes = $optionbase;
                $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
                $urlyes = new moodle_url('/mod/surveypro/utemplates_apply.php', $optionsyes);
                $buttonyes = new single_button($urlyes, get_string('confirmallitemserase', 'surveypro'));

                $optionsno = $optionbase;
                $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
                $urlno = new moodle_url('/mod/surveypro/utemplates_apply.php', $optionsno);
                $buttonno = new single_button($urlno, get_string('no'));

                echo $OUTPUT->confirm($message, $buttonyes, $buttonno);
                echo $OUTPUT->footer();
                die();
            }
        }
    }

    /**
     * add_items_from_template
     *
     * @param $templateid
     * @return
     */
    public function add_items_from_template() {
        global $DB, $CFG;

        $context = context_module::instance($this->cm->id);
        $fs = get_file_storage();

        if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) { // it is multilang
            $templatepath = $CFG->dirroot.'/mod/surveypro/template/'.$this->templatename.'/template.xml';
            $templatecontent = file_get_contents($templatepath);
        } else {
            $this->templatename = $this->get_utemplate_name();
            $templatecontent = $this->get_utemplate_content();
        }

        // create the class to apply mastertemplate settings
        if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) {
            $classfile = $CFG->dirroot.'/mod/surveypro/template/'.$this->templatename.'/template.class.php';
            include_once($classfile);
            $classname = 'mod_surveypro_template_'.$this->templatename;
            $mastertemplate = new $classname();
        }

        $simplexml = new SimpleXMLElement($templatecontent);
        // echo '<h2>Items saved in the file ('.count($simplexml->item).')</h2>';

        if (!$sortindexoffset = $DB->get_field('surveypro_item', 'MAX(sortindex)', array('surveyproid' => $this->surveypro->id))) {
            $sortindexoffset = 0;
        }

        foreach ($simplexml->children() as $xmlitem) {
            // echo '<h3>Count of tables for the current item: '.count($xmlitem->children()).'</h3>';
            foreach($xmlitem->attributes() as $attribute => $value) {
                // <item type="format" plugin="label" version="2014030201">
                // echo 'Trovo: '.$attribute.' = '.$value.'<br />';
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
            }

            foreach ($xmlitem->children() as $xmltable) { // surveypro_item and surveypro_<<plugin>>
                $tablename = $xmltable->getName();
                // echo '<h4>Count of fields of the table '.$tablename.': '.count($xmltable->children()).'</h4>';
                $record = array();
                foreach ($xmltable->children() as $xmlfield) {
                    $fieldname = $xmlfield->getName();

                    // tag <embedded> always belong to surveypro_<<plugin>> table
                    // so: ($fieldname == 'embedded') only when surveypro_item has already been saved
                    // so: $itemid is known
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

                        // add the file described by $filename and $filecontent to filearea
                        $filerecord = new stdClass();
                        $filerecord->contextid = $context->id;
                        $filerecord->component = 'mod_surveypro';
                        $filerecord->filearea = SURVEYPRO_ITEMCONTENTFILEAREA;
                        $filerecord->itemid = $itemid;
                        $filerecord->filepath = '/';
                        $filerecord->filename = $filename;
                        $fileinfo = $fs->create_file_from_string($filerecord, $filecontent);
                    } else {
                        $record[$fieldname] = (string)$xmlfield;
                    }
                }

                unset($record['id']);
                $record['surveyproid'] = $this->surveypro->id;

                // apply template settings
                if ($this->templatetype == SURVEYPRO_MASTERTEMPLATE) {
                    $mastertemplate->apply_template_settings($record);
                }

                $record['type'] = $currenttype;
                $record['plugin'] = $currentplugin;
                if ($tablename == 'surveypro_item') {
                    $record['sortindex'] += $sortindexoffset;
                    if (!empty($record['parentid'])) {
                        $whereparams = array('surveyproid' => $this->surveypro->id, 'sortindex' => ($record['parentid'] + $sortindexoffset));
                        $record['parentid'] = $DB->get_field('surveypro_item', 'id', $whereparams, MUST_EXIST);
                    }

                    $itemid = $DB->insert_record($tablename, $record);
                } else {
                    if ($currenttype == SURVEYPRO_TYPEFIELD) {
                        $this->validate_variablename($record);
                    }

                    $record['itemid'] = $itemid;
                    $DB->insert_record($tablename, $record, false);
                }
            }
        }
    }

    /**
     * validate_variablename
     *
     * @param stdobject $record
     * @return
     */
    public function validate_variablename($record) {
        global $DB;

        // if variable does not exist
        if ($record['type'] == SURVEYPRO_TYPEFORMAT) {
            // should never occur
            return;
        }

        $tablename = 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$record['plugin'];
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $select = 'SELECT COUNT(p.id)
                FROM {'.$tablename.'} p
                    JOIN {surveypro_item} i ON i.id = p.itemid ';
        $whereset = 'WHERE (i.surveyproid = :surveyproid)';
        $whereverify = 'WHERE ((i.surveyproid = :surveyproid) AND (p.variable = :variable))';

        // Verify variable was set. If not, set it.
        if (!isset($candidatevariable) || empty($candidatevariable)) {
            $sql = $select.$whereset;
            $plugincount = 1 + $DB->count_records_sql($sql, $whereparams);
            $plugincount = str_pad($plugincount, 3, '0', STR_PAD_LEFT);

            $candidatevariable = $record['plugin'].'_'.$plugincount;
        } else {
            $candidatevariable = $record['variable'];
        }

        // verify the given name is unique. If not, change it.
        $i = 0; // if name is duplicate, restart verification from 0
        $whereparams['variable'] = $candidatevariable;
        $sql = $select.$whereverify;

        // while ($DB->record_exists_sql($sql, $whereparams)) {
        while ($DB->count_records_sql($sql, $whereparams)) {
            $i++;
            $candidatevariable = $record['plugin'].'_'.str_pad($i, 3, '0', STR_PAD_LEFT);
            $whereparams['variable'] = $candidatevariable;
        }

        $record['variable'] = $candidatevariable;
    }

    /**
     * get_plugin_versiondisk
     *
     * @param null
     * @return versions of each field|format item plugin
     */
    public function get_plugin_versiondisk() {
        // Get plugins versiondisk
        $pluginman = core_plugin_manager::instance();
        $subplugins = $pluginman->get_subplugins_of_plugin('surveypro');
        $versions = array();
        foreach ($subplugins as $component => $plugin) {
            if (($plugin->type != 'surveypro'.SURVEYPRO_TYPEFIELD) &&
                ($plugin->type != 'surveypro'.SURVEYPRO_TYPEFORMAT)) {
                continue;
            }
            $versions["$plugin->name"] = $plugin->versiondisk;
        }

        return $versions;
    }

    /**
     * validate_xml
     *
     * @param $xml
     * @return object|boolean error describing the message to show, false if no error is found
     */
    public function validate_xml($xml) {
        global $CFG;

        $debug = false;
        // $debug = true; //if you want to stop anyway to see where the xml template is buggy

        $versiondisk = $this->get_plugin_versiondisk();
        $lastsortindex = 0;
        $simplexml = new SimpleXMLElement($xml);
        foreach ($simplexml->children() as $xmlitem) {
            foreach($xmlitem->attributes() as $attribute => $value) {
                // <item type="format" plugin="label" version="2014030201">
                // echo 'Found: '.$attribute.' = '.$value.'<br />';
                if ($attribute == 'type') {
                    $currenttype = (string)$value;
                }
                if ($attribute == 'plugin') {
                    $currentplugin = (string)$value;
                }
                if ($attribute == 'version') {
                    $currentversion = (string)$value;
                }
            }
            if (!isset($currenttype)) {
                $error = new stdClass();
                $error->key = 'missingitemtype';

                return $error;
            }
            if (!isset($currentplugin)) {
                $error = new stdClass();
                $error->key = 'missingitemplugin';

                return $error;
            }
            if (!isset($currentversion)) {
                $error = new stdClass();
                $error->key = 'missingitemversion';

                return $error;
            }
            // ok, $currenttype and $currentplugin are onboard
            // do they define correctly a class?
            if (!file_exists($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/version.php')) {
                $error = new stdClass();
                $error->key = 'invalidtypeorplugin';

                return $error;
            }

            if (($versiondisk["$currentplugin"] != $currentversion)) {
                $a = new stdClass();
                $a->type = $currenttype;
                $a->plugin = $currentplugin;
                $a->currentversion = $currentversion;
                $a->versiondisk = $versiondisk["$currentplugin"];

                $error = new stdClass();
                $error->a = $a;
                $error->key = 'versionmismatch';

                return $error;
            }

            foreach ($xmlitem->children() as $xmltable) {
                $tablename = $xmltable->getName();

                // I am assuming that surveypro_item table is ALWAYS before the surveypro_<<plugin>> table
                if ($tablename == 'surveypro_item') {
                    if (!isset($xmltable->children()->sortindex)) {
                        $error = new stdClass();
                        $error->key = 'missingsortindex';

                        return $error;
                    } else {
                        $sortindex = (string)$xmltable->children()->sortindex;
                        if ($sortindex != ($lastsortindex + 1)) {
                            $error = new stdClass();
                            $error->key = 'wrongsortindex';

                            return $error;
                        }
                        $lastsortindex = $sortindex;
                    }

                    // I could use a random class here because they all share the same parent item_get_item_schema
                    // but, I need the right class name for the next table, so I start loading the correct class now
                    require_once($CFG->dirroot.'/mod/surveypro/'.$currenttype.'/'.$currentplugin.'/plugin.class.php');
                    $classname = 'mod_surveypro_'.$currenttype.'_'.$currentplugin;
                    $xsd = $classname::item_get_item_schema(); // <- itembase schema
                } else {
                    // $classname is already onboard because of the previous loop over surveypro_item fields
                    $xsd = $classname::item_get_plugin_schema(); // <- plugin schema
                }

                if (empty($xsd)) {
                    $error = new stdClass();
                    $error->key = 'xsdnotfound';

                    return $error;
                }

                $mdom = new DOMDocument();
                $status = $mdom->loadXML($xmltable->asXML());

                // Let's capture errors
                $olderrormode = libxml_use_internal_errors(true);

                // Clear XML error flag so that we don't incorrectly report failure
                // when a previous xml parse failed
                libxml_clear_errors();

                if ($debug) {
                    $status = $status && $mdom->schemaValidateSource($xsd);
                } else {
                    $status = $status && @$mdom->schemaValidateSource($xsd);
                }

                // Check for errors
                $errors = libxml_get_errors();

                // Stop capturing errors
                libxml_use_internal_errors($olderrormode);

                if (!empty($errors)) {
                    $firsterror = reset($errors);
                    $a = sprintf('%s as required by the xsd of the "%s" plugin', trim($firsterror->message, "\n\r\t ."), $currentplugin);

                    $error = new stdClass();
                    $error->a = $a;
                    $error->key = 'reportederror';

                    return $error;
                }

                if (!$status) {
                    // Stop here. It is useless to continue
                    if ($debug) {
                        echo '<hr /><textarea rows="10" cols="100">'.$xmltable->asXML().'</textarea>';
                        echo '<textarea rows="10" cols="100">'.$xsd.'</textarea>';
                    }

                    $error = new stdClass();
                    $error->key = 'schemavalidationfailed';

                    return $error;
                }
            }
        }

        return false;
    }
}
