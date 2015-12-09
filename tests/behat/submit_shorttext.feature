@mod @mod_surveypro
Feature: make a submission test for each available item
  In order to test that minimal use of surveypro is guaranteed
  As student1
  I add a shorttext item, I fill it and I go to see responses

  @javascript
  Scenario: test a submission works fine for each available item
    Given the following "courses" exist:
      | fullname                            | shortname       | category |
      | Test submission for short text item | Submission test | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student1  | user1    | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Submission test | editingteacher |
      | student1 | Submission test | student        |

    And I log in as "teacher1"
    And I follow "Test submission for short text item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                                            |
      | Description | This is a surveypro to test submission of short text item |
    And I follow "Surveypro test"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Write down your email |
      | Required                 | 1                     |
      | Indent                   | 0                     |
      | Question position        | left                  |
      | Element number           | 17a                   |
      | Hide filling instruction | 0                     |
      | id_pattern               | email address         |
    And I press "Add"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Type a web address |
      | Required                 | 1                  |
      | Indent                   | 0                  |
      | Question position        | left               |
      | Element number           | 17b                |
      | Hide filling instruction | 0                  |
      | id_pattern               | web page URL       |
    And I press "Add"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Enter a postal code |
      | Required                 | 1                   |
      | Indent                   | 0                   |
      | Question position        | left                |
      | Element number           | 17c                 |
      | Hide filling instruction | 0                   |
      | id_pattern               | custom              |
      | id_pattern_text          | 00000               |
    And I press "Add"

    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | This is a free text |
      | Required                 | 1                   |
      | Indent                   | 0                   |
      | Question position        | left                |
      | Element number           | 17d                 |
      | Hide filling instruction | 0                   |
      | id_pattern               | free pattern        |
    And I press "Add"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "Test submission for short text item"
    And I follow "Surveypro test"
    And I press "New response"

    # student1 submits
    And I set the following fields to these values:
      | 17a: Write down your email | me@myserver.net       |
      | 17b: Type a web address    | http://www.google.com |
      | 17c: Enter a postal code   | 00136                 |
      | 17d: This is a free text   | Free text here        |

    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions displayed
