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
 * Steps definitions related to mod_surveypro.
 *
 * @package    mod_surveypro
 * @category   test
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

class behat_mod_surveypro extends behat_base {

    /**
     * Fill a textarea with a multiline content.
     *
     * @Given /^I fill the textarea "(?P<textarea_name>(?:[^"]|\\")*)" with multiline content "(?P<multiline_content>(?:[^"]|\\")*)"$/
     */
    public function i_fill_the_textarea_with_multiline_content($textareafield, $multilinevalue) {
        $textareafield = $this->escape($textareafield);
        $multilinevalue = implode("\n", explode('\n', $multilinevalue));

        return array(
            new Given("I set the field \"$textareafield\" to \"$multilinevalue\"")
        );
    }

    /**
     * Check the number of displayed submissions.
     *
     * @Then /^I should see "(?P<given_number>\d+)" submissions displayed$/
     *
     * @param integer $givennumber The supposed count of $locator
     * @return void
     */
    public function i_should_see_submissions($givennumber) {
        // Getting the container where the text should be found.
        $container = $this->get_selected_node('table', 'submissions');

        $nodes = $container->findAll('xpath', "//tr[contains(@id, 'submissionslist') and not(contains(@class, 'emptyrow'))]");
        $tablerows = count($nodes);

        if (intval($givennumber) !== $tablerows) {
            $message = sprintf('%d rows found in the "submission" table, but should be %d.', $tablerows, $givennumber);
            throw new ExpectationException($message, $this->getsession());
        }
    }

    /**
     * Add the specified items to the specified surveypro.
     *
     * The first row should be column names:
     * | type | plugin |
     * that are required.
     *
     * @param string $surveyproname the name of the surveypro to add items to.
     * @param TableNode $data information about the items to add.
     *
     * @Given /^surveypro "([^"]*)" contains the following items:$/
     */
    public function surveypro_has_the_following_items($surveyproname, TableNode $data) {
        global $DB;

        $surveypro = $DB->get_record('surveypro', array('name' => $surveyproname), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id, $surveypro->course, false, MUST_EXIST);

        // Add the questions.
        foreach ($data->getHash() as $surveyprodata) {
            if (!array_key_exists('type', $surveyprodata)) {
                throw new ExpectationException('When adding an item to a surveypro, ' .
                        'the type column is required.', $this->getSession());
            }
            if (!array_key_exists('plugin', $surveyprodata)) {
                throw new ExpectationException('When adding item to a surveypro, ' .
                        'the plugin column is required.', $this->getSession());
            }

            $type = clean_param($surveyprodata['type'], PARAM_TEXT);
            $plugin = clean_param($surveyprodata['plugin'], PARAM_TEXT);
            // Get dummy contents based on type and plugin.
            // $record = $this->get_dummy_contents($type, $plugin);
            $record = get_dummy_contents($type, $plugin);

            // Add the item.
            $item = surveypro_get_item($cm, $surveypro, 0, $type, $plugin);
            $item->item_save($record);
        }
    }

    /**
     * Click on an entry in the language menu.
     * @Given /^I follow "(?P<nodetext_string>(?:[^"]|\\")*)" in the language menu$/
     *
     * @param string $nodetext
     * @return bool|void
     */
    public function i_follow_in_the_language_menu($nodetext) {
        $steps = array();

        if ($this->running_javascript()) {
            // The language menu must be expanded when JS is enabled.
            $xpath = "//li[contains(concat(' ', @class, ' '), ' langmenu ')]//a[contains(concat(' ', @class, ' '), ' dropdown-toggle ')]";
            $steps[] = new Given('I click on "'.$xpath.'" "xpath_element"');
        }

        // Now select the link.
        // The CSS path is always present, with or without JS.
        $csspath = ".langmenu .dropdown-menu";
        // We need this because the lang menu has some hidden chars and we'll need to match them if the original text
        // has code between parenthesis. See get_list_of_translations() implementation.
        $nodetext = str_replace(
            array('(', ')'),
            array(json_decode('"\u200E"') . '(', ')' . json_decode('"\u200E"')),
            $nodetext);
        $steps[] = new Given('I click on "'.$nodetext.'" "link" in the "'.$csspath.'" "css_element"');

        return $steps;
    }
}
