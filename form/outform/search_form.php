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

class mod_surveypro_searchform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return void
     */
    public function definition() {
        global $DB;

        $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        $surveypro = $this->_customdata->surveypro;
        $canaccessreserveditems = $this->_customdata->canaccessreserveditems;

        list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, $canaccessreserveditems, true);
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, type, plugin');

        // This dummy item is needed for the colours alternation.
        // Because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH).
        // as first item are out from the a fieldset
        // so they and are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {
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
                $mform->addElement('mod_surveypro_static', $itemname, $elementnumber, $content, $option);

                $item->item_add_color_unifier($mform);
            }
            if ($position == SURVEYPRO_POSITIONFULLWIDTH) {
                $questioncontent = $item->get_content();
                if ($elementnumber) {
                    // I want to change "4.2:<p>Do you live in NY?</p>" to "<p>4.2: Do you live in NY?</p>"
                    if (preg_match('~^<p>(.*)$~', $questioncontent, $match)) {
                        // print_object($match);
                        $questioncontent = '<p>'.$elementnumber.' '.$match[1];
                    }
                }
                $content = '';
                // $content .= html_writer::start_tag('fieldset', array('class' => 'hidden'));
                // $content .= html_writer::start_tag('div');
                $content .= html_writer::start_tag('div', array('class' => 'fitem'));
                $content .= html_writer::start_tag('div', array('class' => 'fstatic fullwidth'));
                // $content .= html_writer::start_tag('div', array('class' => 'indent-'.$this->indent));
                $content .= $questioncontent;
                // $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');
                // $content .= html_writer::end_tag('div');
                // $content .= html_writer::end_tag('fieldset');
                $mform->addElement('html', $content);

                $item->item_add_color_unifier($mform);
            }

            // Element.
            $item->userform_mform_element($mform, true);

            // Note.
            if ($fullinfo = $item->userform_get_full_info(true)) {
                $item->item_add_color_unifier($mform);

                $itemname = $item->get_itemname().'_info';
                $option = array('class' => 'indent-'.$item->get_indent());
                $mform->addElement('mod_surveypro_static', $itemname, get_string('note', 'mod_surveypro'), $fullinfo, $option);
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

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        // Get _customdata.
        $cm = $this->_customdata->cm;
        $surveypro = $this->_customdata->surveypro;
        // $canaccessreserveditems = $this->_customdata->canaccessreserveditems;

        $errors = array();

        // TODO: verify item per item whether they provide a coherent requests
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_DONTSAVEMEPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        $olditemid = 0;
        foreach ($data as $itemname => $unused) {
            if (preg_match($regexp, $itemname, $matches)) {
                $type = $matches[2]; // Item type.
                $plugin = $matches[3]; // Item plugin.
                $itemid = $matches[4]; // Item id.
                // $option = $matches[5]; // _text or _noanswer or...

                if ($itemid == $olditemid) {
                    continue;
                }

                $olditemid = $itemid;

                $item = surveypro_get_item($cm, $surveypro, $itemid, $type, $plugin);
                $item->userform_mform_validation($data, $errors, $surveypro, true);
            }
        }

        return $errors;
    }
}