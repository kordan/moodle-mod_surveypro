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
 * mod_surveypro mastertemplate applied event.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\event;

/**
 * The mod_surveypro mastertemplate applied event class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mastertemplate_applied extends \core\event\base {

    /**
     * Set basic properties for the event
     */
    protected function init() {
        $this->data['crud'] = 'u'; // One of these: c(reate), r(ead), u(pdate), d(elete).
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'surveypro';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_mastertemplate_applied', 'mod_surveypro');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "User with id '{$this->userid}' has applied the master template '{$this->other['templatename']}'.";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/mod/surveypro/mtemplates.php', ['id' => $this->contextinstanceid, 'section' => 'apply']);
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'surveypro', 'mastertemplate applied',
            $this->get_url(), $this->objectid, $this->contextinstanceid);
    }

    /**
     * Return the legacy event name.
     *
     * @return string
     */
    public static function get_legacy_eventname() {
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        if (!isset($this->other['templatename'])) {
            throw new \coding_exception('templatename is a mandatory property.');
        }
    }
}
