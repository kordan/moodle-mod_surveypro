@mod @mod_surveypro @surveyproformat @surveyprofield_pagebreak
Feature: Create a pagebreak item
  In order to verify pagebreak items can be added to a survey
  As a teacher
  I add a pagebreak item to a survey

  @javascript
  Scenario: Add pagebreak item
    Given the following "courses" exist:
      | fullname           | shortname     | category | groupmode |
      | Add pagebreak item | Add pagebreak | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Add pagebreak | editingteacher |
    And the following "activities" exist:
      | activity  | name           | intro                              | course        |
      | surveypro | Pagebreak test | To test addition of pagebreak item | Add pagebreak |
    And I am on the "Pagebreak test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Page break"
    And I press "Add"

    And I press "Add"
