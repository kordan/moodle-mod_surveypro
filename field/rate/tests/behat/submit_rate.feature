@mod @mod_surveypro @surveyprofield @surveyprofield_rate
Feature: Submit using a rate item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a rate item, I fill it and I go to see responses

  @javascript
  Scenario: Test a submission for rate item
    Given the following "courses" exist:
      | fullname                      | shortname            | category |
      | Test submission for rate item | Rate submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | teacher1 | Rate submission test | editingteacher |
      | student1 | Rate submission test | student        |
    And the following "activities" exist:
      | activity  | name      | intro                           | course               |
      | surveypro | Rate test | To test submission of date item | Rate submission test |
    And I am on the "Rate test" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | How confident are you with the following languages? |
      | Required       | 1                                                   |
      | Indent         | 0                                                   |
      | Element number | 13a                                                 |
      | Element style  | radio buttons                                       |
    And I set the multiline field "Options" to "   Italian\nSpanish\n\n\n            English\n\n\nFrench\n\nGerman\n\n\n"
    And I set the multiline field "Rates" to "\n\n         Mother tongue\n   Very confident\nSomewhat confident\n\nNot confident at all\n"
    And I press "Add"

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | How confident are you with the following languages? |
      | Required       | 1                                                   |
      | Indent         | 0                                                   |
      | Element number | 13b                                                 |
      | Element style  | dropdown menu                                       |
    And I set the multiline field "Options" to "   Italian\nSpanish\n\n\n            English\n\n\nFrench\n\nGerman\n\n\n"
    And I set the multiline field "Rates" to "\n\n         Mother tongue\n   Very confident\nSomewhat confident\n\nNot confident at all\n"
    And I press "Add"

    And I log out

    # student1 logs in
    When I am on the "Rate test" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_field_rate_1_0_0 | 1                    |
      | id_field_rate_1_1_1 | 1                    |
      | id_field_rate_1_2_2 | 1                    |
      | id_field_rate_1_3_3 | 1                    |
      | id_field_rate_1_4_2 | 1                    |
      | id_field_rate_2_0   | Mother tongue        |
      | id_field_rate_2_1   | Very confident       |
      | id_field_rate_2_2   | Somewhat confident   |
      | id_field_rate_2_3   | Not confident at all |
      | id_field_rate_2_4   | Somewhat confident   |
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
