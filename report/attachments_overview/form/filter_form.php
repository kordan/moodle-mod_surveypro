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
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_surveypro_report_filterform extends moodleform {

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

        $submissionidstring = get_string('submission', 'surveyproreport_attachments_overview');

        list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, $canaccessreserveditems);
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, plugin');

        if (!$itemseeds->valid()) {
            // No items are in this page.
            // Display an error message.
            $mform->addElement('static', 'noitemshere', get_string('note', 'mod_surveypro'), 'ERROR: How can I be here if ($formpage > 0) ?');
        }

        // Fieldset.
        // $mform->addElement('header', 'headertools', 'Tools');

        // Itemid.
        $options = array('0' => get_string('eachitem', 'surveyproreport_attachments_overview'));
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
            // Return nothing.
            return;
        }
        $sql = 'SELECT u.id as userid, '.user_picture::fields('u').'
                FROM {user} u
                  JOIN (SELECT id, userid
                        FROM {role_assignments}
                        WHERE contextid = '.$coursecontext->id.'
                          AND roleid IN ('.implode(',', $role).')) ra ON u.id = ra.userid
                  JOIN (SELECT DISTINCT userid
                        FROM {surveypro_submission}
                        WHERE surveyproid = :surveyproid) s ON u.id = s.userid
                ORDER BY u.lastname ASC';
        $whereparams = array('surveyproid' => $surveypro->id);
        $users = $DB->get_recordset_sql($sql, $whereparams);

        $options = array();
        foreach ($users as $user) {
            if ($user->userid == $userid) {
                $i = 0;
                foreach ($submissions as $submission) {
                    $i++;
                    $options[$user->userid.'_'.$submission->id] = fullname($user).' - '.$submissionidstring.': '.$i.' [id: '.$submission->id.']';
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

