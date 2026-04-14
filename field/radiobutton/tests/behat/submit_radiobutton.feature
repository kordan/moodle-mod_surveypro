@mod @mod_surveypro @surveyprofield @surveyprofield_radiobutton
Feature: Submit using a radiobutton item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a radio button item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for radiobutton item
    Given the following "courses" exist:
      | fullname                               | shortname                   | category |
      | Test submission for radio buttons item | Radiobutton submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                      | role    |
      | student1 | Radiobutton submission test | student |
    And the following "activities" exist:
      | activity  | name             | intro                                  | course                      |
      | surveypro | Radiobutton test | To test submission of radiobutton item | Radiobutton submission test |
    And surveypro "Radiobutton test" has the following items:
      | type  | plugin      | settings                                                                                         |
      | field | radiobutton | {"content":"Which summer holidays place do you prefer?", "customnumber":"12a", "adjustment":"0"} |
      | field | radiobutton | {"content":"Which winter holidays place do you prefer?", "customnumber":"12b", "adjustment":"1"} |
    And I am on the "Radiobutton test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_radiobutton_1_3 | 1 |
      | id_field_radiobutton_2_2 | 1 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then the field "id_field_radiobutton_1_3" matches value "1"
    Then the field "id_field_radiobutton_2_2" matches value "1"
