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
 * @package   mod_surveypro
 * @category  test
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\PyStringNode as PyStringNode,
    Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Library for surveypro behat tests
 *
 * @package   mod_surveypro
 * @category  test
 * @copyright 2013 onwards kordan <kordan@mclink.it>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_surveypro extends behat_base {

    /**
     * Checks the field matches the multiline value.
     *
     * @Then /^the field "(?P<field_string>(?:[^"]|\\")*)" matches multiline:$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @param PyStringNode $value
     * @return void
     */
    public function the_field_matches_multiline($field, PyStringNode $value) {
        $this->execute('behat_forms::the_field_matches_value', array($field, (string)$value));
        // $this->the_field_matches_value($field, (string)$value);
    }

    /**
     * Check the number of displayed submissions.
     *
     * @throws ExpectationException
     * @Then /^I should see "(?P<given_number>\d+)" submissions$/
     * @param integer $givennumber
     * @return void|ExpectationException
     */
    public function i_should_see_submission($givennumber) {
        // Getting the container where the text should be found.
        $container = $this->get_selected_node('table', 'submissions');

        $nodes = $container->findAll('xpath', "//tr[contains(@id, 'submissionslist') and not(contains(@class, 'emptyrow'))]");
        $tablerows = count($nodes);

        if (intval($givennumber) !== $tablerows) {
            $message = sprintf('%d submissions found in the "submission" table, but should be %d.', $tablerows, $givennumber);
            throw new ExpectationException($message, $this->getsession());
        }
    }

    /**
     * Check the number of items with specified status.
     *
     * @throws ExpectationException
     * @Then /^I should see "(?P<given_number>\d+)" (?P<status>reserved|available|searchable|not searchable|visible|hidden) items$/
     * @param integer $givennumber
     * @param string $status
     * @return void|ExpectationException
     */
    public function i_should_see_items($givennumber, $status) {
        // Getting the container where the text should be found.
        $container = $this->get_selected_node('table', 'manageitems');

        switch ($status) {
            case 'reserved':
                $xpath = "//a[contains(@id,'makeavailable')] | //img[contains(@id, 'makeavailable')]";
                $nodes = $container->findAll('xpath', $xpath);
                break;
            case 'available':
                $xpath = "//a[contains(@id,'makereserved')] | //img[contains(@id, 'makereserved')]";
                $nodes = $container->findAll('xpath', $xpath);
                break;
            case 'searchable':
                $nodes = $container->findAll('xpath', "//img[contains(@id, 'removefromsearch')]");
                break;
            case 'not searchable':
                $nodes = $container->findAll('xpath', "//img[contains(@id, 'addtosearch')]");
                break;
            case 'visible':
                $nodes = $container->findAll('xpath', "//tr[contains(@id, 'itemslist') and not(contains(@class, 'emptyrow')) and not(contains(@class, 'dimmed'))]");
                break;
            case 'hidden':
                $nodes = $container->findAll('xpath', "//tr[contains(@id, 'itemslist') and not(contains(@class, 'emptyrow')) and contains(@class, 'dimmed')]");
                break;
        }
        $tablerows = count($nodes);

        if (intval($givennumber) == $tablerows) {
            return;
        }

        switch ($status) {
            case 'reserved':
                $message = sprintf('%d reserved items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
            case 'available':
                $message = sprintf('%d available items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
            case 'searchable':
                $message = sprintf('%d searchable items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
            case 'not searchable':
                $message = sprintf('%d unsearchable items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
            case 'visible':
                $message = sprintf('%d visible items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
            case 'hidden':
                $message = sprintf('%d hidden items found in the "item" table, but should be %d.', $tablerows, $givennumber);
                break;
        }
        throw new ExpectationException($message, $this->getsession());
    }

    /**
     * Add the specified items to the specified surveypro.
     *
     * The first row should be column names:
     * | type | plugin |
     * that are required
     *
     * @Given /^surveypro "([^"]*)" contains the following items:$/
     * @param string $surveyproname Name of the surveypro to add items to
     * @param TableNode $data information about the items to add
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
            $record = get_dummy_contents($type, $plugin);

            // Add the item.
            $item = surveypro_get_item($cm, $surveypro, 0, $type, $plugin);
            $item->item_save($record);
        }
    }

    /**
     * Click on an entry in the language menu
     *
     * @Given /^I follow "(?P<nodetext_string>(?:[^"]|\\")*)" in the language menu$/
     * @param string $nodetext
     */
    public function i_follow_in_the_language_menu($nodetext) {
        if ($this->running_javascript()) {
            // The language menu must be expanded when JS is enabled.
            $xpath = "//li[contains(concat(' ', @class, ' '), ' langmenu ')]//a[contains(concat(' ', @class, ' '), ' dropdown-toggle ')]";
            $this->execute('behat_general::i_click_on', array($xpath, 'xpath_element'));
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
        $this->execute('behat_general::i_click_on_in_the', array($nodetext, 'link', $csspath, 'css_element'));
    }

    /**
     * Click on the link in the TAB/page bar on top of the page.
     *
     * @When /^I follow "(?P<element_string>(?:[^"]|\\")*)" page in tab bar$/
     * @param string $nodetext Element we look for
     */
    public function i_follow_page_in_tab_bar($nodetext) {
        $xpath = "//ul[contains(@class,'nav-tabs')]//li//a[contains(@title, '".$nodetext."')]";
        $this->execute('behat_general::i_click_on', array($xpath, 'xpath_element'));
    }

    /**
     * Sets the specified value to the a multiline field.
     *
     * @Given /^I set the multiline field "(?P<field_string>(?:[^"]|\\")*)" to "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @param string $value
     * @return void
     */
    public function i_set_the_multiline_field_to($field, $value) {
        $string = str_replace('\n', "\n", $value);
        $this->execute('behat_forms::set_field_value', [$field, (string)$string]);
    }
}
