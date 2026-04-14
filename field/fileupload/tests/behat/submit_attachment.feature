@mod @mod_surveypro @surveyprofield @surveyprofield_fileupload
Feature: Submit using a fileupload item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an attachment item, I fill it and I go to see responses

  @javascript @_file_upload
  Scenario: Test a submission for attachment item
    Given the following "courses" exist:
      | fullname                            | shortname                  | category |
      | Test submission for attachment item | Attachment submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "user preferences" exist:
      | user     | preference | value    |
    And the following "course enrolments" exist:
      | user     | course                     | role    |
      | student1 | Attachment submission test | student |
    And the following "activities" exist:
      | activity  | name            | intro                                 | course                     |
      | surveypro | Attachment test | To test submission of attachment item | Attachment submission test |
    And surveypro "Attachment test" has the following items:
      | type  | plugin     | settings                     |
      | field | fileupload | {"content":"Upload your CV"} |
    And I am on the "Attachment test" "surveypro activity" page logged in as student1

    # student1 submits
    And I press "New response"
    And I upload "mod/surveypro/tests/fixtures/dummyCV.pdf" file to "Upload your CV" filemanager
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    When I click on "//a[contains(@id,'view_submission_row_1')]" "xpath_element"
    Then I should see "dummyCV.pdf"
