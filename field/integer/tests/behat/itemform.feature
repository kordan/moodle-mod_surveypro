@mod @mod_surveypro @surveyprofield @surveyprofield_integer
Feature: test the use of integer setup form
  In order to test integer setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test integer setup form
    Given the following "courses" exist:
      | fullname           | shortname          | category | groupmode |
      | Integer setup form | Integer setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Integer setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                    | intro                   | course             |
      | surveypro | Test integer setup form | Test integer setup form | Integer setup form |
    And surveypro "Test integer setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test integer setup form" "surveypro activity" page logged in as teacher1

    # add an integer item
    And I set the field "typeplugin" to "Integer"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How many people does your family have besides you? |
      | Required                 | 1                                                  |
      | Indent                   | 1                                                  |
      | Question position        | left                                               |
      | Element number           | II.a                                               |
      | Hide filling instruction | 1                                                  |
      | Variable                 | I1                                                 |
      | Additional note          | Additional note                                    |
      | Hidden                   | 1                                                  |
      | Search form              | 1                                                  |
      | Reserved                 | 1                                                  |
      | Parent element           | Boolean [1]: Is it true?                           |
      | Parent content           | 1                                                  |
      | id_defaultoption_1       | 1                                                  |
      | id_defaultvalue          | 1                                                  |
      | id_lowerbound            | 21                                                 |
      | id_upperbound            | 3                                                  |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower bound must be lower than upper bound"
    And I set the field "id_upperbound" to "21"
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower and upper bounds must be different"
    And I set the following fields to these values:
      | id_lowerbound            | 3                                               |
      | id_upperbound            | 21                                              |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    And I set the field "id_defaultvalue" to "5"
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "How many people does your family have besides you?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "I1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "id_defaultoption_1" matches value "1"
    Then the field "id_defaultvalue" matches value "5"
    Then the field "id_lowerbound" matches value "3"
    Then the field "id_upperbound" matches value "21"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a: How many people does your family have besides you?"
    Then the field "id_surveypro_field_integer_2" matches value "5"
    Then I should see "Additional note"
