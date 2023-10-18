@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Submit using character item and check form validation (4 of 7)
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
      | activity  | name           | intro              | course         |
      | surveypro | Surveypro test | For testing backup | Character item |
    And I am on the "Surveypro test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"
    And I expand all fieldsets

  @javascript
  Scenario: Test character element using configuration 07
    # Configuration 07 consists in:
      # required:                       0
      # Text pattern:                   custom
      # Minimum length (in characters): empty
    Given I set the following fields to these values:
      | Content        | Enter a zip code |
      | Required       | 0                |
      | id_pattern     | custom pattern   |
      | id_patterntext | 00000            |
    And I press "Add"

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 1: Student flies over the answer
    And I press "New response"
    Then I should see "Text is supposed to be exactly 5 characters long; Text is supposed to match the following pattern: \"00000\""
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 1

    # Test number 2: Student submits a standard answer
    And I press "New response"
    Then I should see "Text is supposed to be exactly 5 characters long; Text is supposed to match the following pattern: \"00000\""
    And I set the field "Enter a zip code" to "Cool"
    And I press "Submit"
    Then I should see "Text does not match the required pattern"
    And I set the field "Enter a zip code" to "00123"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions
    # End of test number 2

  @javascript
  Scenario: Test character element using configuration 08
    # Configuration 08 consists in:
      # required:                       1
      # Text pattern:                   custom
      # Minimum length (in characters): empty
    Given I set the following fields to these values:
      | Content        | Enter a zip code |
      | Required       | 1                |
      | id_pattern     | custom pattern   |
      | id_patterntext | 00000            |
    And I press "Add"

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 3: Student flies over the answer
    And I press "New response"
    Then I should see "Text is supposed to be exactly 5 characters long; Text is supposed to match the following pattern: \"00000\""
    And I press "Submit"
    Then I should see "Required"
    And I set the field "Enter a zip code" to "Cool"
    And I press "Submit"
    Then I should see "Text does not match the required pattern"
    And I set the field "Enter a zip code" to "00123"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 3
