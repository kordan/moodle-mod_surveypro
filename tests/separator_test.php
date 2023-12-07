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
use surveyprofield_radiobutton;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot.'/mod/surveypro/lib.php');

/**
 * The class to verify all the lib.php global functions do work as expected.
 *
 * @package   mod_surveypro
 * @copyright 2015 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \surveyprofield_radiobutton
 */
class separator_test extends advanced_testcase {

    /**
     * Data provider for test_userform_get_separator()
     *
     * Cases to be tested by surveyprotemplate_get_plugin_name_provider
     *
     * 12 cases because 12 (3x2x2) is number of cases given from cartesian product of those three sets:
     *     $itemman->defaultoption = SURVEYPRO_CUSTOMDEFAULT;   // Needed to define $invitation.
     *     $itemman->defaultoption = SURVEYPRO_INVITEDEFAULT;   // Needed to define $invitation.
     *     $itemman->defaultoption = SURVEYPRO_NOANSWERDEFAULT; // Needed to define $invitation.
     *
     *     $itemman->labelother = '';                        // Needed to define $addother.
     *     $itemman->labelother = 'Other (please, specify)'; // Needed to define $addother.
     *
     *     $itemman->required = 1; // Needed to define $mandatory.
     *     $itemman->required = 0; // Needed to define $mandatory.
     */
    public function userform_get_separator_provider(): array {
        $userinput = [];
        $userinput['defaultoption'] = SURVEYPRO_CUSTOMDEFAULT;
        $userinput['labelother'] = '';
        $userinput['required'] = 1;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>', ' ', '<br>'];
        $test01 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_INVITEDEFAULT;
        $userinput['labelother'] = '';
        $userinput['required'] = 1;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>', ' ', '<br>'];
        $test02 = [$userinput, $expected];

        // Test03: defaultoption = SURVEYPRO_NOANSWERDEFAULT is not compatible with required = 1

        $userinput['defaultoption'] = SURVEYPRO_CUSTOMDEFAULT;
        $userinput['labelother'] = 'Other (please, specify)';
        $userinput['required'] = 1;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>', ' ', '<br>'];
        $test04 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_INVITEDEFAULT;
        $userinput['labelother'] = 'Other (please, specify)';
        $userinput['required'] = 1;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>', ' ', '<br>'];
        $test05 = [$userinput, $expected];

        // Test06: defaultoption = SURVEYPRO_NOANSWERDEFAULT is not compatible with required = 1

        $userinput['defaultoption'] = SURVEYPRO_CUSTOMDEFAULT;
        $userinput['labelother'] = '';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test07 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_INVITEDEFAULT;
        $userinput['labelother'] = '';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test08 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_NOANSWERDEFAULT;
        $userinput['labelother'] = '';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test09 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_CUSTOMDEFAULT;
        $userinput['labelother'] = 'Other (please, specify)';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test10 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_INVITEDEFAULT;
        $userinput['labelother'] = 'Other (please, specify)';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test11 = [$userinput, $expected];

        $userinput['defaultoption'] = SURVEYPRO_NOANSWERDEFAULT;
        $userinput['labelother'] = 'Other (please, specify)';
        $userinput['required'] = 0;
        $userinput['options'] = 'mum\ndad';
        $expected = ['<br>'];
        $test12 = [$userinput, $expected];

        return [
            $test01,
            $test02,
            $test04,
            $test05,
            $test07,
            $test08,
            $test09,
            $test10,
            $test11,
            $test12,
        ];
    }

    /**
     * test_userform_get_separator
     *
     * @dataProvider userform_get_separator_provider
     * @param object $userinput The passed user input
     * @param object $expected The expected result
     */
    public function test_userform_get_separator($userinput, $expected) {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $params = ['course' => $course->id, 'name' => 'One more surveypro'];
        $surveypro = $this->getDataGenerator()->create_module('surveypro', $params);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $itemman = new surveyprofield_radiobutton\item($cm, $surveypro, null, false);

        // Define parameters.
        $itemman->set_defaultoption($userinput['defaultoption']); // Needed to define $invitation.
        $itemman->set_labelother($userinput['labelother']);       // Needed to define $addother.
        $itemman->set_required($userinput['required']);           // Needed to define $mandatory.
        $itemman->set_options($userinput['options']);             // Needed to define $labels.

        $returned = $itemman->userform_get_separator();
        $this->assertEquals($expected, $returned);
    }
}
