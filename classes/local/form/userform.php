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
        global $DB;

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
        $mode = $this->_customdata->mode;

        if ($mode == SURVEYPRO_PREVIEWMODE) {
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
        $mform->addElement('hidden', 'formpage', 0); // Value is provided by $userform->set_data($prefill); from view.php ['section' => 'submissionform'].
        $mform->setType('formpage', PARAM_INT);

        if ( ($formpage > 0) && ($formpage <= $userformpagecount) ) {
            // $canaccessreserveditems, $searchform=false, $type=false, $formpage.
            list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, true, $canaccessreserveditems, null, null, $formpage);
            $fields = 'id, type, plugin, parentid, parentvalue';
            $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', $fields);

            // There are no items in this page.
            if ( (!$itemseeds->valid()) || ($overflowpage) ) {
                $oneshotsurvey = ($surveypro->pauseresume == SURVEYPRO_ONESHOTNOEMAIL);
                $oneshotsurvey = $oneshotsurvey || ($surveypro->pauseresume == SURVEYPRO_ONESHOTEMAIL);
                $a = $oneshotsurvey ? get_string('onlyreview', 'mod_surveypro') : get_string('revieworpause', 'mod_surveypro');
                $mform->addElement('static', 'nomoreitems', $notestr, get_string('nomoreitems', 'mod_surveypro', $a));
            }

            foreach ($itemseeds as $itemseed) {
                if ($mode == SURVEYPRO_PREVIEWMODE) {
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
                        $content = $item->get_contentwithnumber();
                        $option = ['class' => 'indent-'.$item->get_indent()];
                        $mform->addElement('mod_surveypro_label', $itemname, $elementnumber, $content, $option);

                        $item->item_add_color_unifier($mform);
                    }
                    if ($position == SURVEYPRO_POSITIONFULLWIDTH) {
                        $content = $item->get_contentwithnumber();
                        $html = '';
                        $html .= \html_writer::start_tag('div', ['class' => 'fitem row']);
                        $html .= \html_writer::start_tag('div', ['class' => 'fstatic fullwidth']);
                        $html .= $content;
                        $html .= \html_writer::end_tag('div');
                        $html .= \html_writer::end_tag('div');
                        $mform->addElement('html', $html);

                        $item->item_add_color_unifier($mform);
                    }

                    // Element.
                    $item->userform_mform_element($mform, false, ($mode == SURVEYPRO_READONLYMODE));

                    // Note.
                    if ($fullinfo = $item->userform_get_full_info(false)) {
                        $item->item_add_color_unifier($mform);

                        $itemname = $item->get_itemname().'_note';
                        $attributes = ['class' => 'indent-'.$item->get_indent().' label_static'];
                        $mform->addElement('mod_surveypro_label', $itemname, '', $fullinfo, $attributes);
                    }

                    if (!$surveypro->newpageforchild) {
                        $item->userform_add_disabledif($mform);
                    }
                }
            }
            $itemseeds->close();

            if ($mode != SURVEYPRO_PREVIEWMODE) {
                if (!empty($surveypro->captcha)) {
                    $mform->addElement('recaptcha', 'captcha_form_footer');
                }
            }
        }

        if ($mode == SURVEYPRO_READONLYMODE) {
            // Don't waste your time with buttons that are not going to be displayed.
            return;
        }

        // Buttons.
        $buttonlist = [];
        if ($formpage > $userfirstpage) {
            $buttonlist['prevbutton'] = get_string('previousformpage', 'mod_surveypro');
        }
        if ($mode != SURVEYPRO_PREVIEWMODE) {
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
            $buttonarray = [];
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
            return [];
        }

        // $mform = $this->_form;

        // Get _customdata.
        $cm = $this->_customdata->cm;
        // Useless: $mode = $this->_customdata->mode;.
        $surveypro = $this->_customdata->surveypro;
        // Useless: $submissionid = $this->_customdata->submissionid;.
        // Useless: $userformpagecount = $this->_customdata->userformpagecount;.
        // Useless: $canaccessreserveditems = $this->_customdata->canaccessreserveditems;.
        // Useless: $formpage = $this->_customdata->formpage;.
        // Useless: $userfirstpage = $this->_customdata->userfirstpage;
        // Useless: $userlastpage = $this->_customdata->userlastpage;
        // Useless: $overflowpage = $this->_customdata->overflowpage;
        $mode = $this->_customdata->mode;

        if ($mode == SURVEYPRO_PREVIEWMODE) {
            // Skip validation.
            return [];
        }

        $errors = parent::validation($data, $files);

        // Validate an item only if it is enabled, alias: only if its content matches the parent-child constrain.
        $warnings = [];
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
                // echo ', plugin = '.$item->plugin.'('.$item->content.')<br>';
            }
        }

        if ($errors) {
            // Always sum $warnings to $errors so if an element has a warning and an error too, the error it will be preferred.
            $errors += $warnings;
        }

        return $errors;
    }
}
