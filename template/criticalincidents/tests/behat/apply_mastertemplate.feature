@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_criticalincidents
Feature: apply CI mastertemplate
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
      | surveypro | To apply Critical Incidents | To test Critical Incidents | Apply mastertemplate | surveypro1 |
    And I log in as "teacher1"
    And I follow "To apply mastertemplate"

  @javascript
  Scenario: apply Critical Incidents master template
    When I follow "To apply Critical Incidents"
    And I set the field "Master templates" to "Critical Incidents"
    And I press "Create"
    Then I should see "While thinking about recent events in this class, answer the questions below."
