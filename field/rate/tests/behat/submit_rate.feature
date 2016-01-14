@mod @mod_surveypro @surveyprofield @surveyprofield_rate
Feature: make a submission test for each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a rate item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for rate item
    Given the following "courses" exist:
      | fullname                                        | shortname       | category |
      | Test submission for rate item | Submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for rate item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                      |
      | Description | This is a surveypro to test submission of rate item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Rate"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | How confident are you with the following languages? |
      | Required       | 1                                                   |
      | Indent         | 0                                                   |
      | Element number | 13a                                                 |
      | Element style  | radio buttons                                       |
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nVery confident\nNot enought\nCompletely unknown"
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
    And I fill the textarea "Options" with multiline content "Italian\nSpanish\nEnglish\nFrench\nGerman"
    And I fill the textarea "Rates" with multiline content "Mother tongue\nVery confident\nNot enought\nCompletely unknown"
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for rate item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_rate_1_0_0 | 1                  |
      | id_surveypro_field_rate_1_1_1 | 1                  |
      | id_surveypro_field_rate_1_2_2 | 1                  |
      | id_surveypro_field_rate_1_3_3 | 1                  |
      | id_surveypro_field_rate_1_4_2 | 1                  |
      | id_surveypro_field_rate_2_0   | Mother tongue      |
      | id_surveypro_field_rate_2_1   | Very confident     |
      | id_surveypro_field_rate_2_2   | Not enought        |
      | id_surveypro_field_rate_2_3   | Completely unknown |
      | id_surveypro_field_rate_2_4   | Not enought        |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
