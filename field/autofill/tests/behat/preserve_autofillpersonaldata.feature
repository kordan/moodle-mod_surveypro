@mod @mod_surveypro
Feature: Editing a submission, autofill userID is not overwritten
  In order to test that personal data is not overwritten editing a submission
  As student1 and student2
  I fill a surveypro and edit it as different user

  @javascript
  Scenario: Test autofill userID is not overwritten at submission editing time
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
      | mod/surveypro:seeotherssubmissions  | Allow      | student | Course       | Course grouped |
      | mod/surveypro:editownsubmissions    | Allow      | student | Course       | Course grouped |
      | mod/surveypro:editotherssubmissions | Allow      | student | Course       | Course grouped |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
    And the following "activities" exist:
      | activity  | name              | intro                                                              | course         |
      | surveypro | Preserve autofill | Test that editing a submission, autofill userID is not overwritten | Course grouped |
    And surveypro "Preserve autofill" has the following items:
      | type  | plugin   | settings                                                                                       |
      | field | autofill | {"content":"Your user ID",    "position":"0", "customnumber":"1", "element01":"userid"}        |
      | field | autofill | {"content":"Your first name", "position":"0", "customnumber":"2", "element01":"userfirstname"} |
      | field | autofill | {"content":"Your last name",  "position":"0", "customnumber":"3", "element01":"userlastname"}  |
      | field | boolean  | {"content":"Is it true?",     "position":"0", "customnumber":"4", "required":"1", "style":"0"} |
    And I am on the "Preserve autofill" "Activity editing" page logged in as teacher1

    And I set the following fields to these values:
      | Group mode | Visible groups |
    And I press "Save and display"

    # student1 logs in
    And I am on the "Preserve autofill" "surveypro activity" page logged in as student1

    And I press "New response"

    # student1 submits his first response
    And I set the field "4 Is it true?" to "Yes"
    And I press "Submit"

    And I press "New response"

    # student1 submits his second response
    And I set the field "4 Is it true?" to "No"
    And I press "Submit"

    And I log out

    # student2 logs in
    And I am on the "Preserve autofill" "surveypro activity" page logged in as student2
    And I select "Responses" from the "jump" singleselect
    And I click action "Edit" on item 1
    Then I should see "student1"
    Then I should see "user1"
    Then the field "4 Is it true?" matches value "Yes"

    And I set the field "4 Is it true?" to "No"
    And I press "Submit"

    And I log out

    # student1 logs in
    And I am on the "Preserve autofill" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    And I click action "Edit" on item 1
    Then I should see "student1"
    Then I should see "user1"
    Then the field "4 Is it true?" matches value "No"

    And I log out

    # teacher1 logs in
    And I am on the "Preserve autofill" "surveypro activity" page logged in as teacher1
    And I select "Responses" from the "jump" singleselect

    And I click action "Edit" on item 1
    Then I should see "student1"
    Then I should see "user1"
    Then the field "4 Is it true?" matches value "No"
    And I set the field "4 Is it true?" to "Yes"
    And I press "Submit"

    And I log out

    # student1 logs in
    And I am on the "Preserve autofill" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    And I click action "Edit" on item 1
    Then I should see "student1"
    Then I should see "user1"
    Then the field "4 Is it true?" matches value "Yes"

    And I log out
