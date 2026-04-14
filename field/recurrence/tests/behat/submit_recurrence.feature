@mod @mod_surveypro @surveyprofield @surveyprofield_recurrence
Feature: Submit using a recurrence item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a recurrence item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for recurrence item
    Given the following "courses" exist:
      | fullname                            | shortname                  | category |
      | Test submission for recurrence item | Recurrence submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                     | role    |
      | student1 | Recurrence submission test | student |
    And the following "activities" exist:
      | activity  | name            | intro                           | course                     |
      | surveypro | Recurrence test | To test submission of date item | Recurrence submission test |
    And surveypro "Recurrence test" has the following items:
      | type  | plugin     | settings                                                                                  |
      | field | recurrence | {"required":"1", "indent":"0", "position":"1", "customnumber":"1", "hideinstruction":"1"} |
    And I am on the "Recurrence test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_recurrence_1_day   | 7    |
      | id_field_recurrence_1_month | June |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "7"
    Then I should see "June"
