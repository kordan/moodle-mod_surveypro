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
 * Unit tests for layout_itemlist
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use mod_surveypro\tests\layout_branchingvalidation_test_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests per la classe layout_branchingvalidation.
 */
class mod_surveypro_layout_branchingvalidation_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate layout_itemlist with minimal dependencies.
     *
     * @return array [$manager, $surveypro, $cm]
     */
    private function make_manager(): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $manager = new layout_branchingvalidation_test_helper($cm, $context, $surveypro);
        return [$manager, $surveypro, $cm];
    }

    // -------------------------------------------------------------------------
    // Tests for setup_relations_table()
    // -------------------------------------------------------------------------

    /**
     * setup_relations_table() must return a flexible_table instance.
     */
    public function test_setup_relations_table_returns_flexible_table(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $result = $manager->call_setup_relations_table('Status');

        $this->assertInstanceOf(\flexible_table::class, $result);
    }

    /**
     * setup_relations_table() must use id=validaterelations.
     */
    public function test_setup_relations_table_correct_id(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $table = $manager->call_setup_relations_table('Status');

        $this->assertEquals('validaterelations', $table->attributes['id']);
    }

    // -------------------------------------------------------------------------
    // Tests for get_relation_status_cell()
    // -------------------------------------------------------------------------

    /**
     * get_relation_status_cell() without parentid must return '-'.
     */
    public function test_get_relation_status_cell_no_parent_returns_dash(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $item = new \surveyprofield_character\item($cm, $surveypro, $itemid, false);

        $result = $manager->call_get_relation_status_cell($item, null);

        $this->assertEquals('-', $result);
    }

    /**
     * get_relation_status_cell() with null parentitem must return '-'.
     */
    public function test_get_relation_status_cell_null_parentitem_returns_dash(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $parentid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $childid  = $generator->create_item_character($surveypro, [
            'required'    => 0,
            'parentid'    => $parentid,
            'parentvalue' => '1',
        ]);
        $item = new \surveyprofield_character\item($cm, $surveypro, $childid, false);

        $result = $manager->call_get_relation_status_cell($item, null);

        $this->assertEquals('-', $result);
    }

    /**
     * get_relation_status_cell() with valid parent-child relation must return 'ok' string.
     */
    public function test_get_relation_status_cell_valid_relation_returns_ok(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $parentid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $childid  = $generator->create_item_character($surveypro, [
            'required'    => 0,
            'parentid'    => $parentid,
            'parentvalue' => '1',
        ]);

        $parentitem = new \surveyprofield_boolean\item($cm, $surveypro, $parentid, false);
        $childitem  = new \surveyprofield_character\item($cm, $surveypro, $childid, false);

        $result = $manager->call_get_relation_status_cell($childitem, $parentitem);

        $this->assertEquals(get_string('ok'), $result);
    }

    /**
     * get_relation_status_cell() with invalid relation and visible item
     * must return a span with class errormessage.
     */
    public function test_get_relation_status_cell_invalid_visible_returns_span(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $parentid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $childid  = $generator->create_item_character($surveypro, [
            'required'    => 0,
            'parentid'    => $parentid,
            'parentvalue' => '99', // Invalid value for boolean.
        ]);

        $parentitem = new \surveyprofield_boolean\item($cm, $surveypro, $parentid, false);
        $childitem  = new \surveyprofield_character\item($cm, $surveypro, $childid, true);

        $result = $manager->call_get_relation_status_cell($childitem, $parentitem);

        $this->assertStringContainsString('class="errormessage"', $result);
    }

    // -------------------------------------------------------------------------
    // Tests for build_relation_row()
    // -------------------------------------------------------------------------

    /**
     * build_relation_row() must return an array with exactly 8 elements.
     */
    public function test_build_relation_row_returns_8_elements(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $parentid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $childid  = $generator->create_item_character($surveypro, [
            'required'    => 0,
            'parentid'    => $parentid,
            'parentvalue' => '1',
        ]);

        $parentitem = new \surveyprofield_boolean\item($cm, $surveypro, $parentid, false);
        $childitem  = new \surveyprofield_character\item($cm, $surveypro, $childid, true);

        $editstr  = get_string('edit');
        $editicn  = new \pix_icon('t/edit', $editstr, 'moodle', ['title' => $editstr]);
        $branchstr = get_string('parentelement_title', 'mod_surveypro');
        $branchicn = new \pix_icon('branch', $branchstr, 'surveypro', ['title' => $branchstr]);

        $result = $manager->call_build_relation_row(
            $childitem,
            $parentitem,
            [$parentid => 1],
            $branchicn,
            $editicn,
            $editstr,
        );

        $this->assertIsArray($result);
        $this->assertCount(8, $result);
    }

    /**
     * build_relation_row() for an item without parent must have empty parentid cell.
     */
    public function test_build_relation_row_no_parent_empty_parentid_cell(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $item   = new \surveyprofield_boolean\item($cm, $surveypro, $itemid, false);

        $editstr   = get_string('edit');
        $editicn   = new \pix_icon('t/edit', $editstr, 'moodle', ['title' => $editstr]);
        $branchstr = get_string('parentelement_title', 'mod_surveypro');
        $branchicn = new \pix_icon('branch', $branchstr, 'surveypro', ['title' => $branchstr]);

        $result = $manager->call_build_relation_row(
            $item,
            null,
            [],
            $branchicn,
            $editicn,
            $editstr,
        );

        // Column index 2 is parentid.
        $this->assertEquals('', $result[2]);
    }
}
