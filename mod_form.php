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

    public function definition() {
        global $COURSE, $DB, $CFG, $cm;

        $mform = $this->_form;

        // ----------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $fieldname = 'general';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'form'));

        // Adding the standard "name" field
        $fieldname = 'name';
        $mform->addElement('text', $fieldname, get_string('surveypro'.$fieldname, 'surveypro'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType($fieldname, PARAM_TEXT);
        } else {
            $mform->setType($fieldname, PARAM_CLEANHTML);
        }
        $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');
        $mform->addRule($fieldname, get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton($fieldname, 'surveyproname', 'surveypro');

        // Adding the standard "intro" and "introformat" fields
        $this->add_intro_editor(false);

        // Open date
        $fieldname = 'timeopen';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'surveypro'), array('optional' => true));

        // Close date
        $fieldname = 'timeclose';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'surveypro'), array('optional' => true));

        // modulesettinghdr fieldset (header)
        $fieldname = 'modulesettinghdr';
        $mform->addElement('header', $fieldname, get_string($fieldname, 'surveypro'));

        // newpageforchild
        $fieldname = 'newpageforchild';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // allow/deny saveresume
        $fieldname = 'saveresume';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // history
        $fieldname = 'history';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // allow/deny anonymous
        $fieldname = 'anonymous';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // recaptcha
        if (surveypro_site_recaptcha_enabled()) {
            $fieldname = 'captcha';
            $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
            $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        }

        // startyear
        $boundaryyear = array_combine(range(1902, 2038), range(1902, 2038));

        $fieldname = 'startyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $boundaryyear);
        $mform->setDefault($fieldname, 1970);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // stopyear
        $fieldname = 'stopyear';
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $boundaryyear);
        $mform->setDefault($fieldname, 2020);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // userstyle
        $fieldname = 'userstyle';
        $filemanageroptions = surveypro_get_user_style_options();
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'surveypro'), null, $filemanageroptions);
        $mform->addHelpButton($fieldname.'_filemanager', $fieldname, 'surveypro');

        // maxentries
        $fieldname = 'maxentries';
        $countoptions = array(0 => get_string('unlimited', 'surveypro')) +
                        (array_combine(range(1, SURVEYPRO_MAX_ENTRIES),   // keys
                                       range(1, SURVEYPRO_MAX_ENTRIES))); // values
        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $countoptions);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // notifyrole
        $fieldname = 'notifyrole';
        $options = array();
        $context = context_course::instance($COURSE->id);
        $roleoptions = get_role_names_with_caps_in_context($context, array('mod/surveypro:submit'));
        $roleoptions += get_role_names_with_caps_in_context($context, array('mod/surveypro:accessreports'));
        foreach ($roleoptions as $roleid => $rolename) {
            $options[$roleid] = $rolename;
        }
        $select = $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
        $select->setMultiple(true);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // notifymore
        $fieldname = 'notifymore';
        $mform->addElement('textarea', $fieldname, get_string($fieldname, 'surveypro'), array('wrap' => 'virtual', 'rows' => '10', 'cols' => '65'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // define thanks page
        $fieldname = 'thankshtml';
        // $context = context_course::instance($COURSE->id); <-- just defined 20 rows above
        $editoroptions = surveypro_get_editor_options();
        $mform->addElement('editor', $fieldname.'_editor', get_string($fieldname, 'surveypro'), null, $editoroptions);
        $mform->addHelpButton($fieldname.'_editor', $fieldname, 'surveypro');
        $mform->setType($fieldname.'_editor', PARAM_RAW); // no XSS prevention here, users must be trusted

        // riskyeditdeadline
        $fieldname = 'riskyeditdeadline';
        $mform->addElement('date_time_selector', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // ----------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();

        // ----------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }

    // this function is executed once mod_form has been displayed
    // and it is an helper to prepare data before saving them
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $data->thankshtmlformat = $data->thankshtml_editor['format'];
        $data->thankshtml = $data->thankshtml_editor['text'];

        // notifyrole
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

    // this function is executed once mod_form has been displayed
    // and is needed to define some presets
    public function data_preprocessing(&$defaults) {
        global $DB;

        parent::data_preprocessing($defaults);

        if ($this->current->instance) {
            // manage userstyle filemanager
            $filename = 'userstyle';
            $filemanageroptions = surveypro_get_user_style_options();
            $draftitemid = file_get_submitted_draft_itemid($filename.'_filemanager');

            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, $filemanageroptions);
            $defaults[$filename.'_filemanager'] = $draftitemid;

            // manage thankshtml editor
            $filename = 'thankshtml';
            $editoroptions = surveypro_get_editor_options();
            // editing an existing feedback - let us prepare the added editor elements (intro done automatically)
            $draftitemid = file_get_submitted_draft_itemid($filename);
            $defaults[$filename.'_editor']['text'] =
                                    file_prepare_draft_area($draftitemid, $this->context->id,
                                    'mod_surveypro', SURVEYPRO_THANKSHTMLFILEAREA, false,
                                    $editoroptions,
                                    $defaults[$filename]);

            $defaults[$filename.'_editor']['format'] = $defaults['thankshtmlformat'];
            $defaults[$filename.'_editor']['itemid'] = $draftitemid;

            // notifyrole
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

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    public function add_completion_rules() {
        $mform =& $this->_form;

        // completionsubmit_check is not saved to db because it is completely redundant.
        // if completionsubmit is not empty it is checked otherwise it is not checked
        // see data_preprocessing method just few lines above
        $fieldname = 'completionsubmit';
        $elementgroup = array();
        $elementgroup[] = $mform->createElement('checkbox', $fieldname.'_check', '', get_string($fieldname.'_check', 'surveypro'));
        $elementgroup[] = $mform->createElement('text', $fieldname, '', array('size' => 3));
        $mform->setType($fieldname, PARAM_INT);
        $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname.'_group', 'surveypro'), ' ', false);
        $mform->addHelpButton($fieldname.'_group', $fieldname.'_group', 'surveypro');
        $mform->disabledIf($fieldname, $fieldname.'_check', 'notchecked');

        return array($fieldname.'_group');
    }

    public function completion_rule_enabled($data) {
        return (!empty($data['completionsubmit_check']) && ($data['completionsubmit'] != 0));
    }
}
