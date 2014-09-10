@mod @mod_surveypro
Feature: verify a multiselect item can be added to a survey
  In order to verify multiselect items can be added to a survey
  As a teacher
  I add a multiselect item to a survey

  @javascript
  Scenario: add multiselect item
    Given the following "courses" exist:
      | fullname             | shortname       | category | groupmode |
      | Add multiselect item | Add multiselect | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Add multiselect | editingteacher |
    And I log in as "teacher1"
    And I follow "Add multiselect item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Surveypro test                                |
      | Description | This is a surveypro to add a multiselect item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Multiple selection"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                | What do you usually eat for breakfast? |
      | Indent                 | 0                                      |
      | Question position      | left                                   |
      | Element number         | 10                                     |
      | Minimum required items | 2                                      |
    And I fill the textarea "Options" with multiline content "milk\nsugar\njam\nchocolate"
    And I press "Add"
