@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collespreferred
Feature: apply a mastertemplate
  In order to verify mastertemplates apply correctly // Why this feature is useful
  As a teacher                                       // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I need to apply a mastertemplate                   // The feature we want

  Background:
    Given the following "courses" exist:
      | fullname                | shortname            | category | groupmode |
      | To apply mastertemplate | Apply mastertemplate | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | teacher1 | Apply mastertemplate | editingteacher |
    And I log in as "teacher1"
    And I follow "To apply mastertemplate"
    And I turn editing mode on

  @javascript
  Scenario: apply COLLES (Preferred) master template
    When I add a "Surveypro" to section "2" and I fill the form with:
      | Name        | To apply COLLES (Preferred)                                              |
      | Description | This is a surveypro test to apply the COLLES (Preferred) master template |
    And I follow "To apply COLLES (Preferred)"
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Create"
    Then I should see "In this online unit, I prefer that..."
    Then I should see "my learning focuses on issues that interest me"
