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
 * The class representing the search form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the surveypro search form for the student
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class usersearchform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        $surveypro = $this->_customdata->surveypro;
        $canaccessreserveditems = $this->_customdata->canaccessreserveditems;

        list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, true, $canaccessreserveditems, true);
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin');

        // This dummy item is needed for the colours alternation.
        // Because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH).
        // as first item are out from the a fieldset
        // so they and are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {.
        $mform->addElement('static', 'beginning_extrarow', '', '');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($cm, $surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

            // Position.
            $position = $item->get_position();
            $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
            if ($position == SURVEYPRO_POSITIONTOP) {
                $itemname = $item->get_itemname().'_extrarow';
                $content = $item->get_content();
                $option = array('class' => 'indent-'.$item->get_indent());
                $mform->addElement('mod_surveypro_label', $itemname, $elementnumber, $content, $option);

                $item->item_add_color_unifier($mform);
            }
            if ($position == SURVEYPRO_POSITIONFULLWIDTH) {
                $questioncontent = $item->get_content();
                if ($elementnumber) {
                    // I want to change "4.2:<p>Do you live in NY?</p>" to "<p>4.2: Do you live in NY?</p>".
                    if (preg_match('~^<p>(.*)$~', $questioncontent, $match)) {
                        // print_object($match);
                        $questioncontent = '<p>'.$elementnumber.' '.$match[1];
                    }
                }
                $content = '';
                $content .= \html_writer::start_tag('div', array('class' => 'fitem row'));
                $content .= \html_writer::start_tag('div', array('class' => 'fstatic fullwidth'));
                $content .= $questioncontent;
                $content .= \html_writer::end_tag('div');
                $content .= \html_writer::end_tag('div');
                $mform->addElement('html', $content);

                $item->item_add_color_unifier($mform);
            }

            // Element.
            $item->userform_mform_element($mform, true, false);

            // Note.
            if ($fullinfo = $item->userform_get_full_info(true)) {
                $item->item_add_color_unifier($mform);

                $itemname = $item->get_itemname().'_info';
                $option = array('class' => 'indent-'.$item->get_indent());
                $mform->addElement('mod_surveypro_label', $itemname, get_string('note', 'mod_surveypro'), $fullinfo, $option);
            }
        }
        $itemseeds->close();

        // Buttons.
        // $this->add_action_buttons(true, get_string('search')) does not allow me to give a label to the cancel button!
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('showallsubmissions', 'mod_surveypro'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
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
        $cm = $this->_customdata->cm;
        $surveypro = $this->_customdata->surveypro;
        // Useless: $canaccessreserveditems = $this->_customdata->canaccessreserveditems;.

        $errors = parent::validation($data, $files);

        $olditemid = 0;
        foreach ($data as $elementname => $unused) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                if ($matches['itemid'] == $olditemid) {
                    continue;
                }

                $type = $matches['type'];
                $plugin = $matches['plugin'];
                $itemid = $matches['itemid'];

                $olditemid = $itemid;

                $item = surveypro_get_item($cm, $surveypro, $itemid, $type, $plugin);
                $item->userform_mform_validation($data, $errors, true);
            }
        }

        return $errors;
    }
}
