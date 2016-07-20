@mod @mod_surveypro @surveyprofield @surveyprofield_date
Feature: test the use of date setup form
  In order to test date setup form
  As a teacher
  I fill each its field and I returnback to verify all is where I wrote it

  @javascript
  Scenario: use reserved elements
    Given the following "courses" exist:
      | fullname        | shortname       | category | groupmode |
      | Date setup form | Date setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course          | role           |
      | teacher1 | Date setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                 | intro                | course          | idnumber   |
      | surveypro | Test date setup form | Test date setup form | Date setup form | surveypro1 |
    And surveypro "Test date setup form" contains the following items:
      | type   | plugin  |
      | field  | boolean |
    And I log in as "teacher1"
    And I follow "Date setup form"
    And I follow "Test date setup form"
    And I follow "Layout"

    # add an date item
    And I set the field "typeplugin" to "Date [dd/mm/yyyy]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | When were you born?        |
      | Required                 | 1                          |
      | Indent                   | 1                          |
      | Question position        | left                       |
      | Element number           | II.a                       |
      | Hide filling instruction | 1                          |
      | Variable                 | D1                         |
      | Additional note          | Additional note            |
      | Hidden                   | 1                          |
      | Search form              | 1                          |
      | Reserved                 | 1                          |
      | Parent element           | Boolean [1]: Is this true? |
      | Parent content           | 1                          |
      | id_defaultoption_1       | Custom                     |
      | id_defaultvalueday       | 30                         |
      | id_defaultvaluemonth     | February                   |
      | id_defaultvalueyear      | 1980                       |
      | id_lowerboundday         | 31                         |
      | id_lowerboundmonth       | December                   |
      | id_lowerboundyear        | 2000                       |
      | id_upperboundday         | 31                         |
      | id_upperboundmonth       | December                   |
      | id_upperboundyear        | 2000                       |
    And I press "Add"

    Then I should see "Incorrect value entered"
    Then I should see "Lower and upper bounds must be different"
    And I set the following fields to these values:
      | id_defaultvalueday       | 1        |
      | id_defaultvaluemonth     | January  |
      | id_defaultvalueyear      | 1980     |
      | id_lowerboundday         | 1        |
      | id_lowerboundmonth       | January  |
      | id_lowerboundyear        | 1990     |
      | id_upperboundday         | 31       |
      | id_upperboundmonth       | December |
      | id_upperboundyear        | 1989     |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower bound must be lower than upper bound"
    And I set the following fields to these values:
      | id_lowerboundday         | 1        |
      | id_lowerboundmonth       | January  |
      | id_lowerboundyear        | 1980     |
      | id_upperboundday         | 31       |
      | id_upperboundmonth       | December |
      | id_upperboundyear        | 1989     |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "When were you born?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "D1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is this true?"
    Then the field "Parent content" matches value "1"
    Then the field "id_defaultoption_1" matches value "Custom"
    Then the field "id_defaultvalueday" matches value "1"
    Then the field "id_defaultvaluemonth" matches value "January"
    Then the field "id_defaultvalueyear" matches value "1980"
    Then the field "id_lowerboundday" matches value "1"
    Then the field "id_lowerboundmonth" matches value "January"
    Then the field "id_lowerboundyear" matches value "1980"
    Then the field "id_upperboundday" matches value "31"
    Then the field "id_upperboundmonth" matches value "December"
    Then the field "id_upperboundyear" matches value "1989"
    And I press "Cancel"

    And I follow "show_item_2"
    And I follow "layout_preview"
    Then I should see "II.a: When were you born?"
    Then the field "id_surveypro_field_date_2_day" matches value "1"
    Then the field "id_surveypro_field_date_2_month" matches value "January"
    Then the field "id_surveypro_field_date_2_year" matches value "1980"
    Then I should see "Additional note"
