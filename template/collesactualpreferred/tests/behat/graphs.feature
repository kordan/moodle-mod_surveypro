@mod @mod_surveypro @surveyprotemplate @surveyprotemplate_collesactualpreferred
Feature: apply a COLLES (actual and preferred) mastertemplate to test graphs
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
  Scenario: apply COLLES (Preferred and Actual) master template, add a record and call reports
    When I follow "Run COLLES report"
    And I set the field "Master templates" to "COLLES (Preferred and Actual)"
    And I press "Apply"
    Then I should see "I prefer that my learning focuses on issues that interest me."
    Then I should see "I found that my learning focuses on issues that interest me."

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
      | id_surveypro_field_radiobutton_8_4             | 1          |
      | id_surveypro_field_radiobutton_9_0             | 1          |
      | id_surveypro_field_radiobutton_10_1            | 1          |
      | id_surveypro_field_radiobutton_11_2            | 1          |

      | id_surveypro_field_radiobutton_14_3            | 1          |
      | id_surveypro_field_radiobutton_15_4            | 1          |
      | id_surveypro_field_radiobutton_16_0            | 1          |
      | id_surveypro_field_radiobutton_17_1            | 1          |
      | id_surveypro_field_radiobutton_18_2            | 1          |
      | id_surveypro_field_radiobutton_19_3            | 1          |
      | id_surveypro_field_radiobutton_20_4            | 1          |
      | id_surveypro_field_radiobutton_21_0            | 1          |

      | id_surveypro_field_radiobutton_24_1            | 1          |
      | id_surveypro_field_radiobutton_25_2            | 1          |
      | id_surveypro_field_radiobutton_26_3            | 1          |
      | id_surveypro_field_radiobutton_27_4            | 1          |
      | id_surveypro_field_radiobutton_28_0            | 1          |
      | id_surveypro_field_radiobutton_29_1            | 1          |
      | id_surveypro_field_radiobutton_30_2            | 1          |
      | id_surveypro_field_radiobutton_31_3            | 1          |

      | id_surveypro_field_radiobutton_34_4            | 1          |
      | id_surveypro_field_radiobutton_35_0            | 1          |
      | id_surveypro_field_radiobutton_36_1            | 1          |
      | id_surveypro_field_radiobutton_37_2            | 1          |
      | id_surveypro_field_radiobutton_38_3            | 1          |
      | id_surveypro_field_radiobutton_39_4            | 1          |
      | id_surveypro_field_radiobutton_40_0            | 1          |
      | id_surveypro_field_radiobutton_41_1            | 1          |

      | id_surveypro_field_radiobutton_44_2            | 1          |
      | id_surveypro_field_radiobutton_45_3            | 1          |
      | id_surveypro_field_radiobutton_46_4            | 1          |
      | id_surveypro_field_radiobutton_47_0            | 1          |
      | id_surveypro_field_radiobutton_48_1            | 1          |
      | id_surveypro_field_radiobutton_49_2            | 1          |
      | id_surveypro_field_radiobutton_50_3            | 1          |
      | id_surveypro_field_radiobutton_51_4            | 1          |

      | id_surveypro_field_radiobutton_54_0            | 1          |
      | id_surveypro_field_radiobutton_55_1            | 1          |
      | id_surveypro_field_radiobutton_56_2            | 1          |
      | id_surveypro_field_radiobutton_57_3            | 1          |
      | id_surveypro_field_radiobutton_58_4            | 1          |
      | id_surveypro_field_radiobutton_59_0            | 1          |
      | id_surveypro_field_radiobutton_60_1            | 1          |
      | id_surveypro_field_radiobutton_61_2            | 1          |

      | How long did this survey take you to complete? | 2-3 min    |
      | Do you have any other comments?                | Am I sexy? |
    And I press "Submit"

    And I navigate to "Report > Colles report" in current page administration
    Then I should not see "Summary report"

    And I log out

    When I log in as "teacher1"
    And I am on "To test COLLES graphs" course homepage
    And I follow "Run COLLES report"

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
