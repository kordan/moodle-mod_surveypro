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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/format/label/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    public function definition() {
        // ----------------------------------------
        // I close with the common section of the form
        parent::definition();

        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        // -----------------------------
        // here I open a new fieldset
        // -----------------------------
        $fieldname = 'specializations';
        $typename = get_string('pluginname', 'surveyproformat_'.$item->plugin);
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro', $typename));

        // ----------------------------------------
        // item::fullwidth
        // ----------------------------------------
        $fieldname = 'fullwidth';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveyproformat_label'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproformat_label');
        $mform->setType($fieldname, PARAM_INT);

        // ----------------------------------------
        // item::leftlabel
        // ----------------------------------------
        $fieldname = 'leftlabel';
        $mform->addElement('text', $fieldname, get_string($fieldname, 'surveyproformat_label'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveyproformat_label');
        $mform->setType($fieldname, PARAM_TEXT);

        $this->add_item_buttons();
    }
}