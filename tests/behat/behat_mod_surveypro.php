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
    Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Surveypro-related steps definitions.
 *
 * @package    mod_surveypro
 * @category   test
 * @copyright  2013 onwards kordan <kordan@mclink.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * @return
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
}
