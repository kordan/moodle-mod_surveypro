@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collespreferred
Feature: apply a COLLES (preferred) mastertemplate to test graphs
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
    And the following "activities" exist:
      | activity  | name              | intro                         | course      | idnumber   |
      | surveypro | Run COLLES report | This is to test COLLES graphs | Test graphs | surveypro1 |
    And I log in as "teacher1"
    And I am on "To test COLLES graphs" course homepage

  @javascript
  Scenario: apply COLLES (Preferred) master template, add a record and call reports
    When I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Preferred)"
    And I press "Apply"
    Then I should see "In this online unit"
    Then I should see "my learning focuses on issues that interest me."

    And I log out

    # student1 logs in
    When I log in as "student1"
    And I am on "To test COLLES graphs" course homepage
    And I follow "Run COLLES report"
    And I press "New response"

    # student1 submits his first response
    And I expand all fieldsets
    And I set the following fields to these values:
      | id_surveypro_field_radiobutton_4_0             | 1          |
      | id_surveypro_field_radiobutton_5_1             | 1          |
      | id_surveypro_field_radiobutton_6_2             | 1          |
      | id_surveypro_field_radiobutton_7_3             | 1          |
      | id_surveypro_field_radiobutton_10_4            | 1          |
      | id_surveypro_field_radiobutton_11_0            | 1          |
      | id_surveypro_field_radiobutton_12_1            | 1          |
      | id_surveypro_field_radiobutton_13_2            | 1          |
      | id_surveypro_field_radiobutton_16_3            | 1          |
      | id_surveypro_field_radiobutton_17_4            | 1          |
      | id_surveypro_field_radiobutton_18_0            | 1          |
      | id_surveypro_field_radiobutton_19_1            | 1          |
      | id_surveypro_field_radiobutton_22_2            | 1          |
      | id_surveypro_field_radiobutton_23_3            | 1          |
      | id_surveypro_field_radiobutton_24_4            | 1          |
      | id_surveypro_field_radiobutton_25_0            | 1          |
      | id_surveypro_field_radiobutton_28_1            | 1          |
      | id_surveypro_field_radiobutton_29_2            | 1          |
      | id_surveypro_field_radiobutton_30_3            | 1          |
      | id_surveypro_field_radiobutton_31_4            | 1          |
      | id_surveypro_field_radiobutton_34_0            | 1          |
      | id_surveypro_field_radiobutton_35_1            | 1          |
      | id_surveypro_field_radiobutton_36_2            | 1          |
      | id_surveypro_field_radiobutton_37_3            | 1          |
      | How long did this survey take you to complete? | 2-3 min    |
      | Do you have any other comments?                | Am I sexy? |
    And I press "Submit"

    And I navigate to "Report > Colles report" in current page administration
    Then I should not see "Summary report"

    And I log out

    When I log in as "teacher1"
    And I am on "To test COLLES graphs" course homepage
    And I follow "Run COLLES report"

    # now test links provided by img's

    And I navigate to "Report > Colles report > Summary" in current page administration
    # now I should be in front of "Colles report > Summary"
    Then I should not see "Summary report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Scales"
    Then I should not see "Scales report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Relevance"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Reflective thinking"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Interactivity"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Tutor support"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Peer support"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Questions > Interpretation"
    Then I should not see "Questions report"

    And I click on "div.centerpara a" "css_element"
    # now I should be in front of "Colles report > Summary"
    Then I should not see "Summary report"

    # now test links provided by Admin menu

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Scales" in current page administration
    # now I should be in front of "Colles report > Scales"
    Then I should not see "Scales report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Relevance" in current page administration
    # now I should be in front of "Colles report > Questions > Relevance"
    Then I should not see "Questions report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Reflective thinking" in current page administration
    # now I should be in front of "Colles report > Questions > Reflective thinking"
    Then I should not see "Questions report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Interactivity" in current page administration
    # now I should be in front of "Colles report > Questions > Interactivity"
    Then I should not see "Questions report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Tutor support" in current page administration
    # now I should be in front of "Colles report > Questions > Tutor support"
    Then I should not see "Questions report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Peer support" in current page administration
    # now I should be in front of "Colles report > Questions > Peer support"
    Then I should not see "Questions report"

    And I follow "Run COLLES report"
    And I navigate to "Report > Colles report > Questions > Interpretation" in current page administration
    # now I should be in front of "Colles report > Questions > Interpretation"
    Then I should not see "Questions report"
