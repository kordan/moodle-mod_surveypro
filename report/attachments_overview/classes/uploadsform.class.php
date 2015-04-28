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

require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

/**
 * The base class representing a field
 */
class mod_surveypro_report_uploadformmanager {
    /**
     * $cm
     */
    public $cm = null;

    /**
     * $context
     */
    public $context = null;

    /**
     * $surveypro: the record of this surveypro
     */
    public $surveypro = null;

    /**
     * $submissionid: the ID of the saved surbey_submission
     */
    public $submissionid = 0;

    /**
     * $canaccessadvanceditems
     */
    public $canaccessadvanceditems = false;

    /**
     * $formdata: the form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor
     *
     * @param $cm
     * @param $context
     * @param $surveypro
     * @param $userid
     * @param $itemid
     * @param $submissionid
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;

        // $this->canmanageitems = has_capability('mod/surveypro:manageitems', $this->context, null, true);
        $this->canaccessadvanceditems = has_capability('mod/surveypro:accessadvanceditems', $this->context, null, true);
    }

    // MARK set

    /**
     * set_userid
     *
     * @param string $userid
     * @return none
     */
    public function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * set_itemid
     *
     * @param string $itemid
     * @return none
     */
    public function set_itemid($itemid) {
        $this->itemid = $itemid;
    }

    /**
     * set_submissionid
     *
     * @param string $submissionid
     * @return none
     */
    public function set_submissionid($submissionid) {
        $this->submissionid = $submissionid;
    }

    /**
     * prevent_direct_user_input
     *
     * @param none
     * @return
     */
    public function prevent_direct_user_input() {
        $allowed = has_capability('mod/surveypro:accessreports', $this->context, null, true);

        if (!$allowed) {
            print_error('incorrectaccessdetected', 'surveypro');
        }
    }

    /**
     * display_attachment
     *
     * @param $submissionid
     * @param $itemid
     * @return
     */
    public function display_attachment($submissionid, $itemid) {
        global $CFG, $DB, $OUTPUT;

        $nofilesfound = get_string('nofilesfound', 'surveyproreport_attachments_overview');

        $submission = $DB->get_record('surveypro_submission', array('id' => $submissionid), '*', MUST_EXIST);
        $user = $DB->get_record('user', array('id' => $submission->userid), '*', MUST_EXIST);

        $layout = <<<EOS
<div class="mform">
    <fieldset class="hidden">
        <div>
            <div class="fitem">
                <div class="fitemtitle">
                    <div class="fstaticlabel">
                        <label>
                            @@left@@
                        </label>
                    </div>
                </div>
                <div class="felement fstatic">
                    @@right@@
                </div>
            </div>
        </div>
    </fieldset>
</div>
EOS;

        $left = get_string('fullnameuser');
        $right = fullname($user);
        $output = str_replace('@@left@@', $left, $layout);
        $output = str_replace('@@right@@', $right, $output);

        $left = get_string('submissioninfo', 'surveyproreport_attachments_overview');
        $right = get_string('submissionid', 'surveyproreport_attachments_overview').': '.$submission->id.'<br />';
        $right .= get_string('timecreated', 'surveypro').': '.userdate($submission->timecreated).'<br />';
        if ($submission->timemodified) {
            $right .= get_string('timemodified', 'surveypro').': '.userdate($submission->timemodified);
        } else {
            $right .= get_string('timemodified', 'surveypro').': '.get_string('never');
        }

        $output .= str_replace('@@left@@', $left, $layout);
        $output = str_replace('@@right@@', $right, $output);

        $whereparams = array('submissionid' => $submissionid, 'plugin' => 'fileupload');
        $sql = 'SELECT si.id, a.id as answerid, fu.content
            FROM {surveypro_item} si
                JOIN {surveypro_answer} a ON a.itemid = si.id
                JOIN {surveyprofield_fileupload} fu ON fu.itemid = a.itemid
            WHERE si.plugin = :plugin
                AND a.submissionid = :submissionid';
        if ($itemid) {
            $sql .= ' AND si.id = :itemid ';
            $whereparams['itemid'] = $itemid;
        }
        $sql .= ' ORDER BY si.sortindex';

        $items = $DB->get_records_sql($sql, $whereparams);

        $fs = get_file_storage();
        foreach ($items as $item) {
            if ($files = $fs->get_area_files($this->context->id, 'surveyprofield_fileupload', SURVEYPROFIELD_FILEUPLOAD_FILEAREA, $item->answerid, "timemodified", false)) {
                foreach ($files as $file) {
                    $filename = $file->get_filename();
                    $mimetype = $file->get_mimetype();
                    $iconimage = $OUTPUT->pix_icon(file_file_icon($file, 80), get_mimetype_description($file));

                    $path = '/'.$this->context->id.'/surveyprofield_fileupload/'.SURVEYPROFIELD_FILEUPLOAD_FILEAREA.'/'.$item->answerid.'/'.$filename;
                    $url = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path);

                    $left = $item->content;
                    $right = '<a href="'.$url.'">'.$iconimage.'</a>';
                    $right .= '<a href="'.$url.'">'.s($filename).'</a>';
                    $output .= str_replace('@@left@@', $left, $layout);
                    $output = str_replace('@@right@@', $right, $output);
                }
            } else {
                $left = $item->content;
                $right = $nofilesfound;
                $output .= str_replace('@@left@@', $left, $layout);
                $output = str_replace('@@right@@', $right, $output);
            }
        }

        echo $output;
    }
}
