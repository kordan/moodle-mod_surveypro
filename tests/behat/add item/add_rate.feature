@mod @mod_surveypro
Feature: verify a rate item can be added to a survey
  In order to verify rate items can be added to a survey
  As a teacher
  I add a rate item to a survey

  @javascript
  Scenario: add rate item
    Given the following "courses" exist:
      | fullname      | shortname | category | groupmode |
      | Add rate item | Add rate  | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course   | role           |
      | teacher1 | Add rate | editingteacher |
    And I log in as "teacher1"
    And I follow "Add rate item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                         |
      | Description | This is a surveypro to add a rate item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Please order these foreign languages according to your preferences |
      | Required       | 1                                                                  |
      | Indent         | 0                                                                  |
      | Element number | 13a                                                                |
      | Rate style     | radio buttons                                                      |
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nQuite well\nNot sufficient\nCompletely unknown"
    And I press "Add"

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Please order these foreign languages according to your preferences |
      | Required       | 1                                                                  |
      | Indent         | 0                                                                  |
      | Element number | 13b                                                                |
      | Rate style     | dropdown menu                                                      |
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nQuite well\nNot sufficient\nCompletely unknown"
    And I press "Add"
