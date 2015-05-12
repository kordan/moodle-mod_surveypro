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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_surveypro_utemplatecreateform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return none
     */
    public function definition() {
        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $utemplateman = $this->_customdata->utemplateman;

        // ----------------------------------------
        // utemplatecreate: surveyproid
        // ----------------------------------------
        $fieldname = 'surveyproid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // utemplatecreate: templatename
        // ----------------------------------------
        $fieldname = 'templatename';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_FILE); // templatename is going to be a file name

        // ----------------------------------------
        // utemplatecreate: overwrite
        // ----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // ----------------------------------------
        // utemplatecreate: visiblesonly
        // ----------------------------------------
        $fieldname = 'visiblesonly';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // ----------------------------------------
        // utemplatecreate: sharinglevel
        // ----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = $utemplateman->get_sharinglevel_options($cmid, $surveypro);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('continue'));
    }

    /*
     * get_data
     *
     * @param none
     * @return none
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $checkboxes = array('overwrite', 'visiblesonly');
        foreach ($checkboxes as $checkbox) {
            if (!isset($data->{$checkbox})) {
                $data->{$checkbox} = '0';
            }
        }

        return $data;
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        $mform = $this->_form;

        // ----------------------------------------
        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $utemplateman = $this->_customdata->utemplateman;

        $errors = parent::validation($data, $files);

        // get all template files
        $contextid = $utemplateman->get_contextid_from_sharinglevel($data['sharinglevel']);
        $componentfiles = $utemplateman->get_available_templates($contextid);

        $comparename = str_replace(' ', '_', $data['templatename']).'.xml';
        foreach ($componentfiles as $xmlfile) {
            if ($xmlfile->get_filename() == $comparename) {
                if (isset($data['overwrite'])) {
                    $xmlfile->delete();
                } else {
                    $a = new stdClass();
                    $a->filename = $data['templatename'];
                    $a->overwrite = get_string('overwrite', 'surveypro');
                    $errors['templatename'] = get_string('enteruniquename', 'surveypro', $a);
                }
                break;
            }
        }

        return $errors;
    }
}