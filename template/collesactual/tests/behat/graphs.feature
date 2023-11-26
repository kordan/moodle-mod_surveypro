@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collesactual
Feature: Apply a COLLES (actual) mastertemplate to test graphs
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
    And the following "permission overrides" exist:
      | capability                  | permission | role    | contextlevel | reference   |
      | mod/surveypro:accessreports | Allow      | student | Course       | Test graphs |
    And the following "activities" exist:
      | activity  | name              | intro                         | course      |
      | surveypro | Run COLLES report | This is to test COLLES graphs | Test graphs |

  @javascript
  Scenario: Apply COLLES (Actual) master template, add a record and call reports
    Given I am on the "Run COLLES report" "surveypro activity" page logged in as teacher1

    And I set the field "Master templates" to "COLLES (Actual)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me."

    And I log out

    # student1 logs in
    When I am on the "Run COLLES report" "surveypro activity" page logged in as student1
    And I press "New response"

    # student1 submits his first response
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_0  | 1          |
      | id_surveypro_field_radiobutton_5_1  | 1          |
      | id_surveypro_field_radiobutton_6_2  | 1          |
      | id_surveypro_field_radiobutton_7_3  | 1          |
      | id_surveypro_field_radiobutton_10_4 | 1          |
      | id_surveypro_field_radiobutton_11_0 | 1          |
      | id_surveypro_field_radiobutton_12_1 | 1          |
      | id_surveypro_field_radiobutton_13_2 | 1          |
      | id_surveypro_field_radiobutton_16_3 | 1          |
      | id_surveypro_field_radiobutton_17_4 | 1          |
      | id_surveypro_field_radiobutton_18_0 | 1          |
      | id_surveypro_field_radiobutton_19_1 | 1          |
      | id_surveypro_field_radiobutton_22_2 | 1          |
      | id_surveypro_field_radiobutton_23_3 | 1          |
      | id_surveypro_field_radiobutton_24_4 | 1          |
      | id_surveypro_field_radiobutton_25_0 | 1          |
      | id_surveypro_field_radiobutton_28_1 | 1          |
      | id_surveypro_field_radiobutton_29_2 | 1          |
      | id_surveypro_field_radiobutton_30_3 | 1          |
      | id_surveypro_field_radiobutton_31_4 | 1          |
      | id_surveypro_field_radiobutton_34_0 | 1          |
      | id_surveypro_field_radiobutton_35_1 | 1          |
      | id_surveypro_field_radiobutton_36_2 | 1          |
      | id_surveypro_field_radiobutton_37_3 | 1          |
      | id_surveypro_field_select_38        | 2-3 min    |
      | Do you have any other comments?     | Am I sexy? |
    And I press "Submit"

    And I am on the "Run COLLES report" "mod_surveypro > Reports from secondary navigation" page
    Then I should not see "Summary report"

    And I log out

    And I am on the "Run COLLES report" "mod_surveypro > Reports from secondary navigation" page logged in as teacher1
    And I select "Colles report " from the "jump" singleselect

    # now test links provided by img's

    # now I should be in front of "summary report"
    Then I should not see "Summary report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "relevance", "reflective thinking", "interactivity", "tutor support", "peer support", "interpretation".
    Then I should not see "Relevance"
    Then I should not see "Reflective thinking"
    Then I should not see "Interactivity"
    Then I should not see "Tutor support"
    Then I should not see "Peer support"
    Then I should not see "Interpretation"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Relevance"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Reflective thinking"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Interactivity"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Tutor support"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Peer support"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Interpretation"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    Then I should not see "Summary report"

    # now test links provided by Admin menu
    # (using boost, the Admin menu may be not available. This is why I do not test its links.)
