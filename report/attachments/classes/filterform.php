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
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_attachments;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class to filter the attachment item to overview
 *
 * @package   surveyproreport_attachments
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filterform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;

        // Get _customdata.
        $surveypro = $this->_customdata->surveypro;
        $userid = $this->_customdata->userid;
        $container = $this->_customdata->container;
        $canaccessreserveditems = $this->_customdata->canaccessreserveditems;
        $canviewhiddenactivities = $this->_customdata->canviewhiddenactivities;

        $submissionidstring = get_string('submission', 'surveyproreport_attachments');
        $userstring = get_string('user');

        list($where, $params) = surveypro_fetch_items_seeds($surveypro->id, true, $canaccessreserveditems);
        $itemseeds = $DB->get_recordset_select('surveypro_item', $where, $params, 'sortindex', 'id, plugin');

        if (!$itemseeds->valid()) {
            // No items are in this page.
            // Display an error message.
            $notestr = get_string('note', 'mod_surveypro');
            $mform->addElement('static', 'noitemshere', $notestr, 'ERROR: How can I be here if ($formpage > 0) ?');
        }

        // Itemid.
        $options = ['0' => get_string('eachitem', 'surveyproreport_attachments')];
        foreach ($itemseeds as $itemseed) {
            if ($itemseed->plugin != 'fileupload') {
                continue;
            }
            $content = $DB->get_field('surveyprofield_fileupload', 'content', ['itemid' => $itemseed->id]);
            $options[$itemseed->id] = strip_tags($content);
        }
        $itemseeds->close();

        $mform->addElement('select', 'itemid', get_string('item', 'surveypro'), $options);

        // Get users.
        $coursecontext = \context_course::instance($COURSE->id);
        $userfieldsapi = \core_user\fields::for_userpic()->get_sql('u');

        $whereparams = [];
        $whereparams['surveyproid'] = $surveypro->id;
        $sql = 'SELECT DISTINCT u.id as userid'.$userfieldsapi->selects.'
                FROM {user} u
                    JOIN {surveypro_submission} s ON s.userid = u.id';
        if (!$canviewhiddenactivities) { // Exclude global admins and managers.
            list($enrolsql, $eparams) = get_enrolled_sql($coursecontext);
            $sql .= ' JOIN ('.$enrolsql.') eu ON eu.id = u.id';
        }

        $sql .= ' WHERE surveyproid = :surveyproid';
        $sql .= ' ORDER BY u.lastname ASC';
        if (!$canviewhiddenactivities) {
            $whereparams = array_merge($whereparams, $eparams);
        }
        $users = $DB->get_recordset_sql($sql, $whereparams);

        $options = [];

        // Get submissions of $userid.
        $options = [];
        $whereparams = ['surveyproid' => $surveypro->id, 'userid' => $userid];
        $submissions = $DB->get_records('surveypro_submission', $whereparams);

        // Define $options for current $userid.
        $i = 0;
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        foreach ($submissions as $submission) {
            if (isset($CFG->forcefirstname) || isset($CFG->forcelastname)) {
                $itemcontent = $userstring.' id: '.$userid.' - '.$submissionidstring.' id: '.$submission->id;
            } else {
                $i++;
                $itemcontent = fullname($user).' - '.$submissionidstring.': '.$i;
            }
            $options[$userid.'_'.$submission->id] = $itemcontent;
        }

        // Add next user to make simpler the navigation.
        $firstuserid = 0;
        $nextiscorrect = 0;
        $optionscount = count($options);
        foreach ($users as $user) {
            // The first is ALWAYS correct.
            if (!$firstuserid && ($user->id != $userid)) {
                $firstuserid = $user->id;
                $options[$user->userid.'_0'] = $this->set_select_option($user);
                if ($nextiscorrect) {
                    break;
                } else {
                    continue;
                }
            }

            if ($user->id == $userid) {
                // Next record is the one I am looking for.
                $nextiscorrect = 1;
                continue;
            }

            if ($nextiscorrect) {
                $options[$user->userid.'_0'] = $this->set_select_option($user);
                break;
            }
        }
        if (count($options) == $optionscount + 2) {
            unset($options[$firstuserid.'_0']);
        }
        $mform->addElement('select', 'container', get_string('submission', 'surveypro'), $options);
        $mform->setDefault('container', $container);

        // Button.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'changeuser', get_string('changeuser', 'surveyproreport_attachments'));
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('reload'));
        $mform->addGroup($buttonarray, 'buttonsrow', '', ' ', false);
    }

    /**
     * Set select option.
     *
     * @param stdClass $user The user we are going to set select option
     * @return string
     */
    public function set_select_option($user) {
        global $CFG;

        if (isset($CFG->forcefirstname) || isset($CFG->forcelastname)) {
            $userstring = get_string('user');
            $itemcontent = $userstring.' id: '.$user->userid;
        } else {
            $itemcontent = fullname($user);
        }

        return $itemcontent;
    }
}

