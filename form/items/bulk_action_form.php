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
 * The class representing the bulkaction form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class to manage the form for bulk action performed agains items
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_bulkactionform extends moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $fieldname = 'bulkaction';
        $options = array();
        $options[SURVEYPRO_NOACTION] = get_string('choosedots');
        $options[SURVEYPRO_HIDEALLITEMS] = get_string('hideallitems', 'mod_surveypro');
        $options[SURVEYPRO_SHOWALLITEMS] = get_string('showallitems', 'mod_surveypro');
        $options[SURVEYPRO_DELETEALLITEMS] = get_string('deleteallitems', 'mod_surveypro');
        $options[SURVEYPRO_DELETEVISIBLEITEMS] = get_string('deletevisibleitems', 'mod_surveypro');
        $options[SURVEYPRO_DELETEHIDDENITEMS] = get_string('deletehiddenitems', 'mod_surveypro');

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', $fieldname, null, $options);
        $elementgroup[] = $mform->createElement('submit', 'button', get_string('go'));
        $mform->addElement('group', $fieldname.'_group', get_string($fieldname, 'surveypro'), $elementgroup, ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
    }
}