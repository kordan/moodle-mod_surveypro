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
 * Define the complete assignment structure for restore, with file and id annotations
 *
 * @package    mod_surveypro
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Include the required mod surveypro upgrade code.
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/mod/surveypro/db/upgradelib.php');

/**
 * Define all the restore steps that will be used by the restore_surveypro_activity_task
 *
 * @package   mod_surveypro
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_surveypro_activity_structure_step extends restore_activity_structure_step {

    /**
     * Define the structure of the restore workflow.
     *
     * @return void
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('surveypro', '/activity/surveypro');
        $item = new restore_path_element('surveypro_item', '/activity/surveypro/items/item');
        $paths[] = $item;
        if ($userinfo) {
            $paths[] = new restore_path_element('surveypro_submission', '/activity/surveypro/submissions/submission');
            $answer = new restore_path_element('surveypro_answer', '/activity/surveypro/submissions/submission/answers/answer');
            $paths[] = $answer;
        }

        // Apply for 'surveyprofield' and 'surveyproformat' subplugins optional paths at item level.
        $this->add_subplugin_structure('surveyprofield', $item);
        $this->add_subplugin_structure('surveyproformat', $item);
        if ($userinfo) {
            // Apply for 'surveyprofield' and 'surveyproformat' subplugins optional paths at answer level.
            $this->add_subplugin_structure('surveyprofield', $answer);
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process a surveypro restore.
     *
     * @param object $data Data in object form
     * @return void
     */
    protected function process_surveypro($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $moduleversion = $this->task->get_old_moduleversion();

        // Old backups using advanced field map to new reserved field.
        if (isset($data->advanced)) {
            $data->reserved = $data->advanced;
        }
        if (isset($data->thankshtml)) {
            $data->thankspage = $data->thankshtml;
        }
        if (isset($data->thankshtmlformat)) {
            $data->thankspageformat = $data->thankshtmlformat;
        }
        if (isset($data->notifycontent)) {
            $data->mailcontent = $data->notifycontent;
        }
        if (isset($data->notifycontentformat)) {
            $data->mailcontentformat = $data->notifycontentformat;
        }
        if (isset($data->notifymore)) {
            $data->mailextraaddresses = $data->notifymore;
        }
        if (isset($data->notifyrole)) {
            $data->mailroles = $data->notifyrole;
        }
        if (isset($data->saveresume)) {
            $data->pauseresume = $data->saveresume;
        }
        if ($moduleversion < '2022041400') {
            if (isset($data->pauseresume) && ($data->pauseresume == 1)) {
                $data->pauseresume = 2;
            }
        }
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the surveypro record.
        $newitemid = $DB->insert_record('surveypro', $data);

        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Process an item restore.
     *
     * @param object $data Data in object form
     * @return void
     */
    protected function process_surveypro_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->surveyproid = $this->get_new_parentid('surveypro');
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('surveypro_item', $data);
        // We need the mapping to be able to restore files from filearea 'itemcontent'.
        $this->set_mapping('surveypro_item', $oldid, $newitemid, true);
    }

    /**
     * Process a submission restore.
     *
     * @param object $data Data in object form
     * @return void
     */
    protected function process_surveypro_submission($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->surveyproid = $this->get_new_parentid('surveypro');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('surveypro_submission', $data);
        $this->set_mapping('surveypro_submission', $oldid, $newitemid);
    }

    /**
     * Process an answer restore.
     *
     * @param object $data Data in object form
     * @return void
     */
    protected function process_surveypro_answer($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->submissionid = $this->get_new_parentid('surveypro_submission');
        $data->itemid = $this->get_mappingid('surveypro_item', $data->itemid);

        $newitemid = $DB->insert_record('surveypro_answer', $data);
        $this->set_mapping('surveypro_answer', $oldid, $newitemid, true);
    }

    /**
     * Once the database tables have been fully restored, restore the files.
     *
     * @return void
     */
    protected function after_execute() {
        global $DB;

        // Add surveypro related files, no need to match by itemname (just internally handled context).
        $this->add_related_files('mod_surveypro', 'intro', null);
        $this->add_related_files('mod_surveypro', SURVEYPRO_THANKSPAGEFILEAREA, null);
        $this->add_related_files('mod_surveypro', SURVEYPRO_STYLEFILEAREA, null);
        $this->add_related_files('mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, null);

        // Add item content files, matching by item itemname.
        $this->add_related_files('mod_surveypro', 'itemcontent', 'surveypro_item');

        // 1) get all the item->parentids belonging to the surveypro you are restoring.
        // 2) iterate over them, and when a parentid is found, look in item mappings and perform the set_field.
        $where = array('surveyproid' => $this->get_new_parentid('surveypro'));
        $itemrecords = $DB->get_recordset('surveypro_item', $where, '', 'id, parentid');
        if ($itemrecords->valid()) {
            foreach ($itemrecords as $itemrecord) {
                if ($itemrecord->parentid) {
                    $newparentid = $this->get_mappingid('surveypro_item', $itemrecord->parentid);
                    $DB->set_field('surveypro_item', 'parentid', $newparentid, array('id' => $itemrecord->id));
                }
            }
        }
        $itemrecords->close();
    }

    /**
     * Hook to execute assignment upgrade after restore.
     */
    protected function after_restore() {
        global $DB;

        // Get the id of this surveypro.
        $surveyproid = $this->task->get_activityid();

        $surveypro = $DB->get_record('surveypro', array('id' => $surveyproid), '*', MUST_EXIST);
        surveypro_old_restore_fix($surveypro);
    }
}
