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
 * @package surveypro_autofill
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore subplugin class that provides the necessary information
 * needed to restore one surveypro->autofill subplugin.
 */
class restore_surveypro_autofill_subplugin extends restore_subplugin {

    /**
     * This method processes the config element inside one autofill surveypro (see autofill subplugin backup)
     */
    public function process_surveypro_autofill_config($data) {
        $data = (object)$data;
        print_object($data); // Nothing to do, just print the data

        // Just to check that the whole API is available here
        $this->set_mapping('surveypro_autofill_config', 1, 1, true);
        $this->add_related_files('mod_surveypro', 'intro', 'surveypro_autofill_config');
        print_object($this->get_mappingid('surveypro_autofill_config', 1));
        print_object($this->get_old_parentid('surveypro'));
        print_object($this->get_new_parentid('surveypro'));
        print_object($this->get_mapping('surveypro', $this->get_old_parentid('surveypro')));
        print_object($this->apply_date_offset(1));
        print_object($this->task->get_courseid());
        print_object($this->task->get_contextid());
        print_object($this->get_restoreid());
    }

    /**
     * This method processes the submission_config element inside one autofill surveypro (see autofill subplugin backup)
     */
    public function process_surveypro_autofill_submission_config($data) {
        $data = (object)$data;
        print_object($data); // Nothing to do, just print the data
    }
}
