@mod @mod_surveypro
Feature: Thanks users properly
  In order to test the thankspgae
  As a teacher and as a student
  I fill a surveypro in order to get the thanks page

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | category | groupmode |
      | Thank you | Thanks    | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | Thanks | editingteacher |
      | student1 | Thanks | student        |
    And the following "permission overrides" exist:
      | capability                       | permission | role    | contextlevel | reference |
      | mod/surveypro:editownsubmissions | Allow      | student | Course       | Thanks    |
    And the following "activities" exist:
      | activity  | name             | intro            | course |
      | surveypro | Thanks surveypro | Test thanks page | Thanks |
    And surveypro "Thanks surveypro" has the following items:
      | type  | plugin  |
      | field | boolean |
    And the following "blocks" exist:
      | blockname     | contextlevel | reference | pagetypepattern | defaultregion |
      | private_files | System       | 1         | my-index        | side-post     |

  @javascript
  Scenario: Test the empty thanks page
    When I am on the "Thanks surveypro" "Activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field "Inline thanks page" to ""
    And I press "Save and display"
    And I log out

    # student1 logs in
    When I am on the "Thanks surveypro" "surveypro activity" page logged in as student1
    And I press "New response"
    And I set the field "Is it true?" to "Yes"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully submitted!"

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is it true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"

  @javascript
  Scenario: Test the thanks page with plain text
    Given I am on the "Thanks surveypro" "surveypro activity" page logged in as student1
    And I press "New response"
    And I set the field "Is it true?" to "Yes"
    And I press "Submit"
    Then I should see "Thank you very much for your commitment on this survey."

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is it true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"

  @javascript @_file_upload @editor_tiny
  Scenario: Test the thanks page with images
    Given I log in as "teacher1"
    And I follow "Manage private files"
    And I upload "mod/surveypro/tests/fixtures/thankyou.png" file to "Files" filemanager
    And I click on "Save changes" "button"

    When I am on the "Thanks surveypro" "Activity editing" page logged in as teacher1
    And I expand all fieldsets

    # Atto needs focus to add image, select empty p tag to do so.
    And I click on the "Image" button for the "Inline thanks page" TinyMCE editor
    And I click on "Browse repositories" "button" in the "Insert image" "dialogue"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "thankyou.png" "link"
    And I click on "Select this file" "button"
    # And I set the field "Describe this image for someone who cannot see it" to "Thank you!"
    And I set the field "How would you describe this image to someone who can't see it:" to "Thank you!"
    And I click on "Save" "button" in the "Image details" "dialogue"
    And I press "Save and display"
    And I log out

    # student1 logs in
    When I am on the "Thanks surveypro" "surveypro activity" page logged in as student1
    And I press "New response"
    And I set the field "Is it true?" to "Yes"
    And I press "Submit"
    Then "//img[contains(@src, 'thankyou.png')]" "xpath_element" should exist

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is it true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"
