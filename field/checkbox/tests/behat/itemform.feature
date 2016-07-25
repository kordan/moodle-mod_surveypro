@mod @mod_surveypro @surveyprofield @surveyprofield_checkbox
Feature: test the use of checkbox setup form
  In order to test checkbox setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: use reserved elements
    Given the following "courses" exist:
      | fullname            | shortname           | category | groupmode |
      | Checkbox setup form | Checkbox setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Checkbox setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                     | intro                    | course              | idnumber   |
      | surveypro | Test checkbox setup form | Test checkbox setup form | Checkbox setup form | surveypro1 |
    And surveypro "Test checkbox setup form" contains the following items:
      | type   | plugin  |
      | field  | boolean |
    And I log in as "teacher1"
    And I follow "Checkbox setup form"
    And I follow "Test checkbox setup form"
    And I follow "Layout"

    # add an checkbox item
    And I set the field "typeplugin" to "Checkbox"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | What do you usually get for breakfast? |
      | Required                 | 1                                      |
      | Indent                   | 1                                      |
      | Question position        | left                                   |
      | Element number           | II.a                                   |
      | Hide filling instruction | 1                                      |
      | Variable                 | C1                                     |
      | Additional note          | Additional note                        |
      | Hidden                   | 1                                      |
      | Search form              | 1                                      |
      | Reserved                 | 1                                      |
      | Parent element           | Boolean [1]: Is this true?             |
      | Parent content           | 1                                      |
    And I set the field "Options" to multiline:
      """
      milk


      coffee
           butter

      bread


      """
    And I set the following fields to these values:
      | Option "other"           | other->specify                         |
    And I set the field "Options" to multiline:
      """


      coffee
          bread
      other

      """
    And I set the following fields to these values:
      | "No answer" as defaults  | 0                                      |
      | Adjustment               | vertical                               |
      | Download format          | label of selected items                |
      | minimumrequired          | 2                                      |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "What do you usually get for breakfast?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "C1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is this true?"
    Then the field "Parent content" matches value "1"
    Then the field "Options" matches multiline:
      """
      milk
      coffee
      butter
      bread
      """
    Then the field "Option \"other\"" matches value "other->specify"
    Then the field "Default" matches multiline:
      """
      coffee
      bread
      other
      """
    Then the field "\"No answer\" as defaults" matches value "0"
    Then the field "Adjustment" matches value "vertical"
    Then the field "Download format" matches value "label of selected items"
    Then the field "id_minimumrequired" matches value "2"
    Then the field "minimumrequired" matches value "2"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "layout_preview"
    Then I should see "II.a: What do you usually get for breakfast?"
    Then the field "id_surveypro_field_checkbox_2_0" matches value "0"
    Then the field "id_surveypro_field_checkbox_2_1" matches value "1"
    Then the field "id_surveypro_field_checkbox_2_2" matches value "0"
    Then the field "id_surveypro_field_checkbox_2_3" matches value "1"
    Then the field "id_surveypro_field_checkbox_2_other" matches value "1"
    Then the field "id_surveypro_field_checkbox_2_text" matches value "specify"
    Then I should see "Additional note"
