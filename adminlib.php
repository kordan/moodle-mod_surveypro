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
 * This file contains the classes for the admin settings of the surveypro module.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_admin_page_manage_surveypro_plugins extends admin_externalpage
{
    /**
     * @var string Name of plugin subtype.
     */
    private $subtype = '';

    /**
     * The constructor - calls parent constructor.
     *
     * @param string $subtype
     */
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new \moodle_url('/mod/surveypro/adminmanageplugins.php', ['subtype' => $subtype]);
        parent::__construct(
            'manage' . $subtype . 'plugins',
            get_string('manage' . $subtype . 'plugins', 'mod_surveypro'),
            $url
        );
    }

    /**
     * Search plugins for the specified string.
     *
     * @param string $query String to search for
     * @return array
     */
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (\core_component::get_plugin_list($this->subtype) as $name => $unused) {
            if (strpos(strtolower(get_string('pluginname', $this->subtype . '_' . $name)), $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new \stdClass();
            $result->page = $this;
            $result->settings = [];
            return [$this->name => $result];
        } else {
            return [];
        }
    }
}
