@mod @mod_surveypro @surveyprofield @surveyprofield_character @current
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
  Scenario: test character element with the following settings: 1; web page URL; empty
      # required:                       1
      # Text pattern:                   web page URL
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
      | Content                        | Type a web address |
      | Required                       | 1                  |
      | id_pattern                     | web page URL       |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for character item"
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
    Then I should see "1" submissions displayed
    # End of test number 1

  @javascript
  Scenario: test character element with the following settings: 1; web page URL; 20
      # required:                       1
      # Text pattern:                   web page URL
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
      | Content                        | Type a web address |
      | Required                       | 1                  |
      | id_pattern                     | web page URL       |
      | Minimum length (in characters) | 20                 |
    And I press "Add"

    And I log out
    When I log in as "student1"
    And I follow "Test submission for character item"
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
    Then I should see "1" submissions displayed
    # End of test number 2
