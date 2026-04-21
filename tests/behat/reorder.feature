@mod @mod_surveypro @surveyprofield
Feature: Reorder items
  In order to test reorder feature
  As a teacher
  I create a few items long surveypro and I revert their order

  @javascript
  Scenario: Test item reorder
    Given the following "courses" exist:
      | fullname      | shortname     | category | groupmode |
      | Reorder items | Reorder items | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Reorder items | editingteacher |
    And the following "activities" exist:
      | activity  | name               | intro              | course        |
      | surveypro | Test items reorder | Test items reorder | Reorder items |
    And surveypro "Test items reorder" has the following items:
      | type   | plugin      |
      | field  | boolean     |
      | field  | character   |
      | field  | radiobutton |
      | field  | select      |
    And I am on the "Test items reorder" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # Item order is supposed to be: boolean, character, radiobutton, select
    Then the item in position 1 is "Boolean"
    Then the item in position 2 is "Text (short)"
    Then the item in position 3 is "Radio buttons"
    Then the item in position 4 is "Select"

    # move before the first item
    And I click action "Reorder" on item 4
    And I follow "moveafter_0"
    # Now item order is supposed to be: select, boolean, character, radiobutton
    Then the item in position 1 is "Select"
    Then the item in position 2 is "Boolean"
    Then the item in position 3 is "Text (short)"
    Then the item in position 4 is "Radio buttons"

    # move after the last item
    And I click action "Reorder" on item 3
    And I follow "moveafter_4"
    # Now item order is supposed to be: select, boolean, radiobutton, character
    Then the item in position 1 is "Select"
    Then the item in position 2 is "Boolean"
    Then the item in position 3 is "Radio buttons"
    Then the item in position 4 is "Text (short)"

    # move generic up
    And I click action "Reorder" on item 3
    And I follow "moveafter_1"
    # Now item order is supposed to be: select, radiobutton, character, boolean
    Then the item in position 1 is "Select"
    Then the item in position 2 is "Radio buttons"
    Then the item in position 3 is "Boolean"
    Then the item in position 4 is "Text (short)"

    # move generic down
    And I click action "Reorder" on item 1
    And I follow "moveafter_2"
    # Now item order is supposed to be: select, radiobutton, character, boolean
    Then the item in position 1 is "Radio buttons"
    Then the item in position 2 is "Select"
    Then the item in position 3 is "Boolean"
    Then the item in position 4 is "Text (short)"
