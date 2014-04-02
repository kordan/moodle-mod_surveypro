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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/locallib.php');

class surveypro_chooseitemform extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;

        $surveypro = $this->_customdata->surveypro;

        // only fields
        // no matter for the page
        // elenco dei campi che l'utente vuole vedere nel file esportato

        $where = array();
        $where['surveyproid'] = $surveypro->id;
        $where['type'] = SURVEYPRO_TYPEFIELD;
        $where['advanced'] = 0;
        $where['hidden'] = 0;
        $itemseeds = $DB->get_recordset('surveypro_item', $where, 'sortindex');

        // build options array
        $labelsep = get_string('labelsep', 'langconfig'); // ': '
        $options = array(get_string('choosedots'));
        foreach ($itemseeds as $itemseed) {
            if ($itemseed->plugin == 'textarea') {
                continue;
            }
            $thiscontent = $DB->get_field('surveypro'.$itemseed->type.'_'.$itemseed->plugin, 'content', array('itemid' => $itemseed->id));
            if (!empty($surveypro->template)) {
                $thiscontent = get_string($thiscontent, 'surveyprotemplate_'.$surveypro->template);
            }

            $content = get_string('pluginname', 'surveyprofield_'.$itemseed->plugin).$labelsep.strip_tags($thiscontent);
            $content = surveypro_fixlength($content, 60);
            $options[$itemseed->id] = $content;
        }

        $fieldname = 'itemid';
        $mform->addElement('select', $fieldname, get_string('variable', 'surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproreport_frequency');

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }

    public function validation($data, $files) {
        // "noanswer" default option is not allowed when the item is mandatory
        $errors = array();

        if (!$data['itemid']) {
            $errors['itemid'] = get_string('pleasechooseavalue', 'surveyproreport_frequency');
        }

        return $errors;
    }
}