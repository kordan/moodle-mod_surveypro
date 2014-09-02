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

class surveypro_applyutemplateform extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $utemplateman = $this->_customdata->utemplateman;

        $options = $utemplateman->get_sharinglevel_options();

        $templates = new stdClass();
        $templatesfiles = array();
        foreach ($options as $sharinglevel => $v) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];
            $contextid = $utemplateman->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $utemplateman->get_contextstring_from_sharinglevel($contextlevel);
            $templates->{$contextstring} = $utemplateman->get_available_templates($contextid);
        }

        foreach ($templates as $contextstring => $contextfiles) {
            $contextlabel = get_string($contextstring, 'surveypro');
            foreach ($contextfiles as $xmlfile) {
                $itemsetname = $xmlfile->get_filename();
                $templatesfiles[$xmlfile->get_id()] = '('.$contextlabel.') '.$itemsetname;
            }
        }
        asort($templatesfiles);

        // ----------------------------------------
        // applyutemplate::usertemplate
        // ----------------------------------------
        $fieldname = 'usertemplate';
        $templatesfiles = array(get_string('notanyset', 'surveypro')) + $templatesfiles;
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $templatesfiles);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

        // ----------------------------------------
        // applyutemplate::otheritems
        // ----------------------------------------
        $fieldname = 'action';
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('ignoreitems', 'surveypro'), SURVEYPRO_IGNOREITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('hideitems', 'surveypro'), SURVEYPRO_HIDEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deleteallitems', 'surveypro'), SURVEYPRO_DELETEALLITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deletevisibleitems', 'surveypro'), SURVEYPRO_DELETEVISIBLEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', get_string('deletehiddenitems', 'surveypro'), SURVEYPRO_DELETEHIDDENITEMS);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveypro'), '<br />', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
        $mform->setDefault($fieldname, SURVEYPRO_IGNOREITEMS);

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(true, get_string('continue'));
    }
}