@mod @mod_surveypro @surveyprofield @surveyprofield_age
Feature: test the use of reserved elements
  In order to test reserved elements are only seen by teacher and not students
  As a teacher and student
  I add two items with different availability and go to fill the corresponding survey and edit it

  @javascript
  Scenario: use reserved elements
    Given the following "courses" exist:
      | fullname          | shortname         | category | groupmode |
      | Reserved elements | Reserved elements | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course            | role           |
      | teacher1 | Reserved elements | editingteacher |
      | student1 | Reserved elements | student        |
    And the following "activities" exist:
      | activity  | name                  | intro                    | course            |
      | surveypro | Reserved element test | To test reserved element | Reserved elements |
    And I log in as "teacher1"
    And I am on "Reserved elements" course homepage
    And I follow "Reserved element test"

    # add the first age item generally available
    And I set the field "typeplugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | First age item |
      | Required                 | 1              |
      | Indent                   | 0              |
      | Question position        | left           |
      | Element number           | 1              |
      | Hide filling instruction | 1              |
      | id_defaultoption_2       | Custom         |
      | id_defaultvalueyear      | 14             |
      | id_defaultvaluemonth     | 4              |
    And I press "Add"

    # add the second age item (as reserved element)
    And I set the field "typeplugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | Second age item |
      | Required                 | 1               |
      | Indent                   | 0               |
      | Question position        | left            |
      | Element number           | 2               |
      | Reserved                 | 1               |
      | Hide filling instruction | 1               |
      | id_defaultoption_2       | Custom          |
      | id_defaultvalueyear      | 14              |
      | id_defaultvaluemonth     | 4               |
    And I press "Add"

    And I log out

    # test the user sees only the first age item
    When I log in as "student1"
    And I am on "Reserved elements" course homepage
    And I follow "Reserved element test"
    And I follow "Responses"
    And I press "New response"

    Then I should see "1: First age item"
    Then I should not see "2: Second age item"

    # user submit a surveypro
    And I set the following fields to these values:
      | id_surveypro_field_age_1_year  | 8 |
      | id_surveypro_field_age_1_month | 2 |
    And I press "Submit"

    And I log out

    # test the teacher sees the first and the second age items both
    When I log in as "teacher1"
    And I am on "Reserved elements" course homepage
    And I follow "Reserved element test"
    And I follow "Responses" page in tab bar
    And I follow "edit_submission_row_1"
    Then I should see "1: First age item"
    Then I should see "2: Second age item"

    And I set the following fields to these values:
      | id_surveypro_field_age_2_year  | 24 |
      | id_surveypro_field_age_2_month | 6  |
    And I press "Submit"
