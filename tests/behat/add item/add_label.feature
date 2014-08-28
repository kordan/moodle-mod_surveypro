@mod @mod_surveypro
Feature: verify a label item can be added to a survey
  In order to verify label items can be added to a survey
  As a teacher
  I add a label item to a survey

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
      | Survey name | Add label item                            |
      | Description | This is a surveypro to add each core item |
    And I follow "Add label item"

    And I set the field "plugin" to "Label"
    And I press "Add"

    And I expand all fieldsets
    And I set the following fields to these values:
      | Content | This is just a comment |
    And I press "Add"
