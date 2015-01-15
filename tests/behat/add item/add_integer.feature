@mod @mod_surveypro
Feature: verify an integer item can be added to a survey
  In order to verify integer items can be added to a survey
  As a teacher
  I add an integer item to a survey

  @javascript
  Scenario: add integer item
    Given the following "courses" exist:
      | fullname         | shortname   | category | groupmode |
      | Add integer item | Add integer | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Add integer | editingteacher |
    And I log in as "teacher1"
    And I follow "Add integer item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                             |
      | Description    | This is a surveypro to add an integer item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How many siblings do you have? |
      | Required                 | 1                              |
      | Indent                   | 0                              |
      | Question position        | left                           |
      | Element number           | 9                              |
    And I press "Add"
