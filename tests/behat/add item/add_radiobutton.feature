@mod @mod_surveypro
Feature: verify a radiobutton item can be added to a survey
  In order to verify radiobutton items can be added to a survey
  As a teacher
  I add a radiobutton item to a survey

  @javascript
  Scenario: add radiobutton item
    Given the following "courses" exist:
      | fullname             | shortname       | category | groupmode |
      | Add radiobutton item | Add radiobutton | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Add radiobutton | editingteacher |
    And I log in as "teacher1"
    And I follow "Add radiobutton item"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Surveypro test                                |
      | Description    | This is a surveypro to add a radiobutton item |
    And I follow "Surveypro test"

    And I set the field "plugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Where do you mainly spend your summer holidays? |
      | Required          | 1                                               |
      | Indent            | 0                                               |
      | Question position | left                                            |
      | Element number    | 12a                                             |
      | Adjustment        | vertical                                        |
    And I fill the textarea "Options" with multiline content "sea\nmountain\nlake\nhills\ndesert"
    And I press "Add"

    And I set the field "plugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content           | Where do you mainly spend your summer holidays? |
      | Required          | 1                                               |
      | Indent            | 0                                               |
      | Question position | left                                            |
      | Element number    | 12b                                             |
      | Adjustment        | horizontal                                      |
    And I fill the textarea "Options" with multiline content "sea\nmountain\nlake\nhills\ndesert"
    And I press "Add"
