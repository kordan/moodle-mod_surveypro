@mod @mod_surveypro @surveyprofield @surveyprofield_numeric
Feature: Create a numeric item
  In order to test numeric setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: Test numeric setup form
    Given the following "courses" exist:
      | fullname           | shortname          | category | groupmode |
      | Numeric setup form | Numeric setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Numeric setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                    | intro                   | course             |
      | surveypro | Test numeric setup form | Test numeric setup form | Numeric setup form |
    And surveypro "Test numeric setup form" has the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test numeric setup form" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # add an numeric item
    And I set the field "typeplugin" to "Numeric"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Type the best approximation of π you know |
      | Required                 | 1                                         |
      | Indent                   | 1                                         |
      | Question position        | left                                      |
      | Element number           | II.a                                      |
      | Hide filling instruction | 1                                         |
      | Variable                 | A1                                        |
      | Additional note          | Additional note                           |
      | Hidden                   | 1                                         |
      | Search form              | 1                                         |
      | Reserved                 | 1                                         |
      | Parent element           | Boolean [1]: Is it true?                  |
      | Parent content           | 1                                         |
      | Default                  | 3h14                                      |
      | Signed value             | 1                                         |
      | Decimal positions        | 5                                         |
      | Minimum value            | 4                                         |
      | Maximum value            | 3                                         |
    And I press "Add"

    Then I should see "This is not a number"
    Then I should see "Lower bound must be lower than upper bound"
    And I set the following fields to these values:
      | Default                  | 3.14 |
      | Minimum value            | 4    |
      | Maximum value            | 5    |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    And I set the following fields to these values:
      | Minimum value            | 3 |
      | Maximum value            | 4 |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Type the best approximation of π you know"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "A1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Default" matches value "3.14"
    Then the field "Signed value" matches value "1"
    Then the field "Decimal positions" matches value "5"
    Then the field "Minimum value" matches value "3"
    Then the field "Maximum value" matches value "4"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a Type the best approximation of π you know"
    Then the field "id_field_numeric_2" matches value "3.14"
    Then I should see "Additional note"
