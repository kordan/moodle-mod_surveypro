@mod @mod_surveypro @surveyprofield @surveyprofield_boolean
Feature: test the use of boolean setup form
  In order to test boolean setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test boolean setup form
    Given the following "courses" exist:
      | fullname           | shortname          | category | groupmode |
      | Boolean setup form | Boolean setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course             | role           |
      | teacher1 | Boolean setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                    | intro                   | course             | idnumber   |
      | surveypro | Test boolean setup form | Test boolean setup form | Boolean setup form | surveypro1 |
    And surveypro "Test boolean setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I log in as "teacher1"
    And I am on "Boolean setup form" course homepage
    And I follow "Test boolean setup form"
    And I follow "Layout"

    # add an boolean item
    And I set the field "typeplugin" to "Boolean"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content            | Is it true?              |
      | Required           | 1                        |
      | Indent             | 1                        |
      | Question position  | left                     |
      | Element number     | II.a                     |
      | Variable           | B1                       |
      | Additional note    | Additional note          |
      | Hidden             | 1                        |
      | Search form        | 1                        |
      | Reserved           | 1                        |
      | Parent element     | Boolean [1]: Is it true? |
      | Parent content     | 1                        |
      | Element style      | horizontal radio buttons |
      | id_defaultoption_1 | 1                        |
      | id_defaultvalue    | Yes                      |
      | Download format    | yes/no                   |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Is it true?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Variable" matches value "B1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Element style" matches value "horizontal radio buttons"
    Then the field "id_defaultoption_1" matches value "1"
    Then the field "id_defaultvalue" matches value "Yes"
    Then the field "Download format" matches value "yes/no"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "Preview" page in tab bar
    Then I should see "II.a: Is it true?"
    Then the field "id_surveypro_field_boolean_2_1" matches value "1"
    Then I should see "Additional note"

    And I follow "Elements" page in tab bar
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Element number     | II.b                     |
      | Variable           | B2                       |
      | Additional note    | One more additional note |
      | Element style      | dropdown menu            |
      | id_defaultoption_2 | 1                        |
    And I press "Save as new"

    And I follow "Preview" page in tab bar
    Then I should see "II.b: Is it true?"
    Then the field "id_surveypro_field_boolean_3" matches value "Choose..."
    Then I should see "One more additional note"
