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
  Scenario: Restore a surveypro backup
    When I am on the "Demo course" "restore" page
    And I press "Manage course backup"
    And I upload "mod/surveypro/tests/fixtures/demo_course-20240404.mbz" file to "Files" filemanager
    And I press "Save changes"

    And I restore "demo_course-20240404.mbz" backup into a new course using this options:
      | Schema | Course name | Restored course |
    And I press "Continue"

    When I am on the "Age" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "21" visible items

    When I am on the "attachment" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "3" visible items

    When I am on the "autofill" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "5" visible items

    When I am on the "boolean" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "31" visible items

    When I am on the "checkbox" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "41" visible items

    When I am on the "date" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "19" visible items

    When I am on the "date_(short)" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "19" visible items

    When I am on the "date_and_time" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "19" visible items

    When I am on the "integer" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "9" visible items

    When I am on the "multiselect" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "17" visible items

    When I am on the "numeric" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "9" visible items

    When I am on the "radio_button" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "27" visible items

    When I am on the "rate" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "21" visible items

    When I am on the "recurrence" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "19" visible items

    When I am on the "select" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "27" visible items

    When I am on the "text_area" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "9" visible items

    When I am on the "text_(short)" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "17" visible items

    When I am on the "time" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "19" visible items

    When I am on the "parent-child" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "20" visible items

    When I am on the "MMM" "mod_surveypro > Layout from secondary navigation" page
    Then I should see "44" visible items
