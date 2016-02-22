@mod @mod_surveypro @surveyprofield @surveyprofield_integer
Feature: make a submission test for "integer" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an integer item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for integer item
    Given the following "courses" exist:
      | fullname                         | shortname               | category |
      | Test submission for integer item | Integer submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Integer submission test | editingteacher |
      | student1 | Integer submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                           | course                   | idnumber   |
      | surveypro | Integer test | To test submission of date item | Integer submission test | surveypro1 |
    And I log in as "teacher1"
    And I follow "Test submission for integer item"
    And I follow "Integer test"

    And I set the field "typeplugin" to "Integer (small)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How many brothers/sisters do you have? |
      | Required                 | 1                                      |
      | Indent                   | 0                                      |
      | Question position        | left                                   |
      | Element number           | 9                                      |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for integer item"
    And I follow "Integer test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | 9: How many brothers/sisters do you have? | 3 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
