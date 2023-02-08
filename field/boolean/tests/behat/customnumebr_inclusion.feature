@mod @mod_surveypro
Feature: include custom numbers into element question/content
  In order to verify the correct inclusion of the custom number at the beginning of the question
  As a teacher
  I create a surveypro with few boolean items, I assign a custom number to each item and I verify the text displays correctly.

  @javascript
  Scenario Outline: verify that custom numbers are correctly included
    Given the following "courses" exist:
      | fullname              | shortname | category | groupmode |
      | Verify custom numbers | VerifyCN  | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course   | role           |
      | teacher1 | VerifyCN | editingteacher |
    And the following "activities" exist:
      | activity  | name            | intro                        | course   |
      | surveypro | VerifyCN survey | Verify custom numbers survey | VerifyCN |
    And surveypro "VerifyCN survey" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "VerifyCN survey" "surveypro activity" page logged in as teacher1
    And I follow "Layout" page in tab bar
    And I follow "edit_item_1"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | <content>      |
      | Element number | <customnumber> |
    And I press "Save changes"
    And I follow "Preview" page in tab bar
    Then I should see "<output>"

    Examples:
      | content                                                              | customnumber  | output              |
      | Is it true?                                                          | 1             | 1: Is it true?      |
      | <p>Is it true?</p>                                                   | 2a            | 2a: Is it true?     |
      | <p dir="ltr">Is it true?</p>                                         | III           | III: Is it true?    |
      | <p dir="ltr"><em>Is it true?</em></p>                                | 4th           | 4th: Is it true?    |
      | <p><p dir="ltr">Is it true?</p></p>                                  | 5)            | 5): Is it true?     |
      | <p><p dir="ltr"><p class="lookatme">Is it true?</p></p></p>          | 6.a           | 6.a: Is it true?    |
      | <p><p dir="ltr"><h3><p class="lookatme">Is it true?</p></h3></p></p> | 7.3-II        | 7.3-II: Is it true? |
