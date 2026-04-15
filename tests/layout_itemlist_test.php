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

use mod_surveypro\tests\layout_itemlist_test_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for layout_itemlist methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\layout_itemlist::class)]
final class layout_itemlist_test extends \advanced_testcase {
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
        $manager = new layout_itemlist_test_helper($cm, $context, $surveypro);
        return [$manager, $surveypro, $cm];
    }

    // -------------------------------------------------------------------------
    // Tests for setters
    // -------------------------------------------------------------------------

    /**
     * All setters must execute without errors.
     */
    public function test_setters_execute_without_errors(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();

        $manager->set_type(SURVEYPRO_TYPEFIELD);
        $manager->set_plugin('character');
        $manager->set_itemid(1);
        $manager->set_sortindex(1);
        $manager->set_action(SURVEYPRO_NOACTION);
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);
        $manager->set_itemtomove(0);
        $manager->set_lastitembefore(0);
        $manager->set_nextindent(0);
        $manager->set_parentid(0);
        $manager->set_confirm(SURVEYPRO_UNCONFIRMED);
        $manager->set_itemeditingfeedback(0);
        $manager->set_hassubmissions(false);
        $manager->set_itemcount(0);

        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Tests for get_children()
    // -------------------------------------------------------------------------

    /**
     * get_children() with non-array $where must throw moodle_exception.
     */
    public function test_get_children_non_array_where_throws(): void {
        $this->resetAfterTest();

        [$manager, $surveypro] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);

        $this->expectException(\moodle_exception::class);
        $manager->get_children($itemid, 'notanarray');
    }

    /**
     * get_children() with a valid item and no children must return only the base item.
     */
    public function test_get_children_no_children(): void {
        $this->resetAfterTest();

        [$manager, $surveypro] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);

        $result = $manager->get_children($itemid);

        $this->assertIsArray($result);
        $this->assertArrayHasKey($itemid, $result);
    }

    /**
     * get_children() must return the base item plus all its children.
     */
    public function test_get_children_with_children(): void {
        $this->resetAfterTest();

        [$manager, $surveypro] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $parentid = $generator->create_item_boolean($surveypro, ['required' => 0]);
        $childid = $generator->create_item_character($surveypro, ['required' => 0, 'parentid' => $parentid, 'parentvalue' => '1']);

        $result = $manager->get_children($parentid);

        $this->assertArrayHasKey($parentid, $result);
        $this->assertArrayHasKey($childid, $result);
        $this->assertCount(2, $result);
    }

    /**
     * get_children() with rootitemid set via set_itemid() must use it as base.
     */
    public function test_get_children_uses_rootitemid(): void {
        $this->resetAfterTest();

        [$manager, $surveypro] = $this->make_manager();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);

        $manager->set_itemid($itemid);
        $result = $manager->get_children();

        $this->assertArrayHasKey($itemid, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for get_children() refactoring helpers
    // -------------------------------------------------------------------------

    /**
     * normalize_children_request() must fallback to rootitemid and empty where.
     */
    public function test_normalize_children_request_fallbacks(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $manager->set_rootitemid_for_test(77);

        [$baseitemid, $where] = $manager->call_normalize_children_request(null, null);

        $this->assertEquals(77, $baseitemid);
        $this->assertIsArray($where);
        $this->assertEmpty($where);
    }

    /**
     * normalize_children_request() must throw when where is not an array.
     */
    public function test_normalize_children_request_invalid_where_throws(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $manager->set_rootitemid_for_test(99);

        $this->expectException(\moodle_exception::class);
        $manager->call_normalize_children_request(99, 'invalid');
    }

    // -------------------------------------------------------------------------
    // Tests for setup_items_table()
    // -------------------------------------------------------------------------

    /**
     * setup_items_table() must return a flexible_table instance.
     */
    public function test_setup_items_table_returns_flexible_table(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);
        $result = $manager->call_setup_items_table();

        $this->assertInstanceOf(\flexible_table::class, $result);
    }

    /**
     * setup_items_table() in CHANGEORDERASK mode must use id=sortitems.
     */
    public function test_setup_items_table_changeorderask_id(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_CHANGEORDERASK);
        $table = $manager->call_setup_items_table();

        $this->assertEquals('sortitems', $table->attributes['id']);
    }

    /**
     * setup_items_table() in normal mode must use id=manageitems.
     */
    public function test_setup_items_table_normal_mode_id(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);
        $table = $manager->call_setup_items_table();

        $this->assertEquals('manageitems', $table->attributes['id']);
    }

    // -------------------------------------------------------------------------
    // Tests for get_icon_set()
    // -------------------------------------------------------------------------

    /**
     * get_icon_set() must return an array with expected keys.
     */
    public function test_get_icon_set_has_expected_keys(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $result = $manager->call_get_icon_set();

        $this->assertArrayHasKey('editstr', $result);
        $this->assertArrayHasKey('parentelementstr', $result);
        $this->assertArrayHasKey('reorderstr', $result);
        $this->assertArrayHasKey('hidestr', $result);
        $this->assertArrayHasKey('showstr', $result);
        $this->assertArrayHasKey('deletestr', $result);
        $this->assertArrayHasKey('outdentstr', $result);
        $this->assertArrayHasKey('indentstr', $result);
        $this->assertArrayHasKey('moveherestr', $result);
        $this->assertArrayHasKey('availablestr', $result);
        $this->assertArrayHasKey('reservedstr', $result);
        $this->assertArrayHasKey('unreservablestr', $result);
        $this->assertArrayHasKey('unsearchablestr', $result);
        $this->assertArrayHasKey('unavailablestr', $result);
        $this->assertArrayHasKey('forcedoptionalstr', $result);
    }

    /**
     * get_icon_set() icons must be pix_icon instances.
     */
    public function test_get_icon_set_icons_are_pix_icon(): void {
        $this->resetAfterTest();

        [$manager] = $this->make_manager();
        $result = $manager->call_get_icon_set();

        foreach ($result as $key => $value) {
            if (str_ends_with($key, 'icn')) {
                $this->assertInstanceOf(\pix_icon::class, $value, "Key $key must be a pix_icon");
            }
        }
    }

    // -------------------------------------------------------------------------
    // Tests for get_action_icons()
    // -------------------------------------------------------------------------

    /**
     * get_action_icons() in CHANGEORDERASK mode must return empty string.
     */
    public function test_get_action_icons_changeorderask_returns_empty(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_CHANGEORDERASK);
        $manager->set_itemcount(2);
        $manager->set_hassubmissions(false);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $item = new \surveyprofield_character\item($cm, $surveypro, $itemid, false);

        $iconset = $manager->call_get_icon_set();
        $result = $manager->call_get_action_icons($item, $iconset);

        $this->assertEquals('', $result);
    }

    /**
     * get_action_icons() in normal mode must return non-empty string.
     */
    public function test_get_action_icons_normal_mode_returns_html(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);
        $manager->set_itemcount(2);
        $manager->set_hassubmissions(false);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $item = new \surveyprofield_character\item($cm, $surveypro, $itemid, false);

        $iconset = $manager->call_get_icon_set();
        $result = $manager->call_get_action_icons($item, $iconset);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    // -------------------------------------------------------------------------
    // Tests for build_item_row()
    // -------------------------------------------------------------------------

    /**
     * build_item_row() must return an array with exactly 9 elements.
     */
    public function test_build_item_row_returns_9_elements(): void {
        $this->resetAfterTest();

        $this->setAdminUser();
        [$manager, $surveypro, $cm] = $this->make_manager();
        $manager->set_mode(SURVEYPRO_NEWRESPONSEMODE);
        $manager->set_itemcount(1);
        $manager->set_hassubmissions(false);

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $itemid = $generator->create_item_character($surveypro, ['required' => 0]);
        $item = new \surveyprofield_character\item($cm, $surveypro, $itemid, false);

        $iconset = $manager->call_get_icon_set();
        $result = $manager->call_build_item_row($item, $iconset);

        $this->assertIsArray($result);
        $this->assertCount(9, $result);
    }
}
