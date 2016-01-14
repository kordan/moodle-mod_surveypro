@mod @mod_surveypro @surveyproformat @surveyprofield_label
Feature: verify a label item can be added to a survey
  In order to verify label items can be added to a survey
  As a teacher
  I add a label item to a survey

  @javascript
  Scenario: add label item
    Given the following "courses" exist:
      | fullname       | shortname | category | groupmode |
      | Add label item | Add label | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher1 | Add label | editingteacher |
    And I log in as "teacher1"
    And I follow "Add label item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                          |
      | Description | This is a surveypro to add a label item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Label"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | This is just a comment |
    And I press "Add"
