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
      | type  | plugin  |
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
  Scenario: select each available link as a teacher
    Given I log in as "teacher1"
    And I am on "Test links course" course homepage
    And I follow "sPro test links"
    #
    # Layout TAB
    #
    # Layout -> Preview
    And I navigate to "Layout > Preview" in current page administration

    # Layout -> Elements
    And I follow "Elements" page in tab bar
    # Layout -> Elements: table headers
    And I click on "Element" "link" in the ".plugin" "css_element"
    And I click on "Order" "link" in the ".sortindex" "css_element"
    And I click on "Branching" "link" in the ".parentitem" "css_element"
    And I click on "Page" "link" in the ".formpage" "css_element"

    # Layout -> Elements
    And I navigate to "Layout > Elements" in current page administration

    #
    # Survey TAB
    #
    And I follow "Survey"
    # Survey -> Dashboard
    # This step used to be 'And I follow "Dashboard"', but "Dashboard" is found 4 times in the page
    # so I use a custom "home made" behat call.
    And I follow "Dashboard" page in tab bar
    # Survey -> Dashboard: Reports section
    And I follow "Run Attachments overview report"
    # return home
    And I follow "Dashboard" page in tab bar

    And I follow "Run Delayed users report"
    # return home
    And I follow "Dashboard" page in tab bar

    And I follow "Run Frequency distribution report"
    # return home
    And I follow "Dashboard" page in tab bar

    And I follow "Run Responses per user report"
    # return home
    And I follow "Dashboard" page in tab bar

    And I follow "Run Users per count of responses report"
    # return home
    And I follow "Dashboard" page in tab bar

    # Survey -> Dashboard: User templates section
    And I follow "Manage user templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    And I follow "Save user templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    And I follow "Import user templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    And I follow "Apply user templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    # Survey -> Dashboard: Master templates section
    And I follow "Save master templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    And I follow "Apply master templates"
    # return home
    And I follow "Survey"
    And I follow "Dashboard" page in tab bar

    # Survey -> Responses
    And I follow "Responses" page in tab bar

    # Survey -> Import
    And I navigate to "Survey > Import" in current page administration

    # Survey -> Export
    And I navigate to "Survey > Export" in current page administration

    #
    # User templates TAB
    #
    # User templates -> Manage
    And I navigate to "User templates > Manage" in current page administration
    # User templates: pages
    And I follow "Save" page in tab bar
    And I follow "Import" page in tab bar
    And I follow "Apply" page in tab bar
    # User templates -> Save
    And I navigate to "User templates > Save" in current page administration
    # User templates -> Import
    And I navigate to "User templates > Import" in current page administration
    # User templates -> Apply
    And I navigate to "User templates > Apply" in current page administration

    #
    # Master templates TAB
    #
    # Master templates -> Save
    And I navigate to "Master templates > Save" in current page administration
    # Master templates: pages
    And I follow "Apply" page in tab bar
    And I navigate to "Master templates > Apply" in current page administration

    #
    # Report TAB
    #
    # Report -> Attachments overview
    And I navigate to "Report > Attachments overview" in current page administration

    # Report -> Delayed users
    And I follow "sPro test links"
    And I navigate to "Report > Delayed users" in current page administration

    # Report -> Frequency distribution
    And I follow "sPro test links"
    And I navigate to "Report > Frequency distribution" in current page administration

    # Report -> Responses per user
    And I follow "sPro test links"
    And I navigate to "Report > Responses per user" in current page administration

    # Report -> Users per count of responses
    And I follow "sPro test links"
    And I navigate to "Report > Users per count of responses" in current page administration

  @javascript
  Scenario: select each available link as a student
    Given I log in as "student1"
    And I am on "Test links course" course homepage
    And I follow "sPro test links"
    And I follow "Dashboard" page in tab bar
    And I follow "Responses"
