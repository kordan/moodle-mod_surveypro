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

define('SURVEYPROTEMPLATE_NAMEPLACEHOLDER', '@@templateNamePlaceholder@@');

require_once($CFG->dirroot.'/mod/surveypro/classes/templatebase.class.php');

class mod_surveypro_mastertemplate extends mod_surveypro_templatebase {
    /**
     * $templatetype
     */
    public $templatetype = SURVEYPRO_MASTERTEMPLATE;

    /**
     * $langtree
     */
    public $langtree = array();

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        parent::__construct($cm, $context, $surveypro);
    }

    /**
     * download_mtemplate
     *
     * @param none
     * @return
     */
    public function download_mtemplate() {
        $this->templatename = $this->create_mtemplate();
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
     * create_mtemplate
     *
     * @param none
     * @return
     */
    public function create_mtemplate() {
        global $CFG, $DB;

        $pluginname = clean_filename($this->formdata->mastertemplatename);
        $pluginname = str_replace(' ', '_', $pluginname);
        $tempsubdir = "mod_surveypro/surveyproplugins/$pluginname";
        $tempbasedir = $CFG->tempdir.'/'.$tempsubdir;

        $masterbasepath = "$CFG->dirroot/mod/surveypro/templatemaster";
        $masterfilelist = get_directory_list($masterbasepath);

        // I need to get xml content now because, to save time, I get xml AND $this->langtree contemporary
        $xmlcontent = $this->write_template_content();

        foreach ($masterfilelist as $masterfile) {
            $masterfileinfo = pathinfo($masterfile);
            // create the structure of the temporary folder
            // the folder has to be created WITHOUT $CFG->tempdir/
            $temppath = $tempsubdir.'/'.dirname($masterfile);
            make_temp_directory($temppath); // <-- just created the folder for the current plugin

            $tempfullpath = $CFG->tempdir.'/'.$temppath;

            // echo '<hr />Operate on the file: '.$masterfile.'<br />';
            // echo $masterfileinfo["dirname"] . "<br />";
            // echo $masterfileinfo["basename"] . "<br />";
            // echo $masterfileinfo["extension"] . "<br />";
            // echo dirname($masterfile) . "<br />";

            if ($masterfileinfo['basename'] == 'icon.gif') {
                // simply copy icon.gif
                copy($masterbasepath.'/'.$masterfile, $tempfullpath.'/'.$masterfileinfo['basename']);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.class.php') {
                $templateclass = file_get_contents($masterbasepath.'/'.$masterfile);

                // replace surveyproTemplatePluginMaster with the name of the current surveypro
                $templateclass = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $templateclass);

                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // create $temppath
                $filehandler = fopen($temppath, 'w');
                // write inside all the strings
                fwrite($filehandler, $templateclass);
                // close
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['basename'] == 'template.xml') {
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/'.$masterfileinfo['basename'];

                // create $temppath
                $filehandler = fopen($temppath, 'w');
                // write inside all the strings
                fwrite($filehandler, $xmlcontent);
                // close
                fclose($filehandler);
                continue;
            }

            if ($masterfileinfo['dirname'] == 'lang/en') {
                // in which language the user is using Moodle?
                $userlang = current_language();
                $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/'.$userlang;

                // this is the language folder of the strings hardcoded in the surveypro
                // the folder lang/en already exist
                if ($userlang != 'en') {
                    // I need to create the folder lang/it
                    make_temp_directory($tempsubdir.'/lang/'.$userlang);
                }

                // echo '$masterbasepath = '.$masterbasepath.'<br />';

                $filecopyright = file_get_contents($masterbasepath.'/lang/en/surveyprotemplate_pluginname.php');
                // replace surveyproTemplatePluginMaster with the name of the current surveypro
                $filecopyright = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecopyright);

                $savedstrings = $filecopyright.$this->extract_original_string();
                // echo '<textarea rows="30" cols="100">'.$savedstrings.'</textarea>';
                // die();

                // create - this could be 'en' such as 'it'
                $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                // write inside all the strings
                fwrite($filehandler, $savedstrings);
                // close
                fclose($filehandler);

                // this is the folder of the language en in case the user language is different from en
                if ($userlang != 'en') {
                    $temppath = $CFG->tempdir.'/'.$tempsubdir.'/lang/en';
                    // create
                    $filehandler = fopen($temppath.'/surveyprotemplate_'.$pluginname.'.php', 'w');
                    // write inside all the strings in teh form: 'english translation of $string[stringxx]'
                    $savedstrings = $filecopyright.$this->get_translated_strings($userlang);
                    // save into surveyprotemplate_<<$pluginname>>.php
                    fwrite($filehandler, $savedstrings);
                    // close
                    fclose($filehandler);
                }
                continue;
            }

            // for all the other files: version.php
            // read the master
            $filecontent = file_get_contents($masterbasepath.'/'.$masterfile);
            // replace surveyproTemplatePluginMaster with the name of the current surveypro
            $filecontent = str_replace(SURVEYPROTEMPLATE_NAMEPLACEHOLDER, $pluginname, $filecontent);
            if ($masterfileinfo['basename'] == 'version.php') {
                $currentdate = gmdate("Ymd").'01';
                $filecontent = str_replace('1965100401', $currentdate, $filecontent);
            }
            // open
            $filehandler = fopen($tempbasedir.'/'.$masterfile, 'w');
            // write
            fwrite($filehandler, $filecontent);
            // close
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

        // if (false) {
        foreach ($filelist as $file) {
            unlink($file);
        }
        foreach ($dirnames as $dir) {
            rmdir($tempbasedir.'/'.$dir);
        }
        rmdir($tempbasedir);
        // }

        // Return the full path to the exported template file:
        return $exportfile;
    }

    /**
     * get_used_plugin
     *
     * @param none
     * @return
     */
    public function get_used_plugin() {
        global $DB;

        // STEP 01: make a list of used plugins
        $sql = 'SELECT si.plugin
                FROM {surveypro_item} si
                WHERE si.surveyproid = :surveyproid
                GROUP BY si.plugin';
        $whereparams = array('surveyproid' => $this->surveypro->id);
        $templateplugins = $DB->get_records_sql($sql, $whereparams);

        // STEP 02: add, at top of $templateplugins, the fictitious 'item' plugin
        $base = new stdClass();
        $base->plugin = 'item';
        return array_merge(array('item' => $base), $templateplugins);
    }

    /**
     * build_langtree
     *
     * @param $currentsid
     * @param $values
     * @return
     */
    public function build_langtree($dummyplugin, $multilangfields, $item) {
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
     * extract_original_string
     *
     * @param none
     * @return
     */
    public function extract_original_string() {
        $stringsastext = array();
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                $stringsastext[] = '$string[\''.$k.'\'] = \''.addslashes($stringcontent).'\';';
            }
        }

        return "\n".implode("\n", $stringsastext);
    }

    /**
     * get_translated_strings
     *
     * @param $userlang
     * @return
     */
    public function get_translated_strings($userlang) {
        $stringsastext = array();
        $a = new stdClass();
        $a->userlang = $userlang;
        foreach ($this->langtree as $langbranch) {
            foreach ($langbranch as $k => $stringcontent) {
                $a->stringindex = $k;
                $stringsastext[] = get_string('translatedstring', 'surveypro', $a);
            }
        }
        return "\n".implode("\n", $stringsastext);
    }

    /**
     * trigger_event
     *
     * @param string $event: event to trigger
     * @return void
     */
    public function trigger_event($event) {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $eventdata['other'] = array('templatename' => $this->templatename);
        switch ($event) {
            case 'mastertemplate_applied':
                $event = \mod_surveypro\event\mastertemplate_applied::create($eventdata);
                break;
            case 'mastertemplate_saved': // sometimes called 'downloaded' too
                $event = \mod_surveypro\event\mastertemplate_saved::create($eventdata);
                break;
            default:
                debugging('Error at line '.__LINE__.' of '.__FILE__.'. Unexpected $event = '.$event, DEBUG_DEVELOPER);
        }
        $event->trigger();
    }
}
