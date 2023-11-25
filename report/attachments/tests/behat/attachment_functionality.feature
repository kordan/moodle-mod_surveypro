@mod @mod_surveypro @surveyproreport @surveyproreport_attachment
Feature: Test attachment report
  In order to test functionality of attachment report
  As teacher1
  I call the report and verify it

  @javascript @_file_upload
  Scenario: Test attachment report
    Given the following "courses" exist:
      | fullname               | shortname              | category | groupmode |
      | Test attachment report | Test attachment report | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | student1  | user1    | student1@nowhere.net |
      | student2 | student2  | user2    | student2@nowhere.net |
      | student3 | student3  | user3    | student3@nowhere.net |
      | student4 | student4  | user4    | student4@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role           |
      | teacher1 | Test attachment report | editingteacher |
      | student1 | Test attachment report | student        |
      | student2 | Test attachment report | student        |
      | student3 | Test attachment report | student        |
      | student4 | Test attachment report | student        |
    And the following "permission overrides" exist:
      | capability                       | permission | role    | contextlevel | reference              |
      | mod/surveypro:editownsubmissions | Allow      | student | Course       | Test attachment report |
    And the following "user preferences" exist:
      | user     | preference | value    |
      | teacher1 | htmleditor | textarea |
    And the following "activities" exist:
      | activity  | name                   | intro                                      | course                 |
      | surveypro | Attachment report test | To test functionality of attachment report | Test attachment report |
    And surveypro "Attachment report test" has the following items:
      | type   | plugin     | content                            |
      | field  | fileupload | Upload a passport photo            |
      | field  | fileupload | Upload your CV                     |
      | field  | fileupload | Upload the picture for the contest |
    And I log in as "teacher1"
    And I am on "Test attachment report" course homepage

    And I follow "Attachment report test"
    And I follow "Layout"

    And I log out

    # student1 logs in
    When I am on the "Attachment report test" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect

    And I press "New response"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/photo11.png" file to "Upload a passport photo" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/dummyCV11.pdf" file to "Upload your CV" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/contest11.jpg" file to "Upload the picture for the contest" filemanager
    And I press "Submit"

    And I press "New response"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/photo12.gif" file to "Upload a passport photo" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_2"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/contest12.jpg" file to "Upload the picture for the contest" filemanager
    And I press "Submit"

    And I log out

    # student2 logs in
    When I am on the "Attachment report test" "surveypro activity" page logged in as student2
    And I select "Responses" from the "jump" singleselect

    And I press "New response"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/photo21.jpg" file to "Upload a passport photo" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/dummyCV21.pdf" file to "Upload your CV" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/contest21.jpg" file to "Upload the picture for the contest" filemanager
    And I press "Submit"

    And I log out

    # student3 logs in
    When I am on the "Attachment report test" "surveypro activity" page logged in as student3
    And I select "Responses" from the "jump" singleselect

    And I press "New response"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/photo31.jpg" file to "Upload a passport photo" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/dummyCV31.pdf" file to "Upload your CV" filemanager
    And I press "Submit"
    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I upload "mod/surveypro/report/attachments/tests/fixtures/uploads/contest31.jpg" file to "Upload the picture for the contest" filemanager
    And I press "Submit"

    And I log out

    # teacher logs in
    When I am on the "Attachment report test" "surveypro activity" page logged in as teacher1
    And I follow "Run Attachments overview report"

    # Feature 1: only user who actually submitted are in the list
    Then I should see "student1"
    Then I should see "student2"
    Then I should see "student3"
    Then I should not see "student4"

    # Attachments of Student1
    When I follow "Display attachments"

    # Feature 2: the "Element" drop down menu holds the list of the surveypro "attachment" items
    # Then I set the field "Element" to "Each item"
    Then I set the field "Element" to "Upload a passport photo"
    Then I set the field "Element" to "Upload your CV"
    Then I set the field "Element" to "Upload the picture for the contest"

    # Feature 3: the "Attempt" drop down menu holds the list of each submission of the selected user plus the name of "next" user
    # I do not test the right items are actually listed among drop down items because I will call them in seconds

    # Feature 4: using "Element" drop down menu I can filter the "attachment" field
    When I set the field "Element" to "Upload your CV"
    And I press "Reload"
    Then I should see "dummyCV11.pdf"

    When I set the field "Element" to "Upload the picture for the contest"
    And I press "Reload"
    Then I should see "contest11.jpg"

    When I set the field "Element" to "Each item"
    And I press "Reload"
    Then I should see "photo11.png"
    Then I should see "dummyCV11.pdf"
    Then I should see "contest11.jpg"

    # Feature 5: using the "Attempt" drop down menu I can switch between submissions
    When I set the field "Attempt" to "student1 user1 - Response: 2"
    And I press "Reload"
    Then I should see "photo12.gif"
    Then I should see "no attachment uploaded"
    Then I should see "contest12.jpg"

    # Feature 7: with the "Attempt" drop down menu I can switch to the next user
    When I set the field "Attempt" to "student2 user2"
    And I press "Reload"
    Then I should see "photo21.jpg"
    Then I should see "dummyCV21.pdf"
    Then I should see "contest21.jpg"

    # Feature 8: when the selected user is the last of the list, the next one is the first of the list
    When I set the field "Attempt" to "student3"
    And I press "Reload"
    Then I should see "photo31.jpg"
    Then I should see "dummyCV31.pdf"
    Then I should see "contest31.jpg"

    # Student4 is not supposed to be in the list because the user did not upload any file
    Then I set the field "Attempt" to "student1"

    # Feature 9: To switch to a user different from the next one, return to the original list
    When I press "Choose a different user"
    Then I should see "student1"
    Then I should see "student2"
    Then I should see "student3"
    Then I should not see "student4"

    # Feature 10: using $CFG->forcefirstname and $CFG->forcelastname informations change
