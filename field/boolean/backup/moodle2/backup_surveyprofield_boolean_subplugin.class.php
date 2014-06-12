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
 * @package assignment_offline
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Provides the information to backup boolean grading strategy information
 */
class backup_surveyprofield_boolean_subplugin extends backup_subplugin {

    /**
     * Returns the assessment form definition to attach to 'surveypro' XML element
     */
    protected function define_surveypro_subplugin_structure() {

        // XML nodes declaration
        $subplugin = $this->get_subplugin_element(); // virtual optigroup element
        $subpluginbooleans = new backup_nested_element($this->get_recommended_name());
        $subpluginboolean = new backup_nested_element('surveyprofield_boolean', array('id'), array(
            'content', 'contentformat', 'customnumber', 'position',
            'extranote', 'required', 'variable', 'indent',
            'defaultoption', 'defaultvalue', 'downloadformat', 'style'));

        // connect XML elements into the tree
        $subplugin->add_child($subpluginbooleans);
        $subpluginbooleans->add_child($subpluginboolean);

        // Define sources
        $subpluginboolean->set_source_table('surveyprofield_boolean', array('itemid' => backup::VAR_PARENTID));

        return $subplugin;
    }
}
