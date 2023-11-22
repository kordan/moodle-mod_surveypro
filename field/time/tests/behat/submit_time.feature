@mod @mod_surveypro @surveyprofield @surveyprofield_time
Feature: Submit using a time item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a time item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for time item
    Given the following "courses" exist:
      | fullname                      | shortname            | category |
      | Test submission for time item | Time submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | teacher1 | Time submission test | editingteacher |
      | student1 | Time submission test | student        |
    And the following "activities" exist:
      | activity  | name      | intro                           | course               |
      | surveypro | Time test | To test submission of time item | Time submission test |
    And I am on the "Time test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Time"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | At what time do you usually get up in the morning in a working day? |
      | Required                 | 1                                                                   |
      | Element number           | 18                                                                  |
      | Hide filling instruction | 1                                                                   |
    And I press "Add"

    And I log out

    When I am on the "Time test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_surveypro_field_time_1_hour   | 7  |
      | id_surveypro_field_time_1_minute | 15 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
