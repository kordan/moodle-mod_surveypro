@mod @mod_surveypro @mod_surveypro_verifyadvancedelements
Feature: verify the use of advanced elements
  In order to verify advanced elements are only seen by teacher and not students
  As a teacher and student
  I add two items with different availability and go to fill the corresponding survey and edit it

  @javascript
  Scenario: add some items
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1 | 0 | 0 |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@asd.com |
      | student1 | Student | 1 | student1@asd.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Advanced element test |
      | Description | This is a surveypro to test advanced element |
    And I follow "Advanced element test"

    # add the first age item generally available
    And I set the field "plugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | First age item |
      | Required | 1 |
      | Indent | 0 |
      | Question position | left |
      | Element number | 1 |
      | Hide filling instruction | 1 |
      | id_defaultoption_2 | Custom |
      | id_defaultvalue_year | 14 |
      | id_defaultvalue_month | 4 |
    And I press "Add"

    # add the second age item (as advanced element)
    And I set the field "plugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | Second age item |
      | Required | 1 |
      | Indent | 0 |
      | Question position | left |
      | Element number | 2 |
      | Advanced element | 1 |
      | Hide filling instruction | 1 |
      | id_defaultoption_2 | Custom |
      | id_defaultvalue_year | 14 |
      | id_defaultvalue_month | 4 |
    And I press "Add"

    And I log out

    # test the user sees only the first age item
    When I log in as "student1"
    And I follow "Course 1"
    And I follow "Advanced element test"
    And I press "Add a response"
    Then I should see "1: First age item"
    Then I should not see "2: Second age item"

    # user submit a surveypro
    And I set the following fields to these values:
      | id_surveypro_field_age_1_year | 8 |
      | id_surveypro_field_age_1_month | 2 |
    And I press "Submit"

    And I log out

    # test the teacher sees the first and the second age items both
    When I log in as "teacher1"
    And I follow "Course 1"
    And I follow "Advanced element test"
    And I follow "Responses"
    And I follow "edit_submission_1"
    Then I should see "1: First age item"
    Then I should see "2: Second age item"

    And I set the following fields to these values:
      | id_surveypro_field_age_2_year | 24 |
      | id_surveypro_field_age_2_month | 6 |
    And I press "Submit"
    And I follow "Export"
    And I set the following fields to these values:
      | Advanced element | 1 |
      | Exported file type | download to xls |
    And I press "Continue"
