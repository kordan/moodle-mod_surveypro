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
 * Contains class mod_surveypro\mod_surveypro_usertemplate_name
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class to prepare a usertemplate name for display and in-place editing
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_usertemplate_name extends \core\output\inplace_editable {
    /**
     * Constructor.
     *
     * @param int $xmlfileid
     * @param string $templatename
     */
    public function __construct($xmlfileid, $templatename) {
        $templatename = format_string($templatename);
        parent::__construct('mod_surveypro', 'usertemplate_name', $xmlfileid, true, $templatename, $templatename);
    }

    /**
     * Updates usertemplate name and returns instance of this object
     *
     * @param int $xmlfileid
     * @param string $newtemplatename
     * @return static
     */
    public static function update($xmlfileid, $newtemplatename) {
        global $DB;

        $newtemplatename = clean_param($newtemplatename, PARAM_FILE);

        $fs = get_file_storage();
        $xmlfile = $fs->get_file_by_id($xmlfileid);
        if (strlen($newtemplatename) > 0) {
            $contextid = $xmlfile->get_contextid();
            $component = 'mod_surveypro';
            $filearea = SURVEYPRO_TEMPLATEFILEAREA;
            $filepath = $xmlfile->get_filepath();

            if (!$fs->file_exists($contextid, $component, $filearea, 0, $filepath, $newtemplatename)) {
                $xmlfile->rename($filepath, $newtemplatename);
                $givenname = $newtemplatename;
            } else {
                // A file with $newtemplatename already exists.
                // Give up.
                $oldtemplatename = $xmlfile->get_filename();
                $givenname = $oldtemplatename;
            }
        } else {
            // An empty name was provided. Ignore it and leave xml untouched.
            $oldtemplatename = $xmlfile->get_filename();
            $givenname = $oldtemplatename;
        }

        $filerecord = $DB->get_record('files', array('id' => $xmlfileid), 'id, contextid', MUST_EXIST);
        $context = \context::instance_by_id($filerecord->contextid);
        \external_api::validate_context($context);

        return new static($xmlfileid, $givenname);
    }
}
