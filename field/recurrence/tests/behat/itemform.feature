@mod @mod_surveypro @surveyprofield @surveyprofield_recurrence
Feature: Create a recurrence item
  In order to test recurrence setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: Test recurrence setup form
    Given the following "courses" exist:
      | fullname              | shortname             | category | groupmode |
      | Recurrence setup form | Recurrence setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course                | role           |
      | teacher1 | Recurrence setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                       | intro                      | course                |
      | surveypro | Test recurrence setup form | Test recurrence setup form | Recurrence setup form |
    And surveypro "Test recurrence setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test recurrence setup form" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an recurrence item
    And I set the field "typeplugin" to "Recurrence"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When do you usually celebrate your name-day? |
      | Required                 | 1                                            |
      | Indent                   | 1                                            |
      | Question position        | left                                         |
      | Element number           | II.a                                         |
      | Hide filling instruction | 1                                            |
      | Variable                 | R1                                           |
      | Additional note          | Additional note                              |
      | Hidden                   | 1                                            |
      | Search form              | 1                                            |
      | Reserved                 | 1                                            |
      | Parent element           | Boolean [1]: Is it true?                     |
      | Parent content           | 1                                            |
      | Custom                   | 1                                            |
      | id_defaultvalueday       | 30                                           |
      | id_defaultvaluemonth     | February                                     |
      | id_lowerboundday         | 31                                           |
      | id_lowerboundmonth       | December                                     |
      | id_upperboundday         | 31                                           |
      | id_upperboundmonth       | December                                     |
    And I press "Add"

    Then I should see "Incorrect value entered"
    Then I should see "Lower and upper bounds must be different"
    And I set the following fields to these values:
      | id_defaultvalueday       | 1        |
      | id_defaultvaluemonth     | February |
      | id_lowerboundday         | 31       |
      | id_lowerboundmonth       | December |
      | id_upperboundday         | 1        |
      | id_upperboundmonth       | January  |
    And I press "Add"

    Then I should see "Default does not fall within the specified range (see \"Upper bound\" help)"
    And I set the following fields to these values:
      | id_defaultvalueday       | 1        |
      | id_defaultvaluemonth     | February |
      | id_lowerboundday         | 1        |
      | id_lowerboundmonth       | January  |
      | id_upperboundday         | 31       |
      | id_upperboundmonth       | December |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "When do you usually celebrate your name-day?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "R1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Custom" matches value "1"
    Then the field "Current date" matches value ""
    Then the field "Invite" matches value ""
    Then the field "Like last response" matches value ""
    Then the field "No answer" matches value ""
    Then the field "id_defaultvalueday" matches value "1"
    Then the field "id_defaultvaluemonth" matches value "February"
    Then the field "id_lowerboundday" matches value "1"
    Then the field "id_lowerboundmonth" matches value "January"
    Then the field "id_upperboundday" matches value "31"
    Then the field "id_upperboundmonth" matches value "December"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a When do you usually celebrate your name-day?"
    Then the field "id_surveypro_field_recurrence_2_day" matches value "1"
    Then the field "id_surveypro_field_recurrence_2_month" matches value "February"
    Then I should see "Additional note"
