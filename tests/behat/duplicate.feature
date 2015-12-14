@mod @mod_surveypro
Feature: Backup and restore of surveyspro
  In order to use the file mod/quiz/tests/behat/backup.feature as a guide
  As a teacher
  I need to be able to back them up and restore them.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 3           |
    And I log in as "admin"

  @javascript
  Scenario: Duplicate a surveypro with some item
    Given the following "activities" exist:
      | activity   | name           | intro              | course | idnumber   |
      | surveypro  | surveypro test | For testing backup | C1     | surveypro1 |
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

    And I am on site homepage
    When I follow "Course 1"
    And I turn editing mode on
    And I duplicate "surveypro test" activity editing the new copy with:
      | Name | Copy of surveypro test |
    And I follow "Copy of surveypro test"
    And I follow "Layout"
    Then I should see "Welcome to this new instance of surveypro"
    And I should see "Grouped data"
    And I should see "What do you usually eat for breakfast?"
    And I should see "Write the best approximation of π you can remember"
    And I should see "Is this true?"
    And I should see "Where do you usually spend your summer holidays?"
