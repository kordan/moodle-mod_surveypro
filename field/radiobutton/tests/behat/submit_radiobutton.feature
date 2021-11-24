@mod @mod_surveypro @surveyprofield @surveyprofield_radiobutton
Feature: make a submission test for "radiobutton" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a radio button item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for radio button item
    Given the following "courses" exist:
      | fullname                               | shortname                   | category |
      | Test submission for radio buttons item | Radiobutton submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                      | role           |
      | teacher1 | Radiobutton submission test | editingteacher |
      | student1 | Radiobutton submission test | student        |
    And the following "activities" exist:
      | activity  | name             | intro                                  | course                      |
      | surveypro | Radiobutton test | To test submission of radiobutton item | Radiobutton submission test |
    And I log in as "teacher1"
    And I am on "Test submission for radio buttons item" course homepage
    And I follow "Radiobutton test"

    And I set the field "typeplugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Which summer holidays place do you prefer? |
      | Required          | 1                                          |
      | Indent            | 0                                          |
      | Question position | left                                       |
      | Element number    | 12a                                        |
      | Adjustment        | vertical                                   |
    And I set the multiline field "Options" to "\n   sea\nmountain\n\n\nlake\nhills\n            desert\n\n\n      "
    And I press "Add"

    And I set the field "typeplugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Which summer holidays place do you prefer? |
      | Required          | 1                                          |
      | Indent            | 0                                          |
      | Question position | left                                       |
      | Element number    | 12b                                        |
      | Adjustment        | horizontal                                 |
    And I set the multiline field "Options" to "\n   sea\nmountain\n\n\nlake\nhills\n            desert\n\n\n      "
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Test submission for radio buttons item" course homepage
    And I follow "Radiobutton test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_1_3 | 1 |
      | id_surveypro_field_radiobutton_2_2 | 1 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
