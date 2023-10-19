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
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role           |
      | teacher1 | Select submission test | editingteacher |
      | student1 | Select submission test | student        |
    And the following "activities" exist:
      | activity  | name        | intro                             | course                 |
      | surveypro | Select test | To test submission of select item | Select submission test |
    And I am on the "Select test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Select"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Which summer holidays place do you prefer? |
      | Required          | 1                                          |
      | Option "other"    | other (specify)                            |
    And I set the multiline field "Options" to "\n\nsea\n     mountain\nlake\n\nhills\n\n\n\ndesert\n\n"
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Select test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the field "id_surveypro_field_select_1" to "hills"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions

    # student1 submits
    And I press "New response"
    And I set the field "id_surveypro_field_select_1" to "other (specify)"
    And I press "Submit"
    Then I should see "Please add the text required by your selection"
    And I set the field "id_surveypro_field_select_1_text" to "flying in the sky"
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "2" submissions
