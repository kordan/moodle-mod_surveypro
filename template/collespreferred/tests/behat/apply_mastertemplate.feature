@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collespreferred
Feature: apply COLLES (Preferred) mastertemplate
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
    And the following "course enrolments" exist:
      | user     | course               | role           |
      | teacher1 | Apply mastertemplate | editingteacher |
    And the following "activities" exist:
      | activity  | name                        | intro                      | course               | idnumber   |
      | surveypro | To apply COLLES (Preferred) | To test COLLES (Preferred) | Apply mastertemplate | surveypro1 |
    And I log in as "teacher1"
    And I follow "To apply mastertemplate"

  @javascript
  Scenario: apply COLLES (Preferred) master template
    When I follow "To apply COLLES (Preferred)"
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me"
