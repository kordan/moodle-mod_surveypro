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
 * The class representing the import form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

use core_text;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/csvlib.class.php');

/**
 * Class to manage the data import form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissionimportform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Submissionimport: settingsheader.
        $mform->addElement('header', 'settingsheader', get_string('upload'));

        // Submissionimport: csvfile.
        // Here I use filepicker because I want ONE, and only ONE, file to import.
        $fieldname = 'csvfile';
        $attributes = array('accepted_types' => array('.csv'));
        $mform->addElement('filepicker', $fieldname.'_filepicker', get_string('file'), null, $attributes);
        $mform->addRule($fieldname.'_filepicker', null, 'required');

        // Submissionimport: csvdelimiter.
        $fieldname = 'csvdelimiter';
        $options = \csv_import_reader::get_delimiter_list();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'tool_uploaduser'), $options);
        $mform->setDefault($fieldname, 'comma');

        // Submissionimport: encoding.
        $fieldname = 'encoding';
        $options = core_text::get_encodings();
        $mform->addElement('select', $fieldname, get_string($fieldname, 'tool_uploaduser'), $options);
        $mform->setDefault($fieldname, 'UTF-8');

        $this->add_action_buttons(false, get_string('dataimport', 'mod_surveypro'));
    }
}
