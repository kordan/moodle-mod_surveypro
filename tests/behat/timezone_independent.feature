@mod @mod_surveypro
Feature: Test answers are not timezone dependent
  In order to verify answers are not timezone dependent
  As a student
  I submit answers and I check other answers.

  @javascript
  Scenario: Delete a surveypro activity
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | TZ free  | noTZ      | 0        | 0         |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | noTZ   | G1       |
    And the following "users" exist:
      | username | firstname | lastname | email                | timezone        |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net | Africa/Asmara   |
      | student1 | student1  | user1    | student1@nowhere.net | Indian/Maldives |
      | student2 | student2  | user2    | student2@nowhere.net | America/Bahia   |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | noTZ   | editingteacher |
      | student1 | noTZ   | student        |
      | student2 | noTZ   | student        |
    And the following "group members" exist:
      | user     | group |
      | student1 | G1    |
      | student2 | G1    |
    And the following "permission overrides" exist:
      | capability                         | permission | role    | contextlevel | reference |
      | mod/surveypro:seeotherssubmissions | Allow      | student | Course       | noTZ      |
    And the following "activities" exist:
      | activity  | name        | intro    | course |
      | surveypro | sameanswers | To trash | noTZ   |
    And surveypro "sameanswers" has the following items:
      | type   | plugin      |
      | format | label       |
      | field  | age         |
      | field  | date        |
      | field  | datetime    |
      | field  | recurrence  |
      | field  | shortdate   |
      | field  | time        |

    When I am on the "sameanswers" "surveypro activity" page logged in as student1
    And I press "New response"
    And I set the following fields to these values:
      | id_surveypro_field_age_2_year         | 23      |
      | id_surveypro_field_age_2_month        | 8       |
      | id_surveypro_field_date_3_day         | 16      |
      | id_surveypro_field_date_3_month       | March   |
      | id_surveypro_field_date_3_year        | 1988    |
      | id_surveypro_field_datetime_4_day     | 11      |
      | id_surveypro_field_datetime_4_month   | August  |
      | id_surveypro_field_datetime_4_year    | 2010    |
      | id_surveypro_field_datetime_4_hour    | 17      |
      | id_surveypro_field_datetime_4_minute  | 35      |
      | id_surveypro_field_recurrence_5_day   | 4       |
      | id_surveypro_field_recurrence_5_month | October |
      | id_surveypro_field_shortdate_6_month  | June    |
      | id_surveypro_field_shortdate_6_year   | 1975    |
      | id_surveypro_field_time_7_hour        | 7       |
      | id_surveypro_field_time_7_minute      | 15      |
    And I press "Submit"

    And I log out

    When I am on the "sameanswers" "surveypro activity" page logged in as student2
    And I press "New response"
    And I set the following fields to these values:
      | id_surveypro_field_age_2_year         | 23      |
      | id_surveypro_field_age_2_month        | 8       |
      | id_surveypro_field_date_3_day         | 16      |
      | id_surveypro_field_date_3_month       | March   |
      | id_surveypro_field_date_3_year        | 1988    |
      | id_surveypro_field_datetime_4_day     | 11      |
      | id_surveypro_field_datetime_4_month   | August  |
      | id_surveypro_field_datetime_4_year    | 2010    |
      | id_surveypro_field_datetime_4_hour    | 17      |
      | id_surveypro_field_datetime_4_minute  | 35      |
      | id_surveypro_field_recurrence_5_day   | 4       |
      | id_surveypro_field_recurrence_5_month | October |
      | id_surveypro_field_shortdate_6_month  | June    |
      | id_surveypro_field_shortdate_6_year   | 1975    |
      | id_surveypro_field_time_7_hour        | 7       |
      | id_surveypro_field_time_7_minute      | 15      |
    And I press "Submit"

    And I press "Continue to responses list"
    And I follow "view_submission_row_1"

    Then I should see "23"
    Then I should see "8"
    Then I should see "16"
    Then I should see "March"
    Then I should see "1988"
    Then I should see "23"
    Then I should see "August"
    Then I should see "2010"
    Then I should see "17"
    Then I should see "35"
    Then I should see "4"
    Then I should see "October"
    Then I should see "June"
    Then I should see "1975"
    Then I should see "07"
    Then I should see "15"

    And I select "Responses" from the "jump" singleselect
    And I follow "view_submission_row_2"

    Then I should see "23"
    Then I should see "8"
    Then I should see "16"
    Then I should see "March"
    Then I should see "1988"
    Then I should see "23"
    Then I should see "August"
    Then I should see "2010"
    Then I should see "17"
    Then I should see "35"
    Then I should see "4"
    Then I should see "October"
    Then I should see "June"
    Then I should see "1975"
    Then I should see "07"
    Then I should see "15"

    And I log out
