@mod @mod_surveypro @surveyprofield @surveyprofield_select
Feature: Submit using a select item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a select item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for select item
    Given the following "courses" exist:
      | fullname                        | shortname              | category |
      | Test submission for select item | Select submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role    |
      | student1 | Select submission test | student |
    And the following "activities" exist:
      | activity  | name        | intro                             | course                 |
      | surveypro | Select test | To test submission of select item | Select submission test |
    And surveypro "Select test" has the following items:
      | type  | plugin | settings                                                                         |
      | field | select | {"labelother":"other (specify)", "options":"sea\nmountain\nlake\nhills\ndesert"} |
    And I am on the "Select test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I set the field "id_field_select_1" to "hills"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "hills"
