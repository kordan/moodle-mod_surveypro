@mod @mod_surveypro @surveyprofield @surveyprofield_select
Feature: test the use of select setup form
  In order to test select setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test select setup form
    Given the following "courses" exist:
      | fullname          | shortname         | category | groupmode |
      | Select setup form | Select setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course            | role           |
      | teacher1 | Select setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                   | intro                  | course            |
      | surveypro | Test select setup form | Test select setup form | Select setup form |
    And surveypro "Test select setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test select setup form" "surveypro activity" page logged in as teacher1

    # add an select item
    And I set the field "typeplugin" to "Select"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Which summer holidays place do you prefer? |
      | Required                 | 1                                          |
      | Indent                   | 1                                          |
      | Question position        | left                                       |
      | Element number           | II.a                                       |
      | Variable                 | S1                                         |
      | Additional note          | Additional note                            |
      | Hidden                   | 1                                          |
      | Search form              | 1                                          |
      | Reserved                 | 1                                          |
      | Parent element           | Boolean [1]: Is it true?                   |
      | Parent content           | 1                                          |
    And I set the multiline field "Options" to "\n\nsea\n     mountain\nlake\n\nhills\n\n\n\ndesert\n\n"
    And I set the following fields to these values:
      | Option "other"           | other->specify                             |
      | id_defaultoption_1       | 1                                          |
      | id_defaultvalue          | Surfing                                    |
      | Download format          | value of selected item                     |
    And I press "Add"

    Then I should see "The default item \"Surfing\" was not found among options"
    And I set the field "id_defaultvalue" to "other"
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Which summer holidays place do you prefer?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Variable" matches value "S1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Options" matches multiline:
      """
      sea
      mountain
      lake
      hills
      desert
      """
    Then the field "Option \"other\"" matches value "other->specify"
    Then the field "id_defaultoption_1" matches value "Custom"
    Then the field "id_defaultvalue" matches value "other"
    Then the field "Download format" matches value "value of selected item"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a: Which summer holidays place do you prefer?"
    Then the field "id_surveypro_field_select_2" matches value "other"
    Then the field "id_surveypro_field_select_2_text" matches value "specify"
    Then I should see "Additional note"
