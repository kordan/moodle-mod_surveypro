@mod @mod_surveypro @surveyprofield @surveyprofield_textarea
Feature: Submit using textarea item and check form validation
  Setting I check in this test are:
      # required:                       0 - 1
      # Use html editor:                0 - 1
      # Minimum length (in characters): 20
      # Maximum length (in characters): 1 - empty

  Background:
    Given the following "courses" exist:
      | fullname                          | shortname     | category | numsections |
      | Test submission for textarea item | Textarea item | 0        | 3           |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Textarea item | editingteacher |
      | student1 | Textarea item | student        |
    And the following "activities" exist:
      | activity  | name           | intro              | course        |
      | surveypro | Surveypro test | For testing backup | Textarea item |
    And I am on the "Surveypro test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Text (long)"
    And I press "Add"
    And I expand all fieldsets

  @javascript
  Scenario: Test textarea element using configuration 01
    # Configuration 01 consists in:
      # required:                       0
      # Use html editor:                1
      # Minimum length (in characters): 20
      # Maximum length (in characters): 1 and then empty
    # Test number 1: teacher create an invalid element
    Given I set the following fields to these values:
      | Content                        | Write a short description of yourself |
      | Required                       | 0                                     |
      | Use html editor                | 1                                     |
      | Minimum length (in characters) | 20                                    |
      | Maximum length (in characters) | 1                                     |
    And I press "Add"
    Then I should see "Maximum length can not be lowwer-equal than minimum length"
    And I set the field "Maximum length (in characters)" to ""
    And I press "Add"
    # End of test number 1

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 2: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 2

    # Test number 3: student tries to submit an too short answer
    And I press "New response"
    And I set the field "Write a short description of yourself" to "Super!"
    And I press "Submit"
    Then I should see "Text is too short"
    # End of test number 3

    # Test number 4: student submits a correct answer
    And I set the field "Write a short description of yourself" to "Ok! Now I submit a correct answer."
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions
    # End of test number 4

  @javascript
  Scenario: Test textarea element using configuration 02
    # Configuration 02 consists in:
      # required:                       1
      # Use html editor:                1
      # Minimum length (in characters): 20
      # Maximum length (in characters): empty
    Given I set the following fields to these values:
      | Content                        | Write a short description of yourself |
      | Required                       | 1                                     |
      | Use html editor                | 1                                     |
      | Minimum length (in characters) | 20                                    |
    And I press "Add"

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 5: student submits an empty answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    # End of test number 5

    # Test number 6: student submits a too short answer
    And I set the field "Write a short description of yourself" to "Super!"
    And I press "Submit"
    Then I should see "Text is too short"
    # End of test number 6

    # Test number 7: student submits a correct answer
    And I set the field "Write a short description of yourself" to "Ok! Now I submit a correct answer."
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 7

  @javascript
  Scenario: Test textarea element using configuration 03
    # Configuration 03 consists in:
      # required:                       0
      # Use html editor:                0
      # Minimum length (in characters): 20
      # Maximum length (in characters): 1 and then empty
    Given I set the following fields to these values:
      | Content                        | Write a short description of yourself |
      | Required                       | 0                                     |
      | Use html editor                | 0                                     |
      | Minimum length (in characters) | 20                                    |
      | Maximum length (in characters) | 1                                     |
    And I press "Add"
    Then I should see "Maximum length can not be lowwer-equal than minimum length"
    # End of test number 8

    # if the corresponding field is submitted when still empty
    And I set the field "Maximum length (in characters)" to ""
    And I press "Add"

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 9: Student flies over the answer
    And I press "New response"
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 9

    # Test number 10: student tries to submit an too short answer
    And I press "New response"
    And I set the field "Write a short description of yourself" to "Super!"
    And I press "Submit"
    Then I should see "Text is too short"
    # End of test number 10

    # Test number 11: student submits a correct answer
    And I set the field "Write a short description of yourself" to "Ok! Now I submit a correct answer."
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "2" submissions
    # End of test number 11

  @javascript
  Scenario: Test textarea element using configuration 04
    # Configuration 04 consists in:
      # required:                       1
      # Use html editor:                0
      # Minimum length (in characters): 20
      # Maximum length (in characters): empty
    Given I set the following fields to these values:
      | Content                        | Write a short description of yourself |
      | Required                       | 1                                     |
      | Use html editor                | 0                                     |
      | Minimum length (in characters) | 20                                    |
    And I press "Add"

    And I log out

    When I am on the "Surveypro test" "surveypro activity" page logged in as student1

    # Test number 12: student submits an empty answer
    And I press "New response"
    And I press "Submit"
    Then I should see "Required"
    # End of test number 12

    # Test number 13: student submits a too short answer
    And I set the field "Write a short description of yourself" to "Super!"
    And I press "Submit"
    Then I should see "Text is too short"
    # End of test number 13

    # Test number 14: student submits a correct answer
    And I set the field "Write a short description of yourself" to "Ok! Now I submit a correct answer."
    And I press "Submit"
    And I press "Continue to responses list"
    Then I should see "1" submissions
    # End of test number 14
