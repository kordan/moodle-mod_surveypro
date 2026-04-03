@mod @mod_surveypro
Feature: Submissions seen from students divided into groups (Part 02)
  In order to test which submissions students in groups can see
  As student1 and student2 and student3
  I fill a surveypro and ask for the submissions list

  @javascript
  Scenario: Verify permissions in groups part 02
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
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Groups permissions | editingteacher |
      | student1 | Groups permissions | student        |
      | student2 | Groups permissions | student        |
      | student3 | Groups permissions | student        |
    And the following "group members" exist:
      | user     | group   |
      | student1 | group01 |
      | student2 | group01 |
      | student3 | group02 |
    And the following "activities" exist:
      | activity  | name                        | intro                          | course             | groupmode |
      | surveypro | Verify submission selection | Test what each student can see | Groups permissions | 1         |
    And surveypro "Verify submission selection" has the following items:
      | type  | plugin    | options                                                           |
      | field | character | {"content":"Enter your name", "required":"1", "customnumber":"1"} |
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student1

    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | st1grp1ans1@nowhere.net |
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the following fields to these values:
      | 1 Enter your name | st1grp1ans2@nowhere.net |
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
      | 1 Enter your name | st2grp1ans1@nowhere.net |
    And I press "Submit"

    Then I press "Continue to responses list"
    Then I should not see "student1" in the "submissions" "table"
    Then I should see "Never" in the "student2 user2" "table_row"
    Then I should see "1" submissions

    And I log out

    # student3 logs in
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student3
    And I select "Responses" from the "jump" singleselect

    Then I should see "Nothing to display"

    And I press "New response"

    # student3 submits his first response
    And I set the following fields to these values:
      | 1 Enter your name | st3grp2ans1@nowhere.net |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should not see "student1" in the "submissions" "table"
    Then I should not see "student2" in the "submissions" "table"
    Then I should see "Never" in the "student3 user3" "table_row"
    Then I should see "1" submissions

    And I log out

    # student1 goes to check for his personal submissions
    When I am on the "Verify submission selection" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    Then I should see "Never" in the "student1 user1" "table_row"
    Then I should not see "student2" in the "submissions" "table"
    Then I should not see "student3" in the "submissions" "table"
    Then I should see "2" submissions
