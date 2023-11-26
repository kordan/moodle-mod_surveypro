@mod @mod_surveypro
Feature: Submit each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I fill a surveypro and go to see responses

  @javascript @_file_upload
  Scenario: Test a submission with each core item
    Given the following "courses" exist:
      | fullname                                | shortname       | category | groupmode |
      | Test submission for each available item | Submission test | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |
    And the following "activities" exist:
      | activity  | name                 | intro                                      | course          |
      | surveypro | Each item submission | To test submission for each available item | Submission test |
    And the following "permission overrides" exist:
      | capability                            | permission | role    | contextlevel | reference       |
      | mod/surveypro:duplicateownsubmissions | Allow      | student | Course       | Submission test |
      | mod/surveypro:deleteownsubmissions    | Allow      | student | Course       | Submission test |
    And surveypro "Each item submission" has the following items:
      | type   | plugin      |
      | field  | age         |
      | field  | fileupload  |
      | field  | autofill    |
      | field  | boolean     |
      | field  | checkbox    |
      | field  | shortdate   |
      | field  | date        |
      | field  | datetime    |
      | format | pagebreak   |
      | field  | integer     |
      | field  | multiselect |
      | field  | numeric     |
      | field  | radiobutton |
      | format | fieldset    |
      | field  | rate        |
      | format | fieldsetend |
      | field  | recurrence  |
      | field  | select      |
      | field  | textarea    |
      | field  | character   |
      | field  | time        |
      | format | label       |
    And I am on the "Each item submission" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I select "Preview" from the "jump" singleselect
    And I press "Next page >>"
    And I press "<< Previous page"

    And I log out

    # student1 logs in
    When I am on the "Each item submission" "surveypro activity" page logged in as student1
    And I select "Responses" from the "jump" singleselect
    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | id_surveypro_field_age_1_year        | 23      |
      | id_surveypro_field_age_1_month       | 8       |

    And I upload "mod/surveypro/tests/fixtures/dummyCV.pdf" file to "Please, upload your CV in PDF format" filemanager

    And I set the following fields to these values:
      | Is it true?                          | Yes     |
      | id_surveypro_field_checkbox_5_0      | 1       |
      | id_surveypro_field_shortdate_6_month | March   |
      | id_surveypro_field_shortdate_6_year  | 1975    |
      | id_surveypro_field_date_7_day        | 16      |
      | id_surveypro_field_date_7_month      | October |
      | id_surveypro_field_date_7_year       | 1988    |
      | id_surveypro_field_datetime_8_day    | 23      |
      | id_surveypro_field_datetime_8_month  | August  |
      | id_surveypro_field_datetime_8_year   | 2010    |
      | id_surveypro_field_datetime_8_hour   | 17      |
      | id_surveypro_field_datetime_8_minute | 35      |
    And I press "Next page >>"

    And I set the following fields to these values:
      | How many people does your family counts?  | 7               |
      | id_surveypro_field_multiselect_11         | milk            |
      | Type the best approximation of π you know | 3.14            |
      | id_surveypro_field_radiobutton_13_3       | 1               |
      | id_surveypro_field_rate_15_0_0            | 1               |
      | id_surveypro_field_rate_15_1_1            | 1               |
      | id_surveypro_field_rate_15_2_2            | 1               |
      | id_surveypro_field_rate_15_3_3            | 1               |
      | id_surveypro_field_recurrence_17_day      | 7               |
      | id_surveypro_field_recurrence_17_month    | June            |
      | id_surveypro_field_select_18              | hills           |
      | Write a short description of yourself     | Super!          |
      | Write down your email, please             | me@myserver.net |
      | id_surveypro_field_time_21_hour           | 7               |
      | id_surveypro_field_time_21_minute         | 15              |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions

    And I follow "view_submission_row_1"
    And I press "Next page >>"
    And I press "<< Previous page"
    And I select "Responses" from the "jump" singleselect

    And I should see "1" submissions

    And I follow "duplicate_submission_row_1"
    And I press "Continue"
    And I should see "2" submissions

    And I follow "delete_submission_row_2"
    And I press "Continue"
    And I should see "1" submissions

    And I log out

    When I am on the "Test submission for each available item" course page logged in as teacher1
    And I follow "Each item submission"
    And I select "Responses" from the "jump" singleselect
    And I follow "edit_submission_row_1"
    And I press "Next page >>"
    And I set the field "id_surveypro_field_multiselect_11" to "sugar, jam"
    And I press "Submit"

    And I press "Continue to responses list"
