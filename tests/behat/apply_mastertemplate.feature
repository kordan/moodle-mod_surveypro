@mod @mod_surveypro
Feature: install a mastertemplate
  In order to verify mastertemplates apply correctly // Why this feature is useful
  As a teacher                                       // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I need to apply a mastertemplate                   // The feature we want

  Background:
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
    And I log in as "teacher1"
    And I follow "Course 1"
    And I turn editing mode on

  @javascript
  Scenario: apply ATTLS (20 item version) master template
    When I add a "Surveypro" to section "1" and I fill the form with:
      | Survey name | To apply ATTLS |
      | Description | This is a surveypro test to apply the ATTLS master template |
    And I follow "To apply ATTLS"
    And I set the following fields to these values:
      | Master templates | ATTLS (20 item version) |
    And I press "Create"
    Then I should see "Attitudes Towards Thinking and Learning"

  @javascript
  Scenario: apply COLLES (Preferred) master template
    When I add a "Surveypro" to section "2" and I fill the form with:
      | Survey name | To apply COLLES (Preferred) |
      | Description | This is a surveypro test to apply the COLLES (Preferred) master template |
    And I follow "To apply COLLES (Preferred)"
    And I set the following fields to these values:
      | Master templates | COLLES (Preferred) |
    And I press "Create"
    Then I should see "In this online unit, I prefer that..."
    Then I should see "my learning focuses on issues that interest me"

  @javascript
  Scenario: apply COLLES (Actual) master template
    When I add a "Surveypro" to section "3" and I fill the form with:
      | Survey name | To apply COLLES (Actual) |
      | Description | This is a surveypro test to apply the COLLES (Actual) master template |
    And I follow "To apply COLLES (Actual)"
    And I set the following fields to these values:
      | Master templates | COLLES (Actual) |
    And I press "Create"
    Then I should see "In this online unit I found that..."
    Then I should see "my learning focuses on issues that interest me"

  @javascript
  Scenario: apply COLLES (Actual Preferred) master template
    When I add a "Surveypro" to section "4" and I fill the form with:
      | Survey name | To apply COLLES (Actual Preferred) |
      | Description | This is a surveypro test to apply the COLLES (Actual Preferred) master template |
    And I follow "To apply COLLES (Actual Preferred)"
    And I set the following fields to these values:
      | Master templates | COLLES (Actual Preferred) |
    And I press "Create"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

  @javascript
  Scenario: apply Critical Incidents master template
    When I add a "Surveypro" to section "5" and I fill the form with:
      | Survey name | To apply Critical Incidents |
      | Description | This is a surveypro test to apply the Critical Incidents master template |
    And I follow "To apply Critical Incidents"
    And I set the following fields to these values:
      | Master templates | Critical Incidents |
    And I press "Create"
    Then I should see "While thinking about recent events in this class, answer the questions below."
