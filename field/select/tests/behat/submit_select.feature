@mod @mod_surveypro @surveyprofield @surveyprofield_select
Feature: make a submission test for "select" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a select item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for select item
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
      | activity  | name        | intro                             | course                 | idnumber   |
      | surveypro | Select test | To test submission of select item | Select submission test | surveypro1 |
    And I log in as "teacher1"
    And I am on "Test submission for select item" course homepage
    And I follow "Select test"

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
    When I log in as "student1"
    And I am on "Test submission for select item" course homepage
    And I follow "Select test"
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
