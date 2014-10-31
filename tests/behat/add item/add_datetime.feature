@mod @mod_surveypro
Feature: verify a datetime item can be added to a survey
  In order to verify datetime items can be added to a survey
  As a teacher
  I add a datetime item to a survey

  @javascript
  Scenario: add datetime item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add datetime item | Add datetime | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add datetime | editingteacher |
    And I log in as "teacher1"
    And I follow "Add datetime item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                             |
      | Description    | This is a surveypro to add a datetime item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Date and time [dd/mm/yyyy;hh:mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | In which day and at what time do you remember it happened? |
      | Required                 | 1                                                          |
      | Indent                   | 0                                                          |
      | Question position        | left                                                       |
      | Element number           | 8                                                          |
      | Hide filling instruction | 1                                                          |
    And I press "Add"
