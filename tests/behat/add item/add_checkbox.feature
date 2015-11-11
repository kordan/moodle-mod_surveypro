@mod @mod_surveypro
Feature: verify a checkbox item can be added to a survey
  In order to verify checkbox items can be added to a survey
  As a teacher
  I add a checkbox item to a survey

  @javascript
  Scenario: add checkbox item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add checkbox item | Add checkbox | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add checkbox | editingteacher |
    And I log in as "teacher1"
    And I follow "Add checkbox item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                             |
      | Description | This is a surveypro to add a checkbox item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | What do you usually eat for breakfast? |
      | Indent                   | 0                                      |
      | Question position        | left                                   |
      | Element number           | 5a                                     |
      | Minimum required options | 1                                      |
      | Adjustment               | vertical                               |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"

    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | What do you usually eat for breakfast? |
      | Indent                   | 0                                      |
      | Question position        | left                                   |
      | Element number           | 5b                                     |
      | Minimum required options | 1                                      |
      | Adjustment               | horizontal                             |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"
