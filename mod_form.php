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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_surveypro_mod_form extends moodleform_mod {

    /**
     * definition
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
        $boundaryyear = array_combine(range(1902, 2038), range(1902, 2038));

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
        $filemanageroptions = surveypro_get_user_style_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'mod_surveypro'), null, $filemanageroptions);
        $mform->addHelpButton($fieldname.'_filemanager', $fieldname, 'surveypro');

        // Maxentries.
        $fieldname = 'maxentries';
        $maxentries = 50;
        $countoptions = array_combine(range(1, $maxentries), range(1, $maxentries));
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
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'mod_surveypro'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Define thanks page.
        $fieldname = 'thankshtml';
        // $context = context_course::instance($COURSE->id); <-- just defined 20 rows above
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

    // This function is executed once mod_form has been displayed
    // and it is an helper to prepare data before saving them.
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

        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {
            $autocompletion = !empty($data->completion) && ($data->completion == COMPLETION_TRACKING_AUTOMATIC);
            if (empty($data->completionsubmit_check) || !$autocompletion) {
                $data->completionsubmit = 0;
            }
        }

        return $data;
    }

    // This function is executed once mod_form has been displayed and is needed to define some presets.
    public function data_preprocessing(&$defaults) {
        parent::data_preprocessing($defaults);

        if ($this->current->instance) {
            // Manage userstyle filemanager.
            $filename = 'userstyle';
            $filemanageroptions = surveypro_get_user_style_options();
            $draftitemid = file_get_submitted_draft_itemid($filename.'_filemanager');

            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, $filemanageroptions);
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
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }

    /**
     * add_completion_rules
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
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', get_string($fieldname.'_check', 'mod_surveypro'));
        $elementgroup[] = $mform->createElement('text', $fieldname, '', array('size' => 3));
        $mform->setType($fieldname, PARAM_INT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname.'_group', 'mod_surveypro'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname.'_group', 'surveypro');
        $mform->disabledIf($fieldname, $fieldname.'_check', 'notchecked');

        return array($fieldname.'_group');
    }

    /**
     * completion_rule_enabled
     *
     * @param $data
     * @return void
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionsubmit_check']) && ($data['completionsubmit'] != 0));
    }
}
