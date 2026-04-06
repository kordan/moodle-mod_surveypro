@mod @mod_surveypro @surveyprofield @surveyprofield_boolean
Feature: Submit using a boolean item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a boolean item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for boolean item
    Given the following "courses" exist:
      | fullname                         | shortname               | category |
      | Test submission for boolean item | Boolean submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role           |
      | student1 | Boolean submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                              | course                  |
      | surveypro | Boolean test | To test submission of boolean item | Boolean submission test |
    And surveypro "Boolean test" has the following items:
      | type  | plugin  | settings                                                                    |
      | field | boolean | {"content":"Is it true?", "required":"1", "customnumber":"4a", "style":"0"} |
      | field | boolean | {"content":"Is it true?", "required":"1", "customnumber":"4b", "style":"1"} |
      | field | boolean | {"content":"Is it true?", "required":"1", "customnumber":"4c", "style":"2"} |
    And I am on the "Boolean test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | 4a Is it true?       | Yes |
      | id_field_boolean_2_0 | 1   |
      | id_field_boolean_3_1 | 1   |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "Yes"
    Then the field "id_field_boolean_2_0" matches value "1"
    Then the field "id_field_boolean_3_1" matches value "1"
