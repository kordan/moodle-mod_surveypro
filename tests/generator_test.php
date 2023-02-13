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
 * PHPUnit data generator test.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * PHPUnit label generator testcase
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_generator_testcase extends advanced_testcase {

    /**
     * Test_create_instance.
     *
     * @return void
     */
    public function test_create_instance() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('surveypro', ['course' => $course->id]));
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $this->assertEquals(1, $DB->count_records('surveypro', ['course' => $course->id]));
        $this->assertTrue($DB->record_exists('surveypro', ['course' => $course->id]));
        $this->assertTrue($DB->record_exists('surveypro', ['id' => $surveypro->id]));

        $params = ['course' => $course->id, 'name' => 'One more surveypro'];
        $surveypro = $this->getDataGenerator()->create_module('surveypro', $params);
        $this->assertEquals(2, $DB->count_records('surveypro', ['course' => $course->id]));
        $this->assertEquals('One more surveypro', $DB->get_field('surveypro', 'name', ['id' => $surveypro->id]));
    }

    /**
     * Test apply mastertemplate.
     *
     * @return void
     */
    public function test_apply_mastertemplate() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('surveypro', ['course' => $course->id]));
        $surveypro = $this->getDataGenerator()->create_module('surveypro', ['course' => $course->id]);
        $this->assertEquals(1, $DB->count_records('surveypro', ['course' => $course->id]));

        $this->assertEquals(0, $DB->count_records('surveypro_item', ['surveyproid' => $surveypro->id]));
        $surveyprogenerator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        // Commented for now, till we are able to apply mastertemplates from generator.
        // $surveyprogenerator->apply_mastertemplate();
    }
}
