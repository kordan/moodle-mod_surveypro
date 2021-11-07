@mod @mod_surveypro
Feature: test students can see submissions from their groupmates
  In order to test that students can only see submissions from their groupmates
  As student1 and student2 and student3
  I fill a surveypro and go to see responses

  @javascript
  Scenario: test each student can only see submissions from his/her groupmates
    Given the following "courses" exist:
      | fullname                   | shortname          | category | groupmode |
      | Course divided into groups | Only from my group | 0        | 0         |
    And the following "groups" exist:
      | name    | course             | idnumber |
      | Group 1 | Only from my group | G1       |
      | Group 2 | Only from my group | G2       |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | student1  | user1    | student1@nowhere.net |
      | student2 | student2  | user2    | student2@nowhere.net |
      | student3 | student3  | user3    | student3@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Only from my group | editingteacher |
      | student1 | Only from my group | student        |
      | student2 | Only from my group | student        |
      | student3 | Only from my group | student        |
    And the following "permission overrides" exist:
      | capability                         | permission | role    | contextlevel | reference          |
      | mod/surveypro:seeotherssubmissions | Allow      | student | Course       | Only from my group |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
      | student3 | G2    |

    And I log in as "teacher1"
    And I am on "Course divided into groups" course homepage
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Get only my group submission                                  |
      | Description | Test each student can only see submissions from his/her group |
      | Group mode  | Separate groups                                               |
    And I follow "Get only my group submission"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write down your email |
      | Indent                   | 0                     |
      | Question position        | left                  |
      | Element number           | 1                     |
      | Hide filling instruction | 0                     |
      | id_pattern               | email address         |
    And I press "Add"

    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is it true?   |
      | Required          | 1             |
      | Indent            | 0             |
      | Question position | left          |
      | Element number    | 2             |
      | Element style     | dropdown menu |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Course divided into groups" course homepage
    And I follow "Get only my group submission"
    And I follow "Responses"

    Then I should see "Nothing to display"

    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st1grp1ans1@nowhere.net |
      | 2: Is it true?           | Yes                     |
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1: Write down your email | st1grp1ans2@nowhere.net |
      | 2: Is it true?           | No                      |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "2" submissions

    And I log out

    # student2 logs in
    When I log in as "student2"
    And I am on "Course divided into groups" course homepage
    And I follow "Get only my group submission"
    And I follow "Responses"

    And I should see "Never" in the "student1 user1" "table_row"
    Then I should see "2" submissions

    And I press "New response"

    # student2 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st2grp1ans1@nowhere.net |
      | 2: Is it true?           | Yes                     |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should see "Never" in the "student1 user1" "table_row"
    And I should see "Never" in the "student2 user2" "table_row"
    Then I should see "3" submissions

    And I log out

    # student3 logs in
    When I log in as "student3"
    And I am on "Course divided into groups" course homepage
    And I follow "Get only my group submission"
    And I follow "Responses"

    Then I should see "Nothing to display"

    And I press "New response"

    # student3 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st3grp2ans1@nowhere.net |
      | 2: Is it true?           | Yes                     |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should not see "student1" in the "submissions" "table"
    And I should not see "student2" in the "submissions" "table"
    And I should see "Never" in the "student3 user3" "table_row"
    Then I should see "1" submissions

    And I log out

    # student1 goes to check for his personal submissions
    When I log in as "student1"
    And I am on "Course divided into groups" course homepage
    And I follow "Get only my group submission"
    And I follow "Responses"

    Then I should see "Never" in the "student1 user1" "table_row"
    Then I should see "Never" in the "student2 user2" "table_row"
    Then I should not see "student3" in the "submissions" "table"
    Then I should see "3" submissions
