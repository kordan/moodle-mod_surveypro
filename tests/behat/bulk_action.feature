@mod @mod_surveypro
Feature: Execute bulk actions
  In order to validate each action issues through inline icons
  As Admin
  I issue them and verify the outcome.

  Background:
    Given the following "courses" exist:
      | fullname          | shortname | category | numsections |
      | Bulk item actions | Test IA   | 0        | 3           |
    And I log in as "admin"

  @javascript
  Scenario: Test bulk actions
    Given the following "activities" exist:
      | activity  | name                   | intro                     | course  |
      | surveypro | Test bulk item actions | To test bulk item actions | Test IA |
    And surveypro "Test bulk item actions" has the following items:
      | type   | plugin      |
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
      | field  | radiobutton |
      | field  | rate        |
      | field  | recurrence  |
      | field  | select      |
      | field  | shortdate   |
      | field  | textarea    |
      | field  | time        |
      | format | pagebreak   |
      | format | label       |
      | format | fieldset    |
      | format | fieldsetend |
    And I am on the "Test bulk item actions" "mod_surveypro > Layout from secondary navigation" page

    Then I should see "22" visible items
    Then I should see "0" hidden items

    And I set the field "bulkaction" to "Hide all elements"
    And I press "Go"
    And I press "Hide each element"

    Then I should see "0" visible items
    Then I should see "22" hidden items

    And I set the field "bulkaction" to "Show all elements"
    And I press "Go"
    And I press "Show each element"

    Then I should see "22" visible items
    Then I should see "0" hidden items

    And And I click action "Hide" on item 2
    And And I click action "Hide" on item 4
    And And I click action "Hide" on item 6
    And And I click action "Hide" on item 8
    And And I click action "Hide" on item 10
    And And I click action "Hide" on item 12
    And And I click action "Hide" on item 14
    And And I click action "Hide" on item 16
    And And I click action "Hide" on item 18
    And And I click action "Hide" on item 20
    And And I click action "Hide" on item 22

    Then I should see "11" visible items
    Then I should see "11" hidden items

    And I set the field "bulkaction" to "Delete hidden elements"
    And I press "Go"
    And I press "Delete each hidden element"

    Then I should see "11" visible items
    Then I should see "0" hidden items

    And And I click action "Hide" on item 2
    And And I click action "Hide" on item 4
    And And I click action "Hide" on item 6
    And And I click action "Hide" on item 8
    And And I click action "Hide" on item 10

    And I set the field "bulkaction" to "Delete visible elements"
    And I press "Go"
    And I press "Delete each visible element"

    Then I should see "0" visible items
    Then I should see "5" hidden items

    And I set the field "bulkaction" to "Delete all elements"
    And I press "Go"
    And I press "Delete each element"
