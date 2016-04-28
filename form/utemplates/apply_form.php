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
 * The class representing the "apply user template" form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the form to apply a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_applyutemplateform extends moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Get _customdata.
        // Useless: $cmid = $this->_customdata->cmid;.
        // Useless: $surveypro = $this->_customdata->surveypro;.
        $utemplateman = $this->_customdata->utemplateman;

        $options = $utemplateman->get_sharinglevel_options();

        $templatesfiles = array();
        foreach ($options as $sharinglevel => $unused) {
            $parts = explode('_', $sharinglevel);
            $contextlevel = $parts[0];
            $contextid = $utemplateman->get_contextid_from_sharinglevel($sharinglevel);
            $contextstring = $utemplateman->get_contextstring_from_sharinglevel($contextlevel);
            $contextfiles = $utemplateman->get_available_templates($contextid);

            $contextlabel = get_string($contextstring, 'mod_surveypro');
            foreach ($contextfiles as $xmlfile) {
                $itemsetname = $xmlfile->get_filename();
                $templatesfiles[$contextlevel.'_'.$xmlfile->get_id()] = '('.$contextlabel.') '.$itemsetname;
            }
        }
        asort($templatesfiles);

        // Applyutemplate: cnf.
        $fieldname = 'cnf';
        $mform->addElement('hidden', $fieldname, SURVEYPRO_UNCONFIRMED);
        $mform->setType($fieldname, PARAM_INT);

        // Applyutemplate: usertemplateinfo.
        $fieldname = 'usertemplateinfo';
        array_unshift($templatesfiles, get_string('choosedots'));
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $templatesfiles);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

        // Applyutemplate: otheritems.
        $fieldname = 'action';
        $ignoreitemsstr = get_string('ignoreitems', 'mod_surveypro');
        $hideitemsstr = get_string('hideitems', 'mod_surveypro');
        $deleteallitemsstr = get_string('deleteallitems', 'mod_surveypro');
        $deletevisibleitemsstr = get_string('deletevisibleitems', 'mod_surveypro');
        $deletehiddenitemsstr = get_string('deletehiddenitems', 'mod_surveypro');
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $ignoreitemsstr, SURVEYPRO_IGNOREITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $hideitemsstr, SURVEYPRO_HIDEALLITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $deleteallitemsstr, SURVEYPRO_DELETEALLITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $deletevisibleitemsstr, SURVEYPRO_DELETEVISIBLEITEMS);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $deletehiddenitemsstr, SURVEYPRO_DELETEHIDDENITEMS);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'mod_surveypro'), '<br />', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
        $mform->setDefault($fieldname, SURVEYPRO_IGNOREITEMS);

        $this->add_action_buttons(true, get_string('apply', 'mod_surveypro'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        // $mform = $this->_form;

        // Get _customdata.
        // Useless: $cmid = $this->_customdata->cmid;.
        // Useless: $surveypro = $this->_customdata->surveypro;.
        // Useless: $utemplateman = $this->_customdata->utemplateman;.

        $errors = parent::validation($data, $files);

        // Constrain default between boundaries.
        if (empty($data['usertemplateinfo'])) {
            $errors['usertemplateinfo'] = get_string('required');
        }

        return $errors;
    }
}