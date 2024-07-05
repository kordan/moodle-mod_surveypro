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
 * Surveypro mtemplate_save class.
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
class mtemplate_save extends mtemplate_base {

    /**
     * @var array
     */
    protected $langtree = [];

    // MARK get.

    /**
     * Get translated strings.
     *
     * @param string $userlang
     * @return void
     */
    public function get_translated_strings($userlang) {
        $stringsastext = [];
        $a = new \stdClass();
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

        return "\n".implode("\n", $stringsastext)."\n";
    }

    /**
     * Generate the array of strings for the lang file of the mastertemplate plugin.
     *
     * @return void
     */
    public function get_lang_file_content() {
        $stringsastext = [];
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                // Do not use php addslashes() because it adds slashes to " too.
                $stringcontent = str_replace("'",  "\\'", $stringcontent);
                $stringsastext[] = '$string[\''.$k.'\'] = \''.$stringcontent.'\';';
            }
        }

        return "\n".implode("\n", $stringsastext)."\n";
    }

    /**
     * Provide correct plugin name
     *
     * @return string $pluginname
     */
    public function get_plugin_name() {
        $pluginname = clean_param($this->formdata->mastertemplatename, PARAM_FILE);
        $pluginname = strtolower($pluginname);

        // Only lower case letters at the beginning.
        $pluginname = preg_replace('~^[^a-z]*~', '', $pluginname);

        // Never spaces.
        $pluginname = preg_replace('~[ ]*~', '', $pluginname);

        // Replace dash with underscore.
        $pluginname = str_replace('-', '_', $pluginname);

        // Never double underscore.
        while (strpos($pluginname, '__') !== false) {
            $pluginname = str_replace('__', '_', $pluginname);
        }

        // User wrote a 100% bloody name. GRRRR.
        $condition1 = (bool)core_text::strlen($pluginname);
        $condition2 = (bool)preg_match_all('~[a-z]~', $pluginname);
        $condition = !($condition1 && $condition2);
        if ($condition) {
            // This test provides a 100% correct name. I do not need to iterate it.
            $this->formdata->mastertemplatename = 'mtemplate_'.$this->surveypro->name;
            $pluginname = $this->get_plugin_name();
        }

        return $pluginname;
    }

    // MARK other.

    /**
     * Append all the field that will have content derived from the lang files.
     *
     * @param array $multilangfields
     * @param object $item
     * @return void
     */
    public function build_langtree($multilangfields, $item) {
        global $DB;

        $itemid = $item->get_itemid();
        $plugin = $item->get_plugin();
        foreach ($multilangfields as $table => $multilanfields) {
            foreach ($multilanfields as $multilanfield) {
                // Filename and filecontent are multilang fields BUT I they may not be set.
                if ( ($multilanfield == 'filename') || ($multilanfield == 'filecontent') ) {
                    continue;
                }

                // Key.
                $key = $plugin.'_'.$multilanfield;
                if (isset($this->langtree[$key])) {
                    $index = count($this->langtree[$key]);
                } else {
                    $index = 0;
                }
                $stringindex = sprintf('%02d', 1 + $index);

                // Value.
                if ($multilanfield == 'content') {
                    $value = $DB->get_field('surveypro_item', 'content', ['id' => $itemid], MUST_EXIST);
                } else {
                    $value = str_replace("\r", '', $item->get_generic_property($multilanfield));
                }
                $this->langtree[$key][$key.'_'.$stringindex] = $value;
            }
        }
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

        $pluginname = $this->get_plugin_name();

        // Before starting, clean the destination folder
        // just in case it is not empty as expected.
        $datarelativedir = 'mod_surveypro/surveyproplugins/'.$pluginname;
        $dataabsolutedir = $CFG->tempdir.'/'.$datarelativedir;
        fulldelete($dataabsolutedir);

        $masterbasepath = "$CFG->dirroot/mod/surveypro/templatemaster";
        $masterfilelist = get_directory_list($masterbasepath);

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary.
        $xmlcontent = $this->write_template_content();
        $xmlcontent = str_replace("\r\n", "\n", $xmlcontent); // Fix line ending.

        // Before starting, verify that the current structure of templatemaster folder === structure expected here.
        $templatemastercontent = [
            'classes/privacy/provider.php',
            'classes/template.php',
            'lang/en/surveyprotemplate_pluginname.php',
            'pix/icon.png',
            'pix/icon.svg',
            'template.xml',
            'version.php',
        ];

        if (array_diff($masterfilelist, $templatemastercontent) || array_diff($templatemastercontent, $masterfilelist)) {
            $message = 'The "templatemaster" folder does not match the expected one. This is a security issue. I must stop.';
            debugging($message, DEBUG_DEVELOPER);

            $paramurl = ['s' => $this->cm->instance, 'section' => 'itemslist'];
            $returnurl = new \moodle_url('/mod/surveypro/layout.php', $paramurl);
            redirect($returnurl);
        }

        foreach ($masterfilelist as $masterfile) {
            $masterfileinfo = pathinfo($masterfile);
            // Create the structure of the temporary folder.
            // The folder has to be created WITHOUT $CFG->tempdir/.
            $temppath = $datarelativedir.'/'.dirname($masterfile);
            make_temp_directory($temppath); // I just created the folder for the current plugin.

            $dataabsolutepath = $CFG->tempdir.'/'.$temppath;

            if ($masterfileinfo['basename'] == 'icon.png') {
                // Simply copy icon.png.
                copy($masterbasepath.'/'.$masterfile, $dataabsolutepath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['basename'] == 'icon.svg') {
                // Simply copy icon.svg.
                copy($masterbasepath.'/'.$masterfile, $dataabsolutepath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if (preg_match('~^classes~', $masterfileinfo['dirname'])) {
                // Here I deal with 'classes/privacy/provider.php' or 'classes/template.php'.
                $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
                $filecontent = str_replace("\r\n", "\n", $filecontent); // Fix line ending.

                // Replace 'package   mod_surveypro' with 'package   surveyprotemplate_'.$pluginname.
                $filecontent = $this->replace_package($filecontent, $pluginname);

                $temppath = $CFG->tempdir.'/'.$datarelativedir.'/'.$masterfile;

                // Create $temppath.
                $filehandler = fopen($temppath, 'w');
                // Write inside all the strings.
                fwrite($filehandler, $filecontent);
                // Close.
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.xml') {
                $temppath = $CFG->tempdir.'/'.$datarelativedir.'/'.$masterfileinfo['basename'];

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
                $temppath = $CFG->tempdir.'/'.$datarelativedir.'/lang/'.$userlang;

                // This is the language folder of the strings hardcoded in surveypro.
                // The folder lang/en already exist.
                if ($userlang != 'en') {
                    // I need to create the folder lang/it.
                    make_temp_directory($datarelativedir.'/lang/'.$userlang);
                }

                $filecontent = file_get_contents($masterbasepath.'/lang/en/surveyprotemplate_pluginname.php');

                // Replace 'package   mod_surveypro' with 'package   surveyprotemplate_'.$pluginname.
                $filecontent = $this->replace_package($filecontent, $pluginname);

                $savedstrings = $filecontent.$this->get_lang_file_content();
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
                    $savedstrings = $filecontent.$this->get_translated_strings($userlang);
                    $savedstrings = str_replace("\r\n", "\n", $savedstrings); // Fix line ending.

                    $temppath = $CFG->tempdir.'/'.$datarelativedir.'/lang/en';
                    // Create.
                    $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                    // Save into surveyprotemplate_<<$pluginname>>.php.
                    fwrite($filehandler, $savedstrings);
                    // Close.
                    fclose($filehandler);
                }
                continue;
            }

            if ($masterfileinfo['basename'] == 'version.php') {
                // Read the master.
                $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
                $filecontent = str_replace("\r\n", "\n", $filecontent); // Fix line ending.

                // Replace 'package   mod_surveypro' with 'package   surveyprotemplate_'.$pluginname.
                $filecontent = $this->replace_package($filecontent, $pluginname);

                $oldstring = '$plugin->version = 1965100401;';
                $newstring = '$plugin->version = '.gmdate("Ymd").'01;';
                $filecontent = str_replace($oldstring, $newstring, $filecontent);

                $requires = get_config('moodle', 'version');
                $oldstring = '$plugin->requires = 1965100401;';
                $newstring = '$plugin->requires = '.$requires.';';
                $filecontent = str_replace($oldstring, $newstring, $filecontent);

                // Create.
                $filehandler = fopen($dataabsolutedir.'/'.$masterfile, 'w');
                // Write.
                fwrite($filehandler, $filecontent);
                // Close.
                fclose($filehandler);
            }
        }

        $filenames = [
            'template.xml',
            'version.php',
            'classes/template.php',
            'classes/privacy/provider.php',
            'lang/en/surveyprotemplate_'.$pluginname.'.php',
            'pix/icon.png',
            'pix/icon.svg',
        ];
        if ($userlang != 'en') {
            $filenames[] = 'lang/'.$userlang.'/surveyprotemplate_'.$pluginname.'.php';
        }

        $filelist = [];
        foreach ($filenames as $filename) {
            $filelist[$filename] = $dataabsolutedir.'/'.$filename;
        }

        $exportfile = $dataabsolutedir.'.zip';
        file_exists($exportfile) && unlink($exportfile);

        $fp = get_file_packer('application/zip');
        $fp->archive_to_pathname($filelist, $exportfile);

        // Zip file has been created. Now clean the temporary folder.
        $dirnames = ['classes/privacy/', 'classes/', 'lang/en/', 'pix/'];
        if ($userlang != 'en') {
            $dirnames[] = 'lang/'.$userlang.'/';
        }
        $dirnames[] = 'lang/';

        foreach ($filelist as $file) {
            unlink($file);
        }
        foreach ($dirnames as $dir) {
            rmdir($dataabsolutedir.'/'.$dir);
        }
        rmdir($dataabsolutedir);

        // Return the full path to the exported template file.
        return $exportfile;
    }

    /**
     * Replace package.
     *
     * // Replace 'package   mod_surveypro' with 'package   surveyprotemplate_'.$pluginname
     * // Replace ' templatemaster' with $pluginname
     *
     * @param string $filecontent
     * @param string $pluginname
     * @return string $filecontent
     */
    public function replace_package($filecontent, $pluginname) {
        $oldstring = 'templatemaster';
        $newstring = $pluginname;
        $filecontent = str_replace($oldstring, $newstring, $filecontent);

        $oldstring = ' * @package   mod_surveypro';
        $newstring = ' * @package   surveyprotemplate_'.$pluginname;
        $filecontent = str_replace($oldstring, $newstring, $filecontent);

        return $filecontent;
    }

    /**
     * Add to langtree as many elements as the number of files found among content fields.
     *
     * If in the frame of the content of an item I find 5 embedded images, I add 5 extra elements to langtree.
     * I can not use the standard procedure written in build_langtree because I don't know what I will find.
     *
     * @param string $plugin
     * @param string $field
     * @param string $content
     * @return string $stringindex
     */
    public function add_entry_in_langtree($plugin, $field, $content) {
        $key = $plugin.'_'.$field;
        if (isset($this->langtree[$key])) {
            $index = count($this->langtree[$key]);
        } else {
            $index = 0;
        }
        $stringindex = sprintf('%02d', 1 + $index);
        $val = $key.'_'.$stringindex;
        $this->langtree[$key][$val] = $content;

        return $val;
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

        $where = ['surveyproid' => $this->surveypro->id];
        if ($visiblesonly) {
            $where['hidden'] = '0';
        }
        $itemseeds = $DB->get_records('surveypro_item', $where, 'sortindex', 'id, type, plugin');

        $fs = get_file_storage();
        $context = \context_module::instance($this->cm->id);

        $xmltemplate = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><items></items>');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_itemclass($this->cm, $this->surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            $xmlitem = $xmltemplate->addChild('item');
            $xmlitem->addAttribute('type', $itemseed->type);
            $xmlitem->addAttribute('plugin', $itemseed->plugin);
            $index = $itemseed->type.'_'.$itemseed->plugin;
            $xmlitem->addAttribute('version', $pluginversion[$index]);

            // Surveypro_item.
            $structure = $this->get_table_structure();
            $unrelevantfields = ['id', 'surveyproid', 'type', 'plugin', 'sortindex', 'formpage', 'timecreated', 'timemodified'];
            $unrelevantfields = array_merge($unrelevantfields, $item->item_expected_null_fields());
            $xmltable = $xmlitem->addChild('surveypro_item');

            if ($multilangfields = $item->get_multilang_fields()) { // Pagebreak and fieldsetend have no multilang_fields.
                $this->build_langtree($multilangfields, $item);
            }
            foreach ($structure as $field) {
                if (in_array($field, $unrelevantfields)) {
                    continue;
                }

                if ($field == 'content') {
                    // If $field == 'content' I can not use the property of the object $item because
                    // in case of pictures, for instance, $item->content looks like:
                    // '<img src="@@PLUGINFILE@@/img1.png" alt="MMM" width="313" height="70">'
                    // and not like:
                    // '<img src="http://localhost:8888/master/pluginfile.php/198/mod_surveypro/itemcontent/1960/img1.png" alt=...
                    // $val = $DB->get_field('surveypro_item', 'content', ['id' => $itemseed->id], MUST_EXIST);
                    // $val = $DB->get_field('surveypro_item', 'content', ['id' => $itemseed->id], MUST_EXIST);
                    // if (core_text::strlen($val)) {
                    // $xmlfield = $xmltable->addChild('content', htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE));
                    // }

                    $val = $this->xml_get_field_content($item, $field, $multilangfields);
                    if (\core_text::strlen($val)) {
                        $xmlfield = $xmltable->addChild($field, htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE));
                    } // Otherwise: It is empty, do not evaluate: jump.

                    if ($files = $fs->get_area_files($context->id, 'mod_surveypro', SURVEYPRO_ITEMCONTENTFILEAREA, $itemseed->id)) {
                        foreach ($files as $file) {
                            $filename = $file->get_filename();
                            if ($filename == '.') {
                                continue;
                            }
                            $xmlembedded = $xmltable->addChild('embedded');

                            // Add an entry in langtree for filename.
                            $val = $this->add_entry_in_langtree($itemseed->plugin, 'filename', $filename);
                            // End of: add corresponding string in langtree.

                            // $val = $this->xml_get_field_content($item, 'filename', $multilangfields);
                            $xmlembedded->addChild('filename', htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE));
                            // $xmlembedded->addChild('filename', $filename);

                            // Add corresponding string in langtree.
                            $content = base64_encode($file->get_content());
                            $val = $this->add_entry_in_langtree($itemseed->plugin, 'filecontent', $content);
                            // End of: add corresponding string in langtree.

                            // $val = $this->xml_get_field_content($item, 'filecontent', $multilangfields);
                            $xmlembedded->addChild('filecontent', htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE));
                            // $xmlembedded->addChild('filecontent', $content);
                        }
                    }
                    continue;
                }

                if ($field == 'parentid') {
                    $parentid = $item->get_parentid();
                    if ($parentid) {
                        // Store the sortindex of the parent instead of its id, because at restore time parentid will change.
                        $whereparams = ['id' => $parentid];
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

                $val = $this->xml_get_field_content($item, $field, $multilangfields);
                $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE);
                if (\core_text::strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }

            // Child table.
            $tablename = 'surveypro'.$itemseed->type.'_'.$itemseed->plugin;
            $structure = $this->get_table_structure($itemseed->type, $itemseed->plugin);

            // Take care: some items plugin may be free of their own specific table.
            if (!count($structure)) {
                continue;
            }

            $unrelevantfields = ['id', 'itemid'];
            $xmltable = $xmlitem->addChild($tablename);
            foreach ($structure as $field) {

                if (in_array($field, $unrelevantfields)) {
                    continue;
                }

                $val = $this->xml_get_field_content($item, $field, $multilangfields);
                $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE);
                if (\core_text::strlen($val)) {
                    $xmlfield = $xmltable->addChild($field, $val);
                } // Otherwise: It is empty, do not evaluate: jump.
            }
        }

        // In the coming code, "$option == false;" if 100% waste of time and should be changed to "$option == true;"
        // BUT BUT BUT...
        // the output in the file is well written.
        // I prefer a more readable xml file instead of few nanoseconds saved.
        $option = false;
        if ($option) {
            return $xmltemplate->asXML();
        } else {
            $dom = new \DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xmltemplate->asXML());

            return $dom->saveXML();
        }
    }

    /**
     * Get the content of a field for the XML file.
     * This tree is needed to build the lang files of the master template.
     * The idea is:
     *     if the field am I dialing with is a multilang field {
     *         return the key of the lang file
     *     }
     *     if the field am I dialing with is NOT a multilang field {
     *         return the content of the field
     *     }
     *
     * Once this function returns its output the XML describing the surveypro of this master template will be:
     *     <content>boolean_content_01</content>
     * or
     *     <hiddenfield>0</hiddenfield>
     *
     * @param object $itemclass The class needed to call $itemclass->get_generic_property($field);
     * @param string $field
     * @param array $multilangfields
     * @return string the key for the lang file of the direct content of the file
     */
    public function xml_get_field_content($itemclass, $field, $multilangfields) {
        // 1a: Has the current plugin multilang fields?.
        if (!empty($multilangfields)) { // Pagebrak and fieldsetend don't have $multilangfields.
            $plugin = $itemclass->get_plugin();
            foreach ($multilangfields as $table => $multilangfield) {
                // 1b: Is the field that is going to be assigned belongs to the multilang fields of this plugin?
                if (in_array($field, $multilangfield)) {
                    $key = $plugin.'_'.$field; // For instance: boolean_content

                    if (isset($this->langtree[$key])) { // Langtree has already been defined.
                        $index = count($this->langtree[$key]);
                        $stringindex = sprintf('%02d', $index);
                        $val = $key.'_'.$stringindex;
                        return $val;
                    }
                }
            }
        }

        $content = $itemclass->get_generic_property($field);
        if (\core_text::strlen($content)) {
            $val = $content;
        } else {
            // It is empty, do not evaluate: jump.
            $val = null;
        }

        return $val;
    }
}
