@mod @mod_surveypro @surveyprofield @surveyprofield_shortdate
Feature: make a submission test for "shortdate" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a shortdate item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission for short date item
    Given the following "courses" exist:
      | fullname                           | shortname                 | category |
      | Test submission for shortdate item | Shortdate submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                    | role           |
      | teacher1 | Shortdate submission test | editingteacher |
      | student1 | Shortdate submission test | student        |
    And the following "activities" exist:
      | activity  | name           | intro                           | course                    |
      | surveypro | Shortdate test | To test submission of shortdate | Shortdate submission test |
    And I am on the "Shortdate test" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    And I set the field "typeplugin" to "Date (short) [mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When did you buy your current car? |
      | Required                 | 1                                  |
      | Indent                   | 0                                  |
      | Question position        | left                               |
      | Element number           | 6                                  |
      | Hide filling instruction | 1                                  |
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Shortdate test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_shortdate_1_month | March |
      | id_surveypro_field_shortdate_1_year  | 2005  |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
