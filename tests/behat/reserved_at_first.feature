@mod @mod_surveypro @surveyprofield
Feature: Access a surveypro from the second page if the first one has only reserved items
  Test accessing the surveypro starting from page 2 if the first one has only reserved items
  As a teacher
  I create a surveypro and
  As a student I get it starting from the second page.

  @javascript
  Scenario: Get the surveypro starting from page 2
    Given the following "courses" exist:
      | fullname   | shortname  | category | groupmode |
      | Start at 2 | Start at 2 | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course     | role           |
      | teacher1 | Start at 2 | editingteacher |
      | student1 | Start at 2 | student        |
    And the following "activities" exist:
      | activity  | name            | intro           | newpageforchild | course     |
      | surveypro | Test start at 2 | Test start at 2 | 1               | Start at 2 |
    And surveypro "Test start at 2" contains the following items:
      | type   | plugin      |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | character   |
    And I am on the "Test start at 2" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I click on "//a[contains(@class,'quickeditlink')]//img[contains(@id, 'makereserved_item_1')]" "xpath_element"

    And I log out

    # Let the student start to fill the surveypro
    When I am on the "Test start at 2" "surveypro activity" page logged in as student1

    And I press "New response"
    Then I should see "Write down your email"

    And I set the field "Write down your email" to "su@nowhere.net"
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    And I follow "view_submission_row_1"
    Then I should see "Write down your email"
