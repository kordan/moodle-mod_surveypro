@mod @mod_surveypro @surveyprofield
Feature: In read only mode browse a submission jumping not filled pages
  Test during read only browse of responses user jumps pages without answers
  As a teacher
  I create a surveypro and as a student I fill, submit and browse, in read only mode, my submission.

  @javascript
  Scenario: Jump unused survepro pages in read only mode
    Given the following "courses" exist:
      | fullname               | shortname              | category | groupmode |
      | Jump not allowed pages | Jump not allowed pages | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                | role           |
      | teacher1 | Jump not allowed pages | editingteacher |
      | student1 | Jump not allowed pages | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                       | newpageforchild | course                 |
      | surveypro | Test jump not allowed pages | Test jump not allowed pages | 1               | Jump not allowed pages |
    And surveypro "Test jump not allowed pages" contains the following items:
      | type   | plugin      |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | select      |
      | format | pagebreak   |
      | field  | character   |
      | format | pagebreak   |
      | field  | boolean     |
    And I am on the "Test jump not allowed pages" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I follow "edit_item_3"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Question of page 2       |
      | Required       | 1                        |
      | Parent element | Boolean [1]: Is it true? |
      | Parent content | 1                        |
    And I set the multiline field "Options" to "Up\nDown"
    And I press "Save changes"

    And I follow "edit_item_5"
    And I set the following fields to these values:
      | Content        | Question of page 3       |
      | Parent element | Boolean [1]: Is it true? |
      | Parent content | 0                        |
    And I press "Save changes"

    And I follow "edit_item_7"
    And I set the following fields to these values:
      | Content        | Question of page 4             |
      | Parent element | Select [3]: Question of page 2 |
      | Parent content | Up                             |
    And I press "Save changes"

    And I log out

    # Let the student start to fill the surveypro
    When I am on the "Test jump not allowed pages" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Is it true?" to "1"
    And I press "Next page >>"
    Then I should see "Question of page 2"

    And I set the field "Question of page 2" to "Up"
    And I press "Next page >>"
    Then I should see "Question of page 4"

    And I set the field "Question of page 4" to "0"
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    And I follow "view_submission_row_1"
    Then I should see "Is it true?"

    And I press "Next page >>"
    Then I should see "Question of page 2"

    And I press "Next page >>"
    Then I should see "Question of page 4"
