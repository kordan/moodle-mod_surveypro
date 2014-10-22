@mod @mod_surveypro
Feature: verify a date item can be added to a survey
  In order to verify date items can be added to a survey
  As a teacher
  I add a date item to a survey

  @javascript
  Scenario: add date item
    Given the following "courses" exist:
      | fullname      | shortname | category | groupmode |
      | Add date item | Add date  | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course   | role           |
      | teacher1 | Add date | editingteacher |
    And I log in as "teacher1"
    And I follow "Add date item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                         |
      | Description    | This is a surveypro to add a date item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Date [dd/mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When were you born? |
      | Required                 | 1                   |
      | Indent                   | 0                   |
      | Question position        | left                |
      | Element number           | 7                   |
      | Hide filling instruction | 1                   |
    And I press "Add"
