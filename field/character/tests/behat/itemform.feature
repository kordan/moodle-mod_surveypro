@mod @mod_surveypro @surveyprofield @surveyprofield_character
Feature: test the use of character setup form
  In order to test character setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test character setup form
    Given the following "courses" exist:
      | fullname             | shortname            | category | groupmode |
      | Character setup form | Character setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | teacher1 | Character setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                      | intro                     | course               |
      | surveypro | Test character setup form | Test character setup form | Character setup form |
    And surveypro "Test character setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test character setup form" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an character item
    And I set the field "typeplugin" to "Text (short)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | This is a free text      |
      | Required                       | 1                        |
      | Indent                         | 1                        |
      | Question position              | left                     |
      | Element number                 | II.a                     |
      | Hide filling instruction       | 1                        |
      | Variable                       | C1                       |
      | Additional note                | Additional note          |
      | Hidden                         | 1                        |
      | Search form                    | 1                        |
      | Reserved                       | 1                        |
      | Parent element                 | Boolean [1]: Is it true? |
      | Parent content                 | 1                        |
      | Default                        | simple default           |
      | pattern                        | free pattern             |
      | Minimum length (in characters) | 20                       |
      | Maximum length (in characters) | 4                        |
    And I press "Add"

    Then I should see "Minimum length has be lower than maximum length"
    Then I should see "Default has to be longer-equal than minimum allowed length"
    And I set the field "Maximum length (in characters)" to "40"
    And I press "Add"

    Then I should see "Default has to be longer-equal than minimum allowed length"
    And I set the field "Default" to "simple, but longer, default"
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "This is a free text"
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
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Default" matches value "simple, but longer, default"
    Then the field "pattern" matches value "free pattern"
    Then the field "Minimum length (in characters)" matches value "20"
    Then the field "Maximum length (in characters)" matches value "40"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a: This is a free text"
    Then the field "id_surveypro_field_character_2" matches value "simple, but longer, default"
    Then I should see "Additional note"
