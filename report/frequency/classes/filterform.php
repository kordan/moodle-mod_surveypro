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
 * Class to filter the item to get its frequency in the answers
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_frequency;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class to filter output by group
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filterform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Get _customdata.
        $surveypro = $this->_customdata->surveypro;
        $showjumper = $this->_customdata->showjumper;

        if ($showjumper) {
            $canaccessallgroups = $this->_customdata->canaccessallgroups;
            $addnotinanygroup = $this->_customdata->addnotinanygroup;
            $jumpercontent = $this->_customdata->jumpercontent;
        }

        // Only fields.
        // No matter for the page.
        // I want the list of the fields the user may ask for the report (excluding 'textarea' and 'fileupload').

        $where = ['surveyproid' => $surveypro->id, 'type' => SURVEYPRO_TYPEFIELD, 'reserved' => 0, 'hidden' => 0];
        $itemseeds = $DB->get_recordset('surveypro_item', $where, 'sortindex');

        // Build options array.
        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $options = [get_string('choosedots')];
        foreach ($itemseeds as $itemseed) {
            if (($itemseed->plugin == 'textarea') || ($itemseed->plugin == 'fileupload')) {
                continue;
            }
            $where = ['id' => $itemseed->id];
            $thiscontent = $DB->get_field('surveypro_item', 'content', $where);
            if (!empty($surveypro->template)) {
                $thiscontent = get_string($thiscontent, 'surveyprotemplate_'.$surveypro->template);
            }

            $content = get_string('pluginname', 'surveypro'.SURVEYPRO_TYPEFIELD.'_'.$itemseed->plugin);
            $content .= $labelsep.strip_tags($thiscontent);
            $content = surveypro_cutdownstring($content);
            $options[$itemseed->id] = $content;
        }

        $fieldname = 'itemid';
        $mform->addElement('select', $fieldname, get_string('variable', 'mod_surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproreport_frequency');

        if ($showjumper) {
            $fieldname = 'groupid';
            $options = [];
            if ($canaccessallgroups) {
                $options[] = get_string('allgroups');
            }
            if ($addnotinanygroup) {
                $options['-1'] = get_string('notinanygroup', 'surveyproreport_attachments');
            }
            foreach ($jumpercontent as $group) {
                $options[$group->id] = $group->name;
            }

            $mform->addElement('select', $fieldname, get_string('group', 'group'), $options);
        }

        $this->add_action_buttons(false, get_string('continue'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // Useless: $surveypro = $this->_customdata->surveypro;.

        $errors = parent::validation($data, $files);

        if (!$data['itemid']) {
            $errors['itemid'] = get_string('pleasechooseavalue', 'surveyproreport_frequency');
        }

        return $errors;
    }
}
