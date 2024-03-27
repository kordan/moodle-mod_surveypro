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
 * Surveypro utemplate_import class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_text;
use mod_surveypro\utility_layout;

use mod_surveypro\local\ipe\usertemplate_name;

/**
 * The class representing a user template
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utemplate_import extends utemplate_base {

    /**
     * Setup.
     *
     * @param int $utemplateid
     * @return void
     */
    public function setup($utemplateid) {
        $this->set_utemplateid($utemplateid);
    }

    // MARK set.

    /**
     * Set utemplateid.
     *
     * @param int $utemplateid
     * @return void
     */
    private function set_utemplateid($utemplateid) {
        $this->utemplateid = $utemplateid;
    }

    // MARK get.

    /**
     * Get filemanager options.
     *
     * @return $filemanageroptions
     */
    public function get_filemanager_options() {
        $templateoptions = ['accepted_types' => '.xml'];
        $templateoptions['maxbytes'] = 0;
        $templateoptions['maxfiles'] = -1;
        $templateoptions['mainfile'] = true;
        $templateoptions['subdirs'] = false;

        return $templateoptions;
    }

    // MARK other.

    /**
     * Upload the usertemplate.
     *
     * @return void
     */
    public function upload_utemplate() {
        $templateoptions = $this->get_filemanager_options();
        $contextid = $this->formdata->sharinglevel;
        $fs = get_file_storage();

        // Look at what is already on board.
        $oldfiles = [];
        if ($files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            foreach ($files as $file) {
                $oldfiles[] = $file->get_filename();
            }
        }

        // Add current files.
        $fieldname = 'importfile';
        if ($draftitemid = $this->formdata->{$fieldname.'_filemanager'}) {
            if (isset($templateoptions['return_types']) && !($templateoptions['return_types'] & FILE_REFERENCE)) {
                // We assume that if $options['return_types'] is NOT specified, we DO allow references.
                // This is not exactly right. BUT there are many places in code where filemanager options...
                // ...are not passed to file_save_draft_area_files().
                $allowreferences = false;
            }

            file_save_draft_area_files($draftitemid, $contextid, 'mod_surveypro', 'temporaryarea', 0, $templateoptions);
            $files = $fs->get_area_files($contextid, 'mod_surveypro', 'temporaryarea');
            $filecount = 0;
            foreach ($files as $file) {
                if (in_array($file->get_filename(), $oldfiles)) {
                    continue;
                }

                $filerecord = ['contextid' => $contextid];
                $filerecord['component'] = 'mod_surveypro';
                $filerecord['filearea'] = SURVEYPRO_TEMPLATEFILEAREA;
                $filerecord['itemid'] = 0;
                $filerecord['timemodified'] = time();
                if (!$templateoptions['subdirs']) {
                    if ($file->get_filepath() !== '/' || $file->is_directory()) {
                        continue;
                    }
                }
                if ($templateoptions['maxbytes'] && $templateoptions['maxbytes'] < $file->get_filesize()) {
                    // Oversized file - should not get here at all.
                    continue;
                }
                if ($templateoptions['maxfiles'] != -1 && $templateoptions['maxfiles'] <= $filecount) {
                    // More files - should not get here at all.
                    break;
                }
                if (!$file->is_directory()) {
                    $filecount++;
                }

                if ($file->is_external_file()) {
                    if (!$allowreferences) {
                        continue;
                    }
                    $repoid = $file->get_repository_id();
                    if (!empty($repoid)) {
                        $filerecord['repositoryid'] = $repoid;
                        $filerecord['reference'] = $file->get_reference();
                    }
                }

                $fs->create_file_from_storedfile($filerecord, $file);
            }
        }

        if ($files = $fs->get_area_files($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, 'sortorder', false)) {
            if (count($files) == 1) {
                // Only one file attached, set it as main file automatically.
                $file = array_shift($files);
                $filepath = $file->get_filepath();
                $filename = $file->get_filename();
                file_set_sortorder($contextid, 'mod_surveypro', SURVEYPRO_TEMPLATEFILEAREA, 0, $filepath, $filename, 1);
            }
        }

        $this->utemplateid = $file->get_id();
    }

    /**
     * Display the welcome message of the import page.
     *
     * @return void
     */
    public function welcome_import_message() {
        global $OUTPUT;

        $a = get_string('utemplate_save', 'mod_surveypro');
        $message = get_string('welcome_utemplateimport', 'mod_surveypro', $a);
        echo $OUTPUT->notification($message, 'notifymessage');
    }
}
