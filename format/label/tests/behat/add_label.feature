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
    And the following "activities" exist:
      | activity  | name       | intro                          | course    |
      | surveypro | Label test | To test addition of label item | Add label |
    And I am on the "Label test" "surveypro activity" page logged in as "teacher1"

    And I set the field "typeplugin" to "Label"
    And I press "Add"

    And I expand all fieldsets
    And I set the field "Content" to "This is just a comment"
    And I press "Add"
