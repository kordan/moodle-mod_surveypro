@mod @mod_surveypro @surveyproformat @surveyprofield_fieldsetend
Feature: verify a fieldsetend item can be added to a survey
  In order to verify fieldsetend items can be added to a survey
  As a teacher
  I add a fieldsetend item to a survey

  @javascript
  Scenario: add fieldsetend item
    Given the following "courses" exist:
      | fullname             | shortname       | category | groupmode |
      | Add fieldsetend item | Add fieldsetend | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Add fieldsetend | editingteacher |
    And the following "activities" exist:
      | activity  | name          | intro                                | course          |
      | surveypro | Fieldset test | To test addition of fieldsetend item | Add fieldsetend |
    And I log in as "teacher1"
    And I am on "Add fieldsetend item" course homepage
    And I follow "Fieldset test"

    And I set the field "typeplugin" to "Fieldset closure"
    And I press "Add"

    And I press "Add"
