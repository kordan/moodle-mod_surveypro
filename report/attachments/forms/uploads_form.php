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

class mod_surveyproreport_uploadsform extends moodleform {

    public function definition() {
        global $DB, $CFG;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $itemid = $this->_customdata->itemid;
        $userid = $this->_customdata->userid;
        $submissionid = $this->_customdata->submissionid;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;

        list($sql, $whereparams) = surveypro_fetch_items_seeds($surveypro->id, $canaccessadvanceditems, false);
        $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

        if (!$itemseeds->valid()) {
            // no items are in this page
            // display an error message
            $mform->addElement('static', 'noitemshere', get_string('note', 'surveypro'), 'ERROR: How can I be here if ($formpage > 0) ?');
        }

        $context = context_module::instance($cmid);

        // this dummy item is needed for the colours alternation
        // because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH)
        //     as first item are out from the a fieldset
        //     so they and are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {
        $mform->addElement('static', 'beginning_extrarow', '', '');

        // username
        $user = $DB->get_record('user', array('id' => $userid));
        $mform->addElement('static', 'userfullname', get_string('fullnameuser'), fullname($user));

        // submissionid
        if ($submission = $DB->get_record('surveypro_submission', array('id' => $submissionid))) {
            $message = get_string('submissionid', 'surveyproreport_attachments').': '.$submissionid.'<br />';
            $message .= get_string('timecreated', 'surveypro').': '.userdate($submission-> timecreated).'<br />';
            if ($submission->timemodified) {
                $message .= get_string('timemodified', 'surveypro').': '.userdate($submission->timemodified);
            } else {
                $message .= get_string('timemodified', 'surveypro').': '.get_string('never');
            }
        } else {
            $message = get_string('missing_submission', 'surveyproreport_attachments');
        }
        $mform->addElement('static', 'submissioninfo', get_string('submissioninfo', 'surveyproreport_attachments'), $message);

        foreach ($itemseeds as $itemseed) {
            if ($itemseed->plugin != 'fileupload') {
                continue;
            }
            if ($itemid) {
                if ($itemid != $itemseed->id) {
                    continue;
                }
            }
            $item = surveypro_get_item($itemseed->id, $itemseed->type, $itemseed->plugin);

            // position
            $position = $item->get_position();
            $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
            if ($position == SURVEYPRO_POSITIONTOP) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('div', $item->get_content(), array('class' => 'indent-'.$item->get_indent()));

                $mform->addElement('static', $item->get_itemname().'_extrarow', $elementnumber, $content);
                $item->item_add_color_unifier($mform);
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
                $item->item_add_color_unifier($mform);
            }

            // element
            $item->userform_mform_element($mform, false, false, $submissionid);

            // note
            if ($fullinfo = $item->userform_get_full_info(false)) {
                // workaround suggested by Marina Glancy in MDL-42946
                $content = html_writer::tag('div', $fullinfo, array('class' => 'indent-'.$item->get_indent()));

                $item->item_add_color_unifier($mform);
                $mform->addElement('static', $item->get_itemname().'_note', get_string('note', 'surveypro'), $content);
            }

            if (!$surveypro->newpageforchild) {
                $item->userform_disable_element($mform, $canaccessadvanceditems);
            }
        }
        $itemseeds->close();
    }
}

