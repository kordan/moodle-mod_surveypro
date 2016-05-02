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
 * Surveypro mastertemplate class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SURVEYPROTEMPLATE_NAMEPLACEHOLDER', '00templateNamePlaceholder00');

require_once($CFG->dirroot.'/mod/surveypro/classes/templatebase.class.php');

/**
 * The class representing a master tempalete
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_mastertemplate extends mod_surveypro_templatebase {

    /**
     * @var array
     */
    protected $langtree = array();

    /**
     * Download master template.
     *
     * @return void
     */
    public function download_mtemplate() {
        $this->templatename = $this->generate_mtemplate();
        $exportfilename = basename($this->templatename);
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$exportfilename\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate,post-check=0,pre-check=0');
        header('Pragma: public');
        $exportfilehandler = fopen($this->templatename, 'rb');
        print fread($exportfilehandler, filesize($this->templatename));
        fclose($exportfilehandler);
        unlink($this->templatename);
    }

    /**
     * Generate master template.
     *
     * @return void
     */
    public function generate_mtemplate() {
        global $CFG;

        $pluginname = clean_filename($this->formdata->mastertemplatename);
        $pluginname = str_replace(' ', '_', $pluginname);
        $tempsubdir = "mod_surveypro/surveyproplugins/$pluginname";
        $tempbasedir = $CFG->tempdir.'/'.$tempsubdir;

        $masterbasepath = "$CFG->dirroot/mod/surveypro/templatemaster";
        $masterfilelist = get_directory_list($masterbasepath);

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary.
        $xmlcontent = $this->write_template_content();

        foreach ($masterfilelist as $masterfile) {
            $masterfileinfo = pathinfo($masterfile);
            // Create the structure of the temporary folder.
            // The folder has to be created WITHOUT $CFG->tempdir/.
            $temppath = $tempsubdir.'/'.dirname($masterfile);
            make_temp_directory($temppath); // I just created the folder for the current plugin.

            $tempfullpath = $CFG->tempdir.'/'.$temppath;

            // echo '<hr />Operate on the file: '.$masterfile.'<br />';
            // echo $masterfileinfo["dirname"] . "<br />";
            // echo $masterfileinfo["basename"] . "<br />";
            // echo $masterfileinfo["extension"] . "<br />";
            // echo dirname($masterfile) . "<br />";

            if ($masterfileinfo['basename'] == 'icon.gif') {
                // Simply copy icon.gif.
                copy($masterbasepath.'/'.$masterfile, $tempfullpath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.class.php') {
                $templateclass = file_get_contents($masterbasepath.'/'.$masterfile);

                // Replace surveyproTemplatePluginMaster with the name of the current surveypro.
                $templateclass = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $templateclass);

                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // Create $temppath.
                $filehandler = fopen($temppath, 'w');
                // Write inside all the strings.
                fwrite($filehandler, $templateclass);
                // Close.
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.xml') {
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // Create $temppath.
                $filehandler = fopen($temppath, 'w');
                // Write inside all the strings.
                fwrite($filehandler, $xmlcontent);
                // Close.
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['dirname'] == 'lang/en') {
                // In which language the user is using Moodle?.
                $userlang = current_language();
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/'.$userlang;

                // This is the language folder of the strings hardcoded in the surveypro.
                // The folder lang/en already exist.
                if ($userlang != 'en') {
                    // I need to create the folder lang/it.
                    make_temp_directory($tempsubdir.'/lang/'.$userlang);
                }

                // echo '$masterbasepath = '.$masterbasepath.'<br />';

                $filecopyright = file_get_contents($masterbasepath.'/lang/en/surveyprotemplate_pluginname.php');
                // Replace surveyproTemplatePluginMaster with the name of the current surveypro.
                $filecopyright = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecopyright);

                $savedstrings = $filecopyright.$this->get_lang_file_content();

                // Create - this could be 'en' such as 'it'.
                $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                // Append all the $string['xxx'] = 'yyy' rows.
                fwrite($filehandler, $savedstrings);
                // Close.
                fclose($filehandler);

                // This is the folder of the language en in case the user language is different from en.
                if ($userlang != 'en') {
                    $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/en';
                    // Create.
                    $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                    // Write inside all the strings in teh form: 'english translation of $string[stringxx]'.
                    $savedstrings = $filecopyright.$this->get_translated_strings($userlang);
                    // Save into surveyprotemplate_<<$pluginname>>.php.
                    fwrite($filehandler, $savedstrings);
                    // Close.
                    fclose($filehandler);
                }
                continue;
            }

            // For all the other files: version.php.
            // Read the master.
            $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
            // Replace surveyproTemplatePluginMaster with the name of the current surveypro.
            $filecontent = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecontent);
            if ($masterfileinfo['basename'] == 'version.php') {
                $currentdate = gmdate("Ymd").'01';
                $filecontent = str_replace('1965100401', $currentdate, $filecontent);
            }
            // Open.
            $filehandler = fopen($tempbasedir.'/'.$masterfile, 'w');
            // Write.
            fwrite($filehandler, $filecontent);
            // Close.
            fclose($filehandler);
        }

        $filenames = array(
            'template.xml',
            'pix/icon.gif',
            'template.class.php',
            'version.php',
            'lang/en/surveyprotemplate_'.$pluginname.'.php',
        );
        if ($userlang != 'en') {
            $filenames[] = 'lang/'.$userlang.'/surveyprotemplate_'.$pluginname.'.php';
        }

        $filelist = array();
        foreach ($filenames as $filename) {
            $filelist[$filename] = $tempbasedir.'/'.$filename;
        }

        $exportfile = $tempbasedir.'.zip';
        file_exists($exportfile) && unlink($exportfile);

        $fp = get_file_packer('application/zip');
        $fp->archive_to_pathname($filelist, $exportfile);

        $dirnames = array('pix/', 'lang/en/');
        if ($userlang != 'en') {
            $dirnames[] = 'lang/'.$userlang.'/';
        }
        $dirnames[] = 'lang/';

        foreach ($filelist as $file) {
            unlink($file);
        }
        foreach ($dirnames as $dir) {
            rmdir($tempbasedir.'/'.$dir);
        }
        rmdir($tempbasedir);

        // Return the full path to the exported template file.
        return $exportfile;
    }

    /**
     * Write master template content.
     *
     * @param boolean $visiblesonly
     * @return void
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
            $item = surveypro_get_item($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $xmlitem->addAttribute('version', $versiondisk["$itemseed->plugin"]);

            // Surveypro_item.
            $xmltable = $xmlitem->addChild('surveypro_item');

            if ($multilangfields = $item->item_get_multilang_fields()) { // Pagebreak and fieldset have not multilang_fields.
                $this->build_langtree($multilangfields, $item);
            }

            $structure = $this->get_table_structure('surveypro_item');
            foreach ($structure as $field) {
                if (in_array($field, $uselessitemfields) !== false) {
                    continue;
                }
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

                $val = $this->xml_get_field_content($item, 'item', $field, $multilangfields);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }

            // Child table.
            $xmltable = $xmlitem->addChild('surveypro'.$itemseed->type.'_'.$itemseed->plugin);

            $structure = $this->get_table_structure('surveypro'.$itemseed->type.'_'.$itemseed->plugin);
            foreach ($structure as $field) {
                if (in_array($field, $uselesspluginfields)) {
                    continue;
                }

                $val = $this->xml_get_field_content($item, $itemseed->plugin, $field, $multilangfields);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, htmlspecialchars($val));
                    // Otherwise: It is empty, do not evaluate: jump.
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

        // $option == false if 100% waste of time BUT BUT BUT...
        // the output in the file is well written.
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
     * Get the content of a field for the XML file.
     *
     * @param object $item
     * @param string $dummyplugin
     * @param string $field
     * @param array $multilangfields
     * @return void
     */
    public function xml_get_field_content($item, $dummyplugin, $field, $multilangfields) {
        // 1st: which fields are multilang for the current item?
        if (isset($multilangfields[$dummyplugin])) { // Has the plugin $dummyplugin multilang fields?.
            if (in_array($field, $multilangfields[$dummyplugin])) { // If the field that is going to be assigned belongs to your multilang fields.
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
            // It is empty, do not evaluate: jump.
            $val = null;
        }

        return $val;
    }

    /**
     * Apply template.
     *
     * @return void
     */
    public function apply_template() {
        global $DB, $CFG;

        $this->trigger_event('mastertemplate_applied');

        // Begin of: delete all existing items.
        require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');
        $utilityman = new mod_surveypro_utility($this->cm);
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $utilityman->delete_items($whereparams);
        // End of: delete all existing items.

        $this->templatename = $this->formdata->mastertemplate;
        $record = new stdClass();

        $record->id = $this->surveypro->id;
        $record->template = $this->templatename;
        $DB->update_record('surveypro', $record);

        $this->add_items_from_template();

        $paramurl = array('s' => $this->surveypro->id);
        $redirecturl = new moodle_url('/mod/surveypro/layout_preview.php', $paramurl);
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
    }

    /**
     * Actually add items coming from template to the db.
     *
     * @return void
     */
    public function add_items_from_template() {
        global $CFG, $DB;

        // Create the class to apply mastertemplate settings.
        require_once($CFG->dirroot.'/mod/surveypro/template/'.$this->templatename.'/template.class.php');
        $classname = 'mod_surveypro_template_'.$this->templatename;
        $mastertemplate = new $classname();

        $fs = get_file_storage();

        $templatepath = $CFG->dirroot.'/mod/surveypro/template/'.$this->templatename.'/template.xml';
        $templatecontent = file_get_contents($templatepath);

        $simplexml = new SimpleXMLElement($templatecontent);

        if (!$sortindexoffset = $DB->get_field('surveypro_item', 'MAX(sortindex)', array('surveyproid' => $this->surveypro->id))) {
            $sortindexoffset = 0;
        }

        // Load it only once. You are going to use it later.
        $config = get_config('surveyprotemplate_'.$this->templatename);

        $naturalsortindex = 0;
        foreach ($simplexml->children() as $xmlitem) {

            // Read the attributes of the item node.
            foreach ($xmlitem->attributes() as $attribute => $value) {
                // The $xmlitem looks like: <item type="format" plugin="label" version="2014030201">.
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
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Surveypro_item and surveypro_<<plugin>>.
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

                    // Tag <embedded> always belong to surveypro(field|format)_<<plugin>> table.
                    // So: ($fieldname == 'embedded') only when surveypro_item has already been saved.
                    // So: $itemid is known.
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
                        $fieldexists = in_array($fieldname, $currenttablestructure);
                        if ($fieldexists) {
                            $record->{$fieldname} = (string)$xmlfield;
                        }
                    }
                }

                unset($record->id);

                // Apply master template settings.
                list($tablename, $record) = $mastertemplate->apply_template_settings($tablename, $record, $config);

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
     * Append all the field that will have content derived from the lang files.
     *
     * @param array $multilangfields
     * @param object $item
     * @return void
     */
    public function build_langtree($multilangfields, $item) {
        foreach ($multilangfields as $dummyplugin => $fieldnames) {
            foreach ($fieldnames as $fieldname) {
                $component = $dummyplugin.'_'.$fieldname;
                if (isset($this->langtree[$component])) {
                    $index = count($this->langtree[$component]);
                } else {
                    $index = 0;
                }
                $stringindex = sprintf('%02d', 1 + $index);
                $this->langtree[$component][$component.'_'.$stringindex] = str_replace("\r", '', $item->item_get_generic_property($fieldname));
            }
        }
    }

    /**
     * Generate the array of strings for the lang file of the mastertemplate plugin.
     *
     * @return void
     */
    public function get_lang_file_content() {
        $stringsastext = array();
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                // Do not use php addslashes() because it adds slashes to " too.
                $stringcontent = str_replace("'",  "\\'", $stringcontent);
                $stringsastext[] = '$string[\''.$k.'\'] = \''.$stringcontent.'\';';
            }
        }

        return "\n".implode("\n", $stringsastext);
    }

    /**
     * Trigger the provided event.
     *
     * @param string $eventname Event to trigger
     * @return void
     */
    public function trigger_event($eventname) {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $eventdata['other'] = array('templatename' => $this->formdata->mastertemplate);
        switch ($eventname) {
            case 'mastertemplate_applied':
                $event = \mod_surveypro\event\mastertemplate_applied::create($eventdata);
                break;
            case 'mastertemplate_saved': // Sometimes called 'downloaded' too.
                $event = \mod_surveypro\event\mastertemplate_saved::create($eventdata);
                break;
            default:
                $message = 'Unexpected $event = '.$event;
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. '.$message , DEBUG_DEVELOPER);
        }
        $event->trigger();
    }

    // MARK get.

    /**
     * Get translated strings.
     *
     * @param string $userlang
     * @return void
     */
    public function get_translated_strings($userlang) {
        $stringsastext = array();
        $a = new stdClass();
        $a->userlang = $userlang;
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $unused) {
                $a->stringkey = $k;
                $stringsastext[] = get_string('translatedstring', 'mod_surveypro', $a);
            }
        }

        return "\n".implode("\n", $stringsastext);
    }
}
