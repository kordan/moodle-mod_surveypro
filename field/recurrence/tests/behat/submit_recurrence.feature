@mod @mod_surveypro @surveyprofield @surveyprofield_recurrence
Feature: make a submission test for "recurrence" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a recurrence item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission for recurrence item
    Given the following "courses" exist:
      | fullname                            | shortname                  | category |
      | Test submission for recurrence item | Recurrence submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                     | role           |
      | teacher1 | Recurrence submission test | editingteacher |
      | student1 | Recurrence submission test | student        |
    And the following "activities" exist:
      | activity  | name            | intro                           | course                     |
      | surveypro | Recurrence test | To test submission of date item | Recurrence submission test |
    And I am on the "Recurrence test" "surveypro activity" page logged in as teacher1

    And I set the field "typeplugin" to "Recurrence [dd/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When do you usually celebrate your name-day? |
      | Required                 | 1                                            |
      | Indent                   | 0                                            |
      | Question position        | left                                         |
      | Element number           | 14                                           |
      | Hide filling instruction | 1                                            |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Recurrence test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_recurrence_1_day   | 7    |
      | id_surveypro_field_recurrence_1_month | June |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
