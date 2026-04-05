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
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role    |
      | student1 | Numeric submission test | student |
    And the following "activities" exist:
      | activity  | name         | intro                         | course                  |
      | surveypro | Numeric test | To test submission of numeric | Numeric submission test |
    And surveypro "Numeric test" has the following items:
      | type  | plugin  | settings                                                      |
      | field | numeric | {"hideinstructions":"1", "customnumber":"11", "decimals":"2"} |
    When I am on the "Numeric test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the field "11 Type the best approximation of π you know" to "3.14"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then the field "id_field_numeric_1" matches value "3.14"
