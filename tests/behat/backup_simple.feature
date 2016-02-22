@mod @mod_surveypro
Feature: Backup and restore of surveyspro
  In order to test simple backup procedure
  As a teacher
  I make a simple backup of a course with two instances of surveypro

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | numsections |
      | Course 1 | C1        | 0        | 3           |
    And the following "activities" exist:
      | activity  | course | idnumber   | name                 | intro                     | section |
      | surveypro | C1     | surveypro1 | Test surveypro       | Surveypro description     | 1       |
      | surveypro | C1     | surveypro2 | Test surveypro again | New surveypro description | 2       |
    And I log in as "admin"

  @javascript
  Scenario: Backup a course providing options
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    Then I should see "Restore"
    And I click on "Restore" "link" in the "test_backup.mbz" "table_row"
    And I should see "URL of backup"
    And I should see "Anonymize user information"
