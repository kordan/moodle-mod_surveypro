@mod @mod_surveypro @surveyprofield
Feature: Submission modification time
  Check that even filling a multipage surveypro moving forward and backword, timemodified is recorded ONLY at modification time
  As a teacher
  I create a multipage surveypro and as a student I fill it going forward and backword, and I submit.

  @javascript
  Scenario: check submission modification time
    Given the following "courses" exist:
      | fullname                               | shortname                              | category | groupmode |
      | Timemodified only at modification time | Timemodified only at modification time | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                | role           |
      | teacher1 | Timemodified only at modification time | editingteacher |
      | student1 | Timemodified only at modification time | student        |
    And the following "activities" exist:
      | activity  | name                   | intro                  | newpageforchild | course                                 |
      | surveypro | Test modification time | Test modification time | 1               | Timemodified only at modification time |
    And surveypro "Test modification time" contains the following items:
      | type   | plugin      |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | character   |

    # Let the student start to fill the surveypro
    When I am on the "Test modification time" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Is it true?" to "1"
    And I press "Next page >>"

    And I set the field "Write down your email" to "su@nowhere.net"
    And I press "<< Previous page"

    And I set the field "Is it true?" to "0"
    And I press "Next page >>"

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
    Then I should see "Never"
