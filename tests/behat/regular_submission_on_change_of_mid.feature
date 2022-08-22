@mod @mod_surveypro @surveyprofield
Feature: delete no longer allowed answers on user change of mind
  In order to test the deletion of no longer allowed answers in a parent-child relation over two pages when user changes his answer
  As a teacher
  I create a parent-child relation and as a student I fill, return back, change my answer and continue.

  @javascript
  Scenario: test change my mind
    Given the following "courses" exist:
      | fullname       | shortname      | category | groupmode |
      | Change my mind | Change my mind | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course            | role           |
      | teacher1 | Change my mind | editingteacher |
      | student1 | Change my mind | student        |
    And the following "activities" exist:
      | activity  | name                | intro               | newpageforchild | course         |
      | surveypro | Test change my mind | Test change my mind | 1               | Change my mind |
    And surveypro "Test change my mind" contains the following items:
      | type   | plugin      |
      | field  | character   |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | select      |
      | field  | numeric     |
    And I log in as "teacher1"
    And I am on "Change my mind" course homepage
    And I follow "Test change my mind"
    And I follow "Layout"

    And I follow "edit_item_1"
    And I expand all fieldsets
    And I set the field "Content" to "Useless question"
    And I set the field "id_pattern" to "free pattern"
    And I press "Save changes"

    And I follow "edit_item_2"
    And I set the field "Content" to "Switching boolean"
    And I press "Save changes"

    And I follow "edit_item_4"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Choose a direction             |
      | Parent element | Boolean [2]: Switching boolean |
      | Parent content | 0                              |
    And I set the multiline field "Options" to "North\nEast\nSouth\nWest"
    And I press "Save changes"

    And I follow "edit_item_5"
    And I set the field "Content" to "Question without parent"
    And I press "Save changes"

    And I log out

    # Let the student start to fill the surveypro
    When I log in as "student1"
    And I am on "Change my mind" course homepage
    And I follow "Test change my mind"

    And I press "New response"
    And I set the field "Useless question" to "Useless answers"
    And I set the field "Switching boolean" to "0"
    And I press "Next page >>"
    Then I should see "Choose a direction"
    Then I should see "Question without parent"

    And I set the field "Choose a direction" to "South"
    And I set the field "Question without parent" to "This should remain"
    And I press "<< Previous page"

    And I set the field "Switching boolean" to "1"
    And I press "Next page >>"
    Then I should not see "Choose a direction"
    Then I should see "Question without parent"

    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    Then I should not see "Some answers of this response have been found as unverified."
