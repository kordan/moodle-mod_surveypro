@mod @mod_surveypro
Feature: verify urls really redirect to existing pages
  In order to verify urls really redirect to existing pages // Why this feature is useful
  As a teacher and as a student                             // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I select each link                                        // The feature we want

  Background:
    Given the following "courses" exist:
      | fullname          | shortname | category | groupmode |
      | Test links course | Tl course | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher1 | Tl course | editingteacher |
      | student1 | Tl course | student        |
    And the following "activities" exist:
      | activity  | name            | intro           | course    | idnumber   |
      | surveypro | sPro test links | To verify links | Tl course | surveypro1 |
    And surveypro "sPro test links" contains the following items:
      | type   | plugin  |
      | field  | boolean |

  @javascript
  Scenario: select each available link as a teacher
    Given I log in as "teacher1"
    And I follow "Test links course"
    And I follow "sPro test links"
    #
    # Layout TAB
    #
    # Layout -> Preview
    And I navigate to "Preview" node in "Surveypro administration > Layout"
    # Layout -> Elements
    And I follow "Elements"
        # Layout -> Elements: table headers
        And I follow "Element"
        And I follow "Order"
        And I follow "Branching"
        And I follow "Page"
    # Layout -> Elements
    And I navigate to "Elements" node in "Surveypro administration > Layout"

    #
    # Survey TAB
    #
    And I follow "Survey"
    # Survey -> Dashboard
    And I follow "survey_dashboard"
        # Survey -> Dashboard: Reports section
        And I follow "Run Attachments overview report"
        # return home
        And I follow "survey_dashboard"

        And I follow "Run Delayed users report"
        # return home
        And I follow "survey_dashboard"

        And I follow "Run Frequency distribution report"
        # return home
        And I follow "survey_dashboard"

        And I follow "Run Responses per user report"
        # return home
        And I follow "survey_dashboard"

        And I follow "Run Users per count of responses report"
        # return home
        And I follow "survey_dashboard"

        # Survey -> Dashboard: User templates section
        And I follow "Manage user templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"

        And I follow "Save user templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"

        And I follow "Import user templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"

        And I follow "Apply user templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"

        # Survey -> Dashboard: Master templates section
        And I follow "Save master templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"

        And I follow "Apply master templates"
        # return home
        And I follow "Survey"
        And I follow "survey_dashboard"
    # Survey -> Responses
    And I follow "Responses"
    # Survey -> Import
    And I navigate to "Import" node in "Surveypro administration > Survey"
    # Survey -> Export
    And I navigate to "Export" node in "Surveypro administration > Survey"

    #
    # User templates TAB
    #
    # User templates -> Manage
    And I navigate to "Manage" node in "Surveypro administration > User templates"
        # User templates: pages
        And I follow "Save"
        And I follow "Import"
        And I follow "Apply"
    # User templates -> Save
    And I navigate to "Save" node in "Surveypro administration > User templates"
    # User templates -> Import
    And I navigate to "Import" node in "Surveypro administration > User templates"
    # User templates -> Apply
    And I navigate to "Apply" node in "Surveypro administration > User templates"

    #
    # Master templates TAB
    #
    # Master templates -> Save
    And I navigate to "Save" node in "Surveypro administration > Master templates"
        # Master templates: pages
        And I follow "Apply"
    And I navigate to "Apply" node in "Surveypro administration > Master templates"

    #
    # Report TAB
    #
    # Report -> Attachments overview
    And I navigate to "Attachments overview" node in "Surveypro administration > Report"
    # Report -> Delayed users
    And I navigate to "Delayed users" node in "Surveypro administration > Report"
    # Report -> Frequency distribution
    And I navigate to "Frequency distribution" node in "Surveypro administration > Report"
    # Report -> Responses per user
    And I navigate to "Responses per user" node in "Surveypro administration > Report"
    # Report -> Users per count of responses
    And I navigate to "Users per count of responses" node in "Surveypro administration > Report"

  @javascript
  Scenario: select each available link as a student
    Given I log in as "student1"
    And I follow "Test links course"
    And I follow "sPro test links"
    And I follow "survey_dashboard"
    And I follow "Responses"
