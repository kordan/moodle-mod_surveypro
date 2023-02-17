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
 * The class representing the "import user template" form
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\local\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * The class representing the form to import a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utemplateimportform extends \moodleform {

    /**
     * Definition.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Get _customdata.
        $utemplateman = $this->_customdata->utemplateman;
        $attributes = $this->_customdata->filemanageroptions;

        // Templateimport: importfile.
        // Here I use filemanager because I can even upload more than one usertemplate at once.
        $fieldname = 'importfile';
        $mform->addElement('filemanager', $fieldname.'_filemanager', get_string($fieldname, 'mod_surveypro'), null, $attributes);
        $mform->addRule($fieldname.'_filemanager', null, 'required');

        // Templateimport: overwrite.
        $fieldname = 'overwrite';
        $mform->addElement('checkbox', $fieldname, get_string($fieldname, 'mod_surveypro'));
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');

        // Templateimport: sharinglevel.
        $fieldname = 'sharinglevel';
        $options = array();

        $options = $utemplateman->get_sharinglevel_options();

        $mform->addElement('select', $fieldname, get_string($fieldname, 'mod_surveypro'), $options);
        $mform->addHelpButton($fieldname, $fieldname, 'surveypro');
        $mform->setDefault($fieldname, CONTEXT_SYSTEM);

        $this->add_action_buttons(false, get_string('import'));
    }

    /**
     * Validation.
     *
     * @param array $data
     * @param array $files
     * @return array $errors
     */
    public function validation($data, $files) {
        global $USER;

        // $mform = $this->_form;

        // Get _customdata.
        $utemplateman = $this->_customdata->utemplateman;
        // Useless: $attributes = $this->_customdata->filemanager_options;.

        $errors = parent::validation($data, $files);

        $fs = get_file_storage();
        $usercontext = \context_user::instance($USER->id);
        $draftitemid = file_get_submitted_draft_itemid('importfile_filemanager');
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, '', false);

        if (!count($draftfiles)) {
            $errors['importfile_filemanager'] = get_string('missingfile', 'mod_surveypro');
            return $errors;
        }

        $importedfiles = array();
        foreach ($draftfiles as $file) {
            $xmlfilename = $file->get_filename();
            $importedfiles[] = $xmlfilename;

            $xmlfileid = $file->get_id();
            $xml = $utemplateman->get_utemplate_content($xmlfileid);
            // $xml = @new \SimpleXMLElement($templatecontent);
            $errormessage = $utemplateman->validate_xml($xml);
            if ($errormessage !== false) {
                if (isset($errormessage->a)) {
                    $errors['importfile_filemanager'] = get_string($errormessage->key, 'mod_surveypro', $errormessage->a);
                } else {
                    $errors['importfile_filemanager'] = get_string($errormessage->key, 'mod_surveypro');
                }
                return $errors;
            }
        }

        // Set $debug = true; if you want to always stop to see where the xml template is buggy.
        $debug = false;
        if ($debug) {
            $errors['importfile_filemanager'] = 'All is fine here!';
            return $errors;
        }

        // Get all template files in the specified context.
        $contextid = $utemplateman->get_contextid_from_sharinglevel($data['sharinglevel']);
        $componentfiles = $utemplateman->get_available_templates($contextid);

        foreach ($componentfiles as $xmlfile) {
            $filename = $xmlfile->get_filename();
            if (in_array($filename, $importedfiles)) {
                if (isset($data['overwrite'])) {
                    $xmlfile->delete();
                } else {
                    $a = new \stdClass();
                    $a->filename = $filename;
                    $a->overwrite = get_string('overwrite', 'mod_surveypro');
                    $errors['importfile_filemanager'] = get_string('enteruniquename', 'mod_surveypro', $a);
                    break;
                }
            }
        }

        return $errors;
    }
}
