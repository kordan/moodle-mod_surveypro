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
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use core_admin\admin_search;

/**
 * Admin external page that displays a list of the installed submission plugins.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_page_manage_surveypro_plugins extends \admin_externalpage
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
                $type = admin_search::SEARCH_MATCH_SETTING_DISPLAY_NAME;
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new \stdClass();
            $result->page = $this;
            $result->settings = [];
            $result->searchmatchtype = $type;

            return [$this->name => $result];
        } else {
            return [];
        }
    }
}
