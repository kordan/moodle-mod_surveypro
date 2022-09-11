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
 * Surveypro formpreview class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\utility_layout;
use mod_surveypro\formbase;

/**
 * The base class representing a field
 *
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class layout_preview extends formbase {

    /**
     * Do what is needed ONLY AFTER the view parameter is set.
     *
     * @param int $submissionid
     * @param object $formpage
     */
    public function setup($submissionid, $formpage) {
        global $DB;

        // Assign pages to items.
        $userformpagecount = $DB->get_field('surveypro_item', 'MAX(formpage)', array('surveyproid' => $this->surveypro->id));
        if (!$userformpagecount) {
            $utilitylayoutman = new utility_layout($this->cm, $this->surveypro);
            $userformpagecount = $utilitylayoutman->assign_pages();
        }
        $this->set_userformpagecount($userformpagecount);
        $this->set_user_boundary_formpages();

        $this->set_submissionid($submissionid);
        $this->set_formpage($formpage);

        $this->prevent_direct_user_input();
        $this->trigger_event();

    }

    /**
     * Display the message about the preview mode.
     *
     * @return void
     */
    public function message_preview_mode() {
        global $OUTPUT;

        $a = get_string('tabitemspage1', 'mod_surveypro');
        $previewmodestring = get_string('previewmode', 'mod_surveypro', $a);
        echo $OUTPUT->heading($previewmodestring, 4);
    }

    /**
     * Prevent direct user input.
     *
     * @return void
     */
    private function prevent_direct_user_input() {
        if (!has_capability('mod/surveypro:preview', $this->context)) {
            throw new \moodle_exception('incorrectaccessdetected', 'mod_surveypro');
        }
    }

    /**
     * Trigger the form_previewed event.
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
