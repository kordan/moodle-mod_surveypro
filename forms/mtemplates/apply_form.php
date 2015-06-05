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
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_surveypro_applymtemplateform extends moodleform {

    /*
     * definition
     *
     * @param none
     * @return none
     */
    public function definition() {
        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $inline = $this->_customdata->inline;

        if ($mtemplatepluginlist = get_plugin_list('surveyprotemplate')) {
            $mtemplates = array();

            foreach ($mtemplatepluginlist as $mtemplatename => $mtemplatepath) {
                if (!get_config('surveyprotemplate_'.$mtemplatename, 'disabled')) {
                    $mtemplates[$mtemplatename] = get_string('pluginname', 'surveyprotemplate_'.$mtemplatename);
                }
            }
            asort($mtemplates);
        }

        // ----------------------------------------
        // applymtemplate: mastertemplate
        // ----------------------------------------
        $fieldname = 'mastertemplate';
        if (count($mtemplates)) {
            if ($inline) {
                $elementgroup = array();
                $elementgroup[] = $mform->createElement('select', $fieldname, get_string($fieldname, 'surveypro'), $mtemplates);
                $elementgroup[] = $mform->createElement('submit', $fieldname.'_button', get_string('create'));
                $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'surveypro'), array(' '), false);
                $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
            } else {
                $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $mtemplates);
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

                // ----------------------------------------
                // buttons
                $this->add_action_buttons(true, get_string('continue'));
            }
        } else {
            $mform->addElement('static', 'nomtemplates', get_string('mastertemplate', 'surveypro'), get_string('nomtemplates_message', 'surveypro'));
            $mform->addHelpButton('nomtemplates', 'nomtemplates', 'surveypro');
        }
    }

    /*
     * validation
     *
     * @param $data
     * @param $files
     * @return $errors
     */
    public function validation($data, $files) {
        global $USER, $CFG;

        // ----------------------------------------
        // $mform = $this->_form;

        // ----------------------------------------
        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $mtemplateman = $this->_customdata->mtemplateman;
        $inline = $this->_customdata->inline;

        $errors = parent::validation($data, $files);

        $templatename = $data['mastertemplate'];
        $templatepath = $CFG->dirroot.'/mod/surveypro/template/'.$templatename.'/template.xml';
        $xml = file_get_contents($templatepath);
        // $xml = @new SimpleXMLElement($templatecontent);
        $errormessage = $mtemplateman->validate_xml($xml);
        if ($errormessage !== false) {
            $addendum = get_string('mastertemplateaddendum', 'surveypro');
            if (isset($errormessage->a)) {
                $errors['mastertemplate'] = get_string($errormessage->key, 'surveypro', $errormessage->a).$addendum;
            } else {
                $errors['mastertemplate'] = get_string($errormessage->key, 'surveypro').$addendum;
            }
        }

        return $errors;
    }
}