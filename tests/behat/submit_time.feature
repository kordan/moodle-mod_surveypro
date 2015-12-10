@mod @mod_surveypro
Feature: make a submission test for each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a time item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for each available item
    Given the following "courses" exist:
      | fullname                      | shortname       | category |
      | Test submission for time item | Submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for time item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                      |
      | Description | This is a surveypro to test submission of time item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Time"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | At what time do you usually get up in the morning in the working days? |
      | Required                 | 1                                                                      |
      | Indent                   | 0                                                                      |
      | Element number           | 18                                                                     |
      | Hide filling instruction | 1                                                                      |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for time item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | id_surveypro_field_time_1_hour   | 7  |
      | id_surveypro_field_time_1_minute | 15 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
