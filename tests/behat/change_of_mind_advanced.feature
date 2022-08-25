@mod @mod_surveypro @surveyprofield
Feature: advanced deletion of no longer allowed answers on user change of mind
  Test the deletion of no longer allowed answers in a parent-child relation over three pages when user changes his answer
  As a teacher
  I create a parent-child relation and as a student I fill, return back, change my answer and continue.

  @javascript
  Scenario: Test advanced change of mind
    Given the following "courses" exist:
      | fullname       | shortname      | category | groupmode |
      | Advanced change of mind | Advanced change of mind | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                  | role           |
      | teacher1 | Advanced change of mind | editingteacher |
      | student1 | Advanced change of mind | student        |
    And the following "activities" exist:
      | activity  | name                         | intro                        | newpageforchild | course                  |
      | surveypro | Test advanced change of mind | Test advanced change of mind | 1               | Advanced change of mind |
    And surveypro "Test advanced change of mind" contains the following items:
      | type   | plugin      |
      | field  | character   |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | radiobutton |
      | format | pagebreak   |
      | field  | select      |
      | field  | checkbox    |
      | field  | character   |
    And I log in as "teacher1"
    And I am on "Advanced change of mind" course homepage
    And I follow "Test advanced change of mind"
    And I follow "Layout"

    And I follow "edit_item_1"
    And I set the field "Required" to "1"
    And I press "Save changes"

    And I follow "edit_item_2"
    And I set the field "Required" to "1"
    And I press "Save changes"

    And I follow "edit_item_4"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Which pet do you like more? |
      | Required       | 1                        |
      | Parent element | Boolean [2]: Is it true?    |
      | Parent content | 1                           |
    And I set the multiline field "Options" to "dog\ncat\nbird\ncrocodile"
    And I press "Save changes"

    And I follow "edit_item_6"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Choose a direction       |
      | Required       | 1                        |
      | Parent element | Boolean [2]: Is it true? |
      | Parent content | 0                        |
    And I set the multiline field "Options" to "North\nEast\nSouth\nWest"
    And I press "Save changes"

    And I follow "edit_item_7"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Required       | 1                        |
      | Parent element | Boolean [2]: Is it true? |
      | Parent content | 1                        |
    And I press "Save changes"

    And I follow "edit_item_8"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content    | Question without parent |
      | Required   | 1                       |
      | id_pattern | free pattern            |
    And I press "Save changes"

    And I log out

    # Let the student start to fill the surveypro
    When I log in as "student1"
    And I am on "Advanced change of mind" course homepage
    And I follow "Test advanced change of mind"

    And I press "New response"
    And I set the field "Write down your email" to "su@nowhere.net"
    And I set the field "Is it true?" to "1"
    And I press "Next page >>"
    Then I should see "Which pet do you like more?"

    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_2 | 1 |
    And I press "Next page >>"
    Then I should not see "Choose a direction"

    And I set the following fields to these values:
      | id_surveypro_field_checkbox_7_1 | 1 |
    And I set the field "Question without parent" to "This should remain"
    And I press "<< Previous page"

    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_3 | 1 |
    And I press "<< Previous page"

    And I set the field "Is it true?" to "0"
    And I press "Next page >>"
    Then I should see "Page 3 of 3"
    Then I should not see "Which pet do you like more?"
    Then I should not see "What do you usually get for breakfast?"
    Then I should see "Choose a direction"
    Then I should see "Question without parent"

    And I set the field "Choose a direction" to "South"
    And I press "Submit"

    And I press "Continue to responses list"
    Then I should see "1" submissions
    Then I should not see "Some answers of this response have been found as unverified."
