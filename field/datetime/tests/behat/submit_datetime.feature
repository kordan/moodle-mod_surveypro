@mod @mod_surveypro @surveyprofield @surveyprofield_datetime
Feature: Submit using a datetime item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a datetime item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for datetime item
    Given the following "courses" exist:
      | fullname                          | shortname                | category |
      | Test submission for datetime item | Datetime submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                   | role           |
      | student1 | Datetime submission test | student        |
    And the following "activities" exist:
      | activity  | name          | intro                           | course                   |
      | surveypro | Datetime test | To test submission of date item | Datetime submission test |
    And surveypro "Datetime test" has the following items:
      | type  | plugin   | settings                                                                                   |
      | field | datetime | {"required":"1", "indent":"0", "position":"0", "customnumber":"5a", "hideinstruction":"1"} |
    When I am on the "Datetime test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_datetime_1_day    | 23     |
      | id_field_datetime_1_month  | August |
      | id_field_datetime_1_year   | 2010   |
      | id_field_datetime_1_hour   | 17     |
      | id_field_datetime_1_minute | 35     |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "23"
    Then I should see "August"
    Then I should see "2010"
    Then I should see "17"
    Then I should see "35"
