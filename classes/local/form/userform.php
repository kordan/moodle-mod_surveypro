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
 * The class representing the out form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

use mod_surveypro\utility_item;

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the surveypro form for the student
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        $surveypro = $this->_customdata->surveypro;
        $submissionid = $this->_customdata->submissionid;
        $userformpagecount = $this->_customdata->userformpagecount;
        $canaccessreserveditems = $this->_customdata->canaccessreserveditems;
        $formpage = $this->_customdata->formpage;
        $userfirstpage = $this->_customdata->userfirstpage;
        $userlastpage = $this->_customdata->userlastpage;
        $overflowpage = $this->_customdata->overflowpage;
        $tabpage = $this->_customdata->tabpage;
        $readonly = $this->_customdata->readonly; // I see a form (record) that is not mine.
        $preview = $this->_customdata->preview; // Are we in preview mode?

        if ($preview) {
            $mform->disable_form_change_checker();
        }

        $notestr = get_string('note', 'mod_surveypro');

        // Userform: s.
        $mform->addElement('hidden', 's', $surveypro->id);
        $mform->setType('s', PARAM_INT);

        // Userform: submissionid.
        $mform->addElement('hidden', 'submissionid', 0);
        $mform->setType('submissionid', PARAM_INT);

        // Userform: formpage.
        $mform->addElement('hidden', 'formpage', 0); // Value is provided by $userform->set_data($prefill); from view_form.php.
        $mform->setType('formpage', PARAM_INT);

        if ( ($formpage > 0) && ($formpage <= $userformpagecount) ) {
            // $canaccessreserveditems, $searchform=false, $type=false, $formpage
            list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, true, $canaccessreserveditems, null, null, $formpage);
            $fields = 'id, type, plugin, parentid, parentvalue';
            $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', $fields);

            // There are no items in this page
            if ( (!$itemseeds->valid()) || ($overflowpage) ) {
                $oneshotsurvey = ($surveypro->pauseresume == SURVEYPRO_ONESHOTNOEMAIL);
                $oneshotsurvey = $oneshotsurvey || ($surveypro->pauseresume == SURVEYPRO_ONESHOTEMAIL);
                $a = $oneshotsurvey ? get_string('onlyreview', 'mod_surveypro') : get_string('revieworpause', 'mod_surveypro');
                $mform->addElement('static', 'nomoreitems', $notestr, get_string('nomoreitems', 'mod_surveypro', $a));
            }

            // This dummy item is needed for the colours alternation
            // because 'label' or ($position == SURVEYPRO_POSITIONFULLWIDTH)
            // as first item are out from the a fieldset
            // so they are not selected by the css3 selector: fieldset div.fitem:nth-of-type(even) {.
            // $readonly page is not a form. The alternation is inverted. I need to jump this element.
            if (!$readonly) {
                $mform->addElement('static', 'beginning_extrarow', '', '');
            }

            foreach ($itemseeds as $itemseed) {
                if ($tabpage == SURVEYPRO_LAYOUT_PREVIEW) {
                    $itemaschildisallowed = true;
                } else {
                    // Is the current item allowed in this page?
                    if ($itemseed->parentid) {
                        // Get it now AND NEVER MORE.
                        $parentitem = surveypro_get_item($cm, $surveypro, $itemseed->parentid);

                        // If parentitem is in a previous page, have a check
                        // otherwise
                        // display the current item.
                        if ($parentitem->get_formpage() < $formpage) {
                            $itemaschildisallowed = $parentitem->userform_is_child_allowed_static($submissionid, $itemseed);
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
                    $item = surveypro_get_item($cm, $surveypro, $itemseed->id, $itemseed->type, $itemseed->plugin);

                    // Position.
                    $position = $item->get_position();
                    $elementnumber = $item->get_customnumber() ? $item->get_customnumber().':' : '';
                    if ($position == SURVEYPRO_POSITIONTOP) {
                        $itemname = $item->get_itemname().'_extrarow';
                        $content = $item->get_content();
                        $option = ['class' => 'indent-'.$item->get_indent()];
                        $mform->addElement('mod_surveypro_label', $itemname, $elementnumber, $content, $option);

                        $item->item_add_color_unifier($mform);
                    }
                    if ($position == SURVEYPRO_POSITIONFULLWIDTH) {
                        $questioncontent = $item->get_content();
                        if ($elementnumber) {
                            // I want to change "4.2:<p dir="ltr" style="text-align:left;">Do you live in NY?</p>"
                            // to
                            // "<p dir="ltr" style="text-align:left;">4.2: Do you live in NY?</p>".
                            if (preg_match('~^<p([^>]*)>(.*)$~', $questioncontent, $match)) {
                                $questioncontent = '<p'.$match[1].'>'.$elementnumber.' '.$match[2];
                            } else {
                                $questioncontent = $elementnumber.' '.$questioncontent;
                            }
                        }
                        $content = '';
                        $content .= \html_writer::start_tag('div', ['class' => 'fitem row']);
                        $content .= \html_writer::start_tag('div', ['class' => 'fstatic fullwidth']);
                        $content .= $questioncontent;
                        $content .= \html_writer::end_tag('div');
                        $content .= \html_writer::end_tag('div');
                        $mform->addElement('html', $content);

                        $item->item_add_color_unifier($mform);
                    }

                    // Element.
                    $item->userform_mform_element($mform, false, $readonly);

                    // Note.
                    if ($fullinfo = $item->userform_get_full_info(false)) {
                        $item->item_add_color_unifier($mform);

                        $itemname = $item->get_itemname().'_note';
                        $attributes = ['class' => 'indent-'.$item->get_indent().' label_static'];
                        $mform->addElement('mod_surveypro_label', $itemname, $notestr, $fullinfo, $attributes);
                    }

                    if (!$surveypro->newpageforchild) {
                        $item->userform_add_disabledif($mform);
                    }
                }
            }
            $itemseeds->close();

            if ($tabpage != SURVEYPRO_LAYOUT_PREVIEW) {
                if (!empty($surveypro->captcha)) {
                    $mform->addElement('recaptcha', 'captcha_form_footer');
                }
            }
        }

        // Buttons.
        $buttonlist = array();
        if ($formpage > $userfirstpage) {
            $buttonlist['prevbutton'] = get_string('previousformpage', 'mod_surveypro');
        }
        if ($tabpage != SURVEYPRO_LAYOUT_PREVIEW) {
            $pasuseresumesurvey = ($surveypro->pauseresume == SURVEYPRO_PAUSERESUMENOEMAIL);
            $pasuseresumesurvey = $pasuseresumesurvey || ($surveypro->pauseresume == SURVEYPRO_PAUSERESUMEEMAIL);
            if ($pasuseresumesurvey) {
                $buttonlist['pausebutton'] = get_string('pause', 'mod_surveypro');
            }
            if ($formpage == $userlastpage) {
                if ($surveypro->history) {
                    $where = ['id' => $submissionid];
                    $submissionstatus = $DB->get_field('surveypro_submission', 'status', $where, IGNORE_MISSING);
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
        if ($formpage < $userlastpage) {
            $buttonlist['nextbutton'] = get_string('nextformpage', 'mod_surveypro');
        }

        if (count($buttonlist) == 1) {
            $name = array_key_first($buttonlist);
            $label = $buttonlist[$name];

            $mform->closeHeaderBefore($name);
            $mform->addElement('submit', $name, $label);
        }

        if (count($buttonlist) > 1) {
            $buttonarray = array();
            foreach ($buttonlist as $name => $label) {
                $buttonarray[] = $mform->createElement('submit', $name, $label);
            }
            $mform->addGroup($buttonarray, 'buttonsrow', '', ' ', false);
            $mform->setType('buttonsrow', PARAM_RAW);
            $mform->closeHeaderBefore('buttonsrow');
        }
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        if (isset($data['prevbutton']) || isset($data['pausebutton'])) {
            // Skip validation.
            return array();
        }

        // $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        // Useless: $tabpage = $this->_customdata->tabpage;.
        $surveypro = $this->_customdata->surveypro;
        // Useless: $submissionid = $this->_customdata->submissionid;.
        // Useless: $formpage = $this->_customdata->formpage;.
        // Useless: $userformpagecount = $this->_customdata->userformpagecount;.
        // Useless: $canaccessreserveditems = $this->_customdata->canaccessreserveditems;.
        // Useless: $readonly = $this->_customdata->readonly; // I see a form (record) that is not mine.
        $preview = $this->_customdata->preview; // Are we in preview mode?

        if ($preview) {
            // Skip validation.
            return array();
        }

        $errors = parent::validation($data, $files);

        // Validate an item only if it is enabled, alias: only if its content matches the parent-child constrain.
        $warnings = array();
        $olditemid = 0;
        foreach ($data as $elementname => $content) {
            if ($matches = utility_item::get_item_parts($elementname)) {
                if ($matches['itemid'] == $olditemid) {
                    continue; // To next foreach.
                }

                $type = $matches['type'];
                $plugin = $matches['plugin'];
                $itemid = $matches['itemid'];

                $olditemid = $itemid;

                $item = surveypro_get_item($cm, $surveypro, $itemid, $type, $plugin);
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
                        $parentitem = surveypro_get_item($cm, $surveypro, $parentitemid);
                        // Tell parent that his child has parentvalue = 1;3.
                        if ($parentitem->get_formpage() == $item->get_formpage()) {
                            $itemisenabled = $parentitem->userform_is_child_allowed_dynamic($item->get_parentvalue(), $data);
                        } else {
                            $itemisenabled = true;
                        }
                        // Parent item, knowing how itself exactly is, compare what is needed and provide an answer.
                    }
                }

                if ($itemisenabled) {
                    if ($item->get_trimonsave()) {
                        if (trim($content) != $content) {
                            $warnings[$elementname] = get_string('uerr_willbetrimmed', 'mod_surveypro');
                        }
                    }
                    $item->userform_mform_validation($data, $errors, false);
                }
                // Otherwise...
                // echo 'parent item doesn\'t allow the validation of the child item '.$item->itemid;
                // echo ', plugin = '.$item->plugin.'('.$item->content.')<br />';
            }
        }

        if ($errors) {
            // Always sum $warnings to $errors so if an element has a warning and an error too, the error it will be preferred.
            $errors += $warnings;
        }

        return $errors;
    }
}
