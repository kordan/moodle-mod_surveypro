@mod @mod_surveypro @current
Feature: apply a COLLES mastertemplate to test graphs
  In order to verify graphs for COLLES mastertemplates // Why this feature is useful
  As a teacher                                         // It can be 'an admin', 'a teacher', 'a student', 'a guest', 'a user', 'a tests writer' and 'a developer'
  I need to apply a mastertemplate                     // The feature we want

  Background:
    Given the following "courses" exist:
      | fullname              | shortname   | category | groupmode |
      | To test COLLES graphs | Test graphs | 0        | 0         |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@nowhere.net |
      | student1 | Student   | 1        | student1@nowhere.net |
    And the following "course enrolments" exist:
      | user     | course      | role           |
      | teacher1 | Test graphs | editingteacher |
      | student1 | Test graphs | student        |
    And I log in as "teacher1"
    And I follow "To test COLLES graphs"
    And I turn editing mode on

  @javascript
  Scenario: apply COLLES (Preferred) master template, add records and call reports
    When I add a "Surveypro" to section "1" and I fill the form with:
      | Surveypro name | Run COLLES report                           |
      | Description | This is a surveypro test to test COLLES graphs |
    And I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Create"
    Then I should see "In this online unit I found that..."
    Then I should see "my learning focuses on issues that interest me"

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I follow "To test COLLES graphs"
    And I follow "Run COLLES report"
    And I press "New response"

    # student1 submits his first response
    And I set the following fields to these values:
      | surveypro_field_radiobutton_398003             | Almost never  |
      | surveypro_field_radiobutton_398004             | Seldom        |
      | surveypro_field_radiobutton_398005             | Sometimes     |
      | surveypro_field_radiobutton_398006             | Often         |
      | surveypro_field_radiobutton_398009             | Almost always |
      | surveypro_field_radiobutton_398010             | Almost never  |
      | surveypro_field_radiobutton_398011             | Seldom        |
      | surveypro_field_radiobutton_398012             | Sometimes     |
      | surveypro_field_radiobutton_398015             | Often         |
      | surveypro_field_radiobutton_398016             | Almost always |
      | surveypro_field_radiobutton_398017             | Almost never  |
      | surveypro_field_radiobutton_398018             | Seldom        |
      | surveypro_field_radiobutton_398021             | Sometimes     |
      | surveypro_field_radiobutton_398022             | Often         |
      | surveypro_field_radiobutton_398023             | Almost always |
      | surveypro_field_radiobutton_398024             | Almost never  |
      | surveypro_field_radiobutton_398027             | Seldom        |
      | surveypro_field_radiobutton_398028             | Sometimes     |
      | surveypro_field_radiobutton_398029             | Often         |
      | surveypro_field_radiobutton_398030             | Almost always |
      | surveypro_field_radiobutton_398033             | Almost never  |
      | surveypro_field_radiobutton_398034             | Seldom        |
      | surveypro_field_radiobutton_398035             | Sometimes     |
      | surveypro_field_radiobutton_398036             | Often         |
      | How long did this survey take you to complete? | 2-3 min       |
      | Do you have any other comments?                | Am I sexy?    |
    And I press "Submit"

    And I navigate to "Colles report" node in "Surveypro administration > Report"
    Then I should not see "Summary report"

#   @javascript
#   Scenario: apply COLLES (Actual) master template
#     When I add a "Surveypro" to section "3" and I fill the form with:
#       | Surveypro name | To apply COLLES (Actual)                                           |
#       | Description | This is a surveypro test to apply the COLLES (Actual) master template |
#     And I follow "To apply COLLES (Actual)"
#     And I set the field "Master templates" to "COLLES (Actual)"
#     And I press "Create"
#     Then I should see "In this online unit I found that..."
#     Then I should see "my learning focuses on issues that interest me"
#
#   @javascript
#   Scenario: apply COLLES (Actual Preferred) master template
#     When I add a "Surveypro" to section "4" and I fill the form with:
#       | Surveypro name | To apply COLLES (Actual Preferred)                                           |
#       | Description | This is a surveypro test to apply the COLLES (Actual Preferred) master template |
#     And I follow "To apply COLLES (Actual Preferred)"
#     And I set the field "Master templates" to "COLLES (Actual Preferred)"
#     And I press "Create"
#     Then I should see "I prefer that my learning focuses on issues that interest me."
#     Then I should see "I found that my learning focuses on issues that interest me."
