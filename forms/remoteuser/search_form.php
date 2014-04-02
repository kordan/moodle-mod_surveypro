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

/*
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_surveypro
 * @copyright  2013 kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class surveypro_searchform extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        // $canaccessadvanceditems, $searchform=true, $type=false, $formpage=false
        list($sql, $whereparams) = surveypro_fetch_items_seeds($surveypro->id, $canaccessadvanceditems, true);
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        $context = context_module::instance($cmid);

        // this dummy item is needed for the colours alternation
        // because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH)
        //     as first item are out from the a fieldset
        //     so they and are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {
        $mform->addElement('static', 'beginning_extrarow', '', '');
        foreach ($itemseeds as $itemseed) {
            $item = surveypro_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            // position
            $position = $item->get_position();
            $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
            if ($position == SURVEYPRO_POSITIONTOP) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('div', $item->get_content(), array('class' => 'indent-'.$item->get_indent()));

                $mform->addElement('static', $item->get_itemname().'_extrarow', $elementnumber, $content);
            }
            if ($position == SURVEYPRO_POSITIONFULLWIDTH) {
                $questioncontent = $item->get_content();
                if ($elementnumber) {
                    // I want to change "4.2:<p>Do you live in NY?</p>" to "<p>4.2: Do you live in NY?</p>"
                    if (preg_match('/^<p>(.*)$/', $questioncontent, $match)) {
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
            }

            // element
            $item->userform_mform_element($mform, true);

            // note
            if ($fullinfo = $item->userform_get_full_info(true)) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('div', $fullinfo, array('class' => 'indent-'.$item->get_indent()));

                $mform->addElement('static', $item->get_itemname().'_info', get_string('note', 'surveypro'), $content);
            }
        }
        $itemseeds->close();

        // buttons
        // $this->add_action_buttons(true, get_string('search'));
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('search'));
        $buttonarray[] = $mform->createElement('cancel', 'cancel', get_string('findall', 'surveypro'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    function validation($data, $files) {
        // $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        // $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        $errors = array();

        // TODO: verify item per item whether they provide a coherent requests
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_PLACEHOLDERPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        $olditemid = 0;
        foreach ($data as $itemname => $v) {
            if (preg_match($regexp, $itemname, $matches)) {
                $type = $matches[2]; // item type
                $plugin = $matches[3]; // item plugin
                $itemid = $matches[4]; // item id
                // $option = $matches[5]; // _text or _noanswer or...

                if ($itemid == $olditemid) {
                    continue;
                }

                $olditemid = $itemid;

                $item = surveypro_get_item($itemid, $type, $plugin);
                $item->userform_mform_validation($data, $errors, $surveypro, true);
            }
        }

        return $errors;
    }
}