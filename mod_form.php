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
 * This file contains the forms to create and edit an instance of this module
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Surveypro settings form.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_mod_form extends moodleform_mod {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $fieldname = 'general';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'form'));

        // Adding the standard "name" field.
        $fieldname = 'name';
        $mform->addElement('text', $fieldname, get_string('name'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType($fieldname, PARAM_TEXT);
        } else {
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->addRule($fieldname, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton($fieldname, 'surveyproname', 'surveypro');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements(get_string('moduleintro'));

        // Open date.
        $fieldname = 'timeopen';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_surveypro'), array('optional' => true));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Close date.
        $fieldname = 'timeclose';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_surveypro'), array('optional' => true));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Modulesettinghdr fieldset (header).
        $fieldname = 'modulesettinghdr';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'mod_surveypro'));

        // Newpageforchild.
        $fieldname = 'newpageforchild';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Allow/deny saveresume.
        $fieldname = 'saveresume';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // History.
        $fieldname = 'history';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Allow/deny anonymous.
        $fieldname = 'anonymous';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Recaptcha.
        if (surveypro_site_recaptcha_enabled()) {
            $fieldname = 'captcha';
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        }

        // Startyear.
        $yearsrange = range(1902, 2038);
        $boundaryyear = array_combine($yearsrange, $yearsrange);

        $fieldname = 'startyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $boundaryyear);
        $mform->setDefault($fieldname, 1970);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Stopyear.
        $fieldname = 'stopyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $boundaryyear);
        $mform->setDefault($fieldname, 2020);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Userstyle.
        $fieldname = 'userstyle';
        $attributes = surveypro_get_user_style_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'mod_surveypro'), null, $attributes);
        $mform->addHelpButton($fieldname.'_filemanager', $fieldname, 'surveypro');

        // Maxentries.
        $fieldname = 'maxentries';
        $maxentries = 50;
        $entriesrange = range(1, $maxentries);
        $countoptions = array_combine($entriesrange, $entriesrange);
        array_unshift($countoptions, get_string('unlimited', 'mod_surveypro'));

        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $countoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Notifyrole.
        $fieldname = 'notifyrole';
        $options = array();
        $context = context_course::instance($COURSE->id);
        $roleoptions = get_role_names_with_caps_in_context($context, array('mod/surveypro:submit'));
        $roleoptions += get_role_names_with_caps_in_context($context, array('mod/surveypro:accessreports'));
        foreach ($roleoptions as $roleid => $rolename) {
            $options[$roleid] = $rolename;
        }
        $select = $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
        $select->setMultiple(true);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Notifymore.
        $fieldname = 'notifymore';
        $textareaoptions = array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65');
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'mod_surveypro'), $textareaoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Define thanks page.
        $fieldname = 'thankshtml';
        $editoroptions = surveypro_get_editor_options();
        $mform->addElement('editor', $fieldname.'_editor', get_string($fieldname, 'mod_surveypro'), null, $editoroptions);
        $mform->addHelpButton($fieldname.'_editor', $fieldname, 'surveypro');
        $mform->setType($fieldname.'_editor', PARAM_RAW); // No XSS prevention here, users must be trusted.

        // Riskyeditdeadline.
        $fieldname = 'riskyeditdeadline';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Retrieve the data parsed from the address.
     *
     * @return \stdClass the parsed data.
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $data->thankshtmlformat = $data->thankshtml_editor['format'];
        $data->thankshtml = $data->thankshtml_editor['text'];

        // Notifyrole.
        if (isset($data->notifyrole)) {
            $data->notifyrole = implode($data->notifyrole, ',');
        } else {
            $data->notifyrole = '';
        }

        // Turn off completion settings if the checkboxes aren't ticked.
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && ($data->completion == COMPLETION_TRACKING_AUTOMATIC);
            if (empty($data->completionsubmit_check) || !$autocompletion) {
                $data->completionsubmit = 0;
            }
        }

        return $data;
    }

    /**
     * Any data processing needed before the form is displayed
     * (needed to set up draft areas for editor and filemanager elements)
     *
     * @param object $defaults the data being passed to the form.
     */
    public function data_preprocessing(&$defaults) {
        parent::data_preprocessing($defaults);

        if ($this->current->instance) {
            // Manage userstyle filemanager.
            $filename = 'userstyle';
            $filemanageroptions = surveypro_get_user_style_options();
            $draftitemid = file_get_submitted_draft_itemid($filename.'_filemanager');

            $filearea = SURVEYPRO_STYLEFILEAREA;
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_surveypro', $filearea, 0, $filemanageroptions);
            $defaults[$filename.'_filemanager'] = $draftitemid;

            // Manage thankshtml editor.
            $filename = 'thankshtml';
            $editoroptions = surveypro_get_editor_options();
            // Editing an existing surveypro - let us prepare the added editor elements (intro done automatically).
            $draftitemid = file_get_submitted_draft_itemid($filename);
            $defaults[$filename.'_editor']['text'] = file_prepare_draft_area($draftitemid, $this->context->id,
                'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA, false, $editoroptions, $defaults[$filename]);
            $defaults[$filename.'_editor']['format'] = $defaults['thankshtmlformat'];
            $defaults[$filename.'_editor']['itemid'] = $draftitemid;

            // Notifyrole.
            $presetroles = explode(',', $defaults['notifyrole']);
            foreach ($presetroles as $roleid) {
                $values[] = $roleid;
            }
            $defaults['notifyrole'] = $values;
        }

        $fieldname = 'completionsubmit';
        $defaults[$fieldname.'_check'] = !empty($defaults[$fieldname]) ? 1 : 0;
        if (empty($defaults[$fieldname])) {
            $defaults[$fieldname] = 1;
        }
    }

    /**
     * Validate form data.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * Add_completion_rules.
     *
     * @return void
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        // Completionsubmit_check is not saved to db because it is completely redundant.
        // If completionsubmit is not empty it is checked otherwise it is not checked.
        // See data_preprocessing method just few lines above.
        $fieldname = 'completionsubmit';
        $elementgroup = array();
        $checklabel = get_string($fieldname.'_check', 'mod_surveypro');
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', $checklabel);
        $elementgroup[] = $mform->createElement('text', $fieldname, '', array('size' => 3));
        $mform->setType($fieldname, PARAM_INT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname.'_group', 'mod_surveypro'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname.'_group', 'surveypro');
        $mform->disabledIf($fieldname, $fieldname.'_check', 'notchecked');

        return array($fieldname.'_group');
    }

    /**
     * Completion_rule_enabled.
     *
     * @param array $data
     * @return bool
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionsubmit_check']) && ($data['completionsubmit'] != 0));
    }
}
