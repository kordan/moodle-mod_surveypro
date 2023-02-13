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
 * The class representing the form to choose the item to create
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the form to choose the item to create
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class itemchooser extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Selectitem: plugin.
        $fieldname = 'typeplugin';
        // Take care! Here the plugin holds type and plugin both.
        $fieldplugins = surveypro_get_plugin_list(SURVEYPRO_TYPEFIELD, true);
        foreach ($fieldplugins as $k => $v) {
            $fieldplugins[$k] = get_string('userfriendlypluginname', 'surveyprofield_'.$v);
        }
        asort($fieldplugins);

        $formatplugins = surveypro_get_plugin_list(SURVEYPRO_TYPEFORMAT, true);
        foreach ($formatplugins as $k => $v) {
            $formatplugins[$k] = get_string('userfriendlypluginname', 'surveyproformat_'.$v);
        }
        asort($formatplugins);

        $pluginlist = array(get_string('typefield', 'mod_surveypro') => $fieldplugins,
                            get_string('typeformat', 'mod_surveypro') => $formatplugins);

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('selectgroups', $fieldname, '', $pluginlist);
        // $attributes = ['id' => 'type_plugin', 'class' => 'autosubmit ignoredirty'];
        // $elementgroup[] = $mform->createElement('selectgroups', $fieldname, '', $pluginlist, $attributes);
        $elementgroup[] = $mform->createElement('submit', $fieldname.'_button', get_string('add'));
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'mod_surveypro'), [' '], false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
    }
}
