@mod @mod_surveypro @surveyprofield @surveyprofield_textarea
Feature: Trim textarea content
  In order to test textarea trim feature
  As a student
  I fill a field and I verify what is in it

  @javascript
  Scenario: Test textarea trim
    Given the following "courses" exist:
      | fullname           | shortname          | category | groupmode |
      | Textarea trim test | Textarea trim test | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | student1 | Textarea trim test | student        |
    And the following "activities" exist:
      | activity  | name               | intro              | course             |
      | surveypro | Test textarea trim | Test textarea trim | Textarea trim test |
    And the following "permission overrides" exist:
      | capability                          | permission | role    | contextlevel | reference          |
      | mod/surveypro:editownsubmissions    | Allow      | student | Course       | Textarea trim test |
    And surveypro "Test textarea trim" has the following items:
      | type  | plugin   | settings                                                                                                                                                                |
      | field | textarea | {"content":"This is a standard text", "required":"1", "customnumber":"1", "extranote":"This will not be trimmed", "minlength":"20", "maxlength":"30"}                   |
      | field | textarea | {"content":"Text to trim",            "required":"1", "customnumber":"2", "extranote":"This will be trimmed",     "minlength":"20", "maxlength":"30", "trimonsave":"1"} |
    When I am on the "Test textarea trim" "surveypro activity" page logged in as student1

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
    And I follow "edit_submission_row_1"
    Then the field "id_field_textarea_1" matches value "   false long text   "
    Then the field "id_field_textarea_2" matches value "text correctly trimmed"
