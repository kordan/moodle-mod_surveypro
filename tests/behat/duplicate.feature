@mod @mod_surveypro
Feature: Duplicate a surveypro
  In order to validate backup and restore process
  As a teacher
  I duplicate a surveypro instance.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 3           |
    And I log in as "admin"

  @javascript
  Scenario: Duplicate a non empty surveypro
    Given the following "activities" exist:
      | activity  | name           | intro              | course |
      | surveypro | surveypro test | For testing backup | C1     |
    And surveypro "surveypro test" contains the following items:
      | type   | plugin      |
      | format | label       |
      | format | fieldset    |
      | field  | checkbox    |
      | format | fieldsetend |
      | field  | numeric     |
      | format | pagebreak   |
      | field  | boolean     |
      | field  | select      |

    And I am on "Course 1" course homepage with editing mode on
    And I duplicate "surveypro test" activity editing the new copy with:
      | Name | Copy of surveypro test |
    And I am on the "Copy of surveypro test" "surveypro activity" page
    And I follow "Layout"
    Then I should see "Welcome to this new instance of surveypro"
    And I should see "Grouped data"
    And I should see "What do you usually get for breakfast?"
    And I should see "Type the best approximation of π you know"
    And I should see "Is it true?"
    And I should see "Where do you usually spend your summer holidays?"
