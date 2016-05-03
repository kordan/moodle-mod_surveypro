@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: make a submission test for "age" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add an age item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for age item
    Given the following "courses" exist:
      | fullname                     | shortname           | category |
      | Test submission for age item | Age submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Age submission test | editingteacher |
      | student1 | Age submission test | student        |
    And the following "activities" exist:
      | activity  | name     | intro                          | course              | idnumber   |
      | surveypro | Age test | To test submission of age item | Age submission test | surveypro1 |
    And I log in as "teacher1"
    And I follow "Test submission for age item"
    And I follow "Age test"

    And I set the field "typeplugin" to "Age"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How old were you when you learned to ride a bike? |
      | Required                 | 1                                                 |
      | Indent                   | 0                                                 |
      | Question position        | left                                              |
      | Element number           | 1                                                 |
      | Hide filling instruction | 1                                                 |
      | id_defaultoption_2       | Custom                                            |
      | id_defaultvalue_year     | 14                                                |
      | id_defaultvalue_month    | 4                                                 |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for age item"
    And I follow "Age test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_age_1_year  | 23 |
      | id_surveypro_field_age_1_month | 8  |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
