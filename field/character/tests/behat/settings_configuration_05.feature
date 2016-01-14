@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Validate feebacks of creation and submit using all the principal combinations of settings
  Setting I check in this test are:
      # required:                       0 - 1
      # Text pattern:                   free pattern - email address - web page URL - custom
      # Minimum length (in characters): empty - 20
  In order to validate backup and restore process
  As a teacher
  I duplicate a surveypro instance.

  Background:
    Given the following "courses" exist:
      | fullname                           | shortname      | category | numsections |
      | Test submission for character item | Character item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role            |
      | teacher1 | Character item | editingteacher |
      | student1 | Character item | student        |
    And I log in as "teacher1"

  @javascript
  Scenario: test long text element with the following settings: 1; free pattern; empty
      # required:                       1
      # Text pattern:                   free pattern
      # Minimum length (in characters): empty
    Given the following "activities" exist:
      | activity   | name           | intro              | course         | idnumber   |
      | surveypro  | Surveypro test | For testing backup | Character item | surveypro1 |

    And I follow "Test submission for character item"
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | This is a free text |
      | Required                       | 1                   |
      | id_pattern                     | free pattern        |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for character item"
    And I follow "Surveypro test"

    # Test number 1: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    And I set the field "This is a free text" to "Nice to know!"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
    # End of test number 1

  @javascript
  Scenario: test long text element with the following settings: 1; free pattern; 20
      # required:                       1
      # Text pattern:                   free pattern
      # Minimum length (in characters): 20
    Given the following "activities" exist:
      | activity   | name           | intro              | course         | idnumber   |
      | surveypro  | Surveypro test | For testing backup | Character item | surveypro1 |

    And I follow "Test submission for character item"
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | This is a free text |
      | Required                       | 1                   |
      | id_pattern                     | free pattern        |
      | Minimum length (in characters) | 20                  |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for character item"
    And I follow "Surveypro test"

    # Test number 2: Student flies over the answer
    And I press "New response"
    Then I should see "Text is supposed to be longer-equal than 20 characters"
    And I press "Submit"
    Then I should see "Required"
    And I set the field "This is a free text" to "Nice to know!"
    And I press "Submit"
    Then I should see "Text is too short"
    And I set the field "This is a free text" to "Ok! Now I enter a correct text."
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
    # End of test number 2
