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
 * Surveypro utemplate_manage class.
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
class utemplate_manage extends utemplate_base {

    /**
     * @var int What the user is trying to do.
     */
    protected $action;

    /**
     * @var int User confirmation to actions.
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
     * Create the tool to sort usertemplates in the table.
     *
     * @param array $templates
     * @param string $usersort
     * @return void
     */
    private function get_virtual_table($templates, $usersort) {
        // Original table per columns: originaltablepercols.
        $templatenamecol = [];
        $sharinglevelcol = [];
        $creationdatecol = [];
        $xmlfileidcol = [];
        foreach ($templates as $template) {
            $templatenamecol[] = $template->filename;
            $sharinglevelcol[] = $template->sharingcontext;
            $creationdatecol[] = $template->timecreated;
            $xmlfileidcol[] = $template->fileid;
        }
        $originaltablepercols = [$templatenamecol, $sharinglevelcol, $creationdatecol, $xmlfileidcol];

        // Original table per rows: originaltableperrows.
        $originaltableperrows = [];
        foreach ($templatenamecol as $k => $unused) {
            $tablerow = [];
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
        $fieldindex = [0, 0, 0];
        $sortflag = [SORT_ASC, SORT_ASC, SORT_ASC];
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

    // MARK other.

    /**
     * Delete usertemplate.
     *
     * @return void
     */
    public function delete_utemplate() {
        global $OUTPUT;

        if ($this->confirm == SURVEYPRO_UNCONFIRMED) {
            // Ask for confirmation.
            $a = $this->get_utemplate_name();
            $message = get_string('confirm_delete1utemplate', 'mod_surveypro', $a);
            $optionsbase = ['s' => $this->surveypro->id, 'act' => SURVEYPRO_DELETEUTEMPLATE];

            $optionsyes = $optionsbase;
            $optionsyes['cnf'] = SURVEYPRO_CONFIRMED_YES;
            $optionsyes['fid'] = $this->utemplateid;
            $optionsyes['section'] = 'manage';
            $urlyes = new \moodle_url('/mod/surveypro/utemplates.php', $optionsyes);
            $buttonyes = new \single_button($urlyes, get_string('yes'));

            $optionsno = $optionsbase;
            $optionsno['cnf'] = SURVEYPRO_CONFIRMED_NO;
            $optionsno['section'] = 'manage';
            $urlno = new \moodle_url('/mod/surveypro/utemplates.php', $optionsno);
            $buttonno = new \single_button($urlno, get_string('no'));

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
        $paramurlbase = ['s' => $this->cm->instance];
        // End of $paramurlbase definition.

        $deletetitle = get_string('delete');
        $iconparams = ['title' => $deletetitle];
        $deleteicn = new \pix_icon('t/delete', $deletetitle, 'moodle', $iconparams);

        $importtitle = get_string('exporttemplate', 'mod_surveypro');
        $iconparams = ['title' => $importtitle];
        $importicn = new \pix_icon('t/download', $importtitle, 'moodle', $iconparams);

        $table = new \flexible_table('templatelist');

        $paramurl = ['s' => $this->cm->instance, 'section' => 'manage'];
        $baseurl = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
        $table->define_baseurl($baseurl);

        $tablecolumns = [];
        $tablecolumns[] = 'templatename';
        $tablecolumns[] = 'sharinglevel';
        $tablecolumns[] = 'timecreated';
        $tablecolumns[] = 'actions';
        $table->define_columns($tablecolumns);

        $tableheaders = [];
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

        $xmlfiles = $this->get_xmlfiles_list();

        $utemplates = [];
        foreach ($xmlfiles as $contextid => $xmlfile) {
            foreach ($xmlfiles[$contextid] as $xmlfile) {
                $utemplate = new \stdClass();
                $utemplate->filename = $xmlfile->get_filename();
                $utemplate->sharingcontext = $this->get_label_forcontextid($contextid);
                $utemplate->timecreated = $xmlfile->get_timecreated();
                $utemplate->fileid = $xmlfile->get_id();
                $utemplate->userid = $xmlfile->get_userid();
                $utemplates[] = $utemplate;
            }
        }

        $virtualtable = $this->get_virtual_table($utemplates, $table->get_sql_sort());

        $row = 0;
        foreach ($utemplates as $utemplate) {

            $xmlfileid = $virtualtable[$row]['xmlfileid'];
            $templatename = $virtualtable[$row]['templatename'];
            $tmpl = new usertemplate_name($xmlfileid, $templatename);

            $tablerow = [];
            $tablerow[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
            $tablerow[] = $virtualtable[$row]['sharinglevel'];
            $tablerow[] = userdate($virtualtable[$row]['creationdate']);

            $paramurlbase['fid'] = $virtualtable[$row]['xmlfileid'];
            $row++;

            $icons = '';
            // SURVEYPRO_DELETEUTEMPLATE.
            if ($candeleteutemplates) {
                if ($utemplate->userid == $USER->id) { // The user template can be deleted only by its owner.
                    $paramurl = $paramurlbase;
                    $paramurl['act'] = SURVEYPRO_DELETEUTEMPLATE;
                    $paramurl['section'] = 'manage';
                    $paramurl['sesskey'] = sesskey();

                    $link = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
                    $icons .= $OUTPUT->action_icon($link, $deleteicn, null, ['title' => $deletetitle]);
                }
            }

            // SURVEYPRO_EXPORTUTEMPLATE.
            if ($candownloadutemplates) {
                $paramurl = $paramurlbase;
                $paramurl['act'] = SURVEYPRO_EXPORTUTEMPLATE;
                $paramurl['section'] = 'manage';
                $paramurl['sesskey'] = sesskey();

                $link = new \moodle_url('/mod/surveypro/utemplates.php', $paramurl);
                $icons .= $OUTPUT->action_icon($link, $importicn, null, ['title' => $importtitle]);
            }

            $tablerow[] = $icons;

            $table->add_data($tablerow);
        }
        $table->set_attribute('align', 'center');
        $table->summary = get_string('templatelist', 'mod_surveypro');
        $table->print_html();
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

        // Debug: echo '<textarea rows="10" cols="100">'.$content.'</textarea>';.

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
}
