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
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role    |
      | student1 | Integer submission test | student |
    And the following "activities" exist:
      | activity  | name         | intro                           | course                  |
      | surveypro | Integer test | To test submission of date item | Integer submission test |
    And surveypro "Integer test" has the following items:
      | type  | plugin  |
      | field | integer |
    When I am on the "Integer test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the field "id_field_integer_1" to "3"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "3"
