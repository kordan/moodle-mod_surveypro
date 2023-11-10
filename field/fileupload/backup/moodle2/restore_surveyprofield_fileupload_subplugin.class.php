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
 * This file contains the restore code for the surveyprofield_fileupload plugin.
 *
 * @package    surveyprofield_fileupload
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed
 * to restore one surveyprofield_fileupload subplugin.
 *
 * @package   surveyprofield_fileupload
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_surveyprofield_fileupload_subplugin extends restore_subplugin {

    /**
     * Define new path for subplugin at item level
     */
    protected function define_item_subplugin_structure() {
        $paths = [];

        $elename = $this->get_namefor();
        $elepath = $this->get_pathfor($elename);
        $paths[] = new restore_path_element($elename.'_item', $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Processes the surveyprofield_fileupload element at item level.
     *
     * @param mixed $data
     */
    public function process_surveyprofield_fileupload_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->itemid = $this->get_new_parentid('surveypro_item');

        // Insert the surveyprofield_fileupload record.
        $newfileuploadid = $DB->insert_record('surveyprofield_fileupload', $data);
        $this->set_mapping($this->get_namefor('fileupload'), $oldid, $newfileuploadid, true);

        // Process files for this surveyprofield_fileupload->id only.
        $fileupload = $this->get_namefor('fileupload');
        $this->add_related_files('surveyprofield_fileupload', 'fileuploadfiles', $fileupload, null, $oldid);
    }

    /**
     * Define new path for subplugin at answer level
     */
    protected function define_answer_subplugin_structure() {
        $paths = [];

        $elename = $this->get_namefor();
        $elepath = $this->get_pathfor($elename);
        $paths[] = new restore_path_element($elename . '_answer', $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process an answer restore.
     *
     * Nothing really expected here to process, because the subplugin
     * does not contain own XML structures, but we need at least this
     * process of empty XML path defined in order to get the following
     * after_execute at answer level executed, leading to the restoration
     * of answer files.
     *
     * @param object $data Data in object form
     * Processes the surveyprofield_fileupload element at answer level
     */
    protected function process_surveyprofield_fileupload_answer($data) {
    }

    /**
     * After execution method fir surveyprofield_fileupload at answer level
     */
    protected function after_execute_answer() {
        // Add surveyprofield_fileupload files, matching by answer item name.
        $this->add_related_files('surveyprofield_fileupload', 'fileuploadfiles', 'surveypro_answer');
    }
}
