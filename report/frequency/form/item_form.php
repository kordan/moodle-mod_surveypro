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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

class mod_surveypro_chooseitemform extends moodleform {

    /**
     * definition
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Get _customdata.
        $surveypro = $this->_customdata->surveypro;

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
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $options = array(get_string('choosedots'));
        foreach ($itemseeds as $itemseed) {
            if (($itemseed->plugin == 'textarea') || ($itemseed->plugin == 'fileupload')) {
                continue;
            }
            $thiscontent = $DB->get_field('surveypro'.$itemseed->type.'_'.$itemseed->plugin, 'content', array('itemid' => $itemseed->id));
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

        $this->add_action_buttons(false, get_string('continue'));
    }

    /**
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        // $surveypro = $this->_customdata->surveypro;

        // "noanswer" default option is not allowed when the item is mandatory
        $errors = array();

        if (!$data['itemid']) {
            $errors['itemid'] = get_string('pleasechooseavalue', 'surveyproreport_frequency');
        }

        return $errors;
    }
}