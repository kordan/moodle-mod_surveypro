@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Validate creation and submit for "character" elements using the principal combinations of settings (7 of 7)
  Setting I check in this test are:
      # required:                       0 - 1
      # Text pattern:                   free pattern - email address - web page URL - custom
      # Minimum length (in characters): empty - 20

  Background:
    Given the following "courses" exist:
      | fullname                           | shortname      | category | numsections |
      | Test submission for character item | Character item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role            |
      | teacher1 | Character item | editingteacher |
      | student1 | Character item | student        |
    And the following "activities" exist:
      | activity  | name           | intro              | course         | idnumber   |
      | surveypro | Surveypro test | For testing backup | Character item | surveypro1 |
    And I log in as "teacher1"
    And I am on "Test submission for character item" course homepage
    And I follow "Surveypro test"
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"
    And I expand all fieldsets

  @javascript
  Scenario: test character element using configuration 13
    # Configuration 13 consists in:
      # required:                       1
      # Text pattern:                   web page URL
      # Minimum length (in characters): empty
    Given I set the following fields to these values:
      | Content    | Type a web address |
      | Required   | 1                  |
      | id_pattern | web page URL       |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for character item" course homepage
    And I follow "Surveypro test"

    # Test number 1: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    And I set the field "Type a web address" to "moodle site"
    And I press "Submit"
    Then I should see "Text is not a valid URL"
    And I set the field "Type a web address" to "www.moodle.org"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 1

  @javascript
  Scenario: test character element using configuration 14
    # Configuration 14 consists in:
      # required:                       1
      # Text pattern:                   web page URL
      # Minimum length (in characters): 20
    Given I set the following fields to these values:
      | Content                        | Type a web address |
      | Required                       | 1                  |
      | id_pattern                     | web page URL       |
      | Minimum length (in characters) | 20                 |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I am on "Test submission for character item" course homepage
    And I follow "Surveypro test"

    # Test number 2: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    And I set the field "Type a web address" to "moodle site"
    And I press "Submit"
    Then I should see "Text is not a valid URL"
    And I set the field "Type a web address" to "www.moodle.org"
    And I press "Submit"
    Then I should see "Text is too short"
    And I set the field "Type a web address" to "http://www.moodle.org"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 2
