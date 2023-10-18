@mod @mod_surveypro
Feature: Simple restore of a surveypro
  In order to test simple restore procedure
  As a teacher
  I make a simple restore of a course with two instances of surveypro

  Background:
    Given the following "courses" exist:
      | fullname    | shortname | category |
      | Demo course | C1        | 0        |
    And I log in as "admin"

  @javascript @_file_upload
  Scenario: Restore a course having surveypro
    When I am on the "Demo course" "restore" page
    And I press "Manage backup files"
    And I upload "mod/surveypro/tests/fixtures/demo_course-20160108.mbz" file to "Files" filemanager
    And I press "Save changes"
    And I restore "demo_course-20160108.mbz" backup into a new course using this options:
    Then I should see "\"age\" element"
    Then I should see "\"attachment\" element"
    Then I should see "\"autofill\" element"
    Then I should see "\"boolean\" element"
    Then I should see "\"checkbox\" element"
    Then I should see "\"date\" element"
    Then I should see "\"date (short)\" element"
    Then I should see "\"date and time\" element"
    Then I should see "\"integer\" element"
    Then I should see "\"multiselect\" element"
    Then I should see "\"numeric\" element"
    Then I should see "\"radio button\" element"
    Then I should see "\"rate\" element"
    Then I should see "\"recurrence\" element"
    Then I should see "\"select\" element"
    Then I should see "\"text area\" element"
    Then I should see "\"text (short)\" element"
    Then I should see "\"time\" element"
    Then I should see "Examples of parent-child relations"
    Then I should see "General example of use of this module"
