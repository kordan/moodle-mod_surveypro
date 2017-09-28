@mod @mod_surveypro @surveyprofield @surveyprofield_autofill
Feature: test the use of autofill setup form
  In order to test autofill setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test autofill setup form
    Given the following "courses" exist:
      | fullname            | shortname           | category | groupmode |
      | Autofill setup form | Autofill setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Autofill setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                     | intro                    | course              | idnumber   |
      | surveypro | Test autofill setup form | Test autofill setup form | Autofill setup form | surveypro1 |
    And surveypro "Test autofill setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I log in as "teacher1"
    And I am on "Autofill setup form" course homepage
    And I follow "Test autofill setup form"
    And I follow "Layout"

    # add an autofill item
    And I set the field "typeplugin" to "Autofill"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content            | Your user ID               |
      | Indent             | 1                          |
      | Question position  | left                       |
      | Element number     | II.a                       |
      | Variable           | A1                         |
      | Additional note    | Additional note            |
      | Hidden             | 1                          |
      | Search form        | 1                          |
      | Reserved           | 1                          |
      | Parent element     | Boolean [1]: Is this true? |
      | Parent content     | 1                          |
      | id_element01select | user ID                    |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Your user ID"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Variable" matches value "A1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is this true?"
    Then the field "Parent content" matches value "1"
    Then the field "element01select" matches value "user ID"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "Preview" page in tab bar
    Then I should see "II.a: Your user ID"
    Then I should see "Additional note"
