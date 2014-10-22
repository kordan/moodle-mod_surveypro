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
 * This is a one-line short description of the file
 *
 * @package    mod_surveypro
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_surveypro_importutemplateform extends moodleform {

    public function definition() {
        // ----------------------------------------
        $mform = $this->_form;

        // ----------------------------------------
        // get _customdata
        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $utemplateman = $this->_customdata->utemplateman;
        $filemanageroptions = $this->_customdata->filemanageroptions;

        // ----------------------------------------
        // templateimport::importfile
        // ----------------------------------------
        // here I use filemanager because I can even upload more than one usertemplate at once
        $fieldname = 'importfile';
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'surveypro'), null, $filemanageroptions);
        $mform->addRule($fieldname.'_filemanager', null, 'required');

        // ----------------------------------------
        // templateimport::overwrite
        // ----------------------------------------
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // ----------------------------------------
        // templateimport::sharinglevel
        // ----------------------------------------
        $fieldname = 'sharinglevel';
        $options = array();

        $options = $utemplateman->get_sharinglevel_options($cmid, $surveypro);

        $mform->addElement('select', $fieldname, get_string($fieldname, 'surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        // ----------------------------------------
        // buttons
        $this->add_action_buttons(false, get_string('templateimport', 'surveypro'));
    }

    public function validation($data, $files) {
        global $USER;

        $mform = $this->_form;

        $cmid = $this->_customdata->cmid;
        $surveypro = $this->_customdata->surveypro;
        $utemplateman = $this->_customdata->utemplateman;
        // $filemanageroptions = $this->_customdata->filemanager_options;

        $errors = parent::validation($data, $files);

        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);
        $draftitemid = file_get_submitted_draft_itemid('importfile_filemanager');
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, '', false);

        if (!count($draftfiles)) {
            $errors['importfile_filemanager'] = get_string('missingfile', 'surveypro');
        }

        $importedfiles = array();
        foreach ($draftfiles as $file) {
            $xmlfilename = $file->get_filename();
            $importedfiles[] = $xmlfilename;

            $xmlfileid = $file->get_id();
            $xml = $utemplateman->get_utemplate_content($xmlfileid);
            // $xml = @new SimpleXMLElement($templatecontent);
            $errormessage = $utemplateman->validate_xml($xml);
            if ($errormessage !== false) {
                if (isset($errormessage->a)) {
                    $errors['importfile_filemanager'] = get_string($errormessage->key, 'surveypro', $errormessage->a);
                } else {
                    $errors['importfile_filemanager'] = get_string($errormessage->key, 'surveypro');
                }
                return $errors;
            }
        }

        // set $debug = true; if you want to always stop to see where the xml template is buggy
        $debug = false;
        if ($debug) {
            $errors['importfile_filemanager'] = 'All is fine here!';
            return $errors;
        }

        // get all template files in the specified context
        $contextid = $utemplateman->get_contextid_from_sharinglevel($data['sharinglevel']);
        $componentfiles = $utemplateman->get_available_templates($contextid);

        foreach ($componentfiles as $xmlfile) {
            $filename = $xmlfile->get_filename();
            if (in_array($filename, $importedfiles)) {
                if (isset($data['overwrite'])) {
                    $xmlfile->delete();
                } else {
                    $a = new stdClass();
                    $a->filename = $filename;
                    $a->overwrite = get_string('overwrite', 'surveypro');
                    $errors['importfile_filemanager'] = get_string('enteruniquename', 'surveypro', $a);
                    break;
                }
            }
        }

        return $errors;
    }
}
