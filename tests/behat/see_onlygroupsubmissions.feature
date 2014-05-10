@mod @mod_surveypro @current
Feature: verify each student sees only personal submissions
  In order to verify that students can only see their personal submissions
  As student1 and student2
  I fill a surveypro go to see responses

  @javascript
  Scenario: verify each student can only see submissions from people of his/her group
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course divided in groups | C1 | 0 | 0 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group 1 | C1 | G1 |
      | Group 2 | C1 | G2 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | teacher | teacher1@asd.com |
      | student1 | student1 | user1 | student1@asd.com |
      | student2 | student2 | user2 | student2@asd.com |
      | student3 | student3 | user3 | student3@asd.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | student3 | C1 | student |
    And the following "permission overrides" exist:
      | capability | permission | role | contextlevel | reference |
      | mod/surveypro:seeotherssubmissions | Allow | student | Course | C1 |
    And the following "group members" exist:
      | user | group |
      | student1 | G1 |
      | student2 | G1 |
      | student3 | G2 |

    And I log in as "teacher1"
    And I follow "Course divided in groups"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Simple test |
      | Description | This is a surveypro to test each student can only see submissions from people of his/her group |
      | Group mode | Separate groups |
    And I follow "Simple test"

    And I set the field "plugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | Write down your email |
      | Required | 1 |
      | Indent | 0 |
      | Question position | left |
      | Element number | 1 |
      | Hide filling instruction | 0 |
      | id_pattern | email address |
    And I press "Add"

    And I set the field "plugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | Is this true? |
      | Required | 1 |
      | Indent | 0 |
      | Question position | left |
      | Element number | 2 |
    And I press "Add"

    And I log out

    # student1 get in
    When I log in as "student1"
    And I follow "Course divided in groups"
    And I follow "Simple test"
    And I press "Add a response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st11email@st11server.net |
      | 2: Is this true? | Yes |
    And I press "Submit"

    And I press "Let me add one more response, please"
    And I press "Add a response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1: Write down your email | st12email@st12server.net |
      | 2: Is this true? | No |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "2" rows in the submissions table

    And I log out

    # student2 get in
    When I log in as "student2"
    And I follow "Course divided in groups"
    And I follow "Simple test"
    And I press "Add a response"

    # student2 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st21email@st21server.net |
      | 2: Is this true? | Yes |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should see "Never" in the "student1 user1" "table_row"
    And I should see "Never" in the "student2 user2" "table_row"
    Then I should see "3" rows in the submissions table

    And I log out

    # student3 get in
    When I log in as "student3"
    And I follow "Course divided in groups"
    And I follow "Simple test"
    And I press "Add a response"

    # student3 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st31email@st31server.net |
      | 2: Is this true? | Yes |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should not see "student1" in the "submissions" "table"
    And I should not see "student2" in the "submissions" "table"
    And I should see "Never" in the "student3 user3" "table_row"
    Then I should see "1" rows in the submissions table

    And I log out

    # student1 goes to check for his personal submissions
    When I log in as "student1"
    And I follow "Course divided in groups"
    And I follow "Simple test"

    And I follow "Responses"
    Then I should see "Never" in the "student1 user1" "table_row"
    Then I should see "Never" in the "student2 user2" "table_row"
    Then I should not see "student3" in the "submissions" "table"
    Then I should see "3" rows in the submissions table


