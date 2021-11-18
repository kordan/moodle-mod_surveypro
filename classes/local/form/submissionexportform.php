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
 * The class representing the export form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class to manage the data export form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submissionexportform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $DB, $COURSE, $USER;

        $mform = $this->_form;

        // Get _customdata.
        $surveypro = $this->_customdata->surveypro;
        $activityisgrouped = $this->_customdata->activityisgrouped;
        $context = $this->_customdata->context;
        $attachmentshere = $this->_customdata->attachmentshere;

        // Submissionexport: settingsheader.
        $mform->addElement('header', 'settingsheader', get_string('download'));

        // Submissionexport: groupid.
        if ($activityisgrouped) {
            if ($allgroups = groups_get_all_groups($COURSE->id)) {
                $fieldname = 'groupid';
                $options = array();
                if (has_capability('moodle/site:accessallgroups', $context)) {
                    $options[] = get_string('allgroups');
                } else {
                    $allgroups = groups_get_all_groups($COURSE->id, $USER->id);
                }

                foreach ($allgroups as $group) {
                    $options[$group->id] = $group->name;
                }

                $mform->addElement('select', $fieldname, get_string('groupname', 'group'), $options);
            }
        }

        // Submissionexport: status.
        $fieldname = 'status';
        $where = array('surveyproid' => $surveypro->id, 'status' => SURVEYPRO_STATUSINPROGRESS);
        if ($DB->get_records('surveypro_submission', $where)) {
            $options = array();
            $options[SURVEYPRO_STATUSCLOSED] = get_string('statusclosed', 'mod_surveypro');
            $options[SURVEYPRO_STATUSINPROGRESS] = get_string('statusinprogress', 'mod_surveypro');
            $options[SURVEYPRO_STATUSALL] = get_string('statusboth', 'mod_surveypro');
            $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
        } else {
            $mform->addElement('hidden', $fieldname, SURVEYPRO_STATUSCLOSED);
            $mform->setType($fieldname, PARAM_INT);
        }

        // Submissionexport: includenames.
        if (empty($surveypro->anonymous)) {
            $fieldname = 'includenames';
            $warningtxt = get_string('import_rawwarning', 'mod_surveypro');
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'), $warningtxt);
            $mform->setDefault($fieldname, 1);
            $mform->setType($fieldname, PARAM_INT);
        }

        // Submissionexport: Creation and modification date.
        $fieldname = 'includedates';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->setDefault($fieldname, 1);
        $mform->setType($fieldname, PARAM_INT);

        // Submissionexport: includehidden.
        $fieldname = 'includehidden';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->setType($fieldname, PARAM_INT);

        // Submissionexport: includereserved.
        $fieldname = 'includereserved';
        if (has_capability('mod/surveypro:accessreserveditems', $context)) {
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        } else {
            $mform->addElement('hidden', $fieldname, 0);
            $mform->setType($fieldname, PARAM_INT);
        }

        // Submissionexport: downloadtype.
        $fieldname = 'downloadtype';
        $pluginlist = array();
        $pluginlist[SURVEYPRO_DOWNLOADCSV] = get_string('downloadtocsv', 'mod_surveypro');
        $pluginlist[SURVEYPRO_DOWNLOADTSV] = get_string('downloadtotsv', 'mod_surveypro');
        $pluginlist[SURVEYPRO_DOWNLOADXLS] = get_string('downloadtoxls', 'mod_surveypro');
        if ($attachmentshere) {
            $pluginlist[SURVEYPRO_FILESBYUSER] = get_string('downloadtozipbyuser', 'mod_surveypro');
            $pluginlist[SURVEYPRO_FILESBYITEM] = get_string('downloadtozipbysubmission', 'mod_surveypro');
        }
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $pluginlist);

        // Submissionexport: outputstyle.
        $fieldname = 'outputstyle';
        $elementgroup = array();
        $verbosestr = get_string('verbose', 'mod_surveypro');
        $rawstr = get_string('raw', 'mod_surveypro');
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $verbosestr, SURVEYPRO_VERBOSE);
        $elementgroup[] = $mform->createElement('radio', $fieldname, '', $rawstr, SURVEYPRO_RAW);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'mod_surveypro'), '<br />', false);
        $mform->setDefault($fieldname, SURVEYPRO_VERBOSE);
        $mform->disabledIf($fieldname.'_group', 'downloadtype', 'eq', SURVEYPRO_FILESBYUSER);
        $mform->disabledIf($fieldname.'_group', 'downloadtype', 'eq', SURVEYPRO_FILESBYITEM);

        $this->add_action_buttons(false, get_string('continue'));
    }
}
