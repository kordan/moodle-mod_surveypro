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

class mod_surveypro_outform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        $firstpageright = $this->_customdata->firstpageright;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $surveypro = $this->_customdata->surveypro;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;
        $modulepage = $this->_customdata->modulepage;
        $readonly = $this->_customdata->readonly; // I see a form (record) that is not mine.
        $preview = $this->_customdata->preview; // Are we in preview mode?

        if ($preview) {
            $mform->disable_form_change_checker();
        }

        // Userform: s.
        $mform->addElement('hidden', 's', $surveypro->id);
        $mform->setType('s', PARAM_INT);

        // Userform: submissionid.
        $mform->addElement('hidden', 'submissionid', 0);
        $mform->setType('submissionid', PARAM_INT);

        // Userform: formpage.
        $mform->addElement('hidden', 'formpage', 0); // <-- this value comes from default as set just before $mform->display(); in view_form.php
        $mform->setType('formpage', PARAM_INT);

        if ($formpage == SURVEYPRO_LEFT_OVERFLOW) {
            $mform->addElement('static', 'nomoreitems', get_string('note', 'mod_surveypro'), get_string('onlyadvanceditemhere', 'mod_surveypro'));
            // $mform->addElement('static', 'nomoreitems', get_string('note', 'mod_surveypro'), 'SURVEYPRO_LEFT_OVERFLOW');
        }

        if ($formpage == SURVEYPRO_RIGHT_OVERFLOW) {
            $a = $surveypro->saveresume ? get_string('revieworpause', 'mod_surveypro') : get_string('onlyreview', 'mod_surveypro');
            $mform->addElement('static', 'nomoreitems', get_string('note', 'mod_surveypro'), get_string('nomoreitems', 'mod_surveypro', $a));
            // $mform->addElement('static', 'nomoreitems', get_string('note', 'mod_surveypro'), 'SURVEYPRO_RIGHT_OVERFLOW');
        }

        if ($formpage >= 0) {
            // $canaccessadvanceditems, $searchform=false, $type=false, $formpage
            list($sql, $whereparams) = surveypro_fetch_items_seeds($surveypro->id, $canaccessadvanceditems, false, false, $formpage);
            $itemseeds = $DB->get_recordset_sql($sql, $whereparams);

            if (!$itemseeds->valid()) {
                // No items are in this page.
                // Display an error message.
                $mform->addElement('static', 'noitemshere', get_string('note', 'mod_surveypro'), 'ERROR: How can I be here if ($formpage > 0) ?');
            }

            $context = context_module::instance($cm->id);

            // This dummy item is needed for the colours alternation.
            // Because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH)
            // as first item are out from the a fieldset
            // so they are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {.
            $mform->addElement('static', 'beginning_extrarow', '', '');

            foreach ($itemseeds as $itemseed) {
                if ($modulepage == SURVEYPRO_ITEMS_PREVIEW) {
                    $itemaschildisallowed = true;
                } else {
                    // Is the current item allowed to be displayed in this page?
                    if ($itemseed->parentid) {
                        // Get it now AND NEVER MORE.
                        $parentitem = surveypro_get_item($cm, $itemseed->parentid);

                        // If parentitem is in a previous page, have a check
                        // otherwise
                        // display the current item.
                        if ($parentitem->get_formpage() < $formpage) {
                            require_once($CFG->dirroot.'/mod/surveypro/'.$itemseed->type.'/'.$itemseed->plugin.'/classes/plugin.class.php');

                            $itemaschildisallowed = $parentitem->userform_child_item_allowed_static($submissionid, $itemseed);
                        } else {
                            $itemaschildisallowed = true;
                        }
                    } else {
                        // Current item has no parent: display it.
                        $parentitem = null;
                        $itemaschildisallowed = true;
                    }
                }

                if ($itemaschildisallowed) {
                    $item = surveypro_get_item($cm, $itemseed->id, $itemseed->type, $itemseed->plugin);

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
                    $item->userform_mform_element($mform, false, $readonly, $submissionid);

                    // Note.
                    if ($fullinfo = $item->userform_get_full_info(false)) {
                        $item->item_add_color_unifier($mform);

                        $itemname = $item->get_itemname().'_note';
                        $option = array('class' => 'indent-'.$item->get_indent());
                        $mform->addElement('mod_surveypro_static', $itemname, get_string('note', 'mod_surveypro'), $fullinfo, $option);
                    }

                    if (!$surveypro->newpageforchild) {
                        $item->userform_add_disabledif($mform, $canaccessadvanceditems);
                    }
                }
            }
            $itemseeds->close();

            if ($modulepage != SURVEYPRO_ITEMS_PREVIEW) {
                if (!empty($surveypro->captcha)) {
                    $mform->addElement('recaptcha', 'captcha_form_footer');
                }
            }
        }

        // Buttons.
        $buttonlist = array();

        if ( ($formpage == SURVEYPRO_RIGHT_OVERFLOW) || ($formpage > 1) ) {
            $buttonlist['prevbutton'] = get_string('previousformpage', 'mod_surveypro');
        }
        if ($modulepage != SURVEYPRO_ITEMS_PREVIEW) {
            if ($surveypro->saveresume) {
                if ($maxassignedpage > 1) {
                    $buttonlist['pausebutton'] = get_string('pause', 'mod_surveypro');
                }
            }
            if (($formpage == $maxassignedpage) || ($formpage == SURVEYPRO_RIGHT_OVERFLOW)) {
                if ($surveypro->history) {
                    $submissionstatus = $DB->get_field('surveypro_submission', 'status', array('id' => $submissionid), IGNORE_MISSING);
                    if ($submissionstatus === false) { // Submissions still does not exist.
                        $usesimplesavebutton = true;
                    } else {
                        $usesimplesavebutton = ($submissionstatus == SURVEYPRO_STATUSINPROGRESS);
                    }
                } else {
                    $usesimplesavebutton = true;
                }
                if ($usesimplesavebutton) {
                    $buttonlist['savebutton'] = get_string('submit');
                } else {
                    $buttonlist['saveasnewbutton'] = get_string('saveasnew', 'mod_surveypro');
                }
            }
        }
        if ( ($formpage == SURVEYPRO_LEFT_OVERFLOW) || ($formpage > 0 && $formpage < $maxassignedpage) ) {
            $buttonlist['nextbutton'] = get_string('nextformpage', 'mod_surveypro');
        }

        if (count($buttonlist) > 1) {
            $buttonarray = array();
            foreach ($buttonlist as $name => $label) {
                $buttonarray[] = $mform->createElement('submit', $name, $label);
            }
            $mform->addGroup($buttonarray, 'buttonsrow', '', ' ', false);
            $mform->setType('buttonar', PARAM_RAW);
            $mform->closeHeaderBefore('buttonar');
        } else { // Only one button here.
            foreach ($buttonlist as $name => $label) { // $buttonlist is a one element only array
                $mform->closeHeaderBefore($name);
                $mform->addElement('submit', $name, $label);
            }
        }
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        if (isset($data['prevbutton']) || isset($data['pausebutton'])) {
            // Skip validation.
            return array();
        }

        // $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        $modulepage = $this->_customdata->modulepage;
        $surveypro = $this->_customdata->surveypro;
        $submissionid = $this->_customdata->submissionid;
        $formpage = $this->_customdata->formpage;
        $firstpageright = $this->_customdata->firstpageright;
        $maxassignedpage = $this->_customdata->maxassignedpage;
        $canaccessadvanceditems = $this->_customdata->canaccessadvanceditems;
        // $readonly = $this->_customdata->readonly; // I see a form (record) that is not mine
        $preview = $this->_customdata->preview; // Are we in preview mode?

        if ($preview) {
            // Skip validation.
            return array();
        }

        $errors = parent::validation($data, $files);

        // Validate an item only if is enabled, alias: only if it matches the parent value
        $regexp = '~('.SURVEYPRO_ITEMPREFIX.'|'.SURVEYPRO_DONTSAVEMEPREFIX.')_('.SURVEYPRO_TYPEFIELD.'|'.SURVEYPRO_TYPEFORMAT.')_([a-z]+)_([0-9]+)_?([a-z0-9]+)?~';
        $olditemid = 0;
        foreach ($data as $itemname => $v) {
            if (preg_match($regexp, $itemname, $matches)) {
                $type = $matches[2]; // Item type.
                $plugin = $matches[3]; // Item plugin.
                $itemid = $matches[4]; // Item id.
                // $option = $matches[5]; // _text or _noanswer or...

                if ($itemid == $olditemid) {
                    continue;
                }

                $olditemid = $itemid;

                $item = surveypro_get_item($cm, $itemid, $type, $plugin);
                if ($surveypro->newpageforchild) {
                    $itemisenabled = true; // Since it is displayed, it is enabled.
                    $parentitem = null;
                } else {
                    $parentitemid = $item->get_parentid();
                    if (!$parentitemid) {
                        $itemisenabled = true;
                        $parentitem = null;
                    } else {
                        // Call its parent.
                        $parentitem = surveypro_get_item($cm, $parentitemid);
                        // Tell parent that his child has parentvalue = 1;3.
                        if ($parentitem->get_formpage() == $item->get_formpage()) {
                            $itemisenabled = $parentitem->userform_child_item_allowed_dynamic($item->get_parentvalue(), $data);
                        } else {
                            $itemisenabled = true;
                        }
                        // Parent item, knowing how itself exactly is, compare what is needed and provide an answer.
                    }
                }

                if ($itemisenabled) {
                    $item->userform_mform_validation($data, $errors, $surveypro, false);
                    // } else {
                    // echo 'parent item doesn\'t allow the validation of the child item '.$item->itemid.', plugin = '.$item->plugin.'('.$item->content.')<br />';
                }
            }
        }

        return $errors;
    }
}
