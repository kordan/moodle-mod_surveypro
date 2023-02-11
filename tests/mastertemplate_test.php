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
 * @covers    \mod_surveypro\mastertemplate
 */
class mastertemplate_test extends advanced_testcase {

    /**
     * Data provider for surveyprotemplate_get_plugin_name_provider()
     *
     * Cases to be tested by test_surveyprotemplate_get_plugin_name
     */
    public function surveyprotemplate_get_plugin_name_provider() {
        return [
            'test01' => ['correct_pluginname', 'correct_pluginname'],
            'test02' => ['123startswitnumbers', 'startswitnumbers'],
            'test03' => ['abc-123', 'abc_123'],
            'test04' => ['abc-_123', 'abc_123'],
            'test05' => ['abc----_____123', 'abc_123'],
            'test06' => ['12345.+?)', 'mtemplate_onemoresurveypro']
        ];
    }

    /**
     * test_surveyprotemplate_get_plugin_name
     *
     * @dataProvider surveyprotemplate_get_plugin_name_provider
     * @param object $userinput The passed user input
     * @param object $expected The expected result
     */
    public function test_surveyprotemplate_get_plugin_name($userinput, $expected) {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $params = ['course' => $course->id, 'name' => 'One more surveypro'];
        $surveypro = $this->getDataGenerator()->create_module('surveypro', $params);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);

        $mtemplateman = new mastertemplate($cm, $context, $surveypro);
        $mtemplateman->formdata = (object)['mastertemplatename' => $userinput];
        // $mtemplateman->formdata->mastertemplatename = $userinput;
        $returned = $mtemplateman->get_plugin_name();
        $this->assertEquals($expected, $returned);
    }
}
