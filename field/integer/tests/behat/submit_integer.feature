@mod @mod_surveypro @surveyprofield @surveyprofield_integer
Feature: Submit using an integer item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an integer item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for integer item
    Given the following "courses" exist:
      | fullname                         | shortname               | category |
      | Test submission for integer item | Integer submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Integer submission test | editingteacher |
      | student1 | Integer submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                           | course                  |
      | surveypro | Integer test | To test submission of date item | Integer submission test |
    And I am on the "Integer test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How many people does your family counts? |
      | Required                 | 1                                        |
      | Indent                   | 0                                        |
      | Question position        | left                                     |
      | Element number           | 9                                        |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Integer test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the field "9 How many people does your family counts?" to "3"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
