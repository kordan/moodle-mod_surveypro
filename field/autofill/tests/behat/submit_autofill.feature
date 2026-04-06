@mod @mod_surveypro @surveyprofield @surveyprofield_autofill
Feature: Submit using an autofill item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an autofill item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for autofill item
    Given the following "courses" exist:
      | fullname                          | shortname                | category |
      | Test submission for autofill item | Autofill submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | student1 | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                   | role           |
      | student1 | Autofill submission test | student        |
    And the following "activities" exist:
      | activity  | name          | intro                               | course                   |
      | surveypro | Autofill test | To test submission of autofill item | Autofill submission test |
    And surveypro "Autofill test" has the following items:
      | type  | plugin   | settings                                                                    |
      | field | autofill | {"indent":"0", "element01":"Have a nice day ", "element02":"userfirstname"} |
    And I am on the "Autofill test" "surveypro activity" page logged in as student1

    # I want hiddenfield = 0 but it is a checkbox. So, in order to leave it untouched, I have to omit it.
    # This is the reason whu I do not add  "hiddenfield":"0"; in json.

    # student1 submits
    And I press "New response"
    # And I set the following fields to these values:
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "Have a nice day Student1"
