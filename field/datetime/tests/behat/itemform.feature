@mod @mod_surveypro @surveyprofield @surveyprofield_datetime
Feature: Create a datetime item
  In order to test datetime setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: Test datetime setup form
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
      | activity  | name                     | intro                    | course              |
      | surveypro | Test datetime setup form | Test datetime setup form | Datetime setup form |
    And surveypro "Test datetime setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test datetime setup form" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an datetime item
    And I set the field "typeplugin" to "Date and time [dd/mm/yyyy;hh:mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Please, write down date and time of your last flight to Los Angeles. |
      | Required                 | 1                                                                    |
      | Indent                   | 1                                                                    |
      | Question position        | left                                                                 |
      | Element number           | II.a                                                                 |
      | Hide filling instruction | 1                                                                    |
      | Variable                 | DT1                                                                  |
      | Additional note          | Additional note                                                      |
      | Hidden                   | 1                                                                    |
      | Search form              | 1                                                                    |
      | Reserved                 | 1                                                                    |
      | Parent element           | Boolean [1]: Is it true?                                             |
      | Parent content           | 1                                                                    |
      | Step                     | five minutes                                                         |
      | Custom                   | 1                                                                    |
      | id_defaultvalueday       | 30                                                                   |
      | id_defaultvaluemonth     | February                                                             |
      | id_defaultvalueyear      | 1980                                                                 |
      | id_defaultvaluehour      | 0                                                                    |
      | id_defaultvalueminute    | 0                                                                    |
      | id_lowerboundday         | 31                                                                   |
      | id_lowerboundmonth       | December                                                             |
      | id_lowerboundyear        | 2000                                                                 |
      | id_lowerboundhour        | 0                                                                    |
      | id_lowerboundminute      | 0                                                                    |
      | id_upperboundday         | 31                                                                   |
      | id_upperboundmonth       | December                                                             |
      | id_upperboundyear        | 2000                                                                 |
      | id_upperboundhour        | 0                                                                    |
      | id_upperboundminute      | 0                                                                    |
    And I press "Add"

    Then I should see "Incorrect value entered"
    Then I should see "Lower and upper bounds must be different"
    And I set the following fields to these values:
      | id_defaultvalueday       | 1        |
      | id_defaultvaluemonth     | January  |
      | id_defaultvalueyear      | 1980     |
      | id_defaultvaluehour      | 0        |
      | id_defaultvalueminute    | 0        |
      | id_lowerboundday         | 1        |
      | id_lowerboundmonth       | January  |
      | id_lowerboundyear        | 1990     |
      | id_lowerboundhour        | 0        |
      | id_lowerboundminute      | 0        |
      | id_upperboundday         | 31       |
      | id_upperboundmonth       | December |
      | id_upperboundyear        | 1989     |
      | id_upperboundhour        | 23       |
      | id_upperboundminute      | 59       |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower bound must be lower than upper bound"
    And I set the following fields to these values:
      | id_lowerboundday         | 1        |
      | id_lowerboundmonth       | January  |
      | id_lowerboundyear        | 1980     |
      | id_lowerboundhour        | 0        |
      | id_lowerboundminute      | 0        |
      | id_upperboundday         | 31       |
      | id_upperboundmonth       | December |
      | id_upperboundyear        | 1989     |
      | id_upperboundhour        | 23       |
      | id_upperboundminute      | 59       |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "Please, write down date and time of your last flight to Los Angeles."
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "DT1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Step" matches value "five minutes"
    Then the field "Custom" matches value "1"
    Then the field "Current date and time" matches value ""
    Then the field "Invite" matches value ""
    Then the field "Like last response" matches value ""
    Then the field "No answer" matches value ""
    Then the field "id_defaultvalueday" matches value "1"
    Then the field "id_defaultvaluemonth" matches value "January"
    Then the field "id_defaultvalueyear" matches value "1980"
    Then the field "id_defaultvaluehour" matches value "0"
    Then the field "id_defaultvalueminute" matches value "0"
    Then the field "id_lowerboundday" matches value "1"
    Then the field "id_lowerboundmonth" matches value "January"
    Then the field "id_lowerboundyear" matches value "1980"
    Then the field "id_lowerboundhour" matches value "0"
    Then the field "id_lowerboundminute" matches value "0"
    Then the field "id_upperboundday" matches value "31"
    Then the field "id_upperboundmonth" matches value "December"
    Then the field "id_upperboundyear" matches value "1989"
    Then the field "id_upperboundhour" matches value "23"
    Then the field "id_upperboundminute" matches value "59"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a Please, write down date and time of your last flight to Los Angeles."
    Then the field "id_surveypro_field_datetime_2_day" matches value "1"
    Then the field "id_surveypro_field_datetime_2_month" matches value "January"
    Then the field "id_surveypro_field_datetime_2_year" matches value "1980"
    Then the field "id_surveypro_field_datetime_2_hour" matches value "0"
    Then the field "id_surveypro_field_datetime_2_minute" matches value "0"
    Then I should see "Additional note"
