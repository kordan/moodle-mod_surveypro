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
require_once($CFG->dirroot.'/mod/surveypro/forms/items/itembase_form.php');
require_once($CFG->dirroot.'/mod/surveypro/format/fieldset/lib.php');

class mod_surveypro_pluginform extends mod_surveypro_itembaseform {

    /*
     * definition
     *
     * @param none
     * @return none
     */
    public function definition() {
        // ----------------------------------------
        // I close with the common section of the form
        parent::definition();

        // ----------------------------------------
        // $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        // $item = $this->_customdata->item;
        // $surveypro = $this->_customdata->surveypro;

        $this->add_item_buttons();
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // ----------------------------------------
        // $item = $this->_customdata->item;

        $errors = parent::validation($data, $files);

        if (strlen($data['content']) > 128) {
            $errors['content'] = get_string('contenttoolong', 'surveypro');
        }
    }
}