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
 * The uploadformmanager class
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_attachments;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/surveypro/field/fileupload/lib.php');

/**
 * The class managing the attachement overview report
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form {

    /**
     * @var object Course module object
     */
    protected $cm;

    /**
     * @var object Context object
     */
    protected $context;

    /**
     * @var object Surveypro object
     */
    protected $surveypro;

    /**
     * @var int ID of the saved suryey_submission
     */
    public $submissionid = 0;

    /**
     * @var object Form content as submitted by the user
     */
    public $formdata = null;

    /**
     * Class constructor.
     *
     * @param object $cm
     * @param object $context
     * @param object $surveypro
     */
    public function __construct($cm, $context, $surveypro) {
        $this->cm = $cm;
        $this->context = $context;
        $this->surveypro = $surveypro;
    }

    // MARK set.

    /**
     * Set user id.
     *
     * @param string $userid
     * @return void
     */
    public function set_userid($userid) {
        $this->userid = $userid;
    }

    /**
     * Set item id.
     *
     * @param string $itemid
     * @return void
     */
    public function set_itemid($itemid) {
        $this->itemid = $itemid;
    }

    /**
     * Set submission id.
     *
     * @param string $submissionid
     * @return void
     */
    public function set_submissionid($submissionid) {
        $this->submissionid = $submissionid;
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    public function prevent_direct_user_input() {
        $allowed = has_capability('mod/surveypro:accessreports', $this->context);

        if (!$allowed) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Display_attachment.
     *
     * @param int $submissionid
     * @param int $itemid
     * @return void
     */
    public function display_attachment($submissionid, $itemid) {
        global $CFG, $DB, $OUTPUT;

        $nofilesfound = get_string('nofilesfound', 'surveyproreport_attachments');

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

        $left = get_string('submissioninfo', 'surveyproreport_attachments');
        $right = get_string('submissionid', 'surveyproreport_attachments').': '.$submission->id.'<br />';
        $right .= get_string('timecreated', 'mod_surveypro').': '.userdate($submission->timecreated).'<br />';
        if ($submission->timemodified) {
            $right .= get_string('timemodified', 'mod_surveypro').': '.userdate($submission->timemodified);
        } else {
            $right .= get_string('timemodified', 'mod_surveypro').': '.get_string('never');
        }

        $output .= str_replace('@@left@@', $left, $layout);
        $output = str_replace('@@right@@', $right, $output);

        $whereparams = array('submissionid' => $submissionid, 'plugin' => 'fileupload');
        $sql = 'SELECT i.id, a.id as answerid, fu.content
                FROM {surveypro_item} i
                  JOIN {surveypro_answer} a ON a.itemid = i.id
                  JOIN {surveyprofield_fileupload} fu ON fu.itemid = a.itemid
                WHERE i.plugin = :plugin
                  AND a.submissionid = :submissionid';
        if ($itemid) {
            $sql .= ' AND i.id = :itemid ';
            $whereparams['itemid'] = $itemid;
        }
        $sql .= ' ORDER BY i.sortindex';

        $items = $DB->get_records_sql($sql, $whereparams);

        $fs = get_file_storage();
        $component = 'surveyprofield_fileupload';
        $filearea = 'fileuploadfiles';
        foreach ($items as $item) {
            if ($files = $fs->get_area_files($this->context->id, $component, $filearea, $item->answerid, 'timemodified', false)) {
                foreach ($files as $file) {
                    $filename = $file->get_filename();
                    $iconimage = $OUTPUT->pix_icon(file_file_icon($file, 80), get_mimetype_description($file));

                    $path = '/'.$this->context->id.'/surveyprofield_fileupload/'.$filearea.'/'.$item->answerid.'/'.$filename;
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
