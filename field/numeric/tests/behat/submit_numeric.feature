@mod @mod_surveypro @surveyprofield @surveyprofield_numeric
Feature: make a submission test for "numeric" item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a numeric item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for numeric item
    Given the following "courses" exist:
      | fullname                         | shortname       | category |
      | Test submission for numeric item | Submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for numeric item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                         |
      | Description | This is a surveypro to test submission of numeric item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Numeric"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write the best approximation of π you can remember |
      | Required                 | 1                                                  |
      | Indent                   | 0                                                  |
      | Question position        | left                                               |
      | Element number           | 11                                                 |
      | Hide filling instruction | 1                                                  |
      | Decimal positions        | 2                                                  |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for numeric item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | 11: Write the best approximation of π you can remember | 3.14 |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
