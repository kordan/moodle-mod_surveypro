@mod @mod_surveypro
Feature: Test correctness of urls
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
      | activity  | name            | intro           | course    |
      | surveypro | sPro test links | To verify links | Tl course |
    And surveypro "sPro test links" contains the following items:
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

  @javascript
  Scenario: Select each available link as a teacher
    Given I am on the "sPro test links" "surveypro activity" page logged in as teacher1

    #
    # "Survey" in secondary navigation
    #
    And I follow "Surveypro"

    # Surveypro -> Dashboard: Reports section
    And I follow "Run Attachments overview report"
    And I select "Surveypro" from secondary navigation

    And I follow "Run Late users report"
    And I select "Surveypro" from secondary navigation

    And I follow "Run Frequency distribution report"
    And I select "Surveypro" from secondary navigation

    And I follow "Run Responses per user report"
    And I select "Surveypro" from secondary navigation

    And I follow "Run Users per count of responses report"
    And I select "Surveypro" from secondary navigation

    # Surveypro -> Dashboard: User templates section
    And I follow "Manage user templates"
    And I select "Surveypro" from secondary navigation

    And I follow "Save user templates"
    And I select "Surveypro" from secondary navigation

    And I follow "Import user templates"
    And I select "Surveypro" from secondary navigation

    And I follow "Apply user templates"
    And I select "Surveypro" from secondary navigation

    # Surveypro -> Dashboard: Master templates section
    And I follow "Save master templates"
    And I select "Surveypro" from secondary navigation

    And I follow "Apply master templates"
    And I select "Surveypro" from secondary navigation

    # Surveypro -> Responses
    And I select "Responses" from the "jump" singleselect

    #
    # "Layout" in secondary navigation
    #
    And I select "Layout" from secondary navigation

    # Layout -> Preview
    And I select "Preview" from the "jump" singleselect

    # Layout -> Elements
    And I select "Elements" from the "jump" singleselect

    # Layout -> Elements: table headers
    And I click on "Type" "link" in the ".plugin" "css_element"
    And I click on "Order" "link" in the ".sortindex" "css_element"
    And I click on "Branching" "link" in the ".parentitem" "css_element"
    And I click on "Page" "link" in the ".formpage" "css_element"

    #
    # "Reports" in secondary navigation
    #
    And I select "Reports" from secondary navigation

    # Report -> Users per count of responses
    And I select "Users per count of responses" from the "jump" singleselect

    # Report -> Responses per user
    And I select "Responses per user" from the "jump" singleselect

    # Report -> Late users
    And I select "Late user" from the "jump" singleselect

    # Report -> Frequency distribution
    And I select "Frequency distribution" from the "jump" singleselect

    # Report -> Attachments overview
    And I select "Attachments overview" from the "jump" singleselect

    #
    # "Tools" in secondary navigation
    #
    And I select "Tools" from secondary navigation

    # Tools -> Import overview
    And I select "Import" from the "jump" singleselect

    # Tools -> Export
    And I select "Export" from the "jump" singleselect

    #
    # "User templates" in secondary navigation
    #
    And I am on the "sPro test links" "mod_surveypro > User templates manage" page
    # User templates -> Save
    And I select "Save" from the "jump" singleselect

    # User templates -> Import
    And I select "Import" from the "jump" singleselect

    # User templates -> Apply
    And I select "Apply" from the "jump" singleselect

    # User templates -> Manage
    And I select "Manage" from the "jump" singleselect

    #
    # "Master templates" in secondary navigation
    #
    And I am on the "sPro test links" "mod_surveypro > Master templates apply" page

    # Master templates -> Save
    And I select "Save" from the "jump" singleselect

    # Master templates -> Apply
    And I select "Apply" from the "jump" singleselect

  @javascript
  Scenario: Select each available link as a student
    Given I am on the "sPro test links" "surveypro activity" page logged in as student1
    And I select "Dashboard" from the "jump" singleselect
    And I select "Responses" from the "jump" singleselect
