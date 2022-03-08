@mod @mod_surveypro
Feature: editing a submission, autofill userID is not overwritten
  In order to test that personal data is not overwritten editing a submission
  As student1 and student2
  I fill a surveypro and edit it as different user

  @javascript
  Scenario: test that editing a submission, autofill userID is not overwritten
    Given the following "courses" exist:
      | fullname                   | shortname      | category | groupmode |
      | Course divided into groups | Course grouped | 0        | 0         |
    And the following "groups" exist:
      | name    | course         | idnumber |
      | Group 1 | Course grouped | G1       |
      | Group 2 | Course grouped | G2       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | student1  | user1    | student1@nowhere.net |
      | student2 | student2  | user2    | student2@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | teacher1 | Course grouped | editingteacher |
      | student1 | Course grouped | student        |
      | student2 | Course grouped | student        |
    And the following "permission overrides" exist:
      | capability                          | permission | role    | contextlevel | reference      |
      | mod/surveypro:editownsubmissions    | Allow      | student | Course       | Course grouped |
      | mod/surveypro:seeotherssubmissions  | Allow      | student | Course       | Course grouped |
      | mod/surveypro:editotherssubmissions | Allow      | student | Course       | Course grouped |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
    And the following "activities" exist:
      | activity  | name              | intro                                                              | course         |
      | surveypro | Preserve autofill | Test that editing a submission, autofill userID is not overwritten | Course grouped |

    When I am on the "Preserve autofill" "Activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | Group mode | Visible groups |
    And I press "Save and display"

    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content            | Your user ID |
      | Indent             | 0            |
      | Question position  | left         |
      | Element number     | 1            |
      | id_element01select | user ID      |
    And I press "Add"

    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content            | Your first name |
      | Question position  | left            |
      | Element number     | 2               |
      | id_element01select | user first name |
    And I press "Add"

    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content            | Your last name |
      | Question position  | left           |
      | Element number     | 3              |
      | id_element01select | user last name |
    And I press "Add"

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is it true?   |
      | Required          | 1             |
      | Question position | left          |
      | Element number    | 4             |
      | Element style     | dropdown menu |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Course divided into groups" course homepage
    And I follow "Preserve autofill"

    And I press "New response"

    # student1 submits his first response
    And I set the field "4: Is it true?" to "Yes"
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the field "4: Is it true?" to "No"
    And I press "Submit"

    And I log out

    # student2 logs in
    When I log in as "student2"
    And I am on "Course divided into groups" course homepage
    And I follow "Preserve autofill"
    And I follow "Responses"
    And I follow "edit_submission_row_1"
    Then the field "Your first name" matches value "student1"
    Then the field "Your last name" matches value "user1"
    Then the field "4: Is it true?" matches value "Yes"

    And I set the field "4: Is it true?" to "No"
    And I press "Submit"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Course divided into groups" course homepage
    And I follow "Preserve autofill"
    And I follow "Responses"

    And I follow "edit_submission_row_1"
    Then the field "Your first name" matches value "student1"
    Then the field "Your last name" matches value "user1"
    Then the field "4: Is it true?" matches value "No"

    And I log out

    # teacher1 logs in
    When I log in as "teacher1"
    And I am on "Course divided into groups" course homepage
    And I follow "Preserve autofill"
    And I follow "Responses" page in tab bar

    And I follow "edit_submission_row_1"
    Then the field "Your first name" matches value "student1"
    Then the field "Your last name" matches value "user1"
    Then the field "4: Is it true?" matches value "No"
    And I set the field "4: Is it true?" to "Yes"
    And I press "Submit"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Course divided into groups" course homepage
    And I follow "Preserve autofill"
    And I follow "Responses"

    And I follow "edit_submission_row_1"
    Then the field "Your first name" matches value "student1"
    Then the field "Your last name" matches value "user1"
    Then the field "4: Is it true?" matches value "Yes"

    And I log out
