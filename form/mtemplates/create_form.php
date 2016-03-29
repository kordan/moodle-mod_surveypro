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

class mod_surveypro_mtemplatecreateform extends moodleform {

    /**
     * definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Mtemplatecreate: surveyproid.
        $fieldname = 'surveyproid';
        $mform->addElement('hidden', $fieldname, 0);
        $mform->setType($fieldname, PARAM_INT);

        // Mtemplatecreate: mastertemplatename.
        $fieldname = 'mastertemplatename';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->setType($fieldname, PARAM_FILE); // This word is going to be a file name.

        $this->add_action_buttons(false, get_string('save', 'mod_surveypro'));
    }
}
