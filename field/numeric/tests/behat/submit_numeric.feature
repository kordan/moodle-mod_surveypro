@mod @mod_surveypro @surveyprofield @surveyprofield_numeric
Feature: Submit using a numeric item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a numeric item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for numeric item
    Given the following "courses" exist:
      | fullname                         | shortname               | category |
      | Test submission for numeric item | Numeric submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role           |
      | teacher1 | Numeric submission test | editingteacher |
      | student1 | Numeric submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                         | course                  |
      | surveypro | Numeric test | To test submission of numeric | Numeric submission test |
    And I am on the "Numeric test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Numeric"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write your best approximation of π |
      | Required                 | 1                                  |
      | Indent                   | 0                                  |
      | Question position        | left                               |
      | Element number           | 11                                 |
      | Hide filling instruction | 1                                  |
      | Decimal positions        | 2                                  |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Numeric test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the field "11 Write your best approximation of π" to "3.14"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
