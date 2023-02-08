@mod @mod_surveypro
Feature: verify instance deletion
  In order to verify the deletion of a surveypro instance
  As a teacher
  I delete a surveypro activity.

  @javascript
  Scenario: delete a surveypro activity
    Given the following "courses" exist:
      | fullname               | shortname         | category | groupmode |
      | Test activity deletion | Activity deletion | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher1 | Activity deletion | editingteacher |
    And the following "activities" exist:
      | activity  | name                 | intro    | course            |
      | surveypro | Activity delenda est | To trash | Activity deletion |
    And surveypro "Activity delenda est" contains the following items:
      | type   | plugin      |
      | format | label       |
      | format | fieldset    |
      | field  | age         |
      | field  | autofill    |
      | field  | boolean     |
      | field  | character   |
      | field  | checkbox    |
      | field  | date        |
      | field  | datetime    |
      | field  | fileupload  |
      | field  | integer     |
      | field  | multiselect |
      | field  | numeric     |
      | format | fieldsetend |
      | field  | radiobutton |
      | format | pagebreak   |
      | field  | rate        |
      | field  | recurrence  |
      | field  | select      |
      | field  | shortdate   |
      | field  | textarea    |
      | field  | time        |

    And I log in as "teacher1"
    And I am on "Test activity deletion" course homepage
    Then I should see "Activity delenda est" in the "#region-main" "css_element"
    When I turn editing mode on
    And I delete "Activity delenda est" activity
    Then I should not see "Activity delenda est" in the "#region-main" "css_element"
