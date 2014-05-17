@mod @mod_surveypro
Feature: verify age items can be added to a survey
  In order to verify age items can be added to a survey
  As a teacher
  I add an age item to a survey

  @javascript
  Scenario: add some items
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | Add age item                              |
      | Description | This is a surveypro to add each core item |
    And I follow "Add age item"

    And I set the field "plugin" to "Age [yy/mm]"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content                  | How old were you at you first access to narcotics |
      | Required                 | 1                                                 |
      | Indent                   | 0                                                 |
      | Question position        | left                                              |
      | Element number           | 1                                                 |
      | Hide filling instruction | 1                                                 |
      | id_defaultoption_2       | Custom                                            |
      | id_defaultvalue_year     | 14                                                |
      | id_defaultvalue_month    | 4                                                 |
    And I press "Add"
