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

define('SURVEYPROTEMPLATE_NAMEPLACEHOLDER', 'templatemaster');

/**
 * The class representing a master template
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
     * Display the welcome message of the apply page.
     *
     * @return void
     */
    public function welcome_apply_message() {
        global $OUTPUT;

        $message = get_string('welcome_mtemplateapply', 'mod_surveypro');
        echo $OUTPUT->notification($message, 'notifymessage');
    }

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
        $pluginname = strtolower(preg_replace('~[\d ]*~', '', $pluginname));

        // Before starting, clean the destination folder
        // just in case it is not empty as expected.
        $tempsubdir = 'mod_surveypro/surveyproplugins/'.$pluginname;
        $tempbasedir = $CFG->tempdir.'/'.$tempsubdir;
        fulldelete($tempbasedir);

        $masterbasepath = "$CFG->dirroot/mod/surveypro/templatemaster";
        $masterfilelist = get_directory_list($masterbasepath);

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary.
        $xmlcontent = $this->write_template_content();
        $xmlcontent = str_replace("\r\n", "\n", $xmlcontent); // Fix line ending.

        // Before starting, verify that the current structure of templatemaster folder === structure expected here.
        $templatemastercontent = array(
            'classes/template.php',
            'lang/en/surveyprotemplate_pluginname.php',
            'pix/icon.png',
            'pix/icon.svg',
            'template.xml',
            'version.php'
        );

        if ($masterfilelist !== $templatemastercontent) {
            $message = 'The "templatemaster" folder does not match the expected one. This is a security issue. I must stop.';
            debugging($message, DEBUG_DEVELOPER);

            $paramurl = array();
            $paramurl['id'] = $this->cm->id;
            $returnurl = new moodle_url('/mod/surveypro/layout_itemlist.php', $paramurl);
            redirect($returnurl);
        }

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

            if ($masterfileinfo['basename'] == 'icon.png') {
                // Simply copy icon.png.
                copy($masterbasepath.'/'.$masterfile, $tempfullpath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['basename'] == 'icon.svg') {
                // Simply copy icon.svg.
                copy($masterbasepath.'/'.$masterfile, $tempfullpath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['dirname'] == 'classes') {
                $templateclass = file_get_contents($masterbasepath.'/'.$masterfile);
                $templateclass = str_replace("\r\n", "\n", $templateclass); // Fix line ending.
                // Replace surveyproTemplatePluginMaster with the name of the current surveypro.
                $templateclass = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $templateclass);

                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/classes/'.$masterfileinfo['basename'];

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
                $savedstrings = str_replace("\r\n", "\n", $savedstrings); // Fix line ending.

                // Create - this could be 'en' such as 'it'.
                $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                // Append all the $string['xxx'] = 'yyy' rows.
                fwrite($filehandler, $savedstrings);
                // Close.
                fclose($filehandler);

                // This is the folder of the language en in case the user language is different from en.
                if ($userlang != 'en') {
                    // Write inside all the strings in teh form: 'english translation of $string[stringxx]'.
                    $savedstrings = $filecopyright.$this->get_translated_strings($userlang);
                    $savedstrings = str_replace("\r\n", "\n", $savedstrings); // Fix line ending.

                    $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/en';
                    // Create.
                    $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                    // Save into surveyprotemplate_<<$pluginname>>.php.
                    fwrite($filehandler, $savedstrings);
                    // Close.
                    fclose($filehandler);
                }
                continue;
            }

            // For all the other files... like, for instance, version.php.

            // Read the master.
            $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
            $filecontent = str_replace("\r\n", "\n", $filecontent); // Fix line ending.
            // Replace surveyproTemplatePluginMaster with the name of the current surveypro.
            $filecontent = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecontent);

            if ($masterfileinfo['basename'] == 'version.php') {
                $defaultversion = '$plugin->version = 1965100401;';
                $currentversion = '$plugin->version = '.gmdate("Ymd").'01;';
                $filecontent = str_replace($defaultversion, $currentversion, $filecontent);

                $requires = get_config('moodle', 'version');
                $defaultrequires = '$plugin->requires = 1965100401;';
                $currentrequires = '$plugin->requires = '.$requires.';';
                $filecontent = str_replace($defaultrequires, $currentrequires, $filecontent);
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
            'version.php',
            'classes/template.php',
            'lang/en/surveyprotemplate_'.$pluginname.'.php',
            'pix/icon.png',
            'pix/icon.svg'
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

        $dirnames = array('classes', 'lang/en/', 'pix/', );
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

            if ($multilangfields = $item->item_get_multilang_fields()) { // Pagebreak and fieldsetend have no multilang_fields.
                $this->build_langtree($multilangfields, $item);
            }

            $structure = $this->get_table_structure();
            foreach ($structure as $field) {
                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        // Store the sortindex of the parent instead of its id, because at restore time parentid will change.
                        $whereparams = array('id' => $parentid);
                        $sortindex = $DB->get_field('surveypro_item', 'sortindex', $whereparams, MUST_EXIST);
                        $val = $item->get_parentvalue();

                        $xmlparent = $xmltable->addChild('parent');
                        $xmlfield = $xmlparent->addChild('parentid', $sortindex);
                        $xmlfield = $xmlparent->addChild('parentvalue', $val);
                    } // Otherwise: It is empty, do not evaluate: jump.
                    continue;
                }
                if ($field == 'parentvalue') {
                    continue;
                }

                $val = $this->xml_get_field_content($item, 'item', $field, $multilangfields);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }

            // Child table.
            $xmltable = $xmlitem->addChild('surveypro'.$itemseed->type.'_'.$itemseed->plugin);

            $structure = $this->get_table_structure($itemseed->type, $itemseed->plugin);
            foreach ($structure as $field) {
                $val = $this->xml_get_field_content($item, $itemseed->plugin, $field, $multilangfields);

                if (strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, htmlspecialchars($val));
                    // Otherwise: It is empty, do not evaluate: jump.
                }

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

        // The case: $option == false if 100% waste of time
        // BUT BUT BUT...
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
     * @param string $plugin
     * @param string $field
     * @param array $multilangfields
     * @return void
     */
    public function xml_get_field_content($item, $plugin, $field, $multilangfields) {
        // 1a: Has the plugin $plugin multilang fields?.
        if (isset($multilangfields[$plugin])) {
            // 1b: If the field that is going to be assigned belongs to your multilang fields.
            if (in_array($field, $multilangfields[$plugin])) {
                $component = $plugin.'_'.$field;

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
        $classname = 'surveyprotemplate_'.$this->templatename.'_template';
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
            $item = surveypro_get_item($this->cm, $this->surveypro, 0, $currenttype, $currentplugin);

            foreach ($xmlitem->children() as $xmltable) { // Surveypro_item and surveypro_<<plugin>>.
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

                    // Tag <parent> always belong to surveypro_item table
                    if ($fieldname == 'parent') {
                        // echo '<h5>Count of attributes of the field '.$fieldname.': '.count($xmlfield->children()).'</h5>';
                        foreach ($xmlfield->children() as $xmlparentattribute) {
                            $fieldname = $xmlparentattribute->getName();
                            $fieldexists = in_array($fieldname, $currenttablestructure);
                            if ($fieldexists) {
                                $record->{$fieldname} = (string)$xmlparentattribute;
                            }
                        }
                        continue;
                    }

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
                        continue;
                    }

                    $fieldexists = in_array($fieldname, $currenttablestructure);
                    if ($fieldexists) {
                        $record->{$fieldname} = (string)$xmlfield;
                    }
                }

                unset($record->id);

                // Apply master template settings.
                list($tablename, $record) = $mastertemplate->apply_template_settings($tablename, $record, $config);

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
     * Append all the field that will have content derived from the lang files.
     *
     * @param array $multilangfields
     * @param object $item
     * @return void
     */
    public function build_langtree($multilangfields, $item) {
        foreach ($multilangfields as $plugin => $fieldnames) {
            foreach ($fieldnames as $fieldname) {
                $component = $plugin.'_'.$fieldname;
                if (isset($this->langtree[$component])) {
                    $index = count($this->langtree[$component]);
                } else {
                    $index = 0;
                }
                $stringindex = sprintf('%02d', 1 + $index);
                $content = str_replace("\r", '', $item->item_get_generic_property($fieldname));
                $this->langtree[$component][$component.'_'.$stringindex] = $content;
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
            foreach ($langbranch as $k => $originalstring) {
                if (empty($originalstring)) {
                    $stringsastext[] = '$string[\''.$k.'\'] = \'\';';
                } else {
                    $a->stringkey = $k;
                    $stringsastext[] = get_string('translatedstring', 'mod_surveypro', $a);
                }
            }
        }

        return "\n".implode("\n", $stringsastext);
    }
}
