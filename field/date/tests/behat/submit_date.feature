@mod @mod_surveypro @surveyprofield @surveyprofield_date
Feature: make a submission test for "date" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a date item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission for date item
    Given the following "courses" exist:
      | fullname                      | shortname            | category |
      | Test submission for date item | Date submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | teacher1 | Date submission test | editingteacher |
      | student1 | Date submission test | student        |
    And the following "activities" exist:
      | activity  | name         | intro                        | course               |
      | surveypro | Date test | To test submission of date item | Date submission test |
    And I am on the "Date test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Date [dd/mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When were you born? |
      | Required                 | 1                   |
      | Indent                   | 0                   |
      | Question position        | left                |
      | Element number           | 7                   |
      | Hide filling instruction | 1                   |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Date test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_date_1_day   | 16      |
      | id_surveypro_field_date_1_month | October |
      | id_surveypro_field_date_1_year  | 1988    |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
