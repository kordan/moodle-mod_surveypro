@mod @mod_surveypro
Feature: verify a fieldsetend item can be added to a survey
  In order to verify fieldsetend items can be added to a survey
  As a teacher
  I add a fieldsetend item to a survey

  @javascript
  Scenario: add fieldset item
    Given the following "courses" exist:
      | fullname             | shortname       | category | groupmode |
      | Add fieldsetend item | Add fieldsetend | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Add fieldsetend | editingteacher |
    And I log in as "teacher1"
    And I follow "Add fieldsetend item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                                |
      | Description    | This is a surveypro to add a fieldsetend item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Fieldset closure"
    And I press "Add"

    And I press "Add"
