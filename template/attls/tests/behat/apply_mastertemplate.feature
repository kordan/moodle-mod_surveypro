@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_attls
Feature: Apply ATTLS (20 item version) mastertemplate
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
      | activity  | name           | intro         | course               |
      | surveypro | To apply ATTLS | To test ATTLS | Apply mastertemplate |

  @javascript
  Scenario: Apply ATTLS (20 item version) master template
    Given I am on the "To apply ATTLS" "surveypro activity" page logged in as teacher1

    And I set the field "Master templates" to "ATTLS (20 item version)"
    And I press "Apply"
    Then I should see "Attitudes Towards Thinking and Learning"
