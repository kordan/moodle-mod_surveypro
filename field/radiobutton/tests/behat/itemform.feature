@mod @mod_surveypro @surveyprofield @surveyprofield_radiobutton
Feature: test the use of radiobutton setup form
  In order to test radiobutton setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: use reserved elements
    Given the following "courses" exist:
      | fullname               | shortname              | category | groupmode |
      | Radiobutton setup form | Radiobutton setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                 | role           |
      | teacher1 | Radiobutton setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                        | intro                       | course                 | idnumber   |
      | surveypro | Test radiobutton setup form | Test radiobutton setup form | Radiobutton setup form | surveypro1 |
    And surveypro "Test radiobutton setup form" contains the following items:
      | type   | plugin  |
      | field  | boolean |
    And I log in as "teacher1"
    And I follow "Radiobutton setup form"
    And I follow "Test radiobutton setup form"
    And I follow "Layout"

    # add an radiobutton item
    And I set the field "typeplugin" to "Radio buttons"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Which summer holidays place do you prefer? |
      | Required                 | 1                                          |
      | Indent                   | 1                                          |
      | Question position        | left                                       |
      | Element number           | II.a                                       |
      | Variable                 | R1                                         |
      | Additional note          | Additional note                            |
      | Hidden                   | 1                                          |
      | Search form              | 1                                          |
      | Reserved                 | 1                                          |
      | Parent element           | Boolean [1]: Is this true?                 |
      | Parent content           | 1                                          |
    And I set the field "Options" to multiline:
      """

         sea
      mountain


      lake
hills
                  desert


      """
    And I set the following fields to these values:
      | Option "other"           | other->specify                             |
      | id_defaultoption_1       | Custom                                     |
      | id_defaultvalue          | hill                                       |
      | Download format          | value of selected items                    |
      | Adjustment               | horizontal                                 |
    And I press "Add"

    Then I should see "The default item \"hill\" was not found among options"
    And I set the following fields to these values:
      | id_defaultvalue          | hills                                      |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Which summer holidays place do you prefer?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Variable" matches value "R1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is this true?"
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
    Then the field "id_defaultvalue" matches value "hills"
    Then the field "Download format" matches value "value of selected items"
    Then the field "Adjustment" matches value "horizontal"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "layout_preview"
    Then I should see "II.a: Which summer holidays place do you prefer?"
    Then the field "id_surveypro_field_radiobutton_2_0" matches value "0"
    Then the field "id_surveypro_field_radiobutton_2_1" matches value "0"
    Then the field "id_surveypro_field_radiobutton_2_2" matches value "0"
    Then the field "id_surveypro_field_radiobutton_2_3" matches value "1"
    Then the field "id_surveypro_field_radiobutton_2_4" matches value "0"
    Then the field "id_surveypro_field_radiobutton_2_other" matches value "0"
    Then the field "id_surveypro_field_radiobutton_2_text" matches value "specify"
    Then I should see "Additional note"
