@mod @mod_surveypro
Feature: Submissions seen from students divided into groups (Part 04)
  In order to test which submissions students in groups can see
  As student1 not in any group and student2, 3 and 4 into groups with mod/surveypro:seeotherssubmissions capability
  I fill a surveypro and ask for the submissions list

  @javascript
  Scenario: Verify permissions in groups part 04
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
      | student3 | student3  | user3    | student3@nowhere.net |
      | student4 | student4  | user4    | student4@nowhere.net |
    And the following "user preferences" exist:
      | user     | preference | value    |
      | teacher1 | htmleditor | textarea |
      | student1 | htmleditor | textarea |
      | student2 | htmleditor | textarea |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Groups permissions | editingteacher |
      | student1 | Groups permissions | student        |
      | student2 | Groups permissions | student        |
      | student3 | Groups permissions | student        |
      | student4 | Groups permissions | student        |
    And the following "permission overrides" exist:
      | capability                         | permission | role    | contextlevel | reference          |
      | mod/surveypro:seeotherssubmissions | Allow      | student | Course       | Groups permissions |
    And the following "group members" exist:
      | user     | group   |
      | student2 | group01 |
      | student3 | group01 |
      | student4 | group02 |

    And I log in as "teacher1"
    And I am on "Verify permissions in groups" course homepage with editing mode on
    And I add a surveypro activity to course "Verify permissions in groups" section "1" and I fill the form with:
      | Name        | Verify submission selection    |
      | Description | Test what each student can see |
      | Group mode  | Separate groups                |
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
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | student 1 nogroup answer 1 |
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1 Enter your name | student 1 nogroup answer 2 |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "2" submissions

    And I log out

    # student2 logs in
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student2
    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student2 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | student 2 group 1 answer 1 |
    And I press "Submit"

    Then I press "Continue to responses list"
    Then I should not see "student1" in the "submissions" "table"
    Then I should see "Never" in the "student2 user2" "table_row"
    Then I should see "1" submissions

    And I log out

    # student3 logs in
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student3
    And I select "Responses" from the "jump" singleselect

    Then I should not see "student1" in the "submissions" "table"
    Then I should see "student2" in the "submissions" "table"
    Then I should see "1" submissions

    And I press "New response"

    # student3 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | student 3 group 1 answer 1 |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should not see "student1" in the "submissions" "table"
    Then I should see "student2" in the "submissions" "table"
    Then I should see "Never" in the "student3 user3" "table_row"
    Then I should see "2" submissions

    And I log out

    # student4 logs in
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student4
    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student4 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | student 4 group 2 answer 1 |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "Never" in the "student4 user4" "table_row"
    Then I should see "1" submissions

    And I log out

    # student1 goes to check for his personal submissions
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    Then I should see "Never" in the "student1 user1" "table_row"
    Then I should see "student2" in the "submissions" "table"
    Then I should see "student3" in the "submissions" "table"
    Then I should see "student4" in the "submissions" "table"
    Then I should see "5" submissions
