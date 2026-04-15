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
 * Classe helper per esporre i metodi protetti di layout_branchingvalidation.
 * Situata in classes/tests per l'autoloading.
 */
class layout_branchingvalidation_test_helper extends \mod_surveypro\layout_branchingvalidation {
    /**
     * Public wrapper to setup relations table.
     *
     * @return flexible_table
     */
    public function call_setup_relations_table(): \flexible_table {
        $statusstr = get_string('relation_status', 'mod_surveypro');

        return $this->setup_relations_table($statusstr);
    }

    /**
     * Public wrapper to relation status cell.
     *
     * @param \mod_surveypro\itembase $item
     * @param \mod_surveypro\itembase|null $parentitem
     * @return string HTML
     */
    public function call_get_relation_status_cell(\mod_surveypro\itembase $item, ?\mod_surveypro\itembase $parentitem): string {
        return $this->get_relation_status_cell($item, $parentitem);
    }

    /**
     * Wrapper per il metodo build_relation_row.
     *
     * @param \mod_surveypro\itembase $item
     * @param \mod_surveypro\itembase|null $parentitem
     * @param array $isparent
     * @param \pix_icon $branchicn
     * @param \pix_icon $editicn      (kept for signature compatibility but no longer used)
     * @param string $editstr
     * @return array
     */
    public function call_build_relation_row(
        \mod_surveypro\itembase $item,
        ?\mod_surveypro\itembase $parentitem,
        array $isparent,
        \pix_icon $branchicn,
        \pix_icon $editicn,
        string $editstr
    ): array {
        return $this->build_relation_row($item, $parentitem, $isparent, $branchicn, $editicn, $editstr);
    }
}
