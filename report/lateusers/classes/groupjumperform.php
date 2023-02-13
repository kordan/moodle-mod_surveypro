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
 * Class to filter output by group
 *
 * @package   surveyproreport_lateusers
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace surveyproreport_lateusers;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class to filter the attachment item to overview
 *
 * @package   surveyproreport_lateusers
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class groupjumperform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $COURSE, $USER;

        $mform = $this->_form;

        // Get _customdata.
        $canaccessallgroups = $this->_customdata->canaccessallgroups;
        $addnotinanygroup = $this->_customdata->addnotinanygroup;
        $jumpercontent = $this->_customdata->jumpercontent;

        $fieldname = 'groupid';
        $options = array();
        if ($canaccessallgroups) {
            $options[] = get_string('allgroups');
        }
        if ($addnotinanygroup) {
            $options['-1'] = get_string('notinanygroup', 'surveyproreport_attachments');
        }
        foreach ($jumpercontent as $group) {
            $options[$group->id] = $group->name;
        }

        $attributes = ['class' => 'autosubmit ignoredirty'];
        $mform->addElement('select', $fieldname, get_string('group', 'group'), $options, $attributes);

        // Legacy standard.
        // $elementgroup[] = $mform->createElement('select', $fieldname, get_string('group', 'group'), $options);
        // $elementgroup[] = $mform->createElement('submit', 'submitbutton', get_string('reload'));
        // $mform->addGroup($elementgroup, 'groupid_group', get_string('filter'), array(' '), false);
    }
}

