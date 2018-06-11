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
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

/**
 * Provide mandatory dummy contents for each plugin item
 *
 * @param string $type
 * @param string $plugin
 * @return object
 */
function get_dummy_contents($type, $plugin) {

    $return = new stdClass();
    $return->itemid = 0;
    $return->pluginid = 0;
    $return->type = $type;
    $return->plugin = $plugin;
    $return->content_editor = array();
    $return->content_editor['format'] = 1;
    $return->parentid = 0;
    $return->parentcontent = '';

    if ($type == SURVEYPRO_TYPEFIELD) {
        if ($plugin == 'age') {
            $return->content_editor['text'] = 'How old were you when you learned to ride a bike?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->position = 0;
            $return->customnumber = '';
            $return->variable = '';
            $return->extranote = '';
            $return->parentid = 0;
            $return->parentcontent = '';
            $return->defaultoption = '2';
            $return->lowerboundyear = '0';
            $return->lowerboundmonth = '0';
            $return->upperboundyear = '105';
            $return->upperboundmonth = '11';

            return $return;
        }
        if ($plugin == 'autofill') {
            $return->content_editor['text'] = 'Just your userid';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->position = 0;
            $return->customnumber = '';
            $return->variable = '';
            $return->extranote = '';
            $return->element01select = 'userid';
            $return->element02select = '';
            $return->element03select = '';
            $return->element04select = '';
            $return->element05select = '';

            return $return;
        }
        if ($plugin == 'boolean') {
            $return->content_editor['text'] = 'Is this true?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->position = 0;
            $return->customnumber = '';
            $return->variable = '';
            $return->extranote = '';
            $return->style = 0;
            $return->defaultoption = 2;
            $return->downloadformat = 'strfbool01';
            return $return;
        }
        if ($plugin == 'character') {
            $return->content_editor['text'] = 'Write down your email';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->pattern = 'PATTERN_EMAIL';
            $return->defaultvalue = '';
            $return->minlength = 0;
            $return->maxlength = 0;
            return $return;
        }
        if ($plugin == 'checkbox') {
            $return->content_editor['text'] = 'What do you usually get for breakfast?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->options = "milk\nsugar\njam\nchocolate";
            $return->labelother = '';
            $return->defaultvalue = '';
            $return->adjustment = 0;
            $return->minimumrequired = 0;
            $return->downloadformat = '1';

            return $return;
        }
        if ($plugin == 'date') {
            $return->content_editor['text'] = 'When were you born?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->defaultoption = 2;
            $return->downloadformat = 'strftime05';
            $return->lowerboundday = '1';
            $return->lowerboundmonth = '1';
            $return->lowerboundyear = '1970';
            $return->upperboundday = '31';
            $return->upperboundmonth = '12';
            $return->upperboundyear = '2020';

            return $return;
        }
        if ($plugin == 'datetime') {
            $return->content_editor['text'] = 'Please, write down date and time of your last flight to Los Angeles.';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->step = '1';
            $return->defaultoption = '2';
            $return->downloadformat = 'strftime01';
            $return->lowerboundday = '1';
            $return->lowerboundmonth = '1';
            $return->lowerboundyear = '1970';
            $return->lowerboundhour = '0';
            $return->lowerboundminute = '0';
            $return->upperboundday = '31';
            $return->upperboundmonth = '12';
            $return->upperboundyear = '2020';
            $return->upperboundhour = '23';
            $return->upperboundminute = '59';

            return $return;
        }
        if ($plugin == 'fileupload') {
            $return->content_editor['text'] = 'Upload your CV in PDF format';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->position = 0;
            $return->customnumber = '';
            $return->variable = '';
            $return->extranote = '';
            $return->parentid = 0;
            $return->parentcontent = '';
            $return->maxfiles = 1;
            $return->maxbytes = 0;
            $return->filetypes = '*';

            return $return;
        }
        if ($plugin == 'integer') {
            $return->content_editor['text'] = 'How many people are in your family without you?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->defaultoption = '2';
            $return->lowerbound = '0';
            $return->upperbound = '255';

            return $return;
        }
        if ($plugin == 'multiselect') {
            $return->content_editor['text'] = 'What do you usually get for breakfast?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->options = "milk\nsugar\njam\nchocolate";
            $return->defaultvalue = '';
            $return->heightinrows = '4';
            $return->minimumrequired = '0';
            $return->downloadformat = '1';

            return $return;
        }
        if ($plugin == 'numeric') {
            $return->content_editor['text'] = 'Write the best approximation of π you can remember';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->defaultvalue = '';
            $return->decimals = 2;
            $return->lowerbound = '';
            $return->upperbound = '';

            return $return;
        }
        if ($plugin == 'radiobutton') {
            $return->content_editor['text'] = 'Where do you usually spend your summer holidays?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->options = "sea\nmountain\nlake\nhills";
            $return->labelother = '';
            $return->defaultoption = '2';
            $return->downloadformat = '1';
            $return->adjustment = '0';

            return $return;
        }
        if ($plugin == 'rate') {
            $return->content_editor['text'] = 'How confident are you with the following languages?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->options = "EN\nES\nIT\nFR";
            $return->rates = "Mother tongue\nVery confident\nNot enought\nCompletely unknown";
            $return->style = '0';
            $return->defaultoption = '2';
            $return->downloadformat = '1';

            return $return;
        }
        if ($plugin == 'recurrence') {
            $return->content_editor['text'] = 'When do you usually celebrate your name-day?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->defaultoption = 2;
            $return->downloadformat = 'strftime2';
            $return->lowerboundday = '1';
            $return->lowerboundmonth = '1';
            $return->upperboundday = '31';
            $return->upperboundmonth = '12';

            return $return;
        }
        if ($plugin == 'select') {
            $return->content_editor['text'] = 'Where do you usually spend your summer holidays?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->options = "sea\nmountain\nlake\nhills";
            $return->labelother = '';
            $return->defaultoption = '2';
            $return->downloadformat = '1';

            return $return;
        }
        if ($plugin == 'shortdate') {
            $return->content_editor['text'] = 'When did you buy your current car?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->defaultoption = '2';
            $return->downloadformat = 'strftime01';
            $return->lowerboundmonth = '1';
            $return->lowerboundyear = '1970';
            $return->upperboundmonth = '12';
            $return->upperboundyear = '2020';

            return $return;
        }
        if ($plugin == 'textarea') {
            $return->content_editor['text'] = 'Write a short description of yourself';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->useeditor = 1;
            $return->arearows = 10;
            $return->areacols = 60;
            $return->minlength = 0;
            $return->maxlength = '';

            return $return;
        }
        if ($plugin == 'time') {
            $return->content_editor['text'] = 'At what time do you usually get up in the morning in the working days?';
            $return->contentformat = 1;
            $return->indent = 0;
            $return->step = '1';
            $return->defaultoption = '2';
            $return->downloadformat = 'strftime1';
            $return->lowerboundhour = '0';
            $return->lowerboundminute = '0';
            $return->upperboundhour = '23';
            $return->upperboundminute = '59';

            return $return;
        }
    }

    if ($type == SURVEYPRO_TYPEFORMAT) {
        if ($plugin == 'label') {
            $return->content_editor['text'] = 'Welcome to this new instance of surveypro';
            $return->indent = 0;
            $return->customnumber = '';
            $return->leftlabel = '';

            return $return;
        }
        if ($plugin == 'pagebreak') {
            unset($return->content_editor);
            unset($return->parentid);
            unset($return->parentcontent);

            return $return;
        }
        if ($plugin == 'fieldset') {
            unset($return->content_editor);
            $return->content = 'Grouped data inside';

            return $return;
        }
        if ($plugin == 'fieldsetend') {
            unset($return->content_editor);

            return $return;
        }
    }
}
