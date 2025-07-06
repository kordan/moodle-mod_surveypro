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
 * PHPUnit data generator tests.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The class to verify all the setup options do work as expected.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class setup_test extends advanced_testcase {

    /**
     * Test that all the global settings are stored properly and with expected defaults.
     *
     * Any new setting and/or change of default will require a change here.
     */
    public function test_global_config_defaults(): void {

        $this->assertCount(0, (array)get_config('surveypro'));
        $this->assertCount(4, (array)get_config('mod_surveypro'));

        // Verify all defaults are the expected ones.
        $this->assertEquals('168', get_config('mod_surveypro', 'maxinputdelay'));
        $this->assertEquals(0, get_config('mod_surveypro', 'extranoteinsearch'));
        $this->assertEquals(0, get_config('mod_surveypro', 'fillinginstructionsinsearch'));
    }
}
