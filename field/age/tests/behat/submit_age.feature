@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: Submit using an age item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an age item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for age item
    Given the following "courses" exist:
      | fullname                     | shortname           | category |
      | Test submission for age item | Age submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | student1 | Age submission test | student        |
    And the following "activities" exist:
      | activity  | name     | intro                          | course              |
      | surveypro | Age test | To test submission of age item | Age submission test |
    And surveypro "Age test" has the following items:
      | type  | plugin | settings                                                                                      |
      | field | age    | {"customnumber":"5a", "hideinstruction":"1", "defaultoption":1, "defaultvalue":"-2148552000"} |
    When I am on the "Age test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_age_1_year  | 23 |
      | id_field_age_1_month | 8  |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "23"
    Then I should see "8"
