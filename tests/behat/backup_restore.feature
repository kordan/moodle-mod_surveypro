@mod @mod_surveypro
Feature: Backup and restore a surveypro
  In order to validate backup and restore process
  As a teacher
  I backup and restore a surveypro instance.

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 3           |
    And I log in as "admin"

  @javascript
  Scenario: Backup and restore a surveypro with items
    Given the following "activities" exist:
      | activity  | name           | intro              | course |
      | surveypro | Surveypro test | For testing backup | C1     |
    And surveypro "Surveypro test" contains the following items:
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

    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    Then I should see "Restore"

    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Copy of course 1 |
      | Schema | Course short name | C2               |
    Then I should see "Copy of course 1"

    # You can not use
    # When I am on the "Surveypro test" "surveypro activity" page
    # because there is more than a copy of "Surveypro test" "surveypro activity" page
    And I follow "Surveypro test"
    And I follow "Layout"

    Then I should see "Welcome to this new instance of surveypro"
    And I should see "Grouped data inside"
    And I should see "How old were you when you started cycling?"
    And I should see "Just your userid"
    And I should see "Is it true?"
    And I should see "Write down your email"
    And I should see "What do you usually get for breakfast?"
    And I should see "When were you born?"
    And I should see "Please, write down date and time of your last flight to Los Angeles."
    And I should see "Upload your CV in PDF format"
    And I should see "How many people does your family counts?"
    And I should see "multiselect_001"
    And I should see "Write your best approximation of π"
    And I should see "Where do you usually spend your summer holidays?"
    And I should see "How confident are you with the following languages?"
    And I should see "When do you usually celebrate your name-day?"
    And I should see "select_001"
    And I should see "When did you buy your current car?"
    And I should see "Write a short description of yourself"
    And I should see "At what time do you usually get up in the morning in the working days?"
