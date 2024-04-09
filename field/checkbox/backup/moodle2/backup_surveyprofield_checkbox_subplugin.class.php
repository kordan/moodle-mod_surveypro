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
 * This file contains the backup code for the surveyprofield_checkbox plugin.
 *
 * @package   surveyprofield_checkbox
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the information to backup checkbox field information
 *
 * @package   surveyprofield_checkbox
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_surveyprofield_checkbox_subplugin extends backup_subplugin {

    /**
     * Returns the structure to be attached to the 'item' XML element
     */
    protected function define_item_subplugin_structure() {

        // XML nodes declaration.
        $subplugin = $this->get_subplugin_element(null, '../../plugin', 'checkbox'); // Virtual optigroup element.
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $subplugincheckbox = new backup_nested_element('surveyprofield_checkbox', ['id'], [
            'options', 'labelother', 'defaultvalue', 'noanswerdefault', 'downloadformat',
            'minimumrequired', 'maximumrequired', 'adjustment',
        ]);

        // Connect XML elements into the tree.
        $subplugin->add_child($wrapper);
        $wrapper->add_child($subplugincheckbox);

        // Define sources.
        $subplugincheckbox->set_source_table('surveyprofield_checkbox', ['itemid' => backup::VAR_PARENTID]);

        return $subplugin;
    }
}
