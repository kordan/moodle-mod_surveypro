@mod @mod_surveypro @surveyprofield @surveyprofield_date
Feature: Submit using a date item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a date item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for date item
    Given the following "courses" exist:
      | fullname                      | shortname            | category |
      | Test submission for date item | Date submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | student1 | Date submission test | student        |
    And the following "activities" exist:
      | activity  | name      | intro                        | course               |
      | surveypro | Date test | To test submission of date item | Date submission test |
    And surveypro "Date test" has the following items:
      | type  | plugin | settings                                                                  |
      | field | date   | {"required":"1", "indent":"0", "customnumber":"7", "hideinstruction":"1"} |
    When I am on the "Date test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_date_1_day   | 16      |
      | id_field_date_1_month | October |
      | id_field_date_1_year  | 1988    |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "16"
    Then I should see "October"
    Then I should see "1988"
