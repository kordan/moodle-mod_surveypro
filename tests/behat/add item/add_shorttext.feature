@mod @mod_surveypro
Feature: verify a shorttext item can be added to a survey
  In order to verify shorttext items can be added to a survey
  As a teacher
  I add a shorttext item to a survey

  @javascript
  Scenario: add shorttext item
    Given the following "courses" exist:
      | fullname           | shortname     | category | groupmode |
      | Add shorttext item | Add shorttext | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course        | role           |
      | teacher1 | Add shorttext | editingteacher |
    And I log in as "teacher1"
    And I follow "Add shorttext item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Name        | Surveypro test                              |
      | Description | This is a surveypro to add a shorttext item |
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
