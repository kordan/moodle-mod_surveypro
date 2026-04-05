@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Submit using character item and check form validation (5 of 7)
  Settings I check in this test are:
      # required:                       0 - 1
      # Text pattern:                   free pattern - email address - web page URL - custom
      # Minimum length (in characters): empty - 20

  Background:
    Given the following "courses" exist:
      | fullname                           | shortname      | category | numsections |
      | Test submission for character item | Character item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | student1 | Character item | student        |
    And the following "activities" exist:
      | activity  | name           | intro              | course         |
      | surveypro | Surveypro test | For testing backup | Character item |

  @javascript
  Scenario: Test character element using configuration 09
    # Configuration 09 consists in:
      # required:                       1
      # Text pattern:                   free pattern
      # Minimum length (in characters): empty
    Given surveypro "Surveypro test" has the following items:
      | type  | plugin    | settings                                                                    |
      | field | character | {"content":"This is a free text", "required":"1", "pattern":"PATTERN_FREE"} |
    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 1: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    And I set the field "This is a free text" to "Nice to know!"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 1

  @javascript
  Scenario: Test character element using configuration 10
    # Configuration 10 consists in:
      # required:                       1
      # Text pattern:                   free pattern
      # Minimum length (in characters): 20
    Given surveypro "Surveypro test" has the following items:
      | type  | plugin    | settings                                                                                      |
      | field | character | {"content":"This is a free text", "required":"1", "pattern":"PATTERN_FREE", "minlength":"20"} |
    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

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
    Then I should see "1" submissions
    # End of test number 2
