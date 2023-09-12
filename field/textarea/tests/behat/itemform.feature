@mod @mod_surveypro @surveyprofield @surveyprofield_textarea
Feature: test the use of textarea setup form
  In order to test textarea setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test textarea setup form
    Given the following "courses" exist:
      | fullname            | shortname           | category | groupmode |
      | Textarea setup form | Textarea setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Textarea setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                     | intro                    | course              |
      | surveypro | Test textarea setup form | Test textarea setup form | Textarea setup form |
    And surveypro "Test textarea setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test textarea setup form" "surveypro activity" page logged in as teacher1

    # add an textarea item
    And I set the field "typeplugin" to "Text (long)"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                        | Write a short description of yourself |
      | Required                       | 1                                     |
      | Indent                         | 1                                     |
      | Question position              | left                                  |
      | Element number                 | II.a                                  |
      | Hide filling instruction       | 1                                     |
      | Variable                       | T1                                    |
      | Clean answer at save time      | 1                                     |
      | Additional note                | Additional note                       |
      | Hidden                         | 1                                     |
      | Search form                    | 1                                     |
      | Reserved                       | 1                                     |
      | Parent element                 | Boolean [1]: Is it true?              |
      | Parent content                 | 1                                     |
      | Use html editor                | 1                                     |
      | Area height in rows            | 7                                     |
      | Area width in columns          | 40                                    |
      | Minimum length (in characters) | 14                                    |
      | Maximum length (in characters) | 4                                     |
    And I press "Add"

    Then I should see "Maximum length can not be lowwer-equal than minimum length"
    And I set the field "Maximum length (in characters)" to "40"
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Write a short description of yourself"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "T1"
    Then the field "Clean answer at save time" matches value "1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Use html editor" matches value "1"
    Then the field "Area height in rows" matches value "7"
    Then the field "Area width in columns" matches value "40"
    Then the field "Minimum length (in characters)" matches value "14"
    Then the field "Maximum length (in characters)" matches value "40"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a: Write a short description of yourself"
    Then I should see "Additional note"
    Then "//div[contains(@id, 'id_surveypro_field_textarea_2editable')]" "xpath_element" should exist

    And I select "Elements" from the "jump" singleselect
    And I follow "edit_item_2"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Element number                 | II.b                                  |
      | Hide filling instruction       | 1                                     |
      | Variable                       | T2                                    |
      | Use html editor                | 0                                     |
      | Additional note                | One more additional note              |
    And I press "Save as new"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.b: Write a short description of yourself"
    Then I should see "One more additional note"
    Then "//textarea[contains(@id, 'id_surveypro_field_textarea_3')]" "xpath_element" should exist
