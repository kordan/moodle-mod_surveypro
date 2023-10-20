@mod @mod_surveypro @surveyprofield @surveyprofield_datetime
Feature: Submit using a datetime item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a datetime item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for datetime item
    Given the following "courses" exist:
      | fullname                          | shortname                | category |
      | Test submission for datetime item | Datetime submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                   | role           |
      | teacher1 | Datetime submission test | editingteacher |
      | student1 | Datetime submission test | student        |
    And the following "activities" exist:
      | activity  | name          | intro                           | course                   |
      | surveypro | Datetime test | To test submission of date item | Datetime submission test |
    And I am on the "Datetime test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Date and time [dd/mm/yyyy;hh:mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Please, write down date and time of your last flight to Los Angeles. |
      | Required                 | 1                                                                    |
      | Indent                   | 0                                                                    |
      | Question position        | left                                                                 |
      | Element number           | 5a                                                                   |
      | Hide filling instruction | 1                                                                    |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Datetime test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_datetime_1_day    | 23     |
      | id_surveypro_field_datetime_1_month  | August |
      | id_surveypro_field_datetime_1_year   | 2010   |
      | id_surveypro_field_datetime_1_hour   | 17     |
      | id_surveypro_field_datetime_1_minute | 35     |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
