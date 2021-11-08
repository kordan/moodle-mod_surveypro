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
 * mod_surveypro item modified event.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace \mod_surveypro\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_surveypro item modified event class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_modified extends \core\event\base {

    /**
     * Set basic properties for the event
     */
    protected function init() {
        $this->data['crud'] = 'u'; // One of these: c(reate), r(ead), u(pdate), d(elete).
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'surveypro_item';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event_item_modified', 'mod_surveypro');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "User with id '{$this->userid}' has modified the '{$this->other['plugin']}' item with id '{$this->objectid}'.";
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $paramurl = array();
        $paramurl['id'] = $this->contextinstanceid;
        $paramurl['itemid'] = $this->objectid;
        $paramurl['type'] = $this->other['type'];
        $paramurl['plugin'] = $this->other['plugin'];
        $paramurl['view'] = $this->other['view'];
        return new \moodle_url('/mod/surveypro/layout_itemsetup.php', $paramurl);
    }

    /**
     * Return legacy data for add_to_log().
     *
     * @return array
     */
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'surveypro', 'item modified',
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
        if (!isset($this->other['type'])) {
            throw new \coding_exception('type is a mandatory property.');
        }
        if (!isset($this->other['plugin'])) {
            throw new \coding_exception('plugin is a mandatory property.');
        }
        if (!isset($this->other['view'])) {
            throw new \coding_exception('view is a mandatory property.');
        }
    }
}
