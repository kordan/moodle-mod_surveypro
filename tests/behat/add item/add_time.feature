@mod @mod_surveypro
Feature: verify a time item can be added to a survey
  In order to verify time items can be added to a survey
  As a teacher
  I add a time item to a survey

  @javascript
  Scenario: add time item
    Given the following "courses" exist:
      | fullname      | shortname | category | groupmode |
      | Add time item | Add time  | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course   | role           |
      | teacher1 | Add time | editingteacher |
    And I log in as "teacher1"
    And I follow "Add time item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Surveypro test                         |
      | Description | This is a surveypro to add a time item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Time"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | At what time do you usually get breakfast? |
      | Required                 | 1                                          |
      | Indent                   | 0                                          |
      | Element number           | 18                                         |
      | Hide filling instruction | 1                                          |
    And I press "Add"
