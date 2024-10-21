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
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "user preferences" exist:
      | user     | preference | value    |
      | teacher1 | htmleditor | textarea |
    And the following "course enrolments" exist:
      | user     | course                     | role           |
      | teacher1 | Attachment submission test | editingteacher |
      | student1 | Attachment submission test | student        |
    And the following "activities" exist:
      | activity  | name            | intro                                 | course                     |
      | surveypro | Attachment test | To test submission of attachment item | Attachment submission test |
    And I am on the "Attachment test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Attachment"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Upload your CV |
      | Required          | 1              |
      | Indent            | 0              |
      | Question position | left           |
    And I press "Add"

    And I log out

    # student1 logs in

    When I am on the "Attachment test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    # And I pause scenario execution
    And I upload "mod/surveypro/tests/fixtures/dummyCV.pdf" file to "Upload your CV" filemanager

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
