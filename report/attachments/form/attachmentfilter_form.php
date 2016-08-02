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
 * Class to filter the attachment item to overview
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class to filter the attachment item to overview
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class surveyproreport_attachmentfilterform extends moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $DB, $COURSE;

        $mform = $this->_form;

        // Get _customdata.
        $surveypro = $this->_customdata->surveypro;
        $userid = $this->_customdata->userid;
        $submissionid = $this->_customdata->submissionid;
        $canaccessreserveditems = $this->_customdata->canaccessreserveditems;
        $canviewhiddenactivities = $this->_customdata->canviewhiddenactivities;

        $submissionidstring = get_string('submission', 'surveyproreport_attachments');

        list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, true, $canaccessreserveditems);
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, plugin');

        if (!$itemseeds->valid()) {
            // No items are in this page.
            // Display an error message.
            $notestr = get_string('note', 'mod_surveypro');
            $mform->addElement('static', 'noitemshere', $notestr, 'ERROR: How can I be here if ($formpage > 0) ?');
        }

        // Fieldset.
        // $mform->addElement('header', 'headertools', 'Tools');

        // Itemid.
        $options = array('0' => get_string('eachitem', 'surveyproreport_attachments'));
        foreach ($itemseeds as $itemseed) {
            if ($itemseed->plugin != 'fileupload') {
                continue;
            }
            $content = $DB->get_field('surveyprofield_fileupload', 'content', array('itemid' => $itemseed->id));
            $options[$itemseed->id] = strip_tags($content);
        }
        $itemseeds->close();

        $elementgroup = array();
        $elementgroup[] = $mform->createElement('select', 'itemid', '', $options);

        // Get submissions list. Needed later.
        $options = array();
        $whereparams = array('surveyproid' => $surveypro->id, 'userid' => $userid);
        $submissions = $DB->get_records('surveypro_submission', $whereparams);

        // Userid.
        $coursecontext = context_course::instance($COURSE->id);
        $roles = get_roles_used_in_context($coursecontext);
        if (!$role = array_keys($roles)) {
            if (!$canviewhiddenactivities) {
                // Return nothing.
                return;
            }
        }

        $whereparams = array();
        $whereparams['surveyproid'] = $surveypro->id;
        $sql = 'SELECT u.id as userid, '.user_picture::fields('u').'
                FROM {user} u
                    JOIN (SELECT DISTINCT userid
                          FROM {surveypro_submission}
                          WHERE surveyproid = :surveyproid) s ON u.id = s.userid';
        if (!$canviewhiddenactivities) {
            $sql .= ' JOIN (SELECT id, userid
                            FROM {role_assignments}
                            WHERE contextid = :contextid
                                AND roleid IN ('.implode(',', $role).')) ra ON u.id = ra.userid';
            $whereparams['contextid'] = $coursecontext->id;
        }
        $sql .= ' ORDER BY u.lastname ASC';
        $users = $DB->get_recordset_sql($sql, $whereparams);

        $options = array();
        foreach ($users as $user) {
            if ($user->userid == $userid) {
                $i = 0;
                foreach ($submissions as $submission) {
                    $i++;
                    $itemcontent = fullname($user).' - '.$submissionidstring;
                    $itemcontent .= ': ';
                    $itemcontent .= $i.' [id: '.$submission->id.']';
                    $options[$user->userid.'_'.$submission->id] = $itemcontent;
                }
            } else {
                $options[$user->userid] = fullname($user);
            }
        }
        $elementgroup[] = $mform->createElement('select', 'userid', '', $options);
        $mform->setDefault('userid', $userid.'_'.$submissionid);

        // Button.
        $elementgroup[] = $mform->createElement('submit', 'submitbutton', get_string('reload'));

        $mform->addGroup($elementgroup, 'item_user', get_string('filter'), array(' '), false);
    }
}

