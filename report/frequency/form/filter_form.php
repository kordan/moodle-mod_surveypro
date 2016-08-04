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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

/**
 * The class to filter the item to get its frequency in the answers
 *
 * @package   surveyproreport_frequency
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_filterform extends moodleform {

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
        // I get the list of fields that the use wants to see in the exported file.

        $where = array();
        $where['surveyproid'] = $surveypro->id;
        $where['type'] = SURVEYPRO_TYPEFIELD;
        $where['reserved'] = 0;
        $where['hidden'] = 0;
        $itemseeds = $DB->get_recordset('surveypro_item', $where, 'sortindex');

        // Build options array.
        $labelsep = get_string('labelsep', 'langconfig'); // Separator usually is ': '.
        $options = array(get_string('choosedots'));
        foreach ($itemseeds as $itemseed) {
            if (($itemseed->plugin == 'textarea') || ($itemseed->plugin == 'fileupload')) {
                continue;
            }
            $where = array('itemid' => $itemseed->id);
            $thiscontent = $DB->get_field('surveypro'.$itemseed->type.'_'.$itemseed->plugin, 'content', $where);
            if (!empty($surveypro->template)) {
                $thiscontent = get_string($thiscontent, 'surveyprotemplate_'.$surveypro->template);
            }

            $content = get_string('pluginname', 'surveyprofield_'.$itemseed->plugin).$labelsep.strip_tags($thiscontent);
            $content = surveypro_cutdownstring($content);
            $options[$itemseed->id] = $content;
        }

        $fieldname = 'itemid';
        $mform->addElement('select', $fieldname, get_string('variable', 'mod_surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproreport_frequency');

        if ($showjumper) {
            $fieldname = 'groupid';
            $options = array();
            if ($canaccessallgroups) {
                $options[] = get_string('allgroups');
            }
            if ($addnotinanygroup) {
                $options['-1'] = get_string('notinanygroup', 'surveyproreport_attachments');
            }
            foreach ($jumpercontent as $group) {
                $options[$group->id] = $group->name;
            }
        }

        $mform->addElement('select', $fieldname, get_string('group', 'group'), $options);

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