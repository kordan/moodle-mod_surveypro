@mod @mod_surveypro @surveyprofield @surveyprofield_shortdate
Feature: Submit using a shortdate item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a shortdate item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for shortdate item
    Given the following "courses" exist:
      | fullname                           | shortname                 | category |
      | Test submission for shortdate item | Shortdate submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                    | role    |
      | student1 | Shortdate submission test | student |
    And the following "activities" exist:
      | activity  | name           | intro                           | course                    |
      | surveypro | Shortdate test | To test submission of shortdate | Shortdate submission test |
    And surveypro "Shortdate test" has the following items:
      | type  | plugin    | options                                                                   |
      | field | shortdate | {"required":"1", "indent":"0", "customnumber":"1", "hideinstruction":"1"} |
    When I am on the "Shortdate test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_shortdate_1_month | March |
      | id_field_shortdate_1_year  | 2005  |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "March"
    Then I should see "2005"
