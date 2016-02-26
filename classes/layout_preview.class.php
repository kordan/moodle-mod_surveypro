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

require_once($CFG->dirroot.'/mod/surveypro/classes/formbase.class.php');
require_once($CFG->dirroot.'/mod/surveypro/classes/utils.class.php');

/**
 * The base class representing a field
 */
class mod_surveypro_formpreview extends mod_surveypro_formbase {
    /**
     * Do what is needed ONLY AFTER the view parameter is set
     * setup
     */
    public function setup($submissionid, $formpage) {
        global $DB;

        $this->set_submissionid($submissionid);
        $this->set_formpage($formpage);

        $this->prevent_direct_user_input();
        $this->trigger_event();

        // Assign pages to items.
        $maxassignedpage = $DB->get_field('surveypro_item', 'MAX(formpage)', array('surveyproid' => $this->surveypro->id));
        if (!$maxassignedpage) {
            $utilityman = new mod_surveypro_utility($this->cm, $this->surveypro);
            $maxassignedpage = $utilityman->assign_pages();
            $this->set_maxassignedpage($maxassignedpage);
        } else {
            $this->set_maxassignedpage($maxassignedpage);
        }
    }

    /**
     * surveypro_add_custom_css
     *
     * @param none
     * @return void
     */
    public function surveypro_add_custom_css() {
        global $PAGE;

        $fs = get_file_storage();
        if ($files = $fs->get_area_files($this->context->id, 'mod_surveypro', SURVEYPRO_STYLEFILEAREA, 0, 'sortorder', false)) {
            $PAGE->requires->css('/mod/surveypro/userstyle.php?id='.$this->surveypro->id.'&amp;cmid='.$this->cm->id); // Not overridable via themes!
        }
    }

    /**
     * message_preview_mode
     *
     * @param none
     * @return void
     */
    public function message_preview_mode() {
        global $OUTPUT;

        $a = get_string('tabitemspage1', 'mod_surveypro');
        $previewmodestring = get_string('previewmode', 'mod_surveypro', $a);
        echo $OUTPUT->heading($previewmodestring, 4);
    }

    /**
     * prevent_direct_user_input
     *
     * @param none
     * @return void
     */
    private function prevent_direct_user_input() {
        if (!has_capability('mod/surveypro:preview', $this->context)) {
            print_error('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * trigger_event
     *
     * @return void
     */
    private function trigger_event() {
        // Event: form_previewed.
        $eventdata = array('context' => $this->context, 'objectid' => $this->surveypro->id);
        $event = \mod_surveypro\event\form_previewed::create($eventdata);
        $event->trigger();
    }
}
