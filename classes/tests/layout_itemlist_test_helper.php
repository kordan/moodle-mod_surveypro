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
 * Unit test helper for layout_itemlist.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro\tests;

defined('MOODLE_INTERNAL') || die();

/**
 * Subclass exposing protected methods for testing.
 */
class layout_itemlist_test_helper extends \mod_surveypro\layout_itemlist {
    /**
     * Public wrapper for normalize_children_request().
     *
     * @param int|null $baseitemid
     * @param array|null $where
     * @return array
     */
    public function call_normalize_children_request($baseitemid, $where): array {
        return $this->normalize_children_request($baseitemid, $where);
    }

    /**
     * Public wrapper to set root item id.
     *
     * @param int $rootitemid
     * @return void
     */
    public function set_rootitemid_for_test(int $rootitemid): void {
        $this->rootitemid = $rootitemid;
    }

    /**
     * Public wrapper to setup items table.
     *
     * @return flexible_table
     */
    public function call_setup_items_table(): \flexible_table {
        return $this->setup_items_table();
    }

    /**
     * Public wrapper to get icon set.
     *
     * @return array
     */
    public function call_get_icon_set(): array {
        return $this->get_icon_set();
    }

    /**
     * Public wrapper to build item row.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset
     * @return array
     */
    public function call_build_item_row(\mod_surveypro\itembase $item, array $iconset): array {
        return $this->build_item_row($item, $iconset);
    }

    /**
     * Public wrapper to get availability icons.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset
     * @return array
     */
    public function call_get_availability_icons(\mod_surveypro\itembase $item, array $iconset): string {
        return $this->get_availability_icons($item, $iconset);
    }

    /**
     * Public wrapper to get_action_icons.
     *
     * @param \mod_surveypro\itembase $item
     * @param array $iconset
     * @return array
     */
    public function call_get_action_icons(\mod_surveypro\itembase $item, array $iconset): string {
        return $this->get_action_icons($item, $iconset);
    }
}
