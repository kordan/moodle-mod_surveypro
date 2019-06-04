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
 * PHPUnit lib.php tests
 *
 * @package   mod_surveypro
 * @copyright 2019 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The class to verify all the lib.php global functions do work as expected.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_surveypro_lib extends advanced_testcase {

    /**
     * test_surveypro_cutdownstring provider
     *
     * Cases to be tested by test_surveypro_cutdownstring
     */
    public function test_surveypro_cutdownstring_provider() {
        return [
            'plain_short_string' => ['Hello world!', 60, 'Hello world!'],
            'utf8_short_string'  => ['Hello 🌍 !',   60, 'Hello 🌍 !'],
            'plain_cut_string'   => ['Hello world!', 10, 'Hello w...'],
            'utf8_cut_string'    => ['Hello 🌍 !',   10, 'Hello 🌍...']
        ];
    }

    /**
     * test_surveypro_cutdownstring
     *
     * @covers ::surveypro_cutdownstring
     * @dataProvider test_surveypro_cutdownstring_provider
     * @param string $plainstring The string being passed
     * @param int $maxlength The length passed
     * @param string $expected The expected result
     */
    public function test_surveypro_cutdownstring($plainstring, $maxlength, $expected) {
        // Let's test that surveypro_cutdownstring() works as expected.
        $this->assertSame($expected, surveypro_cutdownstring($plainstring, $maxlength));
    }
}
