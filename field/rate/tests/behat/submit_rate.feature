@mod @mod_surveypro @surveyprofield @surveyprofield_rate
Feature: Submit using a rate item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a rate item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for rate item
    Given the following "courses" exist:
      | fullname                      | shortname            | category |
      | Test submission for rate item | Rate submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role    |
      | student1 | Rate submission test | student |
    And the following "activities" exist:
      | activity  | name      | intro                           | course               |
      | surveypro | Rate test | To test submission of date item | Rate submission test |
    And surveypro "Rate test" has the following items:
      | type  | plugin | options                                            |
      | field | rate   | {"position":"1", "style":"0", "customnumber":"h1"} |
      | field | rate   | {"position":"2", "style":"1", "customnumber":"h2"} |
    When I am on the "Rate test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the following fields to these values:
      | id_field_rate_1_0_0 | 1              |
      | id_field_rate_1_1_1 | 1              |
      | id_field_rate_1_2_2 | 1              |
      | id_field_rate_1_3_3 | 1              |
      | id_field_rate_2_0   | Very confident |
      | id_field_rate_2_1   | Very confident |
      | id_field_rate_2_2   | Very confident |
      | id_field_rate_2_3   | Very confident |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then the field "id_field_rate_1_0_0" matches value "1"
    Then the field "id_field_rate_1_1_1" matches value "1"
    Then the field "id_field_rate_1_2_2" matches value "1"
    Then the field "id_field_rate_1_3_3" matches value "1"
    Then I should not see "Mother tongue" in the "[data-fieldtype=select]" "css_element"
    Then I should see "Very confident"
    Then I should not see "Somewhat confident" in the "[data-fieldtype=select]" "css_element"
    Then I should not see "Not confident at all" in the "[data-fieldtype=select]" "css_element"
