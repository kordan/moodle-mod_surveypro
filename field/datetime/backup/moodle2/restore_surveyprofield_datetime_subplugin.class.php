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
 * This file contains the restore code for the surveyprofield_datetime plugin.
 *
 * @package    surveyprofield_datetime
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restore subplugin class.
 *
 * Provides the necessary information needed
 * to restore one surveyprofield_datetime subplugin.
 *
 * @package   surveyprofield_datetime
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_surveyprofield_datetime_subplugin extends restore_subplugin {

    /**
     * Define new path for item subplugin
     */
    protected function define_item_subplugin_structure() {
        $paths = [];

        $elename = $this->get_namefor();
        $elepath = $this->get_pathfor($elename);
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Processes the surveyprofield_datetime element.
     *
     * @param mixed $data
     */
    public function process_surveyprofield_datetime($data) {
        global $DB;

        $data = (object)$data;
        $data->itemid = $this->get_new_parentid('surveypro_item');

        // Insert the surveyprofield_datetime record.
        $newdatetimeid = $DB->insert_record('surveyprofield_datetime', $data);
    }
}
