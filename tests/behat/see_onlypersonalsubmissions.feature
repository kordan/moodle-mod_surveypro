@mod @mod_surveypro
Feature: test each student sees only personal submissions
  In order to test that students can only see their personal submissions
  As student1 and student2
  I fill a surveypro and go to see responses

  @javascript
  Scenario: test each student sees only his/her own submissions
    Given the following "courses" exist:
      | fullname                      | shortname                 | category | groupmode |
      | See only personal submissions | Only personal submissions | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
      | student2 | Student2  | user2    | student2@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                    | role           |
      | teacher1 | Only personal submissions | editingteacher |
      | student1 | Only personal submissions | student        |
      | student2 | Only personal submissions | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                                                  | course                    |
      | surveypro | Get only my own submissions | Test each student can only see his/her own submissions | Only personal submissions |
    And I am on the "Get only my own submissions" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

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
    When I am on the "Get only my own submissions" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1 Write down your email | st11email@st11server.net |
      | 2 Is it true?           | Yes                      |
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1 Write down your email | st12email@st12server.net |
      | 2 Is it true?           | No                       |
    And I press "Submit"

    And I log out

    # student2 logs in
    When I am on the "Get only my own submissions" "surveypro activity" page logged in as student2
    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student2 submits a response
    And I set the following fields to these values:
      | 1 Write down your email | st21email@st21server.net |
      | 2 Is it true?           | Yes                      |
    And I press "Submit"

    And I press "Continue to responses list"
    And I should see "Never" in the "Student2 user2" "table_row"
    And I should not see "Student1" in the "submissions" "table"
    Then I should see "1" submissions

    And I log out

    # student1 goes to check for his personal submissions
    When I am on the "Get only my own submissions" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    Then I should see "Never" in the "Student1 user1" "table_row"
    Then I should not see "Student2" in the "submissions" "table"
    Then I should see "2" submissions
