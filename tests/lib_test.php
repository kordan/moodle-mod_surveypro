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

namespace mod_surveypro;

use advanced_testcase;

/**
 * The class to verify all the lib.php global functions do work as expected.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib_test extends advanced_testcase {

    /**
     * surveypro_cutdownstring provider
     *
     * Cases to be tested by surveypro_cutdownstring
     */
    public function surveypro_cutdownstring_provider() {
        return [
            'plain_short_string' => ['Hello world!', 60, 'Hello world!'],
            'utf8_short_string' => ['Hello 🌍 !',   60, 'Hello 🌍 !'],
            'plain_cut_string' => ['Hello world!', 10, 'Hello w...'],
            'utf8_cut_string' => ['Hello 🌍 !',   9, 'Hello 🌍 !']
        ];
    }

    /**
     * surveypro_cutdownstring
     *
     * @covers ::surveypro_cutdownstring
     * @dataProvider surveypro_cutdownstring_provider
     * @param string $plainstring The string being passed
     * @param int $maxlength The length passed
     * @param string $expected The expected result
     */
    public function test_surveypro_cutdownstring($plainstring, $maxlength, $expected) {
        // Let's test that surveypro_cutdownstring() works as expected.
        $this->assertSame($expected, surveypro_cutdownstring($plainstring, $maxlength));
    }

    /**
     * Data provider for surveypro_pre_process_checkboxes()
     *
     * Cases to be tested by surveypro_pre_process_checkboxes
     */
    public function surveypro_pre_process_checkboxes_provider() {
        return [
            'test01' => [
                (object) [
                    'captcha' => 1
                ],
                (object) [
                    'newpageforchild' => 0,
                    'neverstartedemail' => 0,
                    'keepinprogress' => 0,
                    'history' => 0,
                    'anonymous' => 0,
                    'captcha' => 1
                ]
            ],
            'test02' => [
                (object) [
                    'captcha' => 1,
                    'history' => 1
                ],
                (object) [
                    'newpageforchild' => 0,
                    'neverstartedemail' => 0,
                    'keepinprogress' => 0,
                    'history' => 1,
                    'anonymous' => 0,
                    'captcha' => 1
                ]
            ],
            'test03' => [
                (object) [
                    'newpageforchild' => 1,
                    'neverstartedemail' => 1,
                    'keepinprogress' => 1,
                    'anonymous' => 1
                ],
                (object) [
                    'newpageforchild' => 1,
                    'neverstartedemail' => 1,
                    'keepinprogress' => 1,
                    'history' => 0,
                    'anonymous' => 1,
                    'captcha' => 0
                ]
            ]
        ];
    }

    /**
     * test_surveypro_pre_process_checkboxes
     *
     * @covers ::surveypro_pre_process_checkboxes
     * @dataProvider surveypro_pre_process_checkboxes_provider
     * @param object $userinput The passed user input
     * @param object $expected The expected result
     */
    public function test_surveypro_pre_process_checkboxes($userinput, $expected) {
        // Let's test that surveypro_pre_process_checkboxes() works as expected.
        surveypro_pre_process_checkboxes($userinput);
        $this->assertEquals($expected, $userinput);
    }
}
