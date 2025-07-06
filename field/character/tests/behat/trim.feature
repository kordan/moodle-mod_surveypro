@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: Test the use of character trim
  In order to test character trim feature
  As a student
  I fill a field and I verify what is in it

  @javascript
  Scenario: Test character trim
    Given the following "courses" exist:
      | fullname            | shortname           | category | groupmode |
      | Character trim test | Character trim test | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | teacher1 | Character trim test | editingteacher |
      | student1 | Character trim test | student        |
    And the following "activities" exist:
      | activity  | name                | intro               | course              |
      | surveypro | Test character trim | Test character trim | Character trim test |
    And I am on the "Test character trim" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # add an character item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | This is a standard text  |
      | Required                       | 1                        |
      | Clean answer at save time      | 0                        |
      | Additional note                | This will not be trimmed |
      | pattern                        | free pattern             |
      | Minimum length (in characters) | 20                       |
      | Maximum length (in characters) | 30                       |
    And I press "Add"

    # add one more character item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | Text to trim         |
      | Required                       | 1                    |
      | Clean answer at save time      | 1                    |
      | Additional note                | This will be trimmed |
      | pattern                        | free pattern         |
      | Minimum length (in characters) | 20                   |
      | Maximum length (in characters) | 30                   |
    And I press "Add"

    And I log out

    When I am on the "Test character trim" "surveypro activity" page logged in as student1

    # Test number 1: Student insert a record
    And I press "New response"
    And I set the field "This is a standard text" to "   false long text   "
    And I set the field "Text to trim" to "   false long text   "

    And I press "Submit"

    Then I should see "Text is too short"
    And I set the field "This is a standard text" to ""
    And I set the field "Text to trim" to "   text correctly trimmed   "
    And I press "Submit"

    Then I should see "Required"
    Then I should see "Answer will be cleaned up from trailing spaces"
    And I set the field "This is a standard text" to "   false long text   "
    And I press "Submit"

    And I press "Continue to responses list"
    And I follow "view_submission_row_1"
    Then the field "id_field_character_1" matches value "   false long text   "
    Then the field "id_field_character_2" matches value "text correctly trimmed"
