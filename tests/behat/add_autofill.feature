@mod @mod_surveypro
Feature: verify an autofill item can be added to a survey
  In order to verify autofill items can be added to a survey
  As a teacher
  I add an autofill item to a survey

  @javascript
  Scenario: add autofill item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add autofill item | Add autofill | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add autofill | editingteacher |
    And I log in as "teacher1"
    And I follow "Add autofill item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                              |
      | Description | This is a surveypro to add an autofill item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content             | Your user ID |
      | Indent              | 0            |
      | Question position   | left         |
      | Element number      | 3            |
      | id_element01_select | user ID      |
    And I press "Add"
