@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: test the use of age setup form
  In order to test age setup form
  As a teacher
  I fill each its field and I return back to verify all is where I wrote it

  @javascript
  Scenario: test age setup form
    Given the following "courses" exist:
      | fullname       | shortname      | category | groupmode |
      | Age setup form | Age setup form | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course         | role           |
      | teacher1 | Age setup form | editingteacher |
    And the following "activities" exist:
      | activity  | name                | intro               | course         |
      | surveypro | Test age setup form | Test age setup form | Age setup form |
    And surveypro "Test age setup form" contains the following items:
      | type  | plugin  |
      | field | boolean |
    And I am on the "Test age setup form" "surveypro activity" page logged in as teacher1
    And I select "Layout" from secondary navigation

    # add an age item
    And I set the field "typeplugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How old were you when you started cycling? |
      | Required                 | 1                                          |
      | Indent                   | 1                                          |
      | Question position        | left                                       |
      | Element number           | II.a                                       |
      | Hide filling instruction | 1                                          |
      | Variable                 | A1                                         |
      | Additional note          | Additional note                            |
      | Hidden                   | 1                                          |
      | Search form              | 1                                          |
      | Reserved                 | 1                                          |
      | Parent element           | Boolean [1]: Is it true?                   |
      | Parent content           | 1                                          |
      | Custom                   | 1                                          |
      | id_defaultvalueyear      | 14                                         |
      | id_defaultvaluemonth     | 7                                          |
      | id_lowerboundyear        | 14                                         |
      | id_lowerboundmonth       | 4                                          |
      | id_upperboundyear        | 14                                         |
      | id_upperboundmonth       | 4                                          |
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower and upper bounds must be different"
    And I set the field "id_lowerboundmonth" to "7"
    And I press "Add"

    Then I should see "Default does not fall within the specified range"
    Then I should see "Lower bound must be lower than upper bound"
    And I set the following fields to these values:
      | id_lowerboundmonth       | 4  |
      | id_upperboundmonth       | 10 |
    And I press "Add"

    And I follow "edit_item_2"
    Then the field "Content" matches value "How old were you when you started cycling?"
    Then the field "Required" matches value "1"
    Then the field "Indent" matches value "1"
    Then the field "Question position" matches value "left"
    Then the field "Element number" matches value "II.a"
    Then the field "Hide filling instruction" matches value "1"
    Then the field "Variable" matches value "A1"
    Then the field "Additional note" matches value "Additional note"
    Then the field "Hidden" matches value "1"
    Then the field "Search form" matches value "1"
    Then the field "Reserved" matches value "1"
    Then the field "Parent element" matches value "Boolean [1]: Is it true?"
    Then the field "Parent content" matches value "1"
    Then the field "Custom" matches value "1"
    Then the field "Invite" matches value ""
    Then the field "No answer" matches value ""
    Then the field "id_defaultvalueyear" matches value "14"
    Then the field "id_defaultvaluemonth" matches value "7"
    Then the field "id_lowerboundyear" matches value "14"
    Then the field "id_lowerboundmonth" matches value "4"
    Then the field "id_upperboundyear" matches value "14"
    Then the field "id_upperboundmonth" matches value "10"
    And I press "Cancel"

    And I follow "show_item_2"
    And I select "Preview" from the "jump" singleselect
    Then I should see "II.a: How old were you when you started cycling?"
    Then the field "id_surveypro_field_age_2_year" matches value "14"
    Then the field "id_surveypro_field_age_2_month" matches value "7"
    Then I should see "Additional note"
