@mod @mod_surveypro
Feature: verify a pagebreak item can be added to a survey
  In order to verify pagebreak items can be added to a survey
  As a teacher
  I add a pagebreak item to a survey

  @javascript
  Scenario: add pagebreak item
    Given the following "courses" exist:
      | fullname           | shortname     | category | groupmode |
      | Add pagebreak item | Add pagebreak | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Add pagebreak | editingteacher |
    And I log in as "teacher1"
    And I follow "Add pagebreak item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Surveypro test                              |
      | Description | This is a surveypro to add a pagebreak item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Page break"
    And I press "Add"

    And I press "Add"
