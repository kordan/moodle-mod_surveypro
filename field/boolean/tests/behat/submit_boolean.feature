@mod @mod_surveypro @surveyprofield @surveyprofield_boolean
Feature: make a submission test for "boolean" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a boolean item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission for boolean item
    Given the following "courses" exist:
      | fullname                         | shortname               | category |
      | Test submission for boolean item | Boolean submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role           |
      | teacher1 | Boolean submission test | editingteacher |
      | student1 | Boolean submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                              | course                  |
      | surveypro | Boolean test | To test submission of boolean item | Boolean submission test |
    And I am on the "Boolean test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is it true?   |
      | Required          | 1             |
      | Indent            | 0             |
      | Question position | left          |
      | Element number    | 4a            |
      | Element style     | dropdown menu |
    And I press "Add"

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is it true?            |
      | Required          | 1                      |
      | Indent            | 0                      |
      | Question position | left                   |
      | Element number    | 4b                     |
      | Element style     | vertical radio buttons |
    And I press "Add"

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is it true?              |
      | Required          | 1                        |
      | Indent            | 0                        |
      | Question position | left                     |
      | Element number    | 4c                       |
      | Element style     | horizontal radio buttons |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Boolean test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | 4a Is it true?                 | Yes |
      | id_surveypro_field_boolean_2_0 | 1   |
      | id_surveypro_field_boolean_3_1 | 1   |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
