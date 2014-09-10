@mod @mod_surveypro
Feature: verify a longtext item can be added to a survey
  In order to verify longtext items can be added to a survey
  As a teacher
  I add a longtext item to a survey

  @javascript
  Scenario: add longtext item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add longtext item | Add longtext | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add longtext | editingteacher |
    And I log in as "teacher1"
    And I follow "Add longtext item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Surveypro test                             |
      | Description | This is a surveypro to add a longtext item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Text (long)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Enter a short description of yourself |
      | Required                 | 1                                     |
      | Indent                   | 0                                     |
      | Question position        | left                                  |
      | Element number           | 16a                                   |
      | Hide filling instruction | 1                                     |
      | Use html editor          | 0                                     |
    And I press "Add"

    And I set the field "plugin" to "Text (long)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Enter a short description of yourself |
      | Required                 | 1                                     |
      | Indent                   | 0                                     |
      | Question position        | left                                  |
      | Element number           | 16b                                   |
      | Hide filling instruction | 1                                     |
      | Use html editor          | 1                                     |
    And I press "Add"
