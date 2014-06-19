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
 * @package surveypro_boolean
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore subplugin class that provides the necessary information
 * needed to restore one surveypro->boolean subplugin.
 */
class restore_surveyprofield_boolean_subplugin extends restore_subplugin {

    protected function define_item_subplugin_structure() {
        $paths = array();

        $elename = $this->get_namefor();
        $elepath = $this->get_pathfor($elename);
        $paths[] = new restore_path_element($elename, $elepath);

echo 'I am at the line '.__LINE__.' of the file '.__FILE__."\n";
var_dump($paths);
// die;

        return $paths; // And we return the interesting paths
    }

    /**
     * Processes the surveyprofield_boolean element
     */
    public function process_surveyprofield_boolean($data) {
        global $DB;

        $data = (object)$data;
echo 'I am at the line '.__LINE__.' of the file '.__FILE__."\n";
var_dump($data);

        $oldid = $data->id;
        $data->itemid = $this->get_mappingid('itemid', $data->itemid);

        // insert the assignment record
        $newitemid = $DB->insert_record('surveyprofield_boolean', $data);

        // immediately after inserting "activity" record, call this
        // $this->apply_activity_instance($newitemid);

// die;
    }
}
