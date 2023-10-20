@mod @mod_surveypro @surveyprofield @surveyprofield_time
Feature: Create a time item
  In order to test time setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: Test time setup form
    Given the following "courses" exist:
      | fullname            | shortname           | category | groupmode |
      | Datetime setup form | Datetime setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course              | role           |
      | teacher1 | Datetime setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                 | intro                | course              |
      | surveypro | Test time setup form | Test time setup form | Datetime setup form |
    And surveypro "Test time setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test time setup form" "mod_surveypro > Layout from secondary navigation" page logged in as teacher1

    # add an time item
    And I set the field "typeplugin" to "Time"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | At what time do you usually get up in the morning in the working days? |
      | Required                 | 1                                                                      |
      | Indent                   | 1                                                                      |
      | Question position        | left                                                                   |
      | Element number           | II.a                                                                   |
      | Hide filling instruction | 1                                                                      |
      | Variable                 | T1                                                                     |
      | Additional note          | Additional note                                                        |
      | Hidden                   | 1                                                                      |
      | Search form              | 1                                                                      |
      | Reserved                 | 1                                                                      |
      | Parent element           | Boolean [1]: Is it true?                                               |
      | Parent content           | 1                                                                      |
      | Step                     | five minutes                                                           |
      | Custom                   | 1                                                                      |
      | id_defaultvaluehour      | 7                                                                      |
      | id_defaultvalueminute    | 40                                                                     |
      | id_lowerboundhour        | 6                                                                      |
      | id_lowerboundminute      | 0                                                                      |
      | id_upperboundhour        | 6                                                                      |
      | id_upperboundminute      | 0                                                                      |
    And I press "Add"

    Then I should see "Lower and upper bounds must be different"
    And I set the following fields to these values:
      | id_lowerboundhour        | 10                                                                     |
      | id_lowerboundminute      | 0                                                                      |
      | id_upperboundhour        | 7                                                                      |
      | id_upperboundminute      | 0                                                                      |
    And I press "Add"

    Then I should see "Default does not fall within the specified range (see \"Upper bound\" help)"
    And I set the following fields to these values:
      | id_lowerboundhour        | 7                                                                      |
      | id_lowerboundminute      | 0                                                                      |
      | id_upperboundhour        | 10                                                                     |
      | id_upperboundminute      | 0                                                                      |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "At what time do you usually get up in the morning in the working days?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "T1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Step" matches value "five minutes"
    Then the field "Custom" matches value "1"
    Then the field "Current time" matches value ""
    Then the field "Invite" matches value ""
    Then the field "Like last response" matches value ""
    Then the field "No answer" matches value ""
    Then the field "id_defaultvaluehour" matches value "7"
    Then the field "id_defaultvalueminute" matches value "40"
    Then the field "id_lowerboundhour" matches value "7"
    Then the field "id_lowerboundminute" matches value "0"
    Then the field "id_upperboundhour" matches value "10"
    Then the field "id_upperboundminute" matches value "0"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a At what time do you usually get up in the morning in the working days?"
    Then the field "id_surveypro_field_time_2_hour" matches value "7"
    Then the field "id_surveypro_field_time_2_minute" matches value "40"
    Then I should see "Additional note"
