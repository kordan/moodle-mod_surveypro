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
 * Surveypro behat test library
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

/**
 * Returns dummy contents for a surveypro item.
 *
 * Defaults are provided for each plugin but can be overridden
 * by passing custom parameters.
 *
 * @param string $type
 * @param string $plugin
 * @param array $customsettings
 * @return \stdClass
 */
function surveypro_get_dummy_contents(string $type, string $plugin, array $customsettings = []): \stdClass {

    $mandatory = [
        'itemid' => 0,
        'pluginid' => 0,
        'type' => $type,
        'plugin' => $plugin,
        // 'required' => 0, // it is a checkbox. To set it to 0 I must jump it.
        'indent' => 0,
        'position' => 0,
        'customnumber' => '',
        // 'hideinstruction' => 0, // it is a checkbox. To set it to 0 I must jump it.
        'variable' => '',
        'extranote' => '',
        'parentid' => 0,
        'parentcontent' => '',
        'contentformat' => 1,
        'content_editor' => ['format' => 1],
    ];
    // $mandatory[‘required’] sets a checkbox. If you want to leave it unselected, $mandatory[‘required’] must not exist.

    if ($type === SURVEYPRO_TYPEFIELD) {
        $map = [
            'age' => [
                'content_editor' => ['text' => 'How old were you when you learned to ride a bike?'],
                'defaultoption' => '2',
                'lowerboundyear' => '0',
                'lowerboundmonth' => '0',
                'upperboundyear' => '105',
                'upperboundmonth' => '11',
            ],
            'autofill' => [
                'content_editor' => ['text' => 'Your userid'],
                'element01' => 'userid',
                'element02' => '',
                'element03' => '',
                'element04' => '',
                'element05' => '',
            ],
            'boolean' => [
                'content_editor' => ['text' => 'Is it true?'],
                'style' => 0,
                'defaultoption' => 2,
                'downloadformat' => 'strfbool01',
            ],
            'character' => [
                'content_editor' => ['text' => 'Write down your email, please'],
                'pattern' => 'PATTERN_FREE',
                'defaultvalue' => '',
                'minlength' => 0,
                'maxlength' => 0,
            ],
            'checkbox' => [
                'content_editor' => ['text' => 'What do you usually get for breakfast?'],
                'options' => "milk\nsugar\njam\nchocolate",
                'labelother' => '',
                'defaultvalue' => '',
                'adjustment' => 0,
                'minimumrequired' => 0,
                'maximumrequired' => '0',
                'downloadformat' => '1',
            ],
            'date' => [
                'content_editor' => ['text' => 'When were you born?'],
                'defaultoption' => 2,
                'downloadformat' => 'strftime05',
                'lowerboundday' => '1',
                'lowerboundmonth' => '1',
                'lowerboundyear' => '1970',
                'upperboundday' => '31',
                'upperboundmonth' => '12',
                'upperboundyear' => '2020',
            ],
            'datetime' => [
                'content_editor' => ['text' => 'Please, write down date and time of your last flight to Los Angeles.'],
                'step' => '1',
                'defaultoption' => '2',
                'downloadformat' => 'strftime01',
                'lowerboundday' => '1',
                'lowerboundmonth' => '1',
                'lowerboundyear' => '1970',
                'lowerboundhour' => '0',
                'lowerboundminute' => '0',
                'upperboundday' => '31',
                'upperboundmonth' => '12',
                'upperboundyear' => '2020',
                'upperboundhour' => '23',
                'upperboundminute' => '59',
            ],
            'fileupload' => [
                'content_editor' => ['text' => 'Please, upload your CV in PDF'],
                'maxfiles' => 1,
                'maxbytes' => 0,
                'filetypes' => '*',
            ],
            'integer' => [
                'content_editor' => ['text' => 'How many people does your family counts?'],
                'defaultoption' => '2',
                'lowerbound' => '0',
                'upperbound' => '255',
            ],
            'multiselect' => [
                'content_editor' => ['text' => 'What do you usually get for breakfast?'],
                'options' => "milk\nsugar\njam\nchocolate",
                'defaultvalue' => '',
                'heightinrows' => '4',
                'minimumrequired' => '0',
                'downloadformat' => '1',
            ],
            'numeric' => [
                'content_editor' => ['text' => 'Type the best approximation of π you know'],
                'defaultvalue' => '',
                'decimals' => 0,
                'lowerbound' => '',
                'upperbound' => '',
            ],
            'radiobutton' => [
                'content_editor' => ['text' => 'Where do you usually spend your summer holidays?'],
                'options' => "sea\nmountain\nlake\nhills",
                'labelother' => '',
                'defaultoption' => '2',
                'downloadformat' => '1',
                'adjustment' => '1',
            ],
            'rate' => [
                'content_editor' => ['text' => 'How confident are you with the following languages?'],
                'options' => "EN\nES\nIT\nFR",
                'rates' => "Mother tongue\nVery confident\nSomewhat confident\nNot confident at all",
                'style' => '0',
                'defaultoption' => '2',
                'downloadformat' => '1',
            ],
            'recurrence' => [
                'content_editor' => ['text' => 'When do you usually celebrate your name-day?'],
                'defaultoption' => 2,
                'downloadformat' => 'strftime02',
                'lowerboundday' => '1',
                'lowerboundmonth' => '1',
                'upperboundday' => '31',
                'upperboundmonth' => '12',
            ],
            'select' => [
                'content_editor' => ['text' => 'Where do you usually spend your summer holidays?'],
                'options' => "sea\nmountain\nlake\nhills",
                'labelother' => '',
                'defaultoption' => '2',
                'downloadformat' => '1',
            ],
            'shortdate' => [
                'content_editor' => ['text' => 'When did you buy your current car?'],
                'defaultoption' => '2',
                'downloadformat' => 'strftime01',
                'lowerboundmonth' => '1',
                'lowerboundyear' => '1970',
                'upperboundmonth' => '12',
                'upperboundyear' => '2020',
            ],
            'textarea' => [
                'content_editor' => ['text' => 'Write a short description of yourself'],
                // 'useeditor' => 0, // it is a checkbox. To set it to 0 I must jump it.
                'arearows' => 10,
                'areacols' => 60,
                'minlength' => 0,
                'maxlength' => '',
            ],
            'time' => [
                'content_editor' => ['text' => 'At what time do you usually get up in the morning in a working day?'],
                'step' => '1',
                'defaultoption' => '2',
                'downloadformat' => 'strftime01',
                'lowerboundhour' => '0',
                'lowerboundminute' => '0',
                'upperboundhour' => '23',
                'upperboundminute' => '59',
            ],
        ];
    } else if ($type === SURVEYPRO_TYPEFORMAT) {
        $map = [
            'label' => [
                'content_editor' => ['text' => 'Welcome to this new instance of surveypro'],
                'leftlabel' => '',
            ],
            'pagebreak' => [],
            'fieldset' => [
                'content' => 'Grouped data inside',
            ],
            'fieldsetend' => [],
        ];
    }

    if (isset($map[$plugin])) {
        $defaults = $map[$plugin];
    } else {
        $defaults = [];
    }

    $data = array_replace_recursive($mandatory, $defaults, $customsettings);

    return (object)$data;
}
