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
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Get _customdata.
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

        // Applymtemplate: mastertemplate.
        if (count($mtemplates)) {
            $fieldname = 'mastertemplate';
            if ($inline) {
                $elementgroup = array();
                $elementgroup[] = $mform->createElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $mtemplates);
                $elementgroup[] = $mform->createElement('submit', $fieldname.'_button', get_string('apply', 'mod_surveypro'));
                $mform->addGroup($elementgroup, $fieldname.'_group', get_string($fieldname, 'mod_surveypro'), array(' '), false);
                $mform->addHelpButton($fieldname.'_group', $fieldname, 'surveypro');
            } else {
                $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $mtemplates);
                $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
                $mform->addRule($fieldname, get_string('required'), 'required', null, 'client');

                $this->add_action_buttons(true, get_string('apply', 'mod_surveypro'));
            }
        } else {
            $fieldname = 'nomtemplates';
            $mform->addElement('static', $fieldname, get_string('mastertemplate', 'mod_surveypro'), get_string('nomtemplates_message', 'mod_surveypro'));
            $mform->addHelpButton($fieldname, 'nomtemplates', 'surveypro');
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
        global $CFG;

        // $mform = $this->_form;

        // Get _customdata.
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
            $addendum = get_string('mastertemplateaddendum', 'mod_surveypro');
            if (isset($errormessage->a)) {
                $errors['mastertemplate'] = get_string($errormessage->key, 'mod_surveypro', $errormessage->a).$addendum;
            } else {
                $errors['mastertemplate'] = get_string($errormessage->key, 'mod_surveypro').$addendum;
            }
        }

        return $errors;
    }
}