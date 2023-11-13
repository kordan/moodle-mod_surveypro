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
 * The class representing the "create user template" form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the form to create a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utemplate_createform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Get _customdata.
        $utemplateman = $this->_customdata->utemplateman;
        $defaultname = $this->_customdata->defaultname;

        // Utemplatecreate: surveyproid.
        $fieldname = 'surveyproid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // Utemplatecreate: templatename.
        $fieldname = 'templatename';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_FILE); // Templatename is going to be a file name.
        $mform->setDefault($fieldname, $defaultname);

        // Utemplatecreate: overwrite.
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Utemplatecreate: visiblesonly.
        $fieldname = 'visiblesonly';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Utemplatecreate: sharinglevel.
        $fieldname = 'sharinglevel';
        $contexts = $utemplateman->get_sharingcontexts();
        $options = [];
        foreach ($contexts as $context) {
            $options[$context->id] = $utemplateman->contextlevel_to_scontextlabel($context->contextlevel);
        }
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        $this->add_action_buttons(false, get_string('save', 'mod_surveypro'));
    }

    /**
     * Get data.
     *
     * @return void
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $checkboxes = ['overwrite', 'visiblesonly'];
        foreach ($checkboxes as $checkbox) {
            if (!isset($data->{$checkbox})) {
                $data->{$checkbox} = '0';
            }
        }

        return $data;
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
        $utemplateman = $this->_customdata->utemplateman;

        $errors = parent::validation($data, $files);

        // Add the extension to the file name whether missing.
        $comparename = str_replace(' ', '_', $data['templatename']);
        if (!preg_match('~\.xml$~', $comparename)) {
            $comparename .= '.xml';
        }

        // Get all template files.
        $xmlfiles = $utemplateman->get_xmlfiles_list($data['sharinglevel']);

        foreach ($xmlfiles as $contextid => $xmlfile) {
            foreach ($xmlfiles[$contextid] as $xmlfile) {
                $existingname = $xmlfile->get_filename();
                if ($existingname == $comparename) {
                    if (isset($data['overwrite'])) {
                        $xmlfile->delete();
                    } else {
                        $a = new \stdClass();
                        $a->filename = $comparename;
                        $a->overwrite = get_string('overwrite', 'mod_surveypro');
                        $errors['templatename'] = get_string('enteruniquename', 'mod_surveypro', $a);
                    }
                    break;
                }
            }
        }

        return $errors;
    }
}
