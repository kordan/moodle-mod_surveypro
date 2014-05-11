@mod @mod_surveypro
Feature: test each student sees only personal submissions
  In order to test that students can only see their personal submissions
  As student1 and student2
  I fill a surveypro and go to see responses

  @javascript
  Scenario: test each student sees only personal submissions
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | teacher  | teacher1@asd.com |
      | student1 | Student1  | user1    | student1@asd.com |
      | student2 | Student2  | user2    | student2@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |

    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Simple test                                                            |
      | Description | This is a surveypro to test each user can only get his own submissions |
    And I follow "Simple test"

    And I set the field "plugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write down your email |
      | Required                 | 1                     |
      | Indent                   | 0                     |
      | Question position        | left                  |
      | Element number           | 1                     |
      | Hide filling instruction | 0                     |
      | id_pattern               | email address         |
    And I press "Add"

    And I set the field "plugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Is this true? |
      | Required          | 1             |
      | Indent            | 0             |
      | Question position | left          |
      | Element number    | 2             |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "Simple test"
    And I press "Add a response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1: Write down your email | st11email@st11server.net |
      | 2: Is this true?         | Yes                      |
    And I press "Submit"

    And I press "Let me add one more response, please"
    And I press "Add a response"

    # student1 submits one more response
    And I set the following fields to these values:
      | 1: Write down your email | st12email@st12server.net |
      | 2: Is this true?         | No                       |
    And I press "Submit"

    And I log out

    # student2 logs in
    When I log in as "student2"
    And I follow "Course 1"
    And I follow "Simple test"
    And I press "Add a response"

    # student2 submits a response
    And I set the following fields to these values:
      | 1: Write down your email | st21email@st21server.net |
      | 2: Is this true?         | Yes                      |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should see "Never" in the "Student2 user2" "table_row"
    And I should not see "Student1" in the "submissions" "table"
    Then I should see "1" rows in the submissions table

    And I log out

    # student1 goes to check for his personal submissions
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "Simple test"

    And I follow "Responses"
    Then I should see "Never" in the "Student1 user1" "table_row"
    Then I should not see "Student2" in the "submissions" "table"
    Then I should see "2" rows in the submissions table
