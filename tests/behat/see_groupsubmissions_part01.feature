@mod @mod_surveypro
Feature: submissions seen from students not divided into groups (Part 01)
  In order to test which submissions students can see
  As teacher, student1 and student2 not part of groups
  I fill a surveypro and ask for the submissions list

  @javascript
  Scenario: verify permissions in groups part 01
    Given the following "courses" exist:
      | fullname                     | shortname          | category | groupmode |
      | Verify permissions in groups | Groups permissions | 0        | 0         |
    And the following "groups" exist:
      | name    | course             | idnumber |
      | Group 1 | Groups permissions | group01  |
      | Group 2 | Groups permissions | group02  |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | student1  | user1    | student1@nowhere.net |
      | student2 | student2  | user2    | student2@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Groups permissions | editingteacher |
      | student1 | Groups permissions | student        |
      | student2 | Groups permissions | student        |

    And I log in as "teacher1"
    And I am on "Verify permissions in groups" course homepage
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Verify submission selection    |
      | Description | Test what each student can see |
    And I turn editing mode off
    And I am on the "Verify submission selection" "surveypro activity" page

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Enter your name |
      | Indent                   | 0               |
      | Question position        | left            |
      | Element number           | 1               |
      | Hide filling instruction | 0               |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on the "Verify submission selection" "surveypro activity" page
    And I follow "Responses" page in tab bar

    Then I should see "Nothing to display"

    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1: Enter your name | student 1 nogroup answer 1 |
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1: Enter your name | student 1 nogroup answer 2 |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "2" submissions

    And I log out

    # student2 logs in
    When I log in as "student2"
    And I am on the "Verify submission selection" "surveypro activity" page
    And I follow "Responses" page in tab bar

    Then I should see "Nothing to display"

    And I press "New response"

    # student2 submits his first response
    And I set the following fields to these values:
      | 1: Enter your name | student 2 nogroup answer 1 |
    And I press "Submit"

    Then I press "Continue to responses list"
    Then I should not see "student1" in the "submissions" "table"
    Then I should see "Never" in the "student2 user2" "table_row"
    Then I should see "1" submissions

    And I log out

    # teacher1 goes to check for his personal submissions
    When I log in as "teacher1"
    And I am on the "Verify submission selection" "surveypro activity" page
    And I follow "Responses" page in tab bar

    Then I should see "student1" in the "submissions" "table"
    Then I should see "student2" in the "submissions" "table"
    Then I should see "3" submissions
