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
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyprofield_label;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\local\form\item_setupbaseform;

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/format/label/lib.php');

/**
 * The class representing the plugin form
 *
 * @package   surveyproformat_label
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class itemsetupform extends item_setupbaseform {

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
        $typename = get_string('pluginname', 'surveypro'.SURVEYPRO_TYPEFORMAT.'_'.$item->get_plugin());
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro', $typename));

        // Item: fullwidth.
        $fieldname = 'fullwidth';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyproformat_label'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproformat_label');
        $mform->setType($fieldname, PARAM_INT);

        // Item: leftlabel.
        $fieldname = 'leftlabel';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyproformat_label'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproformat_label');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }
}
