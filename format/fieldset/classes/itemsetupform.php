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
 * Surveypro pluginform class.
 *
 * @package   surveyproformat_fieldset
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_fieldset;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\itemsetupbaseform;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/form/items/itembaseform.php');
require_once($CFG->dirroot.'/mod/surveypro/format/fieldset/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyproformat_fieldset
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class itemsetupform extends itemsetupbaseform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        // Start with common section of the form.
        parent::definition();

        $mform = $this->_form;

        // Get _customdata.
        $item = $this->_customdata['item'];

        // Here I open a new fieldset.
        $fieldname = 'specializations';
        $typename = get_string('pluginname', 'surveyproformat_'.$item->get_plugin());
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro', $typename));

        // Item: defaultstatus.
        $fieldname = 'defaultstatus';
        $options = array();
        $options[] = get_string('forceclosed', 'surveyproformat_fieldset');
        $options[] = get_string('forceopened', 'surveyproformat_fieldset');
        $options[] = get_string('moodledefault', 'surveyproformat_fieldset');
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveyproformat_fieldset'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproformat_fieldset');
        $mform->setDefault($fieldname, 2);

        $this->add_item_buttons();
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
        // Useless: $item = $this->_customdata['item'];.

        $errors = parent::validation($data, $files);

        if (strlen($data['content']) > 128) {
            $errors['content'] = get_string('ierr_contenttoolong', 'surveyproformat_fieldset');
        }
    }
}
