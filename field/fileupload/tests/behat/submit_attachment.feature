@mod @mod_surveypro @surveyprofield @surveyprofield_fileupload
Feature: make a submission test for "fileupload" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an attachment item, I fill it and I go to see responses

  @javascript @_file_upload
  Scenario: test a submission works fine for attachment item
    Given the following "courses" exist:
      | fullname                            | shortname                  | category |
      | Test submission for attachment item | Attachment submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                     | role           |
      | teacher1 | Attachment submission test | editingteacher |
      | student1 | Attachment submission test | student        |
    And the following "activities" exist:
      | activity  | name            | intro                                 | course                     |
      | surveypro | Attachment test | To test submission of attachment item | Attachment submission test |
    And I log in as "teacher1"
    And I am on "Test submission for attachment item" course homepage
    And I follow "Attachment test"

    And I set the field "typeplugin" to "Attachment"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Please upload your Curriculum Vitae |
      | Required                 | 1                                   |
      | Indent                   | 0                                   |
      | Question position        | left                                |
      | Element number           | 2                                   |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Test submission for attachment item" course homepage
    And I follow "Attachment test"
    And I press "New response"

    # student1 submits
    And I upload "mod/surveypro/tests/fixtures/dummyCV.pdf" file to "2: Please upload your Curriculum Vitae" filemanager

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
