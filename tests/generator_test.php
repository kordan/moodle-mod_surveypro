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
 * Generator tests class.
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_generator_testcase extends advanced_testcase {

    /**
     * Test_create_instance
     *
     * @return void
     */
    public function test_create_instance() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('surveypro', array('course' => $course->id)));
        $surveypro = $this->getDataGenerator()->create_module('surveypro', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('surveypro', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('surveypro', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('surveypro', array('id' => $surveypro->id)));

        $params = array('course' => $course->id, 'name' => 'One more surveypro');
        $surveypro = $this->getDataGenerator()->create_module('surveypro', $params);
        $this->assertEquals(2, $DB->count_records('surveypro', array('course' => $course->id)));
        $this->assertEquals('One more surveypro', $DB->get_field('surveypro', 'name', array('id' => $surveypro->id)));
    }

    /**
     * Test_apply_mastertemplate
     *
     * @return void
     */
    public function test_apply_mastertemplate() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('surveypro', array('course' => $course->id)));
        $surveypro = $this->getDataGenerator()->create_module('surveypro', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('surveypro', array('course' => $course->id)));

        $this->assertEquals(0, $DB->count_records('surveypro_item', array('surveyproid' => $surveypro->id)));
        $surveyprogenerator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $surveyprogenerator->apply_mastertemplate();
    }
}
