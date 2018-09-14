@mod @mod_surveypro
Feature: verify the thanks page is shown properly
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
      | capability                          | permission | role    | contextlevel | reference |
      | mod/surveypro:editownsubmissions    | Allow      | student | Course       | Thanks    |
    And the following "activities" exist:
      | activity  | name             | intro            | course | idnumber   |
      | surveypro | Thanks surveypro | Test thanks page | Thanks | surveypro1 |
    And surveypro "Thanks surveypro" contains the following items:
      | type  | plugin  |
      | field | boolean |

  @javascript
  Scenario: test the empty thanks page
    When I log in as "teacher1"
    And I am on "Thank you" course homepage
    And I follow "Thanks surveypro"
    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets
    And I set the field "Inline thanks page" to ""
    And I press "Save and display"
    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Thank you" course homepage
    And I follow "Thanks surveypro"
    And I press "New response"
    And I set the field "Is this true?" to "Yes"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully submitted!"

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is this true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"

  @javascript
  Scenario: test the thanks page with plain text
    # student1 logs in
    When I log in as "student1"
    And I am on "Thank you" course homepage
    And I follow "Thanks surveypro"
    And I press "New response"
    And I set the field "Is this true?" to "Yes"
    And I press "Submit"
    Then I should see "Thank you very much for your commitment on this survey."

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is this true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"

  @javascript
  Scenario: test the thanks page with images
    When I log in as "teacher1"
    And I follow "Manage private files"
    # And I upload "mod/lesson/tests/fixtures/moodle_logo.jpg" file to "Files" filemanager
    And I upload "mod/surveypro/tests/fixtures/thankyou.png" file to "Files" filemanager
    And I click on "Save changes" "button"

    And I am on "Thank you" course homepage
    And I follow "Thanks surveypro"

    And I navigate to "Edit settings" in current page administration
    And I expand all fieldsets

    # Atto needs focus to add image, select empty p tag to do so.
    And I select the text in the "id_thankspageeditor" Atto editor
    And I click on "Insert or edit image" "button"
    And I click on "Browse repositories..." "button"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "thankyou.png" "link"
    And I click on "Select this file" "button"
    And I set the field "Describe this image for someone who cannot see it" to "Thank you!"
    And I click on "Save image" "button"
    And I press "Save and display"
    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "Thank you" course homepage
    And I follow "Thanks surveypro"
    And I press "New response"
    And I set the field "Is this true?" to "Yes"
    And I press "Submit"
    Then "//img[contains(@src, 'thankyou.png')]" "xpath_element" should exist

    And I press "Continue to responses list"
    And I follow "edit_submission_row_1"
    And I set the field "Is this true?" to "No"
    And I press "Submit"
    Then I should see "Thank you. Your response has been successfully modified!"
