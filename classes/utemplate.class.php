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
     * $templatetype
     */
    public $templatetype = SURVEYPRO_USERTEMPLATE;

    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $utemplateid: the ID of the current working user template
     */
    public $utemplateid = 0;

    /**
     * $confirm: is the action confirmed by the user?
     */
    public $confirm = SURVEYPRO_UNCONFIRMED;

    /**
     * $candownloadutemplates
     */
    public $candownloadutemplates = false;

    /**
     * $candeleteutemplates
     */
    public $candeleteutemplates = false;

    /**
     * $nonmatchingplugin
     */
    public $nonmatchingplugin = array();

    /**
     * Class constructor
     */
    public function __construct($cm, $context, $surveypro) {
        parent::__construct($cm, $context, $surveypro);

        $this->candownloadutemplates = has_capability('mod/surveypro:downloadusertemplates', $context, null, true);
        $this->candeleteutemplates = has_capability('mod/surveypro:deleteusertemplates', $context, null, true);
    }

    // MARK set

    /**
     * set_utemplateid
     *
     * @param $utemplateid
     * @return none
     */
    public function set_utemplateid($utemplateid) {
        $this->utemplateid = $utemplateid;
    }

    /**
     * set_action
     *
     * @param $action
     * @return none
     */
    public function set_action($action) {
        $this->action = $action;
    }

    /**
     * set_view
     *
     * @param $view
     * @return none
     */
    public function set_view($view) {
        $this->view = $view;
    }

    /**
     * set_confirm
     *
     * @param $confirm
     * @return none
     */
    public function set_confirm($confirm) {
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

        //       $parts[0]    |   $parts[1]
        //  ----------------------------------
        //     CONTEXT_SYSTEM | 0
        //  CONTEXT_COURSECAT | $category->id
        //     CONTEXT_COURSE | $COURSE->id
        //     CONTEXT_MODULE | $cm->id
        //       CONTEXT_USER | $USER->id

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
        // depending on the context level the component can be:
        // system, category, course, module, user
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
            if (has_capability('mod/surveypro:saveusertemplates', $context)) {
                $options[$context->contextlevel.'_'.$context->instanceid] = $context->get_context_name();
            }
        }

        $context = context_system::instance();
        if (has_capability('mod/surveypro:saveusertemplates', $context)) {
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
     * welcome_message
     *
     * @param none
     * @return none
     */
    public function welcome_message() {
        global $OUTPUT;

        $a = new stdClass();
        $a->usertemplate = get_string('usertemplateinfo', 'mod_surveypro');
        $a->none = get_string('notanyset', 'mod_surveypro');
        $a->action = get_string('action', 'mod_surveypro');
        $a->deleteallitems = get_string('deleteallitems', 'mod_surveypro');

        $message = get_string('applyutemplateinfo', 'mod_surveypro', $a);
        echo $OUTPUT->box($message, 'generaltable generalbox boxaligncenter boxwidthnormal');
    }

    /**
     * check_items_versions
     *
     * rationale: usertemplates are validated at upload time using validate_xml.
     * Mastertemplates are not validated "at the beginning" because they are never uploaded.
     * At application time, I only need check_items_versions for user templates
     * while I need the bigger validate_xml to validate mastertemplates for the first time.
     * The issue is that validate_xml includes a lite item version validation too.
     * So, to make effective code, I should call check_items_versions from within validate_xml
     * but check_items_versions uses $this->nonmatchingplugin that, I think,
     * is mostly useless in master templates
     *
     * @param none
     * @return none
     */
    public function check_items_versions() {
        if (empty($this->formdata->usertemplateinfo)) { // nothing was selected
            return;
        }

        $versiondisk = $this->get_plugin_versiondisk();
        $parts = explode('_', $this->formdata->usertemplateinfo);
        $this->utemplateid = $parts[1];
        $this->templatename = $this->get_utemplate_name();
        $templatecontent = $this->get_utemplate_content();

        $simplexml = new SimpleXMLElement($templatecontent);
        foreach ($simplexml->children() as $xmlitem) {
            $currentplugin = '';
            $currentversion = '';
            foreach ($xmlitem->attributes() as $attribute => $value) {
                if ($attribute == 'plugin') {
                    $currentplugin = $value;
                }
                if ($attribute == 'version') {
                    $currentversion = $value;
                }
            }

            if (empty($currentplugin)) {
                $currentplugin = get_string('missingplugin', 'mod_surveypro');
            }

            if (empty($currentversion)) {
                $currentversion = '-1';
            }

            // just to save few nanoseconds: continue if already pinned
            if (isset($this->nonmatchingplugin["$currentplugin"])) {
                // already pinned
                continue;
            }
            if (isset($versiondisk["$currentplugin"])) { // to be sure user didn't write a bad plugin or missed it
                if (($versiondisk["$currentplugin"] != $currentversion)) {
                    $this->nonmatchingplugin["$currentplugin"] = $versiondisk["$currentplugin"];
                }
            } else {
                // if the user wrote a bad plugin or missed it
                $this->nonmatchingplugin["$currentplugin"] = 0;
            }
        }

        return empty($this->nonmatchingplugin);
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

        // look at what is already on board
        $oldfiles = array();
        if ($files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            foreach ($files as $file) {
                $oldfiles[] = $file->get_filename();
            }
        }

        // add current files
        $fieldname = 'importfile';
        if ($draftitemid = $this->formdata->{$fieldname.'_filemanager'}) {
            if (isset($templateoptions['return_types']) && !($templateoptions['return_types'] & FILE_REFERENCE)) {
                // we assume that if $options['return_types'] is NOT specified, we DO allow references.
                // this is not exactly right. BUT there are many places in code where filemanager options
                // are not passed to file_save_draft_area_files()
                $allowreferences = false;
            }

            file_save_draft_area_files($draftitemid, $contextid, 'mod_surveypro', 'temporaryarea', 0, $templateoptions);
            $files = $fs->get_area_files($contextid, 'mod_surveypro', 'temporaryarea');
            $filecount = 0;
            foreach ($files as $file) {
                if (in_array($file->get_filename(), $oldfiles)) {
                    continue;
                }

                $filerecord = array('contextid' => $contextid, 'component' => 'mod_surveypro', 'filearea' => SURVEYPRO_TEMPLATEFILEAREA, 'itemid' => 0, 'timemodified' => time());
                if (!$templateoptions['subdirs']) {
                    if ($file->get_filepath() !== '/' or $file->is_directory()) {
                        continue;
                    }
                }
                if ($templateoptions['maxbytes'] and $templateoptions['maxbytes'] < $file->get_filesize()) {
                    // oversized file - should not get here at all
                    continue;
                }
                if ($templateoptions['maxfiles'] != -1 and $templateoptions['maxfiles'] <= $filecount) {
                    // more files - should not get here at all
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
                // only one file attached, set it as main file automatically
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

        $filerecord->filename = str_replace(' ', '_', $this->formdata->templatename);
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

        require_once($CFG->libdir.'/tablelib.php');

        // -----------------------------
        // $paramurlbase definition
        $paramurlbase = array();
        $paramurlbase['id'] = $this->cm->id;
        // end of $paramurlbase definition
        // -----------------------------

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
        $table->sortable(true, 'templatename'); // sorted by sortindex by default
        $table->no_sorting('actions');

        $table->column_class('templatename', 'templatename');
        $table->column_class('sharinglevel', 'sharinglevel');
        $table->column_class('timecreated', 'timecreated');
        $table->column_class('actions', 'actions');

        // general properties for the whole table
        // $table->set_attribute('cellpadding', '5');
        $table->set_attribute('id', 'managetemplates');
        $table->set_attribute('class', 'generaltable');
        // $table->set_attribute('width', '90%');
        $table->setup();

        $applytitle = get_string('applytemplate', 'mod_surveypro');
        $deletetitle = get_string('delete');
        $exporttitle = get_string('exporttemplate', 'mod_surveypro');

        $options = $this->get_sharinglevel_options($this->cm->id);

        // echo '$options:';
        // var_dump($options);

        $templates = new stdClass();
        foreach ($options as $sharinglevel => $v) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];

            $contextid = $this->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $this->get_contextstring_from_sharinglevel($contextlevel);
            $templates->{$contextstring} = $this->get_available_templates($contextid);
        }
        // echo '$templates:';
        // var_dump($templates);

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
                // *************************************** SURVEYPRO_DELETEUTEMPLATE
                if ($this->candeleteutemplates) {
                    if ($xmlfile->get_userid() == $USER->id) { // only the owner can delete his/her template
                        $paramurl = $paramurlbase;
                        $paramurl['act'] = SURVEYPRO_DELETEUTEMPLATE;
                        $paramurl['sesskey'] = sesskey();

                        $icons .= $OUTPUT->action_icon(new moodle_url('/mod/surveypro/utemplates_manage.php', $paramurl),
                            new pix_icon('t/delete', $deletetitle, 'moodle', array('title' => $deletetitle)),
                            null, array('title' => $deletetitle));
                    }
                }

                // *************************************** SURVEYPRO_EXPORTUTEMPLATE
                if ($this->candownloadutemplates) {
                    $paramurl = $paramurlbase;
                    $paramurl['view'] = SURVEYPRO_EXPORTUTEMPLATE;

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
    public function create_fictitious_table($templates, $usersort) {
        // original table per columns: originaltablepercols
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

        // original table per rows: originaltableperrows
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
            // ask for confirmation
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
        // $action
        if ($this->action != SURVEYPRO_NOACTION) {
            require_sesskey();
        }
        if ($this->action == SURVEYPRO_DELETEUTEMPLATE) {
            require_capability('mod/surveypro:deleteusertemplates', $this->context);
        }
        if ($this->action == SURVEYPRO_DELETEALLITEMS) {
            require_capability('mod/surveypro:manageusertemplates', $this->context);
        }
        if ($this->view == SURVEYPRO_EXPORTUTEMPLATE) {
            require_capability('mod/surveypro:downloadusertemplates', $this->context);
        }
    }

    /**
     * trigger_event
     *
     * @param string $event: event to trigger
     * @return none
     */
    public function trigger_event($eventname) {
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        switch ($eventname) {
            case 'all_usertemplates_viewed':
                $event = \mod_surveypro\event\all_usertemplates_viewed::create($eventdata);
                break;
            case 'usertemplate_applied':
                $eventdata['other'] = array('templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_applied::create($eventdata);
                break;
            case 'usertemplate_exported':
                $eventdata['other'] = array('view' => SURVEYPRO_EXPORTUTEMPLATE, 'templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_exported::create($eventdata);
                break;
            case 'usertemplate_saved':
                $eventdata['other'] = array('templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_saved::create($eventdata);
                break;
            case 'usertemplate_imported':
                $eventdata['other'] = array('templatename' => $this->templatename);
                $event = \mod_surveypro\event\usertemplate_imported::create($eventdata);
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
