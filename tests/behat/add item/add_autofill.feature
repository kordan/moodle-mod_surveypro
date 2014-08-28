@mod @mod_surveypro
Feature: verify an autofill item can be added to a survey
  In order to verify autofill items can be added to a survey
  As a teacher
  I add an autofill item to a survey

  @javascript
  Scenario: add some items
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Add autofill item                         |
      | Description | This is a surveypro to add each core item |
    And I follow "Add autofill item"

    And I set the field "plugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content             | Your user ID |
      | Indent              | 0            |
      | Question position   | left         |
      | Element number      | 3            |
      | id_element01_select | user ID      |
    And I press "Add"
