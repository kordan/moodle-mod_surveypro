@mod @mod_surveypro @surveyprofield
Feature: Delete of no longer allowed answers on user change of mind
  Test the deletion of no longer allowed answers in a parent-child relation over two pages when user changes his answer
  As a teacher
  I create a parent-child relation and as a student I fill, return back, change my answer and continue.

  @javascript
  Scenario: Test change of mind: 1-2-1-2
    Given the following "courses" exist:
      | fullname        | shortname     | category | groupmode |
      | Change of mind | Change of mind | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | teacher1 | Change of mind | editingteacher |
      | student1 | Change of mind | student        |
    And the following "activities" exist:
      | activity  | name                | intro               | newpageforchild | course         |
      | surveypro | Test change of mind | Test change of mind | 1               | Change of mind |
    And surveypro "Test change of mind" contains the following items:
      | type   | plugin      |
      | field  | character   |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | select      |
      | field  | character   |
    And I am on the "Test change of mind" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I follow "edit_item_1"
    And I set the field "Required" to "1"
    And I press "Save changes"

    And I follow "edit_item_2"
    And I set the field "Required" to "1"
    And I press "Save changes"

    And I follow "edit_item_4"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Choose a direction       |
      | Required       | 1                        |
      | Parent element | Boolean [2]: Is it true? |
      | Parent content | 0                        |
    And I set the multiline field "Options" to "North\nEast\nSouth\nWest"
    And I press "Save changes"

    And I follow "edit_item_5"
    And I set the following fields to these values:
      | Content    | Question without parent |
      | Required   | 1                       |
      | id_pattern | free pattern            |

    And I press "Save changes"

    And I log out

    # Let the student start to fill the surveypro
    When I am on the "Test change of mind" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Write down your email, please" to "su@nowhere.net"
    And I set the field "Is it true?" to "0"
    And I press "Next page >>"
    Then I should see "Choose a direction"
    Then I should see "Question without parent"

    And I set the field "Choose a direction" to "South"
    And I set the field "Question without parent" to "This should remain"
    And I press "<< Previous page"

    And I set the field "Is it true?" to "1"
    And I press "Next page >>"
    Then I should not see "Choose a direction"
    Then I should see "Question without parent"

    And I press "Submit"
    Then I should not see "Some answers of this response have been found as unverified."

    And I press "Continue to responses list"
    Then I should see "1" submissions

  @javascript
  Scenario: Test change of mind: 1-2-1-3
    Given the following "courses" exist:
      | fullname               | shortname              | category | groupmode |
      | 1-2-1-3 change of mind | 1-2-1-3 change of mind | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role           |
      | teacher1 | 1-2-1-3 change of mind | editingteacher |
      | student1 | 1-2-1-3 change of mind | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                       | newpageforchild | course                 |
      | surveypro | Test 1-2-1-3 change of mind | Test 1-2-1-3 change of mind | 1               | 1-2-1-3 change of mind |
    And surveypro "Test 1-2-1-3 change of mind" contains the following items:
      | type   | plugin      |
      | field  | character   |
      | field  | boolean     |
      | format | pagebreak   |
      | field  | radiobutton |
      | format | pagebreak   |
      | field  | select      |
      | field  | checkbox    |
      | field  | character   |
    And I am on the "Test 1-2-1-3 change of mind" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

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
      | Required       | 1                           |
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
    When I am on the "Test 1-2-1-3 change of mind" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Write down your email, please" to "su@nowhere.net"
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
    Then I should not see "Some answers of this response have been found as unverified."

    And I press "Continue to responses list"
    Then I should see "1" submissions

  @javascript
  Scenario: Test change of mind: 1-3-1-2
    Given the following "courses" exist:
      | fullname               | shortname              | category | groupmode |
      | 1-3-1-2 change of mind | 1-3-1-2 change of mind | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | teacher  | teacher1@nowhere.net |
      | student1 | Student   | student  | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role           |
      | teacher1 | 1-3-1-2 change of mind | editingteacher |
      | student1 | 1-3-1-2 change of mind | student        |
    And the following "activities" exist:
      | activity  | name                        | intro                       | newpageforchild | course                 |
      | surveypro | Test 1-3-1-2 change of mind | Test 1-3-1-2 change of mind | 1               | 1-3-1-2 change of mind |
    And surveypro "Test 1-3-1-2 change of mind" contains the following items:
      | type   | plugin      |
      | field  | character   |
      | field  | radiobutton |
      | format | pagebreak   |
      | field  | boolean     |
      | field  | character   |
      | format | pagebreak   |
      | field  | boolean     |
      | field  | character   |
    And I am on the "Test 1-3-1-2 change of mind" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    And I follow "edit_item_1"
    And I set the field "Required" to "1"
    And I press "Save changes"

    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Which artwork did you make? |
      | Required       | 1                           |
    And I set the multiline field "Options" to "p::painting\ns::sculpture\nm::musical"
    And I press "Save changes"

    And I follow "edit_item_4"
    And I set the following fields to these values:
      | Content        | Is it A4 format?                              |
      | Required       | 1                                             |
      | Parent element | Radio button [2]: Which artwork did you make? |
      | Parent content | p                                             |
    And I press "Save changes"

    And I follow "edit_item_5"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Content        | Simple parent child question                  |
      | Required       | 1                                             |
      | id_pattern     | free pattern                                  |
      | Parent element | Radio button [2]: Which artwork did you make? |
      | Parent content | p                                             |
    And I press "Save changes"

    And I follow "edit_item_7"
    And I set the following fields to these values:
      | Content        | Was it carved in marble?                      |
      | Required       | 1                                             |
      | Parent element | Radio button [2]: Which artwork did you make? |
      | Parent content | s                                             |
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
    When I am on the "Test 1-3-1-2 change of mind" "surveypro activity" page logged in as student1

    And I press "New response"
    And I set the field "Write down your email, please" to "su@nowhere.net"

    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_2_1 | 1 |
    And I press "Next page >>"
    Then I should see "Was it carved in marble?"
    Then I should not see "Is it A4 format?"

    And I set the field "Was it carved in marble?" to "No"
    And I set the field "Question without parent" to "I am proud of it"
    And I press "<< Previous page"
    Then I should see "Which artwork did you make?"

    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_2_0 | 1 |
    And I press "Next page >>"
    Then I should see "Is it A4 format?"
    Then I should not see "Was it carved in marble?"

    And I set the field "Is it A4 format?" to "Yes"
    And I set the field "Simple parent child question" to "I am preparing a bigger one"
    And I press "Next page >>"
    Then I should see "Question without parent"
    Then I should not see "Was it carved in marble?"

    And I press "Submit"
    Then I should not see "Some answers of this response have been found as unverified."

    And I press "Continue to responses list"
    Then I should see "1" submissions
