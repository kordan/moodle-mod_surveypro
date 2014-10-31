@mod @mod_surveypro
Feature: verify a fieldset item can be added to a survey
  In order to verify fieldset items can be added to a survey
  As a teacher
  I add a fieldset item to a survey

  @javascript
  Scenario: add fieldset item
    Given the following "courses" exist:
      | fullname          | shortname    | category | groupmode |
      | Add fieldset item | Add fieldset | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course       | role           |
      | teacher1 | Add fieldset | editingteacher |
    And I log in as "teacher1"
    And I follow "Add fieldset item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                             |
      | Description    | This is a surveypro to add a fieldset item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Fieldset"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | A bunch of items |
    And I press "Add"
