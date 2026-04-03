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
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | student1 | Time submission test | student        |
    And the following "activities" exist:
      | activity  | name      | intro                           | course               |
      | surveypro | Time test | To test submission of time item | Time submission test |
    And surveypro "Time test" has the following items:
      | type  | plugin | options                                                             |
      | field | time   | {"content":"At what time...?", "required":"1", "customnumber":"18"} |
    When I am on the "Time test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_time_1_hour   | 7  |
      | id_field_time_1_minute | 15 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "07"
    Then I should see "15"
